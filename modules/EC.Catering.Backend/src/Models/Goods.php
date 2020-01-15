<?php

namespace GuoJiangClub\EC\Catering\Backend\Models;

use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Goods extends \GuoJiangClub\Catering\Component\Product\Models\Goods implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

	public function hasOnePoint()
	{
		return $this->hasOne('GuoJiangClub\Catering\Component\Point\Model\PointGoods', 'item_id');
	}

	public function hasManyProducts()
	{
		return $this->hasMany(Product::class, 'goods_id');
	}

	public function hasManyAttribute()
	{
		return $this->belongsToMany(Attribute::class, config('ibrand.app.database.prefix', 'ibrand_') . 'goods_attribute_relation', 'goods_id', 'attribute_id')
			->withPivot('attribute_value_id', 'model_id', 'attribute_value');
	}

	public function model()
	{
		return $this->belongsTo(Models::class, 'model_id', 'id');
	}

	public function hasManySpec()
	{
		return $this->belongsToMany(Spec::class, config('ibrand.app.database.prefix', 'ibrand_') . 'goods_spec_relation', 'goods_id', 'spec_id');
	}

	public function category()
	{
		return $this->belongsTo(Category::class, 'category_id');
	}

	public function categories()
	{
		return $this->belongsToMany(Category::class, config('ibrand.app.database.prefix', 'ibrand_') . 'goods_category', 'goods_id', 'category_id');
	}

	public function specs()
	{
		return $this->belongsToMany(Spec::class, config('ibrand.app.database.prefix', 'ibrand_') . 'goods_spec_relation', 'goods_id', 'spec_id')
			->withPivot('spec_value', 'category_id')->withTimestamps();
	}

	public function GoodsPhotos()
	{
		return $this->hasMany(GoodsPhoto::class, 'goods_id');
	}

	public function SearchSpec()
	{
		return $this->hasMany(SearchSpec::class, 'goods_id');
	}

	public function getArrayTagsAttribute()
	{
		return explode(',', $this->attributes['tags']);
	}

	public function setImglistAttribute($value)
	{
		$this->attributes['imglist'] = serialize($value);
	}

	public function getImglistAttribute($value)
	{
		$data = unserialize($value);

		return $data ? $data : [];
	}

	public function specValue()
	{
		return $this->belongsToMany(SpecsValue::class, config('ibrand.app.database.prefix', 'ibrand_') . 'goods_spec_relation', 'goods_id', 'spec_value_id')
			->withPivot('spec_id', 'alias', 'img', 'sort')->withTimestamps();
	}

	public function setExtraAttribute($value)
	{
		if ($value) {
			$this->attributes['extra'] = json_encode($value);
		}
	}

	public function getExtraAttribute($value)
	{
		if ($value) {
			return json_decode($value);
		}

		return '';
	}

	/**
	 * 获取商品价格区间
	 *
	 * @return string
	 */
	public function getGoodsSectionSellPriceAttribute()
	{
		$min = $this->sell_price;
		$max = $this->sell_price;
		if ($min_price = $this->min_price) {
			$min = $min_price;
		}

		if ($max_price = $this->max_price) {
			$max = $max_price;
		}

		if ($min == $max) {
			return '￥ ' . $min;
		}

		return '￥ ' . $min . ' - ￥ ' . $max;
	}

	public function setExtendImageAttribute($value)
	{
		if ($value) {
			$this->attributes['extend_image'] = json_encode($value);
		}
	}

	public function getExtendImageAttribute()
	{
		if ($this->attributes['extend_image']) {
			return json_decode($this->attributes['extend_image'], true);
		}
	}
}
