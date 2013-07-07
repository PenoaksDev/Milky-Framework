package com.chiorichan.command.defaults;

import java.util.List;

import org.apache.commons.lang3.StringUtils;
import org.apache.commons.lang3.Validate;

import com.chiorichan.ChioriFramework;
import com.chiorichan.command.Command;
import com.chiorichan.command.CommandSender;
import com.chiorichan.entity.User;
import com.google.common.collect.ImmutableList;

public class StopCommand extends VanillaCommand
{
	public StopCommand()
	{
		super( "stop" );
		this.description = "Stops the server with optional reason";
		this.usageMessage = "/stop [reason]";
		this.setPermission( "bukkit.command.stop" );
	}
	
	@Override
	public boolean execute( CommandSender sender, String currentAlias, String[] args )
	{
		if ( !testPermission( sender ) )
			return true;
		
		Command.broadcastCommandMessage( sender, "Server shutting down.." );
		ChioriFramework.shutdown();
		
		String reason = this.createString( args, 0 );
		if ( StringUtils.isNotEmpty( reason ) )
		{
			for ( User user : ChioriFramework.getOnlineUsers() )
			{
				user.kick( reason );
			}
		}
		
		return true;
	}
	
	@Override
	public List<String> tabComplete( CommandSender sender, String alias, String[] args ) throws IllegalArgumentException
	{
		Validate.notNull( sender, "Sender cannot be null" );
		Validate.notNull( args, "Arguments cannot be null" );
		Validate.notNull( alias, "Alias cannot be null" );
		
		return ImmutableList.of();
	}
}
