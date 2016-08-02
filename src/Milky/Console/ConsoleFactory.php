<?php namespace Milky\Console;

use Milky\Binding\UniversalBuilder;
use Milky\Facades\Hooks;
use Milky\Framework;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;

class ConsoleFactory extends SymfonyApplication
{
	/**
	 * @var Framework
	 */
	private $fw;

	/**
	 * The output from the previous command.
	 *
	 * @var BufferedOutput
	 */
	protected $lastOutput;

	/**
	 * Create a new cli factory.
	 *
	 * @return void
	 */
	public function __construct( Framework $fw )
	{
		parent::__construct( Framework::PRODUCT, Framework::VERSION );

		$this->fw = $fw;

		$this->setAutoExit( false );
		$this->setCatchExceptions( false );

		Hooks::trigger( 'cli.starting', ['cli' => $this] );
	}

	/**
	 * Run an Artisan console command by name.
	 *
	 * @param  string $command
	 * @param  array $parameters
	 * @return int
	 */
	public function call( $command, array $parameters = [] )
	{
		$parameters = collect( $parameters )->prepend( $command );

		$this->lastOutput = new BufferedOutput;

		$this->setCatchExceptions( false );

		$result = $this->run( new ArrayInput( $parameters->toArray() ), $this->lastOutput );

		$this->setCatchExceptions( true );

		return $result;
	}

	/**
	 * Get the output for the last run command.
	 *
	 * @return string
	 */
	public function output()
	{
		return $this->lastOutput ? $this->lastOutput->fetch() : '';
	}

	/**
	 * Add a command to the console.
	 *
	 * @param  Command $command
	 * @return Command
	 */
	public function add( SymfonyCommand $command )
	{
		return $this->addToParent( $command );
	}

	/**
	 * Add the command to the parent instance.
	 *
	 * @param  Command $command
	 * @return Command
	 */
	protected function addToParent( SymfonyCommand $command )
	{
		return parent::add( $command );
	}

	/**
	 * Add a command, resolving through the application.
	 *
	 * @param  string $command
	 * @return Command
	 */
	public function resolve( $command )
	{
		return $this->add( UniversalBuilder::resolve( $command ) );
	}

	/**
	 * Resolve an array of commands through the application.
	 *
	 * @param  array|mixed $commands
	 * @return $this
	 */
	public function resolveCommands( $commands )
	{
		$commands = is_array( $commands ) ? $commands : func_get_args();

		foreach ( $commands as $command )
		{
			$this->resolve( $command );
		}

		return $this;
	}

	/**
	 * Get the default input definitions for the applications.
	 *
	 * This is used to add the --env option to every available command.
	 *
	 * @return InputDefinition
	 */
	protected function getDefaultInputDefinition()
	{
		$definition = parent::getDefaultInputDefinition();

		$definition->addOption( $this->getEnvironmentOption() );

		return $definition;
	}

	/**
	 * Get the global environment option for the definition.
	 *
	 * @return InputOption
	 */
	protected function getEnvironmentOption()
	{
		$message = 'The environment the command should run under.';

		return new InputOption( '--env', null, InputOption::VALUE_OPTIONAL, $message );
	}


	/**
	 * Determine if we are running in the console.
	 *
	 * @return bool
	 */
	public static function runningInConsole()
	{
		return php_sapi_name() == 'cli';
	}
}
