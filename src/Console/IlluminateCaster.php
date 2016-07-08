<?php

namesapce Penoaks\Console;

use Exception;
use Foundation\Support\Collection;
use Foundation\Framework;
use Foundation\Database\Eloquent\Model;
use Symfony\Component\VarDumper\Caster\Caster;

class IlluminateCaster
{
	/**
	 * Illuminate application methods to include in the presenter.
	 *
	 * @var array
	 */
	private static $fwProperties = [
		'configurationIsCached',
		'environment',
		'environmentFile',
		'isLocal',
		'routesAreCached',
		'runningUnitTests',
		'version',
		'path',
		'basePath',
		'configPath',
		'databasePath',
		'langPath',
		'publicPath',
		'storagePath',
		'bootstrapPath',
	];

	/**
	 * Get an array representing the properties of an application.
	 *
	 * @param  \Penoaks\Framework  $fw
	 * @return array
	 */
	public static function castApplication(Framework $fw)
	{
		$results = [];

		foreach (self::$fwProperties as $property)
{
			try
{
				$val = $fw->$property();

				if (! is_null($val))
{
					$results[Caster::PREFIX_VIRTUAL.$property] = $val;
				}
			} catch (Exception $e)
{
				//
			}
		}

		return $results;
	}

	/**
	 * Get an array representing the properties of a collection.
	 *
	 * @param  \Penoaks\Support\Collection  $collection
	 * @return array
	 */
	public static function castCollection(Collection $collection)
	{
		return [
			Caster::PREFIX_VIRTUAL.'all' => $collection->all(),
		];
	}

	/**
	 * Get an array representing the properties of a model.
	 *
	 * @param  \Penoaks\Database\Eloquent\Model  $model
	 * @return array
	 */
	public static function castModel(Model $model)
	{
		$attributes = array_merge(
			$model->getAttributes(), $model->getRelations()
		);

		$visible = array_flip(
			$model->getVisible() ?: array_diff(array_keys($attributes), $model->getHidden())
		);

		$results = [];

		foreach (array_intersect_key($attributes, $visible) as $key => $value)
{
			$results[(isset($visible[$key]) ? Caster::PREFIX_VIRTUAL : Caster::PREFIX_PROTECTED).$key] = $value;
		}

		return $results;
	}
}
