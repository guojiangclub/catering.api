<?php

namespace GuoJiangClub\Catering\Component\Gift\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class GiftActivity extends Model
{
	protected $guarded = ['id'];

	protected $appends = ['status_text_new_user', 'receive_coupon_num', 'point_double_status', 'point_double_title', 'point_double_time', 'double'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'gift_activity');
	}

	public function gift()
	{
		return $this->hasMany(GiftCoupon::class, 'type_id');
	}

	public function getReceiveCouponNumAttribute()
	{
		$i = 0;
		if (count($this->gift) > 0) {
			foreach ($this->gift as $item) {
				if (count($item->receive) > 0) {
					$i++;
				}
			}

			return $i;
		}
	}

	public function getDoubleAttribute()
	{
		return $this->point_double * 100 / 100;
	}

	public function getPointDoubleStatusAttribute()
	{
		if ($this->point_double * 100 / 100 !== 1) {
			return true;
		}

		return false;
	}

	public function getPointDoubleTitleAttribute()
	{
		if ($this->point_double * 100 / 100 !== 1) {
			if ($this->activity_day != 1) {
				return '未来' . $this->activity_day . '天购物积分' . $this->point_double . '倍';
			}

			return '当天购物积分' . $this->point_double . '倍';
		}

		return '';
	}

	public function getPointDoubleTimeAttribute()
	{
		if ($this->point_double * 100 / 100 !== 1) {
			$time         = Carbon::now()->timestamp;
			$date         = date('Y-m-d', $time);
			$activity_day = $this->activity_day - 1;
			$wei_time     = date("Y-m-d", strtotime("+$activity_day day", strtotime($date)));

			return $date . ' - ' . $wei_time;
		}

		return '';
	}

	public function getStatusTextNewUserAttribute()
	{
		if (Carbon::now() > $this->ends_at && $this->status == 0) {
			return "已过期失效";
		} elseif ($this->status == 0) {
			return "已关闭";
		} elseif (Carbon::now() <= $this->ends_at && Carbon::now() >= $this->starts_at) {
			return "进行中";
		} elseif (Carbon::now() > $this->ends_at) {
			return "已过期";
		} elseif (Carbon::now() < $this->starts_at) {
			return "未开始";
		}
	}

}

