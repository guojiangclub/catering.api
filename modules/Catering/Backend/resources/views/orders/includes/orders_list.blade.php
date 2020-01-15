<div class="hr-line-dashed"></div>
<div class="table-responsive">
    @if(count($orders)>0)
        <table id="order-table" class="table table-hover table-striped">
            <tbody>
            <!--tr-th start-->
            <tr>
                <th>订单编号</th>
                <th>支付方式</th>
                <th>下单会员</th>
                <th>订单状态</th>
                <th>总金额</th>
                <th>优惠金额</th>
                <th>余额抵扣</th>
                <th>实付金额</th>
                <th>下单时间</th>
                <th>付款时间</th>
                <th style="width: 150px;">操作</th>
            </tr>
            <!--tr-th end-->
            @foreach ($orders as $order)
                <tr class="order{{$order->id}}" order-id="{{$order->id}}">
                    <td>{{$order->order_no}}</td>
                    <td>{{$order->payment_text}}</td>
                    <td><a href="{{route('admin.users.edit', $order->user_id)}}" target="_blank">
                            {{$order->order_user_name}}
                        </a>
                    </td>
                    <td>{{$order->StatusText}}</td>
                    <td>{{$order->items_total_yuan}}</td>
                    <td>{{ abs($order->adjustments_total_yuan) }}</td>
                    <td>{{$order->used_balance_amount}}</td>
                    <td>{{$order->paid_amount}}</td>
                    <td>{{$order->created_at}}</td>
                    <td>{{$order->pay_time}}</td>
                    <td style="position: relative;">
                        <a href="{{route('admin.shitang.orders.show',['id'=>$order->id])}}" class="btn btn-xs btn-success" no-pjax><i class="fa fa-eye" data-toggle="tooltip" data-placement="top" title="查看"></i></a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="pull-left">
            &nbsp;&nbsp;共&nbsp;{!! $orders->total() !!} 条记录
        </div>

        <div class="pull-right id='ajaxpag'">
            {!! $orders->appends(request()->except('page'))->render() !!}
        </div>

        <!-- /.box-body -->

    @else
        <div>
            &nbsp;&nbsp;&nbsp;当前无数据
        </div>
    @endif
</div>












