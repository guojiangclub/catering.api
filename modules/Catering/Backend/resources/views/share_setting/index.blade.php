<div class="tabs-container">
    <form method="post" action="{{route('admin.shitang.setting.save')}}" class="form-horizontal" id="setting_site_form">
        {{csrf_field()}}
        <div class="tab-content">
            <div class="tab-pane active" id="tab_1">
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">小程序分享文案设置：</label>
                        <div class="col-sm-4">
                            <input type="text" name="h5-home-page-share-title" class="form-control"
                                   value="{{ settings('h5-home-page-share-title')?settings('h5-home-page-share-title'):''}}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">小程序分享图片设置：</label>
                        <div class="col-sm-4">
                            <div class="pull-left" id="homepage-swal-img">
                                <img src="{{ settings('h5-home-page-share-logo') ? settings('h5-home-page-share-logo') : "/assets/backend/images/backgroundImage/pictureBackground.png" }}"
                                     class="member_shop_logo" width="182px" style="margin-right: 23px;">
                                <input type="hidden" name="h5-home-page-share-logo" class="form-control"
                                       value="{{ settings('h5-home-page-share-logo') ? settings('h5-home-page-share-logo')  : '' }}">
                            </div>
                            <div class="clearfix" style="padding-top: 22px;">
                                <div id="homepageSwalImgPicker">添加图片</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">分享者获得优惠券：</label>
                        <div class="col-sm-4">
                            <select class="form-control select_coupon" id="sharer_get_coupon" name="sharer_get_coupon">
                                <option value="">请选择优惠券</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">被分享者获得优惠券：</label>
                        <div class="col-sm-4">
                            <select class="form-control select_coupon" id="sharee_get_coupon" name="sharee_get_coupon">
                                <option value="">请选择优惠券</option>
                            </select>
                        </div>
                    </div>

                    <a href="javascript:;" class="btn btn-w-m  hide" id="search">搜索</a>
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
{!! Html::script('assets/backend/libs/webuploader-0.1.5/webuploader.js') !!}

<script>
    var coupon_api = "{{route('admin.shitang.directional.coupon.api.coupon',['status'=>'ing'])}}"
    var sharer_get_coupon ={{settings('sharer_get_coupon')?settings('sharer_get_coupon'):0}};
    var sharee_get_coupon ={{settings('sharee_get_coupon')?settings('sharee_get_coupon'):0}};
</script>

<script>
    $('#search').click(function () {
        var url = coupon_api;
        $('.select_coupon').html('');
        $.get(url, function (res) {
            if (res.status) {
                var data = res.data;
                var html = "<option value=''>" + "请选择优惠券" + "</option>";
                var html_sharee = "<option value=''>" + "请选择优惠券" + "</option>";

                if (data.length > 0) {
                    $.each(data, function (k, v) {
                        if (sharer_get_coupon == v.id) {
                            html += "<option value=" + v.id + " selected>" + v.title + "</option>";
                        } else {
                            html += "<option value=" + v.id + ">" + v.title + "</option>";
                        }

                        if (sharee_get_coupon == v.id) {
                            html_sharee += "<option value=" + v.id + " selected>" + v.title + "</option>";
                        } else {
                            html_sharee += "<option value=" + v.id + ">" + v.title + "</option>";
                        }

                    })
                } else {
                    html = "<option value=''>" + "无进行中的优惠券" + "</option>";
                    html_sharee = "<option value=''>" + "无进行中的优惠券" + "</option>";
                }
                $('#sharer_get_coupon').html(html);
                $('#sharee_get_coupon').html(html_sharee);
            }
        })
    });
    $('#search').trigger("click");


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
    });

    $(document).ready(function () {
        var postImgUrl = '{{route('upload.image',['_token'=>csrf_token()])}}';
        var homepageUploader = WebUploader.create({
            auto: true,
            swf: '{{url('assets/backend/libs/webuploader-0.1.5/Uploader.swf')}}',
            server: postImgUrl,
            pick: '#homepageSwalImgPicker',
            fileVal: 'upload_image',
            accept: {
                title: 'Images',
                extensions: 'jpg,jpeg,png',
                mimeTypes: 'image/jpg,image/jpeg,image/png'
            }
        });
        // 文件上传成功，给item添加成功class, 用样式标记上传成功。
        homepageUploader.on('uploadSuccess', function (file, response) {
            $('#homepage-swal-img img').attr("src", response.url);
            $('#homepage-swal-img input').val(response.url);
        });
    });

</script>