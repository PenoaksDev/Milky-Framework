package com.chiorichan;

import java.io.BufferedReader;
import java.io.DataOutputStream;
import java.io.InputStreamReader;
import java.io.UnsupportedEncodingException;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import org.json.JSONObject;

import vnet.java.util.MySQLUtils;

public class SMSHandler
{
	private static String API_URL_BASE = "http://java1.api2.textmarks.com";
	private static String sms_ApiKey = "applebloom_co__38ca3c0d";
	private static String sms_AuthUser = "chiorigreene";
	private static String sms_AuthPass = "**********";
	public static HashMap<String, List<String>> smsq = new HashMap<String, List<String>>();
	
	public static String lastSMS = "N/A", lastNo = "N/A", lastResult = "N/A";
	public static long lastCycle = 0;
	public static long completed = 0;
	
	public static void addSMS( String mobile_no, String msg, String tm )
	{
		if ( smsq.get( mobile_no ) != null && smsq.get( mobile_no ).equals( msg ) )
			return;
		
		List<String> ext = new ArrayList<String>();
		ext.add( msg );
		ext.add( tm );
		
		smsq.put( mobile_no, ext );
		
		InteractiveConsole.info( "Q'ed message for broadcast..." );
	}
	
	public static void processSMSQ() throws Exception
	{
		SqlConnector db = APIServlet.getDatabase();
		
		if ( smsq.size() < 1 )
			return;
		
		int l = 0;
		
		for ( String mobile_no : smsq.keySet() )
		{
			l++;
			String msg = smsq.get( mobile_no ).get( 0 ); // Message
			String tm = smsq.get( mobile_no ).get( 1 ); // Keyword
			
			if ( !msg.equals( "" ) && !mobile_no.equals( "" ) )
			{
				InteractiveConsole.info( "&4Sending SMS \"" + msg + "\" to \"" + mobile_no + "\"" );
				
				HashMap<String, String> result = sendSMS( Arrays.asList( mobile_no ), msg, tm );
				
				int success = 0;
				if ( result.get( "status" ).equalsIgnoreCase( "success" ) )
					success = 1;
				
				//db.update( "sms_translog", Arrays.asList( "pending", "success", "debug" ), Arrays.asList( "0", success, MySQLUtils.mysql_real_escape_string( db.con, "HTTP_GET: " + result.get( "url" ) + "\n" + result.get( "debug" ) + "\n" + result.get( "data" ) ) ), Arrays.asList( "mobile_no", "created" ), Arrays.asList( sms.get( "mobile_no" ), sms.get( "created" ) ) );
				
				try
				{
					db.queryUpdate( "INSERT INTO `sms_translog` (`mobile_no`, `origin`, `class`, `operator`, `msg`, `created`, `pending`, `success`, `debug`) VALUES ('" + mobile_no + "', 'API_OUTGOING', '(webform)', 'UNKNOWN', '" + msg + "', '" + result.get("created") + "', '0', '" + success + "', '" + MySQLUtils.mysql_real_escape_string( db.con, result.get( "resmsg" ) ) + "');" );
				}
				catch ( Exception e ) 
				{
					e.printStackTrace();
				}
				
				lastSMS = msg;
				lastNo = mobile_no;
				lastResult = result.get( "resmsg" );
				lastCycle = System.currentTimeMillis();
				
				if ( result.get( "rescode" ) != "1503" ) // Is provider throttling.
				{
					smsq.remove( mobile_no );
					completed++;
				}
				
				if ( l >= 10 )
					break;
			}
		}
	}
	
	public static HashMap<String, String> inviteMobile( List<String> mobileNo, String tm )
	{
		return inviteMobile(mobileNo, tm, null);
	}
	
	public static HashMap<String, String> inviteMobile( List<String> mobileNo, String tm, String msg )
	{
		//SqlConnector db = APIServlet.getDatabase();
		
		HashMap<String, String> result = new HashMap<String, String>();
		
		result.put( "data", "" );
		result.put( "url", "" );
		result.put( "status", "FAILED" );
		result.put( "debug", "The sending of the SMS failed for an unknown reason." );
		result.put( "created", "" + System.currentTimeMillis() );
		
		if ( tm == null || tm.equals( "" ) )
		{
			System.err.print( "[SMS] Target Keyword can not be null!\n" );
			return result;
		}
		
		if ( mobileNo.size() < 1 )
		{
			System.err.print( "[SMS] The list of mobile numbers can not be empty!\n" );
			return result;
		}
		
		for ( String p : mobileNo )
		{
			if ( p == null || p.length() != 10 )
			{
				System.err.print( "[SMS] " + p + " is a malformed mobile number!\n" );
				result.put( "debug", p + " is a malformed mobile number!" );
				break;
			}
			
			try
			{
				HashMap<String, Object> requestParams = new HashMap<String, Object>();
				requestParams.put( "api_key", sms_ApiKey );
				requestParams.put( "auth_user", sms_AuthUser );
				requestParams.put( "auth_pass", sms_AuthPass );
				
				String sUrl = API_URL_BASE + "/Anybody/invite_to_group/";
				
				result.put( "target", tm );
				result.put( "mobile", p );
				
				requestParams.put( "tm", tm );
				requestParams.put( "user", p );
				
				//requestParams.put( "why", "" );
				//requestParams.put( "details", "" );
				
				if ( msg != null )
					requestParams.put( "desc", msg );
				
				String resp = call( sUrl, requestParams );
				
				JSONObject jResp = new JSONObject( resp );
				
				result.put( "debug", p + " was invited to group " + tm + "!" );
				result.put( "status", "SUCCESS" );
				
				result.put( "rescode", "" + jResp.getJSONObject( "head" ).getInt( "rescode" ) );
				result.put( "resmsg", jResp.getJSONObject( "head" ).getString( "resmsg" ) );
				
				System.out.print( p + " was invited to group " + tm + " which returned result \"" + resp + "\"!\n" );
			}
			catch ( Exception e )
			{
				e.printStackTrace();
				result.put( "debug", p + " receive an unexpected response from the Texting Servers!" );
			}
		}
		
		return result;
	}
	
	public static HashMap<String, String> sendSMS( List<String> mobileNo, String msg, String tm )
	{
		//SqlConnector db = APIServlet.getDatabase();
		
		if ( tm == null || tm.equals( "" ) )
			tm = "donut";
		
		HashMap<String, String> result = new HashMap<String, String>();
		
		result.put( "data", "" );
		result.put( "url", "" );
		result.put( "status", "FAILED" );
		result.put( "debug", "The sending of the SMS failed for an unknown reason." );
		result.put( "created", "" + System.currentTimeMillis() );
		
		if ( mobileNo.size() < 1 )
		{
			System.err.print( "[SMS] The list of mobile numbers can not be empty!\n" );
			return result;
		}
		
		List<String> msgs = new ArrayList<String>();
		
		do
		{
			if ( msg.length() > 160 )
			{
				msgs.add( msg.substring( 0, 160 ) );
				msg = msg.substring( 160 );
			}
			else
			{
				msgs.add( msg );
				msg = "";
			}
		}
		while ( msg.length() > 0 );
		
		for ( String m : msgs )
		{
			for ( String p : mobileNo )
			{
				if ( p == null || p.length() != 10 )
				{
					System.err.print( "[SMS] " + p + " is a malformed mobile number!\n" );
					result.put( "debug", p + " is a malformed mobile number!" );
					break;
				}
				
				try
				{
					HashMap<String, Object> requestParams = new HashMap<String, Object>();
					requestParams.put( "api_key", sms_ApiKey );
					requestParams.put( "auth_user", sms_AuthUser );
					requestParams.put( "auth_pass", sms_AuthPass );
					
					// Remove unsupported characters in messages.
					m = m.replaceAll( "[^A-Za-z0-9@$_\\/.,\"():;\\-=+&%#!?<>\' \\n]", "" );
					
					String sUrl = API_URL_BASE + "/GroupLeader/send_one_message/";
					
					requestParams.put( "tm", tm );
					requestParams.put( "msg", m );
					requestParams.put( "to", p );
					
					String resp = call( sUrl, requestParams );
					
					JSONObject jResp = new JSONObject( resp );
					
					result.put( "debug", "\"" + m + "\" was successfully sent to the Texting Servers!" );
					result.put( "status", "SUCCESS" );
					
					result.put( "rescode", "" + jResp.getJSONObject( "head" ).getInt( "rescode" ) );
					result.put( "resmsg", jResp.getJSONObject( "head" ).getString( "resmsg" ) );
					
					System.out.print( "Sent SMS to mobile # \"" + p + "\" with message body \"" + m + "\" which returned result \"" + resp + "\"!\n" );
				}
				catch ( Exception e )
				{
					e.printStackTrace();
					result.put( "debug", p + " receive an unexpected response from the Texting Servers!" );
				}
			}
		}
		
		return result;
	}
	
	public static String call( String sUrl, Map<String, Object> msoParams ) throws Exception
	{
		String sPostData = "";
		try
		{
			StringBuffer sbPostData = new StringBuffer();
			for ( Map.Entry<String, Object> entry : msoParams.entrySet() )
			{
				sbPostData.append( "&" ).append( URLEncoder.encode( entry.getKey(), "UTF-8" ) );
				sbPostData.append( "=" ).append( URLEncoder.encode( String.valueOf( entry.getValue() ), "UTF-8" ) );
			}
			sPostData = sbPostData.toString();
		}
		catch ( UnsupportedEncodingException e )
		{}
		
		HttpURLConnection urlConnection = null;
		try
		{
			URL url = new URL( sUrl );
			urlConnection = (HttpURLConnection) url.openConnection();
			( urlConnection ).setRequestMethod( "POST" );
			urlConnection.setDoInput( true );
			urlConnection.setDoOutput( true );
			urlConnection.setUseCaches( false );
			urlConnection.setRequestProperty( "Content-Type", "application/x-www-form-urlencoded" );
			urlConnection.setRequestProperty( "Content-Length", "" + sPostData.length() );
		}
		catch ( Exception e )
		{
			throw new Exception( sUrl + " saw HTTP connection error: " + e.toString(), e );
		}
		
		try
		{
			DataOutputStream out = new DataOutputStream( urlConnection.getOutputStream() );
			out.writeBytes( sPostData );
			out.close();
		}
		catch ( Exception e )
		{
			throw new Exception( sUrl + " saw HTTP request error: " + e.toString(), e );
		}
		
		try
		{
			BufferedReader in = new BufferedReader( new InputStreamReader( urlConnection.getInputStream() ) );
			StringBuffer sbResponse = new StringBuffer();
			String sBuf;
			while ( ( sBuf = in.readLine() ) != null )
			{
				sbResponse.append( sBuf );
			}
			in.close();
			return sbResponse.toString();
		}
		catch ( Exception e )
		{
			throw new Exception( sUrl + " saw HTTP response error: " + e.toString(), e );
		}
	}
}
