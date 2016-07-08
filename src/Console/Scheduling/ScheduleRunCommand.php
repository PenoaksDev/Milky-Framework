<?php

namespace Penoaks\Console\Scheduling;

use Penoaks\Console\Command;

class ScheduleRunCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'schedule:run';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Run the scheduled commands';

	/**
	 * The schedule instance.
	 *
	 * @var \Penoaks\Console\Scheduling\Schedule
	 */
	protected $schedule;

	/**
	 * Create a new command instance.
	 *
	 * @param  \Penoaks\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	public function __construct(Schedule $schedule)
	{
		$this->schedule = $schedule;

		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$events = $this->schedule->dueEvents($this->framework);

		$eventsRan = 0;

		foreach ($events as $event)
{
			if (! $event->filtersPass($this->framework))
{
				continue;
			}

			$this->line('<info>Running scheduled command:</info> '.$event->getSummaryForDisplay());

			$event->run($this->framework);

			++$eventsRan;
		}

		if (count($events) === 0 || $eventsRan === 0)
{
			$this->info('No scheduled commands are ready to run.');
		}
	}
}
