<?php

namespace GuoJiangClub\Catering\Backend\Repositories;

use GuoJiangClub\Catering\Backend\Models\CouponCenterItem;
use Prettus\Repository\Eloquent\BaseRepository;

class CouponCenterItemRepository extends BaseRepository
{
	public function model()
	{
		return CouponCenterItem::class;
	}
}