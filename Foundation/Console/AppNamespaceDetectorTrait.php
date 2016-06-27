<?php

namespace Foundation\Console;

use Foundation\Container\Container;

trait AppNamespaceDetectorTrait
{
	/**
	 * Get the application namespace.
	 *
	 * @return string
	 */
	protected function getAppNamespace()
	{
		return Container::getInstance()->getNamespace();
	}
}
