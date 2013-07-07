package com.chiorichan;

import java.io.IOException;
import java.io.PrintWriter;
import java.util.HashMap;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import com.chiorichan.OurWebsocketServlet.RewardsWebSocket;


public class SystemStatus extends HttpServlet
{
	private static final long serialVersionUID = -01312013011626L;
	
	protected void doGet( HttpServletRequest request, HttpServletResponse response ) throws ServletException, IOException
	{
		PrintWriter op = response.getWriter();
		
		op.println( "<table id=\"\" class=\"altrowstable\">" );
		op.println( "	<tbody>" );
		op.println( "		<tr>" );
		op.println( "			<th></th>" );
		op.println( "			<th>Unique ID</th>" );
		op.println( "			<th>Last IP</th>" );
		op.println( "			<th>Location</th>" );
		op.println( "			<th>State</th>" );
		op.println( "		</tr>" );
		
		int l = 0;
		
		for ( RewardsWebSocket rws : OurWebsocketServlet._members )
		{
			HashMap<String, String> loc = ( rws.myAsset == null ) ? new HashMap<String, String>() : rws.myAsset.getLocationArray();
			
			op.println( "		<tr id=\"uuid_" + rws.deviceId + "\" rel=\"\" class=\"" + ( ( (l & 1) == 0 ) ? "oddrowcolor" : "evenrowcolor" ) + "\">" );
			op.println( "			<td id=\"col_0\" class=\"\">" );
			op.println( "				<img src=\"http://images.applebloom.co/elements/tablet_icon.png\"></td>" );
			op.println( "			<td id=\"col_1\" class=\"\"><a href=\"http://accounts.applebloom.co/pages/misc/device_history?deviceId=" + rws.deviceId + "\">" + rws.deviceId + "</a></td>" );
			op.println( "			<td id=\"col_2\" class=\"\">" + rws.ip_addr + "</td>" );
			op.println( "			<td id=\"col_3\" class=\"\">" + rws.myAsset.locationId + " - " + loc.get( "title" ) + "</td>" );
			op.println( "			<td id=\"col_4\" class=\"\">" );
			op.println( "			<img src=\"http://images.applebloom.co/elements/p_success.png\" width=\"16\" height=\"16\"></td>" );
			op.println( "		</tr>" );
			
			l++;
		}
		
		op.println( "	</tbody>" );
		op.println( "</table>" );
	}
}
