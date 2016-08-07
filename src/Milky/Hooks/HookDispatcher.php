<?php namespace Milky\Hooks;

use Milky\Binding\BindingBuilder;
use Milky\Helpers\Arr;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class HookDispatcher
{
	/**
	 * Hooks
	 *
	 * @var array
	 */
	private $hooks = [];

	/**
	 * Baked hook triggers
	 *
	 * @var array
	 */
	private $triggers = [];

	/**
	 * Adds Hook
	 *
	 * @param string|array $triggers
	 * @param callable $callable
	 * @param string $name
	 */
	public function addHook( $triggers, callable $callable, $key = null )
	{
		if ( !is_array( $triggers ) )
			$triggers = [$triggers];

		if ( is_null( $key ) )
		{
			$key = 0;
			while ( array_key_exists( $key, $this->hooks ) )
			{
				$key++;
			}
		}

		$this->hooks[$key] = ['triggers' => $triggers, 'callable' => $callable];
		$this->bakeTriggers();
	}

	/**
	 * Remove Hook
	 *
	 * @param $key
	 */
	public function removeHooks( $key )
	{
		if ( array_key_exists( $key, $this->hooks ) )
		{
			unset( $this->hooks[$key] );
			$this->bakeTriggers();
		}
	}

	/**
	 * Fire an event until the first non-null response is returned.
	 *
	 * @param  string $trigger
	 * @param  array $params
	 * @return mixed
	 */
	public function until( $trigger, $params = [] )
	{
		return $this->trigger( $trigger, $params, true );
	}

	/**
	 * Triggers Hooks
	 *
	 * @param string $trigger
	 */
	public function trigger( $trigger, $params = [], $halt = false )
	{
		$keys = explode( '.', $trigger );
		$current = $this->triggers;
		$results = [];

		foreach ( $keys as $key )
		{
			$current = Arr::get( $current, $key );
			if ( !is_null( $current ) ) // && array_key_exists( '__hook_', $current ) )
			{
				foreach ( preg_grep_keys( "/__hook_[0-9]?/", $current ) as $hook )
				{
					if ( array_key_exists( $hook, $this->hooks ) )
					{
						$hook = $this->hooks[$hook];
						$result = BindingBuilder::call( $hook['callable'], is_array( $params ) ? $params : ['payload' => $params] );
						if ( !is_null( $result ) )
						{
							if ( $halt )
								return $result;
							else if ( is_array( $result ) )
								$results = array_merge_recursive( $results, $result );
							else
								$results[] = $result;
						}
					}
				}
			}
		}

		return $halt ? null : $results;
	}

	private function bakeTriggers()
	{
		$this->triggers = [];
		foreach ( $this->hooks as $key => $hook )
			foreach ( $hook['triggers'] as $trigger )
			{
				$exam = Arr::get( $this->triggers, $trigger, [] );
				$cnt = 0;
				while ( array_key_exists( '__hook_' . $cnt, $exam ) )
				{
					$cnt++;
				}
				$exam['__hook_' . $cnt] = $key;
				Arr::set( $this->triggers, $trigger, $exam );
			}
	}
}
