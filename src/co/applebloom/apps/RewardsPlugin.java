package co.applebloom.apps;

import com.chiorichan.ChioriFramework;
import com.chiorichan.command.Command;
import com.chiorichan.command.CommandSender;
import com.chiorichan.event.EventHandler;
import com.chiorichan.event.EventPriority;
import com.chiorichan.event.Listener;
import com.chiorichan.event.websocket.IncomingEvent;
import com.chiorichan.plugin.Plugin;

public class RewardsPlugin extends Plugin implements Listener
{
	@Override
	public void onDisable()
	{
		
	}
	
	@Override
	public void onLoad()
	{
		
	}
	
	@Override
	public void onEnable()
	{
		// ChioriFramework.getEntityManager().addEntity( new DeviceEntity() );
		
		ChioriFramework.registerEvents( this );
		
		ChioriFramework.getServer().getPluginManager().registerEvents( this, this );
	}
	
	@Override
	public boolean onCommand( CommandSender sender, Command command, String label, String[] args )
	{
		
		return false;
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
	
	private long lastPing = 0;
}
