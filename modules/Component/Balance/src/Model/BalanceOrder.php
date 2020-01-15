<?php

namespace GuoJiangClub\Catering\Component\Balance\Model;

use Illuminate\Database\Eloquent\Model;
use GuoJiangClub\Catering\Component\Recharge\Models\RechargeRule;

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

}