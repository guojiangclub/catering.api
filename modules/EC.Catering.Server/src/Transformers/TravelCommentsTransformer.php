<?php

namespace ElementVip\Server\Transformers;

class TravelCommentsTransformer extends BaseTransformer
{
	public function transformData($model)
	{
		$model->published_at = date('Y-m-d', strtotime($model->created_at));

		return $model->toArray();
	}
}