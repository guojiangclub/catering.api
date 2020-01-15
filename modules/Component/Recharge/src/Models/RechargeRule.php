<?php

namespace GuoJiangClub\Catering\Component\Recharge\Models;

use Illuminate\Database\Eloquent\Model;

class RechargeRule extends Model
{
	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'recharge_rule');
	}

	public function gift()
	{
		return $this->hasMany(GiftCoupon::class, 'type_id');
	}

}

