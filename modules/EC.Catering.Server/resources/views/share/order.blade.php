<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="format-detection" content="telephone=no"/>
    <meta name="format-detection" content="email=no"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, shrink-to-fit=no">
    <title>订单分享</title>
</head>

<style type="text/css">
    @font-face {
        font-family: 'MILANTING--GBK1-Light';
        font-weight: normal;
        font-style: normal;
    }
    body {
        font-family:"MILANTING--GBK1-Light" !important;
    }
    .img-box {
        background: #F6F9FC;
        padding: 15px 0px;
    }
    .img-box .user-info {
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-align: center;
        -webkit-align-items: center;
        -ms-flex-align: center;
        align-items: center;
        align-items: center;
        padding: 0 15px;
    }
    .img-box .user-info .img {
        width: 30px;
        height: 30px;
        border-radius: 100%;
        overflow: hidden;
    }
    .img-box .user-info .img img {
        width: 100%;
        height: 100%;
    }
    .img-box .user-info .name {
        margin-left: 20px;
        font-size: 12px;
        color: #909090;
    }
    .img-box .goods-info {
        position: relative;
        padding: 0 15px;
        margin-top: 10px;
    }
    .img-box .goods-info img {
        width: 100%;
    }
    .img-box .goods-info .text {
        position: absolute;
        bottom: -80px;
        background: #FFFFFF;
        padding: 10px 15px;
        left: 15px;
        right: 15px;
        box-shadow: 0px 4px 8px 2px rgba(0, 0, 0, 0.1);
    }
    .img-box .goods-info .text .good-name-info {
        /*padding: 10px 15px;*/

        padding: 10px 0;
        border-bottom: 1px solid #E1E2EA;
    }
    .img-box .goods-info .text .good-name-info .goods-name {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }
    .img-box .goods-info .text .good-name-info .num-box {
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-align: center;
        -webkit-align-items: center;
        -ms-flex-align: center;
        align-items: center;
        -webkit-box-pack: justify;
        -webkit-justify-content: space-between;
        -ms-flex-pack: justify;
        justify-content: space-between;
        font-size: 13px;
    }
    .img-box .goods-info .text .good-name-info .num-box .price {
        font-size: 15px;
        color: #E42B1E;
    }
    .img-box .goods-info .text .success {
        font-size: 14px;
        color: #909090;
        padding: 10px 0;
    }
    .img-box .goods-info .text .success .ok {
        font-size: 18px;
        color: #1AAD19;
    }
    .code-info {
        background: #FFFFFF;
        padding: 80px 15px 20px 15px;
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-align: center;
        -webkit-align-items: center;
        -ms-flex-align: center;
        align-items: center;
        align-items: center;
    }
    .code-info .code {
        width: 100px;
        height: 100px;
    }
    .code-info .code img {
        width: 100%;
        height: 100%;
    }
    .code-info .info {
        margin-left: 10px;
        color: #666666;
        font-size: 12px;
    }
</style>

<body style="margin: 0; padding: 0">
<div class="img-box">
    <div class="user-info">
        <div class="img">
             @if($order['avatar'])
             <img src="{{$order['avatar']}}" alt="">
             @else
                <img src="http://img.alicdn.com/tps/TB1ld1GNFXXXXXLapXXXXXXXXXX-200-200.png" alt="">
            @endif

        </div>
        <div class="name">
                    <span>
                       {{$order['accept_name']}}
                    </span>
            的购物清单
        </div>
    </div>
    <div class="goods-info">

        <img src="{{isset($order['items'][0]['item_meta']['image'])?$order['items'][0]['item_meta']['image']:''}}" alt="">

        <div class="text">
            <div class="good-name-info">
                <div class="goods-name" style="margin-bottom: 5px">
                    @if(isset($order['items'][0]['item_name']))
                          {{$order['items'][0]['item_name']}}
                    @endif
                </div>
                <div class="num-box">
                    <div class="num">
                        共{{$order['count']}}件
                    </div>
                    <div class="price">
                        <span style="font-family: 'Microsoft Yahei, Tahoma, Arial, Helvetica, sans-serif'">￥</span> {{$order['total_yuan']}}
                    </div>
                </div>
            </div>

            <div class="success">
                <div class="ok">


                    支付完成，货物整装待


                </div>
                <div style="margin-top: 10px">
                    购同款，买好物，就在 米尔商城
                </div>
            </div>
        </div>

    </div>
</div>

<div class="code-info">
    <div class="code">

        <img src="{{asset('storage/share_order/mini/qrcode/'.$order_no.'_share_order_mini_qrcode.jpg')}}" alt="">

    </div>

    <div class="info">
        <div>
            长按识别，看看我都买了什么
        </div>
        <div>
            分享自 米尔商城
        </div>
    </div>
</div>
</body>
</html>