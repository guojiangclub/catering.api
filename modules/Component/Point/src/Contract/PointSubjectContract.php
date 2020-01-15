<?php

namespace GuoJiangClub\Catering\Component\Point\Contract;

interface PointSubjectContract
{

	/**
	 * get subject items
	 *
	 * @return mixed
	 */
	public function getItems();

	/**
	 * get subject count
	 *
	 * @return mixed
	 */
	public function countItems();

	/**
	 * @param $adjustment
	 *
	 * @return mixed
	 */
	public function addAdjustment($adjustment);

	/**
	 * @return mixed
	 */
	public function getAdjustments();

}