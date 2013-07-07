package co.applebloom.apps.rewards;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Arrays;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;
import java.util.logging.Level;

import org.json.JSONException;
import org.json.JSONObject;

import com.chiorichan.InteractiveConsole;

import co.applebloom.api.Main;

public class Asset
{
	public String deviceId, lastIp, serial, model, appVersion, status, type,
			locationId, state;
	public Long lastActive, created;
	public Boolean assigned = false;
	
	public Asset(HashMap<String, Object> result)
	{
		try
		{
			if ( result == null )
				return;
			
			deviceId = (String) result.get( "deviceId" );
			lastIp = (String) result.get( "lastIp" );
			serial = (String) result.get( "serial" );
			model = (String) result.get( "model" );
			appVersion = (String) result.get( "appVersion" );
			status = (String) result.get( "status" );
			type = (String) result.get( "type" );
			locationId = (String) result.get( "locationId" );
			state = (String) result.get( "state" );
			assigned = (Boolean) result.get( "assigned" );
			
			lastActive = Long.parseLong( (String) result.get( "lastActive" ) );
			created = Long.parseLong( (String) result.get( "created" ) );
		}
		catch ( Exception e )
		{
			// Most likely a CAST exception will appear here
			e.printStackTrace();
		}
	}
	
	public Asset(ResultSet rs)
	{
		try
		{
			if ( rs == null || rs.isAfterLast() )
				return;
			
			deviceId = rs.getString( "deviceId" );
			lastIp = rs.getString( "lastIp" );
			serial = rs.getString( "serial" );
			model = rs.getString( "model" );
			appVersion = rs.getString( "appVersion" );
			status = rs.getString( "status" );
			type = rs.getString( "type" );
			locationId = rs.getString( "locationId" );
			state = rs.getString( "state" );
			assigned = rs.getBoolean( "status" );
			
			lastActive = rs.getLong( "lastActive" );
			created = rs.getLong( "created" );
		}
		catch ( SQLException e )
		{
			e.printStackTrace();
			return;
		}
	}
	
	public HashMap<String, String> getLocationArray()
	{
		String locId = "null";
		String title = "Apple Bloom Rewards";
		String address1 = "";
		String address2 = "";
		String header = "";
		Boolean assigned = false;
		
		if ( locationId != null )
		{
			ResultSet rs = Main.getDatabase().query( "SELECT * FROM `locations` WHERE `locID` = '" + locationId + "';" );
			
			if ( Main.getDatabase().getRowCount( rs ) > 0 )
			{
				try
				{
					locId = rs.getString( "locID" );
					title = rs.getString( "title" );
					address1 = rs.getString( "address1" ) + ", " + rs.getString( "address2" );
					address2 = rs.getString( "city" ) + ", " + rs.getString( "state" ) + " " + rs.getString( "zipcode" );
					
					ResultSet rs1 = Main.getDatabase().query( "SELECT * FROM `franchises` WHERE `franID` = '" + rs.getString( "franID" ) + "';" );
					if ( Main.getDatabase().getRowCount( rs1 ) > 0 )
						header = rs1.getString( "Img" );
				}
				catch ( SQLException e )
				{
					e.printStackTrace();
				}
				
				assigned = true;
			}
		}
		
		HashMap<String, String> hs = new HashMap<String, String>();
		
		if ( !assigned )
			title = "No Location Assigned!";
		
		hs.put( "locId", locId );
		hs.put( "title", title );
		hs.put( "address1", address1 );
		hs.put( "address2", address2 );
		hs.put( "header", header );
		hs.put( "assigned", assigned.toString() );
		
		return hs;
	}
	
	public String getLocationJSON()
	{
		String locId = "null";
		String title = "Apple Bloom Rewards";
		String address1 = "";
		String address2 = "";
		String header = "";
		
		if ( locationId != null )
		{
			ResultSet rs = Main.getDatabase().query( "SELECT * FROM `locations` WHERE `locID` = '" + locationId + "';" );
			
			if ( Main.getDatabase().getRowCount( rs ) > 0 )
			{
				try
				{
					locId = rs.getString( "locID" );
					title = rs.getString( "title" );
					address1 = rs.getString( "address1" ) + ", " + rs.getString( "address2" );
					address2 = rs.getString( "city" ) + ", " + rs.getString( "state" ) + " " + rs.getString( "zipcode" );
					
					ResultSet rs1 = Main.getDatabase().query( "SELECT * FROM `franchises` WHERE `franID` = '" + rs.getString( "franID" ) + "';" );
					if ( Main.getDatabase().getRowCount( rs1 ) > 0 )
						header = rs1.getString( "Img" );
				}
				catch ( SQLException e )
				{
					e.printStackTrace();
				}
				
				assigned = true;
			}
		}
		
		try
		{
			JSONObject jsn = new JSONObject();
			jsn.put( "locId", locId );
			jsn.put( "title", title );
			jsn.put( "address1", address1 );
			jsn.put( "address2", address2 );
			jsn.put( "header", header );
			jsn.put( "assigned", assigned );
			
			return jsn.toString();
		}
		catch ( JSONException e )
		{
			e.printStackTrace();
			return null;
		}
	}
	
	@Override
	public String toString()
	{
		String locId = "null";
		String title = "Apple Bloom Rewards";
		String address1 = "";
		String address2 = "";
		
		if ( locationId != null || !locationId.isEmpty() )
		{
			ResultSet rs = Main.getDatabase().query( "SELECT * FROM `locations` WHERE `locID` = '" + locationId + "';" );
			
			if ( Main.getDatabase().getRowCount( rs ) > 0 )
			{
				try
				{
					locId = rs.getString( "locID" );
					title = rs.getString( "title" );
					address1 = rs.getString( "address1" ) + " " + rs.getString( "address2" );
					address2 = rs.getString( "city" ) + ", " + rs.getString( "state" ) + " " + rs.getString( "zipcode" );
				}
				catch ( SQLException e )
				{
					e.printStackTrace();
				}
			}
		}
		
		return "Assigned: " + assigned + ", Device UUID: " + deviceId + ", Location Id: " + locId + ", Title: " + title + ", Address: " + address1 + ", " + address2;
	}
	
	public void fullSave()
	{
		if ( deviceId == null )
			return;
		
		HashMap<String, Object> result = Main.getDatabase().selectOne( "devices", Arrays.asList( "deviceId" ), Arrays.asList( deviceId ) );
		
		if ( result.size() < 1 )
		{
			Main.getDatabase().queryUpdate( "INSERT INTO `devices` ( `deviceId`, `lastIp`, `lastActive`, `created`, `status`, `type` ) VALUES ( '" + deviceId + "', '" + lastIp + "', '" + System.currentTimeMillis() + "', '" + System.currentTimeMillis() + "', '" + assigned + "', 'rewards' );" );
		}

		Main.getDatabase().queryUpdate( "UPDATE `devices` SET `deviceId` = '" + deviceId + "', `lastIp` = '" + lastIp + "', `serial` = '" + serial + "', `model` = '" + model + "', `lastActive` = '" + lastActive + "', `appVersion` = '" + appVersion + "', `state` = '" + state + "' WHERE `deviceId` = '" + deviceId + "';" );
	}
	
	public void updateInfo( String json )
	{
		updateInfo( json, null );
	}
	
	public void updateInfo( String json, String ip )
	{
		if ( json.equals( "" ) )
			json = "{}";
		
		if ( deviceId == null )
			return;
		
		try
		{
			JSONObject dada = new JSONObject( json );
			
			if ( ip != null && ip != "0.0.0.0" )
				lastIp = ip;
			
			// TODO: Improve this error handling
			lastActive = System.currentTimeMillis();
			appVersion = dada.getString( "appVersion" );
			state = dada.getString( "state" );
			serial = dada.getString( "serial" );
			model = dada.getString( "model" );
		}
		catch ( JSONException e )
		{
			// e.printStackTrace();
		}
		finally
		{
			Main.getDatabase().queryUpdate( "UPDATE `devices` SET `lastIp` = '" + lastIp + "', `serial` = '" + serial + "', `model` = '" + model + "', `lastActive` = '" + lastActive + "', `appVersion` = '" + appVersion + "', `state` = '" + state + "' WHERE `deviceId` = '" + deviceId + "';" );
		}
	}
	
	public Boolean isActive()
	{
		// Has been active in the last 60 seconds.
		if ( lastActive > System.currentTimeMillis() - 60000 )
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * This method will always return the following ArrayList format
	 * 
	 * @return List<valid, errorId, reason>
	 */
	public List<? extends Object> isValid()
	{
		if ( deviceId == null || deviceId.equals( "" ) )
			return Arrays.asList( false, 101, "This asset is missing a valid DeviceId!" );
		
		return Arrays.asList( true, 0, "This asset has been verified as valid." );
	}
	
	public Boolean isComplete()
	{
		if ( locationId == null || locationId.equals( "" ) )
		{
			return false;
		}
		
		return true;
	}
}
