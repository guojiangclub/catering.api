<?php

namespace ElementVip\Server\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use ElementVip\Server\Models\GoodsRemind;

class GoodsRemindRepository extends BaseRepository
{
	public function model()
	{
		return GoodsRemind::class;
	}
}