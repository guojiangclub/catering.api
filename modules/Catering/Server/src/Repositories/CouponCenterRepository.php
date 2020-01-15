<?php

namespace GuoJiangClub\Catering\Server\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

interface CouponCenterRepository extends RepositoryInterface
{
	public function getActivityList($user);
}