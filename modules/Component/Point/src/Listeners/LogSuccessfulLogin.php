<?php

namespace GuoJiangClub\Catering\Component\Point\Listeners;

use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Component\User\Models\User;
use Illuminate\Auth\Events\Login;
use GuoJiangClub\Catering\Notifications\PointRecord;

class LogSuccessfulLogin
{
	private $point;

	public function __construct(PointRepository $pointRepository)
	{
		$this->point = $pointRepository;
	}

	public function handle(Login $event)
	{
		$user = $event->user;

		//如果启用了积分系统,这样做的目的是防止分多喜也来记录积分数据，并且是前台用户，屏蔽后台登陆可能产生的bug
		if (settings('point_enabled') AND get_class($user) == User::class) {

			$sumPointMonth = $this->point->getMonthlySumByAction($user->id, 'daily_login');
			$sumPointToday = $this->point->getDailySumByAction($user->id, 'daily_login');

			if ($max = config('point.point_rule.daily_login.max')
				AND $max > $sumPointMonth AND $sumPointToday == 0
			) {
				$ratio = config('point.point_rule.ratio')[$user->group ? $user->group->grade : 0];

				$value = config('point.point_rule.daily_login.base') * $ratio;

				$this->point->create(['user_id' => $user->id, 'action' =>
					'daily_login', 'note'       => '登录积分', 'item_type' => User::class,
				                      'item_id' => $user->id
				                      , 'value' => $value]);

				event('point.change', $user->id);
				$user->notify(new PointRecord(['point' => [
					'user_id'   => $user->id,
					'action'    => 'daily_login',
					'note'      => '登录积分',
					'item_type' => User::class,
					'item_id'   => $user->id,
					'value'     => $value]]));
			}
		}
	}
}