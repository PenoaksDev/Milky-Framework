package com.chiorichan;

import java.sql.ResultSet;
import java.util.Arrays;
import java.util.HashMap;
import java.util.Timer;
import java.util.TimerTask;

import vnet.java.util.MySQLUtils;

public class ServletHelper implements Runnable
{
	private static Timer timer1 = new Timer();
	private static Timer timer2 = null;
	public static Boolean cycleActive = false;
	public static long lastStart = 0;
	
	public void run()
	{
		timer1.scheduleAtFixedRate( new TimerTask()
		{
			@Override
			public void run()
			{
				try
				{
					SqlConnector db = Main.getDatabase();
					InteractiveConsole.info( "&1Parsing SMS Translog... OH YEAH!" );
					
					ResultSet rs = db.query( "SELECT * FROM `sms_translog` WHERE `pending` = '1';" );
					
					if ( db.getRowCount( rs ) > 0 )
					{
						do
						{
							// Is this an outgoing message?
							if ( !rs.getString( "origin" ).equalsIgnoreCase( "API_INCOMING" ) )
							{
								HashMap<String, String> row = new HashMap<String, String>();
								
								row.put( "mobile_no", rs.getString( "mobile_no" ) );
								row.put( "msg", rs.getString( "msg" ) );
								row.put( "created", rs.getString( "created" ) );
								
								//if ( rs.getString( "mobile_no" ).equals( "7089123702" ) )
									//SMSHandler.addSMS( row );
							}
						}
						while ( rs.next() );
					}
					
					timerCheck();
					
					db.queryUpdate( "DELETE FROM `devices_history` WHERE `timestamp` < '" + ( System.currentTimeMillis() - 604800000 ) + "';" );
				}
				catch ( Exception e )
				{
					e.printStackTrace();
				}
			}
		}, 10000L, 37000L );
	}
	
	public static void timerCheck ()
	{
		if ( SMSHandler.smsq.size() > 0 && timer2 == null )
		{
			SMSHandler.completed = 0;
			lastStart = System.currentTimeMillis();
			cycleActive = true;
			timer2 = new Timer();
			timer2.scheduleAtFixedRate( new TimerTask()
			{
				@Override
				public void run()
				{
					try
					{
						SMSHandler.processSMSQ();
					}
					catch ( Exception e )
					{
						e.printStackTrace();
					}
				}
			}, 0L, 5000L );
			
			InteractiveConsole.info( "&5The SMS Q scheduler has been started!" );
		}
		else if ( SMSHandler.smsq.size() < 1 && timer2 != null )
		{
			timer2.cancel();
			timer2 = null;
			SMSHandler.completed = 0;
			cycleActive = false;
			
			InteractiveConsole.info( "&5The SMS Q scheduler has been suspended!" );
		}
	}
}
