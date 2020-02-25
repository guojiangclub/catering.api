<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use EasyWeChat;
use GuoJiangClub\Catering\Component\Balance\Model\Balance;
use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Server\Repositories\CouponRepository;
use GuoJiangClub\Catering\Server\Transformers\PointTransformer;
use GuoJiangClub\Catering\Server\Transformers\UserTransformer;

class UserController extends Controller
{
    protected $pointRepository;
    protected $couponRepository;

    public function __construct(PointRepository $pointRepository, CouponRepository $couponRepository)
    {
        $this->pointRepository  = $pointRepository;
        $this->couponRepository = $couponRepository;
    }

    public function bindUserInfo()
    {
        $miniProgram = EasyWeChat::miniProgram('shitang');
        $code        = request('code');
        $result      = $miniProgram->auth->session($code);

        if (!isset($result['session_key'])) {
            return $this->failed('获取 session_key 失败.');
        }

        $sessionKey    = $result['session_key'];
        $encryptedData = request('encryptedData');
        $iv            = request('iv');

        $decryptedData = $miniProgram->encryptor->decryptData($sessionKey, $iv, $encryptedData);
        \Log::info($decryptedData);

        $user            = request()->user();
        $user->nick_name = $decryptedData['nickName'];
        $user->sex       = $decryptedData['gender'] == 1 ? '男' : '女';
        $user->avatar    = $decryptedData['avatarUrl'];
        $user->save();

        return $this->success(['user_info' => $user]);
    }

    public function agreement()
    {
        $user_agreement = settings('user_agreement');

        return $this->success(['content' => $user_agreement]);
    }

    public function userDiscountsInfo()
    {
        $user = request()->user();
        $type = request('type') ? request('type') : 'default';

        $balance = Balance::sumByUser($user->id);
        if (!is_numeric($balance)) {
            $balance = 0;
        } else {
            $balance = (int) $balance;
        }

        $point          = $this->pointRepository->getSumPoint($user->id, $type);
        $pointValid     = $this->pointRepository->getSumPointValid($user->id, $type);
        $pointFrozen    = $this->pointRepository->getSumPointFrozen($user->id, $type);
        $pointOverValid = $this->pointRepository->getSumPointOverValid($user->id, $type);

        $coupons = $this->couponRepository->findActiveByUser($user->id, false);

        return $this->success([
            'balance'         => $balance,
            'point'           => [
                'point'          => $point,
                'pointValid'     => $pointValid,
                'pointFrozen'    => $pointFrozen,
                'pointOverValid' => $pointOverValid,
            ],
            'coupons'         => count($coupons),
            'point_deduction' => settings('point_deduction_money'),
        ]);
    }

    public function me()
    {
        $user = request()->user();
        event('st.user.generate.qrcode', [$user]);

        return $this->response()->item($user, new UserTransformer('me'));
    }

    public function updateBirthday()
    {
        $user     = request()->user();
        $birthday = request('birthday');
        if (!$birthday) {
            return $this->failed('生日日期 不能为空');
        }

        if ($user->birthday) {
            return $this->success();
        }

        $user->birthday = $birthday;
        $user->save();

        event('complete_info', [$user, 'complete_info']);

        return $this->success();
    }

    public function shopInfo()
    {
        return $this->success(['manager_shop_name' => settings('manager_shop_name'), 'manager_shop_address' => settings('manager_shop_address')]);
    }

    public function pointList()
    {
        $type = request('type') ? request('type') : 'default';
        $list = request()->user()->points()->type($type);
        if (request('balance') == 'in') {
            $list = $list->where('value', '>', 0);
        }

        if (request('balance') == 'out') {
            $list = $list->where('value', '<', 0);
        }

        $list = $list->orderBy('created_at', 'desc')->paginate();

        return $this->response()->paginator($list, new PointTransformer());
    }
}