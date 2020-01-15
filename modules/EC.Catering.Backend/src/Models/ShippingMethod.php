<?php

namespace GuoJiangClub\EC\Catering\Backend\Models;

use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class ShippingMethod extends \GuoJiangClub\Catering\Component\Shipping\Models\ShippingMethod implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

	public function scopeCheckShipping($query, $code, $name, $id = 0)
	{
		return $query->where(function ($query) use ($code, $name) {
			$query->where('code', $code)->orWhere('name', $name);
		})->where('id', '<>', $id);
	}

}
