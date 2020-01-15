<?php

namespace GuoJiangClub\Catering\Component\Product\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

interface GoodsRepository extends RepositoryInterface
{
	/**
	 * get one goods on sale
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public function findOneById($id);
}