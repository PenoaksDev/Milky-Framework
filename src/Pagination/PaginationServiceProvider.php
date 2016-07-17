<?php
namespace Penoaks\Pagination;

use Penoaks\Barebones\ServiceProvider;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class PaginationServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		Paginator::currentPathResolver( function ()
		{
			return $this->bindings['request']->url();
		} );

		Paginator::currentPageResolver( function ( $pageName = 'page' )
		{
			$page = $this->bindings['request']->input( $pageName );

			if ( filter_var( $page, FILTER_VALIDATE_INT ) !== false && (int) $page >= 1 )
			{
				return $page;
			}

			return 1;
		} );
	}
}
