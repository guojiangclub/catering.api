<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate"/>
    <meta http-equiv="pragma" content="no-cache"/>
    <meta http-equiv="expires" content="0"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="format-detection" content="telephone=no"/>
    <meta name="format-detection" content="email=no"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, shrink-to-fit=no">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_285736_djlywh3u0us.css">
    <title>Title</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
        }

        #sharepage {
            background-color: #58BDA9;
        }

        #sharepage .head {
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
            height: 50px;
            padding: 0 15px;
            color: #FFFFFF;
            -webkit-border-radius: 5px 0 5px 0;
            border-radius: 5px 0 5px 0;
        }

        #sharepage .head .left-box {
            font-size: 15px;
            color: #FFFFFF;
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -webkit-align-items: center;
            -ms-flex-align: center;
            align-items: center;
        }

        #sharepage .head .left-box img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 7px;
        }

        #sharepage .head .right-box {
            position: relative;
            bottom: -6px;
            z-index: 9;
            width: 30%;
        }

        #sharepage .head .right-box img {
            width: 100%;
        }

        #sharepage .head .right-box .iconfont {
            display: inline-block;
            padding-right: 5px;
        }

        #sharepage .content .center-box {
            position: relative;
        }

        #sharepage .content .center-box img {
            display: block;
            padding: 0 15px;
            width: 100%;
            height: auto;
            box-sizing: border-box;
        }

        #sharepage .content .center-box .text {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, .8);
            padding: 10px 15px;
            color: #2E2D2D;
            font-size: 16px;
            line-height: 15px;
            box-sizing: border-box;
        }

        #sharepage .content .bottom-box {
            background-color: #FFFFFF;
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            padding: 24px 10px 17px 10px;
        }

        #sharepage .content .bottom-box .item {
            -webkit-box-flex: 1;
            -webkit-flex: 1;
            -ms-flex: 1;
            flex: 1;
            padding: 5px;
        }

        #sharepage .content .bottom-box .left-item .title {
            font-size: 15px;
            color: #2E2D2D;
            line-height: 20px;
            padding-bottom: 16px;
        }

        #sharepage .content .bottom-box .left-item img {
            display: inline-block;
            width: 100%;
        }

        #sharepage .content .bottom-box .left-item .origin-money {
            color: #9B9B9B;
            line-height: 14px;
            font-size: 10px;
            text-decoration: line-through;
        }

        #sharepage .content .bottom-box .left-item .sale-money {
            color: #E73237;
            font-size: 25px;
            line-height: 35px;
            padding-top: 10px;
        }

        #sharepage .content .bottom-box .rigth-item {
            text-align: left;
        }

        #sharepage .content .bottom-box .rigth-item img {
            display: inline-block;
            width: 100%;
            margin-bottom: 10px;
        }

        #sharepage .content .bottom-box .rigth-item .txt {
            padding-top: 10px;
            color: #2E2D2D;
            font-size: 10px;
            line-height: 17px;
        }

    </style>
</head>
<body>
<div id="sharepage">
    <div class="head">
        <div class="left-box">
            <img src="{{ $content->avatar }}" alt="">
            <span>{{ $content->nick_name }}</span>
        </div>
        <div class="right-box">
            <img src="{{settings('travel_share_template_1_top')?settings('travel_share_template_1_top'):'https://cdn.viperky.com/storage/images/20180928/UH5qRze7mt.png'}}"
                 alt="">
        </div>
    </div>
    <div class="content">
        <div class="center-box">
            <img src="{{ $content->img_list[0] }}" alt="">
            <div class="text">{{ $content->description }}</div>
        </div>
        <div class="bottom-box">
            <div class="left-item item">
                <img src="{{ $mini_code }}" alt="">
            </div>
            <div class="rigth-item item">
                <img src="{{settings('travel_share_template_1_bottom')?settings('travel_share_template_1_bottom'):'https://cdn.viperky.com/storage/images/20180928/Seo1OJmEaU.png'}}"
                     alt="">
                扫描或长按 识别小程序码，为我点赞吧~
            </div>

        </div>
    </div>
</div>

</body>
</html>