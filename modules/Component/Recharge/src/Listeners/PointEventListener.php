<?php

namespace GuoJiangClub\Catering\Component\Recharge\Listeners;

use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Component\User\Models\User;
use GuoJiangClub\Catering\Component\Balance\Model\BalanceOrder;
use GuoJiangClub\Catering\Component\Recharge\Repositories\RechargeRuleRepository;
use GuoJiangClub\Catering\Component\User\Models\UserBind;

class PointEventListener
{
	private   $pointRepository;
	private   $rechargeRuleRepository;
	protected $rechargePoint;

	public function __construct(PointRepository $pointRepository, RechargeRuleRepository $rechargeRuleRepository

	)
	{
		$this->pointRepository        = $pointRepository;
		$this->rechargeRuleRepository = $rechargeRuleRepository;
	}

	public function handle(BalanceOrder $order)
	{
		if ($recharge = $this->rechargeRuleRepository->getRechargeByID($order->recharge_rule_id)) {
			if (settings('point_enabled') && $recharge->point && $order->pay_status) {
				$this->AutoGivePoint($order, $recharge);
			}
		}
	}

	public function AutoGivePoint($order, $recharge)
	{
		try {
			if ($recharge->open_point && $recharge->point) {
				if (!$this->pointRepository->findWhere(['action' => 'recharge_give_point', 'item_id' => $order->id, 'item_type' => BalanceOrder::class])->first()) {
					$this->pointRepository->create(['user_id' => $order->user_id, 'action' =>
						'recharge_give_point', 'note'         => '储值赠送积分', 'item_type' => BalanceOrder::class,
					                                'item_id' => $order->id
					                                , 'value' => $recharge->point]);
					if ($this->pointRepository->updateUserPoint($order->user_id)) {
						$this->WeChatTemplateMessage($order, $recharge);
					}
				}
			}
		} catch (\Exception $exception) {
			\Log::info($exception->getMessage());
		}
	}

	//会员积分变动提醒
	public function WeChatTemplateMessage($order, $recharge)
	{
		try {
			$message = settings('wechat_message_point_changed');
			if (!isset($message['status']) && !isset($message['template_id'])) {
				if (empty($message['status']) || empty($message['template_id'])) {
					return;
				}
			}
			$template_id       = $message['template_id'];
			$mobile_domain_url = settings('mobile_domain_url');
			$app_id            = settings('wechat_app_id');
			$userBind          = UserBind::where('type', 'wechat')->where('user_id', $order->user_id)->where('app_id', $app_id)->first();
			if (!$userBind) {
				$userBind = UserBind::where('type', 'wechat')->where('user_id', $order->user_id)->first();
			}
			if ($userBind) {
				$user = User::find($order->user_id);
				if (!empty($user->nick_name)) {
					$name = $user->nick_name;
				} elseif (!empty($user->name)) {
					$name = $user->name;
				} else {
					$name = $user->mobile;
				}
				if (isset($userBind->open_id)) {
					$data = [
						'template_id' => $template_id,
						'url'         => $mobile_domain_url . '/m/#!/user/point',
						'touser'      =>
							[$userBind->open_id],
						'data'        => [
							'first'    => '充值奖励' . $recharge->point . '积分已到账',
							'keyword1' => $name,
							'keyword2' => isset($user->card->number) ? $user->card->number : '',
							'keyword3' => '您与' . $order->created_at . '，充值成功，赠送' . $recharge->point . '积分',
							'remark'   => '',
						],
					];
					$http = settings('wechat_api_url');
					$url  = $http . "api/notice/sendall?appid=" . $app_id;
					app('wechat.channel')->TemplateMessage($url, $data);
				}
			}
		} catch (\Exception $exception) {
			\Log::info($exception->getMessage());
		}
	}

}