{{--@extends('catering-backend::dashboard')

@section ('title','新增商品')

@section('after-styles-end')--}}
{!! Html::style(env("APP_URL").'/assets/backend/libs/webuploader-0.1.5/webuploader.css') !!}
{!! Html::style(env("APP_URL").'/assets/backend/libs/Tagator/fm.tagator.jquery.css') !!}
{!! Html::script(env("APP_URL").'/vendor/libs/md5.js') !!}
{!! Html::style(env("APP_URL").'/assets/backend/libs/jquery.el/spec/base.css') !!}
<style type="text/css">
    .filelist {
        clear: both;
        width: 100%;
        float: left;
        padding-bottom: 20px;
    }

    .filelist li {
        list-style: none;
        text-align: center;
        margin-right: 12px;
        float: left;
        width: 103px;
        height: 103px
    }

    .filelist li img {
        width: 100px;
        height: 100px
    }

    .filelist .current {
        border: 3px #f60 solid;
    }

    #sku-builder .color-block {
        display: inline-block;
        width: 12px;
        height: 12px;
        border: 1px solid #cccccc;
        border-radius: 2px;
        margin-right: 3px;
    }

    div#containerpc, div#container {
        overflow: hidden
    }

    #sku-table input[type=file] {
        display: inline-block;
        margin-top: 10px;
    }

    #sku-table th {
        min-width: 90px
    }

    #sku-table th.sku-th {
        min-width: 170px
    }

    #sku-table input {
        width: 100%
    }

    .spec_img_th {
        display: block !important;
    }

    .spec_img_th_active {
        display: none;
    }

    .extend_image > img {
        width: 110px;
        height: 110px;
        cursor: pointer
    }
</style>
{{--@stop

@section('breadcrumbs')
    <h2>新建商品</h2>
    <ol class="breadcrumb">
        <li><a href="{!!route('admin.store.index')!!}"><i class="fa fa-dashboard"></i> 首页</a></li>
        <li class="active">新建商品</li>
    </ol>
@endsection

@section('content')--}}
@if (count($errors) > 0)
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="tabs-container">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">款式信息（SPU）<i
                        class="glyphicon"></i></a></li>
        <li class=""><a href="#tab_3" data-toggle="tab" aria-expanded="false">规格信息（SKU）<i class="glyphicon"></i></a>
        </li>
        <li class="editor_li"><a href="#tab_2" data-toggle="tab" data-type="ue"
                                 aria-expanded="false">详细描述(Mobile)</a></li>
        <li class="editor_li"><a href="#tab_6" data-toggle="tab" data-type="uepc" aria-expanded="false">详细描述(PC)</a>
        </li>
        <li class="editor_li"><a href="#tab_8" data-toggle="tab" data-type="ue_collocation"
                                 aria-expanded="false">推荐搭配</a>
        </li>
        <li class=""><a href="#tab_4" data-toggle="tab" aria-expanded="false">橱窗图</a></li>
        <li class=""><a href="#tab_5" data-toggle="tab" aria-expanded="false">SEO设置</a></li>
        {{--@if(config('store.goods_point'))--}}
        <li class=""><a href="#tab_7" data-toggle="tab" aria-expanded="false">积分规则</a></li>
        {{--@endif--}}
        <li class=""><a href="#tab_9" data-toggle="tab" aria-expanded="false">商品视频</a></li>
    </ul>

    {!! Form::open( [ 'url' => [route('admin.goods.store')], 'method' => 'POST', 'id' => 'base-form','class'=>'form-horizontal'] ) !!}
    <div class="tab-content">

        <div class="tab-pane active" id="tab_1">
            <div class="panel-body">
                <input type="hidden" name="id" value="0">
                <input type="hidden" name="img" value="">
                <div class="form-group">
                    <label class="col-sm-2 control-label">商品名称：</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="name" placeholder="">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">商品品牌：</label>
                    <div class="col-sm-10">
                        <select class="form-control" name="brand_id">
                            <option value="">请选择</option>
                            @foreach($brands as $item)
                                <option value="{{$item->id}}">{{$item->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">供应商：</label>
                    <div class="col-sm-10">
                        <select class="form-control" name="supplier_id">
                            @foreach($supplier as $item)
                                <option value="{{$item->id}}">{{$item->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{--<div class="form-group">
                    <label class="col-sm-2 control-label">分类组：</label>
                    <div class="col-sm-10">
                        <select class="form-control" name="category_group">
                            <option value="">请选择分类组</option>
                            @foreach($categoryGroup as $item)
                                <option value="{{$item->id}}">{{$item->group_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>--}}
                <input type="hidden" name="category_group" value="{{ $categoryGroup->id }}">


                <div class="form-group">
                    <label class="col-sm-2 control-label">所属分类：</label>
                    <div class="col-sm-10" id="category-box">
                        {{--<div class="panel panel-default">--}}
                        {{--<div class="panel-heading" role="tab" id="headingOne">--}}
                        {{--<h4 class="panel-title">--}}
                        {{--<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">--}}
                        {{--请选择分类（点击折叠/展开）--}}
                        {{--</a>--}}
                        {{--</h4>--}}
                        {{--</div>--}}
                        {{--<div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">--}}
                        {{--<div class="panel-body" id="category-box">--}}

                        {{--</div>--}}
                        {{--</div>--}}
                        {{--</div>--}}
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">商品数量：</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" name="store_nums" placeholder="">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">商品编号：</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="goods_no" placeholder="">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">市场价：</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="market_price" placeholder="">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">销售价：</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="sell_price" placeholder="">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">是否上架：</label>
                    <div class="col-sm-10">
                        <input name="is_del" type="radio" value="0" checked=checked/> 是
                        <input name="is_del" type="radio" value="2"/> 否
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">是否共享：</label>
                    <div class="col-sm-10">
                        <input name="is_share" type="radio" value="1" checked=checked/> 是
                        <input name="is_share" type="radio" value="0"/> 否
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">是否推荐：</label>
                    <div class="col-sm-10">
                        <input name="is_commend" type="radio" value="1"/> 是
                        <input name="is_commend" type="radio" value="0" checked/> 否
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-sm-2 control-label">是否新品：</label>
                    <div class="col-sm-10">
                        <input name="is_old" type="radio" value="0" checked/> 是
                        <input name="is_old" type="radio" value="1"/> 否
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">是否赠品：</label>
                    <div class="col-sm-10">
                        <input name="is_largess" type="radio" value="1"/> 是
                        <input name="is_largess" type="radio" value="0" checked/> 否
                    </div>
                </div>

                <div class="form-group" id="integral_form" style="display: none">
                    <label class="col-sm-2 control-label">兑换所需积分：</label>
                    <div class="col-sm-10">
                        <input name="redeem_point" value="0" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">商品模型：</label>
                    <div class="col-sm-10">
                        <select class="form-control" name="model_id" onchange="create_attr(this.value)">
                            <option value="">请选择产品模型</option>
                            @foreach($models as $item)
                                <option value="{{$item['id']}}">{{$item['name']}}</option>
                            @endforeach
                        </select>
                        <label>可以加入商品参数，比如：型号，年代，款式...【商品一经发布无法修改】</label>
                    </div>

                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">商品参数：</label>
                    <div class="col-sm-10" id="propert_table">

                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">产品标签：</label>
                    <div class="col-sm-10">
                        {!! Form::text('tags',  '' , ['class' => 'form-control form-inputTagator col-sm-10','id'=>'inputGoodsTags', 'placeholder' => '']) !!}
                        <label>输入产品标签名称，按回车添加</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">排序：</label>
                    <div class="col-sm-10">
                        <input class="form-control" name="sort" value="99" />
                    </div>
                </div>

                @if(str_contains(env('APP_URL'),'jw'))
                    @include('catering-backend::commodity.includes.extend_image')
                @endif

            </div>
        </div><!-- /.tab-pane -->


        <div class="tab-pane" id="tab_3">
            <div class="panel-body">
                @include('catering-backend::commodity.includes.spec')
            </div>
        </div>


        <div class="tab-pane" id="tab_2">
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-1 control-label">内容同步设置：</label>
                    <div class="col-sm-10">
                        <input name="sync" type="radio" value="0" checked/> 不同步
                        <input name="sync" type="radio" value="1"/> 同步至PC端
                    </div>
                </div>
                <div id="upload_container" class="btn btn-primary" style="margin-bottom: 10px;">插入相册图片</div>
                <script id="container" name="content"
                        type="text/plain"></script>
            </div>

        </div><!-- /.tab-pane -->

        @include('catering-backend::commodity.includes.album')

        <div class="tab-pane" id="tab_5">
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-2 control-label">SEO关键词：</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="keywords" placeholder="">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">SEO描述：</label>
                    <div class="col-sm-10">
                        <textarea class="form-control" name="description" placeholder=""></textarea>

                    </div>
                </div>
            </div>

        </div><!-- /.tab-pane -->

        <div class="tab-pane" id="tab_6">
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-1 control-label">内容同步设置：</label>
                    <div class="col-sm-10">
                        <input name="sync" type="radio" value="2"/> 同步至移动端
                    </div>
                </div>
                <div id="upload_containerpc" class="btn btn-primary" style="margin-bottom: 10px;">插入相册图片</div>
                <script id="containerpc" name="contentpc"
                        type="text/plain"></script>
            </div>

        </div><!-- /.tab-pane -->

        <div class="tab-pane" id="tab_8">
            <div class="panel-body">
                <div id="upload_collocation" class="btn btn-primary" style="margin-bottom: 10px;">插入相册图片</div>
                <script id="collocation" name="collocation"
                        type="text/plain"></script>
            </div>
        </div><!-- /.tab-pane -->

        <div class="tab-pane" id="tab_7">
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-2 control-label">积分使用：<i class="fa fa-question-circle"
                                                                  data-toggle="tooltip" data-placement="top"
                                                                  data-original-title="下单购买该商品是否可使用积分抵扣"></i>
                    </label>
                    <div class="col-sm-10">
                        <input type="radio" class="form-control" name="point_can_use_point"
                               value="0">
                        否
                        <input type="radio" class="form-control" name="point_can_use_point"
                               value="1" checked> 是
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">积分返赠：<i class="fa fa-question-circle"
                                                                  data-toggle="tooltip" data-placement="top"
                                                                  data-original-title="是否参与购物获得积分"></i>
                    </label>
                    <div class="col-sm-10">
                        <input type="radio" class="form-control" name="point_status"
                               value="0" {{ !$point_rule?'checked':'' }}>
                        不参与
                        <input type="radio" class="form-control" name="point_status"
                               value="1" {{ $point_rule?'checked':'' }}> 参与
                    </div>
                </div>

                <div id="point_setting_box" style="display: {{$point_rule?'':'none'}}">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">计算方式：</label>
                        <div class="col-sm-10">
                            <input type="radio" class="form-control" name="point_type"
                                   value="0" {{ !$point_rule?'checked':'' }}> 固定值
                            <input type="radio" class="form-control" name="point_type"
                                   value="1" {{ $point_rule?'checked':'' }}> 比例
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">积分数值：</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="point_value" value="{{$point_rule_value}}">
                            <p>说明：当计算方式为<b>比例</b>时，积分数值单位为 %</p>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /.tab-pane -->

        <div class="tab-pane" id="tab_9">
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-2 control-label">视频背景：</label>
                    <div class="col-sm-10">
                        <input type="radio" class="form-control" name="extra[video][status]" value="1"> 启用
                        <input type="radio" class="form-control" name="extra[video][status]" value="0"
                               checked="checked"> 禁用
                    </div>
                </div>

                <div class="form-group">
                    <input type="hidden" name="extra[video][url]">
                    {{--<label class="col-sm-2 control-label">产品相册：</label>--}}
                    <label class="col-sm-2 control-label">视频文件：</label>
                    <div class="col-sm-10">
                        <p id="videoOriginName"></p>
                        <div id="videoPicker">选择视频</div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>

        </div><!-- /.tab-pane -->


        <div class="ibox-content m-b-sm border-bottom text-right">
            <input type="submit" class="btn btn-success" data-toggle="form-submit" data-target="#base-form"
                   value="保存">


        </div>


    </div><!-- /.tab-content -->
    {!! Form::close() !!}


</div>
{{--@stop


@section('before-scripts-end')--}}
<div class="popup">
    <div class="sortContainer">
        <h2 class="sortTitle">销售属性排序</h2>
        <p>销售属性支持排序，您可以直接通过拖拽调节顺序</p>
        <div class="sortMain">
            <ul id="sort">

            </ul>
        </div>

        <div class="btn-box">
            <button class="btn btn-cancle sortCancle pull-right">取消</button>
            <button class="btn btn-success sortOK pull-right">确认</button>
        </div>
    </div>
</div>

{{--@include('vendor.ueditor.assets')--}}
@include('UEditor::head')

{!! Html::script(env("APP_URL").'/vendor/libs/jquery.form.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/webuploader-0.1.5/webuploader.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/Tagator/fm.tagator.jquery.js') !!}

{!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.el/common.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.el/spec/Sortable.js?v=201806041167') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.el/spec/spec.js?v=201806041167') !!}
{{--{!! Html::script('assets/backend/admin/js/common.js') !!}--}}
{{--{!! Html::script('assets/backend/admin/js/spec.js') !!}--}}

<script>
    var postImgUrl = '{{route('upload.image',['_token'=>csrf_token()])}}';
    // 初始化Web Uploader
    $(document).ready(function () {

        var uploader2 = WebUploader.create({
            auto: true,
            swf: '{{url(env("APP_URL").'/assets/backend/libs/webuploader-0.1.5/Uploader.swf')}}',
            server: '{{route('upload.image',['strategy'=>'video','_token'=>csrf_token()])}}',
            pick: '#videoPicker',
            fileVal: 'upload_image',
            accept: {
                title: 'Video',
                extensions: 'mp4,wmv,avi,mpg,rmvb,m4v'
            }
        });

        // 文件上传成功，给item添加成功class, 用样式标记上传成功。
        uploader2.on('uploadSuccess', function (file, response) {
            console.log(response);
            $('input[name="extra[video][url]"]').val(response.url);
            $('#videoOriginName').text(response.original_name);
        });

    });
</script>
<script>
    $(function () {
        $('#inputGoodsTags').tagator({
            autocomplete: ['标签提示1', '标签提示2', 'third', 'fourth', 'fifth', 'sixth', 'seventh', 'eighth', 'ninth', 'tenth']
        });

        $('#filePicker').children().eq(0).css({'width': '90px', 'height': '30px'});

    });

</script>

{{--@stop--}}
@include('catering-backend::commodity.script')
