
<table class="table table-hover table-striped">
    <tbody>

    <tr>
        <th>商品名称</th>
        <th>商品单价</th>
        <th>数量</th>
        <th>优惠金额</th>
        <th>总价</th>
        <th>参数</th>
        <th>SKU</th>
    </tr>
        @foreach($order->items as $item)
            <tr>
                <td>
                    @if($storeUrl=settings('pc_store_domain_url'))
                        <a href="{{$storeUrl.'/store/detail/'.$item->item_info['detail_id']}}" target="_blank">
                            <img width="50" height="50"  src="{{$item->item_info['image']}}" alt="">&nbsp;&nbsp;&nbsp;&nbsp;
                            {{$item->item_name}}
                        </a>
                    @elseif($mobileStoreUrl=settings('mobile_domain_url'))
                        <a href="{{$mobileStoreUrl.'/#!/store/detail/'.$item->item_info['detail_id']}}"
                           target="_blank">
                            <img width="50" height="50"  src="{{$item->item_info['image']}}" alt="">&nbsp;&nbsp;&nbsp;&nbsp;
                            {{$item->item_name}}
                        </a>
                    @else
                        <img width="50" height="50"  src="{{$item->item_info['image']}}" alt="">&nbsp;&nbsp;&nbsp;&nbsp;
                        {{$item->item_name}}
                    @endif
                </td>
                <td>{{$item->unit_price}}</td>
                <td>{{$item->quantity}}</td>
                <td>{{$item->adjustments_total}}</td>
                <td>{{$item->total}}</td>
                <td>{{!empty($item->item_info['specs_text'])?$item->item_info['specs_text']:''}}</td>
                <td>
                    {{$item->getModel() ? $item->getModel()->sku : ''}}
                </td>
            </tr>
        @endforeach

    </tbody>
</table>