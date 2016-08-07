<?php namespace Milky\Account\Guards;

use Milky\Account\Types\Account;
use Milky\Http\Request;

class RequestGuard extends Guard
{
	/**
	 * The guard callback.
	 *
	 * @var callable
	 */
	protected $callback;

	/**
	 * The request instance.
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * Create a new authentication guard.
	 *
	 * @param  callable $callback
	 * @param  Request $request
	 */
	public function __construct( callable $callback, Request $request )
	{
		parent::__construct();

		$this->request = $request;
		$this->callback = $callback;
	}

	/**
	 * Get the currently authenticated acct.
	 *
	 * @return Account|null
	 */
	public function acct()
	{
		// If we've already retrieved the acct for the current request we can just
		// return it back immediately. We do not want to fetch the acct data on
		// every call to this method because that would be tremendously slow.
		if ( !is_null( $this->acct ) )
			return $this->acct;

		return $this->acct = call_user_func( $this->callback, $this->request );
	}

	/**
	 * Validate a acct's credentials.
	 *
	 * @param  array $credentials
	 * @return bool
	 */
	public function validate( array $credentials = [] )
	{
		return !is_null( ( new static( $this->callback, $credentials['request'] ) )->acct() );
	}

	/**
	 * Set the current request instance.
	 *
	 * @param  Request $request
	 * @return $this
	 */
	public function setRequest( Request $request )
	{
		$this->request = $request;

		return $this;
	}

	/**
	 * Get the default Guard Name
	 *
	 * @return string
	 */
	public function name()
	{
		return 'request';
	}
}
