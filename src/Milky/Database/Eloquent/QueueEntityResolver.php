<?php namespace Milky\Database\Eloquent;

use Milky\Exceptions\Queue\EntityNotFoundException;

class QueueEntityResolver
{
	/**
	 * Resolve the entity for the given ID.
	 *
	 * @param  string $type
	 * @param  mixed $id
	 * @return mixed
	 *
	 * @throws EntityNotFoundException
	 */
	public function resolve( $type, $id )
	{
		$instance = ( new $type )->find( $id );

		if ( $instance )
			return $instance;

		throw new EntityNotFoundException( $type, $id );
	}
}
