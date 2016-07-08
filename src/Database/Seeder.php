<?php

namesapce Penoaks\Database;

use Foundation\Console\Command;
use Foundation\Framework;

abstract class Seeder
{
	/**
	 * The bindings instance.
	 *
	 * @var \Penoaks\Framework
	 */
	protected $bindings;

	/**
	 * The console command instance.
	 *
	 * @var \Penoaks\Console\Command
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
	 * @param  string  $class
	 * @return void
	 */
	public function call($class)
	{
		$this->resolve($class)->run();

		if (isset($this->command))
{
			$this->command->getOutput()->writeln("<info>Seeded:</info> $class");
		}
	}

	/**
	 * Resolve an instance of the given seeder class.
	 *
	 * @param  string  $class
	 * @return \Penoaks\Database\Seeder
	 */
	protected function resolve($class)
	{
		if (isset($this->bindings))
{
			$instance = $this->bindings->make($class);

			$instance->setBindings($this->bindings);
		}
else
{
			$instance = new $class;
		}

		if (isset($this->command))
{
			$instance->setCommand($this->command);
		}

		return $instance;
	}

	/**
	 * Set the IoC bindings instance.
	 *
	 * @param  \Penoaks\Framework  $bindings
	 * @return $this
	 */
	public function setBindings(Bindings $bindings)
	{
		$this->bindings = $bindings;

		return $this;
	}

	/**
	 * Set the console command instance.
	 *
	 * @param  \Penoaks\Console\Command  $command
	 * @return $this
	 */
	public function setCommand(Command $command)
	{
		$this->command = $command;

		return $this;
	}
}
