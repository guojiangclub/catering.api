<div class="ibox float-e-margins">
    <div class="ibox-content" style="display: block;">
        <div class="row">
            <div class="panel-body">
                {!! Form::open( [ 'url' => [route('admin.shitang.shop.clerk.store')], 'method' => 'POST', 'id' => 'clerk-form','class'=>'form-horizontal'] ) !!}

                <div class="form-group">
                    <label class="col-sm-2 control-label">接收统计模板消息：</label>
                    <div class="col-sm-10">
                        <label class="checkbox-inline i-checks"><input name="receive_template_message" type="radio" value="1" class="receive_template_message_true">
                            是</label>
                        <label class="checkbox-inline i-checks"><input name="receive_template_message" type="radio" value="0" class="receive_template_message_false" checked> 否</label>
                    </div>
                </div>

                <input type="hidden" value="" name="openid" id="openid">

                <div class="form-group bind_wechat_group" style="display: none;">
                    <label class="col-sm-2 control-label">头像：</label>
                    <div class="col-lg-10">
                        <div class="pull-left" id="userAvatar">
                            <img src="/assets/backend/admin/img/no_head.png" style="margin-right: 23px;width: 100px;height: 100px;border-radius: 50px;">
                        </div>
                        <div class="clearfix" style="padding-top: 22px;">
                            <a class="btn btn-success bind_wechat">绑定微信</a>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">*手机：</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="mobile" placeholder="" required="required" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">*密码：</label>
                    <div class="col-sm-8">
                        <input type="password" class="form-control" name="password" placeholder="" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">*确认密码：</label>
                    <div class="col-sm-8">
                        <input type="password" class="form-control" name="password_confirmation" placeholder="" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">*工号：</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="clerk_no" placeholder="" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">*姓名：</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="name" placeholder="" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">昵称：</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="nick_name" placeholder="" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">邮箱：</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="email" placeholder="" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">店长：</label>
                    <div class="col-sm-10">
                        <label class="checkbox-inline i-checks"><input name="is_clerk_owner" type="radio" value="1">
                            是</label>
                        <label class="checkbox-inline i-checks"><input name="is_clerk_owner" type="radio" value="0" checked> 否</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">启用：</label>
                    <div class="col-sm-10">
                        <label class="checkbox-inline i-checks"><input name="status" type="radio" value="1" checked>是</label>
                        <label class="checkbox-inline i-checks"><input name="status" type="radio" value="0"> 否</label>
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

        <div id="spu_modal" class="modal inmodal fade"></div>

    </div>
</div>
@include('backend-shitang::clerk.script')
{!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.form.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/formvalidation/dist/js/formValidation.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/formvalidation/dist/js/framework/bootstrap.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/formvalidation/dist/js/language/zh_CN.js') !!}
{!! Html::script('assets/backend/libs/webuploader-0.1.5/webuploader.js') !!}
<script>
    $(document).ready(function () {
	    $('#create-clerk-form').formValidation({
		    framework: 'bootstrap',
		    icon: {
			    valid: 'glyphicon glyphicon-ok',
			    invalid: 'glyphicon glyphicon-remove',
			    validating: 'glyphicon glyphicon-refresh'
		    },
		    fields: {
			    name: {
				    validators: {
					    notEmpty: {
						    message: '请输入姓名'
					    }
				    }
			    },
			    email: {
				    validators: {
					    regexp: {
						    regexp: /^([a-zA-Z0-9._-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/,
						    message: '邮箱格式不错误'
					    }
				    }
			    },
			    mobile: {
				    validators: {
					    notEmpty: {
						    message: '请输入手机'
					    },
					    regexp: {
						    regexp: /^1[34578]\d{9}$/,
						    message: '手机号码错误'
					    }
				    }
			    },
			    clerk_no: {
				    validators: {
					    notEmpty: {
						    message: '请输入工号'
					    }
				    }
			    },
			    password: {
				    validators: {
					    notEmpty: {
						    message: '请输入密码'
					    },
					    regexp: {
						    regexp: /^(?![0-9]+$)(?![a-zA-Z]+$)[0-9a-zA-Z]+$/,
						    message: '密码格式错误,必须包含数字、字母，且不小于6位'
					    }
				    }
			    },
			    password_confirmation: {
				    validators: {
					    identical: {
						    field: 'password',
						    message: '两次输入的密码不一致'
					    }
				    }
			    }
		    }
	    });
    });
</script>