<?php namespace Milky\Queue;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
interface QueueableCollection
{
	/**
	 * Get the type of the entities being queued.
	 *
	 * @return string|null
	 */
	public function getQueueableClass();

	/**
	 * Get the identifiers for all of the entities.
	 *
	 * @return array
	 */
	public function getQueueableIds();
}
