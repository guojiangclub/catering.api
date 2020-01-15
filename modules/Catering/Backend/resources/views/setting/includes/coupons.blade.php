@extends('backend-distribution::layouts.bootstrap_modal')

@section('modal_class')
    modal-lg
@stop
@section('title')
    选择优惠券
@stop

@section('body')
    <div class="row">
        <form class="form-horizontal">
            <input type="hidden" name="key" value="{{ $id }}">
            @foreach($coupons as $coupon)
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="col-sm-2 control-label"></label>
                        <div class="col-sm-4" style="margin-top: 20px;">
                            <input type="radio" value="{{ $coupon->code }}" data-name="{{ $coupon->title }}" name="coupon_code" class="coupon_code"> {{ $coupon->title }}
                        </div>
                    </div>
                </div>
            @endforeach
        </form>
    </div>
@stop

@section('footer')
    <button type="button" class="btn btn-primary" id="choose_coupon">确认</button>

    <script>
        $(function () {
	        $('.form-horizontal').find("input").iCheck({
		        checkboxClass: 'icheckbox_square-green',
		        radioClass: 'iradio_square-green',
		        increaseArea: '20%'
	        });

	        $('.area_type_all').on('ifChecked', function () {
		        $('#postage_area').hide();
		        $('.area-item').remove();
	        });

	        $('.area_type_part').on('ifChecked', function () {
		        $('#postage_area').show();
	        });
        });


        $('body').on('click', '#choose_coupon', function () {
	        var choose_coupon_code = $('.coupon_code:checked').val();
	        var choose_coupon_name = $('.coupon_code:checked').data('name');

	        if (typeof choose_coupon_code == 'undefined' || typeof choose_coupon_name == 'undefined') {
		        swal('请选择优惠券', '', 'warning');
		        return false;
	        }

	        var key = $("input[name='key']:hidden").val();
	        $('.coupon-' + key).text(choose_coupon_name);
	        $("input[name='discount_coupon_rules[" + key + "][couponName]']:hidden").val(choose_coupon_name);
	        $("input[name='discount_coupon_rules[" + key + "][couponCode]']:hidden").val(choose_coupon_code);
	        $('.regionSelect' + key).remove();

	        $('#coupon_modal').modal('hide');
        });
    </script>
@stop