<?php

namespace ElementVip\Server\Http\Controllers;

use ElementVip\Component\Card\Builders\Builder;
use ElementVip\Component\Card\Builders\DefaultBuilder;
use ElementVip\Component\Card\Models\Card;
use ElementVip\Member\Backend\Utils\Http;
use ElementVip\Member\Backend\Repository\MemberCardRepository;
use ElementVip\Member\Backend\Repository\WeCardCodesRepository;
use DB;

class MemberCardController extends Controller
{
    protected $memberCard;
    protected $cardCode;
    protected $redirectUrl = '#!/vip/card/register?card_id=';

    public function __construct(MemberCardRepository $memberCardRepository,
                                WeCardCodesRepository $weCardCodesRepository)
    {
        $this->memberCard = $memberCardRepository;
        $this->cardCode = $weCardCodesRepository;
    }

    /**
     * 卡卷二维码接口
     *
     * @return \Dingo\Api\Http\Response
     */
    public function cardQrCode()
    {
        $card = $this->memberCard->with('wxcard')->findWhere(['grade' => 1])->first();
        $qrcode_url = '';
        if (count($card) > 0) {
            $qrcode_url = $card->wxcard->show_qrcode_url;
        }

        return $this->api(['qrcode_url' => $qrcode_url], true, 200, '');
    }

    /**
     * 用户是否领取卡卷 系统是否开启卡卷功能
     *
     * @return \Dingo\Api\Http\Response
     */
    public function checkUserDraw()
    {
        $wechat_card_status = settings('wechat_card_status');
        if (!isset(request()->user()->id)) {

            return $this->api([], false, 500, '您还没有登录');
        }

        $user_id = request()->user()->id;
        $draw = false;
        $is_activate = false;
        $card = $this->memberCard->with('wxcard')->first();
        if (count($card) <= 0 || !isset($card->wxcard->card_id)) {
            return $this->api([], false, 500, '没有可领取的会员卡');
        }

        $card_id = $card->wxcard->card_id;
        $open_id = '';
        $card_draw = DB::table('we_card_codes')->where('card_id', $card_id)->where('user_id', $user_id)->whereNull('deleted_at')->first();
        if ($card_draw) {
            $draw = true;
            $is_activate = $card_draw->activate_status == 1 ? true : false;
            $open_id = $card_draw->openid;
        }

        return $this->api(['card_id' => $card_id, 'open_id' => $open_id, 'is_activate' => $is_activate, 'draw' => $draw, 'wechat_card_status' => $wechat_card_status], true, 200, '');
    }

    /**
     * 跳转激活卡卷页面
     */
    public function activateRedirect()
    {
        $card_id = request('card_id');
        $encrypt_code = request('encrypt_code');
        $openid = request('openid');

//        header('Location: ' . settings('mobile_domain_url') . $this->redirectUrl . $card_id . '&encrypt_code=' . $encrypt_code . '&openid=' . $openid);
        header('Location: ' . settings('wechat_card_activity_url') . '?card_id=' . $card_id . '&encrypt_code=' . $encrypt_code . '&openid=' . $openid);
    }

    public function wxCardActivate()
    {
        if (!isset(request()->user()->id)) {
            return $this->api([], false, 500, '您还没有登录');
        }

        $user = request()->user();

        if (!$card = $user->card) {
            $builder = new DefaultBuilder();
            $card = Card::create([
                'number' => $builder->generateNumber(),
                'name' => request('name'),
                'mobile' => $user->mobile,
                'birthday' => request('birthday'),
                'user_id' => $user->id
            ]);
        } else {
            $card->fill(['name' => request('name'), 'birthday' => request('birthday')]);
            $card->save();
        }

        $card_id = request('card_id');
        $openid = request('openid');
        if (!$card_id || !$openid) {
            return $this->api([], false, 500, '参数错误');
        }

        $status = false;
        $code = 500;
        $card_draw = DB::table('we_card_codes')->where('card_id', $card_id)->where('openid', $openid)->first();
        if (count($card_draw) <= 0) {

            return $this->api([], $status, $code, '您还没有领取卡卷');
        }

        $user_card = DB::table('el_card')->where('user_id', request()->user()->id)->first(['number']);
        $apiActivateUrl = env('APP_URL') . config('wx_card.create_wx_card_activate_url');
        $code = $card_draw->code;
        $activateResponse = Http::request($apiActivateUrl, 'POST', [
            'card_id' => $card_id,
            'code' => $code,
            'membership_number' => $card->number,
        ]);

        if ($activateResponse['status'] && 0 == $activateResponse['data']['errcode'] && 'ok' == $activateResponse['data']['errmsg']) {
            $message = '激活微信卡卷成功';
            $status = true;
            $code = 200;

            $this->cardCode->update(['activate_status' => 1, 'user_id' => $user->id], $card_draw->id);
        } else {
            $message = '激活微信卡卷失败';
        }

        return $this->api([], $status, $code, $message);
    }


}