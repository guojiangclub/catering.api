<?php

namespace GuoJiangClub\Catering\Component\Recharge\Models;

use Illuminate\Database\Eloquent\Model;
use GuoJiangClub\Catering\Component\User\Models\User;

class BalanceOrder extends Model
{
	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'balance_order');
	}

	public function recharge()
	{
		return $this->hasOne(RechargeRule::class, 'id', 'recharge_rule_id');
	}

	public function user()
	{
		return $this->hasOne(User::class, 'id', 'user_id');
	}
}

