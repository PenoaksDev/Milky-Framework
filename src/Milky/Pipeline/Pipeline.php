<?php namespace Milky\Pipeline;

use Closure;
use Milky\Exceptions\PipelineException;
use Milky\Framework;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class Pipeline
{
	/**
	 * The object being passed through the pipeline.
	 *
	 * @var mixed
	 */
	protected $passable;

	/**
	 * The array of class pipes.
	 *
	 * @var array
	 */
	protected $pipes = [];

	/**
	 * The method to call on each pipe.
	 *
	 * @var string
	 */
	protected $method = 'handle';

	/**
	 * Callable handler of exceptions
	 *
	 * @var Callable
	 */
	protected $exceptionHandler;

	public function withExceptionHandler( $callable )
	{
		$this->exceptionHandler = $callable;

		return $this;
	}

	/**
	 * Set the object being sent through the pipeline.
	 *
	 * @param  mixed $passable
	 * @return $this
	 */
	public function send( $passable )
	{
		$this->passable = $passable;

		return $this;
	}

	/**
	 * Set the array of pipes.
	 *
	 * @param  array|mixed $pipes
	 * @return $this
	 */
	public function through( $pipes )
	{
		$this->pipes = is_array( $pipes ) ? $pipes : func_get_args();

		return $this;
	}

	/**
	 * Set the method to call on the pipes.
	 *
	 * @param  string $method
	 * @return $this
	 */
	public function via( $method )
	{
		$this->method = $method;

		return $this;
	}

	/**
	 * Run the pipeline with a final destination callback.
	 *
	 * @param  \Closure $destination
	 * @return mixed
	 */
	public function then( Closure $destination )
	{
		$firstSlice = $this->getInitialSlice( $destination );

		$pipes = array_reverse( $this->pipes );

		return call_user_func( array_reduce( $pipes, $this->getSlice(), $firstSlice ), $this->passable );
	}

	/**
	 * Get a Closure that represents a slice of the application onion.
	 *
	 * @return \Closure
	 */
	protected function getSlice()
	{
		return function ( $stack, $pipe )
		{
			return function ( $passable ) use ( $stack, $pipe )
			{
				try
				{
					$slice = $this->getSliceWithException();

					return call_user_func( $slice( $stack, $pipe ), $passable );
				}
				catch ( \Exception $e )
				{
					if ( is_callable( $this->exceptionHandler ) )
						return call_user_func( $this->exceptionHandler, $passable, $e );
					else
						throw new PipelineException( $e );
				}
				catch ( \Throwable $e )
				{
					if ( is_callable( $this->exceptionHandler ) )
						return call_user_func( $this->exceptionHandler, $passable, new FatalThrowableError( $e ) );
					else
						throw new PipelineException( $e );
				}
			};
		};
	}

	/**
	 * Get a Closure that represents a slice of the application onion.
	 *
	 * @return \Closure
	 */
	protected function getSliceWithException()
	{
		return function ( $stack, $pipe )
		{
			return function ( $passable ) use ( $stack, $pipe )
			{
				if ( $pipe instanceof Closure )
				{
					// If the pipe is an instance of a Closure, we will just call it directly but
					// otherwise we'll resolve the pipes out of the container and call it with
					// the appropriate method and arguments, returning the results back out.
					return call_user_func( $pipe, $passable, $stack );
				}
				elseif ( !is_object( $pipe ) )
				{
					list( $name, $parameters ) = $this->parsePipeString( $pipe );

					// If the pipe is a string we will parse the string and resolve the class out
					// of the dependency injection container. We can then build a callable and
					// execute the pipe function giving in the parameters that are required.
					$pipe = Framework::fw()->get( $name );

					$parameters = array_merge( [$passable, $stack], $parameters );
				}
				else
				{
					// If the pipe is already an object we'll just make a callable and pass it to
					// the pipe as-is. There is no need to do any extra parsing and formatting
					// since the object we're given was already a fully instantiated object.
					$parameters = [$passable, $stack];
				}

				return call_user_func_array( [$pipe, $this->method], $parameters );
			};
		};
	}

	/**
	 * Get the initial slice to begin the stack call.
	 *
	 * @param  \Closure $destination
	 * @return \Closure
	 */
	protected function getInitialSlice( Closure $destination )
	{
		return function ( $passable ) use ( $destination )
		{
			try
			{
				return call_user_func( $destination, $passable );
			}
			catch ( \Exception $e )
			{
				if ( is_callable( $this->exceptionHandler ) )
					return call_user_func( $this->exceptionHandler, $passable, $e );
				else
					throw new PipelineException( $e );
			}
			catch ( \Throwable $e )
			{
				if ( is_callable( $this->exceptionHandler ) )
					return call_user_func( $this->exceptionHandler, $passable, new FatalThrowableError( $e ) );
				else
					throw new PipelineException( $e );
			}
		};
	}

	/**
	 * Parse full pipe string to get name and parameters.
	 *
	 * @param  string $pipe
	 * @return array
	 */
	protected function parsePipeString( $pipe )
	{
		list( $name, $parameters ) = array_pad( explode( ':', $pipe, 2 ), 2, [] );

		if ( is_string( $parameters ) )
			$parameters = explode( ',', $parameters );

		return [$name, $parameters];
	}
}
