<?php

namespace Foundation\Database\Eloquent;

interface Scope
{
	/**
	 * Apply the scope to a given Eloquent query builder.
	 *
	 * @param  \Foundation\Database\Eloquent\Builder  $builder
	 * @param  \Foundation\Database\Eloquent\Model  $model
	 * @return void
	 */
	public function apply(Builder $builder, Model $model);
}
