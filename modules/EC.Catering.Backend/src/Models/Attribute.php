<?php

namespace GuoJiangClub\EC\Catering\Backend\Models;

use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Attribute extends \GuoJiangClub\Catering\Component\Product\Models\Attribute implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

	public function setValueAttribute($value)
	{
		if (is_array($value)) {
			$this->attributes['value'] = implode(',', $value);
		} else {
			$this->attributes['value'] = '';
		}
	}

	public function getSelectValueAttribute()
	{
		if ($this->type == 1) {
			return explode(',', $this->attributes['value']);
		}

		return [];
	}

	public function values()
	{
		return $this->hasMany(AttributeValue::class, 'attribute_id');
	}

}
