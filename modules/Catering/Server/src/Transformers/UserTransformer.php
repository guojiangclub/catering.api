<?php

namespace GuoJiangClub\Catering\Server\Transformers;

use GuoJiangClub\Catering\Component\User\Models\UserRights;

class UserTransformer extends BaseTransformer
{
    public static $excludeable = [
        'password',
        'confirmation_code',
        'remember_token',
    ];

    protected $availableIncludes = [
        'group'
    ];

    protected $type;

    public function __construct($type = 'me')
    {
        $this->type = $type;
    }

    public function transformData($model)
    {
        if ($this->type == 'me') {
            //   $model->group = $model->group;

            $rights = UserRights::where('status', 1)->orderBy('sort', 'desc')->get();

            $rightsIds = $model->group ? ($model->group->rights_ids ? $model->group->rights_ids : []) : [];
            foreach ($rights as $right) {
                $right->have_right = false;

                if (count($rightsIds) AND in_array($right->id, $rightsIds)) {
                    $right->have_right = true;
                }
            }
            $model->rights = $rights;
        }

        $model->agent_code = $model->confirmation_code;

        $user = $model->toArray();

        $user['user_info_fill'] = true;
        if (!$user['avatar']) {
            $user['avatar'] = settings('member_shop_logo');
            $user['user_info_fill'] = false;
            /*if (settings('enabled_union_pay')) {
                $user['avatar'] = asset('/assets/backend/shitang/img/default.png');
            }*/
        }

        return $user;
    }

    public function includeGroup($model)
    {
        $group = $model->group;
        if (is_null($group)) {
            return null;
        }

        return $this->item($group, new GroupTransformer(), '');
    }

}

class GroupTransformer extends BaseTransformer
{
    public function transformData($model)
    {
        return $model->toArray();
    }
}