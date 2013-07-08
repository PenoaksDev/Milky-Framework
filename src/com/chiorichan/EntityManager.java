package com.chiorichan;

import java.awt.List;
import java.io.File;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.HashSet;
import java.util.Set;
import java.util.concurrent.CopyOnWriteArrayList;

import com.chiorichan.entity.BaseEntity;
import com.chiorichan.entity.User;

public class EntityManager
{
	//private static ArrayList<BaseEntity> entities = new ArrayList<BaseEntity>();
	public final CopyOnWriteArrayList<BaseEntity> entities = new java.util.concurrent.CopyOnWriteArrayList<BaseEntity>();
	private static final SimpleDateFormat d = new SimpleDateFormat("yyyy-MM-dd \'at\' HH:mm:ss z");
   private final ChioriFramework server;
   //private final BanList banByName = new BanList( ChioriFramework.getDatabase().query( "SELECT * FROM ``" ) );
   //private final BanList banByIP = new BanList( ChioriFramework.getDatabase().query( "" ) );
   
   private Set<BaseEntity> operators = new HashSet<BaseEntity>();
   private Set<BaseEntity> whitelist = new java.util.LinkedHashSet<BaseEntity>();
   public boolean hasWhitelist;
   protected int maxEntities;
   protected int c;
   private boolean m;
   private int n;
	
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
