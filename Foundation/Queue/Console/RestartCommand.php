<?php

namespace Foundation\Queue\Console;

use Foundation\Console\Command;

class RestartCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'queue:restart';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Restart queue worker daemons after their current job';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->framework['cache']->forever('illuminate:queue:restart', time());

		$this->info('Broadcasting queue restart signal.');
	}
}
