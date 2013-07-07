package com.chiorichan;

import java.io.IOException;
import java.io.PrintWriter;
import java.util.HashMap;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import com.chiorichan.OurWebsocketServlet.RewardsWebSocket;


public class serviceCheck extends HttpServlet
{
	private static final long serialVersionUID = -01312013011626L;
	
	protected void doGet( HttpServletRequest request, HttpServletResponse response ) throws ServletException, IOException
	{
		response.getWriter().println( "<p class=\"success show\">The Apple Bloom Servlet is online and operational.</p>" );
	}
}