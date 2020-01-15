<?php

namespace GuoJiangClub\Catering\Component\Product\Repositories\Eloquent;

use GuoJiangClub\Catering\Component\Product\Models\Goods;
use GuoJiangClub\Catering\Component\Product\Repositories\GoodsRepository;
use Prettus\Repository\Eloquent\BaseRepository;

class GoodsRepositoryEloquent extends BaseRepository implements GoodsRepository
{

	//use CacheableRepository;

	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model()
	{
		return Goods::class;
	}

	/**
	 * get one goods on sale
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public function findOneById($id)
	{
		return $this->findWhere(['id' => $id, 'is_del' => 0])->first();
	}
}