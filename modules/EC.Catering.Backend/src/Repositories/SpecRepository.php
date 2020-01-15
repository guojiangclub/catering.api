<?php

namespace GuoJiangClub\EC\Catering\Backend\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use GuoJiangClub\EC\Catering\Backend\Models\Spec;

/**
 * Class SpecRepositoryEloquent
 *
 * @package namespace App\Repositories;
 */
class SpecRepository extends BaseRepository
{
	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model()
	{
		return Spec::class;
	}

	/**
	 * Boot up the repository, pushing criteria
	 */
	public function boot()
	{
		$this->pushCriteria(app(RequestCriteria::class));
	}

	public function getSpecColorList()
	{
		return $this->find(2);
	}
}
