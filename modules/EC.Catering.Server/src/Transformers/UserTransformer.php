<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-08-23
 * Time: 13:25
 */

namespace ElementVip\Server\Transformers;

use DB;
use ElementVip\Component\Address\Models\Address;
use ElementVip\Component\User\Models\Role;

class UserTransformer extends BaseTransformer
{
    public static $excludeable = [
        'password',
        'confirmation_code',
        'remember_token',
    ];

    protected $availableIncludes = [
        'group',
        'size',
    ];

    /*protected $defaultIncludes = [
        'group'
    ];*/

    public function transformData($model)
    {
        $user = array_except($model->toArray(), self::$excludeable);
        $user['user_info_fill'] = 1;
        if (!$user['avatar'] AND !$user['nick_name']) {
            $user['user_info_fill'] = 0;
        }

        $user['is_agent'] = 0;
        $distribution_status = settings('distribution_status');
        $distribution_recruit_status = settings('distribution_recruit_status');
        $user['distribution_status'] = $distribution_status;
        $user['distribution_recruit_status'] = $distribution_recruit_status;
        if ($distribution_status) {
            if (!$user['avatar']) {
                $user['avatar'] = 'https://ibrand-miniprogram.oss-cn-hangzhou.aliyuncs.com/%E5%B0%8F%E7%A8%8B%E5%BA%8F/%E5%A4%B4%E5%83%8F_%E7%94%BB%E6%9D%BF%201.png';
            }

            $check = DB::table('el_agent')->where('user_id', $user['id'])->first();
            if ($check) {
                //状态  1：审核通过 2：审核不通过 3：清退 4:待审核
                $user['is_agent'] = $check->status == 0 ? 4 : $check->status;
            }
        }

        $user['wecaht_group'] = false;
        /*if ($model->hasRole('wechatmanager') AND settings('other_get_gid')) {
            $user['wecaht_group'] = true;
        }*/

	    if (!$user['avatar']) {
		    $user['avatar'] = asset('/assets/backend/market/img/no_head.jpg');
		    if(settings('enabled_union_pay')){
			    $user['avatar'] = asset('/assets/backend/shitang/img/default.png');
		    }
	    }

	    if(!$user['nick_name']){
			$user['nick_name'] = substr($user['mobile'], 0, 4) . '****' . substr($user['mobile'], -3);
	    }

	    if (isset($user['card_no']) && $user['card_no']) {
		    $user['card_no'] = substr($user['card_no'], 0, 4) . '****' . substr($user['card_no'], -3);
	    }

        return $user;
    }

    /**
     * Include Group
     *
     * @param $model
     *
     * @return \League\Fractal\Resource\Item|null
     */
    public function includeGroup($model)
    {
        $group = $model->group;
        if (is_null($group)) {
            return null;
        }

        return $this->item($group, new GroupTransformer(), '');
    }

    /**
     * Include Size
     *
     * @param $model
     *
     * @return \League\Fractal\Resource\Item|null
     */
    public function includeSize($model)
    {
        $size = $model->size;
        if (is_null($size)) {
            return null;
        }

        return $this->item($size, new SizeTransformer(), '');
    }

}

class GroupTransformer extends BaseTransformer
{
    public function transformData($model)
    {
        return $model->toArray();
    }
}

class SizeTransformer extends BaseTransformer
{
    public function transformData($model)
    {
        return $model->toArray();
    }
}