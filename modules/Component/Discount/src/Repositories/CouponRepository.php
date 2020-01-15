<?php

namespace GuoJiangClub\Catering\Component\Discount\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

interface CouponRepository extends RepositoryInterface
{

	/**
	 * 获取有效优惠券列表
	 *
	 * @param     $userId
	 * @param int $paginate
	 *
	 * @return mixed
	 */
	public function findActiveByUser($userId, $paginate = 15, $channel = 'ec');

	/**
	 * 获取已过期优惠券
	 *
	 * @param     $userId
	 * @param int $paginate
	 *
	 * @return mixed
	 */
	public function findInvalidByUser($userId, $paginate = 15);

	/**
	 * 根据暗码获取优惠券
	 *
	 * @param $code
	 *
	 * @return mixed
	 */
	public function findCouponBySecretCode($code);

	/**
	 * 获取已使用的优惠券
	 *
	 * @param     $userId
	 * @param int $paginate
	 *
	 * @return mixed
	 */
	public function findUsedByUser($userId, $paginate = 15);

	public function getCouponsByUser($user_id, $coupon_id, $type, $utmCampaign = null, $utmSource = null);

	public function getCouponsByUserID($user_id, $coupon_id, $utmCampaign = null, $utmSource = null);

	public function getValidCouponCountByUser($userId);

	public function getCouponDetails($couponId, $user_id);

	/**
	 * 获取当前用户已有该优惠券的数量
	 *
	 * @param $discountId
	 * @param $userId
	 *
	 * @return mixed
	 */
	public function getCouponCountByUser($discountId, $userId, $utmCampaign = null, $utmSource = null);

	public function getUserCouponsByUtm($userId, $utmCampaign, $utmSource);

	public function userGetCoupon($user_id, $coupon_id, $type = 0, $utmCampaign = null, $utmSource = null);
}
