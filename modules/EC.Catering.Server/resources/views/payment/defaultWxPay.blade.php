<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>支付</title>
    <script type="text/javascript">
        var is_low = navigator.userAgent.toLowerCase().indexOf('android') != -1;
        var _ww = (window.screen.availWidth * (window.devicePixelRatio || 1.5) / 1);
        if (is_low && _ww < 720) {
            document.writeln('<meta name="viewport" content="width=640px,target-densitydpi=device-dpi,user-scalable=yes,initial-scale=0.5" />');
        } else {
            document.writeln('<meta name="viewport" content="width=640px,target-densitydpi=device-dpi,user-scalable=no" />');
        }
    </script>
    <script type="text/javascript" src="https://res.wx.qq.com/open/js/jweixin-1.3.0.js"></script>

</head>


<body>
<script type="text/javascript">

    function onBridgeReady() {

        var ret = {!! json_encode($charge) !!};

        WeixinJSBridge.invoke(
            'getBrandWCPayRequest', {
                "appId": ret.wechat.appId,
                "timeStamp": ret.wechat.timeStamp,
                "nonceStr": ret.wechat.nonceStr,
                "package": ret.wechat.package,
                "signType": ret.wechat.signType,
                "paySign": ret.wechat.paySign
            },
            function (res) {
                console.log(res.err_msg);
                if (res.err_msg == "get_brand_wcpay_request:ok") {
                    console.log("orderNo:" + ret.order_no);
                    location = "{{$successUrl}}" + ret.order_no;
                }

                if (res.err_msg == "get_brand_wcpay_request:cancel") {
                    location = "{{$failUcenter}}" + ret.order_no;
                }

                if (res.err_msg == "get_brand_wcpay_request:fail") {
                    location = "{{$failUcenter}}" + ret.order_no;
                }

            }
        )
    }

    if (typeof WeixinJSBridge == "undefined") {
        if (document.addEventListener) {
            document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
        } else if (document.attachEvent) {
            document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
            document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
        }
    } else {
        onBridgeReady();
    }


</script>
</body>
</html>