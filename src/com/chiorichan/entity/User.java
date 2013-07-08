package com.chiorichan.entity;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Map;
import java.util.Set;

import com.chiorichan.ChioriFramework;
import com.chiorichan.command.CommandSender;
import com.chiorichan.configuration.serialization.ConfigurationSerializable;
import com.chiorichan.configuration.serialization.SerializableAs;
import com.chiorichan.event.entity.UserLoad;
import com.chiorichan.permissions.Permission;
import com.chiorichan.permissions.PermissionAttachment;
import com.chiorichan.permissions.PermissionAttachmentInfo;
import com.chiorichan.plugin.Plugin;
import com.chiorichan.plugin.messaging.PluginMessageRecipient;

@SerializableAs( "User" )
public class User extends BaseEntity implements CommandSender, PluginMessageRecipient, ConfigurationSerializable
{
	private boolean banned, whitelisted;
	private long lastRequest = 0, sessionExpiration = 0, firstJoined = 0, lastJoined = 0;
	private String username, userId, phone;
	
	public User( ResultSet rs ) throws SQLException
	{
		username = rs.getString( "username" );
		userId = rs.getString( "userID" );
		phone = rs.getString( "phone" );
		
		ChioriFramework.getServer().getPluginManager().callEvent( new UserLoad( this ) );
		
		// TODO: Add more fields.
	}

	@Override
	public Map<String, Object> serialize()
	{
		
		return null;
	}
	
	@Override
	public boolean isOnline()
	{
		// Was active in the last 5 minutes.
		return ( lastRequest - System.currentTimeMillis() < 300 );
	}
	
	@Override
	public String getName()
	{
		return username;
	}

	@Override
	public String getPhone()
	{
		// TODO Auto-generated method stub
		return phone;
	}

	@Override
	public String getUserId()
	{
		// TODO Auto-generated method stub
		return userId;
	}
	
	@Override
	public boolean isBanned()
	{
		return banned;
	}
	
	@Override
	public void setBanned( boolean banned )
	{
		this.banned = banned;
	}
	
	@Override
	public boolean isWhitelisted()
	{
		return whitelisted;
	}
	
	@Override
	public void setWhitelisted( boolean value )
	{
		whitelisted = value;
	}
	
	@Override
	public long getFirstJoined()
	{
		return firstJoined;
	}
	
	@Override
	public long getLastJoined()
	{
		return lastJoined;
	}
	
	@Override
	public boolean hasJoinedBefore()
	{
		return false;
	}

	@Override
	public boolean isPermissionSet( String name )
	{
		// TODO Auto-generated method stub
		return false;
	}

	@Override
	public boolean isPermissionSet( Permission perm )
	{
		// TODO Auto-generated method stub
		return false;
	}

	@Override
	public boolean hasPermission( String name )
	{
		// TODO Auto-generated method stub
		return false;
	}

	@Override
	public boolean hasPermission( Permission perm )
	{
		// TODO Auto-generated method stub
		return false;
	}

	@Override
	public PermissionAttachment addAttachment( Plugin plugin, String name, boolean value )
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public PermissionAttachment addAttachment( Plugin plugin )
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public PermissionAttachment addAttachment( Plugin plugin, String name, boolean value, int ticks )
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public PermissionAttachment addAttachment( Plugin plugin, int ticks )
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public void removeAttachment( PermissionAttachment attachment )
	{
		// TODO Auto-generated method stub
		
	}

	@Override
	public void recalculatePermissions()
	{
		// TODO Auto-generated method stub
		
	}

	@Override
	public Set<PermissionAttachmentInfo> getEffectivePermissions()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public boolean isOp()
	{
		// TODO Auto-generated method stub
		return false;
	}

	@Override
	public void setOp( boolean value )
	{
		// TODO Auto-generated method stub
		
	}

	@Override
	public void sendMessage( String message )
	{
		// TODO Auto-generated method stub
		
	}

	@Override
	public void sendMessage( String[] messages )
	{
		// TODO Auto-generated method stub
		
	}

	@Override
	public ChioriFramework getFramework()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public void sendPluginMessage( Plugin source, String channel, byte[] message )
	{
		// TODO Auto-generated method stub
		
	}

	@Override
	public Set<String> getListeningPluginChannels()
	{
		// TODO Auto-generated method stub
		return null;
	}

	public void kick( String reason )
	{
		// TODO Kick entity from the server
	}
}
