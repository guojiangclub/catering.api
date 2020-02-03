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
                        <i class="fa fa-info-circle"></i> 统计结果通知
                    </div>
                    <div class="panel-body">
                        <div class="col-sm-8">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">统计结果通知</label>
                                <div class="col-lg-6">
                                    <div class="radio">
                                        <label><input type="radio" name="wechat_message_st_statistics_result[status]"
                                                      value="1" {{isset($statistics_result['status']) && $statistics_result['status']==1 ? 'checked' : (!isset($statistics_result['status']) ? 'checked' : '')}}>启用</label>
                                        <label><input type="radio" name="wechat_message_st_statistics_result[status]"
                                                      value="0" {{isset($statistics_result['status']) && $statistics_result['status']==0 ? 'checked' : ''}}>禁用</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">编号</label>
                                <div class="col-sm-6">
                                    <input type="text" name="wechat_message_st_statistics_result[id]"
                                           value="{{isset($statistics_result['id']) && $statistics_result['id'] ? $statistics_result['id'] : ''}}"
                                           {{isset($statistics_result['id']) && $statistics_result['id'] ? 'readonly' : ''}} class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">标题:</label>
                                <div class="col-sm-6">
                                    <input type="text" name="wechat_message_st_statistics_result[first]"
                                           value="{{isset($statistics_result['first']) && $statistics_result['first'] ? $statistics_result['first'] : '昨日运营收入'}}" class="form-control wechat_message_first_text" oninput="OnInput(event)" onpropertychange="OnPropChanged(event)">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">模版ID</label>
                                <div class="col-sm-6">
                                    <input type="text" name="wechat_message_st_statistics_result[template_id]" value="{{isset($statistics_result['id']) && $statistics_result['template_id'] ? $statistics_result['template_id'] : ''}}" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">提示:</label>
                                <div class="col-sm-6">
                                    <input type="text" name="wechat_message_st_statistics_result[remark]"
                                           value="{{isset($statistics_result['remark']) && $statistics_result['remark'] ? $statistics_result['remark'] : '继续努力'}}"
                                           class="form-control wechat_message_remark_text" oninput="OnInput(event)"
                                           onpropertychange="OnPropChanged(event)">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="wx-template">
                                <div class="wx-title">统计结果通知</div>
                                <div class="wx-date">1月1日</div>
                                <div class="wx-content">
                                    <span class="wechat_message_first_text_target">{{isset($statistics_result['first']) && $statistics_result['first'] ? $statistics_result['first'] : '昨日运营收入'}}</span><br />
                                    企业名称：呷哺呷哺<br>
                                    提醒时间：2017年8月8日 10:00<br>
                                    汇总数据：昨日收入300元，本月总收入70000元<br>
                                    <span class="wechat_message_remark_text_target">{{isset($statistics_result['remark']) && $statistics_result['remark'] ? $statistics_result['remark'] : '继续努力'}}</span>
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