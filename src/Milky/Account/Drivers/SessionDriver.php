<?php namespace Milky\Account\Drivers;

use Milky\Account\Account;
use Milky\Account\Auths\AccountAuth;
use Milky\Framework;
use Milky\Helpers\Str;
use Milky\Http\Cookies\CookieJar;
use Milky\Http\Request;
use Milky\Http\Response;
use Milky\Http\Session\SessionInterface;
use Penoaks\Session\Store;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class SessionDriver extends AccountDriver implements StatefulDriver, SupportsBasicAuth
{
	/**
	 * The account we last attempted to retrieve.
	 *
	 * @var Account
	 */
	protected $lastAttempted;

	/**
	 * Indicates if the account was authenticated via a recaller cookie.
	 *
	 * @var bool
	 */
	protected $viaRemember = false;

	/**
	 * The session used by the guard.
	 *
	 * @var Store
	 */
	protected $session;

	/**
	 * The cookie creator service.
	 *
	 * @var CookieJar
	 */
	protected $cookie;

	/**
	 * The request instance.
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * Indicates if the logout method has been called.
	 *
	 * @var bool
	 */
	protected $loggedOut = false;

	/**
	 * Indicates if a token account retrieval has been attempted.
	 *
	 * @var bool
	 */
	protected $tokenRetrievalAttempted = false;

	public function __construct( AccountAuth $auth, SessionInterface $session, Request $request )
	{
		parent::__construct( $auth );
		$this->session = $session;
		$this->request = $request;
	}

	/**
	 * Get the currently authenticated account.
	 *
	 * @return Account|null
	 */
	public function acct()
	{
		if ( $this->loggedOut )
			return null;

		// If we've already retrieved the account for the current request we can just
		// return it back immediately. We do not want to fetch the account data on
		// every call to this method because that would be tremendously slow.
		if ( !is_null( $this->acct ) )
			return $this->acct;

		$id = $this->session->get( $this->getName() );

		// First we will try to load the account using the identifier in the session if
		// one exists. Otherwise we will check for a "remember me" cookie in this
		// request, and if one exists, attempt to retrieve the account using that.
		$account = null;

		if ( !is_null( $id ) )
			$account = $this->auth->retrieveById( $id );

		// If the account is null, but we decrypt a "recaller" cookie we can attempt to
		// pull the account data on that cookie which serves as a remember cookie on
		// the application. Once we have an account we can return it to the caller.
		$recaller = $this->getRecaller();

		if ( is_null( $account ) && !is_null( $recaller ) )
		{
			$account = $this->getaccountByRecaller( $recaller );

			if ( $account )
			{
				$this->updateSession( $account->getAuthIdentifier() );

				$this->fireLoginEvent( $account, true );
			}
		}

		return $this->acct = $account;
	}

	/**
	 * Get the ID for the currently authenticated account.
	 *
	 * @return int|null
	 */
	public function id()
	{
		if ( $this->loggedOut )
			return null;

		$id = $this->session->get( $this->getName() );

		if ( is_null( $id ) && $this->acct() )
			$id = $this->acct()->getId();

		return $id;
	}

	/**
	 * Pull an account from the repository by its recaller ID.
	 *
	 * @param  string $recaller
	 * @return mixed
	 */
	protected function getaccountByRecaller( $recaller )
	{
		if ( $this->validRecaller( $recaller ) && !$this->tokenRetrievalAttempted )
		{
			$this->tokenRetrievalAttempted = true;

			list( $id, $token ) = explode( '|', $recaller, 2 );

			$this->viaRemember = !is_null( $account = $this->auth->retrieveByToken( $id, $token ) );

			return $account;
		}

		return null;
	}

	/**
	 * Get the decrypted recaller cookie for the request.
	 *
	 * @return string|null
	 */
	protected function getRecaller()
	{
		return $this->request->cookies->get( $this->getRecallerName() );
	}

	/**
	 * Get the account ID from the recaller cookie.
	 *
	 * @return string|null
	 */
	protected function getRecallerId()
	{
		if ( $this->validRecaller( $recaller = $this->getRecaller() ) )
			return head( explode( '|', $recaller ) );

		return null;
	}

	/**
	 * Determine if the recaller cookie is in a valid format.
	 *
	 * @param  mixed $recaller
	 * @return bool
	 */
	protected function validRecaller( $recaller )
	{
		if ( !is_string( $recaller ) || !Str::contains( $recaller, '|' ) )
			return false;

		$segments = explode( '|', $recaller );

		return count( $segments ) == 2 && trim( $segments[0] ) !== '' && trim( $segments[1] ) !== '';
	}

	/**
	 * Log an account into the application without sessions or cookies.
	 *
	 * @param  array $credentials
	 * @return bool
	 */
	public function once( array $credentials = [] )
	{
		if ( $this->validate( $credentials ) )
		{
			$this->setAccount( $this->lastAttempted );

			return true;
		}

		return false;
	}

	/**
	 * Validate an account's credentials.
	 *
	 * @param  array $credentials
	 * @return bool
	 */
	public function validate( array $credentials = [] )
	{
		return $this->attempt( $credentials, false, false );
	}

	/**
	 * Attempt to authenticate using HTTP Basic Auth.
	 *
	 * @param  string $field
	 * @param  array $extraConditions
	 * @return Response|null
	 */
	public function basic( $field = 'email', $extraConditions = [] )
	{
		if ( $this->isAuthenticated() )
			return null;

		// If an accountname is set on the HTTP basic request, we will return out without
		// interrupting the request lifecycle. Otherwise, we'll need to generate a
		// request indicating that the given credentials were invalid for login.
		if ( $this->attemptBasic( $this->getRequest(), $field, $extraConditions ) )
			return null;

		return $this->getBasicResponse();
	}

	/**
	 * Perform a stateless HTTP Basic login attempt.
	 *
	 * @param  string $field
	 * @param  array $extraConditions
	 * @return Response|null
	 */
	public function onceBasic( $field = 'email', $extraConditions = [] )
	{
		$credentials = $this->getBasicCredentials( $this->getRequest(), $field );

		if ( !$this->once( array_merge( $credentials, $extraConditions ) ) )
			return $this->getBasicResponse();

		return null;
	}

	/**
	 * Attempt to authenticate using basic authentication.
	 *
	 * @param  Request $request
	 * @param  string $field
	 * @param  array $extraConditions
	 * @return bool
	 */
	protected function attemptBasic( Request $request, $field, $extraConditions = [] )
	{
		if ( !$request->getUser() )
			return false;

		$credentials = $this->getBasicCredentials( $request, $field );

		return $this->attempt( array_merge( $credentials, $extraConditions ) );
	}

	/**
	 * Get the credential array for a HTTP Basic request.
	 *
	 * @param  Request $request
	 * @param  string $field
	 * @return array
	 */
	protected function getBasicCredentials( Request $request, $field )
	{
		return [$field => $request->getUser(), 'password' => $request->getPassword()];
	}

	/**
	 * Get the response for basic authentication.
	 *
	 * @return Response
	 */
	protected function getBasicResponse()
	{
		$headers = ['WWW-Authenticate' => 'Basic'];

		return new Response( 'Invalid credentials.', 401, $headers );
	}

	/**
	 * Attempt to authenticate an account using the given credentials.
	 *
	 * @param  array $credentials
	 * @param  bool $remember
	 * @param  bool $login
	 * @return bool
	 */
	public function attempt( array $credentials = [], $remember = false, $login = true )
	{
		$this->fireAttemptEvent( $credentials, $remember, $login );

		$this->lastAttempted = $account = $this->auth->retrieveByCredentials( $credentials );

		// If an implementation of accountInterface was returned, we'll ask the provider
		// to validate the account against the given credentials, and if they are in
		// fact valid we'll log the accounts into the application and return true.
		if ( $this->hasValidCredentials( $account, $credentials ) )
		{
			if ( $login )
				$this->login( $account, $remember );

			return true;
		}

		// If the authentication attempt fails we will fire an event so that the account
		// may be notified of any suspicious attempts to access their account from
		// an unrecognized account. A developer may listen to this event as needed.
		if ( $login )
			$this->fireFailedEvent( $account, $credentials );

		return false;
	}

	/**
	 * Determine if the account matches the credentials.
	 *
	 * @param  mixed $account
	 * @param  array $credentials
	 * @return bool
	 */
	protected function hasValidCredentials( $account, $credentials )
	{
		return !is_null( $account ) && $this->auth->validateCredentials( $account, $credentials );
	}

	/**
	 * Fire the attempt event with the arguments.
	 *
	 * @param  array $credentials
	 * @param  bool $remember
	 * @param  bool $login
	 * @return void
	 */
	protected function fireAttemptEvent( array $credentials, $remember, $login )
	{
		Framework::hooks()->trigger( 'acct.attempting', compact( 'credentials', 'remember', 'login' ) );
	}

	/**
	 * Fire the failed authentication attempt event with the given arguments.
	 *
	 * @param  Account|null $account
	 * @param  array $credentials
	 * @return void
	 */
	protected function fireFailedEvent( $account, array $credentials )
	{
		Framework::hooks()->trigger( 'acct.failed', compact( 'account', 'credentials' ) );
	}

	/**
	 * Register an authentication attempt event listener.
	 *
	 * @param  mixed $callback
	 * @return void
	 */
	public function attempting( $callback )
	{
		Framework::hooks()->trigger( 'acct.attempting', compact( 'callback' ) );
	}

	/**
	 * Log an account into the application.
	 *
	 * @param  Account $account
	 * @param  bool $remember
	 * @return void
	 */
	public function login( Account $account, $remember = false )
	{
		$this->updateSession( $account->getId() );

		// If the account should be permanently "remembered" by the application we will
		// queue a permanent cookie that contains the encrypted copy of the account
		// identifier. We will then decrypt this later to retrieve the accounts.
		if ( $remember )
		{
			$this->createRememberTokenIfDoesntExist( $account );

			$this->queueRecallerCookie( $account );
		}

		// If we have an event dispatcher instance set we will fire an event so that
		// any listeners will hook into the authentication events and run actions
		// based on the login and logout events fired from the guard instances.
		$this->fireLoginEvent( $account, $remember );

		$this->setAccount( $account );
	}

	/**
	 * Fire the login event if the dispatcher is set.
	 *
	 * @param  Account $account
	 * @param  bool $remember
	 * @return void
	 */
	protected function fireLoginEvent( $account, $remember = false )
	{
		Framework::hooks()->trigger( 'acct.login', compact( 'account', 'remember' ) );
	}

	/**
	 * Update the session with the given ID.
	 *
	 * @param  string $id
	 * @return void
	 */
	protected function updateSession( $id )
	{
		$this->session->set( $this->getName(), $id );

		$this->session->migrate( true );
	}

	/**
	 * Log the given account ID into the application.
	 *
	 * @param  mixed $id
	 * @param  bool $remember
	 * @return Account|bool
	 */
	public function loginUsingId( $id, $remember = false )
	{
		$account = $this->auth->retrieveById( $id );

		if ( !is_null( $account ) )
		{
			$this->login( $account, $remember );

			return $account;
		}

		return false;
	}

	/**
	 * Log the given account ID into the application without sessions or cookies.
	 *
	 * @param  mixed $id
	 * @return bool
	 */
	public function onceUsingId( $id )
	{
		$account = $this->auth->retrieveById( $id );

		if ( !is_null( $account ) )
		{
			$this->setAccount( $account );

			return true;
		}

		return false;
	}

	/**
	 * Queue the recaller cookie into the cookie jar.
	 *
	 * @param  Account $account
	 * @return void
	 */
	protected function queueRecallerCookie( Account $account )
	{
		$value = $account->getId() . '|' . $account->getRememberToken();

		$this->getCookieJar()->queue( $this->createRecaller( $value ) );
	}

	/**
	 * Create a "remember me" cookie for a given ID.
	 *
	 * @param  string $value
	 * @return Cookie
	 */
	protected function createRecaller( $value )
	{
		return $this->getCookieJar()->forever( $this->getRecallerName(), $value );
	}

	/**
	 * Log the account out of the application.
	 *
	 * @return void
	 */
	public function logout()
	{
		$account = $this->acct();

		$this->clearAccountDataFromStorage();

		if ( !is_null( $this->acct ) )
			$this->refreshRememberToken( $account );

		Framework::hooks()->trigger( 'acct.logout', compact( 'account' ) );

		// Once we have fired the logout event we will clear the accounts out of memory
		// so they are no longer available as the account is no longer considered as
		// being signed into this application and should not be available here.
		$this->acct = null;

		$this->loggedOut = true;
	}

	/**
	 * Remove the account data from the session and cookies.
	 *
	 * @return void
	 */
	protected function clearAccountDataFromStorage()
	{
		$this->session->remove( $this->getName() );

		if ( !is_null( $this->getRecaller() ) )
		{
			$recaller = $this->getRecallerName();

			$this->getCookieJar()->queue( $this->getCookieJar()->forget( $recaller ) );
		}
	}

	/**
	 * Refresh the "remember me" token for the account.
	 *
	 * @param  Account $account
	 * @return void
	 */
	protected function refreshRememberToken( Account $account )
	{
		$account->setRememberToken( $token = Str::random( 60 ) );

		$this->auth->updateRememberToken( $account, $token );
	}

	/**
	 * Create a new "remember me" token for the account if one doesn't already exist.
	 *
	 * @param  Account $account
	 * @return void
	 */
	protected function createRememberTokenIfDoesntExist( Account $account )
	{
		if ( empty( $account->getRememberToken() ) )
			$this->refreshRememberToken( $account );
	}

	/**
	 * Get the cookie creator instance used by the guard.
	 *
	 * @return CookieJar
	 *
	 * @throws \RuntimeException
	 */
	public function getCookieJar()
	{
		if ( !isset( $this->cookie ) )
			throw new \RuntimeException( 'Cookie jar has not been set.' );

		return $this->cookie;
	}

	/**
	 * Set the cookie creator instance used by the guard.
	 *
	 * @param  CookieJar $cookie
	 * @return void
	 */
	public function setCookieJar( CookieJar $cookie )
	{
		$this->cookie = $cookie;
	}

	/**
	 * Get the session store used by the guard.
	 *
	 * @return Store
	 */
	public function getSession()
	{
		return $this->session;
	}

	/**
	 * Set the current account
	 *
	 * @param Account $acct
	 * @return $this
	 */
	public function setAccount( $acct )
	{
		$this->acct = $acct;
		$this->loggedOut = false;

		return $this;
	}

	/**
	 * Get the current request instance.
	 *
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Get the last account we attempted to authenticate.
	 *
	 * @return Account
	 */
	public function getLastAttempted()
	{
		return $this->lastAttempted;
	}

	/**
	 * Get a unique identifier for the auth session value.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'login_session_' . sha1( static::class );
	}

	/**
	 * Get the name of the cookie used to store the "recaller".
	 *
	 * @return string
	 */
	public function getRecallerName()
	{
		return 'remember_session_' . sha1( static::class );
	}

	/**
	 * Determine if the account was authenticated via "remember me" cookie.
	 *
	 * @return bool
	 */
	public function viaRemember()
	{
		return $this->viaRemember;
	}
}
