<?php namespace Milky\Http\Session\Console;

use Milky\Console\Command;
use Milky\Support\Composer;
use Milky\Filesystem\Filesystem;

class SessionTableCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'session:table';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a migration for the session database table';

	/**
	 * The filesystem instance.
	 *
	 * @var Filesystem
	 */
	protected $files;

	/**
	 * @var Composer
	 */
	protected $composer;

	/**
	 * Create a new session table command instance.
	 *
	 * @param  Filesystem $files
	 * @param  Composer $composer
	 */
	public function __construct( Filesystem $files, Composer $composer )
	{
		parent::__construct();

		$this->files = $files;
		$this->composer = $composer;
	}

	/**
	 * Execute the console command.
	 *
	 */
	public function fire()
	{
		$fullPath = $this->createBaseMigration();

		$this->files->put( $fullPath, $this->files->get( __DIR__ . '/stubs/database.stub.php.php' ) );

		$this->info( 'Migration created successfully!' );

		$this->composer->dumpAutoloads();
	}

	/**
	 * Create a base migration file for the session.
	 *
	 * @return string
	 */
	protected function createBaseMigration()
	{
		$name = 'create_sessions_table';

		$path = $this->laravel->databasePath() . '/migrations';

		return $this->laravel['migration.creator']->create( $name, $path );
	}
}
