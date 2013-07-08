package com.chiorichan.scheduler;

@SuppressWarnings( "serial" )
public class UnhandledException extends RuntimeException
{
	private final String message;
	
	/**
	 * Constructs a new AuthorNagException based on the given Exception
	 * 
	 * @param message
	 *           Brief message explaining the cause of the exception
	 */
	public UnhandledException(final String message, Throwable thrown)
	{
		this.message = message;
	}

	@Override
	public String getMessage()
	{
		return message;
	}
}
