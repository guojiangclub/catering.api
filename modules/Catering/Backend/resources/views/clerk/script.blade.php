<div id="bind_modal" class="modal fade bs-example-modal-sm" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" style="text-align: center">请扫描二维码绑定微信号</h4>
            </div>
            <div class="modal-body">
                <p style="text-align: center">
                    <img class="qr_code_url" width="200" src="{{ $qr_code_url }}">
                </p>
            </div>
            <div class="modal-footer">
                <p class="notice_error_message" style="text-align: center">
                </p>
            </div>
        </div>
    </div>
</div>
<script>

    $('#clerk-form').ajaxForm({
	    success: function (result) {
		    if (!result.status) {
			    swal("保存失败!", result.message, "error")
		    } else {
			    swal({
				    title: "保存成功！",
				    text: "",
				    type: "success"
			    }, function () {
				    location = '{{route('admin.shitang.shop.clerk')}}?status=1';
			    });
		    }

	    }
    });

    $('.receive_template_message_true').on('ifChecked', function () {
	    $('.bind_wechat_group').show();
    });

    $('.receive_template_message_false').on('ifChecked', function () {
	    $('.bind_wechat_group').hide();
    });


    var timers;

    $('.bind_wechat').on('click', function () {
	    var code_url = '{{ $qr_code_url }}';
	    if (!code_url) {
		    swal("提示", "请生成店员微信绑定二维码", "warning");
		    return false;
	    }

	    $('#bind_modal').modal();
    });

    $('#bind_modal').on('shown.bs.modal', function () {
	    timers = setInterval(bindWeChat, 2000);
    });

    $('#bind_modal').on('hidden.bs.modal', function () {
	    clearInterval(timers);
    });

    function bindWeChat() {
	    $.ajax({
		    type: "POST",
		    dataType: 'json',
		    url: "{{ route('admin.shitang.shop.clerk.bind.wechat') }}",
		    success: function (res) {
			    if (res.status) {
				    if (res.data.headimgurl && res.data.nick_name && res.data.openid) {
					    $('#userAvatar').children('img').attr('src', res.data.headimgurl);
					    $('[name="nick_name"]').val(res.data.nick_name);
					    $('#openid').val(res.data.openid);
				    }

				    $('#bind_modal').modal('hide');
				    swal({
					    title: '',
					    text: "绑定成功",
					    type: "success",
					    timer: 1000,
					    showConfirmButton: true
				    });
			    }
		    }
	    });
    }
</script>