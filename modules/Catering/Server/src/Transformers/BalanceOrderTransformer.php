<?php

namespace GuoJiangClub\Catering\Server\Transformers;

class BalanceOrderTransformer extends BaseTransformer
{
	public function transformData($model)
	{
		$model->amount_yuan     = number_format($model->amount / 100, 2, '.', '');
		$model->pay_amount_yuan = number_format($model->pay_amount / 100, 2, '.', '');

		return $model->toArray();
	}
}