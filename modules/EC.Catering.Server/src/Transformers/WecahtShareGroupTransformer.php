<?php

namespace ElementVip\Server\Transformers;

class WecahtShareGroupTransformer extends BaseTransformer
{
	public function transformData($model)
	{
		return $model->toArray();
	}
}