<div class="hr-line-dashed"></div>
<div class="table-responsive">
    @if(count($orders)>0)
        <table id="order-table" class="table table-hover table-striped">
            <tbody>
            <tr>
                <th style="text-align: center;"><input type="checkbox" class="check-all"></th>
                <th style="text-align: center;">下单会员</th>
                <th>
                    <table style="width: 100%">
                        <tr>
                            <th style="text-align: center;">订单编号</th>
                            <th style="width: 10%;text-align: center;">订单类型</th>
                            <th style="width: 13%;text-align: center;">收货人</th>
                            <th style="width: 10%;text-align: center;">订单状态</th>
                            {{--<th style="width: 10%;text-align: center;">售后状态</th>--}}
                            <th style="width: 7%;text-align: center;">商品数量</th>
                            {{--<th style="width: 8%;text-align: center;">总金额</th>--}}
                            {{--<th style="width: 7%;text-align: center;">应付金额</th>--}}
                            <th style="width: 15%;text-align: center;">下单时间</th>
                            <th style="width: 15%;text-align: center;">操作</th>
                        </tr>
                    </table>
                </th>
                <th>合并发货</th>
            </tr>
            @foreach ($orders as $key=>$order)
                <tr class="user_{{$key}}" data-uid="{{$key}}">
                    <td style="text-align: center;vertical-align: middle;"><input class="checkbox" type="checkbox"
                                                                                  value="{{$key}}" name="userIds[]">
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <a href="{{route('admin.users.edit', $key)}}">
                            @if($user=\GuoJiangClub\Catering\Component\User\Models\User::find($key))
                                @if($user->name)
                                    {{$user->name}}
                                @elseif($user->mobile)
                                    {{$user->mobile}}
                                @elseif($user->nick_name)
                                    {{$user->nick_name}}
                                @else
                                    /
                                @endif
                            @else
                                /
                            @endif
                        </a>
                    </td>
                    <td>
                        <table class="table table-hover table-striped">
                            <tbody>
                            @foreach($order as $item)
                                <tr>
                                    <td style="text-align: center;">{{$item->order_no}}
                                        <input type="hidden" name="order_id[{{$key}}][]" value="{{$item->id}}">
                                    </td>
                                    <td style="width: 10%;text-align: center;">{{$item->order_type}}</td>
                                    <td style="width: 13%;text-align: center;">{{!empty($item->accept_name)?$item->accept_name:'/'}}
                                        &nbsp;&nbsp;&nbsp;&nbsp;<i
                                                class="fa fa-mobile"></i>&nbsp;{{!empty($item->mobile)?$item->mobile:'/'}}
                                    </td>
                                    <?php
                                    $order_items = $item->items->filter(function ($value, $key) use ($supplierID) {
                                        return in_array($value->supplier_id, $supplierID);
                                    });
                                    ?>
                                    <td style="width: 10%;text-align: center;">{{$item->status_text}}</td>
{{--                                    <td style="width: 10%;text-align: center;">{{$item->refund_status}}</td>--}}
                                    <td style="width: 7%;text-align: center;">{{$order_items->sum('quantity')}}</td>
{{--                                    <td style="width: 8%;text-align: center;">{{$order_items->sum('units_total')/100}}</td>--}}
                                    {{--<td style="width: 7%;text-align: center;">{{$order_items->sum('total')}}</td>--}}
                                    <td style="width: 15%;text-align: center;">{{$item->created_at}}</td>
                                    <td style="position: relative;width: 15%;text-align: center;">
                                        <a href="{{route('admin.orders.show',['id'=>$item->id,'supplier'=>request('supplier')])}}"
                                           class="btn btn-xs btn-success"><i class="fa fa-eye" data-toggle="tooltip"
                                                                             data-placement="top" title="查看"></i></a>
                                        @if($item->status==2 AND $item->groupon_status)
                                            <a class="btn btn-xs btn-success" id="chapter-create-btn"
                                               data-toggle="modal" data-target="#modal" data-backdrop="static"
                                               data-keyboard="false"
                                               data-url="{{route('admin.orders.deliver',['id'=>$item->id,'redirect_url'=>urlencode(Request::getRequestUri())])}}"><i
                                                        class="fa fa-send" data-toggle="tooltip" data-placement="top"
                                                        title="发货"></i></a>

                                        @endif
                                        @if($item->status==2 AND $item->invoiceOrder)
                                            <a class="btn btn-xs btn-success" id="chapter-create-btn"
                                               data-toggle="modal" data-target="#modal_invoice" data-backdrop="static"
                                               data-keyboard="false"
                                               data-url="{{route('admin.orders.invoice.edit',['id'=>$item->invoiceOrder->id])}}"><i
                                                        class="fa fa-file-zip-o" data-toggle="tooltip"
                                                        data-placement="top" title="开具发票"></i></a>
                                        @endif
                                        <a href="javascript:" class="btn btn-xs btn-danger close-order"
                                           data-url="{{route('admin.orders.close',['id'=>$item->id])}}"><i
                                                    class="fa fa-times" data-toggle="tooltip" data-placement="top"
                                                    title="" data-original-title="关闭订单"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        @if($item->status==2)
                            <a class="btn btn-xs btn-warning"
                               data-toggle="multiple_modal"
                               data-uid="{{$key}}" data-target="#modal"
                               data-backdrop="static"
                               data-keyboard="false"
                               data-url="{{route('admin.orders.multiple.deliver',['redirect_url'=>urlencode(Request::getRequestUri())])}}"><i
                                        class="fa fa-mail-forward" data-toggle="tooltip" data-placement="top" title="合并发货"></i></a>
                        @endif

                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="pull-right id='ajaxpag'">
            {!! $users->appends(request()->except('page'))->render() !!}
        </div>
    @else
        <div>
            &nbsp;&nbsp;&nbsp;当前无数据
        </div>
    @endif
</div>