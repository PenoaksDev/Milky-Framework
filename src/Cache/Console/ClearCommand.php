<?php

namesapce Penoaks\Cache\Console;

use Foundation\Console\Command;
use Foundation\Cache\CacheManager;
use Symfony\Component\Console\Input\InputArgument;

class ClearCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'cache:clear';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Flush the application cache';

	/**
	 * The cache manager instance.
	 *
	 * @var \Penoaks\Cache\CacheManager
	 */
	protected $cache;

	/**
	 * Create a new cache clear command instance.
	 *
	 * @param  \Penoaks\Cache\CacheManager  $cache
	 * @return void
	 */
	public function __construct(CacheManager $cache)
	{
		parent::__construct();

		$this->cache = $cache;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$storeName = $this->argument('store');

		$this->framework['events']->fire('cache:clearing', [$storeName]);

		$this->cache->store($storeName)->flush();

		$this->framework['events']->fire('cache:cleared', [$storeName]);

		$this->info('Application cache cleared!');
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['store', InputArgument::OPTIONAL, 'The name of the store you would like to clear.'],
		];
	}
}
