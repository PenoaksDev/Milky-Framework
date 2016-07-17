<?php

namespace Penoaks\Database\Console\Migrations;

use Penoaks\Console\Command;

class BaseCommand extends Command
{
	/**
	 * Get the path to the migration directory.
	 *
	 * @return string
	 */
	protected function getMigrationPath()
	{
		return $this->framework->databasePath().DIRECTORY_SEPARATOR.'migrations';
	}
}
