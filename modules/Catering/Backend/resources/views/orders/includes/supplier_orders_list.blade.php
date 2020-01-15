<div class="hr-line-dashed"></div>
<div class="table-responsive">
    @if(count($orders)>0)
        <table id="order-table" class="table table-hover table-striped">
            <tbody>
            <!--tr-th start-->
            <tr>
                <th><input type="checkbox" class="check-all"></th>
                <th>订单编号</th>
                <th>订单类型</th>
                <th>下单会员</th>
                <th>收货人</th>
                <th>订单状态</th>
                <th>售后状态</th>
                <th>商品数量</th>
                {{--<th>总金额</th>--}}
                {{--<th>应付金额</th>--}}
                <th>下单时间</th>

                <th style="width: 150px;">操作</th>
            </tr>
            <!--tr-th end-->
            @foreach ($orders as $order)
                <tr class="order{{$order->id}}" order-id="{{$order->id}}">
                    <td><input class="checkbox" type="checkbox" value="{{$order->id}}" name="ids[]"></td>
                    <td>{{$order->order_no}}</td>
                    <td>{{$order->order_type}}</td>
                    <td> {{$order->order_user_name}}</a>
                    </td>
                    <td>
                        {{!empty($order->accept_name)?$order->accept_name:'/'}}&nbsp;&nbsp;&nbsp;&nbsp;<i
                                class="fa fa-mobile"></i>&nbsp;{{!empty($order->mobile)?$order->mobile:'/'}}
                    </td>
                    <?php
                    $items = $order->items->filter(function ($value, $key) use ($supplierID) {
                        return in_array($value->supplier_id, $supplierID);
                    });
                    ?>

                    <td>{{$order->status_text}}</td>
                    <td>{{$order->refund_status}}</td>
                    <td>{{$items->sum('quantity')}}</td>
                    {{--<td>{{$items->sum('units_total')/100}}</td>--}}
                    {{--<td>{{$items->sum('total')}}</td>--}}
                    <td>{{$order->created_at}}</td>
                    <td style="position: relative;">
                        <a href="{{route('admin.orders.show',['id'=>$order->id,'supplier'=>request('supplier')])}}"
                           class="btn btn-xs btn-success">
                            <i class="fa fa-eye" data-toggle="tooltip" data-placement="top" title="查看"></i></a>
                        @if($order->status==2 AND $order->groupon_status)
                            <a class="btn btn-xs btn-success" id="chapter-create-btn" data-toggle="modal"
                               data-target="#modal" data-backdrop="static" data-keyboard="false"
                               data-url="{{route('admin.orders.deliver',['id'=>$order->id,'redirect_url'=>urlencode(Request::getRequestUri())])}}">
                                <i class="fa fa-send" data-toggle="tooltip" data-placement="top" title="发货"></i></a>

                        @endif
                        {{--@if($order->status==3)--}}
                            {{--<a class="btn btn-xs btn-success" id="chapter-create-btn" data-toggle="modal"--}}
                               {{--data-target="#modal" data-backdrop="static" data-keyboard="false"--}}
                               {{--data-url="{{route('admin.orders.deliver.edit',['id'=>$order->id])}}">--}}
                                {{--<i class="fa fa-pencil" data-toggle="tooltip" data-placement="top"--}}
                                   {{--title="修改快递信息"></i></a>--}}
                        {{--@endif--}}


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












