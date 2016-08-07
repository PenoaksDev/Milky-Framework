<?php namespace Milky\Database\Eloquent\Nested;

class SetValidator
{
	/**
	 * Node instance for reference
	 *
	 * @var NestedModel
	 */
	protected $model = null;

	/**
	 * Create a new \Baum\SetValidator class instance.
	 *
	 * @param   NestedModel $model
	 * @return  void
	 */
	public function __construct( $model )
	{
		$this->model = $model;
	}

	/**
	 * Determine if the validation passes.
	 *
	 * @return boolean
	 */
	public function passes()
	{
		return $this->validateBounds() && $this->validateDuplicates() && $this->validateRoots();
	}

	/**
	 * Determine if validation fails.
	 *
	 * @return boolean
	 */
	public function fails()
	{
		return !$this->passes();
	}

	/**
	 * Validates bounds of the nested tree structure. It will perform checks on
	 * the `lft`, `rgt` and `parent_id` columns. Mainly that they're not null,
	 * rights greater than lefts, and that they're within the bounds of the parent.
	 *
	 * @return boolean
	 */
	protected function validateBounds()
	{
		$connection = $this->model->getConnection();
		$grammar = $connection->getQueryGrammar();

		$tableName = $this->model->getTable();
		$primaryKeyName = $this->model->getKeyName();
		$parentColumn = $this->model->getQualifiedParentColumnName();

		$lftCol = $grammar->wrap( $this->model->getLeftColumnName() );
		$rgtCol = $grammar->wrap( $this->model->getRightColumnName() );

		$qualifiedLftCol = $grammar->wrap( $this->model->getQualifiedLeftColumnName() );
		$qualifiedRgtCol = $grammar->wrap( $this->model->getQualifiedRightColumnName() );
		$qualifiedParentCol = $grammar->wrap( $this->model->getQualifiedParentColumnName() );

		$whereStm = "($qualifiedLftCol IS NULL OR
      $qualifiedRgtCol IS NULL OR
      $qualifiedLftCol >= $qualifiedRgtCol OR
      ($qualifiedParentCol IS NOT NULL AND
        ($qualifiedLftCol <= parent.$lftCol OR
          $qualifiedRgtCol >= parent.$rgtCol)))";

		$query = $this->model->newQuery()->join( $connection->raw( $grammar->wrapTable( $tableName ) . ' AS parent' ), $parentColumn, '=', $connection->raw( 'parent.' . $grammar->wrap( $primaryKeyName ) ), 'left outer' )->whereRaw( $whereStm );

		return ( $query->count() == 0 );
	}

	/**
	 * Checks that there are no duplicates for the `lft` and `rgt` columns.
	 *
	 * @return boolean
	 */
	protected function validateDuplicates()
	{
		return ( !$this->duplicatesExistForColumn( $this->model->getQualifiedLeftColumnName() ) && !$this->duplicatesExistForColumn( $this->model->getQualifiedRightColumnName() ) );
	}

	/**
	 * For each root of the whole nested set tree structure, checks that their
	 * `lft` and `rgt` bounds are properly set.
	 *
	 * @return boolean
	 */
	protected function validateRoots()
	{
		$roots = forward_static_call( [get_class( $this->model ), 'roots'] )->get();

		// If a scope is defined in the model we should check that the roots are
		// valid *for each* value in the scope columns.
		if ( $this->model->isScoped() )
			return $this->validateRootsByScope( $roots );

		return $this->isEachRootValid( $roots );
	}

	/**
	 * Checks if duplicate values for the column specified exist. Takes
	 * the Nested Set scope columns into account (if appropiate).
	 *
	 * @param   string $column
	 * @return  boolean
	 */
	protected function duplicatesExistForColumn( $column )
	{
		$connection = $this->model->getConnection();
		$grammar = $connection->getQueryGrammar();

		$columns = array_merge( $this->model->getQualifiedScopedColumns(), [$column] );

		$columnsForSelect = implode( ', ', array_map( function ( $col ) use ( $grammar )
		{
			return $grammar->wrap( $col );
		}, $columns ) );

		$wrappedColumn = $grammar->wrap( $column );

		$query = $this->model->newQuery()->select( $connection->raw( "$columnsForSelect, COUNT($wrappedColumn)" ) )->havingRaw( "COUNT($wrappedColumn) > 1" );

		foreach ( $columns as $col )
			$query->groupBy( $col );

		$result = $query->first();

		return !is_null( $result );
	}

	/**
	 * Check that each root node in the list supplied satisfies that its bounds
	 * values (lft, rgt indexes) are less than the next.
	 *
	 * @param   mixed $roots
	 * @return  boolean
	 */
	protected function isEachRootValid( $roots )
	{
		$left = $right = 0;

		foreach ( $roots as $root )
		{
			$rootLeft = $root->getLeft();
			$rootRight = $root->getRight();

			if ( !( $rootLeft > $left && $rootRight > $right ) )
				return false;

			$left = $rootLeft;
			$right = $rootRight;
		}

		return true;
	}

	/**
	 * Check that each root node in the list supplied satisfies that its bounds
	 * values (lft, rgt indexes) are less than the next *within each scope*.
	 *
	 * @param   mixed $roots
	 * @return  boolean
	 */
	protected function validateRootsByScope( $roots )
	{
		foreach ( $this->groupRootsByScope( $roots ) as $scope => $groupedRoots )
		{
			$valid = $this->isEachRootValid( $groupedRoots );

			if ( !$valid )
				return false;
		}

		return true;
	}

	/**
	 * Given a list of root nodes, it returns an array in which the keys are the
	 * array of the actual scope column values and the values are the root nodes
	 * inside that scope themselves
	 *
	 * @param   mixed $roots
	 * @return  array
	 */
	protected function groupRootsByScope( $roots )
	{
		$rootsGroupedByScope = [];

		foreach ( $roots as $root )
		{
			$key = $this->keyForScope( $root );

			if ( !isset( $rootsGroupedByScope[$key] ) )
				$rootsGroupedByScope[$key] = [];

			$rootsGroupedByScope[$key][] = $root;
		}

		return $rootsGroupedByScope;
	}

	/**
	 * Builds a single string for the given scope columns values. Useful for
	 * making array keys for grouping.
	 *
	 * @param NestedModel $model
	 * @return string
	 */
	protected function keyForScope( $model )
	{
		return implode( '-', array_map( function ( $column ) use ( $model )
		{
			$value = $model->getAttribute( $column );

			if ( is_null( $value ) )
				return 'NULL';

			return $value;
		}, $model->getScopedColumns() ) );
	}
}
