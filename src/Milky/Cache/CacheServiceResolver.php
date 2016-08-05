<?php namespace Milky\Cache;

use Milky\Binding\Resolvers\ServiceResolver;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class CacheServiceResolver extends ServiceResolver
{
	private $mgrInstance;
	private $limiterInstance;

	public function __construct()
	{
		$this->setDefault( 'mgr' );

		$this->addClassAlias( CacheManager::class, 'mgr' );
		$this->addClassAlias( RateLimiter::class, 'limiter' );
	}

	public function mgr()
	{
		return $this->mgrInstance ?: $this->mgrInstance = new CacheManager();
	}

	public function limiter()
	{
		return $this->limiterInstance ?: $this->limiterInstance = new RateLimiter( $this->mgr()->repository() );
	}

	public function key()
	{
		return 'cache';
	}
}
