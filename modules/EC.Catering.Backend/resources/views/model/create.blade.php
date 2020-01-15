{{--@extends ('catering-backend::dashboard')

@section ('title','新增商品模型')

@section ('breadcrumbs')
    <h2>新增商品模型</h2>
    <ol class="breadcrumb">
        <li><a href="{!!route('admin.store.index')!!}"><i class="fa fa-dashboard"></i> 首页</a></li>
        <li class="">{!! link_to_route('admin.goods.model.index', '模型管理') !!}</li>
        <li class="active">新增商品模型</li>
    </ol>
@stop

@section('content')--}}
    <div class="ibox float-e-margins">
        <div class="ibox-content" style="display: block;">
            {!! Form::open( [ 'url' => [route('admin.goods.model.store')], 'method' => 'POST', 'id' => 'base-form','class'=>'form-horizontal'] ) !!}


            <div class="form-group">
                {!! Form::label('name','模型名称：', ['class' => 'col-lg-2 control-label']) !!}
                <div class="col-lg-9">
                    <input type="text" class="form-control" name="name" placeholder="" required>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('spec','关联规格：', ['class' => 'col-lg-2 control-label']) !!}
                <div class="col-lg-9" id="spec_box">
                    @foreach( $spec as $item)
                        <input type="checkbox" name="spec_ids[]"
                               value="{{$item->id}}"> {{$item->spec_name}} &nbsp;&nbsp;
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('spec','关联商品参数：', ['class' => 'col-lg-2 control-label']) !!}
                <div class="col-lg-9">
                    @foreach( $attributes as $item)
                        <input type="checkbox" name="attr_ids[]" value="{{$item->id}}"> {{$item->name}} &nbsp;&nbsp;
                    @endforeach
                </div>
            </div>


            {{--<div class="form-group">--}}
            {{--{!! Form::label('name','添加商品参数：', ['class' => 'col-lg-2 control-label']) !!}--}}
            {{--<div class="col-lg-9">--}}
            {{--<button id="modelsAddButton" type="button" class="btn btn-w-m btn-primary">添加商品参数</button>--}}
            {{--</div>--}}
            {{--</div>--}}

            {{--<div class="form-group">--}}
            {{--<div class="col-lg-9 col-lg-offset-2">--}}
            {{--<table class='border_table table table-bordered'>--}}
            {{--<thead>--}}
            {{--<tr>--}}
            {{--<th>参数名</th>--}}
            {{--<th>操作方式</th>--}}
            {{--<th width="40%">选择项数据</th>--}}
            {{--<th>是否作为筛选项</th>--}}
            {{--<th>是否作为图表显示</th>--}}
            {{--<th>操作</th>--}}
            {{--</tr>--}}
            {{--</thead>--}}
            {{--<tbody id='spec_box'>--}}

            {{--</tbody>--}}
            {{--</table>--}}
            {{--</div>--}}
            {{--</div>--}}

            <div class="hr-line-dashed"></div>
            <div class="form-group">
                <div class="col-md-offset-2 col-md-8 controls">
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </div>

            {!! Form::close() !!}
                    <!-- /.tab-content -->
        </div>
    </div>
{{--@endsection--}}

{{--@section('before-scripts-end')--}}
    {!! Html::script(env("APP_URL").'/vendor/libs/jquery.form.min.js') !!}
    @include('catering-backend::model.script')
{{--@stop--}}
