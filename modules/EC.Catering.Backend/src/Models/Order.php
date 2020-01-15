<?php

namespace GuoJiangClub\EC\Catering\Backend\Models;

use GuoJiangClub\Catering\Component\Order\Models\Adjustment;
use GuoJiangClub\Catering\Component\Payment\Models\Payment;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use GuoJiangClub\Catering\Component\User\Models\Relations\BelongToUserTrait;
use GuoJiangClub\Catering\Component\Point\Contract\PointSubjectContract;

class Order extends \GuoJiangClub\Catering\Component\Order\Models\Order implements Transformable, PointSubjectContract
{
	use SoftDeletes;
	use TransformableTrait;
	use BelongToUserTrait;

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

	protected $guarded = ['id'];
	protected $appends = ['groupon_status'];

	public function items()
	{
		return $this->hasMany(OrderItem::class, 'order_id');
	}

	public function shipping()
	{
		return $this->hasMany(Shipping::class, 'order_id');
	}

	/**
	 * 订单状态
	 *
	 * @return string
	 */
	public function getStatusTextAttribute()
	{
		$text       = '';
		$isSupplier = session('admin_check_supplier');

		if ($this->distribution_status == 2 AND !$isSupplier) {
			return '部分已发货';
		}

		if ($isSupplier AND $this->pay_status == 1) {
			$supplierID = session('admin_supplier_id');
			$items      = $this->items->filter(function ($value, $key) use ($supplierID) {
				return in_array($value->supplier_id, $supplierID);
			});

			return $items->first()->is_send ? '已发货' : '待发货';
		}

		switch ($this->status) {
			case 1:
				$text = "待付款";
				break;
			case 2:
				$text = "待发货";
				break;
			case 3:
				$text = "配送中";  //或 待收货
				break;
			case 4:
				$text = "待评价";  //已收货待评价
				break;
			case 5:
				$text = "已完成";
				break;
			case 6:
				$text = "已取消";
				break;
			case 7:
				$text = "退款中";
				break;
			case 8:
				$text = "已作废";
				break;
			case 9:
				$text = "已删除";
				break;
			default:
				$text = "待付款";
		}

		return $text;
	}

	public function getPayTypeTextAttribute()
	{
		$text = '';
		/*switch ($this->attributes['pay_type']) {*/
		if (isset($this->payment->channel)) {
			switch ($this->payment->channel) {
				case 'alipay_wap':
					$text = "支付宝手机网页支付";
					break;
				case 'alipay_pc_direct':
					$text = "支付宝 PC 网页支付";
					break;
				case 'upacp_wap':
					$text = "银联支付";
					break;
				case 'wx_pub':
					$text = "微信支付";
					break;
				case 'wx_lite':
					$text = "小程序支付";
					break;
				case 'balance':
					$text = "余额支付";
					break;
				case 'wx_pub_qr':
					$text = "微信扫码支付";
					break;
				case 'test':
					$text = "测试";
			}
		}

		return $text;
	}

	public function getDistributionTextAttribute()
	{
		switch ($this->distribution_status) {
			case 0:
				return '未发货';
				break;
			case 1:
				return '已发货';
				break;
			case 2:
				return '部分发货';
				break;
		}

		return '';
		/* return $this->distribution_status == 0 ? '未发货' : '已发货';*/
	}

	public function getPayStatusTextAttribute()
	{
		return $this->pay_status == 0 ? '未支付' : '已支付';
	}

	public function getItemsTotalAttribute($value)
	{
		return $value / 100;
	}

	public function getTotalAttribute($value)
	{
		return $value / 100;
	}

	public function getAdjustmentsTotalAttribute($value)
	{
		return $value / 100;
	}

	public function getRealAmountAttribute()
	{
		return $this->total + $this->adjustments_total;
	}

	public function getPayableFreightAttribute($value)
	{
		return $value / 100;
	}

	public function getRealFreightAttribute($value)
	{
		return $value / 100;
	}

	public function getProduceStatusTextAttribute()
	{
		$produce = objectToArray(settings()->getSetting('produce_status_setting'));

		if (array_key_exists($this->produce_status, $produce)) {
			return $produce[$this->produce_status];
		} else {
			return '生产中';
		}
	}

	public function payment()
	{
		return $this->hasOne('GuoJiangClub\Catering\Component\Payment\Models\Payment', 'order_id');
	}

	public function adjustments()
	{
		return $this->hasMany(Adjustment::class, 'order_id');
	}

	public function payments()
	{
		return $this->hasMany(Payment::class);
	}

	public function addAdjustment($adjustment)
	{
		return;
	}

	public function getAdjustments()
	{
		return $this->adjustments;
	}

	public function getItems()
	{
		return $this->items;
	}

	public function countItems()
	{
		return $this->items->count();
	}

	public function getRefundStatusAttribute()
	{
		$refund = $this->refunds;

		if (session('admin_check_supplier')) { //如果是供应商
			$supplierID = session('admin_supplier_id');
			$refund     = $refund->filter(function ($value, $key) use ($supplierID) {
				return in_array($value->orderItem->supplier_id, $supplierID);
			});
		}

		if (count($refund) > 0) {
			$filtered = $refund->filter(function ($value, $key) {
				return $value->status <> 3;
			});

			if ($filtered->count() > 0) {
				return $filtered->first()->StatusText;
			}

			return '已完成';
		}

		return '';
	}

	public function getOrderTypeAttribute()
	{
		switch ($this->type) {
			case 0:
				return '普通订单';
				break;
			case 1:
				return '折扣订单';
				break;
			case 2:
				return '内购订单';
				break;
			case 3:
				return '礼品订单';
				break;
			case 4:
				return '套餐订单';
				break;
			case 5:
				return '积分商城订单';
				break;
			case 6:
				return 'O2O门店订单';
				break;
			case 7:
				return '秒杀订单';
				break;
			case 8:
				return '拼团订单';
				break;
			case 9:
				return '积赞订单';
				break;
			case 10:
				return '拼团订单';
				break;
		}

		return '普通订单';
	}

	public function getBalancePaidAttribute()
	{
		$amount = 0;
		if ($this->payment->count() > 0) {
			foreach ($this->payments as $item) {
				if ($item->status == Payment::STATUS_COMPLETED AND $item->channel == 'balance') {
					$amount += $item->amount;
				}
			}
		}

		return $amount;
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

	/**
	 * 获取tab订单数量
	 *
	 * @param       $status
	 * @param array $supplierID
	 *
	 * @return mixed
	 */
	public static function getOrdersCountByStatus($status, $supplierID = [])
	{
		$model = new self();
		if (is_array($status)) {
			return $model->where('channel', 'ec')->whereBetween('status', $status)->whereHas('items', function ($query) use ($supplierID) {
				if ($supplierID) {
					$query->whereIn('supplier_id', $supplierID);
				}
			})->count();
		} else {
			return $model->where('channel', 'ec')->where('status', $status)->whereHas('items', function ($query) use ($supplierID, $status) {
				if ($supplierID) {
					$query->whereIn('supplier_id', $supplierID);
				}
				if ($status == 2) {
					$query->where('is_send', 0);
				}
			})->count();
		}
	}

	/**
	 * 拼团订单，未发货判断是否可以发货
	 *
	 * @return int
	 */
	public function getGrouponStatusAttribute()
	{
		$status = 0;
		if (($grouponUser = $this->grouponUser AND $grouponUser->status == 1 AND $grouponUser->grouponItem->status == 1) OR
			!$this->grouponUser
		) {
			$status = 1;
		}

		return $status;
	}
}
