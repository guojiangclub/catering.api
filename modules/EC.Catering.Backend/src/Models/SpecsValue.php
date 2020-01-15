<?php

namespace GuoJiangClub\EC\Catering\Backend\Models;

use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class SpecsValue extends \GuoJiangClub\Catering\Component\Product\Models\SpecsValue implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

	public function belongToSpec()
	{
		return $this->belongsTo(Spec::class, 'spec_id');
	}

	public function scopeJudge($query, $name, $spec_id, $id = 0)
	{
		return $query->where('name', $name)->where('spec_id', $spec_id)->where('id', '<>', $id)->get();
	}

}
