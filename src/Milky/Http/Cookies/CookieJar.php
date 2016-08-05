<?php namespace Milky\Http\Cookies;

use Milky\Binding\UniversalBuilder;
use Milky\Helpers\Arr;
use Milky\Impl\Extendable;
use Symfony\Component\HttpFoundation\Cookie;

class CookieJar
{
	use Extendable;

	/**
	 * The default path (if specified).
	 *
	 * @var string
	 */
	protected $path = '/';

	/**
	 * The default domain (if specified).
	 *
	 * @var string
	 */
	protected $domain = null;

	/**
	 * The default secure setting (defaults to false).
	 *
	 * @var bool
	 */
	protected $secure = false;

	/**
	 * All of the cookies queued for sending.
	 *
	 * @var array
	 */
	protected $queued = [];

	/**
	 * @return CookieJar
	 */
	public static function i()
	{
		return UniversalBuilder::resolveClass( static::class );
	}

	/**
	 * Create a new cookie instance.
	 *
	 * @param  string $name
	 * @param  string $value
	 * @param  int $minutes
	 * @param  string $path
	 * @param  string $domain
	 * @param  bool $secure
	 * @param  bool $httpOnly
	 * @return Cookie
	 */
	public function make( $name, $value, $minutes = 0, $path = null, $domain = null, $secure = false, $httpOnly = true )
	{
		list( $path, $domain, $secure ) = $this->getPathAndDomain( $path, $domain, $secure );
		$time = ( $minutes == 0 ) ? 0 : time() + ( $minutes * 60 );

		return new Cookie( $name, $value, $time, $path, $domain, $secure, $httpOnly );
	}

	/**
	 * Create a cookie that lasts "forever" (five years).
	 *
	 * @param  string $name
	 * @param  string $value
	 * @param  string $path
	 * @param  string $domain
	 * @param  bool $secure
	 * @param  bool $httpOnly
	 * @return Cookie
	 */
	public function forever( $name, $value, $path = null, $domain = null, $secure = false, $httpOnly = true )
	{
		return $this->make( $name, $value, 2628000, $path, $domain, $secure, $httpOnly );
	}

	/**
	 * Expire the given cookie.
	 *
	 * @param  string $name
	 * @param  string $path
	 * @param  string $domain
	 * @return Cookie
	 */
	public function forget( $name, $path = null, $domain = null )
	{
		return $this->make( $name, null, -2628000, $path, $domain );
	}

	/**
	 * Determine if a cookie has been queued.
	 *
	 * @param  string $key
	 * @return bool
	 */
	public function hasQueued( $key )
	{
		return !is_null( $this->queued( $key ) );
	}

	/**
	 * Get a queued cookie instance.
	 *
	 * @param  string $key
	 * @param  mixed $default
	 * @return Cookie
	 */
	public function queued( $key, $default = null )
	{
		return Arr::get( $this->queued, $key, $default );
	}

	/**
	 * Queue a cookie to send with the next response.
	 *
	 * @param  mixed
	 */
	public function queue()
	{
		if ( reset( func_get_args() ) instanceof Cookie )
			$cookie = head( func_get_args() );
		else
			$cookie = call_user_func_array( [$this, 'make'], func_get_args() );

		$this->queued[$cookie->getName()] = $cookie;
	}

	/**
	 * Remove a cookie from the queue.
	 *
	 * @param  string $name
	 */
	public function unqueue( $name )
	{
		unset( $this->queued[$name] );
	}

	/**
	 * Get the path and domain, or the default values.
	 *
	 * @param  string $path
	 * @param  string $domain
	 * @param  bool $secure
	 * @return array
	 */
	protected function getPathAndDomain( $path, $domain, $secure = false )
	{
		return [$path ?: $this->path, $domain ?: $this->domain, $secure ?: $this->secure];
	}

	/**
	 * Set the default path and domain for the jar.
	 *
	 * @param  string $path
	 * @param  string $domain
	 * @param  bool $secure
	 * @return $this
	 */
	public function setDefaultPathAndDomain( $path, $domain, $secure = false )
	{
		list( $this->path, $this->domain, $this->secure ) = [$path, $domain, $secure];

		return $this;
	}

	/**
	 * Get the cookies which have been queued for the next request.
	 *
	 * @return array
	 */
	public function getQueuedCookies()
	{
		return $this->queued;
	}
}
