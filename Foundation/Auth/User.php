<?php

namespace Foundation\Auth;

use Foundation\Auth\Authenticatable;
use Foundation\Database\Eloquent\Model;
use Foundation\Auth\Passwords\CanResetPassword;
use Foundation\Auth\Access\Authorizable;
use Foundation\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Foundation\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Foundation\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements
	AuthenticatableContract,
	AuthorizableContract,
	CanResetPasswordContract
{
	use Authenticatable, Authorizable, CanResetPassword;
}
