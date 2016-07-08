<?php

namespace Penoaks\Bus;

use Closure;
use RuntimeException;
use Penoaks\Pipeline\Pipeline;
use Penoaks\Contracts\Queue\Queue;
use Penoaks\Contracts\Queue\ShouldQueue;
use Penoaks\Framework;
use Penoaks\Contracts\Bus\QueueingDispatcher;
use Penoaks\Contracts\Bus\Dispatcher as DispatcherContract;

class Dispatcher implements DispatcherContract, QueueingDispatcher
{
	/**
	 * The bindings implementation.
	 *
	 * @var \Penoaks\Framework
	 */
	protected $bindings;

	/**
	 * The pipeline instance for the bus.
	 *
	 * @var \Penoaks\Pipeline\Pipeline
	 */
	protected $pipeline;

	/**
	 * The pipes to send commands through before dispatching.
	 *
	 * @var array
	 */
	protected $pipes = [];

	/**
	 * The queue resolver callback.
	 *
	 * @var \Closure|null
	 */
	protected $queueResolver;

	/**
	 * Create a new command dispatcher instance.
	 *
	 * @param  \Penoaks\Framework  $bindings
	 * @param  \Closure|null  $queueResolver
	 * @return void
	 */
	public function __construct(Bindings $bindings, Closure $queueResolver = null)
	{
		$this->bindings = $bindings;
		$this->queueResolver = $queueResolver;
		$this->pipeline = new Pipeline($bindings);
	}

	/**
	 * Dispatch a command to its appropriate handler.
	 *
	 * @param  mixed  $command
	 * @return mixed
	 */
	public function dispatch($command)
	{
		if ($this->queueResolver && $this->commandShouldBeQueued($command))
{
			return $this->dispatchToQueue($command);
		}
else
{
			return $this->dispatchNow($command);
		}
	}

	/**
	 * Dispatch a command to its appropriate handler in the current process.
	 *
	 * @param  mixed  $command
	 * @return mixed
	 */
	public function dispatchNow($command)
	{
		return $this->pipeline->send($command)->through($this->pipes)->then(function ($command)
{
			return $this->bindings->call([$command, 'handle']);
		});
	}

	/**
	 * Determine if the given command should be queued.
	 *
	 * @param  mixed  $command
	 * @return bool
	 */
	protected function commandShouldBeQueued($command)
	{
		return $command instanceof ShouldQueue;
	}

	/**
	 * Dispatch a command to its appropriate handler behind a queue.
	 *
	 * @param  mixed  $command
	 * @return mixed
	 *
	 * @throws \RuntimeException
	 */
	public function dispatchToQueue($command)
	{
		$connection = isset($command->connection) ? $command->connection : null;

		$queue = call_user_func($this->queueResolver, $connection);

		if (! $queue instanceof Queue)
{
			throw new RuntimeException('Queue resolver did not return a Queue implementation.');
		}

		if (method_exists($command, 'queue'))
{
			return $command->queue($queue, $command);
		}
else
{
			return $this->pushCommandToQueue($queue, $command);
		}
	}

	/**
	 * Push the command onto the given queue instance.
	 *
	 * @param  \Penoaks\Contracts\Queue\Queue  $queue
	 * @param  mixed  $command
	 * @return mixed
	 */
	protected function pushCommandToQueue($queue, $command)
	{
		if (isset($command->queue, $command->delay))
{
			return $queue->laterOn($command->queue, $command->delay, $command);
		}

		if (isset($command->queue))
{
			return $queue->pushOn($command->queue, $command);
		}

		if (isset($command->delay))
{
			return $queue->later($command->delay, $command);
		}

		return $queue->push($command);
	}

	/**
	 * Set the pipes through which commands should be piped before dispatching.
	 *
	 * @param  array  $pipes
	 * @return $this
	 */
	public function pipeThrough(array $pipes)
	{
		$this->pipes = $pipes;

		return $this;
	}
}
