<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use GuoJiangClub\Catering\Component\Point\Model\Point;
use GuoJiangClub\Catering\Component\User\Repository\UserRepository;
use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Backend\Models\Clerk;

class PointController extends Controller
{
	protected $pointRepository;
	protected $userRepository;

	public function __construct(UserRepository $userRepository, PointRepository $pointRepository)
	{
		$this->userRepository  = $userRepository;
		$this->pointRepository = $pointRepository;
	}

	public function opPointBase()
	{
		$user = $this->userRepository->findWhere(['id' => request('user_id'), 'status' => 1])->first();
		if (!$user) {
			return $this->failed('用户不存在');
		}

		$point  = $this->pointRepository->getSumPointValid($user->id);
		$option = config('ibrand.shitang-api.clerk_rule.point');
		$type   = [];
		$i      = 0;
		foreach ($option as $key => $item) {
			$type[$i]['name']  = $key;
			$type[$i]['value'] = $item;
			$i++;
		}

		return $this->success(['user' => $user, 'point' => $point, 'option' => $type]);
	}

	public function handlePoint()
	{
		$clerk = auth('shitang')->user();
		$user  = $this->userRepository->findWhere(['id' => request('user_id'), 'status' => 1])->first();
		if (!$user) {
			return $this->failed('用户不存在');
		}

		$type  = request('type');
		$value = request('value');
		if (!$type || !$value) {
			return $this->failed('请完善操作信息');
		}

		if (!preg_match('/[\d]+/', $value) || $value <= 0) {
			return $this->failed('积分只能是数字且大于0');
		}

		$user_name = $user->nick_name;
		if (!$user_name) {
			$user_name = $user->mobile;
		}

		$note = config('ibrand.shitang-api.clerk_rule.point_note')[$type] . '，店员信息：' . $clerk->id . '-' . $clerk->name . '，会员名称：' . $user_name . '，积分：' . $value;
		if (request('note')) {
			$note = $note . '，' . request('note');
		}

		$point = Point::create([
			'user_id'    => $user->id,
			'action'     => 'clerk_action',
			'note'       => $note,
			'value'      => $value,
			'valid_time' => 0,
			'status'     => 1,
			'item_type'  => Clerk::class,
			'item_id'    => $clerk->id,
		]);

		event('point.change', $user->id);

		event('st.wechat.message.point', [$user, config('ibrand.shitang-api.clerk_rule.point_note')[$type] . '：', $value]);

		return $this->success(['point' => $point]);
	}
}