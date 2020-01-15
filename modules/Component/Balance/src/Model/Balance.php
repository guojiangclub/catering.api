<?php

namespace GuoJiangClub\Catering\Component\Balance\Model;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'balance');
	}

	public function origin()
	{
		return $this->morphTo();
	}

	public function scopeSumBalance($query)
	{
		return $query->sum('value');
	}

	public function scopeSumByUser($query, $userId)
	{
		return $query->where('user_id', $userId)->sum('value');
	}

}
