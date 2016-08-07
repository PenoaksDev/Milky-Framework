<?php namespace Milky\Auth;

use Milky\Http\Request;

class RequestGuard implements Guard
{
	use GuardHelpers;

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
		$this->request = $request;
		$this->callback = $callback;
	}

	/**
	 * Get the currently authenticated user.
	 *
	 * @return Authenticatable|null
	 */
	public function user()
	{
		// If we've already retrieved the user for the current request we can just
		// return it back immediately. We do not want to fetch the user data on
		// every call to this method because that would be tremendously slow.
		if ( !is_null( $this->user ) )
		{
			return $this->user;
		}

		return $this->user = call_user_func( $this->callback, $this->request );
	}

	/**
	 * Validate a user's credentials.
	 *
	 * @param  array $credentials
	 * @return bool
	 */
	public function validate( array $credentials = [] )
	{
		return !is_null( ( new static( $this->callback, $credentials['request'] ) )->user() );
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
}
