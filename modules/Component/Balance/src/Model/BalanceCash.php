<?php

namespace GuoJiangClub\Catering\Component\Balance\Model;

use GuoJiangClub\Catering\Component\User\Models\User;
use Illuminate\Database\Eloquent\Model;

class BalanceCash extends Model
{
	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'balance_cash');
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}