<table class="table table-hover table-striped">
    <tbody>
    <tr>
        <th>应付商品金额</th>
        <th>实付商品金额</th>
        <th>应付运费金额</th>
        <th>实付运费金额</th>
        <th>促销优惠金额</th>

        <th>订单总金额</th>
    </tr>
    <tr>
        <td>{{$order->items_total}}</td>
        <td>{{$order->total}}</td>
        <td>{{$order->payable_freight}}</td>
        <td>{{$order->real_freight}}</td>
        <td>{{$order->adjustments_total}}</td>
        <td>{{$order->total}}</td>
    </tr>
    </tbody>
</table>
