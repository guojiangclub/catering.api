<?php

namespace GuoJiangClub\Catering\Backend\Models;

use GuoJiangClub\Catering\Component\Order\Models\Adjustment;
use GuoJiangClub\Catering\Component\Order\Models\Order as BaseOrder;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;
use GuoJiangClub\Catering\Component\Payment\Contracts\PaymentsSubjectContract;
use GuoJiangClub\Catering\Component\Point\Contract\PointSubjectContract;
use GuoJiangClub\Catering\Server\Channels\WeChatChannel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends BaseOrder implements DiscountSubjectContract, PaymentsSubjectContract, PointSubjectContract
{
	use SoftDeletes;

	const TYPE_BALANCE           = 11; //余额订单
	const TYPE_BALANCE_AND_POINT = 12; //余额、积分订单
	const TYPE_ALL_POINT         = 13; //积分支付订单

	protected $appends = ['payment_text', 'balance_paid', 'items_total_yuan', 'total_yuan', 'adjustments_total_yuan', 'used_balance_amount', 'used_point_amount', 'used_adjustments_amount', 'paid_amount', 'can_refund'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'order');

		$this->status   = self::STATUS_TEMP;
		$this->order_no = build_order_no('ST');
	}

	public function recalculateItemsTotal()
	{
		$this->recalculateTotal();
	}

	public function getPaymentTextAttribute()
	{
		$payment = $this->payments()->where('channel', WeChatChannel::TYPE)->first();
		if ($payment) {
			return '微信支付';
		}

		if ($this->type == self::TYPE_BALANCE) {
			return '余额支付';
		}

		if ($this->type == self::TYPE_BALANCE_AND_POINT) {
			return '积分、余额支付';
		}

		if ($this->type == self::TYPE_ALL_POINT) {
			return '积分抵扣';
		}

		return '';
	}

	public function getStatusTextAttribute()
	{
		$text = '';

		switch ($this->status) {
			case 2:
				$text = "已支付";
				break;
			case 7:
				$text = "已退款";
				break;
		}

		return $text;
	}

	public function getCanRefundAttribute()
	{
		if ($this->status == self::STATUS_PAY && strtotime($this->created_at) >= strtotime(date('Y-m-d') . ' 00:00:00') && strtotime($this->created_at) <= strtotime(date('Y-m-d') . ' 23:59:59')) {
			return true;
		}

		return false;
	}

	public function getUsedAdjustmentsAmountAttribute()
	{
		$adjustment = $this->adjustments()->where('type', Adjustment::ORDER_DISCOUNT_ADJUSTMENT)->where('origin_type', 'coupon')->first();
		if ($adjustment) {
			return number_format(abs($adjustment->amount) / 100, 2, ".", "");
		}

		return 0;
	}

	public function getUsedPointAmountAttribute()
	{
		$adjustment = $this->adjustments()->where('type', Adjustment::ORDER_POINT_DISCOUNT_ADJUSTMENT)->where('origin_type', 'point')->first();
		if ($adjustment) {
			return number_format(abs($adjustment->amount) / 100, 2, ".", "");
		}

		return 0;
	}

	public function getUsedBalanceAmountAttribute()
	{
		if ($this->payments->count() === 0) {
			return 0;
		}

		$amount = 0;
		foreach ($this->payments as $item) {
			if ($item->status == Payment::STATUS_COMPLETED) {
				if ($item->channel == 'balance') {
					$amount += $item->amount;
				}
			}
		}

		if ($amount > 0) {
			return number_format($amount / 100, 2, ".", "");
		}

		return 0;
	}

	public function getPaidAmountAttribute()
	{
		$payment = $this->payments()->where('channel', Payment::TYPE_WX_LITE)->where('status', Payment::STATUS_COMPLETED)->first();
		if ($payment) {
			return number_format($payment->amount / 100, 2, ".", "");
		}

		return 0;
	}

	public static function getOrdersCountByStatus($status)
	{
		$model = new self();
		if (is_array($status)) {
			return $model->where('channel', 'st')->whereIn('status', $status)->count();
		} else {
			return $model->where('channel', 'st')->where('status', $status)->count();
		}
	}

	public function getOrderUserNameAttribute()
	{
		$user = $this->user;
		if ($user) {
			if ($user->name) {
				return $user->name;
			}
			if ($user->mobile) {
				return $user->mobile;
			}
			if ($user->nick_name) {
				return $user->nick_name;
			}
		}

		return '/';
	}
}