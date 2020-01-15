<?php

namespace GuoJiangClub\Catering\Component\User\Models;

use Illuminate\Database\Eloquent\Model;

class ElGroupUsers extends Model
{
	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		$this->setTable($prefix . 'group_users');
	}
}