<div class="ibox float-e-margins">
    <div class="ibox-content" style="display: block;">
        {!! Form::open(['route' => ['admin.user.change-password', $user->id],
        'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post',
        'id'=>'change-password-form']) !!}

        <div class="form-group">
            <label class="col-lg-2 control-label">密码</label>
            <div class="col-lg-10">
                {!! Form::password('password', ['class' => 'form-control']) !!}
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-2 control-label">确认密码</label>
            <div class="col-lg-10">
                {!! Form::password('password_confirmation', ['class' => 'form-control']) !!}
            </div>
        </div>

        <div class="hr-line-dashed"></div>

        <div class="form-group">
            <label class="col-lg-2 control-label"></label>
            <div class="col-lg-10">
                <input type="submit" class="btn btn-success" value="保存"/>
                <a href="{{route('admin.users.index')}}" class="btn btn-danger">取消</a>
            </div>
        </div>


        {!! Form::close() !!}
    </div>
</div>
{!! Html::script(env("APP_URL").'/assets/backend/libs/formvalidation/dist/js/formValidation.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/formvalidation/dist/js/framework/bootstrap.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/formvalidation/dist/js/language/zh_CN.js') !!}

{!! Html::script('assets/backend/libs/jquery.form.min.js') !!}
<script>
	$(document).ready(function () {
		$('#change-password-form').formValidation({
			framework: 'bootstrap',
			icon: {
				valid: 'glyphicon glyphicon-ok',
				invalid: 'glyphicon glyphicon-remove',
				validating: 'glyphicon glyphicon-refresh'
			},
			fields: {
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
		})
	});
</script>