<?php

namespace Penoaks\Encryption;

use RuntimeException;
use Penoaks\Support\Str;
use Penoaks\Support\ServiceProvider;

class EncryptionServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->fw->bindings->singleton('encrypter', function ()
{
			$config = ->make('config')->get('fw');

			if (Str::startsWith($key = $config['key'], 'base64:'))
{
				$key = base64_decode(substr($key, 7));
			}

			return $this->getEncrypterForKeyAndCipher($key, $config['cipher']);
		});
	}

	/**
	 * Get the proper encrypter instance for the given key and cipher.
	 *
	 * @param  string  $key
	 * @param  string  $cipher
	 * @return mixed
	 *
	 * @throws \RuntimeException
	 */
	protected function getEncrypterForKeyAndCipher($key, $cipher)
	{
		if (Encrypter::supported($key, $cipher))
{
			return new Encrypter($key, $cipher);
		}
elseif (McryptEncrypter::supported($key, $cipher))
{
			return new McryptEncrypter($key, $cipher);
		}
else
{
			throw new RuntimeException('No supported encrypter found. The cipher and / or key length are invalid.');
		}
	}
}
