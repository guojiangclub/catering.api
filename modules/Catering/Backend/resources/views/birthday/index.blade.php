<div class="tabs-container">
        @if (session()->has('flash_notification.message'))
        <div class="alert alert-{{ session('flash_notification.level') }}">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            {!! session('flash_notification.message') !!}
            </div>
    @endif

    <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#tab-1" aria-expanded="true"> 生日礼列表</a></li>
            <a no-pjax href="{{route('admin.shitang.gift.birthday.create')}}" class="btn btn-w-m btn-info pull-right">添加生日礼</a>
        </ul>

        <div class="tab-content">
            <div id="tab-1" class="tab-pane active">
                <div class="panel-body">

                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>活动名称</th>
                            <th>开始时间</th>
                            <th>结束时间</th>
                            <th>赠送优惠券</th>
                            <th>赠送方式</th>
                            <th>活动状态</th>
                            <th>操作</th>
                            <th>启动</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($lists)>0)
                            @foreach($lists as $item)
                                <tr>
                                    <td>{{$item->name}}</td>
                                    <td>{{$item->starts_at}}</td>
                                    <td>{{$item->ends_at}}</td>
                                    <td>
                                        @if(isset($item->gift)&&count($item->gift)>0)
                                            @foreach($item->gift as $val)
                                                <a href="{{route('admin.shitang.coupon.edit',$val->discount->id)}}" target="_blank">{{$val->discount->title}}
                                                    @if(count($coupons)<=0)
                                                        (已过期)
                                                    @elseif(!in_array($val->discount->id,$coupons))
                                                        (已过期)
                                                    @endif
                                                        </a>
                                                {{$val->discount->per_usage_limit}}张<br>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->type ==\GuoJiangClub\Catering\Backend\Models\GiftActivity::TYPE_RANDOM)
                                            随机送一张
                                        @else
                                            全部送
                                        @endif
                                    </td>
                                    <td>{{$item->status_text}}</td>
                                    <td>
                                        <a class="btn btn-xs btn-primary" href="{{route('admin.shitang.gift.birthday.edit',$item->id)}}" no-pjax><i data-toggle="tooltip" data-placement="top" class="fa fa-pencil-square-o" title="编辑"></i></a>
                                        <span class="btn btn-xs btn-danger del-activity" data-url="{{route('admin.shitang.gift.birthday.delete',$item->id)}}"><i class="fa fa-trash" data-toggle="tooltip" data-placement="top" title="删除"></i></span>
                                    </td>
                                    <td>
                                        <a>
                                            <i class="fa switch @if($item->status) fa-toggle-on @else fa-toggle-off @endif" title="切换状态" data-status="{{$item->status}}" data-id="{{ $item->id }}">
                                            </i>
                                        </a>
                                    </td>

                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>

                    @if(count($lists)>0)
                        <div class="pull-lift">
                            {!! $lists->render() !!}
                        </div>
                    @endif

                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function () {
	        $('.del-activity').on('click', function () {
		        var obj = $(this);
		        swal({
			        title: "确定删除该生日礼活动吗？",
			        text: "",
			        type: "warning",
			        showCancelButton: true,
			        confirmButtonColor: "#DD6B55",
			        confirmButtonText: "删除",
			        cancelButtonText: "取消",
			        closeOnConfirm: false
		        }, function () {
			        var url = obj.data('url');
			        $.get(url, function (ret) {
				        if (!ret.status) {
					        swal("删除失败!", "", "warning");
				        } else {
					        swal({
						        title: "删除成功",
						        timer: 800,
						        text: "",
						        type: "success",
						        confirmButtonText: "确定"
					        }, function () {
						        location.reload();
					        });
				        }
			        });
		        });

	        });
        });

        $('.switch').on('click', function () {
	        var status = $(this).data('status');
	        var modelId = $(this).data('id');

	        $.post("{{route('admin.shitang.gift.birthday.toggleStatus')}}",
		        {
			        status: status,
			        id: modelId
		        },
		        function (res) {
			        if (res.status) {
				        window.location.reload();
			        } else {
				        swal("操作失败", res.message, "warning");
			        }
		        });

        })
    </script>