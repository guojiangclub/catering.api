<?php

namespace GuoJiangClub\Catering\Backend\Models;

use GuoJiangClub\Catering\Backend\Models\Coupon\Discount;
use Illuminate\Database\Eloquent\Model;

class GiftDiscount extends Model
{
	public $table = 'st_gift_discount';

	public $guarded = ['id'];

	public function discount()
	{
		return $this->belongsTo(Discount::class);
	}
}