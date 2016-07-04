<?php
namespace Foundation\Autoload;

use RuntimeException;

class Paths
{
	static $paths = [];

	private static $builders = [
		'bootstrap' => 'base'
	];

	public static function set( array $paths, $value = null )
	{
		if ( !is_array( $paths ) )
			$paths = [$paths => $value];

		self::$paths = array_merge( self::$paths, $paths );

		foreach ( self::$paths as $k => $v )
			define( '___' . strtoupper( $k ) . '___', $v );

		if ( !file_exists( self::$paths['config'] ) )
			throw new RuntimeException( "The configuration directory '" . self::$paths['config'] . "' does not exist!" );
	}

	public static function get( $key )
	{
		if (! array_key_exists( 'base', self::$paths ) )
			throw new RuntimeException( "Base directory is not set." );
		if (! array_key_exists( $key, self::$paths ) )
		{
			/* if ( array_key_exists( $key, self::$builders ) )
			{
				$path = [];
				foreach( explode( '/', self::$builders[$key] ) as $p )
					$path .=  $p
			}
			else */
				self::$paths[$key] = self::$paths['base'] . __ . $key;
		}
		return self::$paths[$key];
	}
}
