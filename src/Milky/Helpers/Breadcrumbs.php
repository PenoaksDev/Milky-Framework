<?php

/*
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */

namespace Milky\Helpers;

use Milky\Facades\URL;
use Milky\Http\HttpFactory;

class Breadcrumbs
{
	private static $format = "<li><a href=\":url\">:title</a></li>";

	private static $crumbs = [];

	public static function setFormat( $format )
	{
		static::$format = $format;
	}

	public static function prepend( array $data )
	{
		array_unshift( static::$crumbs, $data );
	}

	public static function append( array $data )
	{
		static::$crumbs[] = $data;
	}

	public static function render( $parameters = [] )
	{
		$result = [];

		$route = HttpFactory::i()->router()->getCurrentRoute();

		$params = $parameters;// array_merge( $parameters, $route->parameters() );
		$actions = $route->getAction();

		if ( $breadcrumbs = $actions['crumbs'] )
		{
			$crumbs = [];

			foreach ( (array) $breadcrumbs as $crumb )
			{
				$segments = explode( '|', $crumb );
				$crumbs[] = [ 'title' => $segments[0], 'route' => isset( $segments[1] ) ? $segments[1] : null ];
			}

			static::$crumbs = array_merge( $crumbs, static::$crumbs );
		}

		foreach ( static::$crumbs as $crumb )
		{
			$title = $crumb['title'];
			$url = isset( $crumb['url'] ) ? $crumb['url'] : ( isset( $crumb['route'] ) ? URL::route( $crumb['route'], $params ) : "" );

			foreach ( $params as $key => $param )
				$title = str_replace( '{' . $key . '}', $param, $title );

			$replace = [':title', ':url'];
			$result[] = str_replace( $replace, [$title, $url], static::$format );
		}

		return implode( "\n", $result );
	}
}
