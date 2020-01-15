@extends('catering-backend::dashboard')

@section ('title','导入商品')

@section('after-styles-end')
    {!! Html::style(env("APP_URL").'/assets/backend/libs/webuploader-0.1.5/webuploader.css') !!}
@stop


@section ('breadcrumbs')
    <h2>导入商品</h2>
    <ol class="breadcrumb">
        <li><a href="{!!route('admin.store.index')!!}"><i class="fa fa-dashboard"></i> 首页</a></li>
        <li class="active">导入商品</li>
    </ol>

@stop

@section('content')

    <div class="ibox float-e-margins">
        <div class="ibox-content" style="display: block;">
            <div class="nav-tabs-custom">
                <div class="tab-content">

                    <div class="tab-pane active" id="tab_1">
                        <div class="form-group">
                            {!! Form::label('name','导入商品：', ['class' => 'col-md-2 control-label']) !!}
                            <div class="col-md-5">
                                <input type="hidden" name="upload_excel"/>
                                <div id="filePicker">选择文件</div>
                                <p class="update_true"></p>
                                <a no-pjax href="{{url('/assets/template/import_goods.xlsx')}}" target="_self">商品导入模板下载</a>
                            </div>
                            <div class="col-md-5">
                                <select name="import_type" class="form-control">
                                    <option value="goods">商品导入</option>
                                    <option value="add">修改商品数据</option>
                                </select>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="well">
                            <div class="pull-left">

                                <button class="btn btn-success add-button" data-toggle="modal-filter"
                                        disabled
                                        data-target="#modal" data-backdrop="static" data-keyboard="false"
                                        data-url="{{route('admin.goods.import.importGoodsModal')}}">
                                    执行导入
                                </button>

                            </div>

                            <div class="clearfix"></div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modal" class="modal inmodal fade" data-keyboard=false data-backdrop="static"></div>
@endsection

@section('before-scripts-end')
    {!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.form.min.js') !!}
    {!! Html::script(env("APP_URL").'/assets/backend/libs/webuploader-0.1.5/webuploader.js') !!}
    @include('catering-backend::commodity.import.script')
@stop
