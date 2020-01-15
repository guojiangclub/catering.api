<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use GuoJiangClub\Catering\Component\Balance\Model\Balance;
use GuoJiangClub\Catering\Component\User\Repository\UserRepository;
use GuoJiangClub\Catering\Backend\Models\Order;
use GuoJiangClub\Catering\Backend\Models\Point;
use GuoJiangClub\Catering\Server\Transformers\BalanceTransformer;
use GuoJiangClub\Catering\Server\Transformers\CouponsTransformer;
use GuoJiangClub\Catering\Server\Transformers\OrderTransformer;
use GuoJiangClub\Catering\Server\Transformers\PointTransformer;
use GuoJiangClub\Catering\Server\Transformers\UserTransformer;
use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Server\Repositories\CouponRepository;
use ElementVip\Component\Balance\Repository\BalanceRepository;
use GuoJiangClub\Catering\Server\Repositories\OrderRepository;

class CommercialUserController extends Controller
{
	protected $userRepository;
	protected $pointRepository;
	protected $couponRepository;
	protected $balanceRepository;
	protected $orderRepository;

	public function __construct(UserRepository $userRepository, PointRepository $pointRepository, CouponRepository $couponRepository, BalanceRepository $balanceRepository, OrderRepository $orderRepository)
	{
		$this->userRepository    = $userRepository;
		$this->pointRepository   = $pointRepository;
		$this->couponRepository  = $couponRepository;
		$this->balanceRepository = $balanceRepository;
		$this->orderRepository   = $orderRepository;
	}

	public function list()
	{
		$limit = request('limit') ? request('limit') : 15;

		$where['status'] = 1;
		if (request('mobile')) {
			$where['mobile'] = ['like', '%' . request('mobile') . '%'];
		}

		$list = $this->userRepository->getUsersByCondition($where, $limit);

		return $this->response()->paginator($list, new UserTransformer());
	}

	public function search()
	{
		if (!request('card_no')) {
			return $this->failed('请输入会员号');
		}

		$user = $this->userRepository->findWhere(['card_no' => request('card_no'), 'status' => 1])->first();
		if (!$user) {
			return $this->failed('用户不存在');
		}

		return $this->success($user);
	}

	public function detail($user_id)
	{
		$user       = $this->userRepository->find($user_id);
		$pointValid = $this->pointRepository->getSumPointValid($user->id, 'default');

		if (!$user->avatar) {
			$user->avatar = asset('/assets/backend/market/img/no_head.jpg');
			if (settings('enabled_union_pay')) {
				$user->avatar = asset('/assets/backend/shitang/img/default.png');
			}
		}

		return $this->success([
			'pointValid' => $pointValid,
			'user'       => $user,
		]);
	}

	public function couponList($user_id)
	{
		$limit = request('limit') ? request('limit') : 15;
		$type  = request('type') ? request('type') : 'active';

		if ($type == 'active') {
			$coupons = $this->couponRepository->findActiveByUser($user_id, $limit);
		} else {
			$coupons = $this->couponRepository->findInvalidByUser($user_id, $limit);
		}

		return $this->response()->paginator($coupons, new CouponsTransformer());
	}

	public function pointList($user_id)
	{
		$user = $this->userRepository->find($user_id);

		$type = request('type') ? request('type') : 'default';
		$list = $user->points()->type($type);
		if (request('balance') == 'in') {
			$list = $list->where('value', '>', 0);
		}

		if (request('balance') == 'out') {
			$list = $list->where('value', '<', 0);
		}

		$list = $list->orderBy('created_at', 'desc')->paginate();

		$point          = $this->pointRepository->getSumPoint($user->id, $type);
		$pointValid     = $this->pointRepository->getSumPointValid($user->id, $type);
		$pointFrozen    = $this->pointRepository->getSumPointFrozen($user->id, $type);
		$pointOverValid = $this->pointRepository->getSumPointOverValid($user->id, $type);
		$pointUsed      = Point::where('user_id', $user->id)->where('value', '<', 0)->sum('value');

		$data = [
			'point'          => $point,
			'pointValid'     => $pointValid,
			'pointFrozen'    => $pointFrozen,
			'pointOverValid' => $pointOverValid,
			'pointUsed'      => $pointUsed,
		];

		return $this->response()->paginator($list, new PointTransformer())->setMeta($data);
	}

	public function balanceList($user_id)
	{
		$type = request('type') ? request('type') : 'recharge';

		$sum = Balance::sumByUser($user_id);
		if (!is_numeric($sum)) {
			$sum = 0;
		} else {
			$sum = (int) $sum;
		}

		return $this->response()->paginator($this->balanceRepository->fluctuation($user_id, $type)->paginate(), new BalanceTransformer())->setMeta(['sum' => $sum]);
	}

	public function orderList($user_id)
	{
		$limit = request('limit') ? request('limit') : 15;

		$list = $this->orderRepository->getOrdersByCondition(['user_id' => $user_id, 'status' => ['>', 0]], [], $limit, ['user']);

		return $this->response()->paginator($list, new OrderTransformer())->setMeta(['manager_shop_name' => settings('manager_shop_name'), 'manager_shop_address' => settings('manager_shop_address')]);
	}
}