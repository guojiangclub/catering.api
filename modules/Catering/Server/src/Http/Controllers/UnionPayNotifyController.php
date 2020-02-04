<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Payment\Contracts\PaymentChargeContract;
use GuoJiangClub\Catering\Server\Service\NotifyService;
use Illuminate\Http\Request;

class UnionPayNotifyController extends Controller
{
	protected $notifyService;
	protected $chargeContract;

	public function __construct(PaymentChargeContract $chargeContract, NotifyService $notifyService)
	{
		$this->chargeContract = $chargeContract;
		$this->notifyService  = $notifyService;
	}

	public function notify(Request $request)
	{
		$notify = $request->all();
		if (empty($notify) || !isset($notify['status']) || $notify['status'] != 'TRADE_SUCCESS') {
			$this->chargeContract->createPaymentLog('result_pay', Carbon::now(), $notify['merOrderId'], '', '', $notify['totalAmount'], '', '', 'FAIL', '', $notify);

			return response('FAILED', 500);
		}

		$attach = json_decode($notify['attachedData'], true);
		$result = $this->notifyService->notify($notify['merOrderId'], $notify, $attach);
		if ($result) {
			return response('SUCCESS', 200);
		}

		return response('FAILED', 500);
	}
}