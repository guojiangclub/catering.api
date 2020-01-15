@if(Session::has('message'))
    <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i> 提示！</h4>
        {{ Session::get('message') }}
    </div>
@endif
<div class="tabs-container">
        <ul class="nav nav-tabs">
            <li class="@if($status===1) active @endif"><a href="{{route('admin.shitang.shop.clerk',['status'=>1])}}" no-pjax> 已启用
                    <span class="badge">{{$inUse}}</span>
                </a></li>
            <li class="@if($status===0) active @endif"><a href="{{route('admin.shitang.shop.clerk',['status'=>0])}}" no-pjax> 禁用
                    <span class="badge">{{$forbidden}}</span>
                </a></li>
        </ul>

        <div class="tab-content">
            <div id="tab-1" class="tab-pane active">
                {!! Form::open( [ 'route' => ['admin.shitang.shop.clerk'], 'method' => 'get', 'id' => 'discount-form','class'=>'form-horizontal'] ) !!}
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-2">
                            <a class="btn btn-primary " href="{{ route('admin.shitang.shop.clerk.create')}}" no-pjax>添加店员</a>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" name="title">
                                <option value="mobile" selected>
                                    手机号
                                </option>
                                <option value="clerk_no" {{request('title')=='clerk_no'?"selected":''}}>
                                    工号
                                </option>
                                <option value="name" {{request('title')=='name'?"selected":''}}>
                                    姓名
                                </option>
                                <option value="email" {{request('title')=='email'?"selected":''}}>
                                    邮箱
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <input type="hidden" name="status" value="{{$status}}">
                            <div class="input-group">
                                <input type="text" name="value" value="{{request('value')}}" placeholder="" class="form-control">
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-primary">查找</button>
                                </span>
                            </div>
                        </div>

                    </div>

                    {!! Form::close() !!}

                    <div class="hr-line-dashed"></div>

                    <div class="table-responsive">

                        <table class="table table-hover table-striped">
                            <tbody>
                            <!--tr-th start-->
                            <tr>
                                <th>ID</th>
                                <th>工号</th>
                                <th>姓名</th>
                                <th>邮箱</th>
                                <th>手机</th>
                                <th>接收统计模板消息</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            @if(count($lists)>0)
                                @foreach($lists as $item)
                                    <tr>
                                        <td>{{$item->id}}</td>
                                        <td>{{$item->clerk_no}}</td>
                                        <td>{{$item->name}}</td>
                                        <td>{{$item->email}}</td>
                                        <td>{{$item->mobile}}</td>
                                        <td>{{$item->receive_template_message == 1 ? '是' : '否'}}</td>
                                        <td>{{$item->created_at}}</td>
                                        <td>
                                            <a class="btn btn-xs btn-primary" href="{{route('admin.shitang.shop.clerk.edit',['clerk_id'=>$item['id']])}}" no-pjax>
                                                <i data-toggle="tooltip" data-placement="top" class="fa fa-pencil-square-o" title="编辑"></i></a>
                                            <a class="btn btn-xs btn-danger delete_items" data-id="{{ $item->id }}">
                                                <i data-toggle="tooltip" data-placement="top" class="fa fa-times" title="删除"></i></a>
                                            <a>
                                                <i class="fa switch @if($item['status']) fa-toggle-on @else fa-toggle-off @endif" title="切换状态" value= {{$item['status']}} >
                                                    <input type="hidden" value={{$item['id']}}>
                                                </i>
                                            </a>
                                            @if($item->is_clerk_owner)
                                                <span class="badge">店长</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                        @if(count($lists)==0)
                            <div>
                                &nbsp;&nbsp;&nbsp;当前无数据
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="modal" class="modal inmodal fade"></div>

{!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.zclip/jquery.zclip.js') !!}
    <script>
        $('.copyBtn').zclip({
	        path: "{{url('assets/backend/libs/jquery.zclip/ZeroClipboard.swf')}}",
	        copy: function () {
		        return $(this).prev().val();
	        }
        });
    </script>

    <script>
        $('.delete_items').on('click', function () {
	        var id = $(this).attr('data-id');
	        $.ajax({
		        type: "get",
		        url: "{{route('admin.shitang.shop.clerk.delete')}}",
		        data: 'id=' + id,
		        dataType: 'json',
		        success: function (res) {
			        if (200 == res.code) {
				        swal({
					        title: "删除成功！",
					        text: "",
					        type: "success"
				        }, function () {
					        location = '{{route('admin.shitang.shop.clerk')}}?status={{ request('status') }}';
				        });
			        } else {
				        swal("删除失败！", res.message, 'warning');
			        }
		        }
	        });
        });

        $('.switch').on('click', function () {
	        var value = $(this).attr('value');
	        var modelId = $(this).children('input').attr('value');
	        value = parseInt(value);
	        modelId = parseInt(modelId);
	        value = value ? 0 : 1;
	        var that = $(this);
	        $.get("{{route('admin.shitang.shop.clerk.status')}}",
		        {
			        status: value,
			        aid: modelId
		        },
		        function (res) {
			        if (res.status) {
				        that.toggleClass("fa-toggle-off , fa-toggle-on");
				        that.attr('value', value);
				        location.reload();
			        } else {
				        swal({
					        title: "操作失败",
					        text: "",
					        type: "error",
					        confirmButtonText: "确定"
				        }, function () {
					        location.reload();
				        });
			        }
		        });

        })
    </script>