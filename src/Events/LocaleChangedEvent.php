<?php
namespace Penoaks\Events;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */

use Penoaks\Barebones\Event;

class LocaleChangedEvent implements Event
{
	use Traits\DynamicProperties;

	/**
	 * LocaleChangedEvent constructor.
	 *
	 * @param mixed $oldLocale
	 * @param string $locale
	 */
	public function __construct( $oldLocale, $locale )
	{
		$this->oldLocale = $oldLocale;
		$this->locale = $locale;
	}
}
