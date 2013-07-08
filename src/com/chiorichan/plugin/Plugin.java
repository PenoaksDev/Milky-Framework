package com.chiorichan.plugin;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.URL;
import java.net.URLConnection;
import java.util.ArrayList;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;

import com.chiorichan.ChioriFramework;
import com.chiorichan.IFramework;
import com.chiorichan.command.Command;
import com.chiorichan.command.CommandExecutor;
import com.chiorichan.command.CommandSender;
import com.chiorichan.command.PluginCommand;
import com.chiorichan.configuration.file.FileConfiguration;
import com.chiorichan.configuration.file.YamlConfiguration;

/**
 * Represents a Plugin
 * <p>
 * The use of {@link PluginBase} is recommended for actual Implementation
 */
public abstract class Plugin implements CommandExecutor
{
	private boolean isEnabled = false;
	private boolean initialized = false;
	private PluginLoader loader = null;
	private File file = null;
	private PluginDescriptionFile description = null;
	private File dataFolder = null;
	private ClassLoader classLoader = null;
	private boolean naggable = true;
	private FileConfiguration newConfig = null;
	private File configFile = null;
	private PluginLogger logger = null;
	private IFramework framework = null;
	
	public ChioriFramework getFramework()
	{
		return ChioriFramework.getFramework();
	}
	
	public File getJarResource( String name )
	{
		return new File( this.getClass().getClassLoader().getResource( name ).toExternalForm() );
	}
	
	/**
	 * Returns the file which contains this plugin
	 * 
	 * @return File containing this plugin
	 */
	protected File getFile()
	{
		return file;
	}
	
	public FileConfiguration getConfig()
	{
		if ( newConfig == null )
		{
			reloadConfig();
		}
		return newConfig;
	}
	
	public void reloadConfig()
	{
		newConfig = YamlConfiguration.loadConfiguration( configFile );
		
		InputStream defConfigStream = getResource( "config.yml" );
		if ( defConfigStream != null )
		{
			YamlConfiguration defConfig = YamlConfiguration.loadConfiguration( defConfigStream );
			
			newConfig.setDefaults( defConfig );
		}
	}
	
	public void saveConfig()
	{
		try
		{
			getConfig().save( configFile );
		}
		catch ( IOException ex )
		{
			logger.log( Level.SEVERE, "Could not save config to " + configFile, ex );
		}
	}
	
	public void saveDefaultConfig()
	{
		if ( !configFile.exists() )
		{
			saveResource( "config.yml", false );
		}
	}
	
	/**
	 * Initializes this plugin with the given variables.
	 * <p>
	 * This method should never be called manually.
	 * 
	 * @param loader
	 *           PluginLoader that is responsible for this plugin
	 * @param server
	 *           Server instance that is running this plugin
	 * @param description
	 *           PluginDescriptionFile containing metadata on this plugin
	 * @param dataFolder
	 *           Folder containing the plugin's data
	 * @param file
	 *           File containing this plugin
	 * @param classLoader
	 *           ClassLoader which holds this plugin
	 */
	protected final void initialize( PluginLoader loader, ChioriFramework framework, PluginDescriptionFile description, File dataFolder, File file, ClassLoader classLoader )
	{
		if ( !initialized )
		{
			this.initialized = true;
			this.loader = loader;
			this.framework = framework;
			this.file = file;
			this.description = description;
			this.dataFolder = dataFolder;
			this.classLoader = classLoader;
			this.configFile = new File( dataFolder, "config.yml" );
			this.logger = new PluginLogger( this );
		}
	}
	
	/**
	 * Called when this plugin is disabled
	 */
	public abstract void onDisable();
	
	/**
	 * Called after a plugin is loaded but before it has been enabled. When mulitple plugins are loaded, the onLoad() for
	 * all plugins is called before any onEnable() is called.
	 */
	public abstract void onLoad();
	
	/**
	 * Called when this plugin is enabled
	 */
	public abstract void onEnable();
	
	public String toString()
	{
		return description.getFullName();
	}
	
	@Override
	public final int hashCode()
	{
		return getName().hashCode();
	}
	
	@Override
	public final boolean equals( Object obj )
	{
		if ( this == obj )
		{
			return true;
		}
		if ( obj == null )
		{
			return false;
		}
		if ( !( obj instanceof Plugin ) )
		{
			return false;
		}
		return getName().equals( ( (Plugin) obj ).getName() );
	}
	
	public final String getName()
	{
		return getDescription().getName();
	}
	
	public void saveResource( String resourcePath, boolean replace )
	{
		if ( resourcePath == null || resourcePath.equals( "" ) )
		{
			throw new IllegalArgumentException( "ResourcePath cannot be null or empty" );
		}
		
		resourcePath = resourcePath.replace( '\\', '/' );
		InputStream in = getResource( resourcePath );
		if ( in == null )
		{
			throw new IllegalArgumentException( "The embedded resource '" + resourcePath + "' cannot be found in " + file );
		}
		
		File outFile = new File( dataFolder, resourcePath );
		int lastIndex = resourcePath.lastIndexOf( '/' );
		File outDir = new File( dataFolder, resourcePath.substring( 0, lastIndex >= 0 ? lastIndex : 0 ) );
		
		if ( !outDir.exists() )
		{
			outDir.mkdirs();
		}
		
		try
		{
			if ( !outFile.exists() || replace )
			{
				OutputStream out = new FileOutputStream( outFile );
				byte[] buf = new byte[1024];
				int len;
				while ( ( len = in.read( buf ) ) > 0 )
				{
					out.write( buf, 0, len );
				}
				out.close();
				in.close();
			}
			else
			{
				logger.log( Level.WARNING, "Could not save " + outFile.getName() + " to " + outFile + " because " + outFile.getName() + " already exists." );
			}
		}
		catch ( IOException ex )
		{
			logger.log( Level.SEVERE, "Could not save " + outFile.getName() + " to " + outFile, ex );
		}
	}
	
	public InputStream getResource( String filename )
	{
		if ( filename == null )
		{
			throw new IllegalArgumentException( "Filename cannot be null" );
		}
		
		try
		{
			URL url = getClassLoader().getResource( filename );
			
			if ( url == null )
			{
				return null;
			}
			
			URLConnection connection = url.openConnection();
			connection.setUseCaches( false );
			return connection.getInputStream();
		}
		catch ( IOException ex )
		{
			return null;
		}
	}
	
	/**
	 * Returns the folder that the plugin data's files are located in. The folder may not yet exist.
	 * 
	 * @return The folder.
	 */
	public final File getDataFolder()
	{
		return dataFolder;
	}
	
	/**
	 * Gets the associated PluginLoader responsible for this plugin
	 * 
	 * @return PluginLoader that controls this plugin
	 */
	public final PluginLoader getPluginLoader()
	{
		return loader;
	}
	
	/**
	 * Returns the Server instance currently running this plugin
	 * 
	 * @return Server running this plugin
	 */
	public final IFramework getServer()
	{
		return framework;
	}
	
	/**
	 * Returns a value indicating whether or not this plugin is currently enabled
	 * 
	 * @return true if this plugin is enabled, otherwise false
	 */
	public final boolean isEnabled()
	{
		return isEnabled;
	}
	
	/**
	 * Returns the plugin.yaml file containing the details for this plugin
	 * 
	 * @return Contents of the plugin.yaml file
	 */
	public final PluginDescriptionFile getDescription()
	{
		return description;
	}
	
	/**
	 * Returns the ClassLoader which holds this plugin
	 * 
	 * @return ClassLoader holding this plugin
	 */
	public final ClassLoader getClassLoader()
	{
		return classLoader;
	}
	
	/**
	 * Sets the enabled state of this plugin
	 * 
	 * @param enabled
	 *           true if enabled, otherwise false
	 */
	public final void setEnabled( final boolean enabled )
	{
		if ( isEnabled != enabled )
		{
			isEnabled = enabled;
			
			if ( isEnabled )
			{
				onEnable();
			}
			else
			{
				onDisable();
			}
		}
	}
	
	/**
	 * Initializes this plugin with the given variables.
	 * <p>
	 * This method should never be called manually.
	 * 
	 * @param loader
	 *           PluginLoader that is responsible for this plugin
	 * @param server
	 *           Server instance that is running this plugin
	 * @param description
	 *           PluginDescriptionFile containing metadata on this plugin
	 * @param dataFolder
	 *           Folder containing the plugin's data
	 * @param file
	 *           File containing this plugin
	 * @param classLoader
	 *           ClassLoader which holds this plugin
	 */
	public final void initialize( PluginLoader loader, IFramework server, PluginDescriptionFile description, File dataFolder, File file, ClassLoader classLoader )
	{
		if ( !initialized )
		{
			this.initialized = true;
			this.loader = loader;
			this.framework = server;
			this.file = file;
			this.description = description;
			this.dataFolder = dataFolder;
			this.classLoader = classLoader;
			this.configFile = new File( dataFolder, "config.yml" );
			this.logger = new PluginLogger( this );
		}
	}
	
	/**
	 * Provides a list of all classes that should be persisted in the database
	 * 
	 * @return List of Classes that are Ebeans
	 */
	public List<Class<?>> getDatabaseClasses()
	{
		return new ArrayList<Class<?>>();
	}
	
	private String replaceDatabaseString( String input )
	{
		input = input.replaceAll( "\\{DIR\\}", dataFolder.getPath().replaceAll( "\\\\", "/" ) + "/" );
		input = input.replaceAll( "\\{NAME\\}", description.getName().replaceAll( "[^\\w_-]", "" ) );
		return input;
	}
	
	/**
	 * Gets the initialization status of this plugin
	 * 
	 * @return true if this plugin is initialized, otherwise false
	 */
	public final boolean isInitialized()
	{
		return initialized;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public boolean onCommand( CommandSender sender, Command command, String label, String[] args )
	{
		return false;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public List<String> onTabComplete( CommandSender sender, Command command, String alias, String[] args )
	{
		return null;
	}
	
	/**
	 * Gets the command with the given name, specific to this plugin. Commands need to be registered in the
	 * {@link PluginDescriptionFile#getCommands() PluginDescriptionFile} to exist at runtime.
	 * 
	 * @param name
	 *           name or alias of the command
	 * @return the plugin command if found, otherwise null
	 */
	public PluginCommand getCommand( String name )
	{
		String alias = name.toLowerCase();
		PluginCommand command = getServer().getPluginCommand( alias );
		
		if ( ( command != null ) && ( command.getPlugin() != this ) )
		{
			command = getServer().getPluginCommand( description.getName().toLowerCase() + ":" + alias );
		}
		
		if ( ( command != null ) && ( command.getPlugin() == this ) )
		{
			return command;
		}
		else
		{
			return null;
		}
	}
	
	public final boolean isNaggable()
	{
		return naggable;
	}
	
	public final void setNaggable( boolean canNag )
	{
		this.naggable = canNag;
	}
	
	public final Logger getLogger()
	{
		return logger;
	}
}
