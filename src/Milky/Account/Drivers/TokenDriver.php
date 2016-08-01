<?php namespace Milky\Account\Drivers;

use Milky\Account\Account;
use Milky\Account\Auths\AccountAuth;
use Milky\Http\Request;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class TokenDriver extends AccountDriver
{
	/**
	 * The request instance.
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * The name of the field on the request containing the API token.
	 *
	 * @var string
	 */
	protected $inputKey;

	/**
	 * The name of the token "column" in persistent storage.
	 *
	 * @var string
	 */
	protected $storageKey;

	public function __construct( AccountAuth $auth, Request $request )
	{
		parent::__construct( $auth );
		$this->request = $request;
		$this->inputKey = 'api_token';
		$this->storageKey = 'api_token';
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

		$acct = null;

		$token = $this->getTokenForRequest();

		if ( !empty( $token ) )
			$acct = $this->auth->retrieveByCredentials( [$this->storageKey => $token] );

		return $this->acct = $acct;
	}

	/**
	 * Get the token for the current request.
	 *
	 * @return string
	 */
	protected function getTokenForRequest()
	{
		$token = $this->request->input( $this->inputKey );

		if ( empty( $token ) )
		{
			$token = $this->request->bearerToken();
		}

		if ( empty( $token ) )
		{
			$token = $this->request->getPassword();
		}

		return $token;
	}

	/**
	 * Validate a acct's credentials.
	 *
	 * @param  array $credentials
	 * @return bool
	 */
	public function validate( array $credentials = [] )
	{
		$credentials = [$this->storageKey => $credentials[$this->inputKey]];

		if ( $this->auth->retrieveByCredentials( $credentials ) )
			return true;

		return false;
	}
}
