<?php

namespace GuoJiangClub\EC\Catering\Backend\Models;

class Shipping extends \GuoJiangClub\Catering\Component\Shipping\Models\Shipping
{

	protected $guarded = ['id'];

	public function shippingMethod()
	{
		return $this->belongsTo(ShippingMethod::class, 'method_id');
	}

}