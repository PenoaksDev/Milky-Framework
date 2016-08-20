<?php namespace Milky\Account\Passwords;

use InvalidArgumentException;
use Milky\Account\AccountManager;
use Milky\Database\DatabaseManager;
use Milky\Facades\Config;
use Milky\Helpers\Str;
use Milky\Mail\Mailer;

class PasswordBrokerManager
{
	/**
	 * The array of created "drivers".
	 *
	 * @var array
	 */
	protected $brokers = [];

	/**
	 * Attempt to get the broker from the local cache.
	 *
	 * @param  string $name
	 * @return PasswordBroker
	 */
	public function broker( $name = null )
	{
		$name = $name ?: $this->getDefaultDriver();

		return isset( $this->brokers[$name] ) ? $this->brokers[$name] : $this->brokers[$name] = $this->resolve( $name );
	}

	/**
	 * Resolve the given broker.
	 *
	 * @param  string $name
	 * @return PasswordBroker
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function resolve( $name )
	{
		$config = $this->getConfig( $name );

		if ( is_null( $config ) )
			throw new InvalidArgumentException( "Password resetter [{$name}] is not defined." );

		// The password broker uses a token repository to validate tokens and send user
		// password e-mails, as well as validating that password reset process as an
		// aggregate service of sorts providing a convenient interface for resets.
		return new PasswordBroker( $this->createTokenRepository( $config ), AccountManager::i()->resolveAuth( $config['provider'] ), Mailer::i(), $config['email'] );
	}

	/**
	 * Create a token repository instance based on the given configuration.
	 *
	 * @param  array $config
	 * @return TokenRepositoryInterface
	 */
	protected function createTokenRepository( array $config )
	{
		$key = Config::get( 'app.key' );

		if ( Str::startsWith( $key, 'base64:' ) )
			$key = base64_decode( substr( $key, 7 ) );

		$connection = isset( $config['connection'] ) ? $config['connection'] : null;

		return new DatabaseTokenRepository( DatabaseManager::i()->connection( $connection ), $config['table'], $key, $config['expire'] );
	}

	/**
	 * Get the password broker configuration.
	 *
	 * @param  string $name
	 * @return array
	 */
	protected function getConfig( $name )
	{
		return Config::get( "auth.passwords.{$name}" );
	}

	/**
	 * Get the default password broker name.
	 *
	 * @return string
	 */
	public function getDefaultDriver()
	{
		return Config::get( 'auth.defaults.passwords' );
	}

	/**
	 * Set the default password broker name.
	 *
	 * @param  string $name
	 * @return void
	 */
	public function setDefaultDriver( $name )
	{
		Config::set('auth.defaults.passwords', $name);
	}

	/**
	 * Dynamically call the default driver instance.
	 *
	 * @param  string $method
	 * @param  array $parameters
	 * @return mixed
	 */
	public function __call( $method, $parameters )
	{
		return call_user_func_array( [$this->broker(), $method], $parameters );
	}
}
