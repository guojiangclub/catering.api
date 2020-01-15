<?php

namespace GuoJiangClub\Catering\Component\Product\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

interface ProductRepository extends RepositoryInterface
{
	/**
	 * get one product by sku
	 *
	 * @param $sku
	 *
	 * @return mixed
	 */
	public function findOneBySku($sku);
}