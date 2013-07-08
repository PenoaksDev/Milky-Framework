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
 * @copyright Greenetree LLC (d.b.a. Apple Bloom Company) (2013)
 * @date July 8th, 2013
 * 
 */
package com.chiorichan;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collections;
import java.util.HashSet;
import java.util.Iterator;
import java.util.LinkedHashMap;
import java.util.LinkedHashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.logging.Level;
import java.util.logging.Logger;

import jline.console.ConsoleReader;
import joptsimple.OptionParser;
import joptsimple.OptionSet;

import org.apache.commons.lang3.Validate;
import org.eclipse.jetty.server.Server;
import org.eclipse.jetty.server.handler.HandlerList;
import org.eclipse.jetty.server.handler.ResourceHandler;
import org.eclipse.jetty.servlet.ServletHandler;
import org.eclipse.jetty.servlet.ServletHolder;
import org.eclipse.jetty.servlet.ServletMapping;
import org.yaml.snakeyaml.Yaml;
import org.yaml.snakeyaml.constructor.SafeConstructor;
import org.yaml.snakeyaml.error.MarkedYAMLException;

import com.chiorichan.Warning.WarningState;
import com.chiorichan.command.Command;
import com.chiorichan.command.CommandException;
import com.chiorichan.command.CommandSender;
import com.chiorichan.command.ConsoleCommandSender;
import com.chiorichan.command.PluginCommand;
import com.chiorichan.command.SimpleCommandMap;
import com.chiorichan.configuration.ConfigurationSection;
import com.chiorichan.configuration.file.YamlConfiguration;
import com.chiorichan.entity.User;
import com.chiorichan.help.HelpMap;
import com.chiorichan.permissions.Permissible;
import com.chiorichan.permissions.Permission;
import com.chiorichan.plugin.Plugin;
import com.chiorichan.plugin.PluginManager;
import com.chiorichan.plugin.ServicesManager;
import com.chiorichan.plugin.SimplePluginManager;
import com.chiorichan.plugin.SimpleServicesManager;
import com.chiorichan.plugin.java.JavaPluginLoader;
import com.chiorichan.plugin.messaging.Messenger;
import com.chiorichan.plugin.messaging.StandardMessenger;
import com.chiorichan.scheduler.FrameworkScheduler;
import com.chiorichan.updater.AutoUpdater;
import com.chiorichan.updater.DLUpdaterService;
import com.chiorichan.util.StringUtil;
import com.google.common.collect.ImmutableList;
import com.sun.org.apache.xerces.internal.impl.PropertyManager;

public class ChioriFramework implements IFramework
{
	private final String serverName = "AppleBloomServer";
	private final String serverVersion;
	private final String bukkitVersion = Versioning.getBukkitVersion();
	private final ServicesManager servicesManager = new SimpleServicesManager();
	private final CraftScheduler scheduler = new CraftScheduler();
	private final SimpleCommandMap commandMap = new SimpleCommandMap( this );
	private final SimpleHelpMap helpMap = new SimpleHelpMap( this );
	private final StandardMessenger messenger = new StandardMessenger();
	private final PluginManager pluginManager = new SimplePluginManager( this, commandMap );
	private final EntityManager entityManager;
	protected final DedicatedUserList entityManager;
	private YamlConfiguration configuration;
	private final Yaml yaml = new Yaml( new SafeConstructor() );
	private final AutoUpdater updater;
	private final EntityMetadataStore entityMetadata = new EntityMetadataStore();
	private final UserMetadataStore playerMetadata = new UserMetadataStore();
	private File container;
	private WarningState warningState = WarningState.DEFAULT;
	private final BooleanWrapper online = new BooleanWrapper();
	
	public final static InteractiveConsole console;
	public static SqlConnector sql = new SqlConnector( "chiori", "chiori", "*****", "50.79.49.250" );
	public static ServletHelper t = new ServletHelper();
	private final static ChioriFramework instance = null;
	
	public static void setFramework( ChioriFramework fw )
	{
		instance = fw;
	}
	
	public ChioriFramework(InteractiveConsole console)
	{
		setFramework( this );
		
		pluginManager = new SimplePluginManager( this, new SimpleCommandMap( this ) );
		// scheduler = new FrameworkScheduler();
		entityManager = new EntityManager();
		
		server = new Server( port );
		
		String webDir = ChioriFramework.class.getClassLoader().getResource( "webroot" ).toExternalForm();
		
		ResourceHandler resourceHandler = new ResourceHandler();
		resourceHandler.setDirectoriesListed( true );
		resourceHandler.setWelcomeFiles( new String[] { "index.html" } );
		resourceHandler.setResourceBase( webDir );
		resourceHandler.setStylesheet( "/css/default.css" );
		
		mainHandler.addHandler( resourceHandler );
		mainHandler.addHandler( servletHandler );
		server.setHandler( mainHandler );
		
		this.console = console;
		this.entityManager = (DedicatedUserList) entityManager;
		this.serverVersion = ChioriFramework.class.getPackage().getImplementationVersion();
		
		if ( !ChioriFramework.useConsole )
		{
			getLogger().info( "Console input is disabled due to --noconsole command argument" );
		}
		
		configuration = YamlConfiguration.loadConfiguration( getConfigFile() );
		configuration.options().copyDefaults( true );
		configuration.setDefaults( YamlConfiguration.loadConfiguration( getClass().getClassLoader().getResourceAsStream( "configurations/bukkit.yml" ) ) );
		saveConfig();
		( (SimplePluginManager) pluginManager ).useTimings( configuration.getBoolean( "settings.plugin-profiling" ) );
		console.autosavePeriod = configuration.getInt( "ticks-per.autosave" );
		warningState = WarningState.value( configuration.getString( "settings.deprecated-verbose" ) );
		
		updater = new AutoUpdater( new DLUpdaterService( configuration.getString( "auto-updater.host" ) ), getLogger(), configuration.getString( "auto-updater.preferred-channel" ) );
		updater.setEnabled( configuration.getBoolean( "auto-updater.enabled" ) );
		updater.setSuggestChannels( configuration.getBoolean( "auto-updater.suggest-channels" ) );
		updater.getOnBroken().addAll( configuration.getStringList( "auto-updater.on-broken" ) );
		updater.getOnUpdate().addAll( configuration.getStringList( "auto-updater.on-update" ) );
		updater.check( serverVersion );
		
		loadPlugins();
		
		ChioriFramework.registerServlet( OurWebsocketServlet.class, "/websocket" );
		ChioriFramework.registerServlet( APIServlet.class, "/current.jsp" );
		ChioriFramework.registerServlet( SystemStatus.class, "/status.html" );
		ChioriFramework.registerServlet( SendSMS.class, "/sendSMS.jsp" );
		ChioriFramework.registerServlet( StatusSMS.class, "/statusSMS.jsp" );
		ChioriFramework.registerServlet( serviceCheck.class, "/serviceCheck.html" );
		
		helpMap.clear();
		helpMap.initializeGeneralTopics();
		
		Plugin[] plugins = pluginManager.getPlugins();
		
		for ( Plugin plugin : plugins )
		{
			if ( ( !plugin.isEnabled() ) )
			{
				loadPlugin( plugin );
			}
		}
		
		commandMap.registerServerAliases();
		loadCustomPermissions();
		DefaultPermissions.registerCorePermissions();
		helpMap.initializeCommands();
		
		ChioriFramework.start();
	}
	
	private static Server server = null;
	
	private static final HandlerList mainHandler = new HandlerList();
	private static ServletHandler servletHandler = new ServletHandler();
	private static int port = 8080;
	private static final List<ServletHolder> holders = new ArrayList<ServletHolder>();
	private static final List<ServletMapping> mappings = new ArrayList<ServletMapping>();
	
	private static List<String> asList( String... params )
	{
		return Arrays.asList( params );
	}
	
	public static void main( String... args ) throws Exception
	{
		OptionParser parser = new OptionParser()
		{
			{
				acceptsAll( asList( "?", "help" ), "Show the help" );
				
				acceptsAll( asList( "c", "config" ), "Properties file to use" ).withRequiredArg().ofType( File.class ).defaultsTo( new File( "server.properties" ) ).describedAs( "Properties file" );
				
				acceptsAll( asList( "P", "plugins" ), "Plugin directory to use" ).withRequiredArg().ofType( File.class ).defaultsTo( new File( "plugins" ) ).describedAs( "Plugin directory" );
				
				acceptsAll( asList( "h", "host", "server-ip" ), "Host to listen on" ).withRequiredArg().ofType( String.class ).describedAs( "Hostname or IP" );
				
				acceptsAll( asList( "p", "port", "server-port" ), "Port to listen on" ).withRequiredArg().ofType( Integer.class ).describedAs( "Port" );
				
				acceptsAll( asList( "s", "size", "max-entities" ), "Maximum amount of entities that can exist at a time" ).withRequiredArg().ofType( Integer.class ).describedAs( "Server size" );
				
				acceptsAll( asList( "d", "date-format" ), "Format of the date to display in the console (for log entries)" ).withRequiredArg().ofType( SimpleDateFormat.class ).describedAs( "Log date format" );
				
				acceptsAll( asList( "log-pattern" ), "Specfies the log filename pattern" ).withRequiredArg().ofType( String.class ).defaultsTo( "server.log" ).describedAs( "Log filename" );
				
				acceptsAll( asList( "log-limit" ), "Limits the maximum size of the log file (0 = unlimited)" ).withRequiredArg().ofType( Integer.class ).defaultsTo( 0 ).describedAs( "Max log size" );
				
				acceptsAll( asList( "log-count" ), "Specified how many log files to cycle through" ).withRequiredArg().ofType( Integer.class ).defaultsTo( 1 ).describedAs( "Log count" );
				
				acceptsAll( asList( "log-append" ), "Whether to append to the log file" ).withRequiredArg().ofType( Boolean.class ).defaultsTo( true ).describedAs( "Log append" );
				
				acceptsAll( asList( "log-strip-color" ), "Strips color codes from log file" );
				
				acceptsAll( asList( "b", "bukkit-settings" ), "File for bukkit settings" ).withRequiredArg().ofType( File.class ).defaultsTo( new File( "bukkit.yml" ) ).describedAs( "Yml file" );
				
				acceptsAll( asList( "nojline" ), "Disables jline and emulates the vanilla console" );
				
				acceptsAll( asList( "noconsole" ), "Disables the console" );
				
				acceptsAll( asList( "v", "version" ), "Show the Server Version" );
				
				acceptsAll( asList( "demo" ), "Demo mode" );
			}
		};
		
		OptionSet options = null;
		
		try
		{
			options = parser.parse( args );
		}
		catch ( joptsimple.OptionException ex )
		{
			Logger.getLogger( ChioriFramework.class.getName() ).log( Level.SEVERE, ex.getLocalizedMessage() );
		}
		
		if ( ( options == null ) || ( options.has( "?" ) ) )
		{
			try
			{
				parser.printHelpOn( System.out );
			}
			catch ( IOException ex )
			{
				Logger.getLogger( ChioriFramework.class.getName() ).log( Level.SEVERE, null, ex );
			}
		}
		else if ( options.has( "v" ) )
		{
			System.out.println( ChioriFramework.class.getPackage().getImplementationVersion() );
		}
		else
		{
			if ( options.has( "port" ) )
				port = Integer.parseInt( (String) options.valueOf( "port" ) );
			
			try
			{
				new ChioriFramework( new InteractiveConsole( options ) );
			}
			catch ( Throwable t )
			{
				t.printStackTrace();
			}
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
	
	public static SqlConnector getDatabase()
	{
		return sql;
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
	
	private File getConfigFile()
	{
		return (File) console.options.valueOf( "bukkit-settings" );
	}
	
	private void saveConfig()
	{
		try
		{
			configuration.save( getConfigFile() );
		}
		catch ( IOException ex )
		{
			Logger.getLogger( ChioriFramework.class.getName() ).log( Level.SEVERE, "Could not save " + getConfigFile(), ex );
		}
	}
	
	public void loadPlugins()
	{
		pluginManager.registerInterface( JavaPluginLoader.class );
		
		File pluginFolder = (File) console.options.valueOf( "plugins" );
		
		if ( pluginFolder.exists() )
		{
			Plugin[] plugins = pluginManager.loadPlugins( pluginFolder );
			for ( Plugin plugin : plugins )
			{
				try
				{
					String message = String.format( "Loading %s", plugin.getDescription().getFullName() );
					plugin.getLogger().info( message );
					plugin.onLoad();
				}
				catch ( Throwable ex )
				{
					Logger.getLogger( ChioriFramework.class.getName() ).log( Level.SEVERE, ex.getMessage() + " initializing " + plugin.getDescription().getFullName() + " (Is it up to date?)", ex );
				}
			}
		}
		else
		{
			pluginFolder.mkdir();
		}
	}
	
	public void disablePlugins()
	{
		pluginManager.disablePlugins();
	}
	
	private void loadPlugin( Plugin plugin )
	{
		try
		{
			pluginManager.enablePlugin( plugin );
			
			List<Permission> perms = plugin.getDescription().getPermissions();
			
			for ( Permission perm : perms )
			{
				try
				{
					pluginManager.addPermission( perm );
				}
				catch ( IllegalArgumentException ex )
				{
					getLogger().log( Level.WARNING, "Plugin " + plugin.getDescription().getFullName() + " tried to register permission '" + perm.getName() + "' but it's already registered", ex );
				}
			}
		}
		catch ( Throwable ex )
		{
			Logger.getLogger( ChioriFramework.class.getName() ).log( Level.SEVERE, ex.getMessage() + " loading " + plugin.getDescription().getFullName() + " (Is it up to date?)", ex );
		}
	}
	
	public String getName()
	{
		return serverName;
	}
	
	public String getVersion()
	{
		return serverVersion + " (MC: " + console.getVersion() + ")";
	}
	
	public String getBukkitVersion()
	{
		return bukkitVersion;
	}
	
	@SuppressWarnings( "unchecked" )
	public User[] getOnlineUsers()
	{
		List<User> online = entityManager.players;
		User[] players = new User[online.size()];
		
		for ( int i = 0; i < players.length; i++ )
		{
			players[i] = online.get( i ).playerConnection.getUser();
		}
		
		return players;
	}
	
	public User getUser( final String name )
	{
		Validate.notNull( name, "Name cannot be null" );
		
		User[] players = getOnlineUsers();
		
		User found = null;
		String lowerName = name.toLowerCase();
		int delta = Integer.MAX_VALUE;
		for ( User player : players )
		{
			if ( player.getName().toLowerCase().startsWith( lowerName ) )
			{
				int curDelta = player.getName().length() - lowerName.length();
				if ( curDelta < delta )
				{
					found = player;
					delta = curDelta;
				}
				if ( curDelta == 0 )
					break;
			}
		}
		return found;
	}
	
	public User getUserExact( String name )
	{
		Validate.notNull( name, "Name cannot be null" );
		
		String lname = name.toLowerCase();
		
		for ( User player : getOnlineUsers() )
		{
			if ( player.getName().equalsIgnoreCase( lname ) )
			{
				return player;
			}
		}
		
		return null;
	}
	
	public int broadcastMessage( String message )
	{
		return broadcast( message, BROADCAST_CHANNEL_USERS );
	}
	
	public User getUser( final User entity )
	{
		return entity.playerConnection.getUser();
	}
	
	public List<User> matchUser( String partialName )
	{
		Validate.notNull( partialName, "PartialName cannot be null" );
		
		List<User> matchedUsers = new ArrayList<User>();
		
		for ( User iterUser : this.getOnlineUsers() )
		{
			String iterUserName = iterUser.getName();
			
			if ( partialName.equalsIgnoreCase( iterUserName ) )
			{
				// Exact match
				matchedUsers.clear();
				matchedUsers.add( iterUser );
				break;
			}
			if ( iterUserName.toLowerCase().contains( partialName.toLowerCase() ) )
			{
				// Partial match
				matchedUsers.add( iterUser );
			}
		}
		
		return matchedUsers;
	}
	
	public int getMaxUsers()
	{
		return entityManager.getMaxUsers();
	}
	
	// NOTE: These are dependent on the corrisponding call in MinecraftServer
	// so if that changes this will need to as well
	public int getPort()
	{
		return this.getConfigInt( "server-port", 25565 );
	}
	
	public int getViewDistance()
	{
		return this.getConfigInt( "view-distance", 10 );
	}
	
	public String getIp()
	{
		return this.getConfigString( "server-ip", "" );
	}
	
	public String getServerName()
	{
		return this.getConfigString( "server-name", "Unknown Server" );
	}
	
	public String getServerId()
	{
		return this.getConfigString( "server-id", "unnamed" );
	}
	
	public String getWorldType()
	{
		return this.getConfigString( "level-type", "DEFAULT" );
	}
	
	public boolean getGenerateStructures()
	{
		return this.getConfigBoolean( "generate-structures", true );
	}
	
	public boolean getAllowEnd()
	{
		return this.configuration.getBoolean( "settings.allow-end" );
	}
	
	public boolean getAllowNether()
	{
		return this.getConfigBoolean( "allow-nether", true );
	}
	
	public boolean getWarnOnOverload()
	{
		return this.configuration.getBoolean( "settings.warn-on-overload" );
	}
	
	public boolean getQueryPlugins()
	{
		return this.configuration.getBoolean( "settings.query-plugins" );
	}
	
	public boolean hasWhitelist()
	{
		return this.getConfigBoolean( "white-list", false );
	}
	
	// NOTE: Temporary calls through to server.properies until its replaced
	private String getConfigString( String variable, String defaultValue )
	{
		return this.console.getPropertyManager().getString( variable, defaultValue );
	}
	
	private int getConfigInt( String variable, int defaultValue )
	{
		return this.console.getPropertyManager().getInt( variable, defaultValue );
	}
	
	private boolean getConfigBoolean( String variable, boolean defaultValue )
	{
		return this.console.getPropertyManager().getBoolean( variable, defaultValue );
	}
	
	// End Temporary calls
	
	public String getUpdateFolder()
	{
		return this.configuration.getString( "settings.update-folder", "update" );
	}
	
	public File getUpdateFolderFile()
	{
		return new File( (File) console.options.valueOf( "plugins" ), this.configuration.getString( "settings.update-folder", "update" ) );
	}
	
	public int getPingPacketLimit()
	{
		return this.configuration.getInt( "settings.ping-packet-limit", 100 );
	}
	
	public long getConnectionThrottle()
	{
		return this.configuration.getInt( "settings.connection-throttle" );
	}
	
	public int getTicksPerAnimalSpawns()
	{
		return this.configuration.getInt( "ticks-per.animal-spawns" );
	}
	
	public int getTicksPerMonsterSpawns()
	{
		return this.configuration.getInt( "ticks-per.monster-spawns" );
	}
	
	public ServicesManager getServicesManager()
	{
		return servicesManager;
	}
	
	public DedicatedUserList getHandle()
	{
		return entityManager;
	}
	
	// NOTE: Should only be called from DedicatedServer.ah()
	public boolean dispatchServerCommand( CommandSender sender, ServerCommand serverCommand )
	{
		if ( sender instanceof Conversable )
		{
			Conversable conversable = (Conversable) sender;
			
			if ( conversable.isConversing() )
			{
				conversable.acceptConversationInput( serverCommand.command );
				return true;
			}
		}
		try
		{
			return dispatchCommand( sender, serverCommand.command );
		}
		catch ( Exception ex )
		{
			getLogger().log( Level.WARNING, "Unexpected exception while parsing console command \"" + serverCommand.command + '"', ex );
			return false;
		}
	}
	
	public boolean dispatchCommand( CommandSender sender, String commandLine )
	{
		Validate.notNull( sender, "Sender cannot be null" );
		Validate.notNull( commandLine, "CommandLine cannot be null" );
		
		if ( commandMap.dispatch( sender, commandLine ) )
		{
			return true;
		}
		
		sender.sendMessage( "Unknown command. Type \"help\" for help." );
		
		return false;
	}
	
	public void reload()
	{
		configuration = YamlConfiguration.loadConfiguration( getConfigFile() );
		PropertyManager config = new PropertyManager( console.options, console.getLogger() );
		
		( (DedicatedServer) console ).propertyManager = config;
		
		boolean animals = config.getBoolean( "spawn-animals", console.getSpawnAnimals() );
		boolean monsters = config.getBoolean( "spawn-monsters", console.worlds.get( 0 ).difficulty > 0 );
		int difficulty = config.getInt( "difficulty", console.worlds.get( 0 ).difficulty );
		
		online.value = config.getBoolean( "online-mode", console.getOnlineMode() );
		console.setSpawnAnimals( config.getBoolean( "spawn-animals", console.getSpawnAnimals() ) );
		console.setPvP( config.getBoolean( "pvp", console.getPvP() ) );
		console.setAllowFlight( config.getBoolean( "allow-flight", console.getAllowFlight() ) );
		console.setMotd( config.getString( "motd", console.getMotd() ) );
		monsterSpawn = configuration.getInt( "spawn-limits.monsters" );
		animalSpawn = configuration.getInt( "spawn-limits.animals" );
		waterAnimalSpawn = configuration.getInt( "spawn-limits.water-animals" );
		ambientSpawn = configuration.getInt( "spawn-limits.ambient" );
		warningState = WarningState.value( configuration.getString( "settings.deprecated-verbose" ) );
		console.autosavePeriod = configuration.getInt( "ticks-per.autosave" );
		chunkGCPeriod = configuration.getInt( "chunk-gc.period-in-ticks" );
		chunkGCLoadThresh = configuration.getInt( "chunk-gc.load-threshold" );
		
		entityManager.getIPBans().load();
		entityManager.getNameBans().load();
		
		for ( WorldServer world : console.worlds )
		{
			world.difficulty = difficulty;
			world.setSpawnFlags( monsters, animals );
			if ( this.getTicksPerAnimalSpawns() < 0 )
			{
				world.ticksPerAnimalSpawns = 400;
			}
			else
			{
				world.ticksPerAnimalSpawns = this.getTicksPerAnimalSpawns();
			}
			
			if ( this.getTicksPerMonsterSpawns() < 0 )
			{
				world.ticksPerMonsterSpawns = 1;
			}
			else
			{
				world.ticksPerMonsterSpawns = this.getTicksPerMonsterSpawns();
			}
		}
		
		pluginManager.clearPlugins();
		commandMap.clearCommands();
		resetRecipes();
		
		int pollCount = 0;
		
		// Wait for at most 2.5 seconds for plugins to close their threads
		while ( pollCount < 50 && getScheduler().getActiveWorkers().size() > 0 )
		{
			try
			{
				Thread.sleep( 50 );
			}
			catch ( InterruptedException e )
			{}
			pollCount++;
		}
		
		List<BukkitWorker> overdueWorkers = getScheduler().getActiveWorkers();
		for ( BukkitWorker worker : overdueWorkers )
		{
			Plugin plugin = worker.getOwner();
			String author = "<NoAuthorGiven>";
			if ( plugin.getDescription().getAuthors().size() > 0 )
			{
				author = plugin.getDescription().getAuthors().get( 0 );
			}
			getLogger().log( Level.SEVERE, String.format( "Nag author: '%s' of '%s' about the following: %s", author, plugin.getDescription().getName(), "This plugin is not properly shutting down its async tasks when it is being reloaded.  This may cause conflicts with the newly loaded version of the plugin" ) );
		}
		loadPlugins();
		enablePlugins( PluginLoadOrder.STARTUP );
		enablePlugins( PluginLoadOrder.POSTWORLD );
	}
	
	@SuppressWarnings( { "unchecked", "finally" } )
	private void loadCustomPermissions()
	{
		File file = new File( configuration.getString( "settings.permissions-file" ) );
		FileInputStream stream;
		
		try
		{
			stream = new FileInputStream( file );
		}
		catch ( FileNotFoundException ex )
		{
			try
			{
				file.createNewFile();
			}
			finally
			{
				return;
			}
		}
		
		Map<String, Map<String, Object>> perms;
		
		try
		{
			perms = (Map<String, Map<String, Object>>) yaml.load( stream );
		}
		catch ( MarkedYAMLException ex )
		{
			getLogger().log( Level.WARNING, "Server permissions file " + file + " is not valid YAML: " + ex.toString() );
			return;
		}
		catch ( Throwable ex )
		{
			getLogger().log( Level.WARNING, "Server permissions file " + file + " is not valid YAML.", ex );
			return;
		}
		finally
		{
			try
			{
				stream.close();
			}
			catch ( IOException ex )
			{}
		}
		
		if ( perms == null )
		{
			getLogger().log( Level.INFO, "Server permissions file " + file + " is empty, ignoring it" );
			return;
		}
		
		List<Permission> permsList = Permission.loadPermissions( perms, "Permission node '%s' in " + file + " is invalid", Permission.DEFAULT_PERMISSION );
		
		for ( Permission perm : permsList )
		{
			try
			{
				pluginManager.addPermission( perm );
			}
			catch ( IllegalArgumentException ex )
			{
				getLogger().log( Level.SEVERE, "Permission in " + file + " was already defined", ex );
			}
		}
	}
	
	@Override
	public String toString()
	{
		return "Apple Bloom Server{" + "serverName=" + serverName + ",serverVersion=" + serverVersion + ",minecraftVersion=" + console.getVersion() + '}';
	}
	
	public ConsoleReader getReader()
	{
		return console.reader;
	}
	
	public PluginCommand getPluginCommand( String name )
	{
		Command command = commandMap.getCommand( name );
		
		if ( command instanceof PluginCommand )
		{
			return (PluginCommand) command;
		}
		else
		{
			return null;
		}
	}
	
	public void saveUsers()
	{
		entityManager.saveUsers();
	}
	
	public void configureDbConfig( ServerConfig config )
	{
		Validate.notNull( config, "Config cannot be null" );
		
		DataSourceConfig ds = new DataSourceConfig();
		ds.setDriver( configuration.getString( "database.driver" ) );
		ds.setUrl( configuration.getString( "database.url" ) );
		ds.setUsername( configuration.getString( "database.username" ) );
		ds.setPassword( configuration.getString( "database.password" ) );
		ds.setIsolationLevel( TransactionIsolation.getLevel( configuration.getString( "database.isolation" ) ) );
		
		if ( ds.getDriver().contains( "sqlite" ) )
		{
			config.setDatabasePlatform( new SQLitePlatform() );
			config.getDatabasePlatform().getDbDdlSyntax().setIdentity( "" );
		}
		
		config.setDataSourceConfig( ds );
	}
	
	public boolean addRecipe( Recipe recipe )
	{
		CraftRecipe toAdd;
		if ( recipe instanceof CraftRecipe )
		{
			toAdd = (CraftRecipe) recipe;
		}
		else
		{
			if ( recipe instanceof ShapedRecipe )
			{
				toAdd = CraftShapedRecipe.fromBukkitRecipe( (ShapedRecipe) recipe );
			}
			else if ( recipe instanceof ShapelessRecipe )
			{
				toAdd = CraftShapelessRecipe.fromBukkitRecipe( (ShapelessRecipe) recipe );
			}
			else if ( recipe instanceof FurnaceRecipe )
			{
				toAdd = CraftFurnaceRecipe.fromBukkitRecipe( (FurnaceRecipe) recipe );
			}
			else
			{
				return false;
			}
		}
		toAdd.addToCraftingManager();
		CraftingManager.getInstance().sort();
		return true;
	}
	
	public List<Recipe> getRecipesFor( ItemStack result )
	{
		Validate.notNull( result, "Result cannot be null" );
		
		List<Recipe> results = new ArrayList<Recipe>();
		Iterator<Recipe> iter = recipeIterator();
		while ( iter.hasNext() )
		{
			Recipe recipe = iter.next();
			ItemStack stack = recipe.getResult();
			if ( stack.getType() != result.getType() )
			{
				continue;
			}
			if ( result.getDurability() == -1 || result.getDurability() == stack.getDurability() )
			{
				results.add( recipe );
			}
		}
		return results;
	}
	
	public Iterator<Recipe> recipeIterator()
	{
		return new RecipeIterator();
	}
	
	public void clearRecipes()
	{
		CraftingManager.getInstance().recipes.clear();
		RecipesFurnace.getInstance().recipes.clear();
	}
	
	public void resetRecipes()
	{
		CraftingManager.getInstance().recipes = new CraftingManager().recipes;
		RecipesFurnace.getInstance().recipes = new RecipesFurnace().recipes;
	}
	
	public Map<String, String[]> getCommandAliases()
	{
		ConfigurationSection section = configuration.getConfigurationSection( "aliases" );
		Map<String, String[]> result = new LinkedHashMap<String, String[]>();
		
		if ( section != null )
		{
			for ( String key : section.getKeys( false ) )
			{
				List<String> commands;
				
				if ( section.isList( key ) )
				{
					commands = section.getStringList( key );
				}
				else
				{
					commands = ImmutableList.of( section.getString( key ) );
				}
				
				result.put( key, commands.toArray( new String[commands.size()] ) );
			}
		}
		
		return result;
	}
	
	public void removeBukkitSpawnRadius()
	{
		configuration.set( "settings.spawn-radius", null );
		saveConfig();
	}
	
	public int getBukkitSpawnRadius()
	{
		return configuration.getInt( "settings.spawn-radius", -1 );
	}
	
	public String getShutdownMessage()
	{
		return configuration.getString( "settings.shutdown-message" );
	}
	
	public int getSpawnRadius()
	{
		return ( (DedicatedServer) console ).propertyManager.getInt( "spawn-protection", 16 );
	}
	
	public void setSpawnRadius( int value )
	{
		configuration.set( "settings.spawn-radius", value );
		saveConfig();
	}
	
	public boolean getOnlineMode()
	{
		return online.value;
	}
	
	public boolean getAllowFlight()
	{
		return console.getAllowFlight();
	}
	
	public boolean isHardcore()
	{
		return console.isHardcore();
	}
	
	public boolean useExactLoginLocation()
	{
		return configuration.getBoolean( "settings.use-exact-login-location" );
	}
	
	public ChunkGenerator getGenerator( String world )
	{
		ConfigurationSection section = configuration.getConfigurationSection( "worlds" );
		ChunkGenerator result = null;
		
		if ( section != null )
		{
			section = section.getConfigurationSection( world );
			
			if ( section != null )
			{
				String name = section.getString( "generator" );
				
				if ( ( name != null ) && ( !name.equals( "" ) ) )
				{
					String[] split = name.split( ":", 2 );
					String id = ( split.length > 1 ) ? split[1] : null;
					Plugin plugin = pluginManager.getPlugin( split[0] );
					
					if ( plugin == null )
					{
						getLogger().severe( "Could not set generator for default world '" + world + "': Plugin '" + split[0] + "' does not exist" );
					}
					else if ( !plugin.isEnabled() )
					{
						getLogger().severe( "Could not set generator for default world '" + world + "': Plugin '" + plugin.getDescription().getFullName() + "' is not enabled yet (is it load:STARTUP?)" );
					}
					else
					{
						result = plugin.getDefaultWorldGenerator( world, id );
						if ( result == null )
						{
							getLogger().severe( "Could not set generator for default world '" + world + "': Plugin '" + plugin.getDescription().getFullName() + "' lacks a default world generator" );
						}
					}
				}
			}
		}
		
		return result;
	}
	
	public CraftMapView getMap( short id )
	{
		WorldMapCollection collection = console.worlds.get( 0 ).worldMaps;
		WorldMap worldmap = (WorldMap) collection.get( WorldMap.class, "map_" + id );
		if ( worldmap == null )
		{
			return null;
		}
		return worldmap.mapView;
	}
	
	public CraftMapView createMap( World world )
	{
		Validate.notNull( world, "World cannot be null" );
		
		net.minecraft.server.ItemStack stack = new net.minecraft.server.ItemStack( Item.MAP, 1, -1 );
		WorldMap worldmap = Item.MAP.getSavedMap( stack, ( (CraftWorld) world ).getHandle() );
		return worldmap.mapView;
	}
	
	public void shutdown()
	{
		console.safeShutdown();
	}
	
	public int broadcast( String message, String permission )
	{
		int count = 0;
		Set<Permissible> permissibles = getPluginManager().getPermissionSubscriptions( permission );
		
		for ( Permissible permissible : permissibles )
		{
			if ( permissible instanceof CommandSender && permissible.hasPermission( permission ) )
			{
				CommandSender user = (CommandSender) permissible;
				user.sendMessage( message );
				count++;
			}
		}
		
		return count;
	}
	
	public OfflineUser getOfflineUser( String name )
	{
		return getOfflineUser( name, true );
	}
	
	public OfflineUser getOfflineUser( String name, boolean search )
	{
		Validate.notNull( name, "Name cannot be null" );
		
		OfflineUser result = getUserExact( name );
		String lname = name.toLowerCase();
		
		if ( result == null )
		{
			result = offlineUsers.get( lname );
			
			if ( result == null )
			{
				if ( search )
				{
					WorldNBTStorage storage = (WorldNBTStorage) console.worlds.get( 0 ).getDataManager();
					for ( String dat : storage.getUserDir().list( new DatFileFilter() ) )
					{
						String datName = dat.substring( 0, dat.length() - 4 );
						if ( datName.equalsIgnoreCase( name ) )
						{
							name = datName;
							break;
						}
					}
				}
				
				result = new CraftOfflineUser( this, name );
				offlineUsers.put( lname, result );
			}
		}
		else
		{
			offlineUsers.remove( lname );
		}
		
		return result;
	}
	
	@SuppressWarnings( "unchecked" )
	public Set<String> getIPBans()
	{
		return entityManager.getIPBans().getEntries().keySet();
	}
	
	public void banIP( String address )
	{
		Validate.notNull( address, "Address cannot be null." );
		
		BanEntry entry = new BanEntry( address );
		entityManager.getIPBans().add( entry );
		entityManager.getIPBans().save();
	}
	
	public void unbanIP( String address )
	{
		entityManager.getIPBans().remove( address );
		entityManager.getIPBans().save();
	}
	
	public Set<User> getBannedUsers()
	{
		Set<User> result = new HashSet<User>();
		
		for ( Object name : entityManager.getNameBans().getEntries().keySet() )
		{
			result.add( getOfflineUser( (String) name ) );
		}
		
		return result;
	}
	
	public void setWhitelist( boolean value )
	{
		entityManager.hasWhitelist = value;
		console.getPropertyManager().a( "white-list", value );
	}
	
	public Set<OfflineUser> getWhitelistedUsers()
	{
		Set<OfflineUser> result = new LinkedHashSet<OfflineUser>();
		
		for ( Object name : entityManager.getWhitelisted() )
		{
			if ( ( (String) name ).length() == 0 || ( (String) name ).startsWith( "#" ) )
			{
				continue;
			}
			result.add( getOfflineUser( (String) name ) );
		}
		
		return result;
	}
	
	public Set<User> getOperators()
	{
		Set<User> result = new HashSet<User>();
		
		for ( Object name : entityManager.getOPs() )
		{
			result.add( getOfflineUser( (String) name ) );
		}
		
		return result;
	}
	
	public void reloadWhitelist()
	{
		entityManager.reloadWhitelist();
	}
	
	public ConsoleCommandSender getConsoleSender()
	{
		return console.console;
	}
	
	public EntityMetadataStore getEntityMetadata()
	{
		return entityMetadata;
	}
	
	public UserMetadataStore getUserMetadata()
	{
		return playerMetadata;
	}
	
	public void detectListNameConflict( User entityUser )
	{
		// Collisions will make for invisible ponies
		for ( int i = 0; i < getHandle().players.size(); ++i )
		{
			User testUser = (User) getHandle().players.get( i );
			
			// We have a problem!
			if ( testUser != entityUser && testUser.listName.equals( entityUser.listName ) )
			{
				String oldName = entityUser.listName;
				int spaceLeft = 16 - oldName.length();
				
				if ( spaceLeft <= 1 )
				{ // We also hit the list name length limit!
					entityUser.listName = oldName.subSequence( 0, oldName.length() - 2 - spaceLeft ) + String.valueOf( System.currentTimeMillis() % 99 );
				}
				else
				{
					entityUser.listName = oldName + String.valueOf( System.currentTimeMillis() % 99 );
				}
				
				return;
			}
		}
	}
	
	public Messenger getMessenger()
	{
		return messenger;
	}
	
	public void sendPluginMessage( Plugin source, String channel, byte[] message )
	{
		StandardMessenger.validatePluginMessage( getMessenger(), source, channel, message );
		
		for ( User player : getOnlineUsers() )
		{
			player.sendPluginMessage( source, channel, message );
		}
	}
	
	public Set<String> getListeningPluginChannels()
	{
		Set<String> result = new HashSet<String>();
		
		for ( User player : getOnlineUsers() )
		{
			result.addAll( player.getListeningPluginChannels() );
		}
		
		return result;
	}
	
	public void onUserJoin( User player )
	{
		if ( ( updater.isEnabled() ) && ( updater.getCurrent() != null ) && ( player.hasPermission( Server.BROADCAST_CHANNEL_ADMINISTRATIVE ) ) )
		{
			if ( ( updater.getCurrent().isBroken() ) && ( updater.getOnBroken().contains( AutoUpdater.WARN_OPERATORS ) ) )
			{
				player.sendMessage( ChatColor.DARK_RED + "The version of CraftBukkit that this server is running is known to be broken. Please consider updating to the latest version at dl.bukkit.org." );
			}
			else if ( ( updater.isUpdateAvailable() ) && ( updater.getOnUpdate().contains( AutoUpdater.WARN_OPERATORS ) ) )
			{
				player.sendMessage( ChatColor.DARK_PURPLE + "The version of CraftBukkit that this server is running is out of date. Please consider updating to the latest version at dl.bukkit.org." );
			}
		}
	}
	
	public HelpMap getHelpMap()
	{
		return helpMap;
	}
	
	public SimpleCommandMap getCommandMap()
	{
		return commandMap;
	}
	
	public boolean isPrimaryThread()
	{
		return Thread.currentThread().equals( console.primaryThread );
	}
	
	public String getMotd()
	{
		return console.getMotd();
	}
	
	public WarningState getWarningState()
	{
		return warningState;
	}
	
	public List<String> tabComplete( ICommandListener sender, String message )
	{
		if ( !( sender instanceof User ) )
		{
			return ImmutableList.of();
		}
		
		User player = ( (User) sender ).getBukkitEntity();
		if ( message.startsWith( "/" ) )
		{
			return tabCompleteCommand( player, message );
		}
		else
		{
			return tabCompleteChat( player, message );
		}
	}
	
	public List<String> tabCompleteCommand( User player, String message )
	{
		List<String> completions = null;
		try
		{
			completions = getCommandMap().tabComplete( player, message.substring( 1 ) );
		}
		catch ( CommandException ex )
		{
			player.sendMessage( ChatColor.RED + "An internal error occurred while attempting to tab-complete this command" );
			getLogger().log( Level.SEVERE, "Exception when " + player.getName() + " attempted to tab complete " + message, ex );
		}
		
		return completions == null ? ImmutableList.<String> of() : completions;
	}
	
	public List<String> tabCompleteChat( User player, String message )
	{
		User[] players = getOnlineUsers();
		List<String> completions = new ArrayList<String>();
		UserChatTabCompleteEvent event = new UserChatTabCompleteEvent( player, message, completions );
		String token = event.getLastToken();
		for ( User p : players )
		{
			if ( player.canSee( p ) && StringUtil.startsWithIgnoreCase( p.getName(), token ) )
			{
				completions.add( p.getName() );
			}
		}
		pluginManager.callEvent( event );
		
		Iterator<?> it = completions.iterator();
		while ( it.hasNext() )
		{
			Object current = it.next();
			if ( !( current instanceof String ) )
			{
				// Sanity
				it.remove();
			}
		}
		Collections.sort( completions, String.CASE_INSENSITIVE_ORDER );
		return completions;
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
	public Set<User> getWhitelistedUsers()
	{
		// TODO Auto-generated method stub
		return null;
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
	public boolean unloadWorld( String name, boolean save )
	{
		// TODO Auto-generated method stub
		return false;
	}
	
	@Override
	public void saveUsers()
	{
		// TODO Auto-generated method stub
		
	}
	
	@Override
	public User getUser( String name )
	{
		// TODO Auto-generated method stub
		return null;
	}
}
