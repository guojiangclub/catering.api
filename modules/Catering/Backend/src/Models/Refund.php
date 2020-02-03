<?php

namespace GuoJiangClub\Catering\Backend\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
	public $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.shitang-api.database.prefix', 'ca_');

		$this->setTable($prefix . 'order_refund');
	}
}