<?php

namespace GuoJiangClub\Catering\Component\Discount\Services;

use GuoJiangClub\Catering\Component\Discount\Applicators\DiscountApplicator;
use GuoJiangClub\Catering\Component\Discount\Checkers\ContainsRoleRuleChecker;
use GuoJiangClub\Catering\Component\Discount\Checkers\CouponEligibilityChecker;
use GuoJiangClub\Catering\Component\Discount\Checkers\DatesEligibilityChecker;
use GuoJiangClub\Catering\Component\Discount\Checkers\DiscountEligibilityChecker;
use GuoJiangClub\Catering\Component\Discount\Checkers\UsageLimitEligibilityChecker;
use GuoJiangClub\Catering\Component\Discount\Checkers\ContainsCategoryRuleChecker;
use GuoJiangClub\Catering\Component\Discount\Checkers\ContainsProductRuleChecker;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountItemContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;
use GuoJiangClub\Catering\Component\Discount\Models\Coupon;
use GuoJiangClub\Catering\Component\Discount\Models\Discount;
use GuoJiangClub\Catering\Component\Discount\Models\Rule;
use GuoJiangClub\Catering\Component\Discount\Models\SingleDiscountCondition;
use GuoJiangClub\Catering\Component\Discount\Repositories\CouponRepository;
use GuoJiangClub\Catering\Component\Discount\Repositories\DiscountRepository;
use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Order\Models\Order;
use GuoJiangClub\Catering\Component\Product\Models\Goods;
use Exception;
use DB;
use Illuminate\Support\Collection;

class DiscountService
{
	private   $discountRepository;
	private   $discountChecker;
	private   $couponChecker;
	private   $couponRepository;
	protected $applicator;
	protected $datesEligibilityChecker;

	const SINGLE_DISCOUNT_CACHE = 'single_discount_cache';

	public function __construct(DiscountRepository $discountRepository
		, DiscountEligibilityChecker $discountEligibilityChecker
		, CouponRepository $couponRepository
		, CouponEligibilityChecker $couponEligibilityChecker
		, DiscountApplicator $discountApplicator
		, DatesEligibilityChecker $datesEligibilityChecker)
	{
		$this->discountRepository      = $discountRepository;
		$this->discountChecker         = $discountEligibilityChecker;
		$this->couponRepository        = $couponRepository;
		$this->couponChecker           = $couponEligibilityChecker;
		$this->applicator              = $discountApplicator;
		$this->datesEligibilityChecker = $datesEligibilityChecker;
	}

	public function getEligibilityDiscounts(DiscountSubjectContract $subject, $channel = 'ec')
	{
		try {

			$discounts = $this->discountRepository->findActive(0, $channel);
			if (count($discounts) == 0) {
				return false;
			}

			$filtered = $discounts->filter(function ($item) use ($subject) {
				return $this->discountChecker->isEligible($subject, $item);
			});

			if (count($filtered) == 0) {
				return false;
			}

			foreach ($filtered as $item) {
				$this->applicator->calculate($subject, $item);
			}

			return $filtered;
		} catch (Exception $e) {
			\Log::info('折扣异常:' . $e->getMessage());

			return false;
		}
	}

	public function getEligibilityCoupons(DiscountSubjectContract $subject, $userId, $channel = 'ec')
	{
		try {

			$coupons = $this->couponRepository->findActiveByUser($userId, false, $channel);
			if (count($coupons) == 0) {
				return false;
			}

			$filtered = $coupons->filter(function ($item) use ($subject) {
				return $this->couponChecker->isEligible($subject, $item);
			});

			if (count($filtered) == 0) {
				return false;
			}

			foreach ($filtered as $item) {
				$this->applicator->calculate($subject, $item);
			}

			return $filtered;
		} catch (Exception $e) {
			\Log::info('优惠券异常:' . $e->getMessage());

			return false;
		}
	}

	public function checkDiscount(DiscountSubjectContract $subject, Discount $discount)
	{
		return $this->discountChecker->isEligible($subject, $discount);
	}

	public function checkCoupon(DiscountSubjectContract $subject, Coupon $coupon)
	{
		return $this->couponChecker->isEligible($subject, $coupon);
	}

	/**
	 * 2018新优惠券领取
	 *
	 * @param      $couponCode
	 * @param      $user_id
	 * @param null $utmCampaign
	 * @param null $utmSource
	 *
	 * @throws Exception
	 */
	public function getCouponConvert($couponCode, $user_id, $utmCampaign = null, $utmSource = null)
	{


		$coupont = $this->discountRepository->getCouponByCodeAndUserID($couponCode, $user_id);

		if (!$coupont) {
			throw new Exception('该优惠券码不存在或已过期');
		}

		if ($coupont->has_get) {
			throw new Exception('您已经领取过该优惠券');
		}

		if ($coupont->has_max) {
			throw new Exception('该优惠券已领完库存不足');
		}

		//领取优惠券
		$coupon = $this->couponRepository->getCouponsByUserID($user_id, $coupont->id, $utmCampaign, $utmSource);

		if ($coupon) {
			event('user.get.coupon', [$coupon]);
		}

		return $coupon;
	}

	private function receiveCouponBySecretCode($couponCode, $userId, $type = 0, $utmCampaign = null, $utmSource = null)
	{
		$coupon = $this->couponRepository->findCouponBySecretCode($couponCode)->first();

		if (!$coupon OR $coupon->user_id) {
			return false;
		}

		$input['user_id'] = $userId;

		if ($utmCampaign) {
			$input['utm_campaign'] = $utmCampaign;
		}
		if ($utmSource) {
			$input['utm_source'] = $utmSource;
		}
		$coupon->fill($input);
		$coupon->save();

		return $coupon;
	}

	public function getCouponByCode($user_id, $code)
	{
		$discount = $this->discountRepository->getDiscountByCode($code, true);

		$checker = new UsageLimitEligibilityChecker();

		if ($discount AND $checker->isEligible($discount)) {

			$data['user_id']     = $user_id;
			$data['discount_id'] = $discount->id;
			$data['code']        = build_order_no('C');
			$coupon              = $this->couponRepository->create($data);

			$discount->receiveCoupon();

			return $coupon;
		}

		return false;
	}

	public function getCouponByGoods(DiscountItemContract $item)
	{
		$discountIds = [];

		$rules = Rule::where('type', '<>', ContainsRoleRuleChecker::TYPE)->where(function ($query) {
			$query->where('type', '=', ContainsCategoryRuleChecker::TYPE)
				->orWhere('type', '=', ContainsProductRuleChecker::TYPE);
		})->get();

		foreach ($rules as $rule) {
			$checker       = app($rule->type);
			$configuration = json_decode($rule->configuration, true);
			if ($checker->isEligibleByItem($item, $configuration)) {
				$discountIds[] = $rule->discount_id;
			}
		}
		$discounts = $this->discountRepository->scopeQuery(function ($query) use ($discountIds) {
			return $query->whereIn('id', $discountIds)->where('coupon_based', 1)->where('status', 1);
		})->all();

		return $discounts;
	}

	/**
	 * 根据商品获取所有的优惠折扣，包含促销活动和优惠券，同时过滤是否显示在前端的数据
	 *
	 * @param DiscountItemContract $item
	 */
	public function getDiscountsByGoods(DiscountItemContract $discountItemContract, $channel = 'ec')
	{
		$collect   = collect();
		$discounts = $this->discountRepository->findActive(2, $channel);
		$discounts = $discounts->filter(function ($item) use ($discountItemContract) {
			return $this->discountChecker->isEligibleItem($discountItemContract, $item) AND $item->is_open;
		});

		empty_collect_cache([$discountItemContract->id => $discounts], 'goods_discount_cache', 30);

		if ($discounts instanceof Collection) {
			return $discounts;
		}

		return collect();
	}

	public function getGoodsByRole($role)
	{
		$spu             = [];
		$sku             = [];
		$category        = [];
		$percentageGroup = [];
		$percentage      = 100;

		//1. 找到包含该角色的所有促销活动，因为用户可能设置一个角色有多个促销活动
		$discounts = Discount::where(function ($query) {
			$query->where('status', 1)
				->where('coupon_based', 0);
		})->whereHas('rules', function ($query) use ($role) {
			$query->where(['type' => 'contains_role', 'configuration' => json_encode(['name' => $role])]);
		})->get();

		//2. 过滤日期不符合要求的
		$discounts = $discounts->filter(function ($item) {
			return $this->datesEligibilityChecker->isEligible($item);
		});

		//3. 获取所有满足条件的SKU 和 SPU ID：
		foreach ($discounts as $discount) {

			$discountSpu      = [];
			$discountSku      = [];
			$discountCategory = [];

			foreach ($discount->rules as $rule) {
				if ($rule->type == 'contains_product') {
					$configuration = json_decode($rule->configuration, true);
					if (!empty($configuration['spu'])) {

						$discountSpu = array_merge($discountSpu, explode(',', $configuration['spu']));

						$diffIds = DB::table('el_goods')
							->whereIn('goods_no', ['2t7d', '2t7e'])
							->select('id')
							->get()->pluck('id')->toArray();

						$discountSku = array_diff($discountSku, $diffIds);
					}
					if (!empty($configuration['sku'])) {
						$discountSku = array_merge($discountSku, explode(',', $configuration['sku']));
					}
				} elseif ($rule->type == 'contains_category') {
					$configuration = json_decode($rule->configuration, true);

					if (count($configuration['items'])) {
						$discountCategory = array_merge($discountCategory, $configuration['items']);

						$spuIds = DB::table('el_goods_category')
							->whereIn('category_id', $discountCategory)
							->select('goods_id')
							->distinct()->get()->pluck('goods_id')->toArray();

						$discountSpu = array_merge($discountSpu, $spuIds);

						$diffIds = DB::table('el_goods')
							->whereIn('goods_no', ['2t7d', '2t7e'])
							->select('id')
							->get()->pluck('id')->toArray();

						$discountSpu = array_diff($discountSpu, $diffIds);

						if (isset($configuration['exclude_spu'])
							AND $excludeSpus = explode(',', $configuration['exclude_spu'])
							AND count($excludeSpus) > 0
						) {

							$discountSpu = array_diff($discountSpu, $excludeSpus);
						}
					}
				}
			}

			if ($action = $discount->actions()->first()) {
				$configuration                = json_decode($action->configuration, true);
				$percentage                   = $configuration['percentage'];
				$percentageGroup[$percentage] = $discountSpu;
			}

			$spu      = array_merge($spu, $discountSpu);
			$sku      = array_merge($sku, $discountSku);
			$category = array_merge($category, $discountCategory);
		}

		/*}*/

		return [
			'spu'             => $spu,
			'sku'             => $sku,
			'category'        => $category,
			'percentage'      => $percentage,
			'percentageGroup' => $percentageGroup,
			'discounts'       => $discounts,
		];
	}

	public function getDiscountsByActionType($actionType)
	{
		return $this->discountRepository->getDiscountsByActionType($actionType);
	}

	/**
	 * 计算出优惠组合，把优惠的可能情况都计算出来给到前端
	 *
	 * @param $discounts
	 * @param $coupons
	 */
	public function getOrderDiscountGroup($order, $discounts, $coupons)
	{

		$order = Order::find($order->id);

		$groups = new Collection();

		foreach ($discounts as $discount) {
			if ($discount->exclusive == 1) {
				continue;
			}
			foreach ($coupons as $coupon) {
				if ($coupon->discount->exclusive == 1) {
					continue;
				}
				$groups->push(['discount' => $discount->id, 'coupon' => $coupon->id]);
			}
		}

		$result = [];

		foreach ($groups as $group) {
			$discount = Discount::find($group['discount']);
			$coupon   = Coupon::find($group['coupon']);

			$adjustment2 = $this->calculateDiscounts($order, $coupon, $discount);
			$adjustment1 = $this->calculateDiscounts($order, $discount, $coupon);

			$max = min($adjustment1, $adjustment2);
			$min = max($adjustment1, $adjustment2);

			$group['minAdjustment'] = $min;
			$group['maxAdjustment'] = $max;

			$result[] = $group;
			//dd($group);
		}

		return $result;
	}

	public function calculateDiscounts($order, ...$discounts)
	{
		$tempOrder       = $order;
		$adjustmentTotal = 0;
		foreach ($discounts as $discount) {
			if ($discount->isCouponBased()) {
				if ($this->couponChecker->isEligible($tempOrder, $discount)) {

					$this->applicator->combinationCalculate($tempOrder, $discount);

					$adjustmentTotal = $adjustmentTotal + $discount->adjustmentTotal;
				}
			} else {
				if ($this->discountChecker->isEligible($tempOrder, $discount)) {

					$this->applicator->combinationCalculate($tempOrder, $discount);

					$adjustmentTotal = $adjustmentTotal + $discount->adjustmentTotal;
				}
			}
		}
		//dd($tempOrder);
		//\Log::info(json_encode($tempOrder));

		return $adjustmentTotal;
	}

	public function getSingleDiscountByGoods($goods)
	{

		/*$discounts = empty_collect_cache(self::SINGLE_DISCOUNT_CACHE, $goods->id);

		if (!is_null($discounts)) {
			return $discounts;
		}*/

		if ($goods instanceof Goods) {
			$skus = $goods->products->pluck('sku')->toArray();
		} else {
			$skus = [$goods->sku];
		}

		$condition = SingleDiscountCondition::whereIn('name', $skus)->whereHas('discount', function ($query) {
			return $query->where('status', 1)
				->where('starts_at', '<=', Carbon::now()->toDateTimeString())
				->where('ends_at', '>', Carbon::now()->toDateTimeString());
		})->first();

		if ($condition) {
			$discount = $condition->discount;

			//empty_collect_cache([$goods->id => $discount], self::SINGLE_DISCOUNT_CACHE, 30);
			return $discount;
		} else { //说明该商品目前没有单品折扣
			//empty_collect_cache([$goods->id => ''], self::SINGLE_DISCOUNT_CACHE, 30);
		}

		return false;
	}

	public function getProductPriceFromSingleDiscount($product, $singleDiscount)
	{
		if ($product instanceof Goods) {
			return $product->sell_price;
		}

		if (!$singleDiscount) {
			return $product->sell_price;
		}

		$condition = $singleDiscount->conditions->where('name', $product->sku)->first();

		if (!$condition) {
			return $product->sell_price;
		}

		$type = $condition->type;

		$value = $product->sell_price;
		if ($type == 'type_discount') {
			$value = number_format($product->market_price * $condition->price / 10, 2, '.', '');
		}
		if ($type == 'type_cash') {
			$value = number_format($condition->price, 2, '.', '');
		}

		return $value;
	}

	/**
	 * TNF 活动特定方法，请勿在其他客户进行使用
	 *
	 * @param     $couponCode
	 * @param     $userId
	 * @param int $type
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function getConsumerCouponConvert($couponCode, $userId, $type = 0, $utmCampaign = null, $utmSource = null)
	{

		if (!$discount = $this->discountRepository->findWhere([
			'code' => $couponCode,
		])->first()
		) {
			throw new Exception('您输入的优惠券有误');
		}
		$type = $discount->type;

		if (strtotime($discount->ends_at) < strtotime(Carbon::today()) || $discount->status == 0) {
			throw new Exception('该优惠券不能兑换');
		}

		if ($utmCampaign) { //如果是通过渠道来领取优惠券
			if ($this->couponRepository->getCouponCountByUser($discount->id, $userId, $utmCampaign, $utmSource) > 0) {
				throw new Exception('您已经领取过该优惠券');
			}
		} else {
			if ($discount->per_usage_limit AND $discount->per_usage_limit > 0
				AND $this->couponRepository->getCouponCountByUser($discount->id, $userId) >= $discount->per_usage_limit
			) {
				throw new Exception('您已经领取过该优惠券');
			}
		}

		//判断是否可以领取
		if ($discount->usage_limit - 1 >= 0) {

			$coupon = $this->couponRepository->userGetCoupon($userId, $discount->id, $type, $utmCampaign, $utmSource);

			if ($coupon) {
				event('user.get.coupon', [$coupon]);
			}

			return $coupon;
		} else {
			return false;
		}
	}

	public function CouponIsAgentShare($code)
	{
		$coupon = $this->discountRepository->findWhere(['code' => $code, 'is_agent_share' => 1, 'coupon_based' => 1])->first();
		if (isset($coupon->id)) {
			return true;
		}

		return false;
	}

}