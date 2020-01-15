<?php

namespace GuoJiangClub\EC\Catering\Backend\Models;

use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class GoodsPhoto extends \GuoJiangClub\Catering\Component\Product\Models\GoodsPhoto implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

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

}
