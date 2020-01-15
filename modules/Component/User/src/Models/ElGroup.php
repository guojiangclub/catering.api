<?php

namespace GuoJiangClub\Catering\Component\User\Models;

use Illuminate\Database\Eloquent\Model;

class ElGroup extends Model
{
	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		$this->setTable($prefix . 'group');
	}

	public function users()
	{
		return $this->belongsToMany(User::class, 'el_group_users', 'group_id', 'user_id');
	}

}