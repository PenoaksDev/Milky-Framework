<?php namespace Milky\Auth;

use InvalidArgumentException;
use Milky\Framework;

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
	 * @param  string $provider
	 * @return UserProvider
	 *
	 * @throws \InvalidArgumentException
	 */
	public function createUserProvider( $provider )
	{
		$config = Framework::config()->get( 'auth.providers.' . $provider );

		if ( isset( $this->customProviderCreators[$config['driver']] ) )
			return call_user_func( $this->customProviderCreators[$config['driver']], $config );

		switch ( $config['driver'] )
		{
			case 'database':
				return $this->createDatabaseProvider( $config );
			case 'eloquent':
				return $this->createEloquentProvider( $config );
			default:
				throw new InvalidArgumentException( "Authentication user provider [{$config['driver']}] is not defined." );
		}
	}

	/**
	 * Create an instance of the database user provider.
	 *
	 * @param  array $config
	 * @return DatabaseUserProvider
	 */
	protected function createDatabaseProvider( $config )
	{
		$connection = Framework::get( 'db' )->connection();

		return new DatabaseUserProvider( $connection, Framework::get('hash'), $config['table'] );
	}

	/**
	 * Create an instance of the Eloquent user provider.
	 *
	 * @param  array $config
	 * @return EloquentUserProvider
	 */
	protected function createEloquentProvider( $config )
	{
		return new EloquentUserProvider( Framework::get('hash'), $config['model'] );
	}
}
