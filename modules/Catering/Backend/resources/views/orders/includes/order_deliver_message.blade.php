<table class="table table-hover table-striped">
    <tbody>
    <tr>
        <th>发货时间</th>
        <th>物流公司</th>
        <th>物流单号</th>
    </tr>
    @if($shipping = $order->shipping)
        <?php
        if (session('admin_check_supplier')) {
            $items = $order->items->filter(function ($value, $key) {
                return in_array($value->supplier_id, session('admin_supplier_id'));
            });
        } else {
            $items = $order->items;
        }

        $shippingIds = $items->pluck('shipping_id')->toArray();
        ?>
        @foreach($shipping as $item)
            @if(in_array($item->id,$shippingIds))
                <tr>
                    <td>{{$item->delivery_time}}</td>
                    <td>{{$item->shippingMethod->name}}</td>
                    <td>{{$item->tracking}}</td>
                </tr>
            @endif
            @if($prevShipping)
                <tr>
                    <td>{{$item->delivery_time}}</td>
                    <td>{{$item->shippingMethod->name}}</td>
                    <td>{{$item->tracking}}</td>
                </tr>
            @endif
        @endforeach
    @endif
    </tbody>
</table>

{{--@if($order->pay_status==1 AND $order->distribution_status==0 AND !session('admin_check_supplier'))--}}
    {{--<a data-toggle="modal" class="btn btn-primary"--}}
       {{--data-target="#modal" data-backdrop="static" data-keyboard="false"--}}
       {{--data-url="{{route('admin.orders.editAddress',['id'=>$order->id])}}"--}}
       {{--href="javascript:;">修改收货地址</a>--}}
{{--@endif--}}






