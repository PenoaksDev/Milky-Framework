<?php namespace Milky\Account\Guards;

use Milky\Account\Auths\AccountAuth;
use Milky\Account\Types\Account;
use Milky\Facades\Hooks;
use Milky\Helpers\Str;
use Milky\Http\Cookies\CookieJar;
use Milky\Http\Response;
use Milky\Http\Session\Store;
use RuntimeException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionGuard extends StatefulGuard implements SupportsBasicAuth
{
	/**
	 * The guard name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The acct we last attempted to retrieve.
	 *
	 * @var Account
	 */
	protected $lastAttempted;

	/**
	 * Indicates if the acct was authenticated via a recaller cookie.
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
	 * The Illuminate cookie creator service.
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
	 * Indicates if a token acct retrieval has been attempted.
	 *
	 * @var bool
	 */
	protected $tokenRetrievalAttempted = false;

	/**
	 * Create a new authentication guard.
	 *
	 * @param  string $name
	 * @param  AccountAuth $auth
	 * @param  SessionInterface $session
	 * @param  Request $request
	 */
	public function __construct( $name, AccountAuth $auth, SessionInterface $session, Request $request, CookieJar $cookie )
	{
		$this->name = $name;

		parent::__construct( $auth );

		$this->session = $session;
		$this->request = $request;
		$this->cookie = $cookie;
	}

	/**
	 * Get the currently authenticated acct.
	 *
	 * @return Account|null
	 */
	public function acct()
	{
		if ( $this->loggedOut )
			return null;

		// If we've already retrieved the acct for the current request we can just
		// return it back immediately. We do not want to fetch the acct data on
		// every call to this method because that would be tremendously slow.
		if ( !is_null( $this->acct ) )
			return $this->acct;

		$id = $this->session->get( $this->getName() );

		// First we will try to load the acct using the identifier in the session if
		// one exists. Otherwise we will check for a "remember me" cookie in this
		// request, and if one exists, attempt to retrieve the acct using that.
		$acct = null;

		if ( !is_null( $id ) )
			$acct = $this->auth->retrieveById( $id );

		// If the acct is null, but we decrypt a "recaller" cookie we can attempt to
		// pull the acct data on that cookie which serves as a remember cookie on
		// the application. Once we have a acct we can return it to the caller.
		$recaller = $this->getRecaller();

		if ( is_null( $acct ) && !is_null( $recaller ) )
		{
			$acct = $this->getAcctByRecaller( $recaller );

			if ( $acct )
			{
				$this->updateSession( $acct->getAuthIdentifier() );
				$this->fireLoginEvent( $acct, true );
			}
		}

		return $this->acct = $acct;
	}

	/**
	 * Get the ID for the currently authenticated acct.
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
	 * Pull a acct from the repository by its recaller ID.
	 *
	 * @param  string $recaller
	 * @return mixed
	 */
	protected function getAcctByRecaller( $recaller )
	{
		if ( $this->validRecaller( $recaller ) && !$this->tokenRetrievalAttempted )
		{
			$this->tokenRetrievalAttempted = true;
			list( $id, $token ) = explode( '|', $recaller, 2 );
			$this->viaRemember = !is_null( $acct = $this->auth->retrieveByToken( $id, $token ) );

			return $acct;
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
	 * Get the acct ID from the recaller cookie.
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
	 * Log a acct into the application without sessions or cookies.
	 *
	 * @param  array $credentials
	 * @return bool
	 */
	public function once( array $credentials = [] )
	{
		if ( $this->validate( $credentials ) )
		{
			$this->setAcct( $this->lastAttempted );

			return true;
		}

		return false;
	}

	/**
	 * Validate a acct's credentials.
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
		if ( $this->check() )
			return null;

		// If a acctname is set on the HTTP basic request, we will return out without
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
	 * Attempt to authenticate a acct using the given credentials.
	 *
	 * @param  array $credentials
	 * @param  bool $remember
	 * @param  bool $login
	 * @return bool
	 */
	public function attempt( array $credentials = [], $remember = false, $login = true )
	{
		$this->fireAttemptEvent( $credentials, $remember, $login );

		$this->lastAttempted = $acct = $this->auth->retrieveByCredentials( $credentials );

		// If an implementation of AcctInterface was returned, we'll ask the provider
		// to validate the acct against the given credentials, and if they are in
		// fact valid we'll log the accts into the application and return true.
		if ( $this->hasValidCredentials( $acct, $credentials ) )
		{
			if ( $login )
				$this->login( $acct, $remember );

			return true;
		}

		// If the authentication attempt fails we will fire an event so that the acct
		// may be notified of any suspicious attempts to access their account from
		// an unrecognized acct. A developer may listen to this event as needed.
		if ( $login )
			$this->fireFailedEvent( $acct, $credentials );

		return false;
	}

	/**
	 * Determine if the acct matches the credentials.
	 *
	 * @param  mixed $acct
	 * @param  array $credentials
	 * @return bool
	 */
	protected function hasValidCredentials( $acct, $credentials )
	{
		return !is_null( $acct ) && $this->auth->validateCredentials( $acct, $credentials );
	}

	/**
	 * Fire the attempt event with the arguments.
	 *
	 * @param  array $credentials
	 * @param  bool $remember
	 * @param  bool $login
	 */
	protected function fireAttemptEvent( array $credentials, $remember, $login )
	{
		Hooks::trigger( 'acct.attempting', compact( 'credentials', 'remember', 'login' ) );
	}

	/**
	 * Fire the failed authentication attempt event with the given arguments.
	 *
	 * @param  Account|null $acct
	 * @param  array $credentials
	 */
	protected function fireFailedEvent( $acct, array $credentials )
	{
		Hooks::trigger( 'acct.failed', compact( 'acct', 'credentials' ) );
	}

	/**
	 * Register an authentication attempt event listener.
	 *
	 * @param  mixed $callback
	 */
	public function attempting( $callback )
	{
		Hooks::trigger( 'acct.attempting', compact( 'callback' ) );
	}

	/**
	 * Log a acct into the application.
	 *
	 * @param  Account $acct
	 * @param  bool $remember
	 */
	public function login( Account $acct, $remember = false )
	{
		$this->updateSession( $acct->getId() );

		// If the acct should be permanently "remembered" by the application we will
		// queue a permanent cookie that contains the encrypted copy of the acct
		// identifier. We will then decrypt this later to retrieve the accts.
		if ( $remember )
		{
			$this->createRememberTokenIfDoesntExist( $acct );

			$this->queueRecallerCookie( $acct );
		}

		// If we have an event dispatcher instance set we will fire an event so that
		// any listeners will hook into the authentication events and run actions
		// based on the login and logout events fired from the guard instances.
		$this->fireLoginEvent( $acct, $remember );

		$this->setAcct( $acct );
	}

	/**
	 * Fire the login event if the dispatcher is set.
	 *
	 * @param  Account $acct
	 * @param  bool $remember
	 */
	protected function fireLoginEvent( $acct, $remember = false )
	{
		Hooks::trigger( 'acct.login', compact( 'acct', 'remember' ) );
	}

	/**
	 * Update the session with the given ID.
	 *
	 * @param  string $id
	 */
	protected function updateSession( $id )
	{
		$this->session->set( $this->getName(), $id );

		$this->session->migrate( true );
	}

	/**
	 * Log the given acct ID into the application.
	 *
	 * @param  mixed $id
	 * @param  bool $remember
	 * @return Account|bool
	 */
	public function loginUsingId( $id, $remember = false )
	{
		$acct = $this->auth->retrieveById( $id );

		if ( !is_null( $acct ) )
		{
			$this->login( $acct, $remember );

			return $acct;
		}

		return false;
	}

	/**
	 * Log the given acct ID into the application without sessions or cookies.
	 *
	 * @param  mixed $id
	 * @return bool
	 */
	public function onceUsingId( $id )
	{
		$acct = $this->auth->retrieveById( $id );

		if ( !is_null( $acct ) )
		{
			$this->setAcct( $acct );

			return true;
		}

		return false;
	}

	/**
	 * Queue the recaller cookie into the cookie jar.
	 *
	 * @param  Account $acct
	 */
	protected function queueRecallerCookie( Account $acct )
	{
		$value = $acct->getId() . '|' . $acct->getRememberToken();

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
	 * Log the acct out of the application.
	 *
	 */
	public function logout()
	{
		$acct = $this->acct();

		// If we have an event dispatcher instance, we can fire off the logout event
		// so any further processing can be done. This allows the developer to be
		// listening for anytime a acct signs out of this application manually.
		$this->clearAcctDataFromStorage();

		if ( !is_null( $this->acct ) )
			$this->refreshRememberToken( $acct );

		Hooks::trigger( 'acct.logout', compact( 'acct' ) );

		// Once we have fired the logout event we will clear the accts out of memory
		// so they are no longer available as the acct is no longer considered as
		// being signed into this application and should not be available here.
		$this->acct = null;
		$this->loggedOut = true;
	}

	/**
	 * Remove the acct data from the session and cookies.
	 *
	 */
	protected function clearAcctDataFromStorage()
	{
		$this->session->remove( $this->getName() );

		if ( !is_null( $this->getRecaller() ) )
		{
			$recaller = $this->getRecallerName();
			$this->getCookieJar()->queue( $this->getCookieJar()->forget( $recaller ) );
		}
	}

	/**
	 * Refresh the "remember me" token for the acct.
	 *
	 * @param  Account $acct
	 */
	protected function refreshRememberToken( Account $acct )
	{
		$acct->setRememberToken( $token = Str::random( 60 ) );
		$this->auth->updateRememberToken( $acct, $token );
	}

	/**
	 * Create a new "remember me" token for the acct if one does not already exist.
	 *
	 * @param  Account $acct
	 */
	protected function createRememberTokenIfDoesntExist( Account $acct )
	{
		if ( empty( $acct->getRememberToken() ) )
			$this->refreshRememberToken( $acct );
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
			throw new RuntimeException( 'Cookie jar has not been set.' );

		return $this->cookie;
	}

	/**
	 * Set the cookie creator instance used by the guard.
	 *
	 * @param  CookieJar $cookie
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
	 * Return the currently cached acct.
	 *
	 * @return Account|null
	 */
	public function getAcct()
	{
		return $this->acct;
	}

	/**
	 * Set the current acct.
	 *
	 * @param  Account $acct
	 * @return $this
	 */
	public function setAcct( Account $acct )
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
		return $this->request ?: Request::createFromGlobals();
	}

	/**
	 * Set the current request instance.
	 *
	 * @param  Request $request
	 * @return $this
	 */
	public function setRequest( Request $request )
	{
		$this->request = $request;

		return $this;
	}

	/**
	 * Get the last acct we attempted to authenticate.
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
		return 'login_' . $this->name . '_' . sha1( static::class );
	}

	/**
	 * Get the name of the cookie used to store the "recaller".
	 *
	 * @return string
	 */
	public function getRecallerName()
	{
		return 'remember_' . $this->name . '_' . sha1( static::class );
	}

	/**
	 * Determine if the acct was authenticated via "remember me" cookie.
	 *
	 * @return bool
	 */
	public function viaRemember()
	{
		return $this->viaRemember;
	}

	/**
	 * Get the default Guard Name
	 *
	 * @return string
	 */
	public function name()
	{
		return 'session';
	}
}
