<div class="tabs-container">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">小程序支付参数</a></li>
        <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">积分设置</a></li>
        <li class=""><a href="#tab_3" data-toggle="tab" aria-expanded="false">模板消息</a></li>
        <li class=""><a href="#tab_4" data-toggle="tab" aria-expanded="false">新人礼</a></li>
    </ul>
    <form method="post" action="{{route('admin.shitang.setting.save')}}" class="form-horizontal" id="setting_site_form">
        {{csrf_field()}}
        <div class="tab-content">
            <div class="tab-pane active" id="tab_1">
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">开启银商支付：</label>
                        <div class="col-sm-10">
                            <label class="checkbox-inline i-checks"><input name="enabled_union_pay" type="radio" value="1" @if($enabled_union_pay==1) checked @endif>是</label>
                            <label class="checkbox-inline i-checks"><input name="enabled_union_pay" type="radio" value="0" @if(!$enabled_union_pay) checked @endif>否</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">支付二维码链接:</label>
                        <div class="col-sm-4">
                            <input type="text" name="st_pay_code_base_url" placeholder="" class="form-control" value="{{$st_pay_code_base_url or ''}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">支付二维码：</label>
                        <div class="col-lg-10">
                            <div class="pull-left" id="qrcode">
                                <img src="{{ $pay_qr_code ? $pay_qr_code :'/assets/backend/images/no-image.jpg' }}" style="margin-right: 23px;width: 100px;height: 100px;">
                                <input type="hidden" name="pay_qr_code" value="{{ $pay_qr_code or '' }}">
                            </div>
                            <div class="clearfix" style="padding-top: 22px;">
                                <a class="btn btn-success create_pay_qr_code">生成支付二维码</a>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">接口地址:</label>
                        <div class="col-sm-4">
                            <input type="text" name="shitang_miniProgram_pay_config[api_url]" placeholder="" class="form-control" value="{{$shiTangMiniProgram['api_url'] or ''}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">msgSrcId:</label>
                        <div class="col-sm-4">
                            <input type="text" name="shitang_miniProgram_pay_config[msgSrcId]" placeholder="" class="form-control" value="{{$shiTangMiniProgram['msgSrcId'] or ''}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">mid:</label>
                        <div class="col-sm-4">
                            <input type="text" name="shitang_miniProgram_pay_config[mid]" placeholder="" class="form-control" value="{{$shiTangMiniProgram['mid'] or ''}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">tid:</label>
                        <div class="col-sm-4">
                            <input type="text" name="shitang_miniProgram_pay_config[tid]" placeholder="" class="form-control" value="{{$shiTangMiniProgram['tid'] or ''}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">instMid:</label>
                        <div class="col-sm-4">
                            <input type="text" name="shitang_miniProgram_pay_config[instMid]" placeholder="" class="form-control" value="{{$shiTangMiniProgram['instMid'] or ''}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">msgSrc:</label>
                        <div class="col-sm-4">
                            <input type="text" name="shitang_miniProgram_pay_config[msgSrc]" placeholder="" class="form-control" value="{{$shiTangMiniProgram['msgSrc'] or ''}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">signKey:</label>
                        <div class="col-sm-4">
                            <input type="text" name="shitang_miniProgram_pay_config[signKey]" placeholder="" class="form-control" value="{{$shiTangMiniProgram['signKey'] or ''}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">支付回调地址:</label>
                        <div class="col-sm-4">
                            <input type="text" name="shitang_miniProgram_pay_config[notifyUrl]" readonly placeholder="" class="form-control" value="{{$shiTangMiniProgram['notifyUrl'] or url('api/union_notify') . '/wx_lite'}}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab_2">
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">每消费1元送X分：</label>
                        <div class="col-sm-4">
                            <input type="text" name="order_paid_give_point_unit" placeholder="" class="form-control" value="{{ $order_paid_give_point_unit or '' }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">每积分抵扣金额（分）：</label>
                        <div class="col-sm-4">
                            <input type="text" name="point_deduction_money" placeholder="" class="form-control" value="{{ $point_deduction_money or '' }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab_3">
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">账户余额提醒：</label>
                        <div class="col-sm-4">
                            <input type="text" name="st_user_balance_change_template_id" placeholder="" class="form-control" value="{{ $st_user_balance_change_template_id or '' }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">积分兑换成功提醒：</label>
                        <div class="col-sm-4">
                            <input type="text" name="st_user_point_change_template_id" placeholder="" class="form-control" value="{{ $st_user_point_change_template_id or '' }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">订单支付成功通知：</label>
                        <div class="col-sm-4">
                            <input type="text" name="st_user_paid_success_template_id" placeholder="" class="form-control" value="{{ $st_user_paid_success_template_id or '' }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab_4">
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">优惠券有效期（月）：</label>
                        <div class="col-sm-4">
                            <input type="text" name="st_discount_expires_at" placeholder="" class="form-control" value="{{ $st_discount_expires_at or '' }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group" style="margin-top: 15px">
            <div class="col-sm-4 col-sm-offset-2">
                <button class="btn btn-primary" type="submit">保存设置</button>
            </div>
        </div>
    </form>
</div>

{!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.form.min.js') !!}
<script>

    $('.create_pay_qr_code').on('click', function () {
	    $.get("{{route('admin.shitang.pay.qrcode')}}", function (res) {
		    if (res.status) {
			    $('#qrcode img').attr("src", res.data.url);
			    $('#qrcode input').val(res.data.url);
		    }
	    });
    });

    $('#setting_site_form').ajaxForm({
	    success: function (result) {
		    swal({
			    title: "保存成功！",
			    text: "",
			    type: "success"
		    }, function () {
			    location.reload();
		    });

	    }
    })
</script>