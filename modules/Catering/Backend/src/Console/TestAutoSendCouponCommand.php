<?php

namespace GuoJiangClub\Catering\Backend\Console;

use Illuminate\Console\Command;
use GuoJiangClub\Catering\Component\User\Models\User;
use GuoJiangClub\Catering\Server\Service\DiscountService;
use DB;

class TestAutoSendCouponCommand extends Command
{
	protected $signature = 'test:auto-send-coupon';

	protected $description = 'test auto send coupon';

	public function handle()
	{
		if (!settings('enabled_auto_marketing')) {
			return;
		}

		$rules = settings('discount_coupon_rules');
		if (!empty($rules)) {
			$rules_collect = collect($rules)->sortBy('days');
			$first_rule    = $rules_collect->first();
			$orders        = DB::table('el_order')->whereIn('status', [2, 3, 4, 5])->groupBy('user_id')->orderBy('created_at', 'DESC')->get();
			if ($orders->count() > 0) {
				foreach ($orders as $order) {
					$diff = round((time() - strtotime($order->created_at)) / 86400);
					if ($diff < $first_rule['days']) {
						continue;
					}

					try {
						$filtered = $rules_collect->where('days', '<=', $diff);
						if ($filtered->count() > 0) {
							$filtered->each(function ($item) use ($order) {
								$coupon = app(DiscountService::class)->getCouponConvert($item['couponCode'], $order->user_id);
								if ($coupon) {
									$user = User::where('status', 1)->where('id', $order->user_id)->first();
									if ($user) {
										event('st.wechat.message.coupon', [$user, $coupon]);
									}
								}
							});
						}
					} catch (\Exception $exception) {
						\Log::info($exception->getMessage());

						continue;
					}
				}
			}
		}
	}
}