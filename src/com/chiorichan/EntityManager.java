package com.chiorichan;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.HashSet;
import java.util.Set;

import com.chiorichan.entity.BaseEntity;
import com.chiorichan.entity.User;

public class EntityManager
{
	private static ArrayList<BaseEntity> entities = new ArrayList<BaseEntity>();
	
	public void cleanUp()
	{
		// Flushes expired and unused entities.
	}
	
	public void addEntity( BaseEntity newEntity )
	{
		entities.add( newEntity );
	}
	
	public Set<BaseEntity> getEntities( Class<? extends BaseEntity> cls )
	{
		Set<BaseEntity> rtn = new HashSet<BaseEntity>();
		
		for ( BaseEntity be : entities )
		{
			if ( be != null && be.getClass().equals( cls ) )
			{
				rtn.add( be );
			}
		}
		
		return rtn;
	}
	
	public User getUser( String name )
	{
		// Find a user from the array or load it from the database.
		
		for ( BaseEntity be : entities )
		{
			if ( be != null && be instanceof User )
			{
				if ( be.getName().equalsIgnoreCase( name ) )
					return (User) be;
			}
		}
		
		SqlConnector db = ChioriFramework.getDatabase();
		
		ResultSet rs = db.query( "SELECT * FROM `users` WHERE `username` = '" + name + "' OR `userID` = '" + name + "' OR `phone` = '" + name + "' LIMIT 1;" );
		
		if ( db.getRowCount( rs ) > 0 )
		{
			try
			{
				User usr = new User( rs );
				entities.add( usr );
				return usr;
			}
			catch ( SQLException e )
			{
				e.printStackTrace();
			}
		}
		
		return null;
	}
}
