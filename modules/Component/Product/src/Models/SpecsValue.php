<?php

namespace GuoJiangClub\Catering\Component\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class SpecsValue extends Model implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'goods_specs_value');
	}

	public function belongToSpec()
	{
		return $this->belongsTo(Specification::class, 'spec_id');
	}

}
