{!! Html::style(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.min.css') !!}
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

                {!! Form::open( [ 'route' => ['admin.shitang.coupon.useRecord'], 'method' => 'get', 'id' => 'recordSearch-form','class'=>'form-horizontal'] ) !!}
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="col-sm-6">
                                <div class="input-group date form_datetime">
                                        <span class="input-group-addon" style="cursor: pointer">
                                            <i class="fa fa-calendar"></i>&nbsp;&nbsp;使用时间</span>
                                    <input type="text" class="form-control inline" name="stime"
                                           value="{{request('stime')}}" placeholder="开始" readonly>
                                    <span class="add-on"><i class="icon-th"></i></span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="input-group date form_datetime">
                                        <span class="input-group-addon" style="cursor: pointer">
                                            <i class="fa fa-calendar"></i></span>
                                    <input type="text" class="form-control" name="etime" value="{{request('etime')}}"
                                           placeholder="截止" readonly>
                                    <span class="add-on"><i class="icon-th"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" name="field">
                                <option value="code" {{request('field')=='code'?'selected':''}} >优惠券码</option>
                                <option value="order_no" {{request('field')=='order_no'?'selected':''}} >订单号</option>
                                <option value="mobile" {{request('field')=='mobile'?'selected':''}} >用户手机</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="text" name="value" value="{{request('value')}}" placeholder="Search"
                                       class=" form-control"> <span
                                        class="input-group-btn">
                                        <button type="submit" class="btn btn-primary">查找</button></span></div>
                        </div>
                    </div>
                    <input type="hidden" name="id" value="{{$id}}">
                    {!! Form::close() !!}

                    <div class="row" style="margin-top: 15px">
                        <div class="col-md-12">
                            <div class="col-md-12">
                                <div class="btn-group">
                                    <a class="btn btn-primary ladda-button dropdown-toggle batch" data-toggle="dropdown"
                                       href="javascript:;" data-style="zoom-in">导出 <span
                                                class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a data-toggle="modal-filter" class="btn btn-primary"
                                               data-target="#download_modal" data-backdrop="static"
                                               data-keyboard="false"
                                               data-link="{{route('admin.shitang.coupon.getCouponsUsedExportData',['type'=>'xls'])}}"
                                               id="all-xls"
                                               data-url="{{route('admin.export.index',['toggle'=>'all-xls'])}}"
                                               data-type="xls"
                                               href="javascript:;">导出数据</a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="btn-group">
                                    <button class="btn btn-primary " type="button" id="reset">重置搜索</button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="table-responsive">
                        <div id="coupons">
                            <div class="hr-line-dashed"></div>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <tbody>
                                    <tr>
                                        <th>使用时间</th>
                                        <th>优惠券名</th>
                                        <th>优惠券码</th>
                                        <th>订单编号</th>
                                        <th>订单总金额</th>
                                        <th>优惠金额</th>
                                        <th>订单状态</th>
                                        <th>会员名</th>
                                    </tr>
                                    @if($coupons->count()>0)
                                        @foreach ($coupons as $coupon)
                                            <tr class="coupon{{$coupon->id}}">
                                                <td>{{$coupon->used_at}}</td>
                                            <td>{{$coupon->discount->title}}</td>
                                            <td>{{$coupon->code}}</td>
                                            <td>
                                                @if($order = $coupon->getOrder())
                                                    {{ $order->order_no }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($order = $coupon->getOrder())
                                                    {{ number_format($order->amount / 100, 2, '.', '') }} 元
                                                @endif
                                            </td>
                                            <td>
                                                @if($order = $coupon->getOrder())
                                                    {{ number_format($order->adjustment->amount / 100, 2, '.', '') }} 元
                                                @endif
                                            </td>
                                            <td>
                                                @if($order = $coupon->getOrder() AND $order->status==1)
                                                    已支付
                                                @elseif($coupon->market_manager_id && $coupon->coupon_use_code)
                                                    核销卷
                                                @else
                                                    订单未支付
                                                @endif
                                            </td>

                                            <td>{{$coupon->user?($coupon->user->name?$coupon->user->name:$coupon->user->mobile):'/'}}</td>

                                        </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                                <div class="pull-left">
                                    &nbsp;&nbsp;共&nbsp;{!! $coupons->total() !!} 条记录
                                </div>

                                <div class="pull-right">
                                    {!! $coupons->appends(request()->except('page'))->render() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div id="download_modal" class="modal inmodal fade"></div>
{!! Html::script(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.zh-CN.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/loader/jquery.loader.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.el/el.common.js') !!}
@include('backend-shitang::coupon.public.coupon_used_script')