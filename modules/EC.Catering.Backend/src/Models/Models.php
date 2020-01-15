<?php

namespace GuoJiangClub\EC\Catering\Backend\Models;

use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Models extends \GuoJiangClub\Catering\Component\Product\Models\Model implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

	public function setSpecIdsAttribute($value)
	{
		$this->attributes['spec_ids'] = implode(',', $value);
	}

	public function getSpecIdsAttribute($value)
	{
		return explode(',', $value);
	}

	public function attribute()
	{
		return $this->hasMany(Attribute::class, 'model_id');
	}

}
