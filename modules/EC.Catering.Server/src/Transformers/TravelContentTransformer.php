<?php

namespace ElementVip\Server\Transformers;

use ElementVip\Component\User\Models\UserBind;
use ElementVip\Server\Repositories\TravelContentPraiseRepository;

class TravelContentTransformer extends BaseTransformer
{
	protected $praise;

	public function __construct(TravelContentPraiseRepository $contentPraiseRepository)
	{
		$this->praise = $contentPraiseRepository;
	}

	public function transformData($model)
	{
		if ($model->tags_list) {
			$model->tags_list = explode(',', $model->tags_list);
		}

		$model->is_praised = false;
		$user              = auth('api')->user();
		if ($user) {
			if ($userBind = UserBind::where(['type' => 'miniprogram', 'user_id' => $user->id])->first() AND $this->praise->getItemByOpenId($userBind->open_id, $model->id)
			) {
				$model->is_praised = true;
			}
		} elseif (request('open_id')) {
			if ($this->praise->getItemByOpenId(request('open_id'), $model->id)) {
				$model->is_praised = true;
			}
		}

		return $model->toArray();
	}
}