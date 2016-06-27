<?php

namespace Foundation\Http\Middleware;

use Closure;
use Foundation\Application;
use Symfony\Component\HttpFoundation\Cookie;
use Foundation\Contracts\Encryption\Encrypter;
use Foundation\Session\TokenMismatchException;

class VerifyCsrfToken
{
	/**
	 * The application instance.
	 *
	 * @var \Foundation\Application
	 */
	protected $app;

	/**
	 * The encrypter implementation.
	 *
	 * @var \Foundation\Contracts\Encryption\Encrypter
	 */
	protected $encrypter;

	/**
	 * The URIs that should be excluded from CSRF verification.
	 *
	 * @var array
	 */
	protected $except = [];

	/**
	 * Create a new middleware instance.
	 *
	 * @param  \Foundation\Application  $app
	 * @param  \Foundation\Contracts\Encryption\Encrypter  $encrypter
	 * @return void
	 */
	public function __construct(Application $app, Encrypter $encrypter)
	{
		$this->app = $app;
		$this->encrypter = $encrypter;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Foundation\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 *
	 * @throws \Foundation\Session\TokenMismatchException
	 */
	public function handle($request, Closure $next)
	{
		if (
			$this->isReading($request) ||
			$this->runningUnitTests() ||
			$this->shouldPassThrough($request) ||
			$this->tokensMatch($request)
		) {
			return $this->addCookieToResponse($request, $next($request));
		}

		throw new TokenMismatchException;
	}

	/**
	 * Determine if the request has a URI that should pass through CSRF verification.
	 *
	 * @param  \Foundation\Http\Request  $request
	 * @return bool
	 */
	protected function shouldPassThrough($request)
	{
		foreach ($this->except as $except) {
			if ($except !== '/') {
				$except = trim($except, '/');
			}

			if ($request->is($except)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the application is running unit tests.
	 *
	 * @return bool
	 */
	protected function runningUnitTests()
	{
		return $this->app->runningInConsole() && $this->app->runningUnitTests();
	}

	/**
	 * Determine if the session and input CSRF tokens match.
	 *
	 * @param  \Foundation\Http\Request  $request
	 * @return bool
	 */
	protected function tokensMatch($request)
	{
		$sessionToken = $request->session()->token();

		$token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');

		if (! $token && $header = $request->header('X-XSRF-TOKEN')) {
			$token = $this->encrypter->decrypt($header);
		}

		if (! is_string($sessionToken) || ! is_string($token)) {
			return false;
		}

		return hash_equals($sessionToken, $token);
	}

	/**
	 * Add the CSRF token to the response cookies.
	 *
	 * @param  \Foundation\Http\Request  $request
	 * @param  \Foundation\Http\Response  $response
	 * @return \Foundation\Http\Response
	 */
	protected function addCookieToResponse($request, $response)
	{
		$config = config('session');

		$response->headers->setCookie(
			new Cookie(
				'XSRF-TOKEN', $request->session()->token(), time() + 60 * 120,
				$config['path'], $config['domain'], $config['secure'], false
			)
		);

		return $response;
	}

	/**
	 * Determine if the HTTP request uses a ‘read’ verb.
	 *
	 * @param  \Foundation\Http\Request  $request
	 * @return bool
	 */
	protected function isReading($request)
	{
		return in_array($request->method(), ['HEAD', 'GET', 'OPTIONS']);
	}
}
