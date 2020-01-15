{{--<div class="ibox-content">--}}
{{--<div class="alert alert-info">--}}
{{--<h3>订单号<span>&nbsp;&nbsp;&nbsp;&nbsp;{{$order->order_no}}</span></h3>--}}
{{--</div>--}}
{{--</div>--}}

<table class="table table-hover table-striped">
    <tbody>
    <tr>
        <th>订单号</th>
        <th>下单会员</th>
    </tr>
    <tr>
        <td>{{$order->order_no}}</td>
        <td>
            @if($user_info=$order->user)
                <a href="{{route('admin.users.edit',['id' => $order->user->id])}}"
                   target="_blank">
                    {{$user_info->nick_name? $user_info->nick_name: $user_info->mobile}}</a>
            @endif
        </td>

    </tr>


    <tr>
        <th>订单状态</th>
        <th>下单时间</th>
    </tr>
    <tr>
        <td>
            @if($order->status==7)
                退款中
            @elseif($order->status==1)
                待付款
            @elseif($order->status==2)
                待发货
            @elseif($order->status==3)
                配送中待收货 <a
                        href="http://m.kuaidi100.com/index_all.html?type={{$order_deliver['shipping_type']}}&postid={{$order_deliver['shipping_no']}}"
                        target="_blank">[查看物流信息]</a>
            @elseif($order->status==4)
                已收货待评价
            @elseif($order->status==5)
                已完成
            @elseif($order->status==6)
                已取消
            @elseif($order->status==9)
                已删除
            @endif

        </td>
        <td>{{$order->created_at}}</td>

    </tr>

    <tr>
        <th>支付状态</th>
        <th>支付渠道</th>
    </tr>
    <tr>
        <td>
            {{$order->pay_status_text}}
        </td>
        <td>
            {{$order->PayTypeText?$order->PayTypeText:'/'}}
        </td>
    </tr>

    @if($order->pay_status)
        <tr>
            <th>pingxx交易号</th>
            <th>支付平台交易流水号</th>
        </tr>
        <tr>
            @if($order->payments)
                <td>
                    @foreach($order->payments as $val)
                        {{$val->pingxx_no}}<br>
                    @endforeach
                </td>
                <td>
                    @foreach($order->payments as $val)
                        {{$val->channel_no}}<br>
                    @endforeach
                </td>
            @endif
        </tr>
    @endif

    <tr>
        <th>付款时间</th>
        <th>发货时间</th>
    </tr>
    <tr>
        <td>
            {{$order->pay_time?$order->pay_time:'/'}}

        </td>
        <td>
            {{isset($order->send_time)?$order->send_time:'/'}}
        </td>
    </tr>
    <tr>
        <th>发货状态</th>
        <th>物流公司</th>

    </tr>
    <tr>
        <td> {{$order->DistributionText}}</td>
        <td>{{$shipping->method_name or "当前无物流信息"}}</td>
    </tr>

    <tr>
        <th>物流单号</th>

    </tr>
    <tr>
        <td>{{$shipping->tracking or "当前无物流信息"}}</td>
    </tr>
    </tbody>
</table>




