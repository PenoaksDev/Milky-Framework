<?php

namespace Foundation;

use Composer\Script\Event;

class ComposerScripts
{
	/**
	 * Handle the post-install Composer event.
	 *
	 * @param  \Composer\Script\Event  $event
	 * @return void
	 */
	public static function postInstall(Event $event)
	{
		require_once $event->getComposer()->getConfig()->get('vendor-dir').'/autoload.php';

		static::clearCompiled();
	}

	/**
	 * Handle the post-update Composer event.
	 *
	 * @param  \Composer\Script\Event  $event
	 * @return void
	 */
	public static function postUpdate(Event $event)
	{
		require_once $event->getComposer()->getConfig()->get('vendor-dir').'/autoload.php';

		static::clearCompiled();
	}

	/**
	 * Clear the cached Framework bootstrapping files.
	 *
	 * @return void
	 */
	protected static function clearCompiled()
	{
		$framework = new Application(getcwd());

		if (file_exists($compiledPath = $framework->getCachedCompilePath()))
{
			@unlink($compiledPath);
		}

		if (file_exists($servicesPath = $framework->getCachedServicesPath()))
{
			@unlink($servicesPath);
		}
	}
}
