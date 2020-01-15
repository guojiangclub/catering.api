<script>

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