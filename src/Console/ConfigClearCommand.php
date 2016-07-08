<?php

namespace Penoaks\Console;

use Penoaks\Console\Command;
use Penoaks\Filesystem\Filesystem;

class ConfigClearCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'config:clear';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Remove the configuration cache file';

	/**
	 * The filesystem instance.
	 *
	 * @var \Penoaks\Filesystem\Filesystem
	 */
	protected $files;

	/**
	 * Create a new config clear command instance.
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
		$this->files->delete($this->framework->getCachedConfigPath());

		$this->info('Configuration cache cleared!');
	}
}
