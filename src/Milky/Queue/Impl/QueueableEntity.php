<?php namespace Milky\Queue\Impl;

interface QueueableEntity
{
	/**
	 * Get the queueable identity for the entity.
	 *
	 * @return mixed
	 */
	public function getQueueableId();
}
