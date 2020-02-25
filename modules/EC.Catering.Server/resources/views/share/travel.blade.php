<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="format-detection" content="telephone=no" />
    <meta name="format-detection" content="email=no" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, shrink-to-fit=no">
</head>
<style>
    @font-face {
        font-family: 'MILANTING--GBK1-Light';
        font-weight: normal;
        font-style: normal;
    }
    body {
        font-family:"MILANTING--GBK1-Light" !important;
    }
    html, body {
        padding: 0;
        margin: 0;
    }

    #sharepage-index {
        height: 100%;
        overflow: auto;
    }

    #sharepage-index .sharepage .img-box {
        position: relative;
    }

    #sharepage-index .sharepage .shareimages {
        box-sizing: border-box;
        width: 100%;
    }

    #sharepage-index .sharepage .shareimages img {
        width: 100%;
        display: block;
    }

    #sharepage-index .sharepage .sharetext {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(255, 255, 255, 0.7);
        color: #000000;
        font-size: 16px;
        line-height: 32px;
        padding: 5px 11px 5px 15px;
        overflow:hidden;
        text-overflow:ellipsis;
        display:-webkit-box;
        -webkit-box-orient:vertical;
        -webkit-line-clamp:2;
    }

    #sharepage-index .sharepage .sharemian {
        background-color: #ffffff;
        padding: 16px 37px 10px 37px;
        border-radius: 0 0 4px 4px;
    }

    #sharepage-index .sharepage .sharemian .shareperson {
        display: flex;
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        background-color: #FBF6DC;
        padding-right: 6px;
    }

    #sharepage-index .sharepage .sharemian .shareperson .userphoto {
        width: 30px;
        height: 30px;
    }

    #sharepage-index .sharepage .sharemian .shareperson .userphoto img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        display: block;
    }

    #sharepage-index .sharepage .sharemian .shareperson .nowtext {
        color: #4A4A4A;
        padding-left: 14px;
        line-height: 30px;
        font-size: 12px;
        width: 90%;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    #sharepage-index .sharepage .sharemian .erweima {
        padding: 8px 0 12px 0;
        text-align: center;
    }

    #sharepage-index .sharepage .sharemian .erweima img {
        width: 94px;
        height: 94px;
    }

    #sharepage-index .sharepage .sharemian .longsee {
        text-align: center;
        color: #9B9B9B;
        font-size: 12px;
    }

</style>
<body>
<div id="sharepage-index">
    <div class="sharepage">
        <div class="img-box">
            <div class="shareimages">
                <img src="{{ $content->img_list[0] }}" />
            </div>
            <div class="sharetext">
                {{ $content->description }}
            </div>
        </div>
        <div class="sharemian">
            <div class="shareperson">
                <div class="userphoto">
                    <img src="{{ $content->avatar }}" />
                </div>
                <div class="nowtext">
                    {{ $content->nick_name }} 发表了 {{ $tag }} {{ $content->description }}
                </div>
            </div>
            <div class="erweima">
                <img src="{{ $mini_code }}" />
            </div>
            <div class="longsee">长按识别小程序码，为我点赞吧~</div>
        </div>
    </div>
</div>
</body>
</html>