<?php
namespace ElementVip\Server\Http\Controllers;

use Carbon\Carbon;
use DB;
use ElementVip\Component\Discount\Models\Discount;
use ElementVip\Component\Discount\Repositories\CouponRepository;
use ElementVip\Component\Discount\Repositories\DiscountRepository;
use ElementVip\Component\Discount\Services\DiscountService;
use ElementVip\Component\Marketing\MarketingService;
use ElementVip\Component\Marketing\Models\Marketing;
use ElementVip\Component\User\Models\UserBind;
use ElementVip\Server\Http\Controllers\Controller;
use Illuminate\Auth\Events\Login;
use Validator;
use ElementVip\Component\Registration\Models\Registration;
use Illuminate\Http\Request;
use ElementVip\Component\Registration\Models\ErpDeliveriesData;
use ElementVip\Store\Backend\Repositories\RegistrationsRepository;
use ElementVip\Component\Brand\Models\Brand;
use Mail;
use RuntimeException;
use ElementVip\Server\Exception\UserExistsException;
use League\OAuth2\Server\Exception\OAuthServerException;
use iBrand\Sms\Facade as Sms;


class RegistrationController extends Controller
{


    protected $registrationsRepository;
    protected $marketingService;
    protected $couponRepository;
    protected $discountRepository;
    protected $discountService;

    public function __construct(RegistrationsRepository $registrationsRepository, MarketingService $marketingService
        , CouponRepository $couponRepository
        , DiscountRepository $discountRepository
        , DiscountService $discountService)
    {
        $this->registrationsRepository = $registrationsRepository;
        $this->marketingService = $marketingService;
        $this->couponRepository = $couponRepository;
        $this->discountRepository = $discountRepository;
        $this->discountService = $discountService;
    }

    /**
     * 获取商品注册列表
     */

    public function getRegistrationByUser()
    {

        $registration = Registration::getRegistrationByUser(request()->user()->id);
        $registrationList = [];
        if (count($registration)) {

            foreach ($registration as $key => $value) {
                $registrationList[$key]['id'] = $value->id;
                $registrationList[$key]['sku'] = isset($value->sku) ? $value->sku : '';
                $registrationList[$key]['status'] = isset($value->status) ? $value->status : '';
                $registrationList[$key]['order_id'] = isset($value->order_id) ? $value->order_id : '';
                $registrationList[$key]['brand_id'] = isset($value->brand_id) ? $value->brand_id : '';

                $registrationList[$key]['entity_id'] = 0;
                $registrationList[$key]['is_entity'] = false;

                $registrationList[$key]['used_at'] = $value->used_at;

                if ($entity = DB::table('funtasy_entity_card')->where(['registration_id' => $value->id])->first()) {
                    $registrationList[$key]['entity_id'] = $entity->id;
                }

                if ($value->used_at > env('ENTITY_DATE')) {
                    $registrationList[$key]['is_entity'] = true;
                }


                $registrationList[$key]['marketing_id'] = isset($value->marketing_id) ? $value->marketing_id : '';
                $registrationList[$key]['goods_name'] = isset($value->product->goods->name) ? $value->product->goods->name : '';
                $registrationList[$key]['goods_img'] = isset($value->product->photo->url) ? $value->product->photo->url : '';
                $registrationList[$key]['spec_array'] = isset($value->product->spec_array) ? implode(' ', collect(json_decode($value->product->spec_array))->pluck('value')->toArray()) : '';

            }
        }
//        return $registrationList;
//        $reg_list=isset($registrationList)&& count($registrationList)  ? $registrationList :'[]';

        return $this->api($registrationList);

    }

    /**
     * 商品注册，貌似是不需要的代码
     */
    public function activateRegistration()
    {
        //
        $validate = request()->all();
        $validator = Validator::make($validate, [
            'pass' => 'required',
            'sn' => 'required',
            /*'channel' => 'required'*/
        ]);

        if ($validator->fails()) {
            return $this->api('', false, 400, '请检查提交的信息是否有误');
        }

        if (!$registration_list = Registration::activateRegistration($validate)) {
            return $this->api('', false, 400, '该商品注册码不能使用');
        }
        if (is_string($registration_list)) {
            return $this->api('', false, 400, $registration_list);
        }

        $registration = Registration::find($registration_list->id);


        event('goods.registration.success', [$registration]);

        return $this->api(['id' => $registration->id], true, 200, '商品注册成功');
    }


    /**
     * 验证商品注册码，进行商品注册
     * @return \Dingo\Api\Http\Response
     */

    public function validateRegistration()
    {
        $validate = request()->all();
        $validator = Validator::make($validate, [
            'pass' => 'required',
            'sn' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->api('', false, 400, '请检查提交的信息是否有误');
        }

        if (!$registration_list = Registration::validateRegistration($validate)) {
            //return $this->api('', false, 400, '商品注册开个小差，请稍后重新尝试或邮件联系客服 kefu@ospreypacks.com.cn');
            return $this->api('', false, 400, '商品注册开个小差，请稍后重新尝试或邮件联系客服');
        }
        if ($registration_list->status == 3) {
            return $this->api('', false, 400, '该商品已被注册');
        }
        if ($registration_list->status != 2) {
            return $this->api('', false, 400, '该商品注册码不能使用');
        }
        $registration_list->channel = request('channel');
        return $this->api($registration_list);
    }

    /**
     * 获取正在注册商品的信息（图片 名称 品牌）
     * @param $id
     * @return \Dingo\Api\Http\Response
     */

    public function getRegistrationGoods($id)
    {
        if (!$registrationGood = Registration::getRegistrationGoods($id)) {
            $registrationGoods = [];
        } else {
            $registrationGoods = array(
                'goods_id' => $registrationGood->product->goods->id,
                'goods_name' => $registrationGood->product->goods->name,
                'goods_img' => $registrationGood->product->photo->url,
                'brand_id' => $registrationGood->brand->id,
                'brand' => $registrationGood->brand->name
            );
        }

        return $this->api($registrationGoods);
    }


    /**
     * 已领取注册礼列表,查看注册礼
     * @param $id
     * @return \Dingo\Api\Http\Response
     */

    public function getReceiveRegistrationGift($id)
    {
        $registrationGift = Registration::getReceiveRegistrationGift($id);
        if (!$registrationGift) {
            return $this->api('', false, 400, '您当前没有领取的注册礼');
        }

        $user = request()->user();

        $registration = Registration::find($id);

        /*if ($registration->status == 3 AND ($registration->created_at < '1970-01-01 00:00:00' OR $registration->created_at == null)) {
            return $this->api(['gift' => [], 'coupons' => []]);
        }*/

        $coupons = [];
        $market_id = 0;
        $existedCoupons = [];

        $gift = [];

        if ($registrationGift->order_id) {
            foreach ($registrationGift->order->items as $key => $value) {
                $gift[$key]['goods_name'] = isset($value->item_name) ? $value->item_name : '';
                $gift[$key]['goods_img'] = isset($value->item_meta->image) ? $value->item_meta->image : '';
            }
        } elseif ($registration->marketing_id) {
            /*$goods = $registration->product->goods;

            $markings = $this->marketingService->getMarkingsByItem($goods, 'GOODS_REGISTER');*/

            $existedCoupons = $this->couponRepository->getUserCouponsByUtm($user->id, 'GOODS_REGISTER', $registration->id);

            $coupons = collect_to_array($existedCoupons);

        }


        return $this->api(['gift' => $gift, 'coupons' => $coupons, 'market_id' => $market_id]);

    }


    /**
     * 可领取注册礼列表,领取注册礼，查看该注册商品可以领取的注册礼有哪些
     * @param $id
     * @return \Dingo\Api\Http\Response
     */

    public function getRegistrationGiftList($id)
    {
        $giftList = [];
        $user = request()->user();
        $registration = Registration::find($id);
        $coupons = [];
        $market_id = 0;
        /*if ($registration->status == 3 AND ($registration->created_at < '1970-01-01 00:00:00' OR $registration->created_at == null)) {
            return $this->api(['gifts' => $giftList, 'coupons' => $coupons]);
        }*/

        $isReceived = false;
        if ($registration->order_id OR $registration->marketing_id) {
            $isReceived = true;
        }


        $goods = $registration->product->goods;
        $markings = $this->marketingService->getMarkingsByItem($goods, 'GOODS_REGISTER');

        $existedCoupons = $this->couponRepository->getUserCouponsByUtm($user->id, 'GOODS_REGISTER', $registration->id);

        if ($markings->count() > 0 AND $market = $markings->first()
            AND !$registration->order_id
        ) {

            $market_id = $market->id;

            $couponAction = array_where($market->actions, function ($value, $key) {
                return $key == 'coupon';
            });

            $discountIds = array_get($couponAction, 'coupon');

            $discounts = $this->discountRepository->findActive(1);

            $discounts = $discounts->whereIn('id', $discountIds);


            foreach ($discounts as $discount) {
                if ($existedCoupon = $existedCoupons->where('discount_id', $discount->id)->first()) {
                    $discount->isReceived = true;
                } else {
                    $discount->isReceived = false;
                }
                $discount->utmCampaign = 'GOODS_REGISTER';
                $discount->utmSource = $registration->id;
                $coupons[] = $discount;
            }
            /*foreach ($market->actions as $key => $value) {
                if ($key == 'coupon') {
                    $coupons = Discount::whereIn('id',$value)->get()->toArray();
                }
            }*/
        }

        if (count($coupons) > 0 OR count($existedCoupons) > 0) {
            return $this->api(['coupons' => $coupons, 'gifts' => $giftList, 'market_id' => $market_id, 'is_received' => $isReceived]);
        }

        $goods = Registration::getRegistrationGiftList($id);


        if ($goods) {
            foreach ($goods as $item) {
                foreach ($item->products as $value) {
                    $giftList[] = array(
                        'goods_id' => $item->id,
                        'products_id' => $value->id,
                        'goods_name' => $item->name,
                        'img' => isset($value->photo->url) ? $value->photo->url : '',
                        'spec' => isset($value->spec_array) ? implode(' ', collect(json_decode($value->spec_array))->pluck('value')->toArray()) : '',

                    );
                }
            }
        }

        //$this->couponRepository->findWhere()

        return $this->api(['coupons' => $coupons, 'gifts' => $giftList, 'is_received' => $isReceived]);
    }

    /***
     * 未登录领取注册礼
     * @return \Dingo\Api\Http\Response
     * @throws OAuthServerException
     * @throws UserExistsException
     */
    public function notLoggedActivateRegistration()
    {
        $pass = request('pass');
        $sn = request('sn');
        $mobile = request('mobile');
        $code = request('code');
        $type = 'direct';
        $open_id = request('open_id');
        if (is_null($model = config('auth.providers.users.model'))) {
            throw new RuntimeException('Unable to determine user model from configuration.');
        }

        if (!empty($type) AND $type == 'register') {  //check if mobile exists
            if ($model::where('mobile', $mobile)->first()) {
                throw new UserExistsException();
            }
        }

        $credentials = [
            'sn' => $sn,
            'pass' => $pass,
            'mobile' => $mobile,
            'verifyCode' => $code,
            'channel' => request('channel')
        ];

        //验证数据
        $validator = Validator::make($credentials, [
            'sn' => 'required',
            'pass' => 'required',
            /*'channel' => 'required',
            'mobile' => 'required|confirm_mobile_not_change|confirm_rule:mobile_required',
            'verifyCode' => 'required|verify_code',*/
        ]);

        /*if ($validator->fails()) {
            throw OAuthServerException::invalidCredentials();
        }*/

        if ($validator->fails()) {
            return $this->api('', false, 400, '请检查提交的信息是否有误');
        }

        if (!Sms::checkCode($mobile, \request('code'))) {
            return $this->api('', false, 400, '验证码错误');
        }

        if (!$user = $model::where('mobile', $mobile)->first()) {
            $user = $model::create([
                'mobile' => $mobile
                , 'card_limit' => date('Y-m-d', time())
                , 'group_id' => 1
            ]);
            event(new Login($user, true));
        }

        if ($open_id AND request('open_type') == 'wechat') {
            $this->bindUserInfo($user, $open_id);
        }


        $token = $user->createToken($mobile)->accessToken;

        $validate = [
            'sn' => $sn,
            'pass' => $pass
        ];

        if (!$registration_list = Registration::validateRegistration($validate)) {
            return $this->api('', false, 400, '产品号或认证号有误，确认后请重新尝试或邮件联系客服');
        }
        if ($registration_list->status == 3) {
            return $this->api('', false, 400, '该商品已被注册');
        }
        if ($registration_list->status != 2) {
            return $this->api('', false, 400, '该商品注册码不能使用');
        }

        if (!$registration_list = Registration::activateRegistration($validate, $user)) {
            return $this->api('', false, 400, '该商品注册码不能使用');
        }

        /*if (!$registration_list = Registration::validateRegistration($validate)) {
            return $this->api('', false, 400, '商品注册开个小差，请稍后重新尝试或邮件联系客服 kefu@ospreypacks.com.cn');
        }
        if ($registration_list->status != 2) {
            return $this->api('', false, 400, '该商品注册码不能使用');
        }*/

        event('goods.registration.success', [$registration_list]);

        $data = [
            'registration_list' => $registration_list,
            'channel' => request('channel'),
            'token' => $token,
            'token_type' => 'Bearer'
        ];
        return $this->api($data);


    }

    /**
     * BI数据对接
     * @return \Dingo\Api\Http\Response
     */
    public function updateRegistration()
    {
        $input = json_decode(request('data'), true);

        $error_array = [];
        $error_num = 0;
        $success_num = 0;
        $inputCount = count($input);
        if ($inputCount == 0) {
            return $this->api('', false, 400, '请传递正确的数据');
        }
        foreach ($input as $item) {
            $inputData = array(
                'customer_channel_id' => isset($item['customer_channel_id']) ? $item['customer_channel_id'] : '',
                'customer_channel_name' => isset($item['customer_channel_name']) ? $item['customer_channel_name'] : '',
                'sn' => isset($item['sn']) ? $item['sn'] : '',
                'sku' => isset($item['sku']) ? $item['sku'] : '',
                'brand_name' => isset($item['brand_name']) ? $item['brand_name'] : '',
                'deliveries_at' => isset($item['deliveries_at']) ? $item['deliveries_at'] : '',

            );
            if (!$erpData = ErpDeliveriesData::create($inputData)) {
                $error_array[] = $item['sn'];
                $error_num += 1;

            }
            if (isset($item['brand_name']) && !empty($item['brand_name'])) {
                $brand = Brand::where('name', $item['brand_name'])->first();
                if (isset($brand)) {
                    if (!$registrations = $this->registrationsRepository->findWhere(['sn' => $item['sn'], 'brand_id' => $brand->id])->first()) {
                        $error_array[] = $item['sn'];
                        $error_num += 1;
                    } else {
                        $registrations->sku = $item['sku'];
                        $registrations->status = 2;
                        $registrations->save();
                        $erpData->status = 1;
                        $erpData->save();
                    }

                } else {
                    $error_array[] = $item['sn'];
                    $error_num += 1;
                }
            }
        }
        $error_sn = implode(',', $error_array);
        if ($inputCount != 0) {
            $success_num = $inputCount - $error_num;
        }

        $data = array(
            'error_sn' => $error_sn,
            'error_num' => $error_num,
            'success_num' => $success_num
        );
        $mail_erp_user = explode(',', env('MAIL_ERP_USER'));

        foreach ($mail_erp_user as $mailUser) {
            Mail::raw('匹配成功数：' . $success_num . '个  匹配失败个数：' . $error_num . '个   匹配失败SN：' . $error_sn, function ($message) use ($error_sn, $error_num, $success_num, $mailUser) {
                $message->to($mailUser)->subject('商品注册匹配结果');
            });
        }
        return $this->api($data);


    }

    /**
     * 一次性领取营销事件的注册礼
     */
    public function receiveMarketingGift()
    {
        if (!$market_id = request('market_id') OR !$registration_id = request('registration_id')) {
            return $this->api('', false, 400, '非法请求');
        }

        if (!$market = Marketing::find($market_id) OR $market->type != 'GOODS_REGISTER') {
            return $this->api('', false, 400, '非法请求');
        }

        $couponAction = array_where($market->actions, function ($value, $key) {
            return $key == 'coupon';
        });

        $discountIds = array_get($couponAction, 'coupon');

        $discounts = $this->discountRepository->findWhereIn('id', $discountIds);

        try {

            DB::beginTransaction();

            foreach ($discounts as $discount) {
                if (!$this->couponRepository->findWhere(['discount_id' => $discount->id
                    , 'user_id' => request()->user()->id, 'utm_campaign' => 'GOODS_REGISTER', 'utm_source' => $registration_id])->first()
                ) {
                    $couponConvert = $this->discountService->getCouponConvert($discount->code, request()->user()->id,
                        0, 'GOODS_REGISTER', $registration_id);
                }

            }

            $registration = Registration::find($registration_id);
            $registration->marketing_id = $market_id;
            $registration->save();

            DB::commit();

            return $this->api();

        } catch (\Exception $exception) {
            DB::rollBack();

            return $this->api('', false, 500, $exception->getMessage());
        }
    }

    private function bindUserInfo($user, $open_id)
    {
        $userInfo = [];
        $wxInfo = wechat_channel()->getUserInfo($open_id);
        if (!$wxInfo) {
            $wxInfo = wechat_channel()->getUserInfo($open_id, true);
            \Log::info('register goods get wechat user info2:' . json_encode($wxInfo));
        }

        if ($wxInfo AND !isset($wxInfo->errcode)) {
            $userInfo['nickName'] = $wxInfo->nickname;
            $userInfo['gender'] = $wxInfo->sex;
            $userInfo['avatarUrl'] = $wxInfo->headimgurl;
            $userInfo['city'] = $wxInfo->city;
            if (isset($wxInfo->unionid)) {
                $userInfo['union_id'] = $wxInfo->unionid;
            }

            $data = [];
            if (!$user->nick_name) {
                $data['nick_name'] = $userInfo['nickName'];
            }
            if (!$user->sex) {
                $data['sex'] = $userInfo['gender'] == 1 ? '男' : '女';
            }
            if (!$user->avatar) {
                $data['avatar'] = $userInfo['avatarUrl'];
            }
            if (!$user->city) {
                $data['city'] = $userInfo['city'];
            }

            if (!$user->union_id AND isset($userInfo['union_id'])) {
                $data['union_id'] = $userInfo['union_id'];
            }

            if (count($data) > 0) {
                $user->fill($data);
                $user->save();
            }

            $userBind = UserBind::Where(['open_id' => $open_id, 'type' => 'wechat'])->first();
            if (!$userBind) {
                $data_input=['open_id' => $open_id, 'type' => 'wechat', 'user_id' => $user->id, 'app_id' => settings('wechat_app_id')];
                if (isset($wxInfo->province)) {
                    $data_input['province']=$wxInfo->province;
                    UserBind::create($data_input);
                }else{
                    UserBind::create($data_input);
                }

            } else {
                UserBind::bindUser($open_id, 'wechat', $user->id);
            }
        }
        return $userInfo;
    }


}