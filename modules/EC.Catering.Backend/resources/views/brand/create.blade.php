{{--@extends ('catering-backend::dashboard')

@section ('title','产品品牌管理')

@section('after-styles-end')
    {!! Html::style(env("APP_URL").'/assets/backend/libs/webuploader-0.1.5/webuploader.css') !!}
@stop

@section ('breadcrumbs')
    <h2>添加品牌</h2>
    <ol class="breadcrumb">
        <li><a href="{!!route('admin.store.index')!!}"><i class="fa fa-dashboard"></i> 首页</a></li>
        <li class="">{!! link_to_route('brand.index', '品牌管理') !!}</li>
        <li class="active">添加品牌</li>
    </ol>
@stop

@section('content')--}}
    <div class="ibox float-e-margins">
        <div class="ibox-content" style="display: block;">
            {!! Form::open( [ 'url' => [route('brand.store')], 'method' => 'POST','id' => 'base-form','class'=>'form-horizontal'] ) !!}
            <input type="hidden" name="id" value="">
            <div class="form-group">
                {!! Form::label('name','品牌名称：', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    <input type="text" class="form-control" name="name" placeholder="" required>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('name','品牌网址：', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    <input type="text" class="form-control" name="url" id="url" placeholder="" required>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('name','品牌LOGO：', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    <input type="hidden" name="logo" value=""/>
                    {{--<div id="fileList" class="uploader-list"></div>--}}
                    <img class="banner-image" src="">
                    <div id="filePicker">选择图片</div>

                </div>
            </div>

            <div class="form-group">
                {!! Form::label('name','品牌描述：', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    <textarea class="form-control" name="description"></textarea>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('name','排序：', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    <input type="text" class="form-control" id="sort" name="sort" placeholder="">
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('name','是否显示：', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    <div class="radio">
                        <label>
                            <input type="radio" name="is_show" id="is_show" value="1" checked="">
                            是
                        </label>
                        <label>
                            <input type="radio" name="is_show" id="is_show" value="0">
                            否
                        </label>
                    </div>
                </div>
            </div>

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
    {!! Html::script(env("APP_URL").'/vendor/libs/webuploader-0.1.5/webuploader.js') !!}
    @include('catering-backend::brand.script')
{{--@stop--}}
