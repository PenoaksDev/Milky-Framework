<?php namespace Milky\Database\Console\Migrations;

use Milky\Console\Command;

class BaseCommand extends Command
{
	/**
	 * Get the path to the migration directory.
	 *
	 * @return string
	 */
	protected function getMigrationPath()
	{
		return $this->laravel->databasePath().DIRECTORY_SEPARATOR.'migrations';
	}
}
