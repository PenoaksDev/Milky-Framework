<?php

namespace Foundation\Console;

use Foundation\Console\Command;
use Foundation\Filesystem\Filesystem;

class ViewClearCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'view:clear';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clear all compiled view files';

	/**
	 * The filesystem instance.
	 *
	 * @var \Foundation\Filesystem\Filesystem
	 */
	protected $files;

	/**
	 * Create a new config clear command instance.
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
		$views = $this->files->glob($this->framework['config']['view.compiled'].'/*');

		foreach ($views as $view)
{
			$this->files->delete($view);
		}

		$this->info('Compiled views cleared!');
	}
}
