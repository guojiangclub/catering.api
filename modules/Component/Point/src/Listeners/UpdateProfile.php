<?php

namespace GuoJiangClub\Catering\Component\Point\Listeners;

use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Component\User\Models\User;
use GuoJiangClub\Catering\Component\Notifications\PointRecord;

class UpdateProfile
{
	private $point;

	public function __construct(PointRepository $pointRepository)
	{
		$this->point = $pointRepository;
	}

	public function handle(User $user, $action)
	{

		//如果启用了积分系统,这样做的目的是防止分多喜也来记录积分数据，并且是前台用户，屏蔽后台登陆可能产生的bug
		if (settings('point_enabled') AND get_class($user) == User::class) {

			if ($user->sex AND $user->city AND $user->qq AND $user->nick_name) { //完善资料

				if ($this->point->getRecordByAction($user->id, $action)) {

					$value = config('point.point_rule.complete_info.base');

					$this->point->create(['user_id' => $user->id, 'action' =>
						'complete_info', 'note'     => '完善个人资料', 'item_type' => User::class,
					                      'item_id' => $user->id
					                      , 'value' => $value]);

					event('point.change', $user->id);
					$user->notify(new PointRecord(['point' => [
						'user_id'   => $user->id,
						'action'    => 'complete_info',
						'note'      => '完善个人资料',
						'item_type' => User::class,
						'item_id'   => $user->id,
						'value'     => $value]]));
				}
			}

			if ($user->avatar) {
				if ($this->point->getRecordByAction($user->id, 'upload_avatar')) {    // 上传头像

					$value = config('point.point_rule.upload_avatar.base');

					$this->point->create(['user_id' => $user->id, 'action' =>
						'upload_avatar', 'note'     => '上传头像', 'item_type' => User::class,
					                      'item_id' => $user->id
					                      , 'value' => $value]);

					event('point.change', $user->id);
					$user->notify(new PointRecord(['point' => [
						'user_id'   => $user->id,
						'action'    => 'upload_avatar',
						'note'      => '上传头像',
						'item_type' => User::class,
						'item_id'   => $user->id,
						'value'     => $value]]));
				}
			}
		}
	}
}