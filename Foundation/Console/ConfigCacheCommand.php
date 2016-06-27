<?php

namespace Foundation\Console;

use Foundation\Console\Command;
use Foundation\Filesystem\Filesystem;

class ConfigCacheCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'config:cache';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a cache file for faster configuration loading';

	/**
	 * The filesystem instance.
	 *
	 * @var \Foundation\Filesystem\Filesystem
	 */
	protected $files;

	/**
	 * Create a new config cache command instance.
	 *
	 * @param  \Foundation\Filesystem\Filesystem  $files
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
		$this->call('config:clear');

		$config = $this->getFreshConfiguration();

		$this->files->put(
			$this->laravel->getCachedConfigPath(), '<?php return '.var_export($config, true).';'.PHP_EOL
		);

		$this->info('Configuration cached successfully!');
	}

	/**
	 * Boot a fresh copy of the application configuration.
	 *
	 * @return array
	 */
	protected function getFreshConfiguration()
	{
		$app = require $this->laravel->bootstrapPath().'/app.php';

		$app->make('Foundation\Contracts\Console\Kernel')->bootstrap();

		return $app['config']->all();
	}
}
