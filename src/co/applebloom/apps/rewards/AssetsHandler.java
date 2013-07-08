package co.applebloom.apps.rewards;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.logging.Level;

import com.chiorichan.ChioriFramework;
import com.chiorichan.InteractiveConsole;
import com.chiorichan.OurWebsocketServlet;
import com.chiorichan.OurWebsocketServlet.RewardsWebSocket;
import com.chiorichan.SqlConnector;

public class AssetsHandler
{
	private static ArrayList<Asset> ass = new ArrayList<Asset>();
	private static SqlConnector sql = null;
	private Boolean stillLoading = false;
	
	public AssetsHandler()
	{
		sql = ChioriFramework.getDatabase();
	}
	
	public void loadAssets()
	{
		if ( ass.size() > 0 )
			return;
		
		stillLoading = true;
		
		ResultSet rs = sql.query( "SELECT * FROM devices;" );
		
		if ( sql.getRowCount( rs ) > 0 )
		{
			try
			{
				do
				{
					ass.add( new Asset( rs ) );
				}
				while ( rs.next() );
			}
			catch ( SQLException e )
			{
				e.printStackTrace();
			}
		}
		
		stillLoading = false;
	}
	
	public void saveAll()
	{
		for ( Asset a : ass )
		{
			if ( a != null )
			{
				a.fullSave();
			}
		}
	}
	
	public void reloadAll()
	{
		try
		{
			InteractiveConsole.log( Level.INFO, "&4Reloading assets from database!" );
			
			ass = new ArrayList<Asset>();
			loadAssets();
			OurWebsocketServlet.scanAssets();
			
			for ( RewardsWebSocket rws : OurWebsocketServlet._members )
			{
				String loc = rws.myAsset.getLocationJSON();
				if ( loc != null )
					rws.sendMessage( "LOCI " + loc );
			}
		}
		catch ( Exception e )
		{	
			e.printStackTrace();
		}
	}
	
	public void reloadAsset( String deviceId )
	{
		// Make query
		HashMap<String, Object> result = sql.selectOne( "devices", Arrays.asList( "deviceId" ), Arrays.asList( deviceId ) );
		
		// If query retuns empty then cancel
		if ( result == null )
			return;
		
		// Remove asset from memory
		unloadAsset( deviceId );
		
		ass.add( new Asset( result ) );
	}
	
	public Boolean assetExists( String deviceId )
	{
		if ( deviceId == null || deviceId.equals( "" ) )
			return false;
		
		for ( Asset a : ass )
		{
			if ( a.deviceId != null && a.deviceId.equals( deviceId ) )
				return true;
		}
		
		return false;
	}
	
	public void unloadAsset( String deviceId )
	{
		if ( deviceId == null || deviceId.equals( "" ) )
			return;
		
		for ( Asset a : ass )
		{
			if ( a.deviceId != null && a.deviceId.equals( deviceId ) )
			{
				ass.remove( a );
				break;
			}
		}
	}
	
	public Asset search( String query )
	{
		if ( query == null || query.equals( "" ) )
			return null;
		
		for ( Asset a : ass )
		{
			if ( a.deviceId != null && ( a.deviceId.startsWith( query ) || a.lastIp.startsWith( query ) ) )
			{
				return a;
			}
		}
		
		return null;
	}
	
	public Asset getAsset( String deviceId )
	{
		if ( deviceId == null || deviceId.equals( "" ) )
			return null;
		
		if ( ass.size() < 1 )
			loadAssets();
		
		long st = System.currentTimeMillis();
		while (stillLoading) { if ( System.currentTimeMillis() - st > 1500 ) break; };
		
		for ( Asset a : ass )
		{
			if ( a.deviceId != null && a.deviceId.equals( deviceId ) )
				return a;
		}
		
		return null;
	}
}
