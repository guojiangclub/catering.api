<?php

namespace GuoJiangClub\Catering\Component\Discount\Models;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
	public $timestamps = false;

	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'discount_action');
	}
}