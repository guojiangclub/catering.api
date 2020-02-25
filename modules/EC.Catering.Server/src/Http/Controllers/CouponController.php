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
use ElementVip\Server\Transformers\CouponsTransformer;
use ElementVip\Component\Discount\Services\DiscountService;
use DNS1D;
use ElementVip\Server\Transformers\DiscountTransformer;
use ElementVip\Distribution\Server\Repository\AgentRepository;
use iBrand\Miniprogram\Poster\MiniProgramShareImg;
use ElementVip\Component\User\Models\User;
use ElementVip\Server\Services\MiniProgramService;

class CouponController extends Controller
{
    private $couponRepository;
    private $discountService;
    private $discountRepository;
    private $agentRepository;
    private $miniProgramService;

    public function __construct(
        CouponRepository $couponRepository
        , DiscountService $discountService
        , DiscountRepository $discountRepository
        ,AgentRepository $agentRepository
        ,MiniProgramService $miniProgramService
    )
    {
        $this->couponRepository = $couponRepository;
        $this->discountService = $discountService;
        $this->discountRepository = $discountRepository;
        $this->agentRepository=$agentRepository;
        $this->miniProgramService = $miniProgramService;
    }

    /**
     * 获取优惠券列表
     * @return mixed
     * is_Active 是否有效 0 无效 1有效
     */
    public function getCoupon()
    {
        if (empty(request('is_active')) AND request('is_active') === '') {
            return $this->api('', false, 400, '无效请求,缺少请求参数');
        }

        $user = request()->user();

        if (request('is_active') == 0) {
            $coupons = $this->couponRepository->findInvalidByUser($user->id);
        } elseif (request('is_active') == 1) {
            if (request('channel')) {
                $coupons = $this->couponRepository->findActiveByUser($user->id, 15, request('channel'));
            } else {
                $coupons = $this->couponRepository->findActiveByUser($user->id);
            }

        } else {
            $coupons = $this->couponRepository->findUsedByUser($user->id);
        }
        return $this->response()->paginator($coupons, new CouponsTransformer());
    }


    /**
     * 领取优惠券
     */
    public function getCouponConvert()
    {
        if (!request('coupon_code')) {
            return $this->api([], false, 400, '请输入兑换码');
        }

        try {

            $utm_campaign = request('utm_campaign');

            $utm_source = request('utm_source');

            $type = request('type') ? request('type') : 0;

            if(request('agent_code')){

                if($this->discountService->CouponIsAgentShare(request('coupon_code'))){

                    $agent = $this->agentRepository->findWhere(['status'=>1,'code'=>request('agent_code')])->first();

                    if($agent){

                        $utm_campaign = 'agent';

                        $utm_source = $agent->id;
                    }
                }

            }

            //$couponConvert = $this->discountService->getCouponConvert(request('coupon_code'), request()->user()->id, $type, $utm_campaign, $utm_source);

            $couponConvert = $this->discountService->getCouponConvert(request('coupon_code'), request()->user()->id,$utm_campaign, $utm_source);

            $couponConvert=$couponConvert?$couponConvert->toArray():[];

            return $this->api($couponConvert);

        } catch (\Exception $exception) {
            return $this->api([], false, 400, $exception->getMessage());
        }

    }


    /**
     * 获取优惠券详细信息
     * @param $couponId
     * @return Response
     */
    public function getCouponDetails($couponId)
    {
        $CouponDetails = $this->couponRepository->getCouponDetails($couponId, request()->user()->id);
        return $this->api($CouponDetails);

    }


    /**
     * 获取可用优惠券数量
     * @return mixed
     */
    public function getCouponNumByUser()
    {
        return $this->api($this->couponRepository->getValidCouponCountByUser(request()->user()->id));
    }

    /**
     * 获取条形码
     * @return Response
     */
    public function getCouponBarCodes()
    {
        $barCode = DNS1D::getBarcodePNG(request('coupon_code'), "EAN13", "4", "100");
        return $this->api(array('base64' => 'data:image/png;base64,' . $barCode));
    }

    /**
     * get coupon discount
     * @param $id
     */
    public function getShareCoupon($id)
    {
        if (!$discount = $this->discountRepository->find($id) OR !$discount->isCouponBased()) {
            return $this->api(null, false, 400, '不存在此优惠券');
        }

        return $this->api($discount);
    }

    /**
     * 领券页面获取优惠券信息
     * @return \Dingo\Api\Http\Response
     */
    public function getCouponByType()
    {
        if (!request('code') AND !request('id')) {
            return $this->api(null, false, 400, '参数错误');
        }

        if ($code = request('code')) {
            $discount = $this->discountRepository->findWhere(['code' => $code])->first();
        }
        if ($id = request('id')) {
            $discount = $discount = $this->discountRepository->find($id);
        }

        if (!$discount OR !$discount->isCouponBased()) {
            return $this->api(null, false, 400, '不存在此优惠券');
        }
        return $this->api($discount);

    }

    /**
     * 推客优惠券分享页面
     * @param $agent_code
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getAgentCouponView($agent_code){

        $agent=$this->agentRepository->findWhere(['status'=>1,'code'=>$agent_code])->first();

        $coupon = $this->discountRepository->find(request('coupon_id'));

        if(!$agent || !$coupon || $coupon->is_agent_share!=1){
            abort(404);
        }

        $user=User::find($agent->user_id);

        $scene=request('coupon_id').','.$agent->code;

        return view('server::share.agent_coupon', compact('coupon','user','scene'));
    }




    public function getAgentCouponImage(){

        $pages = request('pages') ? request('pages') : '';

        $agent=$this->agentRepository->findWhere(['status'=>1,'user_id'=>request()->user()->id])->first();

        if(!$agent)  return $this->failed([]);

        $route = url('api/coupon/share/agent').'/'.$agent->code.'?coupon_id='.request('coupon_id');

        $scene=request('coupon_id').','.$agent->code;

        $result = MiniProgramShareImg::generateShareImage($route,'agent_coupon');

        //获取小程序码
        $mini_code = $this->miniProgramService->createMiniQrcode($pages, 800, $scene, 'agent_coupon');

        if (!$mini_code) {

            return $this->failed('生成小程序码失败');
        }


        if($result AND isset($result['url'])){

            return $this->success($result);
        }

        return $this->failed([]);
    }



}