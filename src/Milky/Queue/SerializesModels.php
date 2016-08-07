<?php namespace Milky\Queue;

use ReflectionClass;
use ReflectionProperty;
use Milky\Contracts\Queue\QueueableEntity;
use Milky\Contracts\Database\ModelIdentifier;
use Milky\Database\Eloquent\Collection as EloquentCollection;

trait SerializesModels
{
	/**
	 * Prepare the instance for serialization.
	 *
	 * @return array
	 */
	public function __sleep()
	{
		$properties = (new ReflectionClass($this))->getProperties();

		foreach ($properties as $property) {
			$property->setValue($this, $this->getSerializedPropertyValue(
				$this->getPropertyValue($property)
			));
		}

		return array_map(function ($p) {
			return $p->getName();
		}, $properties);
	}

	/**
	 * Restore the model after serialization.
	 *
	 * @return void
	 */
	public function __wakeup()
	{
		foreach ((new ReflectionClass($this))->getProperties() as $property) {
			$property->setValue($this, $this->getRestoredPropertyValue(
				$this->getPropertyValue($property)
			));
		}
	}

	/**
	 * Get the property value prepared for serialization.
	 *
	 * @param  mixed  $value
	 * @return mixed
	 */
	protected function getSerializedPropertyValue($value)
	{
		if ($value instanceof QueueableEntity) {
			return new ModelIdentifier(get_class($value), $value->getQueueableId());
		}

		return $value;
	}

	/**
	 * Get the restored property value after deserialization.
	 *
	 * @param  mixed  $value
	 * @return mixed
	 */
	protected function getRestoredPropertyValue($value)
	{
		if (! $value instanceof ModelIdentifier) {
			return $value;
		}

		return is_array($value->id)
				? $this->restoreCollection($value)
				: (new $value->class)->newQuery()->useWritePdo()->findOrFail($value->id);
	}

	/**
	 * Restore a queueable collection instance.
	 *
	 * @param  \Illuminate\Contracts\Database\ModelIdentifier  $value
	 * @return Collection
	 */
	protected function restoreCollection($value)
	{
		if (! $value->class || count($value->id) === 0) {
			return new EloquentCollection;
		}

		$model = new $value->class;

		return $model->newQuery()->useWritePdo()
					->whereIn($model->getKeyName(), $value->id)->get();
	}

	/**
	 * Get the property value for the given property.
	 *
	 * @param  \ReflectionProperty  $property
	 * @return mixed
	 */
	protected function getPropertyValue(ReflectionProperty $property)
	{
		$property->setAccessible(true);

		return $property->getValue($this);
	}
}
