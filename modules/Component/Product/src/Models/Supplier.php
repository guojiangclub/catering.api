<?php

namespace GuoJiangClub\Catering\Component\Product\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'supplier');
	}
}
