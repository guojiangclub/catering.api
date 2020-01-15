{!! Html::style(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.min.css') !!}

    <div class="tabs-container">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#tab-1" aria-expanded="true"> 编辑生日礼（{{$activity->name}}）</a></li>
        </ul>

        <div class="tab-content">
            <div id="tab-1" class="tab-pane active">
                <div class="panel-body">
                    <div class="row">
                        {!! Form::open( [ 'url' => [route('admin.shitang.gift.birthday.store')], 'method' => 'POST', 'id' => 'create-suit-form','class'=>'form-horizontal'] ) !!}
                        <input type="hidden" name="id" value="{{ $activity->id }}">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">*生日礼礼名称:</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="name" placeholder="" required="required" value="{{$activity->name}}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">*生日礼礼标题:</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="title" placeholder="" value="{{$activity->title}}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">*活动有效时间：</label>
                            <div class="col-sm-3">
                                <div class="input-group date form_datetime">
                                        <span class="input-group-addon" style="cursor: pointer">
                                            <i class="fa fa-calendar"></i>&nbsp;&nbsp;开始</span>
                                    <input type="text" class="form-control inline" name="starts_at" value="{{$activity->starts_at}}"
                                           placeholder="点击选择开始时间" readonly>
                                    <span class="add-on"><i class="icon-th"></i></span>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="input-group date form_datetime">
                                        <span class="input-group-addon" style="cursor: pointer">
                                            <i class="fa fa-calendar"></i>&nbsp;&nbsp;截止</span>
                                    <input type="text" class="form-control" name="ends_at"
                                           value="{{$activity->ends_at}}" placeholder="点击选择结束时间" readonly>
                                    <span class="add-on"><i class="icon-th"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">*赠送优惠券（最多2张）：</label>
                            <div class="col-sm-6">
                                <select class="form-control select_coupon" name="coupons[]" id="coupons" multiple="multiple">
                                    @if(count($coupons)>0)
                                        @foreach($coupons as $coupon)
                                            <option value="{{ $coupon->id }}" @if(in_array($coupon->id, $selected))selected @endif>{{ $coupon->title }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">赠送积分:</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="point" placeholder="" value="{{$activity->point}}" />
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">*赠送方式：</label>
                            <div class="col-sm-10">
                                <label class="checkbox-inline i-checks"><input name="type" type="radio" value="{{ \GuoJiangClub\Catering\Backend\Models\GiftActivity::TYPE_RANDOM }}" @if($activity->type==\GuoJiangClub\Catering\Backend\Models\GiftActivity::TYPE_RANDOM) checked @endif>随机送一张</label>
                                <label class="checkbox-inline i-checks"><input name="type" type="radio" value="{{ \GuoJiangClub\Catering\Backend\Models\GiftActivity::TYPE_ALL }}" @if($activity->type==\GuoJiangClub\Catering\Backend\Models\GiftActivity::TYPE_ALL) checked @endif> 全部送</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">*开启状态：</label>
                            <div class="col-sm-10">
                                <label class="checkbox-inline i-checks"><input name="status" type="radio" value="1" @if($activity->status==1) checked @endif>是</label>
                                <label class="checkbox-inline i-checks"><input name="status" type="radio" value="0" @if($activity->status==0) checked @endif>否</label>
                            </div>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <div class="col-sm-4 col-sm-offset-2">
                            <button class="btn btn-primary" type="submit">保存</button>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

{!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.zclip/jquery.zclip.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.zh-CN.js') !!}
    <script>
        $(document).ready(function () {
	        $('#coupons').select2({
		        placeholder: '请选择优惠券',
		        allowClear: true,
		        maximumSelectionLength: 2
	        });

	        $('#markets').select2({
		        placeholder: '请选择所属集市'
	        });
        });

        $('.form_datetime').datetimepicker({
	        language: 'zh-CN',
	        weekStart: 1,
	        todayBtn: 1,
	        autoclose: 1,
	        todayHighlight: 1,
	        startView: 2,
	        forceParse: 0,
	        showMeridian: 1,
	        minuteStep: 1
        });


        $('#create-suit-form').ajaxForm({
	        success: function (result) {
		        if (!result.status) {
			        swal("保存失败!", result.message, "error")
		        } else {
			        swal({
				        title: "保存成功！",
				        text: "",
				        type: "success"
			        }, function () {
				        location = '{{route('admin.shitang.gift.birthday')}}';
			        });
		        }

	        }
        });
    </script>