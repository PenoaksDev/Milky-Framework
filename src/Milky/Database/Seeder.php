<?php namespace Milky\Database;

use Milky\Console\Command;
use Milky\Binding\UniversalBuilder;

abstract class Seeder
{
	/**
	 * The console command instance.
	 *
	 * @var Command
	 */
	protected $command;

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	abstract public function run();

	/**
	 * Seed the given connection from the given path.
	 *
	 * @param  string $class
	 * @return void
	 */
	public function call( $class )
	{
		$this->resolve( $class )->run();

		if ( isset( $this->command ) )
			$this->command->getOutput()->writeln( "<info>Seeded:</info> $class" );
	}

	/**
	 * Resolve an instance of the given seeder class.
	 *
	 * @param  string $class
	 * @return Seeder
	 */
	protected function resolve( $class )
	{
		$instance = UniversalBuilder::resolveClass( $class );

		if ( isset( $this->command ) )
			$instance->setCommand( $this->command );

		return $instance;
	}

	/**
	 * Set the console command instance.
	 *
	 * @param Command $command
	 * @return $this
	 */
	public function setCommand( Command $command )
	{
		$this->command = $command;

		return $this;
	}
}
