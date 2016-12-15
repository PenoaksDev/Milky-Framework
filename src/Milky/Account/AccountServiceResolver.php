<?php namespace Milky\Account;

use Milky\Account\Auths\AccountAuth;
use Milky\Account\Guards\Guard;
use Milky\Account\Passwords\PasswordBrokerManager;
use Milky\Account\Permissions\PermissionManager;
use Milky\Binding\ServiceResolver;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
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
	private $mgrInstance;

	/**
	 * @var PermissionManager
	 */
	private $permissionMgrInstance;

	/**
	 * @var PasswordBrokerManager
	 */
	private $passwordInstance;

	public function __construct()
	{
		$this->setDefault( 'mgr' );

		$this->addClassAlias( AccountManager::class, 'mgr' );
		$this->addClassAlias( PermissionManager::class, 'perm' );
		$this->addClassAlias( Guard::class, 'guard' );
		$this->addClassAlias( AccountAuth::class, 'auth' );
	}

	public function password()
	{
		return $this->passwordInstance ?: $this->passwordInstance = new PasswordBrokerManager();
	}

	public function passwordBroker()
	{
		return $this->password()->broker();
	}

	/**
	 * @return AccountManager
	 */
	public function mgr()
	{
		return $this->mgrInstance ?: $this->mgrInstance = AccountManager::build();
	}

	/**
	 * @return PermissionManager
	 */
	public function perm()
	{
		return $this->permissionMgrInstance ?: $this->permissionMgrInstance = new PermissionManager();
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
