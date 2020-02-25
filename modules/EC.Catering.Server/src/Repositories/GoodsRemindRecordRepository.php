<?php

namespace ElementVip\Server\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use ElementVip\Server\Models\GoodsRemindRecord;

class GoodsRemindRecordRepository extends BaseRepository
{
	public function model()
	{
		return GoodsRemindRecord::class;
	}
}