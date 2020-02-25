<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-09-06
 * Time: 19:33
 */

namespace ElementVip\Server\Http\Controllers;

use ElementVip\Component\Discount\Repositories\CouponRepository;
use ElementVip\Component\Discount\Repositories\DiscountRepository;
use ElementVip\Component\Discount\Services\DiscountService;
use ElementVip\Component\Product\Models\Goods;
use ElementVip\Server\Transformers\DiscountTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use ElementVip\Distribution\Server\Repository\AgentRepository;

class DiscountController extends Controller
{
	private $couponRepository;
	private $discountService;
	private $discountRepository;
    private $agentRepository;

	public function __construct(
		CouponRepository $couponRepository
		, DiscountService $discountService
		, DiscountRepository $discountRepository
        ,AgentRepository $agentRepository
	)
	{
		$this->couponRepository   = $couponRepository;
		$this->discountService    = $discountService;
		$this->discountRepository = $discountRepository;
        $this->agentRepository=$agentRepository;
	}

	public function getDiscountByGoods($id)
	{
		$item = Goods::find($id);
		if (!$item) {
			return $this->api('', false);
		}

		$discounts = $this->discountService->getDiscountsByGoods($item);
		if (!$discounts || count($discounts) == 0) {
			return $this->api('', false);
		}

		$result['discounts'] = collect_to_array($discounts->where('coupon_based', 0));
		$result['coupons']   = collect_to_array($discounts->where('coupon_based', 1));

		//return $discounts->where('coupon_based', 1)->toArray();
		return $this->api($result);
	}

	public function shoppingCartDiscount(Request $request)
	{
		$input = $request->except('file', '_token');
		if (empty($input['ids'])) {
			return $this->api([], false, 500, '必填参数缺失');
		}

		$discount = [];
		$coupon   = [];
		foreach ($input['ids'] as $id) {
			$goods = Goods::find($id);
			if (!$goods) {

				continue;
			}

			$discounts = $this->discountService->getDiscountsByGoods($goods);
			if (!$discounts || count($discounts) == 0) {

				continue;
			}

			$coupon_based_0 = collect_to_array($discounts->where('coupon_based', 0));
			foreach ($coupon_based_0 as $d) {
				if (!array_key_exists($d['id'], $discount)) {
					$discount[$d['id']] = $d;
				}
			}

			$coupon_based_1 = collect_to_array($discounts->where('coupon_based', 1));
			foreach ($coupon_based_1 as $c) {
				if (!array_key_exists($c['id'], $coupon)) {
					$coupon[$c['id']] = $c;
				}
			}
		}

		$result = ['coupons' => array_values($coupon), 'discounts' => array_values($discount)];

		return $this->api($result);
	}

	/**
	 * 获取优惠券、促销活动列表
	 *
	 * @return \Dingo\Api\Http\Response
	 */
	public function getDiscountList()
	{

		$channel = request('type') == 1 ? 'shop' : 'ec';
        $is_agent_share=$this->is_agent()?1:0;
		$coupons = $this->discountRepository->getDiscountByType(request('is_coupon'), $channel, $limit = 10,$is_agent_share);

		foreach ($coupons as $key => $coupon) {
			if ($coupon->rules()->where('type', 'contains_wechat_group')->first()) {
				unset($coupons[$key]);
			}
		}
		return $this->response()->paginator($coupons, new DiscountTransformer('list'));
	}

	/**
	 * 促销活动 优惠券通用接口
	 *
	 * @param $id
	 *
	 * @return \Dingo\Api\Http\Response
	 */
	public function getDiscountDetailByID($id)
	{
		$discount = $this->discountRepository->find($id);

		if($discount->coupon_based==1 AND $discount->is_agent_share==1){
            $agent=$this->is_agent();
            if($agent){
                $discount->agent_code=$agent->code;
            }
        }

		return $this->response()->item($discount, new DiscountTransformer('detail'));
	}



	protected function is_agent(){

        if($user=auth('api')->user() AND request('is_agent')){
            return $this->agentRepository->findWhere(['status'=>1,'user_id'=>$user->id])->first();
        }
        return null;
    }

}
