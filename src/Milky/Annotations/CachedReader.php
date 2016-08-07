<?php namespace Milky\Annotations;

/**
 * A cache aware annotation reader.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
final class CachedReader implements Reader
{
	/**
	 * @var string
	 */
	private static $CACHE_SALT = '@[Annot]';

	/**
	 * @var Reader
	 */
	private $delegate;

	/**
	 * @var Cache
	 */
	private $cache;

	/**
	 * @var boolean
	 */
	private $debug;

	/**
	 * @var array
	 */
	private $loadedAnnotations = [];

	/**
	 * Constructor.
	 *
	 * @param Reader $reader
	 * @param Cache $cache
	 * @param bool $debug
	 */
	public function __construct( Reader $reader, Cache $cache, $debug = false )
	{
		$this->delegate = $reader;
		$this->cache = $cache;
		$this->debug = (boolean) $debug;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getClassAnnotations( \ReflectionClass $class )
	{
		$cacheKey = $class->getName();

		if ( isset( $this->loadedAnnotations[$cacheKey] ) )
			return $this->loadedAnnotations[$cacheKey];

		if ( false == ( $annots = $this->fetchFromCache( $cacheKey, $class ) ) )
		{
			$annots = $this->delegate->getClassAnnotations( $class );
			$this->saveToCache( $cacheKey, $annots );
		}

		return $this->loadedAnnotations[$cacheKey] = $annots;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getClassAnnotation( \ReflectionClass $class, $annotationName )
	{
		foreach ( $this->getClassAnnotations( $class ) as $annot )
			if ( $annot instanceof $annotationName )
				return $annot;

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPropertyAnnotations( \ReflectionProperty $property )
	{
		$class = $property->getDeclaringClass();
		$cacheKey = $class->getName() . '$' . $property->getName();

		if ( isset( $this->loadedAnnotations[$cacheKey] ) )
			return $this->loadedAnnotations[$cacheKey];

		if ( false == ( $annots = $this->fetchFromCache( $cacheKey, $class ) ) )
		{
			$annots = $this->delegate->getPropertyAnnotations( $property );
			$this->saveToCache( $cacheKey, $annots );
		}

		return $this->loadedAnnotations[$cacheKey] = $annots;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPropertyAnnotation( \ReflectionProperty $property, $annotationName )
	{
		foreach ( $this->getPropertyAnnotations( $property ) as $annot )
			if ( $annot instanceof $annotationName )
				return $annot;

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMethodAnnotations( \ReflectionMethod $method )
	{
		$class = $method->getDeclaringClass();
		$cacheKey = $class->getName() . '#' . $method->getName();

		if ( isset( $this->loadedAnnotations[$cacheKey] ) )
			return $this->loadedAnnotations[$cacheKey];

		if ( false == ( $annots = $this->fetchFromCache( $cacheKey, $class ) ) )
		{
			$annots = $this->delegate->getMethodAnnotations( $method );
			$this->saveToCache( $cacheKey, $annots );
		}

		return $this->loadedAnnotations[$cacheKey] = $annots;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMethodAnnotation( \ReflectionMethod $method, $annotationName )
	{
		foreach ( $this->getMethodAnnotations( $method ) as $annot )
			if ( $annot instanceof $annotationName )
				return $annot;

		return null;
	}

	/**
	 * Clears loaded annotations.
	 *
	 * @return void
	 */
	public function clearLoadedAnnotations()
	{
		$this->loadedAnnotations = [];
	}

	/**
	 * Fetches a value from the cache.
	 *
	 * @param string $rawCacheKey The cache key.
	 * @param \ReflectionClass $class The related class.
	 *
	 * @return mixed The cached value or false when the value is not in cache.
	 */
	private function fetchFromCache( $rawCacheKey, \ReflectionClass $class )
	{
		$cacheKey = $rawCacheKey . self::$CACHE_SALT;
		if ( ( $data = $this->cache->fetch( $cacheKey ) ) !== false )
			if ( !$this->debug || $this->isCacheFresh( $cacheKey, $class ) )
				return $data;

		return false;
	}

	/**
	 * Saves a value to the cache.
	 *
	 * @param string $rawCacheKey The cache key.
	 * @param mixed $value The value.
	 *
	 * @return void
	 */
	private function saveToCache( $rawCacheKey, $value )
	{
		$cacheKey = $rawCacheKey . self::$CACHE_SALT;
		$this->cache->save( $cacheKey, $value );
		if ( $this->debug )
			$this->cache->save( '[C]' . $cacheKey, time() );
	}

	/**
	 * Checks if the cache is fresh.
	 *
	 * @param string $cacheKey
	 * @param \ReflectionClass $class
	 *
	 * @return boolean
	 */
	private function isCacheFresh( $cacheKey, \ReflectionClass $class )
	{
		if ( false === $filename = $class->getFileName() )
			return true;

		return $this->cache->fetch( '[C]' . $cacheKey ) >= filemtime( $filename );
	}
}
