package com.chiorichan;

import java.io.PrintStream;
import java.util.Arrays;
import java.util.EnumMap;
import java.util.HashMap;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;

import jline.Terminal;
import jline.console.ConsoleReader;

import org.fusesource.jansi.Ansi;
import org.fusesource.jansi.Ansi.Attribute;

import co.applebloom.apps.rewards.Asset;

import com.chiorichan.OurWebsocketServlet.RewardsWebSocket;
import com.google.common.base.Strings;

public class InteractiveConsole
{
	public static Logger log = Logger.getLogger( "AppleBloomServer" );
	
	public static ConsoleReader reader;
	public static Terminal terminal;
	public static Map<ChatColor, String> replacements = new EnumMap<ChatColor, String>( ChatColor.class );
	public static ChatColor[] colors = ChatColor.values();
	public Boolean isRunning = true;
	public static RewardsWebSocket selected = null;
	
	private static int lineCount = 999;
	
	public InteractiveConsole()
	{
		try
		{
			replacements.put( ChatColor.BLACK, Ansi.ansi().fg( Ansi.Color.BLACK ).boldOff().toString() );
			replacements.put( ChatColor.DARK_BLUE, Ansi.ansi().fg( Ansi.Color.BLUE ).boldOff().toString() );
			replacements.put( ChatColor.DARK_GREEN, Ansi.ansi().fg( Ansi.Color.GREEN ).boldOff().toString() );
			replacements.put( ChatColor.DARK_AQUA, Ansi.ansi().fg( Ansi.Color.CYAN ).boldOff().toString() );
			replacements.put( ChatColor.DARK_RED, Ansi.ansi().fg( Ansi.Color.RED ).boldOff().toString() );
			replacements.put( ChatColor.DARK_PURPLE, Ansi.ansi().fg( Ansi.Color.MAGENTA ).boldOff().toString() );
			replacements.put( ChatColor.GOLD, Ansi.ansi().fg( Ansi.Color.YELLOW ).boldOff().toString() );
			replacements.put( ChatColor.GRAY, Ansi.ansi().fg( Ansi.Color.WHITE ).boldOff().toString() );
			replacements.put( ChatColor.DARK_GRAY, Ansi.ansi().fg( Ansi.Color.BLACK ).bold().toString() );
			replacements.put( ChatColor.BLUE, Ansi.ansi().fg( Ansi.Color.BLUE ).bold().toString() );
			replacements.put( ChatColor.GREEN, Ansi.ansi().fg( Ansi.Color.GREEN ).bold().toString() );
			replacements.put( ChatColor.AQUA, Ansi.ansi().fg( Ansi.Color.CYAN ).bold().toString() );
			replacements.put( ChatColor.RED, Ansi.ansi().fg( Ansi.Color.RED ).bold().toString() );
			replacements.put( ChatColor.LIGHT_PURPLE, Ansi.ansi().fg( Ansi.Color.MAGENTA ).bold().toString() );
			replacements.put( ChatColor.YELLOW, Ansi.ansi().fg( Ansi.Color.YELLOW ).bold().toString() );
			replacements.put( ChatColor.WHITE, Ansi.ansi().fg( Ansi.Color.WHITE ).bold().toString() );
			replacements.put( ChatColor.MAGIC, Ansi.ansi().a( Attribute.BLINK_SLOW ).toString() );
			replacements.put( ChatColor.BOLD, Ansi.ansi().a( Attribute.UNDERLINE_DOUBLE ).toString() );
			replacements.put( ChatColor.STRIKETHROUGH, Ansi.ansi().a( Attribute.STRIKETHROUGH_ON ).toString() );
			replacements.put( ChatColor.UNDERLINE, Ansi.ansi().a( Attribute.UNDERLINE ).toString() );
			replacements.put( ChatColor.ITALIC, Ansi.ansi().a( Attribute.ITALIC ).toString() );
			replacements.put( ChatColor.RESET, Ansi.ansi().a( Attribute.RESET ).fg( Ansi.Color.DEFAULT ).toString() );
			
			String jline_UnsupportedTerminal = new String( new char[] { 'j', 'l', 'i', 'n', 'e', '.', 'U', 'n', 's', 'u', 'p', 'p', 'o', 'r', 't', 'e', 'd', 'T', 'e', 'r', 'm', 'i', 'n', 'a', 'l' } );
			String jline_terminal = new String( new char[] { 'j', 'l', 'i', 'n', 'e', '.', 't', 'e', 'r', 'm', 'i', 'n', 'a', 'l' } );
			
			Boolean useJline = !( jline_UnsupportedTerminal ).equals( System.getProperty( jline_terminal ) );
			
			if ( !useJline )
			{
				System.setProperty( jline.TerminalFactory.JLINE_TERMINAL, jline.UnsupportedTerminal.class.getName() );
			}
			
			try
			{
				this.reader = new ConsoleReader( System.in, System.out );
				this.reader.setExpandEvents( false ); // Avoid parsing exceptions for uncommonly used event designators
			}
			catch ( Exception e )
			{
				try
				{
					// Try again with jline disabled for Windows users without C++ 2008 Redistributable
					System.setProperty( "jline.terminal", "jline.UnsupportedTerminal" );
					System.setProperty( "user.language", "en" );
					this.reader = new ConsoleReader( System.in, System.out );
					this.reader.setExpandEvents( false );
				}
				catch ( java.io.IOException ex )
				{
					// Logger.getLogger( MinecraftServer.class.getName() ).log( Level.SEVERE, null, ex );
				}
			}
			
			reader.setPrompt( "?> " );
			terminal = reader.getTerminal();
			
			ThreadCommandReader threadcommandreader = new ThreadCommandReader( this );
			
			threadcommandreader.setDaemon( true );
			threadcommandreader.start();
			ConsoleLogManager.init( this );
			
			System.setOut( new PrintStream( new LoggerOutputStream( log, Level.INFO ), true ) );
			System.setErr( new PrintStream( new LoggerOutputStream( log, Level.SEVERE ), true ) );
			
			sendMessage( ChatColor.RED + "Welcome to the Apple Bloom Server Interactive Console\n" );
		}
		catch ( Throwable t )
		{
			t.printStackTrace();
		}
	}
	
	public void issueCommand( String cmd )
	{
		String arr[] = cmd.split( " ", 2 );
		cmd = arr[0].toLowerCase();
		String data = ( arr.length > 1 ) ? arr[1].trim() : null;
		
		if ( cmd.equalsIgnoreCase( "quit" ) || cmd.equalsIgnoreCase( "exit" ) || cmd.equalsIgnoreCase( "stop" ) )
		{
			sendMessage( "Apple Bloom Server is now Shutting Down!!!" );
			// reader.getTerminal().restore();
			System.exit( 0 );
		}
		else if ( cmd.equals( "ping" ) )
		{
			sendMessage( "&4PONG!" );
		}
		else if ( cmd.equals( "whois" ) )
		{
			if ( data.isEmpty() )
			{
				sendMessage( "&4You must provide a parital or whole client id to perform this command!" );
			}
			else
			{
				RewardsWebSocket rws = OurWebsocketServlet.search( data );
				
				if ( rws == null )
				{
					sendMessage( "&4That query did not return any results!" );
				}
				else
				{
					sendMessage( ChatColor.AQUA + rws.toString() );
				}
			}
		}
		else if ( cmd.equals( "reboot" ) )
		{
			if ( selected == null )
			{
				sendMessage( "&4You must provide a parital or whole client id to perform this command!" );
			}
			else
			{
				sendMessage( "&6Attempted to send the restart command to the device. Please wait for a full restart." );
				try
				{
					selected.sendMessage( "REBO" );
				}
				catch ( Exception e )
				{
					e.printStackTrace();
				}
			}
		}
		else if ( cmd.equals( "invite" ) )
		{
			if ( data == null )
			{	
				sendMessage( "&6You must enter a phone number and target keyword. ie. invite <mobile#> <keyword> [invite message]" );
			}
			else
			{
				String[] params = data.split( " ", 3 );
				HashMap<String, String> result = null;
				
				if ( params.length > 2 )
				{
					result = SMSHandler.inviteMobile( Arrays.asList( params[0] ), params[1], params[2] );
				}
				else if ( params.length > 1 )
				{
					result = SMSHandler.inviteMobile( Arrays.asList( params[0] ), params[1] );
				}
				else
				{
					sendMessage( "&6You must enter a phone number and target keyword. ie. invite <mobile#> <keyword> [invite message]" );
				}
				
				if ( result != null )
				{
					sendMessage( "&6We tried to invite \"" + result.get( "mobile" ) + "\" to group \"" + result.get( "target" ) + "\" with result \"" + result.get( "resmsg" ) + "\"" );
				}
			}
		}
		else if ( cmd.equals( "chlog" ) )
		{
			if ( data == null )
			{	
				sendMessage( "&4You must enter a valid log level. i.e. SEVERE, DEBUG, 1000!" );
			}
			else
			{
				try
				{
					Level l = Level.parse( data );
					
					if ( l == null )
					{
						sendMessage( "&4Sorry, We could not find a log level that matched that!" );
					}
					else
					{
						log.setLevel( l );
						sendMessage( "&bThe log level has been changed to " + l.getName() + "!" );
					}
				}
				catch ( IllegalArgumentException e )
				{
					sendMessage( "&4Sorry, We could not find a log level that matched that!" );
				}
			}
		}
		else if ( cmd.equals( "send" ) )
		{
			if ( data.isEmpty() )
			{
				sendMessage( "&4You must provide a parital or whole client id to perform this command!" );
			}
			else
			{
				// TODO: Send Command to Device!
				
				RewardsWebSocket rws = OurWebsocketServlet.search( data );
				
				if ( rws == null )
				{
					sendMessage( "&4That query did not return any results!" );
				}
				else
				{
					sendMessage( ChatColor.AQUA + rws.toString() );
				}
			}
		}
		else if ( cmd.equals( "list" ) )
		{
			int x = 0;
			String lst = "&4Apple Bloom Servlet Connections List";
			
			for ( RewardsWebSocket rws : OurWebsocketServlet._members )
			{
				lst += "\n&b" + x + ": " + rws.toString();
				x++;
			}
			
			sendMessage( lst );
		}
		else if ( cmd.equals( "save" ) )
		{
			OurWebsocketServlet.assetsHandler.saveAll();
			sendMessage( ChatColor.AQUA + "All Assets Have Been Saved to the Database!" );
		}
		else if ( cmd.equals( "reload" ) )
		{
			OurWebsocketServlet.assetsHandler.reloadAll();
			sendMessage( ChatColor.AQUA + "All Assets Have Been Reloaded into Memory!" );
		}
		else if ( cmd.equals( "select" ) )
		{
			if ( data.isEmpty() )
			{
				sendMessage( "&4You must provide a parital or whole client id to perform this command!" );
			}
			else
			{
				RewardsWebSocket rws = OurWebsocketServlet.search( data );
				
				if ( rws == null )
				{
					sendMessage( "&4That query did not return any results!" );
				}
				else
				{
					selected = rws;
					sendMessage( ChatColor.AQUA + "You have selected \"" + rws.toString() + "\"" );
				}
			}
		}
		else if ( cmd.equals("updateall") )
		{
			for ( RewardsWebSocket rws : OurWebsocketServlet._members )
			{
				if ( rws != null && rws.myAsset.type.equals( "video" ) )
				{
					try
					{
						rws.updatePlaylist();
					}
					catch ( Exception e )
					{
						e.printStackTrace();
					}
				}
			}
			sendMessage( ChatColor.AQUA + "We attempted to update all video device playlists!" );
		}
		else if ( cmd.equals("update") )
		{
			if ( selected == null )
			{
				sendMessage( "&4You must select a client to continue. Use \"select [query]\"!" );
			}
			else
			{
				try
				{
					selected.updatePlaylist();
				}
				catch ( Exception e )
				{
					e.printStackTrace();
				}
				
				sendMessage( ChatColor.AQUA + "We attempted to update that devices video playlist!" );
			}
		}
		else if ( cmd.equals("kick") )
		{
			if ( data.isEmpty() )
			{
				sendMessage( "&4You must provide a parital or whole client id to perform this command!" );
			}
			else
			{
				RewardsWebSocket rws = OurWebsocketServlet.search( data );
				
				if ( rws == null )
				{
					sendMessage( "&4That query did not return any results!" );
				}
				else
				{
					rws.disconnect();
					sendMessage( ChatColor.AQUA + "Asset has been kicked from the server!" );
				}
			}
		}
		else
		{
			sendMessage( "&4Unknown Command or Keyword, Please Try Again. :D :D :D" );
		}
	}
	
	private static void printHeader()
	{
		if ( lineCount > 40 )
		{
			lineCount = 0;
			log( Level.FINE, ChatColor.GOLD + "<CLIENT ID>     <MESSAGE>" );
		}
		
		lineCount++;
	}
	
	public static void debug( String msg )
	{
		log( Level.FINE, msg );
	}
	
	public static void info( String msg )
	{
		log( Level.INFO, msg );
	}
	
	public static void warning( String msg )
	{
		log( Level.WARNING, msg );
	}
	
	public static void severe( String msg )
	{
		log( Level.SEVERE, msg );
	}
	
	public static void log( Level l, String client, String msg )
	{
		if ( client.length() < 15 )
		{
			client = client + Strings.repeat( " ", 15 - client.length() );
		}
		
		printHeader();
		
		log( l, "&5" + client + " &a" + msg );
	}
	
	public static void log( Level l, String msg )
	{
		if ( terminal.isAnsiSupported() )
		{
			msg = ChatColor.translateAlternateColorCodes( '&', msg ) + ChatColor.RESET;
			
			String result = ChatColor.translateAlternateColorCodes( '&', msg );
			for ( ChatColor color : colors )
			{
				if ( replacements.containsKey( color ) )
				{
					msg = msg.replaceAll( "(?i)" + color.toString(), replacements.get( color ) );
				}
				else
				{
					msg = msg.replaceAll( "(?i)" + color.toString(), "" );
				}
			}
		}
		
		log.log( l, msg );
	}
	
	public void sendMessage( String message )
	{
		if ( terminal.isAnsiSupported() )
		{
			String result = ChatColor.translateAlternateColorCodes( '&', message );
			for ( ChatColor color : colors )
			{
				if ( replacements.containsKey( color ) )
				{
					result = result.replaceAll( "(?i)" + color.toString(), replacements.get( color ) );
				}
				else
				{
					result = result.replaceAll( "(?i)" + color.toString(), "" );
				}
			}
			System.out.print( result + Ansi.ansi().reset().toString() );
			//log( Level.INFO, result + Ansi.ansi().reset().toString() );
		}
		else
		{
			sendRawMessage( message );
		}
	}
	
	public void sendRawMessage( String message )
	{
		System.out.print( ChatColor.stripColor( message ) );
		//log( Level.ALL, ChatColor.stripColor( message ) );
	}
	
	public void sendMessage( String[] messages )
	{
		for ( String message : messages )
		{
			sendMessage( message );
		}
	}
}
