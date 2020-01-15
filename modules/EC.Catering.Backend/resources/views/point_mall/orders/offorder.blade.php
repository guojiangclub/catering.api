@extends('backend.layouts.default')

@section ('title','订单列表')

@section('breadcrumbs')

    <h2>订单列表</h2>
    <ol class="breadcrumb">
        <li><a href="{!!route('backend.dashboard')!!}"><i class="fa fa-dashboard"></i> 首页</a></li>
        <li class="active">线下订单导入列表</li>
    </ol>

@endsection

@section('after-styles-end')
    {!! Html::style(env("APP_URL").'/assets/backend/libs/webuploader-0.1.5/webuploader.css') !!}
    {!! Html::style(env("APP_URL").'/assets/backend/admin/css/plugins/ladda/ladda-themeless.min.css') !!}
    {!! Html::style(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.min.css') !!}

@stop


@section('content')

    @if(Session::has('message'))
        <div class="alert alert-success alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-check"></i> 提示！</h4>
            {{ Session::get('message') }}
        </div>
    @endif

    <div class="tabs-container">

        <div class="tab-content">
            <div id="tab-1" class="tab-pane active">

                <div class="panel-body">

                        <div class="col-sm-12">
                            <button class="ladda-button btn btn-primary" id="export" data-style="slide-right">导出所有订单
                            </button>
                        </div>

                    <br>  <br>  <br>

                    {!! Form::open( [ 'route' => ['admin.orders.offOrders'], 'method' => 'get', 'id' => 'ordersurch-form','class'=>'form-horizontal'] ) !!}
                    <div class="row">

                        <div class="col-md-6">

                            <div class="col-sm-6">
                                <div class="input-group date form_datetime">
                                        <span class="input-group-addon" style="cursor: pointer">
                                            <i class="fa fa-calendar"></i>&nbsp;&nbsp;时间</span>
                                    <input type="text" class="form-control inline" name="Transaction_Date"
                                           value="{{request('stime')}}" placeholder="Transaction_Date " readonly>
                                    <span class="add-on"><i class="icon-th"></i></span>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-2">
                            <select class="form-control" name="field">
                                <option value="VIP_Code_in_TTPOS" {{request('field')=='VIP_Code_in_TTPOS'?'selected':''}} >
                                    VIP_Code_in_TTPOS
                                </option>
                                {{--<option value="Transaction_Date">Transaction_Date</option>--}}
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" name="value" value="{{request('value')}}" placeholder="Search"
                                       class=" form-control"> <span
                                        class="input-group-btn">
                                        <button type="submit" class="btn btn-primary">查找</button></span></div>
                        </div>

                    </div>
                {!! Form::close() !!}

                    <div class="table-responsive">
                        <div id="orders">
                            @include('backend.orders.includes.offorder_list')
                        </div>
                    </div><!-- /.box-body -->

                </div>
            </div>
        </div>
    </div>
    <div id="modal" class="modal inmodal fade"></div>
@endsection



@section('before-scripts-end')
    {!! Html::script(env("APP_URL").'/assets/backend/libs/webuploader-0.1.5/webuploader.js') !!}
    {!! Html::script(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.js') !!}
    {!! Html::script(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.zh-CN.js') !!}
    {!! Html::script(env("APP_URL").'/assets/backend/libs/formvalidation/dist/js/formValidation.min.js') !!}
    {!! Html::script(env("APP_URL").'/assets/backend/libs/formvalidation/dist/js/framework/bootstrap.min.js') !!}
    {!! Html::script(env("APP_URL").'/assets/backend/libs/formvalidation/dist/js/language/zh_CN.js') !!}
    {!! Html::script(env("APP_URL").'/assets/backend/libs/sortable/Sortable.min.js') !!}
    {!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.form.min.js') !!}
    {!! Html::script(env("APP_URL").'/assets/backend/libs/webuploader-0.1.5/webuploader.js') !!}

    {!! Html::script(env("APP_URL").'/assets/backend/admin/js/plugins/ladda/spin.min.js') !!}
    {!! Html::script(env("APP_URL").'/assets/backend/admin/js/plugins/ladda/ladda.min.js') !!}
    {!! Html::script(env("APP_URL").'/assets/backend/admin/js/plugins/ladda/ladda.jquery.min.js') !!}
    {!! Html::script(env("APP_URL").'/assets/backend/libs/loader/jquery.loader.min.js') !!}
    <script>
        $('.form_datetime').datetimepicker({
            minView: "month",
            format: "yyyy-mm-dd",
            autoclose:true,
            language:  'zh-CN',
            weekStart: 1,
            todayBtn:  1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0,
            showMeridian: 1,
            minuteStep : 1
        });

        $('#export').on('click',function () {
            var url =  location.href+"/exportExcel";

            $.ajax({
                type: 'GET',
                url: url,
                success: function(date){
                    window.location.href="{{route('admin.offOrders.download',['url'=>''])}}"+"/"+date;
                }});
            return false;
        });
    </script>
@stop



