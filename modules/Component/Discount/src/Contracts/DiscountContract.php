<?php

namespace GuoJiangClub\Catering\Component\Discount\Contracts;

interface DiscountContract
{
	/**
	 * @return mixed
	 */
	public function hasRules();

	public function isCouponBased();

	public function getActions();

	public function getRules();

	public function setCouponUsed();

}