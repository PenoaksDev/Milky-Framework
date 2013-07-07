package com.chiorichan;

import java.io.IOException;
import java.security.cert.CertificateException;
import java.security.cert.X509Certificate;

import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLException;
import javax.net.ssl.SSLSession;
import javax.net.ssl.SSLSocket;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;

import org.apache.http.client.HttpClient;
import org.apache.http.conn.scheme.Scheme;
import org.apache.http.conn.ssl.SSLSocketFactory;
import org.apache.http.conn.ssl.X509HostnameVerifier;
import org.apache.http.impl.client.DefaultHttpClient;

public class WebClientSecurity
{
	public static void allowAllSSL ( HttpClient httpClient )
	{
		try
		{
			SSLContext ctx = SSLContext.getInstance( "TLS" );
			
			X509TrustManager tm = new X509TrustManager()
			{
				
				public void checkClientTrusted( X509Certificate[] xcs, String string ) throws CertificateException
				{
				}
				
				public void checkServerTrusted( X509Certificate[] xcs, String string ) throws CertificateException
				{
				}
				
				public X509Certificate[] getAcceptedIssuers()
				{
					return null;
				}
			};
			
			X509HostnameVerifier verifier = new X509HostnameVerifier()
			{
				public void verify( String paramString, SSLSocket paramSSLSocket ) throws IOException
				{
					
				}
				
				public void verify( String paramString, X509Certificate paramX509Certificate ) throws SSLException
				{
					
				}
				
				public void verify( String paramString, String[] paramArrayOfString1, String[] paramArrayOfString2 ) throws SSLException
				{
					
				}
				
				public boolean verify( String string, SSLSession ssls )
				{
					return true;
				}
			};
			
			TrustManager[] trustAllCerts = new TrustManager[] { tm };
			ctx.init(null, trustAllCerts, new java.security.SecureRandom());
			SSLSocketFactory sf = new SSLSocketFactory( ctx, SSLSocketFactory.ALLOW_ALL_HOSTNAME_VERIFIER);
			Scheme sch = new Scheme("https", 443, sf);
			//DefaultHttpClient httpclient = new DefaultHttpClient();
			httpClient.getConnectionManager().getSchemeRegistry().register(sch);
		}
		catch ( Exception ex )
		{
			ex.printStackTrace();
		}
	}
}
