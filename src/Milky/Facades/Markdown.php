<?php

/*
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */

namespace Milky\Facades;

use League\CommonMark\Converter;

class Markdown extends BaseFacade
{
	protected function __getResolver()
	{
		return Converter::class;
	}

	/**
	 * Converts CommonMark to HTML.
	 *
	 * @param string $commonMark
	 *
	 * @return string
	 */
	public static function convertToHtml( $commonMark )
	{
		return static::__do( __FUNCTION__, compact( 'commonMark' ) );
	}
}
