<?php namespace Milky\Account\Models;

use Carbon\Carbon;
use HolyWorlds\Models\Setting;
use HolyWorlds\Support\Traits\Authorizable;
use HolyWorlds\Support\Traits\UuidAsKey;
use HolyWorlds\Support\Util;
use Milky\Account\Permissions\PermissionManager;
use Milky\Account\Types\Account;
use Milky\Auth\Authenticatable;
use Milky\Auth\Passwords\CanResetPassword;
use Milky\Database\Eloquent\Relations\HasMany;
use Milky\Database\Eloquent\RoutableModel;
use Milky\Facades\URL;

class User extends Group implements Account, RoutableModel
{
	use Authenticatable, Authorizable, CanResetPassword, UuidAsKey;

	protected $hidden = ["password", "remember_token", "activation_token"];
	public $timestamps = true;
	protected $fillable = [
		'id',
		'oldid',
		'name',
		'email',
		'usebbhash',
		'password',
		'activation_token',
		'activation_updated',
		'created_at',
		'updated_at',
		'visited_at'
	];

	public function getIsNewAttribute()
	{
		return PermissionManager::checkPermission( Setting::get( 'default_group' ) );
	}

	public function getSlugAttribute()
	{
		return Util::slugify( $this->name );
	}

	public function getProfileUrlAttribute()
	{
		return URL::routeModel( 'account.profile', $this );
	}

	public function getDisplayNameAttribute()
	{
		return $this->name;
	}

	/**
	 * @return Group[]
	 */
	public function groups()
	{
		$groups = [];

		foreach ( $this->inheritance as $heir )
			if ( false === array_search( $heir->group, $groups ) )
				$groups[] = $heir->group;

		return $groups;
	}

	/**
	 * @param Group|string $group
	 * @return bool
	 */
	public function hasGroup( $group )
	{
		return $this->inheritance()->where( [
			"parent" => ( $group instanceof Group ) ? $group->id : $group,
			"type" => 1
		] )->exists();
	}

	/**
	 * @param Group|string $parent
	 */
	public function addGroup( $parent )
	{
		$this->inheritance()->create( ["parent" => ( $parent instanceof Group ) ? $parent->id : $parent, "type" => 1] );
	}

	/**
	 * @return HasMany
	 */
	public function inheritance()
	{
		return $this->hasMany( GroupInheritance::class, "child" );
	}

	/**
	 * @return HasMany
	 */
	public function permissions()
	{
		return $this->hasMany( PermissionAssigned::class, "name" );
	}

	public static function stats()
	{
		$result = self::get();

		$activated = 0;
		$registered = 0;

		foreach ( $result as $user )
			if ( $user->isActivated() )
				$activated++;
			else
				$registered++;

		$stats = [];
		$stats[] = ["label" => "Registered", "data" => $activated];
		$stats[] = ["label" => "Unactivated", "data" => $registered];
		$stats[] = ["label" => "Online", "data" => 0];

		return $stats;
	}

	public function isActivated()
	{
		return empty( $this->activation_token );
	}

	public function activate()
	{
		if ( $this->isActivated() )
			return;

		$this->activation_token = null;
		$this->activation_updated = new Carbon();
		$this->save();
	}

	public function scopeActivated( $query )
	{
		return $query->where( "activation_token", null );
	}

	public function scopeForToken( $query, $token )
	{
		return $query->where( 'activation_token', $token );
	}

	public function activationToken()
	{
		if ( $this->isActivated() )
		{
			return null;
		}

		return $this->activation_token;
	}

	public function deactivate()
	{
		if ( !$this->isActivated() )
			return null;

		$token = null;
		do
		{
			$token = str_random( 32 );
		}
		while ( static::where( "activation_token", $token )->first() instanceof User );

		$this->activation_token = $token;
		$this->activation_updated = new Carbon();
		$this->save();

		return $token;
	}

	public static function boot()
	{
		parent::boot();

		static::creating( function ( $user )
		{
			if ( !$user->id )
			{
				$user->id = strtolower( Util::rand( 2, false, true ) ) . Util::rand( 3, true, false ) . Util::rand( 1, false, true );
			}
		} );
	}

	/**
	 * @param string $permission
	 * @return bool
	 */
	public function checkPermission( $permission )
	{
		return PermissionManager::checkPermission( $permission, $this );
	}

	/**
	 * @return bool
	 */
	public function isAdmin()
	{
		return PermissionManager::checkPermission( 'sys.admin', $this );
	}

	/**
	 * Compiles a human readable display name, e.g., John Smith
	 *
	 * @return string A human readable display name
	 */
	public function getDisplayName()
	{
		return $this->name;
	}

	/**
	 * Returns the AcctId for this Account
	 *
	 * @return string Account Id
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{
		return $this->password;
	}

	/**
	 * @return string
	 */
	public function getRememberToken()
	{
		return $this->remember_token;
	}

	/**
	 * @param string $token
	 */
	public function setRememberToken( $token )
	{
		$this->remember_token = $token;
	}

	public function appendRoute( $route, &$parameters, &$appendedUrl )
	{
		$parameters['id'] = $this->id;
		$parameters['name'] = $this->slug;
	}
}
