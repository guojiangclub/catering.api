<?php

namespace GuoJiangClub\Catering\Backend\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
	public $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'order_refund');
	}
}