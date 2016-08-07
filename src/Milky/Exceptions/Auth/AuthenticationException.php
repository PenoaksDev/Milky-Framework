<?php namespace Milky\Exceptions\Auth;

use Milky\Account\Drivers\AccountDriver;

class AuthenticationException extends \Exception
{
	/**
	 * The driver instance
	 *
	 * @var AccountDriver
	 */
	protected $driver;

	/**
	 * Create a new authentication exception.
	 *
	 * @param AccountDriver $driver
	 */
	public function __construct( $driver = null )
	{
		$this->driver = $driver;

		parent::__construct( 'Unauthenticated.' );
	}

	/**
	 * Get the driver instance.
	 *
	 * @return AccountDriver
	 */
	public function driver()
	{
		return $this->driver;
	}
}
