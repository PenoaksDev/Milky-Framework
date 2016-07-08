<?php

namesapce Penoaks\Console;

use Foundation\Console\Command;
use Foundation\Filesystem\Filesystem;
use Foundation\Routing\RouteCollection;

class RouteCacheCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'route:cache';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a route cache file for faster route registration';

	/**
	 * The filesystem instance.
	 *
	 * @var \Penoaks\Filesystem\Filesystem
	 */
	protected $files;

	/**
	 * Create a new route command instance.
	 *
	 * @param  \Penoaks\Filesystem\Filesystem  $files
	 * @return void
	 */
	public function __construct(Filesystem $files)
	{
		parent::__construct();

		$this->files = $files;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->call('route:clear');

		$routes = $this->getFreshApplicationRoutes();

		if (count($routes) == 0)
{
			return $this->error("Your application doesn't have any routes.");
		}

		foreach ($routes as $route)
{
			$route->prepareForSerialization();
		}

		$this->files->put(
			$this->framework->getCachedRoutesPath(), $this->buildRouteCacheFile($routes)
		);

		$this->info('Routes cached successfully!');
	}

	/**
	 * Boot a fresh copy of the application and get the routes.
	 *
	 * @return \Penoaks\Routing\RouteCollection
	 */
	protected function getFreshApplicationRoutes()
	{
		$fw = require $this->framework->bootstrapPath().'/app.php';

		$fw->make('Penoaks\Contracts\Console\Kernel')->bootstrap();

		return $fw->bindings['router']->getRoutes();
	}

	/**
	 * Build the route cache file.
	 *
	 * @param  \Penoaks\Routing\RouteCollection  $routes
	 * @return string
	 */
	protected function buildRouteCacheFile(RouteCollection $routes)
	{
		$stub = $this->files->get( __DIR__ . '/stubs/routes.stub' );

		return str_replace('{{routes}}', base64_encode(serialize($routes)), $stub);
	}
}
