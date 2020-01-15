<?php

namespace GuoJiangClub\Catering\Server\Transformers;

class CouponsTransformer extends BaseTransformer
{
	public function transformData($model)
	{
		if (isset($model->clerk) && $model->clerk) {
			unset($model->clerk->password);
		}

		if (settings('coupon_bg_img')) {
			$model->discount->discount_bg_img = settings('coupon_bg_img');
		}

		return $model->toArray();
	}
}