<?php

namespace GuoJiangClub\Catering\Component\Point\Listeners;

use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Component\User\Models\User;
use GuoJiangClub\Catering\Component\Notifications\PointRecord;

class VerifyMobile
{
	private $point;

	public function __construct(PointRepository $pointRepository)
	{
		$this->point = $pointRepository;
	}

	public function handle(User $user)
	{

		//如果启用了积分系统,这样做的目的是防止分多喜也来记录积分数据，并且是前台用户，屏蔽后台登陆可能产生的bug
		if (settings('point_enabled') AND get_class($user) == User::class) {

			if ($this->point->getRecordByAction($user->id, 'verify_mobile')) {

				$value = config('point.point_rule.verify_mobile.base');

				$this->point->create(['user_id' => $user->id, 'action' =>
					'verify_mobile', 'note'     => '验证手机号码', 'item_type' => User::class,
				                      'item_id' => $user->id
				                      , 'value' => $value]);

				event('point.change', $user->id);
				$user->notify(new PointRecord(['point' => [
					'user_id'   => $user->id,
					'action'    => 'verify_mobile',
					'note'      => '验证手机号码',
					'item_type' => User::class,
					'item_id'   => $user->id,
					'value'     => $value]]));
			}
		}
	}
}