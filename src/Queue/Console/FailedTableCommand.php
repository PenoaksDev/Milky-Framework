<?php

namesapce Penoaks\Queue\Console;

use Foundation\Support\Str;
use Foundation\Console\Command;
use Foundation\Support\Composer;
use Foundation\Filesystem\Filesystem;

class FailedTableCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'queue:failed-table';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a migration for the failed queue jobs database table';

	/**
	 * The filesystem instance.
	 *
	 * @var \Penoaks\Filesystem\Filesystem
	 */
	protected $files;

	/**
	 * @var \Penoaks\Support\Composer
	 */
	protected $composer;

	/**
	 * Create a new failed queue jobs table command instance.
	 *
	 * @param  \Penoaks\Filesystem\Filesystem  $files
	 * @param  \Penoaks\Support\Composer	$composer
	 * @return void
	 */
	public function __construct(Filesystem $files, Composer $composer)
	{
		parent::__construct();

		$this->files = $files;
		$this->composer = $composer;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$table = $this->framework['config']['queue.failed.table'];

		$tableClassName = Str::studly($table);

		$fullPath = $this->createBaseMigration($table);

		$stub = str_replace(
			['{{table}}', '{{tableClassName}}'], [$table, $tableClassName], $this->files->get( __DIR__ . '/stubs/failed_jobs.stub' )
		);

		$this->files->put($fullPath, $stub);

		$this->info('Migration created successfully!');

		$this->composer->dumpAutoloads();
	}

	/**
	 * Create a base migration file for the table.
	 *
	 * @param  string  $table
	 * @return string
	 */
	protected function createBaseMigration($table = 'failed_jobs')
	{
		$name = 'create_'.$table.'_table';

		$path = $this->framework->databasePath().'/migrations';

		return $this->framework['migration.creator']->create($name, $path);
	}
}
