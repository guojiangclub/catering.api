<?php

namespace GuoJiangClub\Catering\Component\Balance\Listeners;

use GuoJiangClub\Catering\Component\Balance\Repository\BalanceRepository;
use GuoJiangClub\Catering\Component\Order\Models\Order;
use GuoJiangClub\Catering\Component\Refund\Models\Refund;
use GuoJiangClub\Catering\Component\Refund\Models\RefundAmount;

class balanceRefundEventListener
{
	private $balanceRepository;

	public function __construct(BalanceRepository $balanceRepository)
	{
		$this->balanceRepository = $balanceRepository;
	}

	public function balanceRefund($refund)
	{
		$refundBalance = RefundAmount::where('refund_id', $refund->id)->where('type', 'balance')->first();
		if ($refundBalance) {
			$data = ['user_id'     => $refund->user_id,
			         'type'        => 'balance_refund',
			         'note'        => '订单退款返还余额：' . $refundBalance->amount / 100 . '元,售后订单号：' . $refund->refund_no,
			         'value'       => $refundBalance->amount,
			         'origin_id'   => $refund->id,
			         'origin_type' => Refund::class,
			];
			$this->balanceRepository->addRecord($data);
			$data['value'] = $data['value'] / 100;
			event('member.balance.changed', [$data]);
		}
	}

	public function subscribe($events)
	{
		$events->listen(
			'balance.refund',
			'GuoJiangClub\Catering\Component\Balance\Listeners\balanceRefundEventListener@balanceRefund'
		);
	}
}