<?php

namespace GuoJiangClub\Catering\Component\Point\Repository;

use Prettus\Repository\Eloquent\BaseRepository;
use GuoJiangClub\Catering\Component\Point\Model\Point;
use GuoJiangClub\Catering\Component\Point\Contract\PointSubjectContract;

class PointRepository extends BaseRepository
{

	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model()
	{
		return Point::class;
	}

	public function getSumPoint($id, $type = null)
	{
		if ($type !== null) {
			$sum = $this->model->where([
				'user_id' => $id,
				'type'    => $type,
			])->sumPoint();
		} else {
			$sum = $this->model->where('user_id', $id)->sumPoint();
		}

		return $this->getSumNumeric($sum);
	}

	public function getSumPointValid($id, $type = null)
	{
		if ($type !== null) {
			$sum = $this->model->where([
				'user_id' => $id,
				'type'    => $type,
			])->valid()->sumPoint();
		} else {
			$sum = $this->model->where('user_id', $id)->valid()->sumPoint();
		}

		return $this->getSumNumeric($sum);
	}

	public function getSumPointOverValid($id, $type = null)
	{
		if ($type !== null) {
			$sum = $this->model->where([
				'user_id' => $id,
				'type'    => $type,
			])->overValid()->sumPoint();
		} else {
			$sum = $this->model->where('user_id', $id)->overValid()->sumPoint();
		}

		return $this->getSumNumeric($sum);
	}

	public function getSumPointFrozen($id, $type = null)
	{
		if ($type !== null) {
			$sum = $this->model->where([
				'user_id' => $id,
				'type'    => $type,
				'status'  => 0,
			])->withinTime()->sumPoint();
		} else {
			$sum = $this->model->where('user_id', $id)->where('status', 0)->withinTime()->sumPoint();
		}

		return $this->getSumNumeric($sum);
	}

	public function getListPoint($id, $valid = 0)
	{
		/*use for manager*/
		$query = $this->model->where('user_id', $id);
		if (0 == $valid) {
			$query = $query->get();
		} elseif (1 == $valid) {
			$query = $query->valid()->get();
		} else {
			$query = $query->overValid()->get();
		}

		return $query;
	}

	private function getSumNumeric($sum)
	{
		if (!is_numeric($sum)) {
			return 0;
		}

		return $sum;
	}

	public function checkUserPoint(PointSubjectContract $subject, $num)
	{
		$uid           = $subject->user_id;
		$point         = $this->getSumPointValid($uid, 'default');
		$pointDiscount = $num * (settings('point_proportion') ? settings('point_proportion') : 0);
		$pointLimit    = $subject->total * (settings('point_order_limit') ? settings('point_order_limit') : 0);
		$num           = $this->getSumNumeric($num);
		if ($point >= $num && $pointDiscount <= $pointLimit) {
			return true;
		}

		return false;
	}

	public function updateUserPoint($uid)
	{
		$user = config('auth.providers.users.model');
		$user = new $user();
		$user = $user::find($uid);
		if ($user) {
			$user->integral           = $this->getSumPoint($uid, 'default');
			$user->available_integral = $this->getSumPointValid($uid, 'default');
			$user->save();

			return true;
		}

		return false;
	}

	/**
	 * 根据action 获取月度积分总和，如果不传，则获取当月
	 *
	 * @param     $userId
	 * @param     $action
	 * @param int $month
	 *
	 * @return mixed
	 */
	public function getMonthlySumByAction($userId, $action, $month = 0)
	{
		$query = $this->model->where('action', $action)->where('user_id', $userId);
		if ($month == 0) {
			$query = $query->whereRaw("DATE_FORMAT(el_point.created_at, '%Y%m') = DATE_FORMAT(CURDATE() , '%Y%m')");
		} else {
			//暂未实现
		}

		return $query->sum('value');
	}

	/**
	 * 根据action 获取某天积分总和，如果不传，则获取当天
	 *
	 * @param     $userId
	 * @param     $action
	 * @param int $day
	 *
	 * @return mixed
	 */
	public function getDailySumByAction($userId, $action, $day = 0)
	{
		$query = $this->model->where('action', $action)->where('user_id', $userId);
		if ($day == 0) {
			$query = $query->whereRaw("DATE_FORMAT(el_point.created_at, '%Y%m%d') = DATE_FORMAT(CURDATE() , '%Y%m%d')");
		} else {
			//暂未实现
		}

		return $query->sum('value');
	}

	/**
	 * 根据action 判断用户是否已获得一次性积分记录
	 *
	 * @param $userID
	 * @param $action
	 *
	 * @return bool
	 */
	public function getRecordByAction($userID, $action)
	{
		if ($record = $this->findWhere(['action' => $action, 'user_id' => $userID])->first()) {
			return false;
		}

		return true;
	}

	public function getPointsByConditions($where, $limit = 20)
	{
		$this->applyConditions($where);

		return $this->orderBy('created_at', 'desc')->paginate($limit);
	}

	public function distributePercentage(PointSubjectContract $subject)
	{
		if (!$adjustment = $subject->getAdjustments() OR !$adjustment = $adjustment->where('origin_type', 'point')->first()) {
			return false;
		}
		$amount              = (-1) * $adjustment->amount;
		$splitDiscountAmount = [];
		$numberOfTargets     = $subject->countItems();
		$percentageTotal     = 100;
		$i                   = 1;
		$items               = $subject->getItems();
		foreach ($items as $item) {
			if ($i > $numberOfTargets) {
				break;
			}
			if ($i == $numberOfTargets) {
				$percentageItem = $percentageTotal;
			} else {
				//因为Backend下的Order模型定义了items_total获取时自动 / 100，percentageItem计算时原本应该 * 100，这里没有处理
				//所以此方法暂时只适用于Backend下导出订单
				$percentageItem  = (int) ($item->units_total / $subject->items_total);
				$percentageTotal -= $percentageItem;
			}
			$splitDiscountAmount[] = [
				'item_id' => $item->id,
				'value'   => (int) ($amount * $percentageItem / 100),
			];
			$i++;
		}

		return $splitDiscountAmount;
	}
}