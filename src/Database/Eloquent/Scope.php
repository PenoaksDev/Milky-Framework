<?php

namesapce Penoaks\Database\Eloquent;

interface Scope
{
	/**
	 * Apply the scope to a given Eloquent query builder.
	 *
	 * @param  \Penoaks\Database\Eloquent\Builder  $builder
	 * @param  \Penoaks\Database\Eloquent\Model  $model
	 * @return void
	 */
	public function apply(Builder $builder, Model $model);
}
