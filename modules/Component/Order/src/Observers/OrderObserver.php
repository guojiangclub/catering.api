<?php

namespace GuoJiangClub\Catering\Component\Order\Observers;

use GuoJiangClub\Catering\Component\Order\Models\Order;
use DB;

class OrderObserver
{
    /**
     * 监听订单(物理)删除事件。
     *
     * @param  Order  $order
     * @return void
     */
    public function deleted(Order $order)
    {
        try {
            DB::beginTransaction();
            if (!Order::withTrashed()->find($order->id)) {
                $order->items()->withTrashed()->forceDelete();
                $order->adjustments()->withTrashed()->forceDelete();
                $order->payments()->forceDelete();
                $order->comments()->withTrashed()->forceDelete();
                $order->shippings()->withTrashed()->forceDelete();
                $order->invoices()->forceDelete();
                $order->refunds()->withTrashed()->forceDelete();
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            \Log::error($exception->getMessage());
        }

    }
}