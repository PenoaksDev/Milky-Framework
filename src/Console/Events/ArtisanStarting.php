<?php

namespace Penoaks\Console\Events;

class ArtisanStarting
{
	/**
	 * The Artisan application instance.
	 *
	 * @var \Penoaks\Console\Application
	 */
	public $artisan;

	/**
	 * Create a new event instance.
	 *
	 * @param  \Penoaks\Console\Application  $artisan
	 * @return void
	 */
	public function __construct($artisan)
	{
		$this->artisan = $artisan;
	}
}
