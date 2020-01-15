<?php

namespace GuoJiangClub\EC\Catering\Backend\Models;

use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class CategoryGroup extends \GuoJiangClub\Catering\Component\Category\Models\CategoryGroup implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

	public function category()
	{
		return $this->hasMany(Category::class, 'group_id');
	}

}
