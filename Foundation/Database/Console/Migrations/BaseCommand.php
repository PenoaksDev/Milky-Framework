<?php

namespace Foundation\Database\Console\Migrations;

use Foundation\Console\Command;

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
