<div class="ibox float-e-margins">

    <div class="ibox-content" style="display: block;">
        <div class="row">

            <div class="panel-body">
                {!! Form::open( [ 'url' => [route('admin.shitang.shop.clerk.update', ['clerk_id'=>$clerk->id])], 'method' => 'POST', 'id' => 'clerk-form','class'=>'form-horizontal'] ) !!}

                <div class="form-group">
                    <label class="col-sm-2 control-label">接收统计模板消息：</label>
                    <div class="col-sm-10">
                        <label class="checkbox-inline i-checks"><input name="receive_template_message" type="radio" value="1" class="receive_template_message_true" @if($clerk->receive_template_message==1) checked @endif>
                            是</label>
                        <label class="checkbox-inline i-checks"><input name="receive_template_message" type="radio" value="0" class="receive_template_message_false" @if($clerk->receive_template_message==0) checked @endif> 否</label>
                    </div>
                </div>

                @if(isset($clerkBind) && !is_null($clerkBind))
                    <input type="hidden" value="{{ $clerkBind->openid }}" name="openid" id="openid">
                @else
                    <input type="hidden" value="" name="openid" id="openid">
                @endif

                <div class="form-group bind_wechat_group" style="display: {{ $clerk->receive_template_message==1 ? 'block' : 'none' }};">
                    <label class="col-sm-2 control-label">头像：</label>
                    <div class="col-lg-10">
                        <div class="pull-left" id="userAvatar">
                            @if(isset($clerkBind) && !is_null($clerkBind))
                                <img src="{{ $clerkBind->headimgurl }}" style="margin-right: 23px;width: 100px;height: 100px;border-radius: 50px;">
                            @else
                                <img src="/assets/backend/admin/img/no_head.png" style="margin-right: 23px;width: 100px;height: 100px;border-radius: 50px;">
                            @endif
                        </div>
                        <div class="clearfix" style="padding-top: 22px;">
                            <a class="btn btn-success bind_wechat">绑定微信</a>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">*手机：</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="mobile" placeholder="" required="required" value="{{$clerk->mobile}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">*密码：</label>
                    <div class="col-sm-8">
                        <input type="password" class="form-control" name="password" placeholder="" value="{{$clerk->password}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">*确认密码：</label>
                    <div class="col-sm-8">
                        <input type="password" class="form-control" name="password_confirmation" placeholder="" value="{{$clerk->password}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">*工号：</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="clerk_no" placeholder="" value="{{$clerk->clerk_no}}" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">*姓名：</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="name" placeholder="" value="{{$clerk->name}} " />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">昵称：</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="nick_name" placeholder="" value="{{$clerk->nick_name}}" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">邮箱：</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="email" placeholder="" value="{{$clerk->email}}" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">*启用：</label>
                    <div class="col-sm-10">
                        <label class="checkbox-inline i-checks"><input name="status" type="radio" value="1" @if($clerk->status==1) checked @endif> 是</label>
                        <label class="checkbox-inline i-checks"><input name="status" type="radio" value="0" @if($clerk->status==0) checked @endif> 否</label>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-sm-2 control-label">店长：</label>
                    <div class="col-sm-10">
                        <label class="checkbox-inline i-checks"><input name="is_clerk_owner" type="radio" value="1" @if($clerk->is_clerk_owner==1) checked @endif> 是</label>
                        <label class="checkbox-inline i-checks"><input name="is_clerk_owner" type="radio" value="0" @if($clerk->is_clerk_owner==0) checked @endif> 否</label>
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