<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

class SystemSettingController extends Controller
{
    public function index()
    {
        return $this->success(settings(request('type')));
    }

    public function site()
    {
        $menu_list = [];
        $menu      = settings('menu_list');
        if (isset($menu) && is_array($menu)) {

            $array = array_values(array_sort($menu, function ($value) {
                return $value['sort'];
            }));

            foreach ($array as $value) {
                if ($value['is_enabled'] == 1) {
                    $menu_list[] = $value;
                }
            }
        }
        $data = [
            'name'                => settings('store_name'),
            'logo'                => settings('store_logo'),
            'store_statistics_js' => settings('store_statistics_js'),
            'menu_list'           => $menu_list,
        ];

        return $this->success($data);
    }

    public function init()
    {
        $goodsRegistrationChannel[] = ['key' => 'osprey', 'value' => 'Osprey中国官网'];
        $goodsRegistrationChannel[] = ['key' => 'osprey_tmall', 'value' => 'Osprey天猫旗舰店'];
        $goodsRegistrationChannel[] = ['key' => 'osprey_amazon', 'value' => '亚马逊（中国）'];
        $goodsRegistrationChannel[] = ['key' => 'osprey_taobao', 'value' => '淘宝'];
        $goodsRegistrationChannel[] = ['key' => 'other', 'value' => '其他电商平台'];
        $goodsRegistrationChannel[] = ['key' => 'offline_store', 'value' => '线下实体门店'];

        $menu_list = [];
        $menu      = settings('menu_list');
        if (isset($menu) && is_array($menu)) {

            $array = array_values(array_sort($menu, function ($value) {
                return $value['sort'];
            }));

            foreach ($array as $value) {
                if ($value['is_enabled'] == 1) {
                    $menu_list[] = $value;
                }
            }
        }
        $siteData = [
            'name'                => settings('store_name'),
            'logo'                => settings('store_logo'),
            'store_statistics_js' => settings('store_statistics_js'),
            'menu_list'           => $menu_list,
        ];

        $data = [
            'analytics'                  => [
                'cookieDomain'            => settings('analytics_domain'),
                'accounts'                => [
                    'piwik'     => settings('analytics_piwik_id')
                    , 'google'  => settings('analytics_google_id')
                    , 'baidu'   => settings('analytics_baidu_id')
                    , 'tencent' => settings('analytics_tencent_id'),
                ]
                , 'baidu_analytics'       => settings('analytics_baidu_id')
                , 'google_analytics'      => settings('analytics_google_ua_id')
                , 'qq_analytics'          => settings('analytics_qq_id') ? settings('analytics_qq_id') : '500623850'
                , 'adobe_dtm_header_code' => settings('adobe_dtm_header_code')
                , 'adobe_dtm_footer_code' => settings('adobe_dtm_footer_code')
                , 'tencent_analytics'     => settings('analytics_tencent_id'),
            ],
            'point_rule_url'             => settings('point_rule_h5_url'),
            'goods_registration_channel' => $goodsRegistrationChannel
            , 'h5_share'                 => [
                'title'    => settings('h5-home-page-share-title')
                , 'desc'   => settings('h5-home-page-share-desc')

                , 'imgUrl' => settings('h5-home-page-share-logo'),
            ],

            'distribution_valid_time'    => settings('distribution_valid_time') ? settings('distribution_valid_time') : 10080,
            'online_service_url'         => settings('online_service_url') ? settings('online_service_url') : 'tel:' . settings('online_service_phone'),
            'online_service_data'        => [
                'online_service_mini_status' => settings('online_service_mini_status') ? settings('online_service_mini_status') : 0,
                'online_service_status'      => settings('online_service_status') ? settings('online_service_status') : 0,
                'online_service_type'        => settings('online_service_type'),
                'online_service_self'        => settings('online_service_self'),
                'online_service_url'         => settings('online_service_url'),
            ],
            'site'                       => $siteData,
            'get_gid'                    => settings('other_get_gid') ? 1 : 0,  //获取微信群ID
            'pick_self'                  => settings('other_pick_self') ? 1 : 0,//门店自提
            'need_sign_in'               => settings('other_need_sign_in') ? 1 : 0, // 会员中心是否开启打卡
            'built_sns_status'           => settings('other_built_sns') ? 1 : 0,//是否开启圈子功能
            'built_sns_title'            => settings('other_built_sns_title'), //圈子标题
            'point_mall_status'          => settings('other_point_mall') ? 1 : 0, //是否启用积分商城
            'other_technical_support'    => settings('other_technical_support') ? 1 : 0, //页面是否显示技术支持
            'shop_show_logo'             => settings('member_shop_logo'),
            'shop_name'                  => settings('shop_name'),
            'mini_program_bg_img'        => settings('mini_program_bg_img'),
            'personal_info_img'          => settings('personal_info_img'),
            'login_page_bg'              => settings('login_page_bg'),
            'miniprogram_customer_title' => settings('miniprogram_customer_title'),

        ];

        return $this->success($data);
    }

    public function analytics()
    {
        $data = [
            'cookieDomain' => settings('analytics_domain'),
            'accounts'     => ['piwik' => settings('analytics_piwik_id'), 'google' => settings('analytics_google_id')],
        ];

        return $this->success($data);
    }

    public function testRedirect()
    {
        $path = request('url');
        $url  = settings('mobile_domain_url') . '#' . $path;

        return redirect($url);
    }

    /**
     * 获取页面底部技术支持内容
     */
    public function ibrand()
    {
        $data = ['content' => file_get_contents('https://iyoyo.oss-cn-hangzhou.aliyuncs.com/contact.html')];

        return $this->success($data);
    }
}