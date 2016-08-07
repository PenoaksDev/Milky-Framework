<?php namespace Milky\Account\Auths;

use Milky\Account\Types\Account;
use Milky\Account\Types\GenericAccount;
use Milky\Database\ConnectionInterface;
use Milky\Database\DatabaseManager;
use Milky\Hashing\BcryptHasher as Hasher;
use Milky\Helpers\Str;

class DatabaseAuth implements AccountAuth
{
	/**
	 * The active database connection.
	 *
	 * @var ConnectionInterface
	 */
	protected $conn;

	/**
	 * The hasher implementation.
	 *
	 * @var Hasher
	 */
	protected $hasher;

	/**
	 * The table containing the users.
	 *
	 * @var string
	 */
	protected $usrTable;

	/**
	 * The table containing the groups.
	 *
	 * @var string
	 */
	protected $grpTable;

	/**
	 * Create a new database user provider.
	 *
	 * @param string $usrTable
	 * @param string $grpTable
	 * @param ConnectionInterface $conn
	 * @param Hasher $hasher
	 *
	 * @return void
	 */
	public function __construct( $usrTable = null, $grpTable = null, ConnectionInterface $conn = null, Hasher $hasher = null )
	{
		$this->usrTable = $usrTable ?: 'user';
		$this->grpTable = $grpTable ?: 'group';
		$this->conn = $conn ?: DatabaseManager::i()->connection();
		$this->hasher = $hasher ?: Hasher::i();
	}

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  mixed $identifier
	 * @return Account|null
	 */
	public function retrieveById( $identifier )
	{
		$acct = $this->conn->table( $this->table )->find( $identifier );

		return $this->getGenericAccount( $acct );
	}

	/**
	 * Retrieve a user by their unique identifier and "remember me" token.
	 *
	 * @param  mixed $identifier
	 * @param  string $token
	 * @return Account|null
	 */
	public function retrieveByToken( $identifier, $token )
	{
		$acct = $this->conn->table( $this->table )->where( 'id', $identifier )->where( 'remember_token', $token )->first();

		return $this->getGenericAccount( $acct );
	}

	/**
	 * Update the "remember me" token for the given user in storage.
	 *
	 * @param  Account $acct
	 * @param  string $token
	 * @return void
	 */
	public function updateRememberToken( Account $acct, $token )
	{
		$this->conn->table( $this->table )->where( 'id', $acct->getId() )->update( ['remember_token' => $token] );
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array $credentials
	 * @return Account|null
	 */
	public function retrieveByCredentials( array $credentials )
	{
		// First we will add each credential element to the query as a where clause.
		// Then we can execute the query and, if we found a user, return it in a
		// generic "user" object that will be utilized by the Guard instances.
		$query = $this->conn->table( $this->table );

		foreach ( $credentials as $key => $value )
		{
			if ( !Str::contains( $key, 'password' ) )
			{
				$query->where( $key, $value );
			}
		}

		// Now we are ready to execute the query to see if we have an user matching
		// the given credentials. If not, we will just return nulls and indicate
		// that there are no matching users for these given credential arrays.
		$acct = $query->first();

		return $this->getGenericAccount( $acct );
	}

	/**
	 * Get the generic user.
	 *
	 * @param  mixed $acct
	 * @return GenericAccount|null
	 */
	protected function getGenericAccount( $acct )
	{
		if ( $acct !== null )
			return new GenericAccount( (array) $acct );

		return null;
	}

	/**
	 * Validate a user against the given credentials.
	 *
	 * @param  Account $acct
	 * @param  array $credentials
	 * @return bool
	 */
	public function validateCredentials( Account $acct, array $credentials )
	{
		$plain = $credentials['password'];

		return $this->hasher->check( $plain, $acct->getAuthPassword() );
	}
}
