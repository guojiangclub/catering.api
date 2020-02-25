<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/6
 * Time: 16:26
 */

namespace ElementVip\Server\Http\Controllers;


use GuoJiangClub\Catering\Component\Order\Models\OrderItem;
use ElementVip\Component\Order\Repositories\OrderRepository;
use ElementVip\Component\Refund\Models\Refund;
use ElementVip\Component\Refund\Models\RefundLog;
use ElementVip\Component\Refund\Models\RefundShipping;
use ElementVip\Component\Refund\Repositories\RefundRepository;
use ElementVip\Server\Transformers\OrderItemTransformer;
use ElementVip\Server\Transformers\RefundTransformer;
use iBrand\Component\MultiGroupon\Models\MultiGrouponUsers;
use Illuminate\Events\Dispatcher;

class RefundController extends Controller
{
    private $orderRepository;
    private $event;
    private $refundRepository;

    public function __construct(OrderRepository $orderRepository, Dispatcher $event
        , RefundRepository $refundRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->event = $event;
        $this->refundRepository = $refundRepository;
    }

    public function index()
    {
        $andConditions ['channel'] = 'ec';
        $andConditions ['user_id'] = request()->user()->id;
        if (request('status')) {
            $andConditions['status'] = request('status');
        }

        $limit = request('limit') ? request('limit') : 15;

        $orConditions = [];

        if ($criteria = request('criteria')) {
            $andConditions ['refund_no'] = ['refund_no', 'like', '%' . $criteria . '%'];
            $orConditions['order_no'] = ['order_no', 'like', '%' . $criteria . '%'];
            $orConditions['item_name'] = ['item_name', 'like', '%' . $criteria . '%'];
        }

        return $this->api($this->refundRepository->getRefundsByCriteria($andConditions, $orConditions, $limit));
        /*return $this->api(Refund::where('user_id', request()->user()->id)->paginate());*/
    }

    public function show($refund_no)
    {
        if (!$refund = Refund::where('refund_no', $refund_no)->with('logs')->with('shipping')->with('orderItem')->get()->first()) {
            return $this->api(null, false, 400, '不存在该售后申请');
        }

        $refund->logs->each(function ($item, $key) use ($refund) {
            $item->getRefundMsg($refund);
        });
        /*return $this->api($refund);*/
        return $this->response()->item($refund, new RefundTransformer());
    }

    public function apply()
    {
        $user = request()->user();

        $order_no = request('order_no');
        if (!$order_no || !$order = $this->orderRepository->getOrderByNo($order_no)) {
            return $this->response()->errorBadRequest('订单不存在');
        }

        if (!$orderItem = OrderItem::find(request('order_item_id'))) {
            return $this->response()->errorForbidden('You need to pass into specific order item id');
        }

        if ($user->cant('refund', [$order, $orderItem])) {
            return $this->response()->errorForbidden('You have no right to refund this order.');
        }

        if (!request('quantity') OR $orderItem->quantity < request('quantity')) {
            return $this->response()->errorForbidden('提交数量错误');
        }

        /*if (Refund::where('order_item_id', request('order_item_id'))->first()) {
            return $this->response()->errorForbidden('您已提交过售后');
        }*/

        if ($grouponUser = MultiGrouponUsers::where('order_id', $order->id)->where('user_id', $user->id)->first() AND
            $grouponUser->status == 1 AND
            ($grouponUser->grouponItem->status == 0)
        ) {
            return $this->api([], false, 400, '拼团活动未结束不可申请售后');
            /*return $this->response()->errorForbidden('拼团未完成不可申请售后');*/
        }

        //如果订单未发货，只有仅退款申请
        if (($orderItem->is_send == 0 AND $order->distribution_status != 1)
            AND request('type') == 4
        ) {
            return $this->response()->errorForbidden('售后申请类型错误');
        }

        //如果已发货订单已经申请过一次仅退款售后，二次售后不能再申请仅退款
        $refund = Refund::where('order_item_id', request('order_item_id'))->first();
        if ($refund AND $refund->type == 1 AND request('type') == 1 AND $orderItem->is_send == 1) {
            return $this->response()->errorForbidden('售后申请类型错误');
        }

        $input = array_filter(request()->only('order_item_id', 'images', 'type', 'quantity',
            'content', 'reason', 'amount'));

        if ($amount = intval(round(floatval(request('amount')) * 100))) {
            if ($orderItem->quantity <> $input['quantity']) {
                /*$theory = number_format(($orderItem->total / 100 / $orderItem->quantity) * $input['quantity'], 2, '.', '');
                $temp = number_format($amount, 2, '.', '');*/
                $theory = ($orderItem->total / $orderItem->quantity) * $input['quantity'];
                $temp = $amount;

                if ($theory < $temp) {
                    return $this->response()->errorForbidden('提交退款金额错误');
                }
            } else {
                if ($orderItem->total < $amount) {
                    return $this->response()->errorForbidden('提交退款金额错误');
                }
            }
            $input['amount'] = $amount;

        } else {
            $input['amount'] = ($orderItem->total / $orderItem->quantity) * $input['quantity'];
        }

        //1.生成创建需要的参数
        $input = array_merge(['user_id' => $user->id, 'order_id' => $order->id], $input);

        $refund = new Refund($input);
        $refund->save();

        /*$note = $input['type'] == 1 ? '用户提交退货申请' : '用户提交换货申请';*/

        $note = '';
        if ($input['type'] == 1) {
            $note = '用户提交退款申请';
        } elseif ($input['type'] == 4) {
            $note = '用户提交退货退款申请';
        }

        RefundLog::create(['refund_id' => $refund->id, 'user_id' => $user->id, 'action' => 'create', 'note' => $note]);
        if ($order->status == 2) {
            $this->event->fire('order.erp.refund', [$refund]);
        } else {
            $this->event->fire('order.erp.trade', [$refund]);
        }

        $this->event->fire('order.refund.apply');
        $this->event->fire('goods.sales.notice', [$refund, $note]);

        return $this->api($refund);
    }

    public function returnRefund()
    {
        $refund_no = request('refund_no');
        if (!$refund_no || !$refund = Refund::where('user_id', request()->user()->id)->where('refund_no', $refund_no)->get()->first()) {
            return $this->response()->errorBadRequest('退货申请单不存在');
        }

        //TODO: 待完善验证。

        RefundLog::create(['refund_id' => $refund->id, 'user_id' => request()->user()->id
            , 'action' => 'express', 'note' => '用户已退货', 'remark' => '物流公司：' . request('shipping_name')
                . ', 运单号' . request('shipping_tracking')]);

        $refund->status = Refund::STATUS_USER_HAS_RETURNED;
        $refund->save();

        RefundShipping::create(['refund_id' => $refund->id,
            'code' => request('shipping_code'),
            'shipping_name' => request('shipping_name'),
            'shipping_tracking' => request('shipping_tracking')]);

        return $this->api($refund);

    }

    public function close()
    {
        $refund_no = request('refund_no');
        if (!$refund_no || !$refund = Refund::where('user_id', request()->user()->id)->where('refund_no', $refund_no)->get()->first()) {
            return $this->response()->errorBadRequest('退货申请单不存在');
        }

        if (($refund->type == 1 AND $refund->status != 0)
            OR
            ($refund->type == 4 AND $refund->status != 0 AND $refund->status != 5)
        ) {
            return $this->response()->errorBadRequest('无权限关闭');
        }

        RefundLog::create(['refund_id' => $refund->id, 'user_id' => request()->user()->id
            , 'action' => 'close', 'note' => '用户取消申请单', 'remark' => request('remark')]);

        $refund->status = Refund::STATUS_CANCEL;
        $refund->save();

        return $this->api($refund);
    }

    public function baseInfo()
    {
        $item_id = request('order_item_id');
        if (!$item_id OR !$orderItem = OrderItem::find($item_id)) {
            return $this->response()->errorBadRequest('订单商品不存在');
        }

        $type = [];
        $refund = $orderItem->refunds;
        if ($refund->count() == 1) {
            if ($orderItem->is_send == 1 OR
                ($orderItem->is_send == 0 AND $orderItem->order->distribution_status == 1)
            ) {
                //已发货订单第二次售后只有退货退款类型
                $type = [
                    ['key' => 4, 'value' => '退货退款']
                ];
            } elseif ($orderItem->is_send == 0 AND $orderItem->order->distribution_status != 1) {
                $type = [
                    ['key' => 1, 'value' => '仅退款']
                ];
            }
        } elseif ($refund->count() == 0) {
            if ($orderItem->is_send == 1 OR
                ($orderItem->order->distribution_status == 1 AND $orderItem->is_send == 0)
            ) {
                //已发货的订单有2种售后类型
                $type = [
                    ['key' => 1, 'value' => '仅退款'],
                    ['key' => 4, 'value' => '退货退款']
                ];
            } elseif ($orderItem->is_send == 0 AND $orderItem->order->distribution_status != 1) {
                //未发货的订单只有仅退款类型
                $type = [
                    ['key' => 1, 'value' => '仅退款']
                ];
            }
        }

        return $this->response()->item($orderItem, new OrderItemTransformer())->setMeta(['type' => $type]);
    }

    /**
     * 获取所有售后申请
     * @return \Dingo\Api\Http\Response
     * 与index方法的返回方式不同
     */
    public function all()
    {
        $andConditions ['user_id'] = request()->user()->id;
        $andConditions ['channel'] = 'ec';
        if (request('status')) {
            $andConditions['status'] = request('status');
        }

        $limit = request('limit') ? request('limit') : 15;

        $orConditions = [];

        if ($criteria = request('criteria')) {
            $andConditions ['refund_no'] = ['refund_no', 'like', '%' . $criteria . '%'];
            $orConditions['order_no'] = ['order_no', 'like', '%' . $criteria . '%'];
            $orConditions['item_name'] = ['item_name', 'like', '%' . $criteria . '%'];
        }

        $refunds = $this->refundRepository->getRefundsByCriteria($andConditions, $orConditions, $limit);

        return $this->response()->paginator($refunds, new RefundTransformer());

    }
}