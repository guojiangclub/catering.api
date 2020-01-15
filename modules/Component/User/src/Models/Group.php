<?php

namespace GuoJiangClub\Catering\Component\User\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		$this->setTable($prefix . 'user_group');
	}

	public function user()
	{
		return $this->hasMany('GuoJiangClub\Catering\Component\User\Models\User', 'group_id');
	}

	public function getRightsIdsAttribute()
	{
		return explode(',', $this->attributes['rights_ids']);
	}
}