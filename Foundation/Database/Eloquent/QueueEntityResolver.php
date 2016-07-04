<?php

namespace Foundation\Database\Eloquent;

use Foundation\Contracts\Queue\EntityNotFoundException;
use Foundation\Contracts\Queue\EntityResolver as EntityResolverContract;

class QueueEntityResolver implements EntityResolverContract
{
	/**
	 * Resolve the entity for the given ID.
	 *
	 * @param  string  $type
	 * @param  mixed  $id
	 * @return mixed
	 *
	 * @throws \Foundation\Contracts\Queue\EntityNotFoundException
	 */
	public function resolve($type, $id)
	{
		$instance = (new $type)->find($id);

		if ($instance)
{
			return $instance;
		}

		throw new EntityNotFoundException($type, $id);
	}
}
