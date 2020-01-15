<?php

namespace GuoJiangClub\Catering\Component\Discount\Contracts;

interface DiscountItemContract
{
	/**
	 * get item key code
	 *
	 * @return int
	 */
	public function getKeyCode($type = 'sku');

	/**
	 * get item relation key codes
	 *
	 * @return mixed
	 */
	public function getChildKeyCodes();

	/**
	 * get item type
	 *
	 * @return string
	 */
	public function getItemType();

	/**
	 * get item categories
	 *
	 * @return mixed
	 */
	public function getCategories();

}