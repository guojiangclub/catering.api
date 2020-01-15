{{--@extends ('catering-backend::dashboard')
@section ('title','修改分类')

@section ('breadcrumbs')
    <h2>修改分类</h2>
    <ol class="breadcrumb">
        <li><a href="{!!route('admin.store.index')!!}"><i class="fa fa-dashboard"></i> 首页</a></li>
        <li><a href="{{route('admin.category.index', ['id' => $category->group_id])}}">分类管理</a></li>
        <li class="active">修改分类</li>
    </ol>
@stop

@section('content')--}}
    <div class="ibox float-e-margins">
        <div class="ibox-content" style="display: block;">

            {!! Form::model($category,['route' => ['admin.category.update',$category->id]
            , 'class' => 'form-horizontal'
            , 'role' => 'form'
            , 'method' => 'post'
            ,'id'=>'Category_form']) !!}

            @include('catering-backend::category.form')

            {!! Form::close() !!}
                    <!-- /.tab-content -->
        </div>
    </div>
{{--@endsection

@section('after-scripts-end')--}}
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
                        location = '{{route('admin.category.index', ['id' => $category->group_id])}}';
                    });
                }

            }
        });

    </script>

{{--@stop--}}