<?php

namespace GuoJiangClub\EC\Catering\Backend\Models;

use GuoJiangClub\Catering\Component\Product\Models\Specification;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Spec extends Specification implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

	public function getTypeNameAttribute()
	{
		return $this->attributes['type'] == 1 ? '文字' : '图片';
	}

	public function specValue()
	{
		return $this->hasMany(SpecsValue::class, 'spec_id');
	}
}
