{!! Html::style(env("APP_URL").'/assets/backend/libs/dategrangepicker/daterangepicker.css') !!}
<style type="text/css">
    .lnk {
        color: #55a8fd;
        text-decoration: none;
        cursor: pointer;
        outline: 0;
    }

    .division .division {
        border: none;
    }

    .division {
        overflow: hidden;
        zoom: 1;
        background: #ffffff;
        border: 1px solid #dbdbdb;
        border-radius: 5px;
    }

    .division {
        padding: 10px;
        line-height: normal;
        white-space: normal;
    }

    .span-auto, .span-6 {
        float: left;
        margin-right: 10px;
        overflow: hidden;
    }

    .frt {
        float: right !important;
    }

    .area-item {
        margin: 5px;
    }

    .coupon-item {
        margin: 5px;
    }
</style>

<div class="ibox float-e-margins">
    <div class="ibox-content" style="display: block;">
        {!! Form::open( [ 'url' => [route('admin.shitang.gift.center.store')], 'method' => 'POST', 'id' => 'base-form','class'=>'form-horizontal'] ) !!}
        <input type="hidden" name="id" value="{{ $activity->id }}">
        <div class="form-group">
            {!! Form::label('status','状态：', ['class' => 'col-lg-2 control-label']) !!}
            <div class="col-lg-9">
                <input value="1" type="radio" name="status" @if($activity->status==1) checked @endif> 启用
                <input value="0" type="radio" name="status" @if($activity->status==0) checked @endif> 禁用
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('name','活动名称：', ['class' => 'col-lg-2 control-label']) !!}
            <div class="col-lg-9">
                <input type="text" class="form-control" name="title" value="{{ $activity->title }}">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">活动banner：</label>
            <div class="col-sm-4">
                <div class="pull-left" id="activity_banner">
                    <img src="{{ $activity->activity_banner }}" width="182px" style="margin-right: 23px;">
                    <input type="hidden" name="activity_banner" class="form-control" value="{{ $activity->activity_banner }}">
                </div>
                <div class="clearfix" style="padding-top: 22px;">
                    <div id="ActivityBannerPicker">添加图片</div>
                </div>
            </div>
        </div>

        <div class="form-group" id="two-inputs">
            <label class="col-sm-2 control-label">活动期限：</label>
            <div class="col-sm-9">
                <div class="input-group date form_datetime" id="start_at">
                    <span class="input-group-addon" style="cursor: pointer">
                        <i class="fa fa-calendar"></i>&nbsp;&nbsp;开始</span>
                            <input type="text" name="starts_at" class="form-control inline" id="date-range200" size="20" value="{{ $activity->starts_at }}" placeholder="点击选择时间" readonly>
                    <span class="add-on"><i class="icon-th"></i></span>
                </div>
                <div id="date-range12-container"></div>
            </div>

            <div class="col-sm-9 col-sm-offset-2" style="margin-top: 10px">
                <div class="input-group date form_datetime" id="end_at">
                    <span class="input-group-addon" style="cursor: pointer">
                        <i class="fa fa-calendar"></i>&nbsp;&nbsp;截止</span>
                        <input type="text" name="ends_at" class="form-control inline" id="date-range201" value="{{ $activity->ends_at }}" placeholder="" readonly>
                    <span class="add-on"><i class="icon-th"></i></span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">关联优惠券：</label>
            <div class="col-sm-9">
                <div style="padding: 10px; background: rgb(239, 239, 239); margin: 10px 0px;">
                    <div class="deliveryArea">
                        <div class="ruleArea">
                            @if($activity->items->count()>0)
                                @foreach($activity->items as $key=>$value)
                                    <div class="coupon-item coupon-item{{ $key }} clearfix division">
                                        <div class="span-auto">
                                            <label> 优惠券：</label>
                                            <span class="coupon-{{ $key }}">{{ $value->discount->title }}</span>
                                        </div>
                                        <input type="hidden" name="discount_coupon_rules[{{ $key }}][couponName]" value="{{ $value->discount->title }}"><input type="hidden" name="discount_coupon_rules[{{ $key }}][couponCode]" value="{{ $value->code }}"><input type="hidden" name="discount_coupon_rules[{{ $key }}][couponId]" value="{{ $value->discount_id }}">
                                        <div class="frt">
                                            <span class="lnk delete-coupon-item" data-id="{{ $key }}">删除</span>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <span class="lnk add-new-rule">[+]添加一张</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-offset-2 col-md-8 controls">
                <button type="submit" class="btn btn-primary">保存</button>
            </div>
        </div>

        {!! Form::close() !!}
    </div>
</div>
<div id="coupon_modal" class="modal inmodal fade"></div>
@include('UEditor::head')
{!! Html::script('assets/backend/libs/jquery.form.min.js') !!}
{!! Html::script('assets/backend/libs/webuploader-0.1.5/webuploader.js') !!}
<script>
    var coupon_code = [];
            @if($activity->items->count()>0)
            @foreach($activity->items as $value)
    var tmp_coupon_code = '{{$value->code}}';
    coupon_code.push(tmp_coupon_code);
    @endforeach
    @endif
</script>
@include('backend-shitang::center.script')