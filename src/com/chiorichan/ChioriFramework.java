/*******************************************************************************
 * Copyright 20013 Greenetree LLC or its affiliates. All Rights Reserved. Licensed under the Apache License, Version 2.0
 * (the "License");
 * 
 * You may not use this file except in compliance with the License. You may obtain a copy of the License at:
 * http://aws.amazon.com/apache2.0 This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied. See the License for the specific language governing permissions and limitations
 * under the License. *****************************************************************************
 * 
 * @author Chiori Greene
 * @email chiorigreene@gmail.com
 * @copyright Debnroo.com (2013)
 * @date February 1st, 2013
 * 
 */
package com.chiorichan;

import java.io.File;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.logging.Logger;

import org.eclipse.jetty.server.Server;
import org.eclipse.jetty.server.handler.HandlerList;
import org.eclipse.jetty.server.handler.ResourceHandler;
import org.eclipse.jetty.servlet.ServletHandler;
import org.eclipse.jetty.servlet.ServletHolder;
import org.eclipse.jetty.servlet.ServletMapping;

import com.chiorichan.Warning.WarningState;
import com.chiorichan.command.CommandException;
import com.chiorichan.command.CommandSender;
import com.chiorichan.command.ConsoleCommandSender;
import com.chiorichan.command.PluginCommand;
import com.chiorichan.command.SimpleCommandMap;
import com.chiorichan.entity.User;
import com.chiorichan.help.HelpMap;
import com.chiorichan.plugin.Plugin;
import com.chiorichan.plugin.PluginManager;
import com.chiorichan.plugin.ServicesManager;
import com.chiorichan.plugin.SimplePluginManager;
import com.chiorichan.plugin.messaging.Messenger;
import com.chiorichan.scheduler.FrameworkScheduler;

public class ChioriFramework implements IFramework
{
	public static InteractiveConsole console = new InteractiveConsole();
	public static SqlConnector sql = new SqlConnector( "chiori", "chiori", "*****", "50.79.49.250" );
	public static ServletHelper t = new ServletHelper();
	public static ChioriFramework instance = null;
	
	public ChioriFramework()
	{
		instance = this;
		pluginManager = new SimplePluginManager( this, new SimpleCommandMap( this ) );
		//scheduler = new FrameworkScheduler();
		entityManager = new EntityManager();
	}
	
	private static Server server = null;
	
	private static final HandlerList mainHandler = new HandlerList();
	private static ServletHandler servletHandler = new ServletHandler();
	private static int port = 8080;
	private static final List<ServletHolder> holders = new ArrayList<ServletHolder>();
	private static final List<ServletMapping> mappings = new ArrayList<ServletMapping>();
	
	public static void main( String... arg ) throws Exception
	{
		try
		{
			new ChioriFramework();
			
			server = new Server( arg.length > 1 ? Integer.parseInt( arg[1] ) : port );
			
			String webDir = ChioriFramework.class.getClassLoader().getResource( "webroot" ).toExternalForm();
			
			ResourceHandler resourceHandler = new ResourceHandler();
			resourceHandler.setDirectoriesListed( true );
			resourceHandler.setWelcomeFiles( new String[] { "index.html" } );
			resourceHandler.setResourceBase( webDir );
			resourceHandler.setStylesheet( "/css/default.css" );
			
			mainHandler.addHandler( resourceHandler );
			mainHandler.addHandler( servletHandler );
			server.setHandler( mainHandler );
			
			instance.getPluginManager().loadPlugins( new File("./plugins") );
			
			// Plugins onLoad
			
			ChioriFramework.registerServlet( OurWebsocketServlet.class, "/websocket" );
			ChioriFramework.registerServlet( APIServlet.class, "/current.jsp" );
			ChioriFramework.registerServlet( SystemStatus.class, "/status.html" );
			ChioriFramework.registerServlet( SendSMS.class, "/sendSMS.jsp" );
			ChioriFramework.registerServlet( StatusSMS.class, "/statusSMS.jsp" );
			ChioriFramework.registerServlet( serviceCheck.class, "/serviceCheck.html" );
			
			// Plugins onEnable
			
			ChioriFramework.start();
			
			
			
			
		}
		catch ( Exception e )
		{
			e.printStackTrace();
			throw e;
		}
		
		t.run();
	}
	
	public static void start() throws Exception
	{
		servletHandler.setServlets( holders.toArray( new ServletHolder[0] ) );
		servletHandler.setServletMappings( mappings.toArray( new ServletMapping[0] ) );
		server.start();
	}
	
	public static void registerServlet( Class servlet, String path )
	{
		ServletHolder holder = new ServletHolder();
		holder.setName( servlet.getName() );
		holder.setClassName( servlet.getName() );
		holders.add( holder );
		ServletMapping mapping = new ServletMapping();
		mapping.setPathSpec( path );
		mapping.setServletName( servlet.getName() );
		mappings.add( mapping );
	}

	public static SqlConnector getDatabase ()
	{
		return sql;
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

	@Override
	public String getName()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public String getVersion()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public String getFrameworkVersion()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public User[] getUsers()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public int getMaxUsers()
	{
		// TODO Auto-generated method stub
		return 0;
	}

	@Override
	public int getPort()
	{
		// TODO Auto-generated method stub
		return 0;
	}

	@Override
	public String getIp()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public String getServerName()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public String getServerId()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public boolean hasWhitelist()
	{
		// TODO Auto-generated method stub
		return false;
	}

	@Override
	public void setWhitelist( boolean value )
	{
		// TODO Auto-generated method stub
		
	}

	@Override
	public Set<User> getWhitelistedUsers()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public void reloadWhitelist()
	{
		// TODO Auto-generated method stub
		
	}

	@Override
	public int broadcastMessage( String message )
	{
		// TODO Auto-generated method stub
		return 0;
	}

	@Override
	public String getUpdateFolder()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public File getUpdateFolderFile()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public long getConnectionThrottle()
	{
		// TODO Auto-generated method stub
		return 0;
	}

	@Override
	public User getUserExact( String name )
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public List<User> matchUser( String name )
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public ServicesManager getServicesManager()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public boolean unloadWorld( String name, boolean save )
	{
		// TODO Auto-generated method stub
		return false;
	}

	@Override
	public void reload()
	{
		// TODO Auto-generated method stub
		
	}

	@Override
	public PluginCommand getPluginCommand( String name )
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public void saveUsers()
	{
		// TODO Auto-generated method stub
		
	}

	@Override
	public boolean dispatchCommand( CommandSender sender, String commandLine ) throws CommandException
	{
		// TODO Auto-generated method stub
		return false;
	}

	@Override
	public void configureDbConfig( ServerConfig config )
	{
		// TODO Auto-generated method stub
		
	}

	@Override
	public Map<String, String[]> getCommandAliases()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public void shutdown()
	{
		// TODO Auto-generated method stub
		
	}

	@Override
	public int broadcast( String message, String permission )
	{
		// TODO Auto-generated method stub
		return 0;
	}

	@Override
	public User getUser( String name )
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public Set<String> getIPBans()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public void banIP( String address )
	{
		// TODO Auto-generated method stub
		
	}

	@Override
	public void unbanIP( String address )
	{
		// TODO Auto-generated method stub
		
	}

	@Override
	public Set<User> getBannedUsers()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public Set<User> getOperators()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public ConsoleCommandSender getConsoleSender()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public Messenger getMessenger()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public HelpMap getHelpMap()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public String getMotd()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public String getShutdownMessage()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public WarningState getWarningState()
	{
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public EntityManager getEntityManager()
	{
		return entityManager;
	}

	@Override
	public FrameworkScheduler getScheduler()
	{
		return scheduler;
	}

	@Override
	public PluginManager getPluginManager()
	{
		return pluginManager;
	}
	
	private PluginManager pluginManager;
	private FrameworkScheduler scheduler;
	private EntityManager entityManager;

	@Override
	public Logger getLogger()
	{
		return ConsoleLogManager.global;
	}
	
	public static ChioriFramework getServer()
	{
		return instance;
	}
	
	public static ChioriFramework getFramework()
	{
		return instance;
	}
}
