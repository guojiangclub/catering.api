<?php

namespace GuoJiangClub\Catering\Component\Product\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Attribute extends BaseModel implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'goods_attribute');
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
		return $this->hasMany(AttributeValue::class);
	}

	public function scopeOfModelIds($query, $modelIds)
	{
		return $query->with('values')->whereIn('model_id', $modelIds)->where('is_search', 1);
	}
}
