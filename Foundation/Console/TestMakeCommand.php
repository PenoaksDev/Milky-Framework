<?php

namespace Foundation\Console;

use Foundation\Console\GeneratorCommand;

class TestMakeCommand extends GeneratorCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'make:test';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new test class';

	/**
	 * The type of class being generated.
	 *
	 * @var string
	 */
	protected $type = 'Test';

	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub()
	{
		return __DIR__.'/stubs/test.stub';
	}

	/**
	 * Get the destination class path.
	 *
	 * @param  string  $name
	 * @return string
	 */
	protected function getPath($name)
	{
		$name = str_replace($this->framework->getNamespace(), '', $name);

		return $this->framework['path.base'].'/tests/'.str_replace('\\', '/', $name).'.php';
	}

	/**
	 * Get the default namespace for the class.
	 *
	 * @param  string  $rootNamespace
	 * @return string
	 */
	protected function getDefaultNamespace($rootNamespace)
	{
		return $rootNamespace;
	}
}
