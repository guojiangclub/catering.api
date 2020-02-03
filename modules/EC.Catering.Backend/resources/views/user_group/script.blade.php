<script>
    $('#rights_ids').select2({
	    placeholder: '请选择会员权益',
	    allowClear: true
    });

    $('#base-form').ajaxForm({
	    success: function (result) {
		    if (result.error_code == 1) {
			    swal({
				    title: result.error,
				    text: "",
				    type: "warning"
			    });
		    } else {
			    swal({
				    title: "编辑成功！",
				    text: "",
				    type: "success"
			    }, function () {
				    location = '{{route('admin.users.grouplist')}}';
			    });
		    }


	    }
    });

    var logoPicker = WebUploader.create({
	    auto: true,
	    swf: '{{url(env("APP_URL").'/assets/backend/libs/webuploader-0.1.5/Uploader.swf')}}',
	    server: '{{route('upload.image',['_token'=>csrf_token()])}}',
	    pick: '#logoPicker',
	    fileVal: 'upload_image',
	    accept: {
		    title: 'Images',
		    extensions: 'gif,jpg,jpeg,bmp,png',
		    mimeTypes: 'image/*'
	    }
    });

    logoPicker.on('uploadSuccess', function (file, response) {
	    var img_url = response.url;

	    $('input[name="pic"]').val(img_url);
	    $('.shop_show_logo').attr('src', img_url);
    });
</script>