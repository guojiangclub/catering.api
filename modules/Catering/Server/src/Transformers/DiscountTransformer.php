<?php

namespace GuoJiangClub\Catering\Server\Transformers;

use GuoJiangClub\Catering\Backend\Models\Coupon\Coupon;

class DiscountTransformer extends BaseTransformer
{
	public function transformData($model)
	{
		$model->coupon_info = Coupon::where('discount_id', $model->id)->where('user_id', auth('api')->user()->id)->first();

		$model->chargeOff = false;
		if ($model->coupon_info->coupon_use_code) {
			$model->chargeOff = true;
		}

		return $model->toArray();
	}
}