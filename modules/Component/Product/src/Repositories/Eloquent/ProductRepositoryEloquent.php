<?php

namespace GuoJiangClub\Catering\Component\Product\Repositories\Eloquent;

use GuoJiangClub\Catering\Component\Product\Models\Product;
use GuoJiangClub\Catering\Component\Product\Repositories\ProductRepository;
use Prettus\Repository\Eloquent\BaseRepository;

class ProductRepositoryEloquent extends BaseRepository implements ProductRepository
{

	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model()
	{
		return Product::class;
	}

	/**
	 * get one product by sku
	 *
	 * @param $sku
	 *
	 * @return mixed
	 */
	public function findOneBySku($sku)
	{
		return $this->findByField('sku', $sku)->first();
	}
}