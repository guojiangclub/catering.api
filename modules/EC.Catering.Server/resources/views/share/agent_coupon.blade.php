<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="format-detection" content="telephone=no"/>
    <meta name="format-detection" content="email=no"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, shrink-to-fit=no">
    <title>Title</title>
    <style rel="stylesheet">
        html,body{
            width: 100%;
            height: 100%;
            padding: 0;
            margin: 0;

        }
        #share {
            width: 100%;
            height: 682px;
            overflow: hidden;
            background: #ffffff;
        }
        #share .top {
            position: relative;
            padding: 20px 15px 0 15px;
            height: 38%;
            background: linear-gradient(315deg, #f54353 0%, #f65d40 100%);
        }
        #share .top .info {
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -webkit-align-items: center;
            align-items: center;
        }
        #share .top .info .avtar {
            width: 35px;
            height: 35px;
        }
        #share .top .info .avtar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
        }
        #share .top .info .name {
            padding-left: 15px;
            font-size: 16px;
            line-height: 22px;
            color: #FFFFFF;
        }
        #share .top .text {
            padding-top: 14px;
            color: rgba(255, 255, 255, 0.6);
            line-height: 16px;
            font-size: 12px;
        }
        #share .top .coupon {
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            position: absolute;
            left: 10px;
            right: 10px;
            bottom: -26%;
            box-shadow: 0px 4px 8px 2px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        #share .top .coupon .left-item {
            width: 110px;
            text-align: center;
            padding: 18px 14px 22px 15px;
            color: #FFFFFF;
            background: url("https://ibrand-miniprogram.oss-cn-hangzhou.aliyuncs.com/%E5%B0%8F%E7%A8%8B%E5%BA%8F/0202.png") no-repeat;
            background-size: 100% 100%;
        }
        #share .top .coupon .left-item .money {
            font-size: 38px;
            line-height: 53px;
        }
        #share .top .coupon .left-item .money span {
            font-size: 25px;
            line-height: 35px;
        }
        #share .top .coupon .left-item .much {
            font-size: 12px;
            line-height: 16px;
        }
        #share .top .coupon .left-item .dot-box {
            position: absolute;
            top: 6px;
            left: -5px;
        }
        #share .top .coupon .left-item .dot-box .dot {
            margin-bottom: 10px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #FFFFFF;
        }
        #share .top .coupon .left-item .dot-box .dot-top {
            margin-bottom: 10px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: linear-gradient(315deg, #f54353 0%, #f65d40 100%);
        }
        #share .top .coupon .right-item {
            background-color: #FFFFFF;
            color: #EA4448;
            line-height: 20px;
            overflow: hidden;
            width: 320px;
           /* flex: 1;
            -webkit-box-flex: 1;
            -ms-flex: 1;*/
        }
        #share .top .coupon .right-item .title {
            font-size: 14px;
            padding: 16px 7px 6px 7px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        #share .top .coupon .right-item .title span {
            font-size: 12px;
            display: inline-block;
            padding: 0 5px;
            border: 1px solid #EA4448;
            border-radius: 3px;
            margin-right: 6px;
        }
        #share .top .coupon .right-item .use {
            color: #EA4448;
            font-size: 14px;
            line-height: 20px;
            padding: 0 7px;
        }
        #share .top .coupon .right-item .time {
            width: 100%;
            color: #9B9B9B;
            font-size: 12px;
            line-height: 16px;
            padding: 13px 7px 0 7px;
        }
        #share .bottom {
            height: 62%;
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-pack: center;
            -webkit-justify-content: center;
            -ms-flex-pack: center;
            justify-content: center;
            -webkit-box-align: center;
            -webkit-align-items: center;
            -ms-flex-align: center;
            align-items: center;
        }
        #share .bottom .code {
            width: 144px;
            height: 144px;
        }
        #share .bottom .code img {
            width: 100%;
            height: 100%;
        }
        #share .bottom .txt {
            padding-left: 26px;
            color: #666666;
            font-size: 12px;
            line-height: 16px;
        }
        #share .bottom .txt .from {
            padding-top: 6px;
        }

    </style>
</head>
<body>
<div id="share">
    <div class="top">
        <div class="info">
            <div class="avtar">

                @if($user->avatar)
                    <img src="{{$user->avatar}}" alt="">
                @else
                    <img src="http://img.alicdn.com/tps/TB1ld1GNFXXXXXLapXXXXXXXXXX-200-200.png" alt="">
                @endif

            </div>
            <div class="name">{{$user->nick_name}}</div>
        </div>
        <div class="text">向你分享了一张超值的优惠券</div>
        <div class="coupon">
            <div class="left-item">
                <div class="money">

                    @if($coupon->action_type['type']=='cash')
                    <span>¥</span>
                    @endif

                        @if($coupon->action_type['type']=='discount')
                            <span>折</span>
                        @endif
                        {{$coupon->action_type['value']}}
                </div>
                <div class="much">{{$coupon->label}}</div>
            </div>
            <div class="right-item">
                <div class="title">

                    @if($coupon->channel=='ec')
                        <span>商城</span>

                    @else
                        <span>门店</span>
                    @endif


                        {{$coupon->title}}

                </div>

                <div class="time">{{$coupon->use_start_time}}-{{$coupon->use_end_time}}</div>
            </div>
        </div>
    </div>
    <div class="bottom">
        <div class="code">
            <img src="{{asset('storage/agent_coupon/mini/qrcode/'.$scene.'_agent_coupon_mini_qrcode.jpg')}}" alt="">
        </div>
        <div class="txt">
            <div>长按识别，领取优惠券</div>
            <div class="from">分享自 米尔商城</div>
        </div>
    </div>
</div>
</body>
</html>