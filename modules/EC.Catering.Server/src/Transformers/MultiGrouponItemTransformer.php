<?php
namespace ElementVip\Server\Transformers;

use Carbon\Carbon;

class MultiGrouponItemTransformer extends BaseTransformer
{
    protected $type;

    public function __construct($type = 'detail')
    {
        $this->type = $type;
    }

    public static $excludeable = [
        'deleted_at'
    ];

    public function transformData($model)
    {
        /*团长信息*/
        $leader = $model->users()->where('status', 1)->where('is_leader', 1)->first();
        $model->leader = $leader;

        /*参团用户信息*/
        $model->users = $model->users()->where('status', 1)->get();

        /*是否已参与该子团*/
        $model->has_joined = 0;
        if ($user = auth('api')->user() AND $grouponUser = $model->users()->where('user_id', $user->id)->first()) {
            $model->has_joined = 1;
            $model->multi_groupon_order_no = $grouponUser->order->pay_status == 1 ? '' : $grouponUser->order->order_no;

            if ($this->type == 'show') {
                $model->order_no = $grouponUser->order->order_no;
            }

        }

        /*差几人成团*/
        $model->gap_number = $model->getGapNumber();

        /*是否已过期*/
        $overdue_status = 0; //未过期
        if ($model->ends_at < Carbon::now()) {
            $overdue_status = 1;
        }
        $model->overdue_status = $overdue_status;

        $items = $model->toArray();
        return $items;
    }
}