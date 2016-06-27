<?php

use Foundation\Contracts\Encryption\Encrypter as EncrypterContract;

class FoundationQueueClosure
{
	/**
	 * The encrypter instance.
	 *
	 * @var \Foundation\Contracts\Encryption\Encrypter
	 */
	protected $crypt;

	/**
	 * Create a new queued Closure job.
	 *
	 * @param  \Foundation\Contracts\Encryption\Encrypter  $crypt
	 * @return void
	 */
	public function __construct(EncrypterContract $crypt)
	{
		$this->crypt = $crypt;
	}

	/**
	 * Fire the Closure based queue job.
	 *
	 * @param  \Foundation\Contracts\Queue\Job  $job
	 * @param  array  $data
	 * @return void
	 */
	public function fire($job, $data)
	{
		$closure = unserialize($this->crypt->decrypt($data['closure']));

		$closure($job);
	}
}
