<?php namespace Milky\Console;

use Milky\Framework;

trait AppNamespaceDetectorTrait
{
	/**
	 * Get the application namespace.
	 *
	 * @return string
	 */
	protected function getAppNamespace()
	{
		return Framework::fw()->getNamespace();
	}
}
