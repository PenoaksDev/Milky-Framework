<?php

namespace Penoaks\Auth;

use Closure;
use InvalidArgumentException;
use Penoaks\Contracts\Auth\Factory as FactoryContract;

class AuthManager implements FactoryContract
{
	use CreatesUserProviders;

	/**
	 * The application instance.
	 *
	 * @var \Penoaks\Framework
	 */
	protected $fw;

	/**
	 * The registered custom driver creators.
	 *
	 * @var array
	 */
	protected $customCreators = [];

	/**
	 * The array of created "drivers".
	 *
	 * @var array
	 */
	protected $guards = [];

	/**
	 * The user resolver shared by various services.
	 *
	 * Determines the default user for Gate, Request, and the Authenticatable contract.
	 *
	 * @var \Closure
	 */
	protected $userResolver;

	/**
	 * Create a new Auth manager instance.
	 *
	 * @param  \Penoaks\Framework  $fw
	 * @return void
	 */
	public function __construct($fw)
	{
		$this->fw = $fw;

		$this->userResolver = function ($guard = null)
{
			return $this->guard($guard)->user();
		};
	}

	/**
	 * Attempt to get the guard from the local cache.
	 *
	 * @param  string  $name
	 * @return \Penoaks\Contracts\Auth\Guard|\Penoaks\Contracts\Auth\StatefulGuard
	 */
	public function guard($name = null)
	{
		$name = $name ?: $this->getDefaultDriver();

		return isset($this->guards[$name])
					? $this->guards[$name]
					: $this->guards[$name] = $this->resolve($name);
	}

	/**
	 * Resolve the given guard.
	 *
	 * @param  string  $name
	 * @return \Penoaks\Contracts\Auth\Guard|\Penoaks\Contracts\Auth\StatefulGuard
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function resolve($name)
	{
		$config = $this->getConfig($name);

		if (is_null($config))
{
			throw new InvalidArgumentException("Auth guard [{$name}] is not defined.");
		}

		if (isset($this->customCreators[$config['driver']]))
{
			return $this->callCustomCreator($name, $config);
		}
else
{
			$driverMethod = 'create'.ucfirst($config['driver']).'Driver';

			if (method_exists($this, $driverMethod))
{
				return $this->{$driverMethod}($name, $config);
			}
else
{
				throw new InvalidArgumentException("Auth guard driver [{$name}] is not defined.");
			}
		}
	}

	/**
	 * Call a custom driver creator.
	 *
	 * @param  string  $name
	 * @param  array  $config
	 * @return mixed
	 */
	protected function callCustomCreator($name, array $config)
	{
		return $this->customCreators[$config['driver']]($this->fw, $name, $config);
	}

	/**
	 * Create a session based authentication guard.
	 *
	 * @param  string  $name
	 * @param  array  $config
	 * @return \Penoaks\Auth\SessionGuard
	 */
	public function createSessionDriver($name, $config)
	{
		$provider = $this->createUserProvider($config['provider']);

		$guard = new SessionGuard($name, $provider, $this->fw->bindings['session.store']);

		// When using the remember me functionality of the authentication services we
		// will need to be set the encryption instance of the guard, which allows
		// secure, encrypted cookie values to get generated for those cookies.
		if (method_exists($guard, 'setCookieJar'))
{
			$guard->setCookieJar($this->fw->bindings['cookie']);
		}

		if (method_exists($guard, 'setDispatcher'))
{
			$guard->setDispatcher($this->fw->bindings['events']);
		}

		if (method_exists($guard, 'setRequest'))
{
			$guard->setRequest($this->fw->refresh('request', $guard, 'setRequest'));
		}

		return $guard;
	}

	/**
	 * Create a token based authentication guard.
	 *
	 * @param  string  $name
	 * @param  array  $config
	 * @return \Penoaks\Auth\TokenGuard
	 */
	public function createTokenDriver($name, $config)
	{
		// The token guard implements a basic API token based guard implementation
		// that takes an API token field from the request and matches it to the
		// user in the database or another persistence layer where users are.
		$guard = new TokenGuard(
			$this->createUserProvider($config['provider']),
			$this->fw->bindings['request']
		);

		$this->fw->refresh('request', $guard, 'setRequest');

		return $guard;
	}

	/**
	 * Get the guard configuration.
	 *
	 * @param  string  $name
	 * @return array
	 */
	protected function getConfig($name)
	{
		return $this->fw->bindings['config']["auth.guards.{$name}"];
	}

	/**
	 * Get the default authentication driver name.
	 *
	 * @return string
	 */
	public function getDefaultDriver()
	{
		return $this->fw->bindings['config']['auth.defaults.guard'];
	}

	/**
	 * Set the default guard driver the factory should serve.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function shouldUse($name)
	{
		$this->setDefaultDriver($name);

		$this->userResolver = function ($name = null)
{
			return $this->guard($name)->user();
		};
	}

	/**
	 * Set the default authentication driver name.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function setDefaultDriver($name)
	{
		$this->fw->bindings['config']['auth.defaults.guard'] = $name;
	}

	/**
	 * Register a new callback based request guard.
	 *
	 * @param  string  $driver
	 * @param  callable  $callback
	 * @return $this
	 */
	public function viaRequest($driver, callable $callback)
	{
		return $this->extend($driver, function () use ($callback)
{
			$guard = new RequestGuard($callback, $this->fw->bindings['request']);

			$this->fw->refresh('request', $guard, 'setRequest');

			return $guard;
		});
	}

	/**
	 * Get the user resolver callback.
	 *
	 * @return \Closure
	 */
	public function userResolver()
	{
		return $this->userResolver;
	}

	/**
	 * Set the callback to be used to resolve users.
	 *
	 * @param  \Closure  $userResolver
	 * @return $this
	 */
	public function resolveUsersUsing(Closure $userResolver)
	{
		$this->userResolver = $userResolver;

		return $this;
	}

	/**
	 * Register a custom driver creator Closure.
	 *
	 * @param  string  $driver
	 * @param  \Closure  $callback
	 * @return $this
	 */
	public function extend($driver, Closure $callback)
	{
		$this->customCreators[$driver] = $callback;

		return $this;
	}

	/**
	 * Register a custom provider creator Closure.
	 *
	 * @param  string  $name
	 * @param  \Closure  $callback
	 * @return $this
	 */
	public function provider($name, Closure $callback)
	{
		$this->customProviderCreators[$name] = $callback;

		return $this;
	}

	/**
	 * Dynamically call the default driver instance.
	 *
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array([$this->guard(), $method], $parameters);
	}
}
