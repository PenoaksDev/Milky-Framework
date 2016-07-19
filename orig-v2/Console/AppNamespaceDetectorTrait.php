<?php

namespace Penoaks\Console;

use Penoaks\Framework;

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
