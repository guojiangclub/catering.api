<?php

namespace GuoJiangClub\Catering\Component\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipping extends Model
{
	use SoftDeletes;

	protected $guarded = ['id'];

	protected $appends = ['name'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'shipping');
	}

	public function method()
	{
		return $this->belongsTo(ShippingMethod::class);
	}

	public function getNameAttribute()
	{
		if (isset($this->method->name)) {
			return $this->method->name;
		}

		return '';
	}
}