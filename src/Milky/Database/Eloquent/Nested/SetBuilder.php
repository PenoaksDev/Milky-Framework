<?php namespace Milky\Database\Eloquent\Nested;

use Milky\Database\Eloquent\Collection;

class SetBuilder
{
	/**
	 * Node instance for reference
	 *
	 * @var NestedModel
	 */
	protected $model = null;

	/**
	 * Array which will hold temporary lft, rgt index values for each scope.
	 *
	 * @var array
	 */
	protected $bounds = [];

	/**
	 * Create a new \Baum\SetBuilder class instance.
	 *
	 * @param   NestedModel $model
	 * @return  void
	 */
	public function __construct( $model )
	{
		$this->model = $model;
	}

	/**
	 * Perform the re-calculation of the left and right indexes of the whole
	 * nested set tree structure.
	 *
	 * @param  bool $force
	 * @return bool
	 */
	public function rebuild( $force = false )
	{
		$alreadyValid = forward_static_call( [get_class( $this->model ), 'isValidNestedSet'] );

		// Do not rebuild a valid Nested Set tree structure
		if ( !$force && $alreadyValid )
			return true;

		// Rebuild lefts and rights for each root node and its children (recursively).
		// We go by setting left (and keep track of the current left bound), then
		// search for each children and recursively set the left index (while
		// incrementing that index). When going back up the recursive chain we start
		// setting the right indexes and saving the nodes...
		$self = $this;

		$this->model->getConnection()->transaction( function () use ( $self )
		{
			foreach ( $self->roots() as $root )
				$self->rebuildBounds( $root, 0 );
		} );

		return false;
	}

	/**
	 * Return all root nodes for the current database table appropiately sorted.
	 *
	 * @return Collection
	 */
	public function roots()
	{
		return $this->model->newQuery()->whereNull( $this->model->getQualifiedParentColumnName() )->orderBy( $this->model->getQualifiedLeftColumnName() )->orderBy( $this->model->getQualifiedRightColumnName() )->orderBy( $this->model->getQualifiedKeyName() )->get();
	}

	/**
	 * Recompute left and right index bounds for the specified node and its
	 * children (recursive call). Fill the depth column too.
	 */
	public function rebuildBounds( $model, $depth = 0 )
	{
		$k = $this->scopedKey( $model );

		$model->setAttribute( $model->getLeftColumnName(), $this->getNextBound( $k ) );
		$model->setAttribute( $model->getDepthColumnName(), $depth );

		foreach ( $this->children( $model ) as $child )
			$this->rebuildBounds( $child, $depth + 1 );

		$model->setAttribute( $model->getRightColumnName(), $this->getNextBound( $k ) );

		$model->save();
	}

	/**
	 * Return all children for the specified node.
	 *
	 * @param   NestedModel $model
	 * @return  Collection
	 */
	public function children( $model )
	{
		$query = $this->model->newQuery();

		$query->where( $this->model->getQualifiedParentColumnName(), '=', $model->getKey() );

		// We must also add the scoped column values to the query to compute valid
		// left and right indexes.
		foreach ( $this->scopedAttributes( $model ) as $fld => $value )
			$query->where( $this->qualify( $fld ), '=', $value );

		$query->orderBy( $this->model->getQualifiedLeftColumnName() );
		$query->orderBy( $this->model->getQualifiedRightColumnName() );
		$query->orderBy( $this->model->getQualifiedKeyName() );

		return $query->get();
	}

	/**
	 * Return an array of the scoped attributes of the supplied node.
	 *
	 * @param   NestedModel $model
	 * @return  array
	 */
	protected function scopedAttributes( $model )
	{
		$keys = $this->model->getScopedColumns();

		if ( count( $keys ) == 0 )
			return [];

		$values = array_map( function ( $column ) use ( $model )
		{
			return $model->getAttribute( $column );
		}, $keys );

		return array_combine( $keys, $values );
	}

	/**
	 * Return a string-key for the current scoped attributes. Used for index
	 * computing when a scope is defined (acsts as an scope identifier).
	 *
	 * @param   NestedModel $model
	 * @return  string
	 */
	protected function scopedKey( $model )
	{
		$attributes = $this->scopedAttributes( $model );

		$output = [];

		foreach ( $attributes as $fld => $value )
			$output[] = $this->qualify( $fld ) . '=' . ( is_null( $value ) ? 'NULL' : $value );

		// NOTE: Maybe an md5 or something would be better. Should be unique though.
		return implode( ',', $output );
	}

	/**
	 * Return next index bound value for the given key (current scope identifier)
	 *
	 * @param   string $key
	 * @return  integer
	 */
	protected function getNextBound( $key )
	{
		if ( false === array_key_exists( $key, $this->bounds ) )
			$this->bounds[$key] = 0;

		$this->bounds[$key] = $this->bounds[$key] + 1;

		return $this->bounds[$key];
	}

	/**
	 * Get the fully qualified value for the specified column.
	 *
	 * @return string
	 */
	protected function qualify( $column )
	{
		return $this->model->getTable() . '.' . $column;
	}
}
