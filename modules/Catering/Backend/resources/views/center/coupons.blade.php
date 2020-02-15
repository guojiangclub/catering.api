@extends('catering-backend::layouts.bootstrap_modal')

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
                            <input type="radio" value="{{ $coupon->code }}" data-id="{{ $coupon->id }}" data-name="{{ $coupon->title }}" name="coupon_code" class="coupon_code"> {{ $coupon->title }}
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


        $('#choose_coupon').on('click', function () {
	        var choose_coupon_code = $('.coupon_code:checked').val();
	        var choose_coupon_name = $('.coupon_code:checked').data('name');
	        var choose_coupon_id = $('.coupon_code:checked').data('id');

	        if (typeof choose_coupon_code == 'undefined' || typeof choose_coupon_name == 'undefined' || typeof choose_coupon_id == 'undefined') {
		        swal('请选择优惠券', '', 'warning');
		        return false;
	        }

	        if ($.inArray(choose_coupon_code, coupon_code) !== -1) {
		        swal(choose_coupon_name + '已选择，请重新选择', '', 'warning');
		        return false;
	        }

	        coupon_code.push(choose_coupon_code);
	        var key = $("input[name='key']:hidden").val();
	        $('.coupon-' + key).text(choose_coupon_name);
	        $("input[name='discount_coupon_rules[" + key + "][couponName]']:hidden").val(choose_coupon_name);
	        $("input[name='discount_coupon_rules[" + key + "][couponCode]']:hidden").val(choose_coupon_code);
	        $("input[name='discount_coupon_rules[" + key + "][couponId]']:hidden").val(choose_coupon_id);
	        $('.regionSelect' + key).remove();

	        $('#coupon_modal').modal('hide');
        });
    </script>
@stop