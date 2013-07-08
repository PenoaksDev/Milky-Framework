package com.chiorichan;

import java.io.IOException;
import java.math.BigDecimal;
import java.util.Arrays;
import java.util.HashMap;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.eclipse.jetty.util.security.Credential.MD5;

public class APIServlet extends HttpServlet
{
	private static final long serialVersionUID = -01312013011626L;
	
	public static APIServlet instance = null;
	public static SqlConnector db1 = ChioriFramework.getDatabase();
	
	public APIServlet()
	{
		super();
		
		instance = this;
		// db1.init( "chiori", "chiori", "**********", "applebloom.co" );
		
		// Thread thrd = new Thread( new ServletHelper() );
		// thrd.start();
		
		// SMSHandler.sendSMS( Arrays.asList( "7089123702" ), "Hello World!!!" );
	}
	
	/**
	 * Get the active instance of this main class.
	 */
	public static APIServlet getInstance()
	{
		return instance;
	}
	
	/**
	 * Gets the active instance of the database class.
	 */
	public static SqlConnector getDatabase()
	{
		return APIServlet.db1;
	}
	
	protected void doGet( HttpServletRequest request, HttpServletResponse response ) throws ServletException, IOException
	{
		// Save these in case the user needs to contact support.
		String clientId = MD5.digest( request.getRemoteAddr() );
		String requestId = MD5.digest( clientId + System.currentTimeMillis() );
		
		Long timestamp = (Long) request.getAttribute( "_ts" );
		String accessKey = (String) request.getAttribute( "_ak" );
		String securityToken = (String) request.getAttribute( "_st" );
		String reaccessToken = (String) request.getAttribute( "_rt" );
		
		String action = (String) request.getAttribute( "action" );
		
		if ( isNull( timestamp ) )
		{
			panic( response, 400, "Missing Parameters!" );
			return;
		}
		
		// Is the time stamp within 30 seconds.
		if ( timestamp + 30000 < System.currentTimeMillis() )
		{
			panic( response, 400, "Timestamp Expired!" );
			return;
		}
		
		if ( isNull( accessKey ) || isNull( securityToken ) )
		{
			if ( isNull( reaccessToken ) )
			{
				panic( response, 400, "Missing Parameters!" );
				return;
			}
			else
			{
				// Pull reaccess token from DB
				HashMap<String, Object> r = db1.selectOne( "apikeys", Arrays.asList( "reaccessToken" ), Arrays.asList( reaccessToken ) );
				
				// Was it locatable in the database?
				if ( isNull( r ) )
				{
					panic( response, 400, "Reaccess Token was invalid!" );
					return;
				}
				
				// Is it expired? It's only good for 5 minutes.
				if ( System.currentTimeMillis() - 600000 > Long.parseLong( (String) r.get( "lastUsed" ) ) )
				{
					panic( response, 400, "Reaccess Token has expired!" );
					return;
				}
				
				if ( action.equalsIgnoreCase( "ping" ) )
				{
					// Renew the reaccess token for 5 more minutes if this is under 7 tries else error.
					if ( Integer.parseInt( (String) r.get( "reaccessRenewal" ) ) >= 6 )
					{
						panic( response, 400, "You have reached the max number of reaccess key renewals." );
						return;
					}
					else
					{
						db1.update( "apikeys", Arrays.asList( "reaccessRenewal" ), Arrays.asList( ( (String) r.get( "reaccessRenewal" ) ) + 1 ), Arrays.asList( "reaccessToken" ), Arrays.asList( r.get( reaccessToken ) ) );
					}
				}
			}
		}
		else
		{
			HashMap<String, Object> r = db1.selectOne( "apikeys", Arrays.asList( "accessKey" ), Arrays.asList( accessKey ) );
			
			String secretKey = (String) r.get( "secretKey" );
			
			if ( MD5.digest( timestamp + accessKey + secretKey ) != securityToken )
			{
				panic( response, 400, "Security Token was Invalid!" );
				return;
			}
			
			reaccessToken = MD5.digest( request.getRemoteAddr() + securityToken + timestamp + accessKey + "ThisIsSomeRandomStringJustForTheFunOfIt!!!" );
			
			// Insert reaccess token into database.
			db1.update( "apikeys", Arrays.asList( "lastIp", "lastUsed", "reaccessToken", "reaccessRenewal" ), Arrays.asList( request.getRemoteAddr(), System.currentTimeMillis(), 0 ), Arrays.asList( "accessKey", reaccessToken ), Arrays.asList( r.get( accessKey ) ) );
		}
		
		// Continue from here.
	}
	
	public Boolean isNull( Object o )
	{
		if ( o == null )
			return true;
		
		return false;
	}
	
	private void panic( HttpServletResponse response, int status, String msg ) throws ServletException, IOException
	{
		response.sendError( status, msg );
	}
	
	private void panic( HttpServletResponse response, int status ) throws ServletException, IOException
	{
		panic( response, status, null );
	}
	
	protected void doPost( HttpServletRequest request, HttpServletResponse response ) throws ServletException, IOException
	{
		
	}
	
	public static double round( double unrounded, int precision, int roundingMode )
	{
		BigDecimal bd = new BigDecimal( unrounded );
		BigDecimal rounded = bd.setScale( precision, roundingMode );
		return rounded.doubleValue();
	}
	
	public static double round( double unrounded, int precision )
	{
		return round( unrounded, precision, BigDecimal.ROUND_HALF_UP );
	}
}
