<?php

namespace Foundation\Console\Events;

class ArtisanStarting
{
	/**
	 * The Artisan application instance.
	 *
	 * @var \Foundation\Console\Application
	 */
	public $artisan;

	/**
	 * Create a new event instance.
	 *
	 * @param  \Foundation\Console\Application  $artisan
	 * @return void
	 */
	public function __construct($artisan)
	{
		$this->artisan = $artisan;
	}
}
