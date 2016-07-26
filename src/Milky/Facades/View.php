<?php namespace Milky\Facades;

use Milky\Http\View\Factory;
use Milky\Http\View\View as BaseView;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class View extends BaseFacade
{
	protected function __getResolver()
	{
		return 'view.factory';
	}

	/**
	 * @return Factory
	 */
	public static function i()
	{
		return static::__self()->scaffold;
	}

	/**
	 * @return BaseView
	 */
	public static function make( $view, $data = [], $mergeData = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * @return string
	 */
	public static function render( $view, $data = [], $mergeData = [] )
	{
		return static::make( $view, $data, $mergeData )->render();
	}
}
