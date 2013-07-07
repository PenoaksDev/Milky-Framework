package com.chiorichan.plugin;

import com.chiorichan.event.EventHandler;
import com.chiorichan.event.EventPriority;
import com.chiorichan.event.websocket.IncomingEvent;

public class Builtin extends Plugin
{
	private long lastPing = 0;
	
	public void onEnable()
	{
		//this.registerEvents( this, this );
	}
	
	/**
	 * Handle the ping and pong messages that keep the websocket open and monitor latency. Ping comes from the client and
	 * Pong is sent as a response
	 */
	@EventHandler( priority = EventPriority.NORMAL )
	public void handlePing( IncomingEvent args )
	{
		if ( args.getCommand().equals( "ping" ) )
		{
			// db.queryUpdate( "INSERT INTO `devices_history` (`deviceId`, `action`, `latency`, `timestamp`) VALUES ('" +
			// deviceId + "', 'ping', '" + data + "', '" + System.currentTimeMillis() + "');" );
			lastPing = System.currentTimeMillis();
			
			args.setResponse( "pong" );
		}
		else if ( args.getCommand().equals( "pong" ) )
		{	
			
		}
	}

	@Override
	public void onDisable()
	{
		// TODO Auto-generated method stub
		
	}

	@Override
	public void onLoad()
	{
		// TODO Auto-generated method stub
		
	}
}
