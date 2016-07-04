<?php

namespace Foundation\Console;

use ClassPreloader\Factory;
use Foundation\Console\Command;
use Foundation\Support\Composer;
use Symfony\Component\Console\Input\InputOption;
use ClassPreloader\Exceptions\VisitorExceptionInterface;

class OptimizeCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'optimize';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Optimize the framework for better performance';

	/**
	 * The composer instance.
	 *
	 * @var \Foundation\Support\Composer
	 */
	protected $composer;

	/**
	 * Create a new optimize command instance.
	 *
	 * @param  \Foundation\Support\Composer  $composer
	 * @return void
	 */
	public function __construct(Composer $composer)
	{
		parent::__construct();

		$this->composer = $composer;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->info('Generating optimized class loader');

		if ($this->option('psr'))
{
			$this->composer->dumpAutoloads();
		}
else
{
			$this->composer->dumpOptimized();
		}

		if ($this->option('force') || ! $this->framework['config']['app.debug'])
{
			$this->info('Compiling common classes');
			$this->compileClasses();
		}
else
{
			$this->call('clear-compiled');
		}
	}

	/**
	 * Generate the compiled class file.
	 *
	 * @return void
	 */
	protected function compileClasses()
	{
		$preloader = (new Factory)->create(['skip' => true]);

		$handle = $preloader->prepareOutput($this->framework->getCachedCompilePath());

		foreach ($this->getClassFiles() as $file)
{
			try
{
				fwrite($handle, $preloader->getCode($file, false)."\n");
			} catch (VisitorExceptionInterface $e)
{
				//
			}
		}

		fclose($handle);
	}

	/**
	 * Get the classes that should be combined and compiled.
	 *
	 * @return array
	 */
	protected function getClassFiles()
	{
		$fw = $this->framework;

		$core = require __DIR__.'/Optimize/config.php';

		$files = array_merge($core, $fw->bindings['config']->get('compile.files', []));

		foreach ($fw->bindings['config']->get('compile.providers', []) as $provider)
{
			$files = array_merge($files, forward_static_call([$provider, 'compiles']));
		}

		return array_map('realpath', $files);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['force', null, InputOption::VALUE_NONE, 'Force the compiled class file to be written.'],

			['psr', null, InputOption::VALUE_NONE, 'Do not optimize Composer dump-autoload.'],
		];
	}
}
