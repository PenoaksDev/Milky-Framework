<?php

namespace Foundation\Queue\Jobs;

use Aws\Sqs\SqsClient;
use Foundation\Framework;
use Foundation\Contracts\Queue\Job as JobContract;

class SqsJob extends Job implements JobContract
{
	/**
	 * The Amazon SQS client instance.
	 *
	 * @var \Aws\Sqs\SqsClient
	 */
	protected $sqs;

	/**
	 * The Amazon SQS job instance.
	 *
	 * @var array
	 */
	protected $job;

	/**
	 * Create a new job instance.
	 *
	 * @param  \Foundation\Framework  $bindings
	 * @param  \Aws\Sqs\SqsClient  $sqs
	 * @param  string  $queue
	 * @param  array   $job
	 * @return void
	 */
	public function __construct(Bindings $bindings,
								SqsClient $sqs,
								$queue,
								array $job)
	{
		$this->sqs = $sqs;
		$this->job = $job;
		$this->queue = $queue;
		$this->bindings = $bindings;
	}

	/**
	 * Fire the job.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->resolveAndFire(json_decode($this->getRawBody(), true));
	}

	/**
	 * Get the raw body string for the job.
	 *
	 * @return string
	 */
	public function getRawBody()
	{
		return $this->job['Body'];
	}

	/**
	 * Delete the job from the queue.
	 *
	 * @return void
	 */
	public function delete()
	{
		parent::delete();

		$this->sqs->deleteMessage([

			'QueueUrl' => $this->queue, 'ReceiptHandle' => $this->job['ReceiptHandle'],

		]);
	}

	/**
	 * Release the job back into the queue.
	 *
	 * @param  int   $delay
	 * @return void
	 */
	public function release($delay = 0)
	{
		parent::release($delay);

		$this->sqs->changeMessageVisibility([
			'QueueUrl' => $this->queue,
			'ReceiptHandle' => $this->job['ReceiptHandle'],
			'VisibilityTimeout' => $delay,
		]);
	}

	/**
	 * Get the number of times the job has been attempted.
	 *
	 * @return int
	 */
	public function attempts()
	{
		return (int) $this->job['Attributes']['ApproximateReceiveCount'];
	}

	/**
	 * Get the job identifier.
	 *
	 * @return string
	 */
	public function getJobId()
	{
		return $this->job['MessageId'];
	}

	/**
	 * Get the IoC bindings instance.
	 *
	 * @return \Foundation\Framework
	 */
	public function getBindings()
	{
		return $this->bindings;
	}

	/**
	 * Get the underlying SQS client instance.
	 *
	 * @return \Aws\Sqs\SqsClient
	 */
	public function getSqs()
	{
		return $this->sqs;
	}

	/**
	 * Get the underlying raw SQS job.
	 *
	 * @return array
	 */
	public function getSqsJob()
	{
		return $this->job;
	}
}
