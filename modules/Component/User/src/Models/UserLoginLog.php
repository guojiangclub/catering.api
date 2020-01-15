<?php

namespace GuoJiangClub\Catering\Component\User\Models;

use Illuminate\Database\Eloquent\Model;

class UserLoginLog extends Model
{
	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		$this->setTable($prefix . 'user_login_log');
	}

}