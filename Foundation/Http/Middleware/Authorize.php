<?php

namespace Foundation\Http\Middleware;

use Closure;
use Foundation\Contracts\Auth\Access\Gate;

class Authorize
{
	/**
	 * The gate instance.
	 *
	 * @var \Foundation\Contracts\Auth\Access\Gate
	 */
	protected $gate;

	/**
	 * Create a new middleware instance.
	 *
	 * @param  \Foundation\Contracts\Auth\Access\Gate  $gate
	 * @return void
	 */
	public function __construct(Gate $gate)
	{
		$this->gate = $gate;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Foundation\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string  $ability
	 * @param  string|null  $model
	 * @return mixed
	 *
	 * @throws \Foundation\Auth\Access\AuthorizationException
	 */
	public function handle($request, Closure $next, $ability, $model = null)
	{
		$this->gate->authorize($ability, $this->getGateArguments($request, $model));

		return $next($request);
	}

	/**
	 * Get the arguments parameter for the gate.
	 *
	 * @param  \Foundation\Http\Request  $request
	 * @param  string|null  $model
	 * @return array|string|\Foundation\Database\Eloquent\Model
	 */
	protected function getGateArguments($request, $model)
	{
		// If there's no model, we'll pass an empty array to the gate. If it
		// looks like a FQCN of a model, we'll send it to the gate as is.
		// Otherwise, we'll resolve the Eloquent model from the route.
		if (is_null($model)) {
			return [];
		}

		if (strpos($model, '\\') !== false) {
			return $model;
		}

		return $request->route($model);
	}
}
