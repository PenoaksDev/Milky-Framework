<?php namespace Milky\Account\Auths;

use Milky\Account\Models\Group;
use Milky\Account\Models\User;
use Milky\Account\Types\Account;
use Milky\Account\Types\EloquentAccount;
use Milky\Database\Eloquent\Model;
use Milky\Hashing\BcryptHasher as Hasher;
use Milky\Helpers\Str;

class EloquentAuth implements AccountAuth
{
	/**
	 * The hasher implementation.
	 *
	 * @var Hasher
	 */
	protected $hasher;

	/**
	 * The Eloquent user model.
	 *
	 * @var string
	 */
	protected $usrModel;

	/**
	 * The Eloquent group model.
	 *
	 * @var string
	 */
	protected $grpModel;

	/**
	 * Create a new database user provider.
	 *
	 * @param  string $usrModel
	 * @param  string $grpModel
	 * @param  Hasher $hasher
	 *
	 * @return void
	 */
	public function __construct( $usrModel = null, $grpModel = null, Hasher $hasher = null )
	{
		$this->usrModel = $usrModel ?: User::class;
		$this->grpModel = $grpModel ?: Group::class;
		$this->hasher = $hasher ?: Hasher::i();
	}

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  mixed $identifier
	 * @return EloquentAccount|null
	 */
	public function retrieveById( $identifier )
	{
		return new EloquentAccount( $this->createModel()->newQuery()->find( $identifier ) );
	}

	/**
	 * Retrieve a user by their unique identifier and "remember me" token.
	 *
	 * @param  mixed $identifier
	 * @param  string $token
	 * @return EloquentAccount|null
	 */
	public function retrieveByToken( $identifier, $token )
	{
		$model = $this->createModel();

		return new EloquentAccount( $model->newQuery()->where( 'id', $identifier )->where( 'remember_token', $token )->first() );
	}

	/**
	 * Update the "remember me" token for the given user in storage.
	 *
	 * @param  Account $user
	 * @param  string $token
	 */
	public function updateRememberToken( Account $user, $token )
	{
		$user->setRememberToken( $token );

		$user->save();
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array $credentials
	 * @return EloquentAccount|null
	 */
	public function retrieveByCredentials( array $credentials )
	{
		if ( empty( $credentials ) )
			return null;

		// First we will add each credential element to the query as a where clause.
		// Then we can execute the query and, if we found a user, return it in a
		// Eloquent User "model" that will be utilized by the Guard instances.
		$query = $this->createModel()->newQuery();

		foreach ( $credentials as $key => $value )
			if ( !Str::contains( $key, 'password' ) )
				$query->where( $key, $value );

		return new EloquentAccount( $query->first() );
	}

	/**
	 * Validate a user against the given credentials.
	 *
	 * @param  Account $user
	 * @param  array $credentials
	 * @return bool
	 */
	public function validateCredentials( Account $user, array $credentials )
	{
		$plain = $credentials['password'];

		return $this->hasher->check( $plain, $user->getAuthPassword() );
	}

	/**
	 * Create a new instance of the model.
	 *
	 * @return Model
	 */
	public function createModel()
	{
		$class = '\\' . ltrim( $this->model, '\\' );

		return new $class;
	}

	/**
	 * Gets the hasher implementation.
	 *
	 * @return Hasher
	 */
	public function getHasher()
	{
		return $this->hasher;
	}

	/**
	 * Sets the hasher implementation.
	 *
	 * @param  Hasher $hasher
	 * @return $this
	 */
	public function setHasher( Hasher $hasher )
	{
		$this->hasher = $hasher;

		return $this;
	}

	/**
	 * Gets the name of the Eloquent user model.
	 *
	 * @return string
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Sets the name of the Eloquent user model.
	 *
	 * @param  string $model
	 * @return $this
	 */
	public function setModel( $model )
	{
		$this->model = $model;

		return $this;
	}
}
