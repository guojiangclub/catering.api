<style>
    .wx-template {
        display: inline-block;
        width: 300px;
        margin: 15px;
        padding: 15px 15px 10px 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
        font-size: 14px;
    }

    .wx-title {
        position: relative;
        margin-bottom: 6px;
        font-size: 20px;
        font-weight: bold;
    }

    .wx-date {
        margin-bottom: 20px;
        font-size: 14px;
        color: #999;
    }

    .wx-content {
        margin-bottom: 20px;
    }

    .wx-link {
        position: relative;
        padding-top: 10px;
        border-top: 1px solid #ccc;
    }
</style>
@include('backend-shitang::wechat_setting.include.script')
<div class="tabs-container">
    <form method="post" action="{{route('admin.shitang.customer.shop.wechat.save')}}" class="form-horizontal" id="setting_site_form">
        {{csrf_field()}}
        <div class="tab-content">
            <div id="tab-1" class="tab-pane active">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-info-circle"></i> 优惠券提醒
                    </div>
                    <div class="panel-body">
                        <div class="col-sm-8">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">优惠券提醒</label>
                                <div class="col-lg-6">
                                    <div class="radio">
                                        <label><input type="radio" name="wechat_message_st_coupon_changed[status]"
                                                      value="1" {{isset($coupon['status']) && $coupon['status']==1 ? 'checked' : (!isset($coupon['status']) ? 'checked' : '')}}>启用</label>
                                        <label><input type="radio" name="wechat_message_st_coupon_changed[status]"
                                                      value="0" {{isset($coupon['status']) && $coupon['status']==0 ? 'checked' : ''}}>禁用</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">编号</label>
                                <div class="col-sm-6">
                                    <input type="text" name="wechat_message_st_coupon_changed[id]"
                                           value="{{isset($coupon['id']) && $coupon['id'] ? $coupon['id'] : 'OPENTM409797795'}}"
                                           {{isset($coupon['id']) && $coupon['id'] ? 'readonly' : ''}} class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">标题:</label>
                                <div class="col-sm-6">
                                    <input type="text" name="wechat_message_st_coupon_changed[first]"
                                           value="{{isset($coupon['first']) && $coupon['first'] ? $coupon['first'] : '优惠券获取通知'}}"
                                           class="form-control wechat_message_first_text" oninput="OnInput(event)"
                                           onpropertychange="OnPropChanged(event)">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">模版ID</label>
                                <div class="col-sm-6">
                                    <input type="text" name="wechat_message_st_coupon_changed[template_id]"
                                           value="{{isset($coupon['id']) && $coupon['template_id'] ? $coupon['template_id'] : ''}}"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">提示:</label>
                                <div class="col-sm-6">
                                    <input type="text" name="wechat_message_st_coupon_changed[remark]"
                                           value="{{isset($coupon['remark']) && $coupon['remark'] ? $coupon['remark'] : '如有疑问，请拨打服务热线010-8888888'}}"
                                           class="form-control wechat_message_remark_text" oninput="OnInput(event)"
                                           onpropertychange="OnPropChanged(event)">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="wx-template">
                                <div class="wx-title">办理成功通知</div>
                                <div class="wx-date">8月8日</div>
                                <div class="wx-content">
                                    <span class="wechat_message_first_text_target">{{isset($coupon['first']) && $coupon['first'] ? $coupon['first'] : '优惠券获取通知'}}</span><br/>
                                    商户名称：氪来科技<br>
                                    卡券名称：满100减20<br>
                                    有效期：2019-01-01到2020-01-01
                                    办理时间：2019-01-01
                                    <span class="wechat_message_remark_text_target">{{isset($coupon['remark']) && $coupon['remark'] ? $coupon['remark'] : '点击了解更多活动详情'}}</span>
                                </div>
                                <div class="wx-link">详情</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ibox-content m-b-sm border-bottom text-center">
                <button class="btn btn-primary" type="submit">保存设置</button>
            </div>
        </div>
    </form>
</div>

<script>
    $(function () {
        $('#setting_site_form').ajaxForm({
            success: function (result) {
                swal({
                    title: "保存成功！",
                    text: "",
                    type: "success"
                }, function () {
                    window.location = '{{route('admin.shitang.customer.shop.wechat')}}';
                });
            }
        });
    })
</script>