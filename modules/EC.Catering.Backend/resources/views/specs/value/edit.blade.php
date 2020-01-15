{{--@extends ('catering-backend::dashboard')

@section('after-styles-end')--}}
    <style type="text/css">
        .color-span {
            width: 70px;
            display: block;
            height: 28px;
            line-height: 28px;
            color: #fff;
            text-align: center
        }
    </style>
{{--@endsection

@section ('title','规格')

@section ('breadcrumbs')
    <h2>编辑商品规格值</h2>
    <ol class="breadcrumb">
        <li><a href="{!!route('admin.store.index')!!}"><i class="fa fa-dashboard"></i> 首页</a></li>
        <li class="">{!! link_to_route('admin.goods.spec.index', '规格管理') !!}</li>
        <li class="active">编辑商品{{$spec->name}}规格值</li>
    </ol>
@stop

@section('content')--}}
    <div class="ibox float-e-margins">
        <div class="ibox-content" style="display: block;">
            <input type="hidden" value="{{$spec->id}}" name="spec_id">
            <div class="form-group">
                <a class="btn btn-w-m btn-primary" data-toggle="modal"
                   data-target="#spu_modal" data-backdrop="static" data-keyboard="false"
                   data-url="{{route('admin.goods.spec.value.addSpecValue',['spec_id'=>$spec->id])}}">
                    添加规格值</a>
            </div>

            <div class="form-group">
                <table class='border_table table table-bordered'>
                    <thead>
                    <tr>
                        <th>规格值</th>
                        @if($spec->id == 2)
                            <th>颜色值</th>
                            <th>所属色系</th>
                        @endif
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody id='spec_value_box'>

                    </tbody>
                </table>

                <div class="pages">

                </div>

            </div>


            {{--<div class="hr-line-dashed"></div>--}}
            {{--<div class="form-group">--}}
                {{--<div class="col-md-offset-2 col-md-8 controls">--}}
                    {{--<button type="submit" class="btn btn-primary">保存</button>--}}
                {{--</div>--}}
            {{--</div>--}}

            {{--            {!! Form::close() !!}--}}
                    <!-- /.tab-content -->
        </div>
    </div>

    <div id="spu_modal" class="modal inmodal fade"></div>
{{--@endsection

@section('before-scripts-end')

@stop--}}

@include('catering-backend::specs.script')

