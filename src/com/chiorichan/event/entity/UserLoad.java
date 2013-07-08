package com.chiorichan.event.entity;

import com.chiorichan.entity.User;
import com.chiorichan.event.Event;
import com.chiorichan.event.HandlerList;

public class UserLoad extends Event
{
	private static HandlerList handlers = new HandlerList();
	private User user;
	
	@Override
	public HandlerList getHandlers()
	{
		return handlers;
	}
	
	public UserLoad ( User usr )
	{
		setUser( usr );
	}
	
	public void setUser ( User usr )
	{
		user = usr;
	}
	
	public User getUser()
	{
		return user;
	}
}
