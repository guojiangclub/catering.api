<?php

namespace GuoJiangClub\Catering\Backend\Models;

use GuoJiangClub\Catering\Component\Payment\Models\Payment as BasePayment;

class Payment extends BasePayment
{
	const TYPE_BALANCE = 'balance';
	const TYPE_WX_LITE = 'wx_lite';
	const TYPE_POINT   = 'point';
	const TYPE_MIXED   = 'balance_point';
}