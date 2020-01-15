<div class="hr-line-dashed"></div>
<div class="table-responsive">
    @if(count($orders)>0)
        <table class="table table-hover table-striped">
            <tbody>
            <!--tr-th start-->
            <tr>
                <th>Issue_Store_ID</th>
                <th>VIP_Code_in_TTPOS</th>
                <th>Issue_Store_Name</th>
                <th>Transaction_ID</th>
                <th>SKU_Ops_Sales_Amount</th>
                <th>Transaction_Date</th>
                <th>Point_Earning</th>
                <th>Point_Redeemed</th>

            </tr>
            <!--tr-th end-->
            @foreach ($orders as $order)
                <tr>
                    <td>{{$order->Issue_Store_ID}}</td>
                    <td>{{$order->VIP_Code_in_TTPOS}}</td>
                    <td>{{$order->Issue_Store_Name}}</td>
                    <td>{{$order->Transaction_ID}}</td>
                    <td>{{$order->SKU_Ops_Sales_Amount}}</td>
                    <td>{{$order->Transaction_Date}}</td>
                    <td>{{$order->Point_Earning}}</td>
                    <td>{{$order->Point_Redeemed}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="pull-left">
            &nbsp;&nbsp;共&nbsp;{!! $orders->total() !!} 条记录
        </div>

        <div class="pull-right id='ajaxpag'">
            {!! $orders->render() !!}
        </div>

        <!-- /.box-body -->

    @else
        <div>
            &nbsp;&nbsp;&nbsp;当前无数据
        </div>
    @endif
</div>












