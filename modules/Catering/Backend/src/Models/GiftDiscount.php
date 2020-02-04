<?php

namespace GuoJiangClub\Catering\Backend\Models;

use GuoJiangClub\Catering\Backend\Models\Coupon\Discount;
use Illuminate\Database\Eloquent\Model;

class GiftDiscount extends Model
{
	public $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'gift_discount');
	}

	public function discount()
	{
		return $this->belongsTo(Discount::class);
	}
}