package com.chiorichan.entity;

import java.util.Set;

import com.chiorichan.ChioriFramework;
import com.chiorichan.command.CommandSender;
import com.chiorichan.permissions.Permission;
import com.chiorichan.permissions.PermissionAttachment;
import com.chiorichan.permissions.PermissionAttachmentInfo;
import com.chiorichan.plugin.Plugin;
import com.chiorichan.plugin.messaging.PluginMessageRecipient;

public class User extends BaseEntity implements CommandSender, PluginMessageRecipient
{
	
	@Override
	public boolean isOnline()
	{
		// TODO Auto-generated method stub
		return false;
	}
	
	@Override
	public String getName()
	{
		// TODO Auto-generated method stub
		return null;
	}
	
	@Override
	public boolean isBanned()
	{
		// TODO Auto-generated method stub
		return false;
	}
	
	@Override
	public void setBanned( boolean banned )
	{
		// TODO Auto-generated method stub
		
	}
	
	@Override
	public boolean isWhitelisted()
	{
		// TODO Auto-generated method stub
		return false;
	}
	
	@Override
	public void setWhitelisted( boolean value )
	{
		// TODO Auto-generated method stub
		
	}
	
	@Override
	public long getFirstJoined()
	{
		// TODO Auto-generated method stub
		return 0;
	}
	
	@Override
	public long getLastJoined()
	{
		// TODO Auto-generated method stub
		return 0;
	}
	
	@Override
	public boolean hasJoinedBefore()
	{
		// TODO Auto-generated method stub
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
