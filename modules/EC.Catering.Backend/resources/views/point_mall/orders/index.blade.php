{!! Html::style(env("APP_URL").'/assets/backend/libs/webuploader-0.1.5/webuploader.css') !!}
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
        <li class="{{ Active::query('status','all') }}"><a no-pjax
                                                           href="{{route('admin.point-mall.orders.index',['status'=>'all'])}}">所有订单
                <span class="badge">{{\GuoJiangClub\EC\Catering\Backend\Models\Order::whereBetween('status',[1,6])->where('type',5)->count()}}</span></a>
        </li>

        <li class="{{ Active::query('status',2) }}"><a no-pjax
                                                       href="{{route('admin.point-mall.orders.index',['status'=>2])}}">待发货
                <span class="badge">{{\GuoJiangClub\EC\Catering\Backend\Models\Order::where('status',2)->where('type',5)->count()}}</span></a>
        </li>
        <li class="{{ Active::query('status',3) }}"><a no-pjax
                                                       href="{{route('admin.point-mall.orders.index',['status'=>3])}}">待收货
                <span class="badge">{{\GuoJiangClub\EC\Catering\Backend\Models\Order::where('status',3)->where('type',5)->count()}}</span></a>
        </li>
        <li class="{{ Active::query('status',4) }}"><a no-pjax
                                                       href="{{route('admin.point-mall.orders.index',['status'=>4])}}">已收货待评价
                <span class="badge">{{\GuoJiangClub\EC\Catering\Backend\Models\Order::where('status',4)->where('type',5)->count()}}</span></a>
        </li>
        <li class="{{ Active::query('status',5) }}"><a no-pjax
                                                       href="{{route('admin.point-mall.orders.index',['status'=>5])}}">已完成
                <span class="badge">{{\GuoJiangClub\EC\Catering\Backend\Models\Order::where('status',5)->where('type',5)->count()}}</span></a>
        </li>
        <li class="{{ Active::query('status',6) }}"><a no-pjax
                                                       href="{{route('admin.point-mall.orders.index',['status'=>6])}}">已取消
                <span class="badge">{{\GuoJiangClub\EC\Catering\Backend\Models\Order::where('status',6)->where('type',5)->count()}}</span></a>
        </li>
        <li class="{{ Active::query('status',9) }}"><a no-pjax
                                                       href="{{route('admin.point-mall.orders.index',['status'=>9])}}">已删除
                <span class="badge">{{\GuoJiangClub\EC\Catering\Backend\Models\Order::where('status',9)->where('type',5)->count()}}</span></a>
        </li>


    </ul>
    <div class="tab-content">
        <div id="tab-1" class="tab-pane active">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group">
                            <a class="btn btn-primary ladda-button dropdown-toggle batch" data-toggle="dropdown"
                               href="javascript:;" data-style="zoom-in">批量操作 <span
                                        class="caret"></span></a>
                            <ul class="dropdown-menu">
                                {{--<li><a id="batchExport" href="javascript:;">批量导出</a></li>--}}

                                <li><a class="export-orders" data-toggle="modal-filter"
                                       data-target="#download_modal" data-backdrop="static" data-keyboard="false"
                                       id="modal-filter-point-orders-xls"
                                       data-url="{{route('admin.export.index',['toggle'=>'modal-filter-point-orders-xls'])}}"
                                       data-type="xls"
                                       href="javascript:;">导出勾选订单 xls格式</a></li>

                                <li><a class="export-orders" data-toggle="modal-filter"
                                       data-target="#download_modal" data-backdrop="static" data-keyboard="false"
                                       id="modal-filter-point-orders-csv"
                                       data-url="{{route('admin.export.index',['toggle'=>'modal-filter-point-orders-csv'])}}"
                                       data-type="csv"
                                       href="javascript:;">导出勾选订单 csv格式</a></li>

                                <li><a id="chapter-create-btn" data-toggle="modal"
                                       data-target="#modal" data-backdrop="static" data-keyboard="false"
                                       data-url="{{route('admin.orders.import')}}">
                                        批量订单发货</a>
                                </li>
                            </ul>
                        </div>

                        <div class="btn-group">
                            {{--<button  data-href="{{route('admin.orders.export.job',request()->except('page'))}}"--}}
                            {{--id="export" class="ladda-button btn btn-primary">导出</button>--}}

                            <a class="btn btn-primary ladda-button dropdown-toggle batch" data-toggle="dropdown"
                               href="javascript:;" data-style="zoom-in">导出 <span
                                        class="caret"></span></a>
                            <ul class="dropdown-menu">

                                <li><a class="export-search-orders" data-toggle="modal"
                                       data-target="#download_modal" data-backdrop="static" data-keyboard="false"
                                       data-link="{{route('admin.point-mall.orders.getExportData')}}"
                                       id="filter-point-orders-xls"
                                       data-url="{{route('admin.export.index',['toggle'=>'filter-point-orders-xls'])}}"
                                       data-type="xls"
                                       href="javascript:;">导出xls格式</a></li>

                                <li><a class="export-search-orders" data-toggle="modal"
                                       data-target="#download_modal" data-backdrop="static" data-keyboard="false"
                                       data-link="{{route('admin.point-mall.orders.getExportData')}}"
                                       id="filter-point-orders-csv"
                                       data-url="{{route('admin.export.index',['toggle'=>'filter-point-orders-csv'])}}"
                                       data-type="csv"
                                       href="javascript:;">导出csv格式</a></li>

                            </ul>

                        </div>
                        <div class="btn-group">
                            <button class="btn btn-primary " id="reset">重置搜索</button>
                        </div>
                    </div>

                </div>

            </div>

            <div class="panel-body">
                {!! Form::open( [ 'route' => ['admin.point-mall.orders.index'], 'method' => 'get', 'id' => 'ordersurch-form','class'=>'form-horizontal'] ) !!}
                <div class="form-group">
                    <input type="hidden" id="status" name="status"
                           value="{{!empty(request('status'))?request('status'):1}}">

                    <div class="col-md-2">
                        <select class="form-control" name="order_status">
                            <option value="" {{request('order_status')==''?'selected':''}}>发货状态</option>
                            <option value="2" {{request('order_status')==2?'selected':''}} >待发货</option>
                            <option value="3" {{request('order_status')==3?'selected':''}} >已发货</option>
                        </select>
                    </div>


                    <div class="col-md-8">
                        <div class="input-group">
                            <div class="input-group-btn">
                                <select class="form-control" name="field" style="width: 150px">
                                    <option value="">请选择条件搜索</option>
                                    <option value="order_no" {{request('field')=='order_no'?'selected':''}} >订单编号
                                    </option>
                                    <option value="accept_name" {{request('field')=='accept_name'?'selected':''}} >
                                        收货人姓名
                                    </option>
                                    <option value="mobile" {{request('field')=='mobile'?'selected':''}} >联系电话
                                    </option>
                                    <option value="address" {{request('field')=='address'?'selected':''}} >地址
                                    </option>
                                    <option value="sku" {{request('field')=='sku'?'selected':''}} >SKU编码</option>
                                    <option value="spu" {{request('field')=='spu'?'selected':''}} >SPU编码</option>
                                </select>
                            </div>


                            <input type="text" name="value" value="{{request('value')}}" placeholder="Search"
                                   class="form-control">
                        </div>
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

                <div class="form-group">
                    <div class="well clearfix" id="more-condition" style="display: {{$more_filter?'':'none'}};">

                        <div class="row">
                            <div class="col-md-2">
                                <select class="form-control" name="distribution">
                                    <option value="">物流公司</option>
                                    @foreach($freightCompany as $item)
                                        <option value="{{$item->id}}" {{request('distribution')==$item->id?'selected':''}}>{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6" id="dist-target">
                                <div class="row">
                                    <div class="col-sm-3" style="text-align: right;line-height: 34px;">
                                        收货地区:
                                    </div>
                                    <div class="col-sm-4">
                                        <select class="form-control" name="province"></select><!-- 省 -->
                                    </div>
                                    <div class="col-sm-1">一</div>
                                    <div class="col-sm-4">
                                        <select class="form-control" name="city"></select><!-- 市 -->
                                    </div>
                                    <select style="display: none;"></select><!-- 区 -->
                                </div>
                            </div>


                        </div>

                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">搜索</button>
                        <span class="more-filter"
                              data-status="{{$more_filter?'hidden':'show'}}"><em>{{$more_filter?'收起':'更多搜索'}}</em>  <i
                                    class="fa {{$more_filter?'fa-chevron-up':'fa-chevron-down'}}"></i></span>
                        <input type="hidden" value="{{request('more_filter')?request('more_filter'):0}}"
                               name="more_filter">
                    </div>


                </div>
                {!! Form::close() !!}

                <div class="table-responsive">
                    <div id="orders">
                        @include('catering-backend::point_mall.orders.includes.orders_list')
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

{!! Html::script(env("APP_URL").'/assets/backend/libs/webuploader-0.1.5/webuploader.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.zh-CN.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/formvalidation/dist/js/formValidation.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/formvalidation/dist/js/framework/bootstrap.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/formvalidation/dist/js/language/zh_CN.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/sortable/Sortable.min.js') !!}

{!! Html::script(env("APP_URL").'/assets/backend/admin/js/plugins/ladda/spin.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/admin/js/plugins/ladda/ladda.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/admin/js/plugins/ladda/ladda.jquery.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/loader/jquery.loader.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.el/el.common.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.el/distpicker.js') !!}
@include('catering-backend::point_mall.orders.includes.script')