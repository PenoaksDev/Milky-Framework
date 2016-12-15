<?php namespace Milky\Console;

use Milky\Binding\ServiceResolver;
use Milky\Console\Scheduling\Schedule;
use Milky\Console\Scheduling\ScheduleRunCommand;
use Milky\Framework;
use Milky\Queue\Console\FlushFailedCommand;
use Milky\Queue\Console\ForgetFailedCommand;
use Milky\Queue\Console\ListFailedCommand;
use Milky\Queue\Console\RetryCommand;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class ConsoleServiceResolver extends ServiceResolver
{
	protected $factory;

	protected $queueFailed;
	protected $queueRetry;
	protected $queueForget;
	protected $queueFlush;

	public function __construct()
	{
		$this->factory = new ConsoleFactory( Framework::fw() );

		$this->queueFailed = new ListFailedCommand();
		$this->queueRetry = new RetryCommand();
		$this->queueForget = new ForgetFailedCommand();
		$this->queueFlush = new FlushFailedCommand();

		$this->scheduleRunCommand = new ScheduleRunCommand( new Schedule() );

		$this->setDefault( 'factory' );
	}

	public function key()
	{
		return 'console';
	}
}
