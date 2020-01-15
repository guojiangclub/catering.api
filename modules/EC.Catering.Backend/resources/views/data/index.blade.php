<style type="text/css">
    .today-sales {
        background-color: #ffffff;
        border: 1px solid #E9E9E9;
        margin: 0;
    }

    .today-sales .title {
        padding: 11px 0 11px 22px;
        color: #4A4A4A;
        font-size: 16px;
        line-height: 22px;
        border-bottom: 1px solid #E9E9E9;
    }

    .today-sales .sales-detail .detail-item.bor-r {
        border-right: 1px solid #DBDBDB;
    }

    .today-sales .sales-detail .detail-item {
        text-align: center;
        margin: 23px 0 20px 0;
    }

    .today-sales .sales-detail .detail-item .num {
        font-size: 24px;
        line-height: 33px;
        color: #2E2D2D;
        padding-bottom: 14px;
        white-space: nowrap;
        -o-text-overflow: ellipsis;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    .today-sales .sales-detail .detail-item .num.yellow {
        color: #F5A623;
    }

    .today-sales .sales-detail .detail-item .text {
        font-size: 14px;
        line-height: 20px;
        color: #4A4A4A;
    }

    .today-sales .sales-detail .detail-item .num.red {
        color: #FF2741;
    }

    .growth {
        margin: 0;
        margin-top: 12px;
        background-color: #ffffff;
        border: 1px solid #E9E9E9;
        -webkit-border-radius: 4px;
        border-radius: 4px;
    }

    .growth .growth-content {
        width: 100%;
    }

    .growth .growth-content .much-order {
        border-bottom: 1px solid #E9E9E9;
        padding: 12px 26px 10px 14px;
    }

    .growth .growth-content .much-order .info-l {
        font-size: 16px;
        color: #4A4A4A;
    }

    .growth .growth-content .much-order .info-r.radiu-d {
        -webkit-border-radius: 0px 4px 4px 0px;
        border-radius: 0px 4px 4px 0px;
    }

    .growth .growth-content .much-order .info-r.active {
        border-color: #2BC0BE;
        color: #2BC0BE;
    }

    .growth .growth-content .much-order .info-r.radiu-m {
        -webkit-border-radius: 4px 0px 0px 4px;
        border-radius: 4px 0px 0px 4px;
    }

    .growth .growth-content .much-order .info-r {
        font-size: 14px;
        color: #9B9B9B;
        display: inline-block;
        line-height: 24px;
        padding: 0 15px;
        border: 1px solid #DBDBDB;
        cursor: pointer;
    }
</style>

<div class="ibox float-e-margins">
    <div class="ibox-content" style="display: block;">
        <div class="today-sales row">
            <div class="title">
                数据汇总
            </div>
            <div class="sales-detail">
                <div class="col-sm-3 detail-item bor-r">
                    <div class="num yellow">{{ $orderTotal }}</div>
                    <div class="text">会员买单金额</div>
                </div>
                <div class="col-sm-3 detail-item bor-r">
                    <div class="num yellow">{{ $adjustmentsTotal }}</div>
                    <div class="text">优惠金额</div>
                </div>
                <div class="col-sm-3 detail-item bor-r">
                    <div class="num">{{ $rechargeTotal }}</div>
                    <div class="text">储值金额</div>
                </div>
                <div class="col-sm-3 detail-item bor-r">
                    <div class="num">{{ $balanceTotal }}</div>
                    <div class="text">消耗金额</div>
                </div>
                <div class="col-sm-3 detail-item bor-r">
                    <div class="num">{{ $userTotal }}</div>
                    <div class="text">会员总数</div>
                </div>
                <div class="col-sm-3 detail-item bor-r">
                    <div class="num">{{ $fansTotal }}</div>
                    <div class="text">公众号粉丝总数</div>
                </div>
                <div class="col-sm-3 detail-item bor-r">
                    <div class="num">{{ $couponTotal }}</div>
                    <div class="text">核销优惠券总数</div>
                </div>
            </div>
        </div>
        <div class="growth row">
            <div class="growth-content">
                <div class="much-order clearfix">
                    <span class="info-l pull-left">每日新增会员</span>
                    <span class="info-r pull-right radiu-d" id="Gmonth">月</span>
                    <span class="info-r pull-right radiu-m active" id="Gday">日</span>
                </div>
                <div id="main-growth" style="width: 100%;height:400px;">

                </div>
            </div>
        </div>

        <div class="growth row">
            <div class="growth-content">
                <div class="much-order clearfix">
                    <span class="info-l pull-left">公众号关注</span>
                    <span class="info-r pull-right radiu-d" id="official_account_month">月</span>
                    <span class="info-r pull-right radiu-m active" id="official_account_day">日</span>
                </div>
                <div id="official_account_growth" style="width: 100%;height:400px;">

                </div>
            </div>
        </div>
    </div>
</div>

{!! Html::script(env("APP_URL").'/assets/backend/libs/echarts.min.js') !!}
@include('catering-backend::data.script')