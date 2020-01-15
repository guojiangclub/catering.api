{!! Html::script(env("APP_URL").'/assets/backend/libs/dategrangepicker/moment.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/dategrangepicker/jquery.daterangepicker.js') !!}
<script>
    $('#base-form').ajaxForm({
	    success: function (result) {
		    if (result.status) {
			    swal({
				    title: "保存成功！",
				    text: "",
				    type: "success"
			    }, function () {
				    window.location = '{{ route('admin.shitang.gift.center.index') }}';
			    });
		    } else {
			    swal('保存失败', result.message, 'warning');
		    }


	    }
    });

    var ue = UE.getEditor('container', {
	    autoHeightEnabled: false,
	    initialFrameHeight: 500
    });

    $('#two-inputs').dateRangePicker(
	    {
		    separator: ' to ',
		    time: {
			    enabled: true
		    },
		    language: 'cn',
		    format: 'YYYY-MM-DD HH:mm',
		    inline: true,
		    container: '#date-range12-container',
		    startDate: '{{\Carbon\Carbon::now()}}',
		    showShortcuts:false,
		    getValue: function () {
			    if ($('#date-range200').val() && $('#date-range201').val())
				    return $('#date-range200').val() + ' to ' + $('#date-range201').val();
			    else
				    return '';
		    },
		    setValue: function (s, s1, s2) {
			    $('#date-range200').val(s1);
			    $('#date-range201').val(s2);
		    }
	    });

    $('.add-new-rule').on('click', function () {
	    var date = new Date();
	    var time = date.getTime();
	    var rand = Math.ceil(Math.random() * 100000);
	    var key = time.toString() + rand.toString();

	    var tpl = '<div class="coupon-item coupon-item' + key + ' clearfix division"><div class="span-auto"><label> 优惠券：</label><span class="coupon-' + key + '"></span><a class="lnk regionSelect' + key + '" data-id="' + key + '" data-toggle="choose_coupon_modal" data-target="#coupon_modal"> 选择优惠券 </a></div><input type="hidden" name="discount_coupon_rules[' + key + '][couponName]" value=""><input type="hidden" name="discount_coupon_rules[' + key + '][couponCode]" value=""><input type="hidden" name="discount_coupon_rules[' + key + '][couponId]" value=""><div class="frt"><span class="lnk delete-coupon-item" data-id="' + key + '">删除</span></div></div>';

	    $('.ruleArea').append(tpl);
    });

    $('body').on('click', '.delete-coupon-item', function () {
	    var id = $(this).data('id');

	    swal({
		    title: "确定要删除吗?",
		    text: "",
		    type: "warning",
		    showCancelButton: true,
		    confirmButtonColor: "#DD6B55",
		    confirmButtonText: "确认",
		    cancelButtonText: '取消',
		    closeOnConfirm: true
	    }, function () {
		    var current_coupon_code = $("input[name='discount_coupon_rules[" + id + "][couponCode]']:hidden").val();
		    var code_index = $.inArray(current_coupon_code, coupon_code);
		    coupon_code.splice(code_index, 1);
		    $('.coupon-item' + id).remove();
	    });
    });

    $(document).on('click.modal.data-api', '[data-toggle="choose_coupon_modal"]', function (e) {
	    var $this = $(this);
	    var modalUrl = '{{ route('admin.shitang.gift.center.coupon.list') }}';

	    var id = $(this).data('id');

	    modalUrl = modalUrl + '?id=' + id;

	    if (modalUrl) {
		    var $target = $($this.attr('data-target'));
		    $target.modal('show');
		    $target.html('').load(modalUrl, function () {

		    });
	    }
    });

    var postImgUrl = '{{route('upload.image',['_token'=>csrf_token()])}}';

    var loginUploader = WebUploader.create({
	    auto: true,
	    swf: '{{url('assets/backend/libs/webuploader-0.1.5/Uploader.swf')}}',
	    server: postImgUrl,
	    pick: '#ActivityBannerPicker',
	    fileVal: 'upload_image',
	    accept: {
		    title: 'Images',
		    extensions: 'jpg,jpeg,png',
		    mimeTypes: 'image/jpg,image/jpeg,image/png'
	    }
    });
    // 文件上传成功，给item添加成功class, 用样式标记上传成功。
    loginUploader.on('uploadSuccess', function (file, response) {
	    $('#activity_banner img').attr("src", response.url);
	    $('#activity_banner input').val(response.url);
    });
</script>