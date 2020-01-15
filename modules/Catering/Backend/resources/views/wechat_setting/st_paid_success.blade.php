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
@include('store-backend::wechat_setting.include.script')
<div class="tabs-container">
    <form method="post" action="{{route('admin.shitang.customer.shop.wechat.save')}}" class="form-horizontal" id="setting_site_form">
        {{csrf_field()}}
        <div class="tab-content">
            <div id="tab-1" class="tab-pane active">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-info-circle"></i> 支付成功通知
                    </div>
                    <div class="panel-body">
                        <div class="col-sm-8">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">支付成功通知</label>
                                <div class="col-lg-6">
                                    <div class="radio">
                                        <label><input type="radio" name="wechat_message_st_paid_success[status]"
                                                      value="1" {{isset($paid_success['status']) && $paid_success['status']==1 ? 'checked' : (!isset($paid_success['status']) ? 'checked' : '')}}>启用</label>
                                        <label><input type="radio" name="wechat_message_st_paid_success[status]"
                                                      value="0" {{isset($paid_success['status']) && $paid_success['status']==0 ? 'checked' : ''}}>禁用</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">编号</label>
                                <div class="col-sm-6">
                                    <input type="text" name="wechat_message_st_paid_success[id]"
                                           value="{{isset($paid_success['id']) && $paid_success['id'] ? $paid_success['id'] : ''}}"
                                           {{isset($paid_success['id']) && $paid_success['id'] ? 'readonly' : ''}} class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">标题:</label>
                                <div class="col-sm-6">
                                    <input type="text" name="wechat_message_st_paid_success[first]"
                                           value="{{isset($paid_success['first']) && $paid_success['first'] ? $paid_success['first'] : '您好，您的微信支付已成功'}}"
                                           class="form-control wechat_message_first_text" oninput="OnInput(event)"
                                           onpropertychange="OnPropChanged(event)">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">模版ID</label>
                                <div class="col-sm-6">
                                    <input type="text" name="wechat_message_st_paid_success[template_id]"
                                           value="{{isset($paid_success['id']) && $paid_success['template_id'] ? $paid_success['template_id'] : ''}}"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">提示:</label>
                                <div class="col-sm-6">
                                    <input type="text" name="wechat_message_st_paid_success[remark]"
                                           value="{{isset($paid_success['remark']) && $paid_success['remark'] ? $paid_success['remark'] : '点击查看消费记录'}}"
                                           class="form-control wechat_message_remark_text" oninput="OnInput(event)"
                                           onpropertychange="OnPropChanged(event)">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="wx-template">
                                <div class="wx-title">支付成功通知</div>
                                <div class="wx-date">1月1日</div>
                                <div class="wx-content">
                                    <span class="wechat_message_first_text_target">{{isset($paid_success['first']) && $paid_success['first'] ? $paid_success['first'] : '您好，您的微信支付已成功'}}</span><br />
                                    订单编号：123456789901234567<br>
                                    消费金额：28.88元<br>
                                    消费门店：一元超市<br>
                                    消费时间：2016年4月8日17:00:00<br>
                                    <span class="wechat_message_remark_text_target">{{isset($paid_success['remark']) && $paid_success['remark'] ? $paid_success['remark'] : '点击查看消费记录'}}</span>
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