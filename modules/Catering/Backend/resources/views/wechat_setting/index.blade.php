<style>
    .switches {
        margin-top: 10px;
    }

    ol, ul {
        list-style: none;
    }

    .switch-item {
        float: left;
        width: 20%;
        margin-bottom: 10px;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
        margin-right: 1.33%;
        background-color: #f8f8f8;
        padding: 10px 20px;
        position: relative;
    }

    a {
        color: #38f;
        text-decoration: none;
    }

    .switch-item .title {
        font-size: 18px;
        line-height: 1.5;
        margin-bottom: 10px;
        color: #38f;
    }

    .switch-item .disable {
        color: #f30;
    }

    .switch-item .disable .free-tip {
        color: #999;
    }

    .switch-item .switch-setting {
        display: none;
        color: #07d;
    }

    .pull-right {
        float: right;
    }

    .switch-item .enable {
        color: #4b0;
    }
</style>

<div class="ibox-content" style="display: block;">
    <ul class="clearfix switches">

        <li class="switch-item">
            <a href="{{route('admin.shitang.customer.shop.wechat.stPointChange')}}">
                <h4 class="title">积分变更提醒</h4>
                <p class="{{isset($st_point_changed['status']) && $st_point_changed['status']==1 ? 'enable' : 'disable'}}">{{isset($st_point_changed['status']) && $st_point_changed['status']==1 ? '已启用' : '未启用'}}<span></span><span class="pull-right switch-setting">设置</span>
                </p>
            </a>
        </li>

        <li class="switch-item">
            <a href="{{route('admin.shitang.customer.shop.wechat.stBalanceChange')}}">
                <h4 class="title">余额变更提醒</h4>
                <p class="{{isset($st_balance_changed['status']) && $st_balance_changed['status']==1 ? 'enable' : 'disable'}}">{{isset($st_balance_changed['status']) && $st_balance_changed['status']==1 ? '已启用' : '未启用'}}
                    <span></span>
                    <span class="pull-right switch-setting">设置</span>
                </p>
            </a>
        </li>

        <li class="switch-item">
            <a href="{{route('admin.shitang.customer.shop.wechat.stCouponChange')}}">
                <h4 class="title">优惠券提醒</h4>
                <p class="{{isset($st_coupon_changed['status']) && $st_coupon_changed['status']==1 ? 'enable' : 'disable'}}">{{isset($st_coupon_changed['status']) && $st_coupon_changed['status']==1 ? '已启用' : '未启用'}}
                    <span></span>
                    <span class="pull-right switch-setting">设置</span>
                </p>
            </a>
        </li>

        <li class="switch-item">
            <a href="{{route('admin.shitang.customer.shop.wechat.joinSuccess')}}">
                <h4 class="title">入会成功通知</h4>
                <p class="{{isset($st_join_success['status']) && $st_join_success['status']==1 ? 'enable' : 'disable'}}">{{isset($st_join_success['status']) && $st_join_success['status']==1 ? '已启用' : '未启用'}}
                    <span></span>
                    <span class="pull-right switch-setting">设置</span>
                </p>
            </a>
        </li>

        <li class="switch-item">
            <a href="{{route('admin.shitang.customer.shop.wechat.paidSuccess')}}">
                <h4 class="title">支付成功通知</h4>
                <p class="{{isset($st_paid_success['status']) && $st_paid_success['status']==1 ? 'enable' : 'disable'}}">{{isset($st_paid_success['status']) && $st_paid_success['status']==1 ? '已启用' : '未启用'}}
                    <span></span>
                    <span class="pull-right switch-setting">设置</span>
                </p>
            </a>
        </li>

        <li class="switch-item">
            <a href="{{route('admin.shitang.customer.shop.wechat.statisticsResult')}}">
                <h4 class="title">统计结果通知</h4>
                <p class="{{isset($st_statistics_result['status']) && $st_statistics_result['status']==1 ? 'enable' : 'disable'}}">{{isset($st_statistics_result['status']) && $st_statistics_result['status']==1 ? '已启用' : '未启用'}}
                    <span></span>
                    <span class="pull-right switch-setting">设置</span>
                </p>
            </a>
        </li>

        <li class="switch-item">
            <a href="{{route('admin.shitang.customer.shop.wechat.couponOverdueRemind')}}">
                <h4 class="title">优惠券过期提醒</h4>
                <p class="{{isset($st_coupon_overdue_remind['status']) && $st_coupon_overdue_remind['status']==1 ? 'enable' : 'disable'}}">{{isset($st_coupon_overdue_remind['status']) && $st_coupon_overdue_remind['status']==1 ? '已启用' : '未启用'}}
                    <span></span>
                    <span class="pull-right switch-setting">设置</span>
                </p>
            </a>
        </li>
    </ul>
</div>
<script>
    $(function () {
        $('#setting_site_form').ajaxForm({
            success: function (result) {
                swal("保存成功!", "", "success")
            }
        });

        $('.switch-item').mouseover(function(){
            $(this).children('a').children('p').children('.switch-setting').show();
        }).mouseleave(function(){
            $(this).children('a').children('p').children('.switch-setting').hide();
        });
    })
</script>