<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>支付宝支付</title>
    <script type="text/javascript">
        var is_low = navigator.userAgent.toLowerCase().indexOf('android') != -1;
        var _ww = (window.screen.availWidth * (window.devicePixelRatio || 1.5) / 1);
        if (is_low && _ww < 720) {
            document.writeln('<meta name="viewport" content="width=640px,target-densitydpi=device-dpi,user-scalable=yes,initial-scale=0.5" />');
        } else {
            document.writeln('<meta name="viewport" content="width=640px,target-densitydpi=device-dpi,user-scalable=no" />');
        }
    </script>

    <script type="text/javascript" src="{{url('assets/server/js/pingpp/pingpp.js')}}"></script>

</head>


<body>
<script type="text/javascript">

    var charge = {!! $charge !!}

    pingpp.createPayment(charge, function (result, err) {
        console.log(result);
        console.log(err.msg);
        console.log(err.extra);
        if (result == "success") {
            // 只有微信公众账号 wx_pub 支付成功的结果会在这里返回，其他的支付结果都会跳转到 extra 中对应的 URL。
            location = '{{settings('wechat_pay_success_url')}}' + ret.metadata.order_sn;
        } else if (result == "fail") {
            // charge 不正确或者微信公众账号支付失败时会在此处返回
            jx.t.alert('支付失败', function () {
                location = '{{settings('recharge_wechat_pay_fail_url')}}';
            });
        } else if (result == "cancel") {
            // 微信公众账号支付取消支付
            location = '{{settings('recharge_wechat_pay_fail_url')}}';
        }
    });

</script>
</body>
</html>