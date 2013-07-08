/*******************************************************************************
 * Copyright 20013 Greenetree LLC or its affiliates. All Rights Reserved. Licensed under the Apache License, Version 2.0
 * (the "License");
 * 
 * You may not use this file except in compliance with the License. You may obtain a copy of the License at:
 * http://aws.amazon.com/apache2.0 This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied. See the License for the specific language governing permissions and limitations
 * under the License. *****************************************************************************
 * 
 * @author Chiori Greene
 * @email chiorigreene@gmail.com
 * @copyright Debnroo.com (2013)
 * @date February 1st, 2013
 * 
 */
package com.chiorichan;

import java.io.IOException;
import java.net.URL;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Date;
import java.util.HashMap;
import java.util.Locale;
import java.util.Set;
import java.util.concurrent.CopyOnWriteArraySet;
import java.util.logging.Level;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;

import org.eclipse.jetty.util.security.Credential.MD5;
import org.eclipse.jetty.websocket.WebSocket;
import org.eclipse.jetty.websocket.WebSocketFactory;
import org.joda.time.DateTime;
import org.joda.time.Days;
import org.joda.time.format.DateTimeFormatter;
import org.joda.time.format.ISODateTimeFormat;
import org.json.JSONException;
import org.json.JSONObject;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

import vnet.java.util.MySQLUtils;
import co.applebloom.apps.rewards.Asset;
import co.applebloom.apps.rewards.AssetsHandler;

public class OurWebsocketServlet extends HttpServlet
{
	public static final Set<RewardsWebSocket> _members = new CopyOnWriteArraySet<RewardsWebSocket>();
	public static AssetsHandler assetsHandler = new AssetsHandler();
	private static final long serialVersionUID = 1L;
	private WebSocketFactory _wsFactory;
	public SqlConnector sql = null;
	
	@Override
	public void init() throws ServletException
	{
		sql = ChioriFramework.getDatabase();
		
		_wsFactory = new WebSocketFactory( new WebSocketFactory.Acceptor()
		{
			public boolean checkOrigin( HttpServletRequest request, String origin )
			{
				return true;
			}
			
			public WebSocket doWebSocketConnect( HttpServletRequest request, String protocol )
			{
				return new RewardsWebSocket( request );
				
				/*
				 * if ( "notify".equals( protocol ) ) return new ChatWebSocket(); return null;
				 */
			}
		} );
		
		_wsFactory.setBufferSize( 65536 );
		_wsFactory.setMaxIdleTime( 6000000 );
	}
	
	@Override
	protected void doGet( HttpServletRequest request, HttpServletResponse response ) throws IOException
	{
		if ( _wsFactory.acceptWebSocket( request, response ) )
			return;
		
		response.sendError( HttpServletResponse.SC_SERVICE_UNAVAILABLE, "Websocket Connections Only" );
	}
	
	public static RewardsWebSocket search( String query )
	{
		if ( query == null )
			return null;
		
		int cnt = 0;
		
		for ( RewardsWebSocket rws : _members )
		{
			if ( rws != null )
			{
				if ( query.matches( "[0-9]+" ) )
				{
					if ( cnt == Integer.parseInt( query ) )
						return rws;
				}
				else
				{
					if ( rws.ip_addr.startsWith( query ) )
					{
						return rws;
					}
					else if ( rws.myAsset != null && ( rws.myAsset.deviceId.startsWith( query ) || rws.myAsset.lastIp.startsWith( query ) ) )
					{
						return rws;
					}
				}
				
				cnt++;
			}
		}
		
		return null;
	}
	
	public static void scanAssets()
	{
		for ( RewardsWebSocket rws : _members )
		{
			if ( rws != null && rws.deviceId != null && !rws.deviceId.isEmpty() )
			{
				Asset a = assetsHandler.getAsset( rws.deviceId );
				
				if ( a != null )
					rws.myAsset = a;
			}
		}
	}
	
	public class RewardsWebSocket implements WebSocket.OnTextMessage
	{
		volatile Connection _connection;
		public Asset myAsset = null;
		public String ip_addr = "0.0.0.0";
		public String deviceId = null;
		public long lastPing = 0;
		
		public RewardsWebSocket(HttpServletRequest request)
		{
			ip_addr = request.getRemoteAddr();
		}
		
		public void disconnect()
		{
			_connection.close();
		}
		
		@Override
		public String toString()
		{
			if ( myAsset != null )
			{
				return "IP Addr: " + ip_addr + ", " + myAsset.type + ", " + myAsset.toString();
			}
			else
			{
				return "IP Addr: " + ip_addr;
			}
		}
		
		public void onOpen( Connection connection )
		{
			_connection = connection;
			_members.add( this );
			InteractiveConsole.log( Level.WARNING, ip_addr, "Websocket Connection Created!" );
		}
		
		public void onClose( int closeCode, String message )
		{
			_members.remove( this );
			InteractiveConsole.log( Level.WARNING, ip_addr, "Websocket Connection Closed, " + closeCode + ": " + message );
		}
		
		/**
		 * NOT IMPLEMENTED
		 */
		public void sendUpdateNotice()
		{
			try
			{
				// TODO: Only send this notice if the device is out of date.
				sendMessage( "UPDT http://web.applebloom.co/files/AppleBloomRewards_v4-5-0208.apk" );
			}
			catch ( IOException e )
			{
				e.printStackTrace();
			}
		}
		
		public void sendMessage( String msg ) throws IOException
		{
			if ( !msg.contains( "PONG" ) )
				InteractiveConsole.log( Level.INFO, ip_addr, "Sent Message \"" + msg + "\"" );
			
			if ( this._connection.isOpen() )
			{
				this._connection.sendMessage( msg );
			}
			else
			{
				// System.err.print(
				// "It seems the connection to the device has been closed and I can not initalize a connection from my end."
				// );
				// TODO: Remember the message that was going to be sent since it might have been extremely important.
			}
		}
		
		public String generateId( String seed )
		{
			return MD5.digest( seed + System.currentTimeMillis() ).replaceAll( "MD5:", "" ).trim();
		}
		
		public void updateAccounts() throws IOException
		{
			if ( myAsset == null )
			{
				// Similar to INVD but instead of asking the device to Register for the first time, we are asking it
				// to simply idenify its self with HELO.
				sendMessage( "NOID" );
			}
			else
			{
				String locId = myAsset.locationId;
				
				if ( locId == null || locId.equals( "" ) )
				{
					// Lets the client know that its not registered with any locations.
					sendMessage( "NOLO" );
				}
				else
				{
					HashMap<String, Object> loc = ChioriFramework.getDatabase().selectOne( "locations", "locID", myAsset.locationId );
					
					ResultSet rsSub = null;
					ResultSet rs = ChioriFramework.getDatabase().query( "SELECT * FROM `contacts_rewards` WHERE `locID` = '" + myAsset.locationId + "';" );
					
					ArrayList<String> lst = new ArrayList<String>();
					
					if ( ChioriFramework.getDatabase().getRowCount( rs ) > 0 )
					{
						try
						{
							do
							{
								rsSub = ChioriFramework.getDatabase().query( "SELECT * FROM `contacts` WHERE `mobile_no` = '" + rs.getString( "mobile_no" ) + "';" );
								
								if ( ChioriFramework.getDatabase().getRowCount( rsSub ) > 0 )
									lst.add( "{'id': '" + rs.getString( "mobile_no" ) + "', 'name': '" + MySQLUtils.escape( rsSub.getString( "name" ) ) + "', 'email': '" + MySQLUtils.escape( rsSub.getString( "email" ) ) + "', 'first_added': '" + rsSub.getString( "first_added" ) + "', 'balance': '" + rs.getString( "balance" ) + "', 'last_instore_check': '" + rs.getString( "last_instore_check" ) + "'}" );
							}
							while ( rs.next() );
							
							String jsonr = "{'users':[";
							String sep = "";
							
							for ( String s : lst )
							{
								jsonr += sep + s;
								sep = ",";
							}
							
							jsonr += "]}";
							
							InteractiveConsole.info( jsonr );
							
							JSONObject json = new JSONObject( jsonr );
							
							System.out.println( json.toString() );
							
							sendMessage( "ACCT " + json.toString() );
						}
						catch ( SQLException e )
						{
							e.printStackTrace();
						}
						catch ( JSONException e )
						{
							e.printStackTrace();
						}
					}
				}
			}
		}
		
		public void updatePlaylist() throws IOException
		{
			if ( myAsset == null )
			{
				// Similar to INVD but instead of asking the device to Register for the first time, we are asking it
				// to simply idenify its self with HELO.
				sendMessage( "NOID" );
			}
			else
			{
				String locId = myAsset.locationId;
				
				HashMap<String, Object> loc = ChioriFramework.getDatabase().selectOne( "locations", "locID", myAsset.locationId );
				
				ResultSet rs = null;
				
				if ( locId == null || locId.equals( "" ) )
				{
					rs = ChioriFramework.getDatabase().query( "SELECT * FROM `youtubeLibrary` WHERE `owner` = '' AND `disabled` = '0';" );
				}
				else
				{
					rs = ChioriFramework.getDatabase().query( "SELECT * FROM `youtubeLibrary` WHERE (`owner` = '" + myAsset.locationId + "' OR `owner` = '' OR `owner` = '" + loc.get( "acctID" ) + "') AND `disabled` = '0';" );
				}
				
				ArrayList<String> lst = new ArrayList<String>();
				
				// Are there videos found
				if ( ChioriFramework.getDatabase().getRowCount( rs ) > 0 )
				{
					try
					{
						do
						{
							if ( rs.getString( "type" ).equals( "playlist" ) )
							{
								// https://gdata.youtube.com/feeds/api/playlists/[PLAYLISTID]?v=2
								
								try
								{
									URL url = new URL( "https://gdata.youtube.com/feeds/api/playlists/" + rs.getString( "Id" ) + "?v=2" );
									
									DocumentBuilderFactory dbFactory = DocumentBuilderFactory.newInstance();
									DocumentBuilder dBuilder = dbFactory.newDocumentBuilder();
									Document xml = dBuilder.parse( url.openStream() );
									
									xml.getDocumentElement().normalize();
									
									NodeList nList = xml.getElementsByTagName( "entry" );
									
									for ( int temp = 0; temp < nList.getLength(); temp++ )
									{
										Node nNode = nList.item( temp );
										
										if ( nNode.getNodeType() == Node.ELEMENT_NODE )
										{
											Element eElement = (Element) nNode;
											
											Element tags = (Element) eElement.getElementsByTagName( "media:group" ).item( 0 );
											String videoId = tags.getElementsByTagName( "yt:videoid" ).item( 0 ).getTextContent();
											String uploaded = tags.getElementsByTagName( "yt:uploaded" ).item( 0 ).getTextContent();
											
											// TODO: Filter results for HD formats.
											
											// Make sure upload was less then 4 days ago.
											DateTimeFormatter fmt = ISODateTimeFormat.dateTime();
											DateTime startDate = fmt.parseDateTime( uploaded );
											DateTime endDate = new DateTime(); // current date
											Days diff = Days.daysBetween( startDate, endDate );
											
											if ( diff.isLessThan( Days.FOUR ) )
												lst.add( "{'id': '" + videoId + "'}" );
										}
									}
								}
								catch ( Exception e )
								{
									e.printStackTrace();
								}
							}
							else if ( rs.getString( "type" ).equals( "user" ) )
							{
								// https://gdata.youtube.com/feeds/api/users/[USERID]/uploads?v=2
								
								try
								{
									URL url = new URL( "https://gdata.youtube.com/feeds/api/users/" + rs.getString( "Id" ) + "/uploads?v=2" );
									
									DocumentBuilderFactory dbFactory = DocumentBuilderFactory.newInstance();
									DocumentBuilder dBuilder = dbFactory.newDocumentBuilder();
									Document xml = dBuilder.parse( url.openStream() );
									
									xml.getDocumentElement().normalize();
									
									NodeList nList = xml.getElementsByTagName( "entry" );
									
									for ( int temp = 0; temp < nList.getLength(); temp++ )
									{
										Node nNode = nList.item( temp );
										
										if ( nNode.getNodeType() == Node.ELEMENT_NODE )
										{
											Element eElement = (Element) nNode;
											
											Element tags = (Element) eElement.getElementsByTagName( "media:group" ).item( 0 );
											String videoId = tags.getElementsByTagName( "yt:videoid" ).item( 0 ).getTextContent();
											String uploaded = tags.getElementsByTagName( "yt:uploaded" ).item( 0 ).getTextContent();
											
											// Make sure upload was less then 4 days ago.
											DateTimeFormatter fmt = ISODateTimeFormat.dateTime();
											DateTime startDate = fmt.parseDateTime( uploaded );
											DateTime endDate = new DateTime(); // current date
											Days diff = Days.daysBetween( startDate, endDate );
											
											if ( diff.isLessThan( Days.FOUR ) )
												lst.add( "{'id': '" + videoId + "'}" );
										}
									}
								}
								catch ( Exception e )
								{
									e.printStackTrace();
								}
							}
							else
							{
								// TODO: More information!
								lst.add( "{'id': '" + rs.getString( "Id" ) + "'}" );
							}
						}
						while ( rs.next() );
						
						String jsonr = "{'list':[";
						String sep = "";
						
						for ( String s : lst )
						{
							jsonr += sep + s;
							sep = ",";
						}
						
						jsonr += "]}";
						
						JSONObject json = new JSONObject( jsonr );
						
						System.out.println( json.toString() );
						
						sendMessage( "UTUB " + json.toString() );
					}
					catch ( SQLException e )
					{
						// TODO: Add better exception handling - i.e. Save the exceptions for later review
						e.printStackTrace();
					}
					catch ( JSONException e )
					{
						e.printStackTrace();
					}
				}
			}
		}
		
		public void onMessage( String data )
		{
			String arr[] = data.split( " ", 2 );
			String cmd = arr[0].toUpperCase();
			data = ( arr.length > 1 ) ? arr[1].trim() : "";
			
			SqlConnector db = ChioriFramework.getDatabase();
			
			// TODO: Make sure UUID's follow a format - At this point it's a MD5 Hash
			try
			{
				if ( !cmd.equals( "PING" ) )
					InteractiveConsole.log( Level.INFO, ip_addr, "Got Message \"" + cmd + "\" with Payload \"" + data + "\"" );
				
				if ( cmd.equals( "PING" ) )
				{
					if ( myAsset != null )
						myAsset.updateInfo( "", ip_addr );
					
					if ( data != "" )
					{
						// InteractiveConsole.info( "&4Connection latency of " + Long.parseLong( data ) + " milliseconds. " );
					}
					
					if ( deviceId != null && !deviceId.equals( "" ) && lastPing < System.currentTimeMillis() - 60000 )
					{
						db.queryUpdate( "INSERT INTO `devices_history` (`deviceId`, `action`, `latency`, `timestamp`) VALUES ('" + deviceId + "', 'ping', '" + data + "', '" + System.currentTimeMillis() + "');" );
						lastPing = System.currentTimeMillis();
					}
					
					// if ( myAsset == null )
					// sendMessage( "NOID" );
					
					sendMessage( "PONG" );
				}
				else if ( cmd.equals( "ACCT" ) ) // There was a change to this account. Check it.
				{
					// TODO: Notify other devices about a point, email or name change on an account.
					
					if ( !data.equals( "" ) && myAsset.locationId != null )
					{
						String id = "", email = "", name = "";
						long added = 0, lst_chk = 0, bal = 0;
						
						try
						{
							JSONObject dada = new JSONObject( data );
							
							id = dada.getString( "id" );
							bal = dada.getLong( "balance" );
							lst_chk = dada.getLong( "last_instore_check" );
							email = dada.getString( "email" );
							added = dada.getLong( "first_added" );
							name = dada.getString( "name" );
							
							if ( email.equalsIgnoreCase( "null" ) )
								email = "";
							
							if ( lst_chk < 1 )
								lst_chk = System.currentTimeMillis();
						}
						catch ( JSONException e )
						{
							e.printStackTrace();
						}
						finally
						{
							ResultSet rs = db.query( "SELECT * FROM `contacts` WHERE `mobile_no` = '" + id + "';" );
							
							if ( db.getRowCount( rs ) > 0 )
							{
								db.queryUpdate( "UPDATE `contacts` SET `last_instore_check` = '" + lst_chk + "' WHERE `mobile_no` = '" + id + "';" );
								
								if ( email != null || !email.isEmpty() )
									db.queryUpdate( "UPDATE `contacts` SET `email` = '" + email + "' WHERE `mobile_no` = '" + id + "';" );
								
								if ( name != null || !name.isEmpty() )
									db.queryUpdate( "UPDATE `contacts` SET `name` = '" + name + "' WHERE `mobile_no` = '" + id + "';" );
								
								InteractiveConsole.info( "&4Updated existing contact record!" );
							}
							else
							{
								if ( added < 1 )
									added = System.currentTimeMillis();
								
								db.queryUpdate( "INSERT INTO `contacts` (`mobile_no`, `last_instore_check`, `first_added`, `email`, `name`) VALUES ('" + id + "', '" + lst_chk + "', '" + added + "', '" + email + "', '" + name + "');" );
								InteractiveConsole.info( "&4Inserted new contact record!" );
							}
							
							rs = db.query( "SELECT * FROM `contacts_rewards` WHERE `mobile_no` = '" + id + "' AND `locID` = '" + myAsset.locationId + "';" );
							
							if ( db.getRowCount( rs ) > 0 )
							{
								db.queryUpdate( "UPDATE `contacts_rewards` SET `last_instore_check` = '" + lst_chk + "', `balance` = '" + bal + "' WHERE `mobile_no` = '" + id + "' AND `locID` = '" + myAsset.locationId + "';" );
								
								InteractiveConsole.info( "&4Updated existing rewards record!" );
							}
							else
							{
								db.queryUpdate( "INSERT INTO `contacts_rewards` (`mobile_no`, `locID`, `balance`, `last_instore_check`) VALUES ('" + id + "', '" + myAsset.locationId + "', '" + bal + "', '" + lst_chk + "');" );
								
								InteractiveConsole.info( "&4Inserted mew rewards record!" );
							}
						}
					}
				}
				else if ( cmd.equals( "TXT" ) ) // Person permitted us to add them to our texting list. Double Opt-in.
				{
					// Welcome, To the SMS VIP Club. Please reply with YES to finish your opt-in process else don't reply and
					// you will not receive anymore sms from us.;
					String content = CrossoverHandler.getSetting( "TEXT_REWARDS_SUCCESS", myAsset.locationId, "Hello from %L% Rewards. To finish the SMS opt-in process you MUST:" );
					
					content = content.replace( "%L%", myAsset.getLocationArray().get( "title" ) );
					// content = content.replace( "%D%", new SimpleDateFormater(). new Date() );
					
					if ( myAsset.locationId != null && myAsset.locationId != "" )
					{
						ResultSet rs = db.query( "SELECT * FROM `locations` WHERE `locID` = '" + myAsset.locationId + "';" );
						if ( db.getRowCount( rs ) > 0 )
						{
							HashMap<String, String> result = SMSHandler.inviteMobile( Arrays.asList( data ), rs.getString( "keyword" ), content );
							
							SMSHandler.sendSMS( Arrays.asList( "7089123702" ), "Invited \"" + data + "\" to group \"" + myAsset.locationId + "/" + rs.getString( "keyword" ) + "\" with result \"" + result.get( "resmsg" ) + "\"", "donut" );
							
							try
							{
								db.queryUpdate( "INSERT INTO `sms_translog` (`mobile_no`, `origin`, `class`, `operator`, `msg`, `created`, `pending`, `success`, `debug`) VALUES ('" + data + "', 'API_INVITE', '(rewards)', 'UNKNOWN', 'Invited \"" + data + "\" to group \"" + myAsset.locationId + "/" + rs.getString( "keyword" ) + "\" with result \"" + result.get( "resmsg" ) + "\"', '" + result.get( "created" ) + "', '0', '1', '" + MySQLUtils.mysql_real_escape_string( db.con, result.get( "resmsg" ) ) + "');" );
							}
							catch ( Exception e )
							{
								e.printStackTrace();
							}
						}
					}
					else
					{
						sendMessage( "Group invite canceled, Because location is not setup for SMS." );
					}
				}
				else if ( cmd.equals( "UPAC" ) ) // Device is asking we send a fresh accounts feed
				{
					updateAccounts();
				}
				else if ( cmd.equals( "UPVI" ) ) // Device is asking that we send it the latest youtube library. ABStreaming
															// devices only!
				{
					updatePlaylist();
				}
				else if ( cmd.equals( "UPRE" ) ) // Device is asking that we update it's redeemables. ADRewards devices
															// only!
				{
					if ( myAsset == null )
					{
						// Similar to INVD but instead of asking the device to Register for the first time, we are asking it
						// to simply idenify its self with HELO.
						sendMessage( "NOID" );
					}
					else
					{
						String locId = myAsset.locationId;
						
						if ( locId == null || locId.equals( "" ) )
						{
							// Lets the client know that its not registered with any locations.
							sendMessage( "NOLO" );
						}
						else
						{
							HashMap<String, Object> loc = ChioriFramework.getDatabase().selectOne( "locations", "locID", myAsset.locationId );
							
							ResultSet rsSub = null;
							ResultSet rs = ChioriFramework.getDatabase().query( "SELECT * FROM `rewards_redeem` WHERE (`owner` = '" + myAsset.locationId + "' OR `owner` = '" + loc.get( "acctID" ) + "') AND `disabled` = '0';" );
							
							ArrayList<String> lst = new ArrayList<String>();
							
							// Are there redeemables found
							if ( ChioriFramework.getDatabase().getRowCount( rs ) > 0 )
							{
								try
								{
									do
									{
										if ( rs.getString( "title" ).startsWith( "all:" ) )
										{
											String id = rs.getString( "title" ).substring( 4 );
											rsSub = ChioriFramework.getDatabase().query( "SELECT * FROM `rewards_redeem` WHERE `owner` = '" + id + "' AND `disabled` = '0';" );
											if ( ChioriFramework.getDatabase().getRowCount( rsSub ) > 0 )
												do
												{
													lst.add( "{'id': '" + rsSub.getString( "redeemID" ) + "', 'title': '" + rsSub.getString( "title" ) + "', 'cost': '" + rsSub.getString( "cost" ) + "'}" );
												}
												while ( rsSub.next() );
										}
										else if ( rs.getString( "title" ).startsWith( "one:" ) )
										{
											String id = rs.getString( "title" ).substring( 4 );
											rsSub = ChioriFramework.getDatabase().query( "SELECT * FROM `rewards_redeem` WHERE `owner` = '" + id + "' AND `disabled` = '0';" );
											if ( ChioriFramework.getDatabase().getRowCount( rsSub ) > 0 )
												do
												{
													lst.add( "{'id': '" + rsSub.getString( "redeemID" ) + "', 'title': '" + rsSub.getString( "title" ) + "', 'cost': '" + rsSub.getString( "cost" ) + "'}" );
												}
												while ( rsSub.next() );
										}
										else
										{
											lst.add( "{'id': '" + rs.getString( "redeemID" ) + "', 'title': '" + rs.getString( "title" ) + "', 'cost': '" + rs.getString( "cost" ) + "'}" );
										}
									}
									while ( rs.next() );
									
									String jsonr = "{'list':[";
									String sep = "";
									
									for ( String s : lst )
									{
										jsonr += sep + s;
										sep = ",";
									}
									
									jsonr += "]}";
									
									JSONObject json = new JSONObject( jsonr );
									
									System.out.println( json.toString() );
									
									sendMessage( "REDM " + json.toString() );
								}
								catch ( SQLException e )
								{
									// TODO: Add better exception handling - i.e. Save the exceptions for later review
									e.printStackTrace();
								}
								catch ( JSONException e )
								{
									e.printStackTrace();
								}
							}
						}
					}
				}
				else if ( cmd.equals( "ASUM" ) ) // Device would like to assume this deviceId - Usually manually done.
				{
					// Get ID from Asset Handler
					myAsset = assetsHandler.getAsset( data );
					
					// Does ID exist
					if ( myAsset == null )
					{
						// `deviceId`, `lastIp`, `lastActive`, `created`, `serial`, `model`, `appVersion`, `status`, `type`,
						// `locationId`, `state`
						// Create new device record
						sql.queryUpdate( "INSERT INTO `devices` ( `deviceId`, `lastIp`, `lastActive`, `created`, `status`, `type`, `state` ) VALUES ( '" + myAsset.deviceId + "', '" + ip_addr + "', '" + System.currentTimeMillis() + "', '" + System.currentTimeMillis() + "', 'unassigned', 'rewards', 'This device has just registered it's self with our servers.' );" );
						
						// Have asset loaded into memory
						assetsHandler.reloadAsset( myAsset.deviceId );
						myAsset = assetsHandler.getAsset( data );
						
						// Request that the device sends you information such as Serial, Model and App Version
						sendMessage( "INFO" );
						
						deviceId = myAsset.deviceId;
						sendMessage( "UUID" + myAsset.deviceId );
					}
					else if ( myAsset.status.equalsIgnoreCase( "deactivated" ) )
					{
						myAsset.status = "active";
						
						// Request that the device sends you information such as Serial, Model and App Version
						sendMessage( "INFO" );
						
						deviceId = myAsset.deviceId;
						sendMessage( "UUID" + myAsset.deviceId );
					}
				}
				else if ( cmd.equals( "UPDT" ) ) // Device has some device information to store.
				{
					if ( myAsset == null )
					{
						// Similar to INVD but instead of asking the device to Register for the first time, we are asking it
						// to simply idenify its self with HELO.
						sendMessage( "NOID" );
					}
					else
					{
						myAsset.updateInfo( data );
					}
				}
				else if ( cmd.equals( "DREG" ) ) // Device would like to deactive it's deviceId - Usually manually done.
				{	
					
				}
				else if ( cmd.equals( "TYPE" ) ) // Temp fix for other types of devices. i.e. TV's
				{
					if ( data.equals( "rewards" ) || data.equals( "video" ) )
					{
						if ( myAsset != null )
						{
							myAsset.type = data;
							sql.queryUpdate( "UPDATE `devices` SET `type` = '" + data + "' WHERE `deviceId` = '" + myAsset.deviceId + "';" );
						}
						else
						{
							// This devices is not synced with the server yet
						}
					}
					else
					{
						// Invalid Device Type
					}
				}
				else if ( cmd.equals( "BOOT" ) ) // Device has no deviceId and would like to request a new one.
				{
					// TODO: Prevent duplicate requests
					String dId = "";
					
					// Make sure we are not reusing any IDs
					do
					{
						dId = generateId( data );
					}
					while ( assetsHandler.assetExists( dId ) );
					
					// `deviceId`, `lastIp`, `lastActive`, `created`, `serial`, `model`, `appVersion`, `status`, `type`,
					// `locationId`, `state`
					// Create new device record
					sql.queryUpdate( "INSERT INTO `devices` ( `deviceId`, `lastIp`, `lastActive`, `created`, `status`, `type`, `state` ) VALUES ( '" + dId + "', '" + ip_addr + "', '" + System.currentTimeMillis() + "', '" + System.currentTimeMillis() + "', 'unassigned', 'rewards', 'This device has just registered its self with our servers.' );" );
					
					// Have asset loaded into memory
					assetsHandler.reloadAsset( dId );
					
					myAsset = assetsHandler.getAsset( dId );
					
					// Request that the device sends you information such as Serial, Model and App Version
					deviceId = dId;
					sendMessage( "UUID " + dId );
					sendMessage( "INFO" );
					
					String loc = myAsset.getLocationJSON();
					
					if ( loc != null )
					{
						sendMessage( "LOCI " + loc );
						
						// TODO: Inform the device the SMS is not enabled at this location.
						// sendMessage( "OPT SMS=FALSE" );
					}
				}
				else if ( cmd.equals( "LOCI" ) ) // Request Location Information
				{
					if ( myAsset != null )
					{
						String loc = myAsset.getLocationJSON();
						if ( loc != null )
							sendMessage( "LOCI " + loc );
					}
				}
				else if ( cmd.equals( "STAT" ) )
				{
					if ( myAsset != null )
					{
						myAsset.status = data;
						
						sendMessage( "Status Change: " + data );
					}
				}
				else if ( cmd.equals( "HELO" ) ) // Device has a deviceId and would like to use it.
				{
					if ( myAsset == null )
						assetsHandler.reloadAsset( data );
					
					// Get ID from Asset Handler
					myAsset = assetsHandler.getAsset( data );
					
					// Does ID exist
					if ( myAsset != null )
					{
						deviceId = data;
						sendMessage( "UUID " + data );
						sendMessage( "INFO" );
						
						String loc = myAsset.getLocationJSON();
						if ( loc != null )
							sendMessage( "LOCI " + loc );
					}
					else
					{
						// TEMP OLD ID CONVERTER - BEGIN
						
						HashMap<String, Object> result = sql.selectOne( "assets", "uuid", data );
						
						if ( result == null )
						{
							// The deviceId you are using is invalid for one reason or other. Get a new one.
							sendMessage( "INVD" );
						}
						else
						{
							String dId = (String) result.get( "uuid" );
							
							// Create new device record
							sql.queryUpdate( "INSERT INTO `devices` ( `deviceId`, `lastIp`, `lastActive`, `created`, `serial`, `model`, `appVersion`, `status`, `type`, `locationId`, `state` ) VALUES ( '" + dId + "', 'unknown', '" + System.currentTimeMillis() + "', '" + System.currentTimeMillis() + "', '" + result.get( "serial_no" ) + "', '" + result.get( "itemID" ) + "', '" + result.get( "appVersion" ) + "', '" + result.get( "status" ) + "', 'rewards', '" + result.get( "locID" ) + "', 'This device has just registered its self with our servers.' );" );
							
							// Have asset loaded into memory
							assetsHandler.reloadAsset( dId );
							myAsset = assetsHandler.getAsset( dId );
							
							// Request that the device sends you information such as Serial, Model and App Version
							sendMessage( "INFO" );
							
							deviceId = dId;
							sendMessage( "UUID " + dId );
						}
						
						// TEMP OLD ID CONVERTER - END
					}
				}
				
				if ( myAsset != null )
					myAsset.lastActive = System.currentTimeMillis();
			}
			catch ( Throwable t )
			{
				t.printStackTrace();
			}
		}
	}
}
