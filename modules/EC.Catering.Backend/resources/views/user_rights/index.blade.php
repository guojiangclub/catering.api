<div class="tabs-container">
    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#tab-1" aria-expanded="true"> 会员权益列表</a></li>
        <a href="{{route('admin.users.rights.create')}}" class="btn btn-w-m btn-info pull-right">添加会员权益</a>
    </ul>
    <div class="tab-content">
        <div id="tab-1" class="tab-pane active">
            <div class="panel-body">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>权益名称</th>
                        <th>图片</th>
                        <th>状态</th>
                        <th>排序</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($rights as $item)
                        <tr>
                            <td>{{$item->name}}</td>
                            <td><img src="{{$item->img}}" width="50"></td>
                            <td>{{$item->status?'使用中':'已禁用'}}</td>
                            <td>{{$item->sort}}</td>
                            <td>
                                <a class="btn btn-xs btn-primary"
                                   href="{{route('admin.users.groupcreate',['id'=>$item->id])}}">
                                    <i data-toggle="tooltip" data-placement="top"
                                       class="fa fa-pencil-square-o"
                                       title="编辑"></i></a>

                                <a href="javascript:;" class="btn btn-xs btn-danger delete-group"
                                   data-href="{{route('admin.users.deletedGroup',['id'=>$item->id])}}">
                                    <i data-toggle="tooltip" data-placement="top"
                                       class="fa fa-trash"
                                       title="删除"></i></a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="clearfix"></div>
            </div>
        </div>

    </div>
</div>
{{--@stop--}}

<script>
    $('.delete-group').on('click', function () {
        var that = $(this);
        var postUrl = that.data('href');
        var body = {
            _token: _token
        };
        swal({
            title: "确定要删除吗?",
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "确认",
            cancelButtonText: '取消',
            closeOnConfirm: false
        }, function () {
            $.post(postUrl, body, function (result) {
                if (result.status) {
                    swal({
                        title: "删除成功！",
                        text: "",
                        type: "success"
                    }, function () {
                        location = '{{route('admin.users.grouplist')}}';
                    });
                } else {
                    swal({
                        title: result.message,
                        text: "",
                        type: "warning"
                    });
                }
            });
        });
    });
</script>
