<?php

namespace GuoJiangClub\EC\Catering\Backend\Models;

use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Product extends \GuoJiangClub\Catering\Component\Product\Models\Product implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

	public function goods()
	{
		return $this->belongsTo(Goods::class, 'goods_id');
	}

	public function getSpecStringAttribute()
	{
		$specStr = '';
		if ($this->attributes['spec_array']) {
			$specArr = json_decode($this->attributes['spec_array'], true);
			foreach ($specArr as $key => $val) {
				$specStr = $specStr . ',' . $val['value'];
			}
		}

		return ltrim($specStr, ",");
	}

	public function photo()
	{
		return $this->hasOne(GoodsPhoto::class, 'sku', 'sku');
	}

	public function setSpecIDAttribute($value)
	{
		$this->attributes['specID'] = json_encode(explode('-', $value));
	}

	public function getSpecIDAttribute($value)
	{
		return json_decode($value);
	}

	public function scopeJudge($query, $sku, $id = 0)
	{
		return $query->where('sku', $sku)->where('id', '<>', $id)->get();
	}
}
