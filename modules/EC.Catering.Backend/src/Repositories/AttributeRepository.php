<?php

namespace GuoJiangClub\EC\Catering\Backend\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use GuoJiangClub\EC\Catering\Backend\Models\Attribute;

/**
 * Class AttributeRepositoryEloquent
 *
 * @package namespace App\Repositories;
 */
class AttributeRepository extends BaseRepository
{
	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model()
	{
		return Attribute::class;
	}

	/**
	 * Boot up the repository, pushing criteria
	 */
	public function boot()
	{
		$this->pushCriteria(app(RequestCriteria::class));
	}

	public function getAttrDataByModelID($model_id)
	{
		return $this->findByField('model_id', $model_id);
	}
}
