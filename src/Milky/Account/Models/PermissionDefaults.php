<?php namespace Milky\Account\Models;

use Milky\Database\Eloquent\Model;

class PermissionDefaults extends Model
{
	protected $table = "permissions_default";
	protected $fillable = ["permission", "value_default", "value_assigned", "description"];
	protected $primaryKey = 'permission';
	public $timestamps = false;
	public $incrementing = false;

	public static function find( $permission, $returnNull = false )
	{
		foreach ( self::get() as $perm )
			if ( $perm->permission == $permission )
				return $perm;

		if ( $returnNull )
			return null;

		$def = static::where( 'permission', '' )->first();

		if ( $def == null )
			$def = static::create( [
				'permission' => '',
				'value_default' => 'NO',
				'value_assigned' => 'YES',
				'description' => "Default Permission Node (DO NOT EDIT!)"
			] );

		return $def;
	}
}
