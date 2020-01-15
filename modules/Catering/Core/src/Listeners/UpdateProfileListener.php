<?php

namespace GuoJiangClub\Catering\Core\Listeners;

use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Component\User\Models\User;

class UpdateProfileListener
{
	private $point;

	public function __construct(PointRepository $pointRepository)
	{
		$this->point = $pointRepository;
	}

	public function updateUserInfo(User $user, $action)
	{
		if (get_class($user) == User::class) {
			if ($user->birthday AND settings('complete_birthday_point')) { //完善生日
				if ($this->point->getRecordByAction($user->id, 'complete_birthday')) {

					$this->point->create(['user_id' => $user->id, 'action' =>
						'complete_birthday', 'note' => '完善生日', 'item_type' => User::class,
					                      'item_id' => $user->id
					                      , 'value' => settings('complete_birthday_point')]);

					event('point.change', $user->id);
					event('st.wechat.message.point', [$user, '完善生日赠送积分', settings('complete_birthday_point')]);
				}
			}
		}
	}

	public function subscribe($events)
	{
		$events->listen(
			'complete_info',
			'GuoJiangClub\Catering\Core\Listeners\UpdateProfileListener@updateUserInfo'
		);
	}

}