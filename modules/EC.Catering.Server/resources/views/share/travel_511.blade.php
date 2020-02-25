<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate"/>
    <meta http-equiv="pragma" content="no-cache"/>
    <meta http-equiv="expires" content="0"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="format-detection" content="telephone=no"/>
    <meta name="format-detection" content="email=no"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, shrink-to-fit=no">
    <title>首页</title>
    <style rel="stylesheet">
        body,
        html {
            padding: 0;
            margin: 0;
            width: 100%;
            height: 100%;
        }

        #fiveShare {
            width: 100%;
        }

        #fiveShare .header {
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
            padding: 15px 25px;
            background-color: #f76106;
        }

        #fiveShare .header .item-left {
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -webkit-align-items: center;
            -ms-flex-align: center;
            align-items: center;
        }

        #fiveShare .header .item-left .advtar {
            width: 30px;
            height: 30px;
        }

        #fiveShare .header .item-left .advtar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            vertical-align: middle;
        }

        #fiveShare .header .item-left .nick-name {
            font-size: 16px;
            color: #FFFFFF;
            margin-left: 10px;
        }

        #fiveShare .header .item-right {
            /* width: 180px; */
            height: 25px;
            line-height: 25px;
            background-color: #000000;
            border-radius: 25px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            max-width: 190px;
        }

        #fiveShare .header .item-right span {
            display: inline-block;
            margin: 0 5px;
            font-size: 12px;
            color: #FFFFFF;
        }

        #fiveShare .imgs {
            background-color: #f76106;
            padding: 0 25px 18px 25px;
            width: 100%;
            box-sizing: border-box;
            -webkit-box-sizing: border-box;
        }

        #fiveShare .photo {
            width: 100%;
            height: 367px;
            background: url("{{ $content->img_list[0] }}") no-repeat;
            background-size: cover;
            background-position: center;
        }

        #fiveShare .text-detail {
            background-color: #f76106;
        }

        #fiveShare .text-detail .descrbie {
            margin: 0 25px;
            padding: 8px 14px;
            border-radius: 14px;
            border: 1px solid #513e25;
            font-size: 16px;
            color: #000000;
        }

        #fiveShare .text-detail .logo {
            margin: 0 25px;
            text-align: center;
            position: relative;
            top: 17px;
            left: 0;
        }

        #fiveShare .text-detail .logo img {
            width: 80%;
        }

        #fiveShare .content {
            background-color: #513e25;
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
            padding: 25px 30px 10px 25px;
        }

        #fiveShare .content .code {
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -webkit-align-items: center;
            -ms-flex-align: center;
            align-items: center;
            -webkit-box-pack: center;
            -webkit-justify-content: center;
            -ms-flex-pack: center;
            justify-content: center;
        }

        #fiveShare .content .code img {
            width: 85px;
            height: 85px;
            border-radius: 50%;
        }

        #fiveShare .content .code .txt {
            color: #FFFFFF;
            font-size: 12px;
            padding-left: 10px;
        }

        #fiveShare .content .small-logo {
            width: 52px;
            height: 54px;
        }

        #fiveShare .content .small-logo img {
            width: 100%;
            height: 100%;
        }

    </style>
</head>
<body>
<div id="fiveShare">
    <div class="header">
        <div class="item-left">
            <div class="advtar">
                <img src="{{ $content->avatar }}" alt="">
            </div>
            <div class="nick-name">{{ $content->nick_name }}</div>
        </div>
        <div class="item-right">
            @foreach($tags as $item)
                <span>{{$item}}</span>
            @endforeach
        </div>
    </div>
    <div class="imgs">
        <div class="photo">
        </div>
    </div>

    <div class="text-detail">
        <div class="descrbie">
            {{ $content->description }}
        </div>
        <div class="logo">
            <img src="http://ibrand-miniprogram.oss-cn-hangzhou.aliyuncs.com/18-11-6/65388483.jpg" alt="">
        </div>
    </div>
    <div class="content">
        <div class="code">
            <img src="{{ $mini_code }}" alt="">
            <div class="txt">我在{{settings('other_built_sns_title')}}发布了内容
                <div>
                    扫码为我点赞！
                </div>
            </div>
        </div>
        <div class="small-logo">
            <img src="http://ibrand-miniprogram.oss-cn-hangzhou.aliyuncs.com/18-11-6/11881946.jpg" alt="">
        </div>
    </div>
</div>
</body>
</html>