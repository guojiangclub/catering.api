<?php

namespace GuoJiangClub\Catering\Component\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class GoodsPhoto extends Model implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'goods_photo');
	}

	public function goods()
	{
		return $this->belongsTo(Goods::class, 'goods_id');
	}

	public function getCheckedStatusAttribute()
	{
		if ($this->attributes['is_default'] == 1) {
			return 'checked';
		}

		return '';
	}

	public function getUrlAttribute($value)
	{

		$replace_url = settings('store_img_replace_url') ? settings('store_img_replace_url') : url('/');

		if (settings('store_img_cdn_status') AND $url = settings('store_img_cdn_url')) {
			$value = str_replace('http://' . $replace_url, $url, $value);
			$value = str_replace('http://', 'https://', $value);
		}

		return $value;
	}

}
