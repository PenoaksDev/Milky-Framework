<?php

namespace Foundation\Pagination;

use Foundation\Support\ServiceProvider;

class PaginationServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		Paginator::currentPathResolver(function ()
{
			return $this->fw->bindings['request']->url();
		});

		Paginator::currentPageResolver(function ($pageName = 'page')
{
			$page = $this->fw->bindings['request']->input($pageName);

			if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1)
{
				return $page;
			}

			return 1;
		});
	}
}
