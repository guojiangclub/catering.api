<?php

namespace GuoJiangClub\Catering\Server\Transformers;

class OrderTransformer extends BaseTransformer
{
	public function transformData($model)
	{
		return $model->toArray();
	}
}