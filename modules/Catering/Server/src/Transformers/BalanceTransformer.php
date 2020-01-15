<?php

namespace GuoJiangClub\Catering\Server\Transformers;

class BalanceTransformer extends BaseTransformer
{
	public static $excludeable = [
		'deleted_at',
	];

	public function transformData($model)
	{
		$balance = array_except($model->toArray(), self::$excludeable);

		return $balance;
	}
}