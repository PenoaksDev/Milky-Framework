package com.chiorichan;

import java.net.ConnectException;

import org.apache.http.HttpHost;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.conn.params.ConnRoutePNames;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.protocol.BasicHttpContext;
import org.apache.http.protocol.HttpContext;
import org.apache.http.util.EntityUtils;

public class HTTPGetter
{
	private static HttpContext localContext = new BasicHttpContext();
	
	private static HttpHost proxy = null;
	
	public static String getUrlWithProxy( String url, String params )
	{
		if ( params.equals( "" ) )
			params = "/";
		
		DefaultHttpClient httpclient = new DefaultHttpClient();
		
		WebClientSecurity.allowAllSSL( httpclient );
		
		try
		{
			if ( proxy != null )
				httpclient.getParams().setParameter( ConnRoutePNames.DEFAULT_PROXY, proxy );
			
			HttpHost target = new HttpHost( url.trim(), 80, "http" );
			HttpGet req = new HttpGet( params );
			req.setHeader( "User-Agent", "Mozilla/4.0 (MSIE 6.0; Windows NT 5.0)" );
			
			System.out.println( "executing request to \"" + target + params + "\" via \"" + proxy + "\"" );
			
			String response = EntityUtils.toString( httpclient.execute( target, req ).getEntity(), "UTF-8" );
			
			return response;
		}
		catch ( ConnectException e )
		{
			strikeListProxy();
		}
		catch ( Exception e )
		{
			e.printStackTrace();
		}
		finally
		{
			httpclient.getConnectionManager().shutdown();
		}
		
		return "";
	}
	
	public static String getUrl( String url, String params )
	{
		if ( params.equals( "" ) )
			params = "/";
		
		DefaultHttpClient httpclient = new DefaultHttpClient();
		
		WebClientSecurity.allowAllSSL( httpclient );
		
		try
		{
			HttpHost target = new HttpHost( url.trim(), 80, "http" );
			HttpGet req = new HttpGet( params );
			req.setHeader( "User-Agent", "Mozilla/4.0 (MSIE 6.0; Windows NT 5.0)" );
			
			System.out.println( "[HTTP_GET] Executing request to \"" + target + params + "\n" );
			
			String response = EntityUtils.toString( httpclient.execute( target, req ).getEntity(), "UTF-8" );
			
			return response;
		}
		catch ( Exception e )
		{
			e.printStackTrace();
		}
		finally
		{
			httpclient.getConnectionManager().shutdown();
		}
		
		return "";
	}
	
	public static void strikeListProxy()
	{
		
	}
	
	public static void setProxy( String addr, int port )
	{
		proxy = new HttpHost( addr, port, "http" );
	}
	
	public static void nextProxy()
	{
		setProxy( "198.154.114.100", 8080 );
	}
}
