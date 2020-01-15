    <div class="tabs-container">
        <ul class="nav nav-tabs">

            <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">基本信息</a></li>
            <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">用户信息</a></li>
            <li class=""><a href="#tab_7" data-toggle="tab" aria-expanded="false">用户留言</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="tab_1">
                <div class="panel-body">
                    @include('backend-shitang::orders.includes.order_basic')
                </div>

            </div>

            <div class="tab-pane" id="tab_2">
                <div class="panel-body">
                    @include('backend-shitang::orders.includes.order_address')
                </div>
            </div>
            <div class="tab-pane" id="tab_7">
                <div class="panel-body">
                    <div class="ibox-content">
                        <div class="well well-lg col-md-8" style="text-indent:25px">
                            {{ $order->note ? $order->note: '暂无留言' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div id="modal" class="modal inmodal fade"></div>
    {!! Html::script(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.js') !!}
    {!! Html::script(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.zh-CN.js') !!}