<?php

namespace GuoJiangClub\Catering\Component\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Specification extends Model implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'goods_spec');
	}

	public function values()
	{
		return $this->hasMany(SpecsValue::class, 'spec_id', 'id');
	}

	public function setValueAttribute($value)
	{
		if (is_array($value) && isset($value[0]) && $value[0]) {
			$value                     = array_filter($value);
			$value                     = array_unique($value);
			$this->attributes['value'] = $value ? json_encode($value, JSON_UNESCAPED_UNICODE) : '';
		}
	}

	public function setExtentAttribute($value)
	{
		if (is_array($value) && isset($value[0]) && $value[0]) {
			$value = array_filter($value);
			// $value = array_unique($value);
			$this->attributes['extent'] = $value ? json_encode($value, JSON_UNESCAPED_UNICODE) : '';
		}
	}

	public function getValueStrAttribute()
	{
		return json_decode($this->attributes['value']);
	}

	public function getExtentStrAttribute()
	{
		return json_decode($this->attributes['extent']);
	}

	public function getTypeNameAttribute()
	{
		return $this->attributes['type'] == 1 ? '文字' : '图片';
	}
}
