<?php namespace Milky\Http\Session\Handlers;

use Milky\Cache\Repository;
use SessionHandlerInterface;

class CacheBasedSessionHandler implements SessionHandlerInterface
{
	/**
	 * The cache repository instance.
	 *
	 * @var Repository
	 */
	protected $cache;

	/**
	 * The number of minutes to store the data in the cache.
	 *
	 * @var int
	 */
	protected $minutes;

	/**
	 * Create a new cache driven handler instance.
	 *
	 * @param  Repository $cache
	 * @param  int $minutes
	 */
	public function __construct( Repository $cache, $minutes )
	{
		$this->cache = $cache;
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
		return $this->cache->get( $sessionId, '' );
	}

	/**
	 * {@inheritdoc
	 */
	public function write( $sessionId, $data )
	{
		return $this->cache->put( $sessionId, $data, $this->minutes );
	}

	/**
	 * {@inheritdoc
	 */
	public function destroy( $sessionId )
	{
		return $this->cache->forget( $sessionId );
	}

	/**
	 * {@inheritdoc
	 */
	public function gc( $lifetime )
	{
		return true;
	}

	/**
	 * Get the underlying cache repository.
	 *
	 * @return Repository
	 */
	public function getCache()
	{
		return $this->cache;
	}
}
