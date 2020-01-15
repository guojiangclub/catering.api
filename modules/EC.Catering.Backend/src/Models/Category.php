<?php

namespace GuoJiangClub\EC\Catering\Backend\Models;

use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Category extends \GuoJiangClub\Catering\Component\Category\Models\Category implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

}
