package com.chiorichan;

import java.io.IOException;

import jline.console.ConsoleReader;

public class ThreadCommandReader extends Thread
{
	public InteractiveConsole server;
	
	ThreadCommandReader(InteractiveConsole ic)
	{
		server = ic;
	}
	
	public void run()
	{
		ConsoleReader bufferedreader = server.reader;
		String s;
		
		try
		{
			while ( server.isRunning )
			{
				s = bufferedreader.readLine( ">", null );
				
				if ( s != null )
					server.issueCommand( s );
			}
		}
		catch ( IOException ioexception )
		{
			//server.log( java.util.logging.Level.SEVERE, null, ioexception );
		}
	}
}
