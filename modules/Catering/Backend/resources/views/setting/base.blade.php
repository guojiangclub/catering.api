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

    .coupon-item {
        margin: 5px;
    }

    .x-input {
        text-align: center;
    }
</style>
<div class="tabs-container">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">基础设置</a></li>
        <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">支付设置</a></li>
        <li class=""><a href="#tab_3" data-toggle="tab" aria-expanded="false">积分设置</a></li>
        {{--<li class=""><a href="#tab_4" data-toggle="tab" aria-expanded="false">模板消息</a></li>--}}
        <li class=""><a href="#tab_5" data-toggle="tab" aria-expanded="false">掌柜端</a></li>
    </ul>
    <form method="post" action="{{route('admin.shitang.setting.save')}}" class="form-horizontal" id="setting_site_form">
        {{csrf_field()}}
        <input type="hidden" name="setting_type" value="base">
        <div class="tab-content">
            <div class="tab-pane active" id="tab_1">
                <div class="panel-body">

                    <div class="form-group">
                        <label class="col-sm-2 control-label">小程序标题设置：</label>
                        <div class="col-sm-4">
                            <input type="text" name="miniprogram_customer_title" class="form-control"
                                   value="{{ settings('miniprogram_customer_title')?settings('miniprogram_customer_title'):''}}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">优惠券背景图：</label>
                        <div class="col-sm-4">
                            <div class="pull-left" id="coupon_bg_img">
                                <img src="{{ settings('coupon_bg_img') ? settings('coupon_bg_img') : "/assets/backend/images/backgroundImage/pictureBackground.png" }}"
                                     class="member_shop_logo" width="182px" style="margin-right: 23px;">
                                <input type="hidden" name="coupon_bg_img" class="form-control"
                                       value="{{ settings('coupon_bg_img') ? settings('coupon_bg_img')  : '' }}">
                            </div>
                            <div class="clearfix" style="padding-top: 22px;">
                                <div id="couponBgPicker">添加图片</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">登录页背景图片：</label>
                        <div class="col-sm-4">
                            <div class="pull-left" id="login_page_bg">
                                <img src="{{ settings('login_page_bg') ? settings('login_page_bg') : "/assets/backend/images/backgroundImage/pictureBackground.png" }}"
                                     class="member_shop_logo" width="182px" style="margin-right: 23px;">
                                <input type="hidden" name="login_page_bg" class="form-control"
                                       value="{{ settings('login_page_bg') ? settings('login_page_bg')  : '' }}">
                            </div>
                            <div class="clearfix" style="padding-top: 22px;">
                                <div id="loginInfoImgPicker">添加图片</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">完善个人信息：</label>
                        <div class="col-sm-4">
                            <div class="pull-left" id="personal-info-img">
                                <img src="{{ $personal_info_img ? $personal_info_img : "/assets/backend/images/backgroundImage/pictureBackground.png" }}"
                                     class="member_shop_logo" width="182px" style="margin-right: 23px;">
                                <input type="hidden" name="personal_info_img" class="form-control"
                                       value="{{ $personal_info_img ? $personal_info_img  : '' }}">
                            </div>
                            <div class="clearfix" style="padding-top: 22px;">
                                <div id="personalInfoImgPicker">添加图片</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">会员章程链接：</label>
                        <div class="col-sm-4">
                            <input type="text" name="member_rules_link" class="form-control"
                                   value="{{ $member_rules_link or "" }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">商户名称：</label>
                        <div class="col-sm-4">
                            <input type="text" name="shop_name" class="form-control" value="{{ $shop_name or "" }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">商户logo：</label>
                        <div class="col-sm-4">
                            <div class="pull-left" id="member-shop-logo">
                                <img src="{{ $member_shop_logo ? $member_shop_logo : "/assets/backend/images/backgroundImage/pictureBackground.png" }}"
                                     class="member_shop_logo" width="182px" style="margin-right: 23px;">
                                <input type="hidden" name="member_shop_logo" class="form-control"
                                       value="{{ $member_shop_logo ? $member_shop_logo  : '' }}">
                            </div>
                            <div class="clearfix" style="padding-top: 22px;">
                                <div id="memberShopLogoPicker">添加图片</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">完善信息获得N积分：</label>
                        <div class="col-sm-4">
                            <input type="text" name="complete_birthday_point" class="form-control"
                                   value="{{ $complete_birthday_point or "" }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">小程序背景图片：</label>
                        <div class="col-sm-4">
                            <div class="pull-left" id="background-image">
                                <img src="{{ $mini_program_bg_img ? $mini_program_bg_img : "/assets/backend/images/backgroundImage/pictureBackground.png" }}"
                                     class="bg_img" width="182px" style="margin-right: 23px;">
                                <input type="hidden" name="mini_program_bg_img" class="form-control"
                                       value="{{ $mini_program_bg_img ? $mini_program_bg_img  : '' }}">
                            </div>
                            <div class="clearfix" style="padding-top: 22px;">
                                <div id="backgroundImagePicker">添加图片</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab_2">
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">开启银商支付：</label>
                        <div class="col-sm-10">
                            <label class="checkbox-inline i-checks"><input name="enabled_union_pay" type="radio"
                                                                           value="1"
                                                                           @if($enabled_union_pay==1) checked @endif>是</label>
                            <label class="checkbox-inline i-checks"><input name="enabled_union_pay" type="radio"
                                                                           value="0"
                                                                           @if(!$enabled_union_pay) checked @endif>否</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">接口地址:</label>
                        <div class="col-sm-4">
                            <input type="text" name="shitang_miniProgram_pay_config[api_url]" placeholder=""
                                   class="form-control" value="{{$shiTangMiniProgram['api_url'] or ''}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">msgSrcId:</label>
                        <div class="col-sm-4">
                            <input type="text" name="shitang_miniProgram_pay_config[msgSrcId]" placeholder=""
                                   class="form-control" value="{{$shiTangMiniProgram['msgSrcId'] or ''}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">mid:</label>
                        <div class="col-sm-4">
                            <input type="text" name="shitang_miniProgram_pay_config[mid]" placeholder=""
                                   class="form-control" value="{{$shiTangMiniProgram['mid'] or ''}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">tid:</label>
                        <div class="col-sm-4">
                            <input type="text" name="shitang_miniProgram_pay_config[tid]" placeholder=""
                                   class="form-control" value="{{$shiTangMiniProgram['tid'] or ''}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">instMid:</label>
                        <div class="col-sm-4">
                            <input type="text" name="shitang_miniProgram_pay_config[instMid]" placeholder=""
                                   class="form-control" value="{{$shiTangMiniProgram['instMid'] or ''}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">msgSrc:</label>
                        <div class="col-sm-4">
                            <input type="text" name="shitang_miniProgram_pay_config[msgSrc]" placeholder=""
                                   class="form-control" value="{{$shiTangMiniProgram['msgSrc'] or ''}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">signKey:</label>
                        <div class="col-sm-4">
                            <input type="text" name="shitang_miniProgram_pay_config[signKey]" placeholder=""
                                   class="form-control" value="{{$shiTangMiniProgram['signKey'] or ''}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">支付回调地址:</label>
                        <div class="col-sm-4">
                            <input type="text" name="shitang_miniProgram_pay_config[notifyUrl]" readonly placeholder=""
                                   class="form-control"
                                   value="{{$shiTangMiniProgram['notifyUrl'] or url('api/union_notify') . '/wx_lite'}}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab_3">
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">每消费1元送X分：</label>
                        <div class="col-sm-4">
                            <input type="text" name="order_paid_give_point_unit" placeholder="" class="form-control"
                                   value="{{ $order_paid_give_point_unit or '' }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">每积分抵扣金额（分）：</label>
                        <div class="col-sm-4">
                            <input type="text" name="point_deduction_money" placeholder="" class="form-control"
                                   value="{{ $point_deduction_money or '' }}">
                        </div>
                    </div>
                </div>
            </div>
            {{--<div class="tab-pane" id="tab_4">
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">账户余额提醒：</label>
                        <div class="col-sm-4">
                            <input type="text" name="st_user_balance_change_template_id" placeholder=""
                                   class="form-control" value="{{ $st_user_balance_change_template_id or '' }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">积分兑换成功提醒：</label>
                        <div class="col-sm-4">
                            <input type="text" name="st_user_point_change_template_id" placeholder=""
                                   class="form-control" value="{{ $st_user_point_change_template_id or '' }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">订单支付成功通知：</label>
                        <div class="col-sm-4">
                            <input type="text" name="st_user_paid_success_template_id" placeholder=""
                                   class="form-control" value="{{ $st_user_paid_success_template_id or '' }}">
                        </div>
                    </div>
                </div>
            </div>--}}
            <div class="tab-pane" id="tab_5">
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">店铺名称：</label>
                        <div class="col-sm-4">
                            <input type="text" name="manager_shop_name" placeholder="" class="form-control" value="{{ $manager_shop_name or '' }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">店铺地址：</label>
                        <div class="col-sm-4">
                            <input type="text" name="manager_shop_address" placeholder="" class="form-control" value="{{ $manager_shop_address or '' }}">
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
<div id="coupon_modal" class="modal inmodal fade"></div>
{!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.form.min.js') !!}
{!! Html::script('assets/backend/libs/webuploader-0.1.5/webuploader.js') !!}
<script>
    $('#setting_site_form').ajaxForm({
	    success: function (result) {
		    if (result.status) {
			    swal({
				    title: "保存成功！",
				    text: "",
				    type: "success"
			    }, function () {
				    location.reload();
			    });
		    } else {
			    swal(result.message, "", "error");
		    }
	    }
    });

    // 初始化Web Uploader
    $(document).ready(function () {
	    var postImgUrl = '{{route('upload.image',['_token'=>csrf_token()])}}';
	    var bgUploader = WebUploader.create({
		    auto: true,
		    swf: '{{url('assets/backend/libs/webuploader-0.1.5/Uploader.swf')}}',
		    server: postImgUrl,
		    pick: '#backgroundImagePicker',
		    fileVal: 'upload_image',
		    accept: {
			    title: 'Images',
			    extensions: 'jpg,jpeg,png',
			    mimeTypes: 'image/jpg,image/jpeg,image/png'
		    }
	    });
	    // 文件上传成功，给item添加成功class, 用样式标记上传成功。
	    bgUploader.on('uploadSuccess', function (file, response) {
		    $('#background-image img').attr("src", response.url);
		    $('#background-image input').val(response.url);
	    });

	    var logoUploader = WebUploader.create({
		    auto: true,
		    swf: '{{url('assets/backend/libs/webuploader-0.1.5/Uploader.swf')}}',
		    server: postImgUrl,
		    pick: '#memberShopLogoPicker',
		    fileVal: 'upload_image',
		    accept: {
			    title: 'Images',
			    extensions: 'jpg,jpeg,png',
			    mimeTypes: 'image/jpg,image/jpeg,image/png'
		    }
	    });
	    // 文件上传成功，给item添加成功class, 用样式标记上传成功。
	    logoUploader.on('uploadSuccess', function (file, response) {
		    $('#member-shop-logo img').attr("src", response.url);
		    $('#member-shop-logo input').val(response.url);
	    });

	    var personalUploader = WebUploader.create({
		    auto: true,
		    swf: '{{url('assets/backend/libs/webuploader-0.1.5/Uploader.swf')}}',
		    server: postImgUrl,
		    pick: '#personalInfoImgPicker',
		    fileVal: 'upload_image',
		    accept: {
			    title: 'Images',
			    extensions: 'jpg,jpeg,png',
			    mimeTypes: 'image/jpg,image/jpeg,image/png'
		    }
	    });
	    // 文件上传成功，给item添加成功class, 用样式标记上传成功。
	    personalUploader.on('uploadSuccess', function (file, response) {
		    $('#personal-info-img img').attr("src", response.url);
		    $('#personal-info-img input').val(response.url);
	    });


	    var loginUploader = WebUploader.create({
		    auto: true,
		    swf: '{{url('assets/backend/libs/webuploader-0.1.5/Uploader.swf')}}',
		    server: postImgUrl,
		    pick: '#loginInfoImgPicker',
		    fileVal: 'upload_image',
		    accept: {
			    title: 'Images',
			    extensions: 'jpg,jpeg,png',
			    mimeTypes: 'image/jpg,image/jpeg,image/png'
		    }
	    });
	    // 文件上传成功，给item添加成功class, 用样式标记上传成功。
	    loginUploader.on('uploadSuccess', function (file, response) {
		    $('#login_page_bg img').attr("src", response.url);
		    $('#login_page_bg input').val(response.url);
	    });

	    var couponBgUploader = WebUploader.create({
		    auto: true,
		    swf: '{{url('assets/backend/libs/webuploader-0.1.5/Uploader.swf')}}',
		    server: postImgUrl,
		    pick: '#couponBgPicker',
		    fileVal: 'upload_image',
		    accept: {
			    title: 'Images',
			    extensions: 'jpg,jpeg,png',
			    mimeTypes: 'image/jpg,image/jpeg,image/png'
		    }
	    });
	    // 文件上传成功，给item添加成功class, 用样式标记上传成功。
	    couponBgUploader.on('uploadSuccess', function (file, response) {
		    $('#coupon_bg_img img').attr("src", response.url);
		    $('#coupon_bg_img input').val(response.url);
	    });
    });
</script>