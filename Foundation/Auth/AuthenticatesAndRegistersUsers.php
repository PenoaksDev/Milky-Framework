<?php

namespace Foundation\Auth;

trait AuthenticatesAndRegistersUsers
{
	use AuthenticatesUsers, RegistersUsers {
		AuthenticatesUsers::redirectPath insteadof RegistersUsers;
		AuthenticatesUsers::getGuard insteadof RegistersUsers;
	}
}
