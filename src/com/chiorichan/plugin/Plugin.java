package com.chiorichan.plugin;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.URL;
import java.net.URLConnection;
import java.util.logging.Level;
import java.util.logging.Logger;

import com.chiorichan.ChioriFramework;
import com.chiorichan.command.CommandExecutor;
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
	private ChioriFramework framework = null;
	
	public ChioriFramework getFramework()
	{
		return ChioriFramework.instance;
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
	
	public File getJarResource( String name )
	{
		return new File( this.getClass().getClassLoader().getResource( name ).toExternalForm() );
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
	 * Returns the file which contains this plugin
	 * 
	 * @return File containing this plugin
	 */
	protected File getFile()
	{
		return file;
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
	 * Returns the ClassLoader which holds this plugin
	 * 
	 * @return ClassLoader holding this plugin
	 */
	protected final ClassLoader getClassLoader()
	{
		return classLoader;
	}
	
	/**
	 * Sets the enabled state of this plugin
	 * 
	 * @param enabled
	 *           true if enabled, otherwise false
	 */
	protected final void setEnabled( final boolean enabled )
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
	
	/**
	 * Returns the primary logger associated with this server instance. The returned logger automatically tags all log
	 * messages with the plugin's name.
	 * 
	 * @return Logger associated with this server
	 */
	public final Logger getLogger()
	{
		return logger;
	}
	
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
}
