package com.chiorichan;

import java.sql.ResultSet;

public class CrossoverHandler
{
	public static String getSetting( String key, String idenifier, String dft )
	{
		try
		{
			SqlConnector db = ChioriFramework.getDatabase();
			
			ResultSet rs1 = db.query( "SELECT * FROM `settings_default` WHERE `key` = '" + key + "';" );
			
			if ( db.getRowCount( rs1 ) < 1 )
				return dft;
			
			ResultSet rs2 = db.query( "SELECT * FROM `settings_custom` WHERE `key` = '" + key + "' AND `owner` = '" + idenifier + "';" );
			
			if ( db.getRowCount( rs2 ) < 1 )
				return rs1.getString( "value" );
			
			return rs2.getString( "value" );
		}
		catch ( Exception e )
		{
			e.printStackTrace();
			return dft;
		}
	}
}
