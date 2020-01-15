<?php

namespace GuoJiangClub\Catering\Component\Payment\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRefundLog extends Model
{
	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'payment_refund_log');
	}
}