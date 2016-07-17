<?php

use Penoaks\Contracts\Encryption\Encrypter as EncrypterContract;

class FrameworkQueueClosure
{
	/**
	 * The encrypter instance.
	 *
	 * @var \Penoaks\Contracts\Encryption\Encrypter
	 */
	protected $crypt;

	/**
	 * Create a new queued Closure job.
	 *
	 * @param  \Penoaks\Contracts\Encryption\Encrypter  $crypt
	 * @return void
	 */
	public function __construct(EncrypterContract $crypt)
	{
		$this->crypt = $crypt;
	}

	/**
	 * Fire the Closure based queue job.
	 *
	 * @param  \Penoaks\Contracts\Queue\Job  $job
	 * @param  array  $data
	 * @return void
	 */
	public function fire($job, $data)
	{
		$closure = unserialize($this->crypt->decrypt($data['closure']));

		$closure($job);
	}
}
