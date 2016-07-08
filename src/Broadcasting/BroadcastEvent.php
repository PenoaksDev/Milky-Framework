<?php

namespace Penoaks\Broadcasting;

use ReflectionClass;
use ReflectionProperty;
use Penoaks\Contracts\Queue\Job;
use Penoaks\Contracts\Support\Arrayable;
use Penoaks\Contracts\Broadcasting\Broadcaster;

class BroadcastEvent
{
	/**
	 * The broadcaster implementation.
	 *
	 * @var \Penoaks\Contracts\Broadcasting\Broadcaster
	 */
	protected $broadcaster;

	/**
	 * Create a new job handler instance.
	 *
	 * @param  \Penoaks\Contracts\Broadcasting\Broadcaster  $broadcaster
	 * @return void
	 */
	public function __construct(Broadcaster $broadcaster)
	{
		$this->broadcaster = $broadcaster;
	}

	/**
	 * Handle the queued job.
	 *
	 * @param  \Penoaks\Contracts\Queue\Job  $job
	 * @param  array  $data
	 * @return void
	 */
	public function fire(Job $job, array $data)
	{
		$event = unserialize($data['event']);

		$name = method_exists($event, 'broadcastAs')
				? $event->broadcastAs() : get_class($event);

		$this->broadcaster->broadcast(
			$event->broadcastOn(), $name, $this->getPayloadFromEvent($event)
		);

		$job->delete();
	}

	/**
	 * Get the payload for the given event.
	 *
	 * @param  mixed  $event
	 * @return array
	 */
	protected function getPayloadFromEvent($event)
	{
		if (method_exists($event, 'broadcastWith'))
{
			return $event->broadcastWith();
		}

		$payload = [];

		foreach ((new ReflectionClass($event))->getProperties(ReflectionProperty::IS_PUBLIC) as $property)
{
			$payload[$property->getName()] = $this->formatProperty($property->getValue($event));
		}

		return $payload;
	}

	/**
	 * Format the given value for a property.
	 *
	 * @param  mixed  $value
	 * @return mixed
	 */
	protected function formatProperty($value)
	{
		if ($value instanceof Arrayable)
{
			return $value->toArray();
		}

		return $value;
	}
}
