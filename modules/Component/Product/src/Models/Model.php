<?php

namespace GuoJiangClub\Catering\Component\Product\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Model extends BaseModel implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'goods_model');
	}

	public function getSpecArrayAttribute()
	{
		if ($this->spec_ids) {
			return explode(',', $this->spec_ids);
		}

		return [];
	}

	public function hasManyAttribute()
	{
		return $this->belongsToMany(Attribute::class, config('ibrand.app.database.prefix', 'ibrand_') . 'model_attribute_relation', 'model_id', 'attribute_id');
	}
}
