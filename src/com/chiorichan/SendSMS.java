package com.chiorichan;

import java.io.IOException;
import java.io.PrintWriter;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.List;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import com.chiorichan.OurWebsocketServlet.RewardsWebSocket;
import com.google.common.base.Splitter;
import com.google.common.collect.Lists;


public class SendSMS extends HttpServlet
{
	private static final long serialVersionUID = -01312013011626L;
	
	protected void doPost( HttpServletRequest request, HttpServletResponse response ) throws ServletException, IOException
	{
		doGet( request, response );
	}
	
	protected void doGet( HttpServletRequest request, HttpServletResponse response ) throws ServletException, IOException
	{
		PrintWriter op = response.getWriter();
		SqlConnector db = ChioriFramework.sql;
		
		if ( request.getParameter( "list" ) == null || request.getParameter( "list" ).equals( "" ) )
		{
			op.println("It seems there was a problem reading the list parameter. Critical Error!");
			return;
		}
		
		List<String> list = Lists.newArrayList(Splitter.on('|').split( request.getParameter( "list" ) ));
		
		for ( String loc : list )
		{
			if ( !loc.equals( "+" ) && !loc.equals( "" ) )
			{
				HashMap<String, Object> result = db.selectOne( "locations", "locID", loc );
				
				if ( result != null )
				{
					ResultSet rs = db.query( "SELECT * FROM `contacts` WHERE `list` like '%" + loc + "%' OR `list` like '%ALL%' OR `list` like '%" + result.get("acctID") + "%'" );
					
					String title = (String) result.get( "title" );
					
					String msg = request.getParameter( "msg" ).replace( "REPLACETHIS", title );
					
					try
					{
						if ( db.getRowCount( rs ) > 0 )
						{
							do
							{
								SMSHandler.addSMS( rs.getString( "mobile_no" ), msg, (String) result.get( "keyword" ) );
								
								op.println("Sent \"" + msg + "\" to \"" + rs.getString( "mobile_no" ) + "\".");
							}
							while ( rs.next() );
						}
					}
					catch ( SQLException e )
					{
						e.printStackTrace();
					}
				}
				else
				{
					op.println("It was a problem finding location " + loc + ".");
				}
			}
		}
	}
}