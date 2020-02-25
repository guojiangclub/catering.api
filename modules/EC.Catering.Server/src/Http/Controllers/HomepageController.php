<?php
namespace ElementVip\Server\Http\Controllers;

use Carbon\Carbon;
use DB;
use ElementVip\Cms\Models\Menu;
use ElementVip\Component\Advertisement\Models\Advertisement;
use ElementVip\Component\Discount\Repositories\DiscountRepository;
use ElementVip\Component\Seckill\Repositories\SeckillItemRepository;
use ElementVip\Store\Backend\Model\Category;
use ElementVip\Store\Backend\Repositories\AdvertisementItemRepository;
use ElementVip\Component\Product\Repositories\GoodsRepository;

use ElementVip\Component\Gift\Repositories\GiftActivityRepository;
use ElementVip\Component\Gift\Models\GiftCouponReceive;
use ElementVip\Component\Gift\Models\GiftActivity;
use ElementVip\Component\Order\Repositories\OrderRepository;
use GuoJiangClub\Catering\Component\Order\Models\Order;
use ElementVip\Component\Point\Repository\PointRepository;
use ElementVip\Component\Gift\Repositories\CardRepository;
use ElementVip\Component\Suit\Repositories\SuitRepository;
use ElementVip\Cms\Models\PageTranslation;
use iBrand\Component\MultiGroupon\Repositories\MultiGrouponRepository;
use iBrand\FreeEvent\Core\Repository\FreeRepository;
use QrCode;

class HomepageController extends Controller
{
    const SHOP = "官方商城shop";
    const TopShuffling = "H5TopShuffling";
    const H5Stories = "H5Stories";
    const Notice = "Notice";
    const H5FloorMenu = "H5FloorMenu";
    const H5HeadTopAd = "H5HeadTopAd";
    const H5GoodsRecommend = "H5GoodsRecommend";
    const H5HeadBottomAd = "H5HeadBottomAd";
    const H5FootAd = "H5FootAd";
    const bestSalesGoods = "bestSalesGoods";
    const onlineService = "onlineService";
    const H5TabData = "H5TabData";
    const H5TopMiniAd = "H5TopMiniAd";


    protected $menu;
    protected $advertisement;
    protected $advertisementItemRepository;
    protected $goodsRepository;

    protected $giftActivityRepository;
    protected $orderRepository;
    protected $pointRepository;
    protected $cardRepository;
    protected $suitRepository;
    protected $seckillItemRepository;

    protected $freeRepository;
    protected $discountRepository;
    protected $multiGrouponRepository;

    public function __construct(Menu $menu
        , Advertisement $advertisement
        , AdvertisementItemRepository $advertisementItemRepository
        , GoodsRepository $goodsRepository
        , GiftActivityRepository $giftActivityRepository
        , OrderRepository $orderRepository
        , PointRepository $pointRepository
        , CardRepository $cardRepository
        , SuitRepository $suitRepository
        , SeckillItemRepository $seckillItemRepository
        , FreeRepository $freeRepository
        , DiscountRepository $discountRepository
        , MultiGrouponRepository $multiGrouponRepository
    )
    {
        $this->menu = $menu;
        $this->advertisement = $advertisement;
        $this->advertisementItemRepository = $advertisementItemRepository;
        $this->goodsRepository = $goodsRepository;

        $this->giftActivityRepository = $giftActivityRepository;
        $this->orderRepository = $orderRepository;

        $this->pointRepository = $pointRepository;
        $this->cardRepository = $cardRepository;
        $this->suitRepository = $suitRepository;
        $this->seckillItemRepository = $seckillItemRepository;
        $this->freeRepository = $freeRepository;
        $this->discountRepository = $discountRepository;
        $this->multiGrouponRepository = $multiGrouponRepository;
    }


    public function HomeMenu()
    {
        if ($newdata = cache('mobile-home-menu')) {
            return $this->api($newdata);
        }

        $menus = $this->menu->GetNavByCode('TopNav') ? $this->menu->GetNavByCode('TopNav') : [];
        $data = [];
        $key = '';
        if (isset($menus['key']) && count($menus) > 0) {
            foreach ($menus['key'] as $k => $item) {
                if ($item['title'] === self::SHOP) {
                    $key = $k;
                }
            }
            if ($key && isset($menus['value'][$key]) && count($menus['value'][$key]) > 0) {
                foreach ($menus['value'][$key] as $kk => $kitem) {

                    $data[$kitem['title']] = $kitem;

                    if (isset($menus['child'][$key][$kk]['ad'])) {
                        $data[$kitem['title']]['ad'] = $menus['child'][$key][$kk]['ad'];
                    }

                    if (isset($menus['child'][$key][$kk]['key']) && count($menus['child'][$key][$kk]['key']) > 0) {

                        foreach ($menus['child'][$key][$kk]['key'] as $ck => $ckitem) {

                            $data[$kitem['title']]['items'][$ck] = $ckitem;

                            if (isset($menus['child'][$key][$kk]['value'][$ck])) {

                                $data[$kitem['title']]['items'][$ck]['items'] = array_values($menus['child'][$key][$kk]['value'][$ck]);

                            }
                        }
                    }
                }
            }
        }
        $newData = array_values($data);
        foreach ($newData as $key => $item) {
            if (isset($item['items']) && count($item['items']) > 0) {
                $newData[$key]['items'] = array_values($item['items']);
            }
        }

        cache(['mobile-home-menu' => $newData], Carbon::now()->addHour(12));

        return $this->api($newData);

    }


    public function HomeIndex()
    {

        $array = [];
        $data = [];
        $includes = request('includes') ? request('includes') : '';
        if (!empty($includes)) {
            $array = explode(',', $includes);
        } else {
            return $this->api($data);
        }


        if ($newdata = cache('mobile-home-index')) {
            return $this->api($newdata);
        }

        //顶部轮播推广
        $H5TopShuffling = [];
        if (in_array(self::TopShuffling, $array)) {
            $H5TopShuffling = $this->advertisement->getAdByCodeNotChild(self::TopShuffling);
            $data[self::TopShuffling] = $H5TopShuffling;
        }

        // 探索故事推广
        $H5Stories = [];
        if (in_array(self::H5Stories, $array)) {
            $H5Stories = $this->advertisement->getAdByCodeNotChild(self::H5Stories);
            $data[self::H5Stories] = $H5Stories;
        }

        //H5首页顶部广告TOP推广
        $H5HeadTopAd = [];
        if (in_array(self::H5HeadTopAd, $array)) {
            $H5HeadTopAd = $this->advertisement->getAdByCodeNotChild(self::H5HeadTopAd);
            $data[self::H5HeadTopAd] = $H5HeadTopAd;
        }

        //H5首页顶部广告BOTTOM推广
        $H5HeadBottomAd = [];
        if (in_array(self::H5HeadBottomAd, $array)) {
            $H5HeadBottomAd = $this->advertisement->getAdByCodeNotChild(self::H5HeadBottomAd);
            $data[self::H5HeadBottomAd] = $H5HeadBottomAd;
        }


        // H5首页底部广告推广
        $H5FootAd = [];
        if (in_array(self::H5FootAd, $array)) {
            $H5FootAd = $this->advertisement->getAdByCodeNotChild(self::H5FootAd);
            $data[self::H5FootAd] = $H5FootAd;
        }

        //滚动系统公告推广
        $Notice = [];
        if (in_array(self::Notice, $array)) {
            $Notice = $this->advertisement->getAdByCodeNotChild(self::Notice);
            $data[self::Notice] = $Notice;
        }

        //H5首页商品推广
        $H5GoodsRecommend = [];
        if (in_array(self::H5GoodsRecommend, $array)) {
            $H5GoodsRecommend = $this->advertisement->RecursiveGetAdByCode(self::H5GoodsRecommend);
            $data[self::H5GoodsRecommend] = $H5FootAd;
        }


        // H5首页底部分类菜单
        $H5FloorMenu = [];
        if (in_array(self::H5FloorMenu, $array)) {
            $floorMenu = $this->menu->GetMenuByCode(self::H5FloorMenu);
            if (isset($floorMenu['key']) && count($floorMenu['key']) > 0) {
                foreach ($floorMenu['key'] as $item) {
                    $H5FloorMenu[] = $item;
                }
            }
            $data[self::H5FloorMenu] = $H5FloorMenu;
        }

//        //最佳销售单品
//        $bestSalesGoods =[];
//        if(in_array(self::bestSalesGoods,$array)) {
//
//            $num = settings('top_best_goods_num') ? settings('top_best_goods_num') : 10;
//            $bestSalesGoods = $this->goodsRepository->scopeQuery(function ($query) use ($num) {
//                $query->where(['is_del' => 0, 'is_largess' => 0]);
//                return $query->limit($num);
//            })->orderBy('sale', 'desc')->all();
//
//            $data[self::bestSalesGoods]=$bestSalesGoods;
//
//        }

        //最佳销售单品
        $bestSalesGoods = [];
        $goods = [];
        if (in_array(self::bestSalesGoods, $array)) {
            $bestSalesGoods = $this->advertisement->getAdByCodeNotChild(self::bestSalesGoods);
            if (count($bestSalesGoods) > 0) {
                $goodsIds = [];
                $goodsSort = [];
                foreach ($bestSalesGoods as $item) {
                    if (isset($item['goods_id'])) {
                        $goodsIds[] = $item['goods_id'];
                        $goodsSort[$item['goods_id']] = $item['sort'];
                    }
                }
                $bestSalesGoods = $this->goodsRepository->findWhereIn('id', $goodsIds)->toArray();
                foreach ($bestSalesGoods as $k => $item) {
                    $goods[$k] = $item;
                    $goods[$k]['ad_sort'] = $goodsSort[$item['id']];
                    $goods[$k]['img'] = $this->getGoodsImgUrl($item['img']);
                }
                $newgoods = collect($goods)->sortBy('ad_sort')->toArray();
                $bestSalesGoods = array_values($newgoods);

            } else {
                $ad = $this->advertisement->where('code', 'bestSalesGoods')->get();
                if (count($ad) == 0) {
                    $num = settings('top_best_goods_num') ? settings('top_best_goods_num') : 10;
                    $bestSalesGoods = $this->goodsRepository->scopeQuery(function ($query) use ($num) {
                        $query->where(['is_del' => 0, 'is_largess' => 0]);
                        return $query->limit($num);
                    })->orderBy('sale', 'desc')->all();
                }

            }

            $data[self::bestSalesGoods] = $bestSalesGoods;
        }


        $onlineService = [
            'online_service_phone' => settings('online_service_phone'),
            'online_service_time' => '工作时间:' . settings('online_service_time'),
            'online_service_url' => settings('online_service_url') ? settings('online_service_url') : 'tel:' . settings('online_service_phone')
        ];

        // 在线客服数据
        $onlineService = [];
        if (in_array(self::onlineService, $array)) {
            $onlineService = [
                'online_service_phone' => settings('online_service_phone'),
                'online_service_time' => '工作时间:' . settings('online_service_time'),
                'online_service_url' => settings('online_service_url') ? settings('online_service_url') : 'tel:' . settings('online_service_phone')
            ];

        }


        $data = [
            'TNFH5TopShuffling' => $H5TopShuffling,
            'TNFNotice' => $Notice,
            'TNFH5HeadTopAd' => $H5HeadTopAd,
            'TNFH5HeadBottomAd' => $H5HeadBottomAd,
            'TNFH5GoodsRecommend' => $H5GoodsRecommend,
            'TNFH5FloorMenu' => $H5FloorMenu,
            'TNFH5Stories' => $H5Stories,
            'TNFH5FootAd' => $H5FootAd,
            'bestSalesGoods' => $bestSalesGoods,
            'onlineService' => $onlineService
        ];

        foreach ($data as $key => $item) {
            if (count($item) > 0) {
                $newdata[$key] = $item;
            }
        }

        cache(['mobile-home-index' => $newdata], Carbon::now()->addHour(12));

        return $this->api($newdata);


    }

    /**
     * 在线客服URL
     * @return \Dingo\Api\Http\Response
     */
    public function onlineService()
    {
        $data = [
            'online_service_url' => settings('online_service_url') ? settings('online_service_url') : 'tel:' . settings('online_service_phone')
        ];

        return $this->api($data);
    }

    protected function getGoodsImgUrl($value)
    {

        $replace_url = settings('store_img_replace_url') ? settings('store_img_replace_url') : url('/');

        if (settings('store_img_cdn_status') AND $url = settings('store_img_cdn_url')) {
            $value = str_replace('http://' . $replace_url, $url, $value);
            $value = str_replace('http://', 'https://', $value);
        }

        return $value;
    }


    //新人进店礼
    public function giftNewUserLanded()
    {
        $user = request()->user();
        if ($gift = $this->giftActivityRepository->DateProcessingGiftNewUser($user)) {
            $is_new_user = $gift->is_new_user;
            event('gift.new.user.point', [$user, $gift]);
            event('gift.new.user.coupon', [$user, $gift]);
            if (!$gift_new_user = $this->giftActivityRepository->DateProcessingGiftNewUser($user)) {
                return $this->api([]);
            }
            $point = $this->pointRepository->findWhere(['action' => 'gift_new_user_point', 'item_id' => $gift_new_user->id, 'item_type' => GiftActivity::class, 'user_id' => $user->id])->first();
            $gift_new_user->point_status = $point ? true : false;
            $gift_new_user->is_new_user = $is_new_user;
            $date['user'] = $user;
            $date['activity'] = $gift_new_user;
            return $this->api($date);
        }
        return $this->api([]);
    }

    public function giftNewUser()
    {
        $gift_new_user = $this->giftActivityRepository->giftListEffective('gift_new_user');
        return $this->api($gift_new_user);
    }


    public function giftBirthday()
    {
        $user = request()->user();
        $gift = $this->giftActivityRepository->DateProcessingGiftBirthday($user);
        if ($gift And isset($gift->activity_day)) {
            $users = $this->cardRepository->getInstantBirthdayUserByDay([], $gift->activity_day, 0, true);
            if (!$this->cardRepository->checkUserBirthdayInUsers($user->id, $users)) {
                return $this->api([]);
            }
            event('gift.birthday.point', [$user, $gift]);
            event('gift.birthday.coupon', [$user, $gift]);
            if (!$gift_new = $this->giftActivityRepository->DateProcessingGiftBirthday($user)) {
                return $this->api([]);
            }

            $point_status = false;
            $time = Carbon::now()->timestamp;
            $birthday = date('Y-m-d', $time);
            if ($point = $this->pointRepository->orderBy('created_at', 'desc')->findWhere(['action' => 'gift_birthday_point', 'user_id' => $user->id])->first()) {
                if (intval(strtotime(date('Y-m-d', strtotime($point->created_at))) == intval(strtotime($birthday)))) {
                    $point_status = true;
                }
            }
            $gift_new->point_status = $point_status ? true : false;
            $date['user'] = $user;
            $date['activity'] = $gift_new;
            return $this->api($date);
        }

        return $this->api([]);

    }


    /**
     * 小程序首页模块化数据
     * @return \Dingo\Api\Http\Response
     */
    public function getHomeModulesData()
    {
        $array = [];
        $data = [];
        $includes = request('includes') ? request('includes') : '';
        if (!empty($includes)) {
            $array = explode(',', $includes);
        } else {
            return $this->api($data);
        }

        /*秒杀*/
        $seckill = 'seckill';
        $data['seckill'] = [];
        if (in_array($seckill, $array)) {
            if ($seckillData = $this->seckillItemRepository->getSeckillItemFirst()) {
                $etime = strtotime($seckillData->seckill->ends_at);
                $seckillData->seckill->ends_at = date('Y/m/d H:i:s', $etime);

                $stime = strtotime($seckillData->seckill->starts_at);
                $seckillData->seckill->starts_at = date('Y/m/d H:i:s', $stime);

                $data['seckill'] = $seckillData;
            }
        }

        $groupon = 'groupon';
        $data['groupon'] = [];
        if (in_array($groupon, $array)) {
            $data['groupon'] = [];
            if ($grouponData = $this->multiGrouponRepository->getGrouponList()->first()) {
                $data['groupon'] = $grouponData;
            }
        }

        if ($modulesData = cache('mini-home-modules-data')) {
            if ($data['seckill']) {
                $modulesData['seckill'] = $data['seckill'];
            } else {
                unset($modulesData['seckill']);
            }

            if ($data['groupon']) {
                $modulesData['groupon'] = $data['groupon'];
            } else {
                unset($modulesData['groupon']);
            }
            return $this->api($modulesData);
        }

        //小程序顶部轮播推广:Mini_TopShuffling
        if (in_array('Mini_TopShuffling', $array)) {
            $Mini_TopShuffling = $this->advertisement->getAdByCodeNotChild('Mini_TopShuffling');
            $data['Mini_TopShuffling'] = $Mini_TopShuffling;
        }

        //优惠券列表
        if (in_array('Mini_coupons', $array)) {
            $user_id = auth('api')->user() ? auth('api')->user()->id : 0;
            $coupons = $this->discountRepository->getCouponsList($user_id);
            foreach ($coupons as $key => $coupon) {
                if ($coupon->rules()->where('type', 'contains_wechat_group')->first()) {
                    unset($coupons[$key]);
                }
            }

            $data['Mini_coupons'] = $coupons;
        }

        if (in_array('Mini_TH_banner', $array)) {
            $Mini_TH_banner = $this->advertisement->getAdByCodeNotChild('Mini_TH_banner');
            $data['Mini_TH_banner'] = $Mini_TH_banner;
        }

        //小程序 tab 切换:Mini_H5TabData
        if (in_array('Mini_H5TabData', $array)) {
            $Mini_H5TabData = $this->advertisement->RecursiveGetAdByCode('Mini_H5TabData');
            foreach ($Mini_H5TabData as $key => $item) {
                if (isset($item['items'])) {
                    $adArray = array_where($item['items'], function ($value, $key) {
                        return isset($value['goods_id']);
                    });
                    if (count($adArray)) {
                        $Mini_H5TabData[$key]['type'] = 'goods';
                    }
                }
            }
            $data['Mini_H5TabData'] = $Mini_H5TabData;
        }

        /*套餐*/
        $suit = 'suit';
        if (in_array($suit, $array)) {
            $data['suit'] = [];
            if ($suitData = $this->suitRepository->getFirstSuit()) {
                $data['suit'] = $suitData;
            }
        }

        //小程序首页小KV Mini_HeadBottomAd
        if (in_array('Mini_HeadBottomAd', $array)) {
            $Mini_HeadBottomAd = $this->advertisement->getAdByCodeNotChild('Mini_HeadBottomAd');
            $data['Mini_HeadBottomAd'] = $Mini_HeadBottomAd;
        }


        //最佳销售单品
        $bestSalesGoods = [];
        $goods = [];
        if (in_array(self::bestSalesGoods, $array)) {
            $bestSalesGoods = $this->advertisement->getAdByCodeNotChild(self::bestSalesGoods);
            if (count($bestSalesGoods) > 0) {
                $goodsIds = [];
                $goodsSort = [];
                foreach ($bestSalesGoods as $item) {
                    if (isset($item['goods_id'])) {
                        $goodsIds[] = $item['goods_id'];
                        $goodsSort[$item['goods_id']] = $item['sort'];
                    }
                }
                $bestSalesGoods = $this->goodsRepository->findWhereIn('id', $goodsIds)->toArray();
                foreach ($bestSalesGoods as $k => $item) {
                    $goods[$k] = $item;
                    $goods[$k]['ad_sort'] = $goodsSort[$item['id']];
                    $goods[$k]['img'] = $this->getGoodsImgUrl($item['img']);
                }
                $newgoods = collect($goods)->sortBy('ad_sort')->toArray();
                $bestSalesGoods = array_values($newgoods);

            } else {
                $ad = $this->advertisement->where('code', 'bestSalesGoods')->get();
                if (count($ad) == 0) {
                    $num = settings('top_best_goods_num') ? settings('top_best_goods_num') : 10;
                    $bestSalesGoods = $this->goodsRepository->scopeQuery(function ($query) use ($num) {
                        $query->where(['is_del' => 0, 'is_largess' => 0]);
                        return $query->limit($num);
                    })->orderBy('sale', 'desc')->all();
                }

            }

            $data[self::bestSalesGoods] = $bestSalesGoods;
        }

        //男女士优选
        $H5GoodsRecommend = [];
        if (in_array(self::H5GoodsRecommend, $array)) {
            $H5GoodsRecommend = $this->advertisement->RecursiveGetAdByCode(self::H5GoodsRecommend);
            $data[self::H5GoodsRecommend] = $H5GoodsRecommend;
        }

        //打call活动
        if (in_array('FreeEvent', $array)) {
            $FreeEvent = $this->freeRepository->getRecommendFree();
            $data['FreeEvent'] = $FreeEvent;
        }

        //页面底部通栏广告:Mini_TopBanner
        if (in_array('Mini_Bottom_Banner', $array)) {
            $Mini_Bottom_Banner = $this->advertisement->getAdByCodeNotChild('Mini_Bottom_Banner');
            foreach ($Mini_Bottom_Banner as $key => $item) {
                if (isset($item['goods_id'])) {
                    $Mini_Bottom_Banner[$key]['type'] = 'goods';
                }
            }
            $data['Mini_Bottom_Banner'] = $Mini_Bottom_Banner;
        }

        // 在线客服数据
        $onlineService = [];
        if (in_array(self::onlineService, $array)) {
            $onlineService = [
                'online_service_phone' => settings('online_service_phone'),
                'online_service_time' => '工作时间:' . settings('online_service_time'),
                'online_service_url' => settings('online_service_url') ? settings('online_service_url') : 'tel:' . settings('online_service_phone')
            ];
            $data['onlineService'] = $onlineService;

        }

        $modulesData = [];
        foreach ($data as $key => $item) {
            if (count($item) > 0) {
                $modulesData[$key] = $item;
            }
        }

        cache(['mini-home-modules-data' => $modulesData], Carbon::now()->addHour(12));

        return $this->api($modulesData);
    }


    /**
     * H5首页模块化数据
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     * @param includes :H5TopShuffling
     */
    public function HomeModulesData()
    {
        $array = [];
        $data = [];
        $includes = request('includes') ? request('includes') : '';
        if (!empty($includes)) {
            $array = explode(',', $includes);
        } else {
            return $this->api($data);
        }

        /*秒杀：秒杀数据放到获取缓存数据之前是为了防止秒杀时间已到首页还有显示*/
        $seckill = 'seckill';
        $data['seckill'] = [];
        if (in_array($seckill, $array)) {
            if ($seckillData = $this->seckillItemRepository->getSeckillItemFirst()) {
                $etime = strtotime($seckillData->seckill->ends_at);
                $seckillData->seckill->ends_at = date('Y/m/d H:i:s', $etime);

                $stime = strtotime($seckillData->seckill->starts_at);
                $seckillData->seckill->starts_at = date('Y/m/d H:i:s', $stime);

                $data['seckill'] = $seckillData;
            }
        }

        $groupon = 'groupon';
        $data['groupon'] = [];
        if (in_array($groupon, $array)) {
            $data['groupon'] = [];
            if ($grouponData = $this->multiGrouponRepository->getGrouponList()->first()) {
                $data['groupon'] = $grouponData;
            }
        }

        if ($modulesData = cache('mobile-home-modules-data')) {
            if ($data['seckill']) {
                $modulesData['seckill'] = $data['seckill'];
            } else {
                unset($modulesData['seckill']);
            }

            if ($data['groupon']) {
                $modulesData['groupon'] = $data['groupon'];
            } else {
                unset($modulesData['groupon']);
            }
            return $this->api($modulesData);
        }

        //顶部轮播推广:H5TopShuffling
        if (in_array(self::TopShuffling, $array)) {
            $H5TopShuffling = $this->advertisement->getAdByCodeNotChild(self::TopShuffling);
            $data[self::TopShuffling] = $H5TopShuffling;
        }

        //轮播图下方小广告
        if (in_array(self::H5TopMiniAd, $array)) {
            $H5TopMiniAd = $this->advertisement->getAdByCodeNotChild(self::H5TopMiniAd);
            $data[self::H5TopMiniAd] = $H5TopMiniAd;
        }

        //tab 切换:H5TabData
        if (in_array(self::H5TabData, $array)) {
            $H5TabData = $this->advertisement->RecursiveGetAdByCode(self::H5TabData);
            foreach ($H5TabData as $key => $item) {
                if (isset($item['items'])) {
                    $adArray = array_where($item['items'], function ($value, $key) {
                        return isset($value['goods_id']);
                    });
                    if (count($adArray)) {
                        $H5GoodsRecommend[$key]['type'] = 'goods';
                    }
                }
            }
            $data[self::H5TabData] = $H5TabData;
        }

        /*套餐*/
        $suit = 'suit';
        if (in_array($suit, $array)) {
            $data['suit'] = [];
            if ($suitData = $this->suitRepository->getFirstSuit()) {
                $data['suit'] = $suitData;
            }
        }

        // 在线客服数据
        $onlineService = [];
        if (in_array(self::onlineService, $array)) {
            $onlineService = [
                'online_service_phone' => settings('online_service_phone'),
                'online_service_time' => '工作时间:' . settings('online_service_time'),
                'online_service_url' => settings('online_service_url') ? settings('online_service_url') : 'tel:' . settings('online_service_phone')
            ];
            $data['onlineService'] = $onlineService;

        }

        //自定义首页
        if (in_array('HomePage', $array)) {
            $data['homePage'] = [];
            if ($page = PageTranslation::where('title', '自定义首页')->first()) {
                $data['homePage']['content'] = $page->body;
                $data['homePage']['cus_css'] = $page->owner->css;
                $data['homePage']['cus_javascript'] = $page->owner->js;
            }
        }

        //最佳销售单品
        $bestSalesGoods = [];
        $goods = [];
        if (in_array(self::bestSalesGoods, $array)) {
            $bestSalesGoods = $this->advertisement->getAdByCodeNotChild(self::bestSalesGoods);
            if (count($bestSalesGoods) > 0) {
                $goodsIds = [];
                $goodsSort = [];
                foreach ($bestSalesGoods as $item) {
                    if (isset($item['goods_id'])) {
                        $goodsIds[] = $item['goods_id'];
                        $goodsSort[$item['goods_id']] = $item['sort'];
                    }
                }
                $bestSalesGoods = $this->goodsRepository->findWhereIn('id', $goodsIds)->toArray();
                foreach ($bestSalesGoods as $k => $item) {
                    $goods[$k] = $item;
                    $goods[$k]['ad_sort'] = $goodsSort[$item['id']];
                    $goods[$k]['img'] = $this->getGoodsImgUrl($item['img']);
                }
                $newgoods = collect($goods)->sortBy('ad_sort')->toArray();
                $bestSalesGoods = array_values($newgoods);

            } else {
                $ad = $this->advertisement->where('code', 'bestSalesGoods')->get();
                if (count($ad) == 0) {
                    $num = settings('top_best_goods_num') ? settings('top_best_goods_num') : 10;
                    $bestSalesGoods = $this->goodsRepository->scopeQuery(function ($query) use ($num) {
                        $query->where(['is_del' => 0, 'is_largess' => 0]);
                        return $query->limit($num);
                    })->orderBy('sale', 'desc')->all();
                }

            }

            $data[self::bestSalesGoods] = $bestSalesGoods;
        }

        //男女士优选
        $H5GoodsRecommend = [];
        if (in_array(self::H5GoodsRecommend, $array)) {
            $H5GoodsRecommend = $this->advertisement->RecursiveGetAdByCode(self::H5GoodsRecommend);
            $data[self::H5GoodsRecommend] = $H5GoodsRecommend;
        }

        //H5首页顶部广告BOTTOM推广
        $H5HeadBottomAd = [];
        if (in_array(self::H5HeadBottomAd, $array)) {
            $H5HeadBottomAd = $this->advertisement->getAdByCodeNotChild(self::H5HeadBottomAd);
            $data[self::H5HeadBottomAd] = $H5HeadBottomAd;
        }

        //积分商品
        if (in_array('integralGoods', $array)) {
            $goodsList = $this->goodsRepository->findWhere(['is_del' => 0, 'is_largess' => 1])->take(9);
            $data['integralGoods'] = $goodsList;
        }


        $modulesData = [];
        foreach ($data as $key => $item) {
            if (count($item) > 0) {
                $modulesData[$key] = $item;
            }
        }

        cache(['mobile-home-modules-data' => $modulesData], Carbon::now()->addHour(12));

        return $this->api($modulesData);


    }


    public function getQRCode()
    {
        $url = request('url');
        if (!empty($url)) {
            $code = QrCode::size(198)->margin(0)->generate($url);
            $pat = "/<svg.*>.*<\/svg>/";
            preg_match_all($pat, $code, $res);
            return $this->api(['code' => $res[0][0]], true, 200, '');
        }
        return $this->api([], false, 400, '');
    }

    /**
     * 小程序新的分类页面接口
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     */
    public function getHomeCategoryList()
    {
        if ($data = cache('mini-program-category-list')) {
            return $this->api($data);
        }

        $listData['CategoryList'] = $this->advertisement->getCategoryAdByCode('CategoryList');
        $listData['CategoryListAd'] = $this->advertisement->getAdByCodeNotChild('CategoryListAd');

        foreach ($listData as $key => $item) {
            if (count($item) > 0) {
                $data[$key] = $item;
            }
        }
        $data['type'] = settings('mini_category_list_type') ? settings('mini_category_list_type') : 'upper_lower';

        cache(['mini-program-category-list' => $data], Carbon::now()->addHour(12));

        return $this->api($data);
    }

    public function openSourceHomePage()
    {
        if ($data = cache('open-source-mini-program-homepage')) {
            return $this->api($data);
        }

        $listData['TopShuffling'] = $this->advertisement->getAdByCodeNotChild('Mini_TopShuffling');
        $listData['categoryList'] = Category::where('status', 1)->where('level', 1)->take(6)->get()->toArray();
        foreach ($listData['categoryList'] as &$category) {
            $goods = DB::table('el_goods')->where('category_group', $category['group_id'])->inRandomOrder()->first();
            $photo = DB::table('el_goods_photo')->where('goods_id', $goods->id)->whereNotNull('url')->first();
            $category['img'] = $photo->url;
            $category['link'] = '';
        }

        $first = array_first($listData['categoryList']);
        $goods_list = DB::table('el_goods')->where('category_group', $first['group_id'])->orderBy('id', 'DESC')->take(6)->get()->toArray();
        $listData['categoryOneGoodsList'] = $goods_list;

        $end = array_last($listData['categoryList']);
        $goods_list = DB::table('el_goods')->where('category_group', $end['group_id'])->orderBy('id', 'ASC')->take(6)->get()->toArray();
        $listData['categoryLastGoodsList'] = $goods_list;

        cache(['open-source-mini-program-homepage' => $listData], Carbon::now()->addHour(12));

        return $this->api($listData);
    }
}
