<?php

namespace GuoJiangClub\Catering\Component\Order\Repositories\Eloquent;

use GuoJiangClub\Catering\Component\Order\Models\Adjustment;
use Prettus\Repository\Eloquent\BaseRepository;
use GuoJiangClub\Catering\Component\Order\Repositories\AdjustmentRepository;

class AdjustmentRepositoryEloquent extends BaseRepository implements AdjustmentRepository
{

	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model()
	{
		return Adjustment::class;
	}
}