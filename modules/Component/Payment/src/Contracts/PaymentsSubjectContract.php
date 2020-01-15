<?php

namespace GuoJiangClub\Catering\Component\Payment\Contracts;

use GuoJiangClub\Catering\Component\Payment\Models\Payment;

interface PaymentsSubjectContract
{
	/**
	 * add payment item
	 *
	 * @param Payment $payment
	 *
	 * @return mixed
	 */
	public function addPayment(Payment $payment);

	/**
	 * get payment subject
	 *
	 * @return mixed
	 */
	public function getSubject();
}