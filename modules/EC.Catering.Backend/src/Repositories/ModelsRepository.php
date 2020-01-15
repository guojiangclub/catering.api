<?php

namespace GuoJiangClub\EC\Catering\Backend\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use GuoJiangClub\EC\Catering\Backend\Models\Models;

/**
 * Class ModelsRepositoryEloquent
 *
 * @package namespace App\Repositories;
 */
class ModelsRepository extends BaseRepository
{
	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model()
	{
		return Models::class;
	}

	/**
	 * Boot up the repository, pushing criteria
	 */
	public function boot()
	{
		$this->pushCriteria(app(RequestCriteria::class));
	}
}
