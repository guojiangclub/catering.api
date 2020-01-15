<?php

namespace GuoJiangClub\Catering\Component\Discount\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

interface DiscountRepository extends RepositoryInterface
{
	/**
	 * get active discount
	 *
	 * @return mixed
	 */
	public function findActive($isCoupon = 0, $channel = 'ec');

	/**
	 * get discount by code
	 *
	 * @param bool $isCoupon
	 *
	 * @return mixed
	 */
	public function getDiscountByCode($code, $isCoupon = false);

	/**
	 * 根据 discount code and type 获取促销或者优惠券
	 *
	 * @param      $code
	 * @param bool $isCoupon
	 * @param null $type
	 *
	 * @return mixed
	 */
	public function getDiscountsByCodeAndType($code, $isCoupon = false, $type = null);

	/**
	 * 根据actiontype 获取discount，因为现在需要根据积分action获取 discounts
	 *
	 * @param $actionType
	 *
	 * @return mixed
	 */
	public function getDiscountsByActionType($actionType);

	/**
	 * 获取可领取优惠券列表/促销优惠活动
	 *
	 * @param int $is_coupon
	 * @param int $channel
	 * @param int $limit
	 *
	 * @return mixed
	 */
	public function getDiscountByType($is_coupon = 1, $channel = 'ec', $limit = 10, $is_agent_share = 0);

	/**
	 * 获取优惠券信息，用于首页
	 *
	 * @param int $user_id
	 *
	 * @return mixed
	 */
	public function getCouponsList($user_id = 0);

	/**
	 * 通过coupon_code获取用户有效优惠券信息
	 *
	 * @param $coupon_code
	 * @param $user_id
	 *
	 * @return mixed
	 */
	public function getCouponByCodeAndUserID($coupon_code, $user_id);

	/**
	 * get active discount by discount ids
	 *
	 * @param        $discount_ids
	 * @param int    $isCoupon
	 * @param string $channel
	 *
	 * @return mixed
	 */
	public function getDiscountByIds($discount_ids, $isCoupon = 0, $channel = 'ec');

}
