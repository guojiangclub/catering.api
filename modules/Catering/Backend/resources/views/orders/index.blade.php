{!! Html::style(env("APP_URL").'/assets/backend/libs/ladda/ladda-themeless.min.css') !!}
{!! Html::style(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.min.css') !!}
    <style type="text/css">
        .more-filter {
            color: #008cee;
            margin-left: 20px;
            cursor: pointer
        }

        .more-filter em {
            font-style: normal
        }

        .well .row {
            margin: 5px 0
        }
    </style>

@if(Session::has('message'))
    <div class="alert alert-success alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-check"></i> 提示！</h4>
        {{ Session::get('message') }}
        </div>
@endif

<div class="tabs-container">
        <ul class="nav nav-tabs">
            <li class="{{$view=='all'?'active':''}}"><a no-pjax href="{{route('admin.shitang.orders.index',['status'=>'all'])}}">所有订单
                    <span class="badge">{{\GuoJiangClub\Catering\Backend\Models\Order::getOrdersCountByStatus([\GuoJiangClub\Catering\Backend\Models\Order::STATUS_PAY,\GuoJiangClub\Catering\Backend\Models\Order::STATUS_REFUND])}}</span></a>
            </li>
            <li class="{{ Active::query('status',\GuoJiangClub\Catering\Backend\Models\Order::STATUS_PAY) }}"><a no-pjax href="{{route('admin.shitang.orders.index',['status'=>\GuoJiangClub\Catering\Backend\Models\Order::STATUS_PAY])}}">已付款
                    <span class="badge">{{\GuoJiangClub\Catering\Backend\Models\Order::getOrdersCountByStatus(\GuoJiangClub\Catering\Backend\Models\Order::STATUS_PAY)}}</span></a>
                <li class="{{ Active::query('status',\GuoJiangClub\Catering\Backend\Models\Order::STATUS_REFUND) }}"><a no-pjax href="{{route('admin.shitang.orders.index',['status'=>\GuoJiangClub\Catering\Backend\Models\Order::STATUS_REFUND])}}">已退款
                    <span class="badge">{{\GuoJiangClub\Catering\Backend\Models\Order::getOrdersCountByStatus(\GuoJiangClub\Catering\Backend\Models\Order::STATUS_REFUND)}}</span></a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="tab-1" class="tab-pane active">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="btn-group">
                                <a class="btn btn-primary ladda-button dropdown-toggle batch" data-toggle="dropdown"
                                   href="javascript:;" data-style="zoom-in">导出 <span
                                            class="caret"></span></a>
                                <ul class="dropdown-menu">

                                    <li><a class="export-search-orders" data-toggle="modal"
                                           data-target="#download_modal" data-backdrop="static" data-keyboard="false"
                                           data-link="{{route('admin.shitang.orders.getExportData')}}" id="filter-xls"
                                           data-url="{{route('admin.export.index',['toggle'=>'filter-xls'])}}"
                                           data-type="xls"
                                           href="javascript:;">导出xls格式</a></li>

                                </ul>

                            </div>
                            <div class="btn-group">
                                <button class="btn btn-primary " id="reset">重置搜索</button>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="panel-body">
                    {!! Form::open( [ 'route' => ['admin.shitang.orders.index'], 'method' => 'get', 'id' => 'ordersurch-form','class'=>'form-horizontal'] ) !!}
                    <div class="form-group">
                        <input type="hidden" id="status" name="status"
                               value="{{!empty(request('status'))?request('status'):1}}">
                        <div class="col-md-6">
                            <div class="input-group">
                                <div class="input-group-btn">
                                    <select class="form-control" name="field" style="width: 150px">
                                        <option value="">请选择条件搜索</option>
                                        <option value="order_no" {{request('field')=='order_no'?'selected':''}} >订单编号
                                        </option>
                                        <option value="mobile" {{request('field')=='mobile'?'selected':''}} >联系电话
                                        </option>
                                    </select>
                                </div>
                                <input type="text" name="value" value="{{request('value')}}" placeholder="Search"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" name="type">
                                <option value="">支付方式</option>
                                <option {{request('type')=='wx_pub'?'selected':''}} value="wx_pub">微信支付
                                </option>
                                <option {{request('type')==\GuoJiangClub\Catering\Backend\Models\Order::TYPE_BALANCE?'selected':''}} value="{{ \GuoJiangClub\Catering\Backend\Models\Order::TYPE_BALANCE }}">
                                    余额支付
                                </option>
                                <option {{request('type')==\GuoJiangClub\Catering\Backend\Models\Order::TYPE_ALL_POINT?'selected':''}} value="{{ \GuoJiangClub\Catering\Backend\Models\Order::TYPE_ALL_POINT }}">
                                    积分抵扣
                                </option>
                                <option {{request('type')==\GuoJiangClub\Catering\Backend\Models\Order::TYPE_BALANCE_AND_POINT?'selected':''}} value="{{ \GuoJiangClub\Catering\Backend\Models\Order::TYPE_BALANCE_AND_POINT }}">
                                    积分、余额支付
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">搜索</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-6">
                            <div class="col-sm-6" style="padding-left: 0">
                                <div class="input-group date form_datetime">
                                        <span class="input-group-addon">
                                            <i class="fa fa-calendar"></i>&nbsp;&nbsp;下单时间</span>
                                    <input type="text" class="form-control inline" name="stime"
                                           value="{{request('stime')}}" placeholder="开始 " readonly>
                                    <span class="add-on"><i class="icon-th"></i></span>
                                </div>
                            </div>
                            <div class="col-sm-1">一</div>
                            <div class="col-sm-5" style="padding-left: 0">
                                <div class="input-group date form_datetime">
                                        <span class="input-group-addon" style="cursor: pointer">
                                            <i class="fa fa-calendar"></i></span>
                                    <input type="text" class="form-control" name="etime" value="{{request('etime')}}"
                                           placeholder="截止" readonly>
                                    <span class="add-on"><i class="icon-th"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="col-sm-6" style="padding-left: 0">
                                <div class="input-group date form_datetime">
                                        <span class="input-group-addon">
                                            <i class="fa fa-calendar"></i>&nbsp;&nbsp;付款时间</span>
                                    <input type="text" class="form-control inline" name="s_pay_time"
                                           value="{{request('s_pay_time')}}" placeholder="开始 " readonly>
                                    <span class="add-on"><i class="icon-th"></i></span>
                                </div>
                            </div>
                            <div class="col-sm-1">一</div>
                            <div class="col-sm-5" style="padding-left: 0">
                                <div class="input-group date form_datetime">
                                        <span class="input-group-addon" style="cursor: pointer">
                                            <i class="fa fa-calendar"></i></span>
                                    <input type="text" class="form-control" name="e_pay_time"
                                           value="{{request('e_pay_time')}}"
                                           placeholder="截止" readonly>
                                    <span class="add-on"><i class="icon-th"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                    <div class="table-responsive">
                        <div id="orders">
                            @include('backend-shitang::orders.includes.orders_list')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="modal" class="modal inmodal fade"></div>
    <div id="modal_invoice" class="modal inmodal fade"></div>
    <div id="modal_produce" class="modal inmodal fade"></div>
    <div id="download_modal" class="modal inmodal fade"></div>
{{--@endsection



@section('before-scripts-end')--}}
{{--    {!! Html::script(env("APP_URL").'/assets/backend/libs/webuploader-0.1.5/webuploader.js') !!}--}}
{!! Html::script(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.zh-CN.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/formvalidation/dist/js/formValidation.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/formvalidation/dist/js/framework/bootstrap.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/formvalidation/dist/js/language/zh_CN.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/sortable/Sortable.min.js') !!}
{{--    {!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.form.min.js') !!}--}}


{!! Html::script(env("APP_URL").'/vendor/libs/ladda/spin.min.js') !!}
{!! Html::script(env("APP_URL").'/vendor/libs/ladda/ladda.min.js') !!}
{!! Html::script(env("APP_URL").'/vendor/libs/ladda/ladda.jquery.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/loader/jquery.loader.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.el/el.common.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.el/distpicker.js') !!}
@include('backend-shitang::orders.includes.script')
{{--@stop--}}



