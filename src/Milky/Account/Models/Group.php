<?php namespace Milky\Account\Models;

use HolyWorlds\Support\Util;
use Milky\Account\Models\Group as BaseGroup;
use Milky\Account\Permissions\PermissionManager;
use Milky\Database\Eloquent\Model;
use Milky\Database\Eloquent\RoutableModel;
use Milky\Facades\URL;

class Group extends Model implements RoutableModel
{
	protected $fillable = ["id", "name", "description"];
	public $timestamps = false;
	public $incrementing = false;

	public function inheritance()
	{
		return $this->hasMany(GroupInheritance::class, "child");
	}

	public function getProfileUrlAttribute()
	{
		return URL::routeModel( 'group.show', $this );
	}

	public function getDisplayNameAttribute()
	{
		return $this->name;
	}

	public function getGroupsAttribute()
	{
		$result = [];
		foreach ( $this->inheritance()->getResults() as $parent )
			$result[] = $parent->group()->getResults();
		return $result;
	}

	public function hasGroups()
	{
		return $this->inheritance()->count() > 0;
	}

	public function addGroup( $parent )
	{
		$parent = ( $parent instanceof Group ) ? $parent->id : $parent;
		if ( $this->id == $parent )
			abort( 500, 'Group can not be a parent of ones self' );
		if ( $this->inheritance()->where("child", "parent")->count() == 0 )
			$this->inheritance()->create(["parent" => $parent, "type" => 0]);
	}

	public function hasChild( $child )
	{
		$child = ( $child instanceof Group ) || ( $child instanceof User ) ? $child->id : $child;
		return $this->children()->where("child", $child)->count() > 0;
	}

	public function addChild( $child )
	{
		$child = ( $child instanceof Group ) || ( $child instanceof User ) ? $child->id : $child;
		if ( $this->id == $child )
			abort( 500, 'Group can not be a child of ones self' );
		$this->children()->create(["child" => $child, "type" => 0]);
	}

	public function permissions()
	{
		return $this->hasMany(PermissionAssigned::class, "name");
	}

	public function hasChildren()
	{
		return $this->children()->count() > 0;
	}

	public function children()
	{
		// What groups/users are memebers?
		return $this->hasMany(GroupInheritance::class, "parent");
	}

	public function getChildUsersAttribute()
	{
		$result = [];
		foreach ( $this->children()->where( 'type', 1 )->getResults() as $child )
			$result[] = $child->assignedTo()->getResults();
		return $result;
	}

	public function getChildGroupsAttribute()
	{
		$result = [];
		foreach ( $this->children()->where( 'type', 0 )->getResults() as $child )
			$result[] = $child->assignedTo()->getResults;
		return $result;
	}

	public function checkPermission( $permission )
	{
		return PermissionManager::checkPermission( $permission, $this );
	}

	public function getSlugAttribute()
	{
		return Util::slugify( $this->name );
	}

	public function appendRoute( $route, &$parameters, &$appendedUrl )
	{
		$parameters['id'] = $this->id;
		$parameters['slug'] = $this->slug;
	}
}
