<?php namespace Milky\Account;

use Milky\Account\Auths\AccountAuth;
use Milky\Account\Guards\Guard;
use Milky\Binding\ServiceResolver;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class AccountServiceResolver extends ServiceResolver
{
	/**
	 * @var AccountManager
	 */
	protected $mgrInstance;

	public function __construct()
	{
		$this->setDefault( 'mgr' );

		$this->addClassAlias( AccountManager::class, 'mgr' );
		$this->addClassAlias( Guard::class, 'guard' );
		$this->addClassAlias( AccountAuth::class, 'auth' );
	}

	public function mgr()
	{
		return $this->mgrInstance ?: $this->mgrInstance = AccountManager::build();
	}

	/**
	 * @return Guard
	 */
	public function guard()
	{
		return $this->mgr()->guard();
	}

	/**
	 * @return AccountAuth
	 */
	public function auth()
	{
		return $this->mgr()->auth();
	}

	public function key()
	{
		return 'account';
	}
}
