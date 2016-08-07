<?php namespace Milky\Database\Console\Seeds;

use Milky\Binding\UniversalBuilder;
use Milky\Console\Command;
use Milky\Console\ConfirmableTrait;
use Milky\Database\ConnectionResolverInterface;
use Milky\Database\Eloquent\Model;
use Milky\Database\Seeder;
use Milky\Facades\Config;
use Symfony\Component\Console\Input\InputOption;

class SeedCommand extends Command
{
	use ConfirmableTrait;

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'db:seed';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Seed the database with records';

	/**
	 * The connection resolver instance.
	 *
	 * @var ConnectionResolverInterface
	 */
	protected $resolver;

	/**
	 * Create a new database seed command instance.
	 *
	 * @param ConnectionResolverInterface $resolver
	 * @return void
	 */
	public function __construct( ConnectionResolverInterface $resolver )
	{
		parent::__construct();

		$this->resolver = $resolver;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		if ( !$this->confirmToProceed() )
		{
			return;
		}

		$this->resolver->setDefaultConnection( $this->getDatabase() );

		Model::unguarded( function ()
		{
			$this->getSeeder()->run();
		} );
	}

	/**
	 * Get a seeder instance from the container.
	 *
	 * @return Seeder
	 */
	protected function getSeeder()
	{
		$class = UniversalBuilder::resolveClass( $this->input->getOption( 'class' ) );

		return $class->setCommand( $this );
	}

	/**
	 * Get the name of the database connection to use.
	 *
	 * @return string
	 */
	protected function getDatabase()
	{
		$database = $this->input->getOption( 'database' );

		return $database ?: Config::get( 'database.default' );
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['class', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder', 'DatabaseSeeder'],

			['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to seed'],

			['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
		];
	}
}
