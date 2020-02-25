<?php

namespace ElementVip\Server\Transformers;

class SuitTransformer extends BaseTransformer
{
	public function transformData($model)
	{
		return $model->toArray();
	}
}