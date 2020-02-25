<?php

namespace ElementVip\Server\Repositories;

use ElementVip\Store\Backend\Model\TravelContentsPraise;
use Prettus\Repository\Eloquent\BaseRepository;

class TravelContentPraiseRepository extends BaseRepository
{
	public function model()
	{
		return TravelContentsPraise::class;
	}

	public function getItemByOpenId($open_id, $content_id)
	{
		return $this->scopeQuery(function ($query) use ($open_id, $content_id) {
			return $query->where('open_id', $open_id)->where('content_id', $content_id)->whereNotNull('open_id');
		})->first();
	}
}