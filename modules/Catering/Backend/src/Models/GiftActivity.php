<?php

namespace GuoJiangClub\Catering\Backend\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class GiftActivity extends Model
{
	public $guarded = ['id'];

	const TYPE_RANDOM = 'random';
	const TYPE_ALL    = 'all';

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.shitang-api.database.prefix', 'ca_');

		$this->setTable($prefix . 'gift_activity');
	}

	public function gift()
	{
		return $this->hasMany(GiftDiscount::class);
	}

	public function getStatusTextAttribute()
	{
		$start  = $this->starts_at;
		$end    = $this->ends_at;
		$status = $this->status;

		if ($start > Carbon::now()) {
			return '活动未开始';
		}

		if ($end < Carbon::now()) {
			return '活动已结束';
		}

		if ($status == 1) {
			if ($start <= Carbon::now() AND $end > Carbon::now()) {
				return '活动进行中';
			}
		} else {
			if ($start <= Carbon::now() AND $end > Carbon::now()) {
				return '活动未启动';
			}
		}

		return '';
	}

	public function getActivityDateStatusAttribute()
	{
		$start = $this->starts_at;
		$end   = $this->ends_at;

		if ($start <= Carbon::now() AND $end > Carbon::now()) {
			return true;
		}

		return false;
	}
}