<?php

namesapce Penoaks\Auth;

use InvalidArgumentException;

trait CreatesUserProviders
{
	/**
	 * The registered custom provider creators.
	 *
	 * @var array
	 */
	protected $customProviderCreators = [];

	/**
	 * Create the user provider implementation for the driver.
	 *
	 * @param  string  $provider
	 * @return \Penoaks\Contracts\Auth\UserProvider
	 *
	 * @throws \InvalidArgumentException
	 */
	public function createUserProvider($provider)
	{
		$config = $this->fw->bindings['config']['auth.providers.'.$provider];

		if (isset($this->customProviderCreators[$config['driver']]))
{
			return call_user_func(
				$this->customProviderCreators[$config['driver']], $this->fw, $config
			);
		}

		switch ($config['driver'])
{
			case 'database':
				return $this->createDatabaseProvider($config);
			case 'eloquent':
				return $this->createEloquentProvider($config);
			default:
				throw new InvalidArgumentException("Authentication user provider [{$config['driver']}] is not defined.");
		}
	}

	/**
	 * Create an instance of the database user provider.
	 *
	 * @param  array  $config
	 * @return \Penoaks\Auth\DatabaseUserProvider
	 */
	protected function createDatabaseProvider($config)
	{
		$connection = $this->fw->bindings['db']->connection();

		return new DatabaseUserProvider($connection, $this->fw->bindings['hash'], $config['table']);
	}

	/**
	 * Create an instance of the Eloquent user provider.
	 *
	 * @param  array  $config
	 * @return \Penoaks\Auth\EloquentUserProvider
	 */
	protected function createEloquentProvider($config)
	{
		return new EloquentUserProvider($this->fw->bindings['hash'], $config['model']);
	}
}
