<?php

namespace GuoJiangClub\Catering\Backend\Models;

use GuoJiangClub\Catering\Component\Point\Model\Point as BasePoint;

class Point extends BasePoint
{
	const ACTION_BALANCE_RECHARGE = 'balance_recharge_point';
	const ACTION_ORDER_PAID       = 'order_paid_award_point';
	const ACTION_ORDER_USED       = 'order_used_point';
	const ACTION_ORDER_REFUND     = 'order_refund_point';
}