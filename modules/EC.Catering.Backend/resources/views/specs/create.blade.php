{{--@extends ('catering-backend::dashboard')

@section ('title','新建商品规格')

@section ('breadcrumbs')
    <h2>新建商品规格</h2>
    <ol class="breadcrumb">
        <li><a href="{!!route('admin.store.index')!!}"><i class="fa fa-dashboard"></i> 首页</a></li>
        <li class="">{!! link_to_route('admin.goods.spec.index', '规格管理') !!}</li>
        <li class="active">新建商品规格</li>
    </ol>
@stop

@section('content')--}}
    <div class="ibox float-e-margins">
        <div class="ibox-content" style="display: block;">

            {!! Form::open( [ 'url' => [route('admin.goods.spec.store')], 'method' => 'POST', 'id' => 'base-form','class'=>'form-horizontal'] ) !!}

            <div class="form-group">
                {!! Form::label('spec_name','系统名称：', ['class' => 'col-lg-2 control-label']) !!}
                <div class="col-lg-9">
                    <input type="text" class="form-control" name="spec_name" placeholder="" required>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('name','显示名称：', ['class' => 'col-lg-2 control-label']) !!}
                <div class="col-lg-9">
                    <input type="text" class="form-control" name="name" placeholder="" required>
                </div>
            </div>



            {{--<div class="form-group">--}}
                {{--{!! Form::label('name','显示类型：', ['class' => 'col-lg-2 control-label']) !!}--}}
                {{--<div class="col-lg-9">--}}
                    {{--<input name="type" type="radio" value="1" checked /> 文字--}}
                    {{--<input name="type" type="radio" value="2"/> 图片--}}
                {{--</div>--}}
            {{--</div>--}}


            <div class="hr-line-dashed"></div>
            <div class="form-group">
                <div class="col-md-offset-2 col-md-8 controls">
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
{{--@endsection

@section('after-scripts-end')--}}
    <script>
        $('#base-form').ajaxForm({
            success: function (result) {
                swal({
                    title: "保存成功！",
                    text: "",
                    type: "success"
                }, function () {
                    window.location = '{{route('admin.goods.spec.index')}}';
                });
            }
        });
    </script>
{{--@endsection--}}