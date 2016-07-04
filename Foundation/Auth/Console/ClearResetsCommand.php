<?php

namespace Foundation\Auth\Console;

use Foundation\Console\Command;

class ClearResetsCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'auth:clear-resets {name? : The name of the password broker}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Flush expired password reset tokens';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->framework['auth.password']->broker($this->argument('name'))->getRepository()->deleteExpired();

		$this->info('Expired reset tokens cleared!');
	}
}
