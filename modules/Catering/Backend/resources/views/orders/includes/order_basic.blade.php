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
            {!! $order->status_text !!}
            {{--<a--}}
                    {{--href="http://m.kuaidi100.com/index_all.html?type={{$order_deliver['shipping_type']}}&postid={{$order_deliver['shipping_no']}}"--}}
                    {{--target="_blank">[查看物流信息]</a>--}}

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

    @if($order->pay_status AND !$isSupplier)
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
    </tr>
    <tr>
        <td>
            {{$order->pay_time?$order->pay_time:'/'}}
        </td>
    </tr>

    </tbody>
</table>




