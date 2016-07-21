<?php namespace Milky\Http\Session;

use Carbon\Carbon;
use Milky\Http\Cookies\CookieJar;
use SessionHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

class CookieSessionHandler implements SessionHandlerInterface
{
	/**
	 * The cookie jar instance.
	 *
	 * @var Factory
	 */
	protected $cookie;

	/**
	 * The request instance.
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * Create a new cookie driven handler instance.
	 *
	 * @param  CookieJar $cookie
	 * @param  int $minutes
	 */
	public function __construct( CookieJar $cookie, $minutes )
	{
		$this->cookie = $cookie;
		$this->minutes = $minutes;
	}

	/**
	 * {@inheritdoc
	 */
	public function open( $savePath, $sessionName )
	{
		return true;
	}

	/**
	 * {@inheritdoc
	 */
	public function close()
	{
		return true;
	}

	/**
	 * {@inheritdoc
	 */
	public function read( $sessionId )
	{
		$value = $this->request->cookies->get( $sessionId ) ?: '';

		if ( !is_null( $decoded = json_decode( $value, true ) ) && is_array( $decoded ) )
		{
			if ( isset( $decoded['expires'] ) && time() <= $decoded['expires'] )
			{
				return $decoded['data'];
			}
		}

		return '';
	}

	/**
	 * {@inheritdoc
	 */
	public function write( $sessionId, $data )
	{
		$this->cookie->queue( $sessionId, json_encode( [
			'data' => $data,
			'expires' => Carbon::now()->addMinutes( $this->minutes )->getTimestamp(),
		] ), $this->minutes );
	}

	/**
	 * {@inheritdoc
	 */
	public function destroy( $sessionId )
	{
		$this->cookie->queue( $this->cookie->forget( $sessionId ) );
	}

	/**
	 * {@inheritdoc
	 */
	public function gc( $lifetime )
	{
		return true;
	}

	/**
	 * Set the request instance.
	 *
	 * @param  Request $request
	 */
	public function setRequest( Request $request )
	{
		$this->request = $request;
	}
}
