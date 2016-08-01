<?php namespace Milky\Bus;

use Closure;
use Milky\Pipeline\Pipeline;
use Milky\Queue\Impl\ShouldQueue;
use Milky\Queue\Impl\Queue;
use Milky\Services\ServiceFactory;
use RuntimeException;

class Dispatcher extends ServiceFactory
{
	/**
	 * The container implementation.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * The pipeline instance for the bus.
	 *
	 * @var Pipeline
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

	public static function build()
	{
		return new Dispatcher( function ( $connection = null ) use ( $fw )
		{
			return $fw['Milky\Queue\Impl\Factory']->connection( $connection );
		} );
	}

	/**
	 * Create a new command dispatcher instance.
	 *
	 * @param  \Closure|null $queueResolver
	 * @return void
	 */
	public function __construct( Closure $queueResolver = null )
	{
		parent::__construct();
		
		$this->queueResolver = $queueResolver;
		$this->pipeline = new Pipeline();
	}

	/**
	 * Dispatch a command to its appropriate handler.
	 *
	 * @param  mixed $command
	 * @return mixed
	 */
	public function dispatch( $command )
	{
		if ( $this->queueResolver && $this->commandShouldBeQueued( $command ) )
		{
			return $this->dispatchToQueue( $command );
		}
		else
		{
			return $this->dispatchNow( $command );
		}
	}

	/**
	 * Dispatch a command to its appropriate handler in the current process.
	 *
	 * @param  mixed $command
	 * @return mixed
	 */
	public function dispatchNow( $command )
	{
		return $this->pipeline->send( $command )->through( $this->pipes )->then( function ( $command )
		{
			return $this->container->call( [$command, 'handle'] );
		} );
	}

	/**
	 * Determine if the given command should be queued.
	 *
	 * @param  mixed $command
	 * @return bool
	 */
	protected function commandShouldBeQueued( $command )
	{
		return $command instanceof ShouldQueue;
	}

	/**
	 * Dispatch a command to its appropriate handler behind a queue.
	 *
	 * @param  mixed $command
	 * @return mixed
	 *
	 * @throws \RuntimeException
	 */
	public function dispatchToQueue( $command )
	{
		$connection = isset( $command->connection ) ? $command->connection : null;

		$queue = call_user_func( $this->queueResolver, $connection );

		if ( !$queue instanceof Queue )
			throw new RuntimeException( 'Queue resolver did not return a Queue implementation.' );

		if ( method_exists( $command, 'queue' ) )
			return $command->queue( $queue, $command );
		else
			return $this->pushCommandToQueue( $queue, $command );
	}

	/**
	 * Push the command onto the given queue instance.
	 *
	 * @param  Queue $queue
	 * @param  mixed $command
	 * @return mixed
	 */
	protected function pushCommandToQueue( $queue, $command )
	{
		if ( isset( $command->queue, $command->delay ) )
		{
			return $queue->laterOn( $command->queue, $command->delay, $command );
		}

		if ( isset( $command->queue ) )
		{
			return $queue->pushOn( $command->queue, $command );
		}

		if ( isset( $command->delay ) )
		{
			return $queue->later( $command->delay, $command );
		}

		return $queue->push( $command );
	}

	/**
	 * Set the pipes through which commands should be piped before dispatching.
	 *
	 * @param  array $pipes
	 * @return $this
	 */
	public function pipeThrough( array $pipes )
	{
		$this->pipes = $pipes;

		return $this;
	}
}
