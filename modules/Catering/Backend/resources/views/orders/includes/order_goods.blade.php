<table class="table table-hover table-striped">
    <tbody>

    <tr>
        <th>商品名称</th>
        <th>商品单价</th>
        <th>数量</th>
        @if(!$isSupplier)
            <th>优惠金额</th>
            <th>总价</th>
            <th>供应商</th>
        @endif
        <th>参数</th>
        <th>SKU</th>
        <th>发货状态</th>
    </tr>
    @if($supplierIds)
        @foreach($order->items as $item)
            @if(in_array($item->supplier_id, $supplierIds))
                <tr>
                    <td>
                        @if($storeUrl=settings('pc_store_domain_url'))
                            <a href="{{$storeUrl.'/store/detail/'.$item->item_info['detail_id']}}" target="_blank">
                                <img width="50" height="50" src="{{$item->item_info['image']}}" alt="">&nbsp;&nbsp;&nbsp;&nbsp;
                                {{$item->item_name}}
                            </a>
                        @elseif($mobileStoreUrl=settings('mobile_domain_url'))
                            <a href="{{$mobileStoreUrl.'/#!/store/detail/'.$item->item_info['detail_id']}}"
                               target="_blank">
                                <img width="50" height="50" src="{{$item->item_info['image']}}" alt="">&nbsp;&nbsp;&nbsp;&nbsp;
                                {{$item->item_name}}
                            </a>
                        @else
                            <img width="50" height="50" src="{{$item->item_info['image']}}" alt="">&nbsp;&nbsp;&nbsp;
                            &nbsp;
                            {{$item->item_name}}
                        @endif
                    </td>
                    <td>{{$item->unit_price}}</td>
                    <td>{{$item->quantity}}</td>
                    @if(!$isSupplier)
                        <td>{{$item->adjustments_total}}</td>
                        <td>{{$item->total}}</td>
                        <td>{{$item->supplier->name}}</td>
                    @endif
                    <td>{{!empty($item->item_info['specs_text'])?$item->item_info['specs_text']:''}}</td>
                    <td>
                        {{$item->getModel() ? $item->getModel()->sku : ''}}
                    </td>
                    <td>
                        @if($item->is_send==1)
                            已发货<br>
                            发货单号：{{$item->shipping->tracking}}
                        @elseif($item->is_send==0 AND $order->distribution_status==1 AND $item->status==1)
                            已发货<br>
                            发货单号：{{$order->shipping->first()->tracking}}
                        @else
                            未发货
                        @endif
                    </td>
                </tr>
            @endif
        @endforeach
    @else
        @foreach($order->items as $item)
            @if(in_array($item->supplier_id, session('admin_supplier_id')) ||  !$isSupplier)
                <tr>
                    <td>
                        @if($storeUrl=settings('pc_store_domain_url'))
                            <a href="{{$storeUrl.'/store/detail/'.$item->item_info['detail_id']}}" target="_blank">
                                <img width="50" height="50" src="{{$item->item_info['image']}}" alt="">&nbsp;&nbsp;&nbsp;&nbsp;
                                {{$item->item_name}}
                            </a>
                        @elseif($mobileStoreUrl=settings('mobile_domain_url'))
                            <a href="{{$mobileStoreUrl.'/#!/store/detail/'.$item->item_info['detail_id']}}"
                               target="_blank">
                                <img width="50" height="50" src="{{$item->item_info['image']}}" alt="">&nbsp;&nbsp;&nbsp;&nbsp;
                                {{$item->item_name}}
                            </a>
                        @else
                            <img width="50" height="50" src="{{$item->item_info['image']}}" alt="">&nbsp;&nbsp;&nbsp;
                            &nbsp;
                            {{$item->item_name}}
                        @endif
                    </td>
                    <td>{{$item->unit_price}}</td>
                    <td>{{$item->quantity}}</td>
                    @if(!$isSupplier)
                        <td>{{$item->adjustments_total}}</td>
                        <td>{{$item->total}}</td>
                        <td>{{$item->supplier->name}}</td>
                    @endif
                    <td>{{!empty($item->item_info['specs_text'])?$item->item_info['specs_text']:''}}</td>
                    <td>
                        {{$item->getModel() ? $item->getModel()->sku : ''}}
                    </td>
                    <td>
                        @if($item->is_send==1)
                            已发货<br>
                            发货单号：{{$item->shipping->tracking}}
                        @elseif($item->is_send==0 AND $order->distribution_status==1 AND $item->status==1)
                            已发货<br>
                            发货单号：{{$order->shipping->first()->tracking}}
                        @else
                            未发货
                        @endif
                    </td>
                </tr>
            @endif
        @endforeach
    @endif

    </tbody>
</table>