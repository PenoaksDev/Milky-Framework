package com.chiorichan;

import java.io.ByteArrayInputStream;

import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;

import org.apache.http.client.CookieStore;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.client.methods.HttpUriRequest;
import org.apache.http.client.protocol.ClientContext;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.client.BasicCookieStore;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.protocol.BasicHttpContext;
import org.apache.http.protocol.HttpContext;
import org.apache.http.util.EntityUtils;
import org.w3c.dom.Document;

public class XMLParser
{
	static String result = null;
	static Exception exception = null;
	static CookieStore cookieStore = new BasicCookieStore();
	
	public static Document getFromUrl( final String urlPath ) throws Exception
	{
		return getFromUrl( urlPath, false );
	}
	
	public static Document getFromUrl( final String urlPath, final Boolean postRequest ) throws Exception
	{
		try
		{
			Thread trd = new Thread( new Runnable()
			{
				@Override
				public void run()
				{
					try
					{
						HttpContext localContext = new BasicHttpContext();
						localContext.setAttribute( ClientContext.COOKIE_STORE, cookieStore );
						
						String arguments = "";
						String httpURL = urlPath.trim();
						
						if ( httpURL.contains( "?" ) )
						{
							arguments = httpURL.substring( httpURL.indexOf( "?" ) + 1 );
							httpURL = httpURL.substring( 0, httpURL.indexOf( "?" ) );
						}
						
						if ( !arguments.isEmpty() )
							arguments += "&";
						
						InteractiveConsole.info( "&5Getting DATA from URL \"" + httpURL + "\" with params \"" + arguments + "\"" );
						
						DefaultHttpClient httpclient = new DefaultHttpClient();
						
						HttpUriRequest method = null;
						
						if ( postRequest )
						{
							method = new HttpPost( httpURL );
							StringEntity params = new StringEntity( arguments );
							( (HttpPost) method).setEntity( params );
						}
						else
						{
							method = new HttpGet( httpURL + httpURL );
						}
						
						method.setHeader( "Content-Type", "application/atom+xml" );
						
						String response = EntityUtils.toString( httpclient.execute( method, localContext ).getEntity(), "UTF-8" );
						
						XMLParser.result = response;
					}
					catch ( Exception e )
					{
						XMLParser.exception = e;
					}
				}
			} );
			
			result = null;
			exception = null;
			trd.start();
			
			trd.join( 5000 );
			
			if ( exception != null )
				throw exception;
			
			if ( result == null )
				return null;
			
			InteractiveConsole.info( "Got Data: " + result );
			
			DocumentBuilderFactory dbFactory = DocumentBuilderFactory.newInstance();
			DocumentBuilder dBuilder = dbFactory.newDocumentBuilder();
			Document doc = dBuilder.parse( new ByteArrayInputStream( result.trim().getBytes() ) );
			
			doc.getDocumentElement().normalize();
			
			return doc;
		}
		catch ( Exception e )
		{
			throw e;
		}
	}
}
