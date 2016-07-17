<?php

namespace Penoaks\Auth;

use Penoaks\Auth\Authenticatable;
use Penoaks\Database\Eloquent\Model;
use Penoaks\Auth\Passwords\CanResetPassword;
use Penoaks\Auth\Access\Authorizable;
use Penoaks\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Penoaks\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Penoaks\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements
	AuthenticatableContract,
	AuthorizableContract,
	CanResetPasswordContract
{
	use Authenticatable, Authorizable, CanResetPassword;
}
