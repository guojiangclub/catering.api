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
        <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">智能营销</a></li>
    </ul>
    <form method="post" action="{{route('admin.shitang.setting.save')}}" class="form-horizontal" id="setting_site_form">
        {{csrf_field()}}
        <input type="hidden" name="setting_type" value="discount_coupon_rules">
        <div class="tab-content">
            <div class="tab-pane active" id="tab_1">
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">是否开启智能营销：</label>
                        <div class="col-sm-10">
                            <label class="checkbox-inline i-checks">
                                <input name="enabled_auto_marketing" type="radio" value="1" @if($enabled_auto_marketing==1) checked @endif>是</label>
                            <label class="checkbox-inline i-checks">
                                <input name="enabled_auto_marketing" type="radio" value="0" @if(!$enabled_auto_marketing) checked @endif>否</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">营销规则：</label>
                        <div class="col-sm-6">
                            <div style="padding: 10px; background: rgb(239, 239, 239); margin: 10px 0px;">
                                <div class="deliveryArea">
                                    <div class="ruleArea">
                                        @if(!empty($discount_coupon_rules))
                                            @foreach($discount_coupon_rules as $key => $value)
                                                <div class="coupon-item coupon-item{{$key}} clearfix division"><div class="span-auto"><label>最近一次消费后：</label><input style="width:40px;" type="text" name="discount_coupon_rules[{{$key}}][days]" value="{{$value['days']}}" class="x-input"> 天 </div><label class="span-auto"> &nbsp;&nbsp;|&nbsp;&nbsp; </label><div class="span-auto"><label>送优惠券：</label><span class="coupon-{{$key}}">{{ $value['couponName'] }}</span></div><input type="hidden" name="discount_coupon_rules[{{$key}}][couponName]" value="{{$value['couponName']}}"><input type="hidden" name="discount_coupon_rules[{{$key}}][couponCode]" value="{{$value['couponCode']}}"><div class="frt"><span class="lnk delete-coupon-item" data-id="{{$key}}">删除</span></div></div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <span class="lnk add-new-rule">[+]添加一条</span>
                                </div>
                            </div>
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

    $('.add-new-rule').on('click', function () {
	    var date = new Date();
	    var time = date.getTime();
	    var rand = Math.ceil(Math.random() * 100000);
	    var key = time.toString() + rand.toString();

	    var tpl = '<div class="coupon-item coupon-item' + key + ' clearfix division"><div class="span-auto"><label>最近一次消费后：</label><input style="width:40px;" type="text" name="discount_coupon_rules[' + key + '][days]" value="0" class="x-input"> 天 </div><label class="span-auto"> &nbsp;&nbsp;|&nbsp;&nbsp; </label><div class="span-auto"><label>送优惠券：</label><span class="coupon-' + key + '"></span><a class="lnk regionSelect' + key + '" data-id="' + key + '" data-toggle="choose_coupon_modal" data-target="#coupon_modal"> 选择优惠券 </a></div><input type="hidden" name="discount_coupon_rules[' + key + '][couponName]" value=""><input type="hidden" name="discount_coupon_rules[' + key + '][couponCode]" value=""><div class="frt"><span class="lnk delete-coupon-item" data-id="' + key + '">删除</span></div></div>';

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
		    $('.coupon-item' + id).remove();
	    });
    });

    $(document).on('click.modal.data-api', '[data-toggle="choose_coupon_modal"]', function (e) {
	    var $this = $(this);
	    var modalUrl = '{{ route('admin.shitang.coupon.list.modal') }}';

	    var id = $(this).data('id');

	    modalUrl = modalUrl + '?id=' + id;

	    if (modalUrl) {
		    var $target = $($this.attr('data-target'));
		    $target.modal('show');
		    $target.html('').load(modalUrl, function () {

		    });
	    }
    });
</script>