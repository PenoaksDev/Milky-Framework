<?php namespace Milky\Binding;

use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Milky\Bus\BusDispatcher;
use Milky\Cache\MemcachedConnector;
use Milky\Database\Eloquent\Factory as EloquentFactory;
use Milky\Encryption\Encrypter;
use Milky\Exceptions\EncryptException;
use Milky\Facades\Config;
use Milky\Filesystem\Filesystem;
use Milky\Framework;
use Milky\Hashing\BcryptHasher;
use Milky\Helpers\Str;
use Milky\Redis\Redis;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class FrameworkServiceResolver extends ServiceResolver
{
	private $encrypterInstance;
	private $filesInstance;
	private $hashInstance;
	private $memcachedInstance;
	private $dispatcherInstance;

	public function __construct()
	{
		$this->setDefault( 'instance' );

		$this->addClassAlias( Encrypter::class, 'encrypter' );
		$this->addClassAlias( Filesystem::class, 'files' );
		$this->addClassAlias( BcryptHasher::class, 'hash' );
		$this->addClassAlias( MemcachedConnector::class, 'memcached' );
		$this->addClassAlias( BusDispatcher::class, 'dispatcher' );
		$this->addClassAlias( Redis::class, 'redis' );
		$this->addClassAlias( FakerGenerator::class, 'fakerGenerator' );
		$this->addClassAlias( EloquentFactory::class, 'eloquentFactory' );
	}

	public function instance()
	{
		return Framework::fw();
	}

	public function encrypter()
	{
		if ( is_null( $this->encrypterInstance ) )
		{
			$config = Config::get( 'app' );

			if ( Str::startsWith( $key = $config['key'], 'base64:' ) )
				$key = base64_decode( substr( $key, 7 ) );
			$cipher = $config['cipher'];

			if ( Encrypter::supported( $key, $cipher ) )
				$this->encrypterInstance = new Encrypter( $key, $cipher );
			else
				throw new EncryptException( 'No supported encrypter found. The cipher and / or key length are invalid.' );
		}

		return $this->encrypterInstance;
	}

	public function files()
	{
		return $this->filesInstance ?: $this->filesInstance = new Filesystem();
	}

	public function hash()
	{
		return $this->hashInstance ?: $this->hashInstance = new BcryptHasher();
	}

	public function memcached()
	{
		return $this->memcachedInstance ?: $this->memcachedInstance = new MemcachedConnector();
	}

	public function redis()
	{
		return new Redis( Config::get( 'database.redis' ) );
	}

	public function dispatcher()
	{
		if( is_null( $this->dispatcherInstance ) )
			$this->dispatcherInstance = new BusDispatcher( function ( $connection = null )
			{
				return UniversalBuilder::resolve( 'queue.connection' );
			} );

		return $this->dispatcherInstance;
	}

	/**
	 * @return FakerGenerator
	 */
	public function fakerGenerator()
	{
		return FakerFactory::create();
	}

	/**
	 * @return EloquentFactory
	 */
	public function eloquentFactory()
	{
		return EloquentFactory::construct( $this->fakerGenerator(), Framework::fw()->buildPath( '__database', 'factories' ) );
	}

	public function key()
	{
		return 'fw';
	}
}
