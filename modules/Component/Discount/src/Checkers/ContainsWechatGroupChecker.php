<?php

namespace GuoJiangClub\Catering\Component\Discount\Checkers;

use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\RuleCheckerContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountItemContract;
use ElementVip\Store\Backend\Model\WecahtGroup;

class ContainsWechatGroupChecker implements RuleCheckerContract
{
	const TYPE = 'contains_wechat_group';

	public function isEligible(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{
		return $this->check($configuration);
	}

	public function isEligibleByItem(DiscountItemContract $item, array $configuration)
	{
		return $this->check($configuration);
	}

	public function check(array $configuration)
	{
		if (empty($configuration) || !isset($configuration['group']) || !$configuration['group']) {
			return false;
		}

		$ids       = explode(',', $configuration['group']);
		$group_ids = WecahtGroup::whereIn('id', $ids)->pluck('group_id')->toArray();
		if (empty($group_ids)) {
			return false;
		}

		if (request('wechat_group_id') && in_array(request('wechat_group_id'), $group_ids)) {
			return true;
		}

		return false;
	}
}
