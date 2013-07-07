package com.chiorichan;

import java.io.IOException;
import java.io.PrintWriter;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Date;
import java.util.HashMap;
import java.util.List;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import com.chiorichan.OurWebsocketServlet.RewardsWebSocket;
import com.google.common.base.Splitter;
import com.google.common.collect.Lists;
import com.google.i18n.phonenumbers.NumberParseException;
import com.google.i18n.phonenumbers.PhoneNumberUtil;
import com.google.i18n.phonenumbers.PhoneNumberUtil.PhoneNumberFormat;
import com.google.i18n.phonenumbers.Phonenumber.PhoneNumber;


public class StatusSMS extends HttpServlet
{
	private static final long serialVersionUID = -01312013011626L;
	
	protected void doPost( HttpServletRequest request, HttpServletResponse response ) throws ServletException, IOException
	{
		doGet( request, response );
	}
	
	protected void doGet( HttpServletRequest request, HttpServletResponse response ) throws ServletException, IOException
	{
		PrintWriter w = response.getWriter();
		response.addHeader("Access-Control-Allow-Origin", "*");
		String lastCycle = new SimpleDateFormat("yyyy-MM-dd HH:mm:SS").format( new Date( SMSHandler.lastCycle ) );
		String status;
		if ( ServletHelper.cycleActive )
		{
			status = "Processing " + SMSHandler.completed + " of " + (SMSHandler.smsq.size() + SMSHandler.completed) + " Message(s) at " + ( SMSHandler.completed / (System.currentTimeMillis() - ServletHelper.lastStart) ) + " per second.";
		}
		else
		{
			if ( SMSHandler.smsq.size() > 0 )
			{
				status = "There is currently " + SMSHandler.smsq.size() + " message(s) pending... The cycle will begin shortly...";
			}
			else
			{
				status = "There are currently no cycles active...";
			}
		}
		
		if ( SMSHandler.lastCycle == 0 )
			lastCycle = "N/A";
		
		if ( request.getParameter( "ajax" ) != null && request.getParameter( "ajax" ).equalsIgnoreCase( "true" ) )
		{
			response.setContentType( "application/json" );
			response.setStatus( HttpServletResponse.SC_OK );
			w.println( "{\"status\": \"" + status + "\", \"sms\": \"" + SMSHandler.lastSMS + "\", \"mobile\": \"" + formatPhoneNumber( SMSHandler.lastNo ) + "\", \"result\": \"" + SMSHandler.lastResult + "\", \"cycle\": \"" + lastCycle + "\"}" );
			return;
		}
		
		w.println( "<style>label { display: inline-block; width: 250px; clear: left; }</style>" );
		
		w.println( "<div class=\"rowElem\"><label>Status:</label><span id=\"status\">" + status + "</span></div>" );
		w.println( "<div class=\"rowElem\"><label>Last Message:</label><span id=\"sms\">" + SMSHandler.lastSMS + "</span></div>" );
		w.println( "<div class=\"rowElem\"><label>Last Mobile #:</label><span id=\"mobile\">" + formatPhoneNumber( SMSHandler.lastNo ) + "</span></div>" );
		w.println( "<div class=\"rowElem\"><label>Last Result:</label><span id=\"result\">" + SMSHandler.lastResult + "</span></div>" );
		w.println( "<div class=\"rowElem\"><label>Last Cycle:</label><span id=\"cycle\">" + lastCycle + "</span></div>" );
	}
	
	public String formatPhoneNumber( String rawPhoneNumber )
	{
		PhoneNumberUtil putil = PhoneNumberUtil.getInstance();
		try
		{
			PhoneNumber num = putil.parse( rawPhoneNumber, "US" );
			return putil.format( num, PhoneNumberFormat.NATIONAL );
		}
		catch ( NumberParseException e )
		{
			return rawPhoneNumber;
		}
	}
}
