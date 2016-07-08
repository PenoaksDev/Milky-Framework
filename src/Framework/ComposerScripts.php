<?php
namespace Penoaks\Framework;

use \Composer\Script\Event;

/*
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */

class ComposerScripts
{
	/**
	 * Handle the post-install Composer event.
	 *
	 * @param  Event $event
	 * @return void
	 */
	public static function postInstall( Event $event )
	{
		require_once $event->getComposer()->getConfig()->get( 'vendor-dir' ) . '/autoload.php';
		static::clearCompiled();
	}

	/**
	 * Handle the post-update Composer event.
	 *
	 * @param  Event $event
	 * @return void
	 */
	public static function postUpdate( Event $event )
	{
		require_once $event->getComposer()->getConfig()->get( 'vendor-dir' ) . '/autoload.php';
		static::clearCompiled();
	}

	/**
	 * Clear the cached Framework bootstrapping files.
	 *
	 * @return void
	 */
	protected static function clearCompiled()
	{
		$framework = new Application( getcwd() );

		if ( file_exists( $compiledPath = $framework->getCachedCompilePath() ) )
		{
			@unlink( $compiledPath );
		}

		if ( file_exists( $servicesPath = $framework->getCachedServicesPath() ) )
		{
			@unlink( $servicesPath );
		}
	}
}
