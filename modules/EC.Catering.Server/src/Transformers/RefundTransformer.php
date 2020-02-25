<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/3/15
 * Time: 15:37
 */

namespace ElementVip\Server\Transformers;


class RefundTransformer extends BaseTransformer
{
    public function transformData($model)
    {
        $log = $model->logs->sortByDesc('id')->first();
        $remark = $log->remark;
        switch ($model->status) {
            case 5:
                $model->tips_title = '请退货';
                $model->tips = settings('refund_note') . '<br>' . $remark;
                break;
            case 4:
                $model->tips_title = '已关闭';
                $model->tips = $remark ? $remark : '';
                break;
            case 3:
                $model->tips_title = $log->note;
                $model->tips = $remark ? $remark : '';
                break;
            case 2:
                $model->tips_title = '拒绝申请';
                $model->tips = $remark ? $remark : '';
                break;
            case 6:
                $model->tips_title = '等待商家收货';
                $model->tips = $remark ? $remark : '';
                break;
            case 8:
                $model->tips_title = '等待商家退款';
                $model->tips = $remark ? $remark : '';
                break;
            default:
                $model->tips_title = '';
                $model->tips = '';
        }
        $refund = $model->toArray();
        return $refund;
    }

}