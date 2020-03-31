<?php

namespace GuoJiangClub\Catering\Component\Order\Models;

use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;
use GuoJiangClub\Catering\Component\Point\Contract\PointSubjectContract;
use GuoJiangClub\Catering\Component\Payment\Contracts\PaymentsSubjectContract;
use GuoJiangClub\Catering\Component\Payment\Models\Payment;
use GuoJiangClub\Catering\Component\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model implements DiscountSubjectContract, PaymentsSubjectContract, PointSubjectContract
{
	use SoftDeletes;

	const STATUS_TEMP = 0;   //临时订单
	const STATUS_NEW  = 1;    //有效订单，待付款
	const STATUS_PAY  = 2;    //已支付订单，待发货

	const STATUS_DELIVERED = 3;    //已发货，待收货
	const STATUS_RECEIVED  = 4;    //已收货，待评价
	const STATUS_COMPLETE  = 5;    //已评价，订单完成

	const STATUS_PAY_PARTLY = 21;    //已经支付部分金额
	const STATUS_CANCEL     = 6; //已取消订单
	const STATUS_INVALID    = 8;//已作废订单
	const STATUS_REFUND     = 7;//有退款订单
	const STATUS_DELETED    = 9;//已删除订单

	const TYPE_DEFAULT   = 0;//默认类型
	const TYPE_DISCOUNT  = 1;//折扣订单
	const TYPE_IN_SOURCE = 2;//内购订单
	const TYPE_GIFT      = 3;//礼品订单
	const TYPE_SUIT      = 4;//套餐订单

	const TYPE_SHOP = 6;//门店O2O订单

	const TYPE_POINT   = 5;//积分商城订单   6是O2O订单
	const TYPE_SECKILL = 7;//秒杀订单

	/*distribution_status*/
	const DELIVERED_WAIT   = 0;  //待发货
	const DELIVERED_STATUS = 1; //已全部发货
	const DELIVERED_PARTLY = 2; //部分发货

	const TYPE_GROUPON       = 8;//拼团订单
	const TYPE_FREE_EVENT    = 9;//免费活动订单
	const TYPE_MULTI_GROUPON = 10; //小拼团订单

	protected $guarded = ['id'];

	protected $appends = ['refund_status', 'payment_text', 'balance_paid',
	                      'items_total_yuan', 'total_yuan', 'adjustments_total_yuan', 'used_balance_amount'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'order');

		$this->status   = self::STATUS_TEMP;
		$this->order_no = build_order_no();
	}

	public function save(array $options = [])
	{
		$order = parent::save($options); // TODO: Change the autogenerated stub

		$this->items()->saveMany($this->getItems());

		$this->adjustments()->saveMany($this->getAdjustments());

        $this->comments()->saveMany($this->comments);

		return $order;
	}

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function items()
	{
		return $this->hasMany(OrderItem::class);
	}

	public function adjustments()
	{
		return $this->hasMany(Adjustment::class);
	}

	public function payments()
	{
		return $this->hasMany(Payment::class);
	}


	public function coupon()
	{
		return $this->belongsToMany('GuoJiangClub\Catering\Component\Discount\Models\Coupon', 'el_order_adjustment', 'order_id', 'origin_id');
	}

	public function getCoupon()
	{
		return $this->coupon()->wherePivot('origin_type', 'coupon')->first();
	}

	/**
	 * get subject total amount
	 *
	 * @return int
	 */
	public function getSubjectTotal()
	{
		return $this->getItemsTotal();
	}

	/**
	 * get subject count item
	 *
	 * @return int
	 */
	public function getSubjectCount()
	{
		return $this->getTotalQuantity();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTotalQuantity()
	{
		$quantity = 0;

		foreach ($this->items as $item) {
			$quantity += $item->quantity;
		}

		return $quantity;
	}

	private function getItemsTotal()
	{
		return $this->items_total;
	}

	public function countItems()
	{
		return $this->items->count();
	}

	/**
	 * get subject items
	 *
	 * @return mixed
	 */
	public function getItems()
	{
		return $this->items;
	}

	public function getAdjustments()
	{
		return $this->adjustments;
	}

	public function addItem(OrderItem $item)
	{
		if ($this->hasItem($item) AND !isset($item->item_meta['dynamic_sku'])) {
			return;
		}

		$this->items_total += $item->getTotal();
		$this->items->add($item);
		$this->recalculateTotal();
		$this->recalculateCount();
	}

	public function hasItem(OrderItem $item)
	{
		return $this->items->contains(function ($value, $key) use ($item) {
			return $item->item_id == $value->item_id AND $item->type = $value->type;
		});
		// return $this->items->contains('goods_id', $item->goods_id);
	}

	public function recalculateItemsTotal()
	{
		$items_total = 0;

		foreach ($this->items as $item) {
			$items_total += $item->total;
		}

		$this->items_total = $items_total;
		$this->recalculateTotal();
	}

	public function recalculateTotal()
	{
		$this->total = $this->items_total + $this->adjustments_total;

		if ($this->total < 0) {
			$this->total = 0;
		}
	}

	protected function recalculateCount()
	{
		$this->count = $this->getTotalQuantity();
	}

	/**
	 * @param $adjustment
	 *
	 * @return mixed
	 */
	public function addAdjustment($adjustment)
	{
		if (!$this->hasAdjustment($adjustment)) {
			$this->adjustments->add($adjustment);
			$this->addToAdjustmentsTotal($adjustment);
		}
	}

	public function hasAdjustment(Adjustment $adjustment)
	{
		return $this->adjustments->contains(function ($value, $key) use ($adjustment) {
			if ($adjustment->order_item_id) {
				return $adjustment->origin_type == $value->origin_type
					AND $adjustment->origin_id == $value->origin_id
					AND $adjustment->order_item_id == $value->order_item_id;
			}

			return $adjustment->origin_type == $value->origin_type
				AND $adjustment->origin_id == $value->origin_id;
		});
	}

	protected function addToAdjustmentsTotal(Adjustment $adjustment)
	{
		$this->adjustments_total += $adjustment->amount;
		$this->recalculateTotal();
	}

	public function addPayment(Payment $payment)
	{
		if (!$this->hasPayment($payment)) {
			$this->payments->add($payment);
		}
	}

	public function hasPayment(Payment $payment)
	{
		return $this->payments->contains(function ($value, $key) use ($payment) {
			return $payment->channel = $value->channel;
		});
	}

	public function getPaidAmountAttribute()
	{
		return $this->getPaidAmount();
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

		return $amount;
	}

	public function getPaidAmount()
	{
		if ($this->payments->count() === 0) {
			return 0;
		}

		$amount = 0;
		foreach ($this->payments as $item) {
			if ($item->status == Payment::STATUS_COMPLETED) {
				$amount += $item->amount;
			}
		}

		return $amount;
	}

	public function getNeedPayAmount()
	{
		return $this->total - $this->getPaidAmount();
	}

	/***
	 * @return string
	 * @throws \Exception
	 */
	public function getSubject()
	{
		if ($this->countItems() > 0) {
			return $this->getItems()->first()->item_name . ' 等' . $this->countItems() . '件商品';
		}

		throw new \Exception('no items on order');
	}

	public function getRefundStatusAttribute()
	{
		$refunds = $this->refunds->where('status', '<>', 3);
		if ($refunds->count() > 0) {
			return $refunds->first()->type;
		}

		return 0;
	}

	/**
	 * get subject user
	 *
	 * @return mixed
	 */
	public function getSubjectUser()
	{
		return $this->user;
	}

	public function getCurrentTotal()
	{
		return $this->total;
	}

	public function getPaymentTextAttribute()
	{
		$channels = $this->payments->pluck('channel');
		$text     = [];
		foreach ($channels as $channel) {
			switch ($channel) {
				case 'alipay_wap':
					$text[] = "支付宝手机网页支付";
					break;
				case 'alipay_pc_direct':
					$text[] = "支付宝 PC 网页支付";
					break;
				case 'wx_pub':
					$text[] = '微信';
					break;
				case 'wx_lite':
					$text[] = "小程序支付";
					break;
				case 'balance':
					$text[] = '余额';
					break;

				case 'wx_pub_qr':
					$text[] = '微信扫码';
					break;

				case 'ali_scan_pay':
					$text[] = '支付宝扫码';
					break;

				case 'pop_cash_pay':
					$text[] = '刷卡';
					break;

				case 'cash_pay':
					$text[] = '现金';
					break;

				default:
					$text[] = '其他';
					break;
			}
		}

		if (count($text) > 0) {
			return implode(" ", $text);
		}

		return '其他';
	}

	public function getItemsTotalYuanAttribute()
	{
		return number_format($this->items_total / 100, 2, ".", "");
	}

	public function getTotalYuanAttribute()
	{
		return number_format($this->total / 100, 2, ".", "");
	}

	public function getAdjustmentsTotalYuanAttribute()
	{
		return number_format($this->adjustments_total / 100, 2, ".", "");
	}

	public function getBalancePaidAttribute()
	{

		$amount = 0;

		foreach ($this->payments as $item) {
			if ($item->status == Payment::STATUS_COMPLETED AND $item->channel == 'balance') {
				$amount += $item->amount;
			}
		}

		return $amount;
	}

	/**
	 * get subject is paid
	 *
	 * @return mixed
	 */
	public function isPaid()
	{
		return $this->pay_status;
	}

    public function countComments()
    {
        return $this->comments->count();
    }
}
