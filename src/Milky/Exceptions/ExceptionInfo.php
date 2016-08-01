<?php namespace Milky\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * This is the exception info class.
 */
class ExceptionInfo
{
	/**
	 * The error info path.
	 *
	 * @var string|null
	 */
	protected $path;

	/**
	 * Create a exception info instance.
	 *
	 * @param string|null $path
	 *
	 * @return void
	 */
	public function __construct( $path = null )
	{
		$this->path = $path;
	}

	/**
	 * Get the exception information.
	 *
	 * @param \Exception $exception
	 * @param string $id
	 * @param int $code
	 *
	 * @return array
	 */
	public function generate( Exception $exception, $id, $code )
	{
		$errors = $this->path ? json_decode( file_get_contents( $this->path ), true ) : [
			500 => [
				'name' => 'Internal Server Error',
				'message' => 'An error has occurred and this resource cannot be displayed.'
			]
		];

		if ( isset( $errors[$code] ) )
		{
			$info = array_merge( ['id' => $id, 'code' => $code], $errors[$code] );
		}
		else
		{
			$info = array_merge( ['id' => $id, 'code' => 500], $errors[500] );
		}

		if ( $exception instanceof HttpExceptionInterface )
		{
			$msg = (string) $exception->getMessage();
			$info['detail'] = ( strlen( $msg ) > 4 ) ? $msg : $info['message'];
			$info['summary'] = ( strlen( $msg ) < 36 && strlen( $msg ) > 4 ) ? $msg : 'Houston, We Have A Problem.';
		}
		else
		{
			$info['detail'] = $info['message'];
			$info['summary'] = 'Houston, We Have A Problem.';
		}

		unset( $info['message'] );

		return $info;
	}
}
