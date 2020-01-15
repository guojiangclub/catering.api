<?php

namespace GuoJiangClub\EC\Catering\Backend\Models;

use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class AttributeValue extends \GuoJiangClub\Catering\Component\Product\Models\AttributeValue implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

}
