<?php namespace Milky\Http\Session;

use Milky\Encryption\Encrypter;
use Milky\Exceptions\DecryptException;
use SessionHandlerInterface;

class EncryptedStore extends Store
{
	/**
	 * The encrypter instance.
	 *
	 * @var Encrypter
	 */
	protected $encrypter;

	/**
	 * Create a new session instance.
	 *
	 * @param  string $name
	 * @param  \SessionHandlerInterface $handler
	 * @param  Encrypter $encrypter
	 * @param  string|null $id
	 */
	public function __construct( $name, SessionHandlerInterface $handler, Encrypter $encrypter, $id = null )
	{
		$this->encrypter = $encrypter;

		parent::__construct( $name, $handler, $id );
	}

	/**
	 * Prepare the raw string data from the session for unserialization.
	 *
	 * @param  string $data
	 * @return string
	 */
	protected function prepareForUnserialize( $data )
	{
		try
		{
			return $this->encrypter->decrypt( $data );
		}
		catch ( DecryptException $e )
		{
			return json_encode( [] );
		}
	}

	/**
	 * Prepare the serialized session data for storage.
	 *
	 * @param  string $data
	 * @return string
	 */
	protected function prepareForStorage( $data )
	{
		return $this->encrypter->encrypt( $data );
	}

	/**
	 * Get the encrypter instance.
	 *
	 * @return Encrypter
	 */
	public function getEncrypter()
	{
		return $this->encrypter;
	}
}
