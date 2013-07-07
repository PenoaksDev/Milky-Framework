package com.chiorichan;

import java.io.File;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.logging.Logger;

import com.chiorichan.Warning.WarningState;
import com.chiorichan.command.CommandException;
import com.chiorichan.command.CommandSender;
import com.chiorichan.command.ConsoleCommandSender;
import com.chiorichan.command.PluginCommand;
import com.chiorichan.entity.User;
import com.chiorichan.help.HelpMap;
import com.chiorichan.plugin.PluginManager;
import com.chiorichan.plugin.ServicesManager;
import com.chiorichan.plugin.messaging.Messenger;
import com.chiorichan.plugin.messaging.PluginMessageRecipient;
import com.chiorichan.scheduler.FrameworkScheduler;

/**
 * Represents a server implementation
 */
public interface IFramework extends PluginMessageRecipient
{
	/**
	 * Used for all administrative messages, such as an operator using a command.
	 * <p>
	 * For use in {@link #broadcast(java.lang.String, java.lang.String)}
	 */
	public static final String BROADCAST_CHANNEL_ADMINISTRATIVE = "bukkit.broadcast.admin";
	
	/**
	 * Used for all announcement messages, such as informing users that a user has joined.
	 * <p>
	 * For use in {@link #broadcast(java.lang.String, java.lang.String)}
	 */
	public static final String BROADCAST_CHANNEL_USERS = "bukkit.broadcast.user";
	
	/**
	 * Gets the name of this server implementation
	 * 
	 * @return name of this server implementation
	 */
	public String getName();
	
	/**
	 * Gets the version string of this server implementation.
	 * 
	 * @return version of this server implementation
	 */
	public String getVersion();
	
	/**
	 * Gets the ChioriFramework version that this server is running.
	 * 
	 * @return Version of ChioriFramework
	 */
	public String getFrameworkVersion();
	
	/**
	 * Gets a list of all currently logged in users
	 * 
	 * @return An array of Users that are currently online
	 */
	public User[] getUsers();
	
	/**
	 * Get the maximum amount of users which can login to this server
	 * 
	 * @return The amount of users this server allows
	 */
	public int getMaxUsers();
	
	/**
	 * Get the game port that the server runs on
	 * 
	 * @return The port number of this server
	 */
	public int getPort();
	
	/**
	 * Get the IP that this server is bound to or empty string if not specified
	 * 
	 * @return The IP string that this server is bound to, otherwise empty string
	 */
	public String getIp();
	
	/**
	 * Get the name of this server
	 * 
	 * @return The name of this server
	 */
	public String getServerName();
	
	/**
	 * Get an ID of this server. The ID is a simple generally alphanumeric ID that can be used for uniquely identifying
	 * this server.
	 * 
	 * @return The ID of this server
	 */
	public String getServerId();
	
	/**
	 * Gets whether this server has a whitelist or not.
	 * 
	 * @return Whether this server has a whitelist or not.
	 */
	public boolean hasWhitelist();
	
	/**
	 * Sets the whitelist on or off
	 * 
	 * @param value
	 *           true if whitelist is on, otherwise false
	 */
	public void setWhitelist( boolean value );
	
	/**
	 * Gets a list of whitelisted users
	 * 
	 * @return Set containing all whitelisted users
	 */
	public Set<User> getWhitelistedUsers();
	
	/**
	 * Reloads the whitelist from disk
	 */
	public void reloadWhitelist();
	
	/**
	 * Broadcast a message to all users.
	 * <p>
	 * This is the same as calling {@link #broadcast(java.lang.String, java.lang.String)} to
	 * {@link #BROADCAST_CHANNEL_USERS}
	 * 
	 * @param message
	 *           the message
	 * @return the number of users
	 */
	public int broadcastMessage( String message );
	
	/**
	 * Gets the name of the update folder. The update folder is used to safely update plugins at the right moment on a
	 * plugin load.
	 * <p>
	 * The update folder name is relative to the plugins folder.
	 * 
	 * @return The name of the update folder
	 */
	public String getUpdateFolder();
	
	/**
	 * Gets the update folder. The update folder is used to safely update plugins at the right moment on a plugin load.
	 * 
	 * @return The name of the update folder
	 */
	public File getUpdateFolderFile();
	
	/**
	 * Gets the value of the connection throttle setting
	 * 
	 * @return the value of the connection throttle setting
	 */
	public long getConnectionThrottle();
	
	/**
	 * Gets the user with the exact given name, case insensitive
	 * 
	 * @param name
	 *           Exact name of the user to retrieve
	 * @return User object or null if not found
	 */
	public User getUserExact( String name );
	
	/**
	 * Attempts to match any users with the given name, and returns a list of all possibly matches
	 * <p>
	 * This list is not sorted in any particular order. If an exact match is found, the returned list will only contain a
	 * single result.
	 * 
	 * @param name
	 *           Name to match
	 * @return List of all possible users
	 */
	public List<User> matchUser( String name );
	
	/**
	 * Gets the PluginManager for interfacing with plugins
	 * 
	 * @return PluginManager for this Server instance
	 */
	public PluginManager getPluginManager();
	
	/**
	 * Gets the Scheduler for managing scheduled events
	 * 
	 * @return Scheduler for this Server instance
	 */
	public FrameworkScheduler getScheduler();
	
	/**
	 * Gets a services manager
	 * 
	 * @return Services manager
	 */
	public ServicesManager getServicesManager();
	
	/**
	 * Unloads a world with the given name.
	 * 
	 * @param name
	 *           Name of the world to unload
	 * @param save
	 *           Whether to save the chunks before unloading.
	 * @return Whether the action was Successful
	 */
	public boolean unloadWorld( String name, boolean save );
	
	/**
	 * Reloads the server, refreshing settings and plugin information
	 */
	public void reload();
	
	/**
	 * Returns the primary logger associated with this server instance
	 * 
	 * @return Logger associated with this server
	 */
	public Logger getLogger();
	
	/**
	 * Gets a {@link PluginCommand} with the given name or alias
	 * 
	 * @param name
	 *           Name of the command to retrieve
	 * @return PluginCommand if found, otherwise null
	 */
	public PluginCommand getPluginCommand( String name );
	
	/**
	 * Writes loaded users to disk
	 */
	public void saveUsers();
	
	/**
	 * Dispatches a command on the server, and executes it if found.
	 * 
	 * @param sender
	 *           The apparent sender of the command
	 * @param commandLine
	 *           command + arguments. Example: "test abc 123"
	 * @return returns false if no target is found.
	 * @throws CommandException
	 *            Thrown when the executor for the given command fails with an unhandled exception
	 */
	public boolean dispatchCommand( CommandSender sender, String commandLine ) throws CommandException;
	
	/**
	 * Populates a given {@link ServerConfig} with values attributes to this server
	 * 
	 * @param config
	 *           ServerConfig to populate
	 */
	public void configureDbConfig( ServerConfig config );
	
	/**
	 * Gets a list of command aliases defined in the server properties.
	 * 
	 * @return Map of aliases to command names
	 */
	public Map<String, String[]> getCommandAliases();
	
	/**
	 * Shutdowns the server, stopping everything.
	 */
	public void shutdown();
	
	/**
	 * Broadcasts the specified message to every user with the given permission
	 * 
	 * @param message
	 *           Message to broadcast
	 * @param permission
	 *           Permission the users must have to receive the broadcast
	 * @return Amount of users who received the message
	 */
	public int broadcast( String message, String permission );
	
	/**
	 * Gets the user by the given name, regardless if they are offline or online.
	 * <p>
	 * This will return an object even if the user does not exist. To this method, all users will exist.
	 * 
	 * @param name
	 *           Name of the user to retrieve
	 * @return User object
	 */
	public User getUser( String name );
	
	/**
	 * Gets a set containing all current IPs that are banned
	 * 
	 * @return Set containing banned IP addresses
	 */
	public Set<String> getIPBans();
	
	/**
	 * Bans the specified address from the server
	 * 
	 * @param address
	 *           IP address to ban
	 */
	public void banIP( String address );
	
	/**
	 * Unbans the specified address from the server
	 * 
	 * @param address
	 *           IP address to unban
	 */
	public void unbanIP( String address );
	
	/**
	 * Gets a set containing all banned users
	 * 
	 * @return Set containing banned users
	 */
	public Set<User> getBannedUsers();
	
	/**
	 * Gets a set containing all user operators
	 * 
	 * @return Set containing user operators
	 */
	public Set<User> getOperators();
	
	/**
	 * Gets the {@link ConsoleCommandSender} that may be used as an input source for this server.
	 * 
	 * @return The Console CommandSender
	 */
	public ConsoleCommandSender getConsoleSender();
	
	/**
	 * Gets the {@link Messenger} responsible for this server.
	 * 
	 * @return Messenger responsible for this server.
	 */
	public Messenger getMessenger();
	
	/**
	 * Gets the {@link HelpMap} providing help topics for this server.
	 * 
	 * @return The server's HelpMap.
	 */
	public HelpMap getHelpMap();
	
	/**
	 * Gets the message that is displayed on the server list
	 * 
	 * @return the servers MOTD
	 */
	public String getMotd();
	
	/**
	 * Gets the default message that is displayed when the server is stopped
	 * 
	 * @return the shutdown message
	 */
	public String getShutdownMessage();
	
	/**
	 * Gets the current warning state for the server
	 * 
	 * @return The configured WarningState
	 */
	public WarningState getWarningState();
}
