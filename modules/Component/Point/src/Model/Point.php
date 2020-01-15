<?php

namespace GuoJiangClub\Catering\Component\Point\Model;

use GuoJiangClub\Catering\Component\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use GuoJiangClub\Catering\Component\Order\Models\Order;
use GuoJiangClub\Catering\Component\Order\Models\OrderItem;

class Point extends Model
{

	protected $guarded = ['id'];
	protected $appends = ['point_order_no', 'expired'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'point');

		$this->valid_time = $this->getPointValidTime();
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id')->withDefault();
	}

	public function point_order()
	{

		return $this->hasOne(Point::class, 'id')
			->where('item_type', 'GuoJiangClub\Catering\Component\Order\Models\Order')->with('order');
	}

	public function point_order_item()
	{

		return $this->hasOne(Point::class, 'id')
			->where('item_type', 'GuoJiangClub\Catering\Component\Order\Models\OrderItem')->with('order_item.order');
	}

	public function order()
	{

		return $this->belongsTo(Order::class, 'item_id');
	}

	public function order_item()
	{

		return $this->belongsTo(OrderItem::class, 'item_id');
	}

	public function getModelAttribute()
	{
		$model = $this->item_type;
		$model = new $model();

		return $model;
	}

	public function getOrder()
	{
		$order = false;
		$type  = $this->item_type;
		if ($type == 'GuoJiangClub\Catering\Component\Order\Models\Order') {
			$order = $this->model;
			$order = $order->find($this->item_id);
		} elseif ($type == 'GuoJiangClub\Catering\Component\Order\Models\OrderItem') {
			$orderItem = $this->model;
			$order     = $orderItem->find($this->item_id)->order;
		}

		return $order;
	}

	public function scopeType($query, $type)
	{
		return $query->where('type', $type);
	}

	public function scopeAction($query, $action)
	{
		return $query->where('action', $action);
	}

	public function scopeSumPoint($query)
	{
		return $query->sum('value');
	}

	public function scopeValid($query)
	{
		return $query->whereRaw('(DATEDIFF(now(),created_at) < valid_time or valid_time = 0)')->where('status', 1);
	}

	public function scopeOverValid($query)
	{
		return $query->whereRaw('(DATEDIFF(now(),created_at) > valid_time and valid_time <> 0)')->where('status', 1);
	}

	public function scopeWithinTime($query)
	{
		return $query->whereRaw('(DATEDIFF(now(),created_at) < valid_time or valid_time = 0)');
	}

	public function getPointOrderNoAttribute()
	{
		$order_no = '';
		$type     = $this->item_type;
		if ($type == 'GuoJiangClub\Catering\Component\Order\Models\Order') {
			return $this->order ? $this->order->order_no : '';
		} elseif ($type == 'GuoJiangClub\Catering\Component\Order\Models\OrderItem') {
			return $this->order_item ? $this->order_item->order->order_no : '';
		}

		return $order_no;
	}

	public function getPointValidTime()
	{
		if (app()->runningInConsole()) {
			return 0;
		}
		$valid_time = settings('point_valid_time_setting') ? settings('point_valid_time_setting') : 0;

		if ($valid_time == 0) {
			return $valid_time;
		}

		return \Carbon\Carbon::now()->addDay($valid_time)->getTimestamp();
	}

	public function getExpiredAttribute()
	{
		if ($this->valid_time == 0) {
			return 0;
		} else {
			return (time() - strtotime($this->created_at)) / 86400 > $this->valid_time ? 1 : 0;
		}
	}
}
