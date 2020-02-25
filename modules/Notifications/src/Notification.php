<?php

namespace GuoJiangClub\EC\Catering\Notifications;

use GuoJiangClub\Catering\Component\User\Models\UserBind;
use GuoJiangClub\Catering\Component\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification as BaseNotification;

class Notification extends BaseNotification
{
    use Queueable;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    protected function checkOpenId($user)
    {
        $userBind = UserBind::byAppID($user->id, 'wechat', app('system_setting')->getSetting('wechat_app_id'))->first();
        if (!$userBind OR empty($userBind->open_id)) {
            return false;
        }

        return true;
    }

    protected function getOpenId($user)
    {
        return UserBind::byAppID($user->id, 'wechat', app('system_setting')->getSetting('wechat_app_id'))->first()->open_id;
    }

    protected function checkAdminOpenId($admin)
    {
        $user = User::where('mobile', $admin->mobile)->first();
        if ($user) {
            $userBind = UserBind::byAppID($user->id, 'wechat', app('system_setting')->getSetting('wechat_app_id'))->first();
            if ($userBind && $userBind->open_id) {
                return $user;
            }
        }

        return false;
    }
}
