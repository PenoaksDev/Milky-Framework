<?php

namesapce Penoaks\Console;

use Foundation\Framework;

trait AppNamespaceDetectorTrait
{
	/**
	 * Get the application namespace.
	 *
	 * @return string
	 */
	protected function getAppNamespace()
	{
		return Bindings::getInstance()->getNamespace();
	}
}
