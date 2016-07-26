<?php namespace Milky\Queue;

use Milky\Encryption\Encrypter;
use Milky\Queue\Jobs\Job;

class IlluminateQueueClosure
{
	/**
	 * The encrypter instance.
	 *
	 * @var Encrypter
	 */
	protected $crypt;

	/**
	 * Create a new queued Closure job.
	 *
	 * @param  Encrypter $crypt
	 * @return void
	 */
	public function __construct( Encrypter $crypt )
	{
		$this->crypt = $crypt;
	}

	/**
	 * Fire the Closure based queue job.
	 *
	 * @param  Job $job
	 * @param  array $data
	 * @return void
	 */
	public function fire( $job, $data )
	{
		$closure = unserialize( $this->crypt->decrypt( $data['closure'] ) );

		$closure( $job );
	}
}
