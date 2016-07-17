<?php

namespace Penoaks\Console;

use Penoaks\Console\Command;
use Penoaks\Filesystem\Filesystem;

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
	 * @var \Penoaks\Filesystem\Filesystem
	 */
	protected $files;

	/**
	 * Create a new config cache command instance.
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
		$this->call('config:clear');

		$config = $this->getFreshConfiguration();

		$this->files->put(
			$this->framework->getCachedConfigPath(), '<?php return '.var_export($config, true).';'.PHP_EOL
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
		$fw = require $this->framework->bootstrapPath().'/app.php';

		$fw->make('Penoaks\Contracts\Console\Kernel')->bootstrap();

		return $fw->bindings['config']->all();
	}
}
