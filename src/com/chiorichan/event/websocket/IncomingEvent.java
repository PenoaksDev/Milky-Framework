package com.chiorichan.event.websocket;

import com.chiorichan.event.Cancellable;
import com.chiorichan.event.Event;
import com.chiorichan.event.HandlerList;

public class IncomingEvent extends Event implements Cancellable
{
	Boolean cancelled = false;
	String response = null;
	
	public String getCommand()
	{
		return "";
	}
	
	public String[] getArguments()
	{
		return null;
	}
	
	public String getResponse()
	{
		return response;
	}
	
	public void setResponse( String msg )
	{
		response = msg;
	}
	
	@Override
	public HandlerList getHandlers()
	{
		return null;
	}

	@Override
	public boolean isCancelled()
	{
		return cancelled;
	}

	@Override
	public void setCancelled( boolean cancel )
	{
		cancelled = cancel;
	}
}
