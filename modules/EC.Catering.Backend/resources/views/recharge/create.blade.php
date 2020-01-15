{!! Html::style(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.min.css') !!}

<div class="tabs-container">
    @if (session()->has('flash_notification.message'))
        <div class="alert alert-{{ session('flash_notification.level') }}">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            {!! session('flash_notification.message') !!}
        </div>
    @endif

    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#tab-1" aria-expanded="true"> 创建储值规则</a></li>
    </ul>

    <div class="tab-content">
        <div id="tab-1" class="tab-pane active">
            <div class="panel-body">
                <div class="row">
                        {!! Form::open( [ 'url' => [route('admin.users.recharge.store')], 'method' => 'POST', 'id' => 'create-suit-form','class'=>'form-horizontal'] ) !!}
                    <div class="form-group">
                            <label class="col-sm-2 control-label">*储值规则名称:</label>
                            <div class="col-sm-8">
                                <input type="hidden" class="form-control" name="type" value="gift_recharge" />
                                <input type="text" class="form-control" name="name" placeholder=""/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">副标题(前端显示):</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="title" placeholder="" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">*实付金额(元):</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control num" name="payment_amount" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">*到账金额(元):</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control num" name="amount" placeholder="" />
                            </div>
                        </div>
                        <input type="hidden" name="open_coupon" value="0">
                        <input type="hidden" name="coupon" value="">
                    {{--<div class="form-group">
                        <label class="col-sm-2 control-label">赠送优惠券：</label>
                        <div class="col-sm-10">
                            <label class="checkbox-inline i-checks"><input name="open_coupon" type="radio"
                                                                           value="1" >
                                是</label>
                            <label class="checkbox-inline i-checks"><input name="open_coupon" type="radio"
                                                                           value="0" checked>否</label>
                        </div>
                    </div>--}}


                    {{--<div class="form-group" style="display: none">
                        <label class="col-sm-2 control-label"></label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="coupon_title" name="coupon_title" placeholder="请输入进行中的优惠券名称搜索" />
                        </div>
                        <div class="col-sm-2">
                           <a href="javascript:;" class="btn btn-w-m btn-info" id="search" >搜索</a>
                        </div>
                    </div>--}}


                    {{--<div class="form-group">
                        <label class="col-sm-2 control-label"></label>
                        <div class="col-sm-4">
                            <select class="form-control" name="coupon" id="select_coupon" >
                                <option id="option_coupon" value="">请选择优惠券</option>
                            </select>
                        </div>

                    </div>--}}

                    {{--<div class="form-group">
                        <label class="col-sm-2 control-label">赠送积分：</label>
                        <div class="col-sm-10">
                            <label class="checkbox-inline i-checks"><input name="open_point" type="radio"
                                                                           value="1" checked>
                                是</label>
                            <label class="checkbox-inline i-checks"><input name="open_point" type="radio"
                                                                           value="0" > 否</label>
                        </div>
                    </div>--}}
                    {{--<div class="form-group">
                        <label class="col-sm-2 control-label">赠送积分：</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control num" name="point" placeholder="输入赠送积分数目" value="0" />
                        </div>
                    </div>--}}
                    <input type="hidden" name="point" value="0">
                    <div class="form-group">
                            <label class="col-sm-2 control-label">*开启状态：</label>
                            <div class="col-sm-10">
                                <label class="checkbox-inline i-checks"><input name="status" type="radio" value="1">是</label>
                                <label class="checkbox-inline i-checks"><input name="status" type="radio" value="0" checked> 否</label>
                            </div>
                     </div>
                    <input type="hidden" name="sort" value="1">
                    {{--<div class="form-group">
                        <label class="col-sm-2 control-label">*排序：</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control num" name="sort" placeholder="" value="1"  required="required" />
                        </div>
                    </div>--}}

                    <div class="form-group">
                        {!! Form::label('pic', '背景图', ['class' => 'col-sm-2 control-label']) !!}

                        <div class="col-sm-10">
                            <input type="hidden" name="img">
                            <img class="shop_show_logo"
                                 src="/assets/backend/images/no-image.jpg" alt=""
                                 style="max-width: 200px;">
                            <div id="logoPicker">选择图片</div>
                            <div class="clearfix"></div>
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
{!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.form.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.zh-CN.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/webuploader-0.1.5/webuploader.js') !!}
<script>

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
				    location = '{{route('admin.users.recharge.index')}}';
			    });
		    }

	    }
    });

    var logoPicker = WebUploader.create({
        auto: true,
        swf: '{{url(env("APP_URL").'/assets/backend/libs/webuploader-0.1.5/Uploader.swf')}}',
        server: '{{route('file.upload',['_token'=>csrf_token()])}}',
        pick: '#logoPicker',
        fileVal: 'file',
        accept: {
            title: 'Images',
            extensions: 'gif,jpg,jpeg,bmp,png',
            mimeTypes: 'image/*'
        }
    });

    logoPicker.on('uploadSuccess', function (file, response) {
        var img_url = response.url;

        $('input[name="img"]').val(img_url);
        $('.shop_show_logo').attr('src', img_url);
    });
</script>