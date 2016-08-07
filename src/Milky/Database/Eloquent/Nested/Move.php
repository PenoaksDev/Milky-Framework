<?php namespace Milky\Database\Eloquent\Nested;

use Milky\Facades\Hooks;
use Milky\Framework;

class Move
{
	/**
	 * Node on which the move operation will be performed
	 *
	 * @var NestedModel
	 */
	protected $model = null;

	/**
	 * Destination node
	 *
	 * @var NestedModel | int
	 */
	protected $target = null;

	/**
	 * Move target position, one of: child, left, right, root
	 *
	 * @var string
	 */
	protected $position = null;

	/**
	 * Memoized 1st boundary.
	 *
	 * @var int
	 */
	protected $_bound1 = null;

	/**
	 * Memoized 2nd boundary.
	 *
	 * @var int
	 */
	protected $_bound2 = null;

	/**
	 * Memoized boundaries array.
	 *
	 * @var array
	 */
	protected $_boundaries = null;

	/**
	 * Create a new Move class instance.
	 *
	 * @param   NestedModel $model
	 * @param   NestedModel|int $target
	 * @param   string $position
	 * @return  void
	 */
	public function __construct( $model, $target, $position )
	{
		$this->model = $model;
		$this->target = $this->resolveNode( $target );
		$this->position = $position;
	}

	/**
	 * Easy static accessor for performing a move operation.
	 *
	 * @param   NestedModel $model
	 * @param   NestedModel|int $target
	 * @param   string $position
	 * @return NestedModel
	 */
	public static function to( $model, $target, $position )
	{
		$instance = new static( $model, $target, $position );

		return $instance->perform();
	}

	/**
	 * Perform the move operation.
	 *
	 * @return NestedModel
	 */
	public function perform()
	{
		$this->guardAgainstImpossibleMove();

		if ( $this->fireMoveEvent( 'moving' ) === false )
			return $this->model;

		if ( $this->hasChange() )
		{
			$self = $this;

			$this->model->getConnection()->transaction( function () use ( $self )
			{
				$self->updateStructure();
			} );

			$this->target->reload();

			$this->model->setDepthWithSubtree();

			$this->model->reload();
		}

		$this->fireMoveEvent( 'moved', false );

		return $this->model;
	}

	/**
	 * Runs the SQL query associated with the update of the indexes affected
	 * by the move operation.
	 *
	 * @return int
	 */
	public function updateStructure()
	{
		list( $a, $b, $c, $d ) = $this->boundaries();

		// select the rows between the leftmost & the rightmost boundaries and apply a lock
		$this->applyLockBetween( $a, $d );

		$connection = $this->model->getConnection();
		$grammar = $connection->getQueryGrammar();

		$currentId = $this->quoteIdentifier( $this->model->getKey() );
		$parentId = $this->quoteIdentifier( $this->parentId() );
		$leftColumn = $this->model->getLeftColumnName();
		$rightColumn = $this->model->getRightColumnName();
		$parentColumn = $this->model->getParentColumnName();
		$wrappedLeft = $grammar->wrap( $leftColumn );
		$wrappedRight = $grammar->wrap( $rightColumn );
		$wrappedParent = $grammar->wrap( $parentColumn );
		$wrappedId = $grammar->wrap( $this->model->getKeyName() );

		$lftSql = "CASE
      WHEN $wrappedLeft BETWEEN $a AND $b THEN $wrappedLeft + $d - $b
      WHEN $wrappedLeft BETWEEN $c AND $d THEN $wrappedLeft + $a - $c
      ELSE $wrappedLeft END";

		$rgtSql = "CASE
      WHEN $wrappedRight BETWEEN $a AND $b THEN $wrappedRight + $d - $b
      WHEN $wrappedRight BETWEEN $c AND $d THEN $wrappedRight + $a - $c
      ELSE $wrappedRight END";

		$parentSql = "CASE
      WHEN $wrappedId = $currentId THEN $parentId
      ELSE $wrappedParent END";

		$updateConditions = [
			$leftColumn => $connection->raw( $lftSql ),
			$rightColumn => $connection->raw( $rgtSql ),
			$parentColumn => $connection->raw( $parentSql )
		];

		if ( $this->model->timestamps )
			$updateConditions[$this->model->getUpdatedAtColumn()] = $this->model->freshTimestamp();

		return $this->model->newNestedSetQuery()->where( function ( $query ) use ( $leftColumn, $rightColumn, $a, $d )
		{
			$query->whereBetween( $leftColumn, [$a, $d] )->orWhereBetween( $rightColumn, [$a, $d] );
		} )->update( $updateConditions );
	}

	/**
	 * Resolves suplied node. Basically returns the node unchanged if
	 * supplied parameter is an instance of NestedModel. Otherwise it will try
	 * to find the node in the database.
	 *
	 * @param   NestedModel|int
	 * @return  NestedModel
	 */
	protected function resolveNode( $model )
	{
		if ( $model instanceof NestedModel )
			return $model->reload();

		return $this->model->newNestedSetQuery()->find( $model );
	}

	/**
	 * Check wether the current move is possible and if not, rais an exception.
	 *
	 * @return void
	 */
	protected function guardAgainstImpossibleMove()
	{
		if ( !$this->model->exists )
			throw new MoveNotPossibleException( 'A new node cannot be moved.' );

		if ( array_search( $this->position, ['child', 'left', 'right', 'root'] ) === false )
			throw new MoveNotPossibleException( "Position should be one of ['child', 'left', 'right'] but is {$this->position}." );

		if ( !$this->promotingToRoot() )
		{
			if ( is_null( $this->target ) )
			{
				if ( $this->position === 'left' || $this->position === 'right' )
					throw new MoveNotPossibleException( "Could not resolve target node. This node cannot move any further to the {$this->position}." );
				else
					throw new MoveNotPossibleException( 'Could not resolve target node.' );
			}

			if ( $this->model->equals( $this->target ) )
				throw new MoveNotPossibleException( 'A node cannot be moved to itself.' );

			if ( $this->target->insideSubtree( $this->model ) )
				throw new MoveNotPossibleException( 'A node cannot be moved to a descendant of itself (inside moved tree).' );

			if ( !$this->model->inSameScope( $this->target ) )
				throw new MoveNotPossibleException( 'A node cannot be moved to a different scope.' );
		}
	}

	/**
	 * Computes the boundary.
	 *
	 * @return int
	 */
	protected function bound1()
	{
		if ( !is_null( $this->_bound1 ) )
			return $this->_bound1;

		switch ( $this->position )
		{
			case 'child':
				$this->_bound1 = $this->target->getRight();
				break;

			case 'left':
				$this->_bound1 = $this->target->getLeft();
				break;

			case 'right':
				$this->_bound1 = $this->target->getRight() + 1;
				break;

			case 'root':
				$this->_bound1 = $this->model->newNestedSetQuery()->max( $this->model->getRightColumnName() ) + 1;
				break;
		}

		$this->_bound1 = ( ( $this->_bound1 > $this->model->getRight() ) ? $this->_bound1 - 1 : $this->_bound1 );

		return $this->_bound1;
	}

	/**
	 * Computes the other boundary.
	 * TODO: Maybe find a better name for this... ¿?
	 *
	 * @return int
	 */
	protected function bound2()
	{
		if ( !is_null( $this->_bound2 ) )
			return $this->_bound2;

		$this->_bound2 = ( ( $this->bound1() > $this->model->getRight() ) ? $this->model->getRight() + 1 : $this->model->getLeft() - 1 );

		return $this->_bound2;
	}

	/**
	 * Computes the boundaries array.
	 *
	 * @return array
	 */
	protected function boundaries()
	{
		if ( !is_null( $this->_boundaries ) )
			return $this->_boundaries;

		// we have defined the boundaries of two non-overlapping intervals,
		// so sorting puts both the intervals and their boundaries in order
		$this->_boundaries = [
			$this->model->getLeft(),
			$this->model->getRight(),
			$this->bound1(),
			$this->bound2()
		];
		sort( $this->_boundaries );

		return $this->_boundaries;
	}

	/**
	 * Computes the new parent id for the node being moved.
	 *
	 * @return int
	 */
	protected function parentId()
	{
		switch ( $this->position )
		{
			case 'root':
				return null;

			case 'child':
				return $this->target->getKey();

			default:
				return $this->target->getParentId();
		}
	}

	/**
	 * Check wether there should be changes in the downward tree structure.
	 *
	 * @return boolean
	 */
	protected function hasChange()
	{
		return !( $this->bound1() == $this->model->getRight() || $this->bound1() == $this->model->getLeft() );
	}

	/**
	 * Check if we are promoting the provided instance to a root node.
	 *
	 * @return boolean
	 */
	protected function promotingToRoot()
	{
		return ( $this->position == 'root' );
	}

	/**
	 * Fire the given move event for the model.
	 *
	 * @param  string $event
	 * @param  bool $halt
	 * @return mixed
	 */
	protected function fireMoveEvent( $event, $halt = true )
	{
		// Basically the same as \Illuminate\Database\Eloquent\Model->fireModelEvent
		// but we relay the event into the node instance.
		$event = "eloquent.{$event}: " . get_class( $this->model );

		$method = $halt ? 'until' : 'fire';

		Hooks::trigger( $event, ['model' => $this->model] );
	}

	/**
	 * Quotes an identifier for being used in a database query.
	 *
	 * @param mixed $value
	 * @return string
	 */
	protected function quoteIdentifier( $value )
	{
		if ( is_null( $value ) )
			return 'NULL';

		$connection = $this->model->getConnection();

		$pdo = $connection->getPdo();

		return $pdo->quote( $value );
	}

	/**
	 * Applies a lock to the rows between the supplied index boundaries.
	 *
	 * @param   int $lft
	 * @param   int $rgt
	 * @return  void
	 */
	protected function applyLockBetween( $lft, $rgt )
	{
		$this->model->newQuery()->where( $this->model->getLeftColumnName(), '>=', $lft )->where( $this->model->getRightColumnName(), '<=', $rgt )->select( $this->model->getKeyName() )->lockForUpdate()->get();
	}
}
