{{--@extends ('catering-backend::dashboard')
@section ('title','添加分类')


@section ('breadcrumbs')
    <h2>添加分类</h2>
    <ol class="breadcrumb">
        <li><a href="{!!route('admin.store.index')!!}"><i class="fa fa-dashboard"></i> 首页</a></li>

        <li><a href="{{route('admin.category.index', ['id' => $group_id])}}">分类管理</a></li>
        <li class="active">添加分类</li>
    </ol>
@stop

@section('content')--}}
    <div class="ibox float-e-margins">
        <div class="ibox-content" style="display: block;">

            {!! Form::open(['route' => 'admin.category.store'
            , 'class' => 'form-horizontal'
            , 'role' => 'form'
            , 'method' => 'POST'
             ,'id'=>'Category_form']) !!}
            <input type="hidden" value="{{$group_id}}" name="group_id">
            @include('catering-backend::category.form')

            {!! Form::close() !!}
                    <!-- /.tab-content -->
        </div>
    </div>
{{--@endsection--}}


{{--@section('after-scripts-end')--}}
    <script>
        $('#Category_form').ajaxForm({
            success: function (result) {
                if(!result.status)
                {
                    swal("保存失败!", result.error, "error")
                }else{
                    swal({
                        title: "保存成功！",
                        text: "",
                        type: "success"
                    }, function() {
                        location = '{{route('admin.category.index', ['id' => $group_id])}}';
                    });
                }

            }
        });

    </script>

{{--@stop--}}