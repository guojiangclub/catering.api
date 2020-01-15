<?php

namespace GuoJiangClub\EC\Catering\Backend\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use GuoJiangClub\EC\Catering\Backend\Models\SearchSpec;

/**
 * Class SearchSpecRepositoryEloquent
 *
 * @package namespace App\Repositories;
 */
class SearchSpecRepository extends BaseRepository
{
	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model()
	{
		return SearchSpec::class;
	}

	/**
	 * Boot up the repository, pushing criteria
	 */
	public function boot()
	{
		$this->pushCriteria(app(RequestCriteria::class));
	}

	public function updateSearchColor($specColor)
	{

		$searchColor = $this->findWhere(['spec_id' => 2]);
		foreach ($searchColor as $key => $val) {
			$colorKey   = array_search($val->spec_value, json_decode($specColor->value));
			$color      = json_decode($specColor->extent2)[$colorKey];
			$val->color = $color;
			$val->save();
		}
	}
}
