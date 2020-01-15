<?php

namespace GuoJiangClub\EC\Catering\Backend\Models;

use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Brand extends \GuoJiangClub\Catering\Component\Brand\Models\Brand implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

}
