@extends('catering-backend::layouts.bootstrap_modal')

@section('modal_class')
    modal-lg
@stop
@section('title')
    批量修改商品信息
@stop

@section('after-styles-end')
    {!! Html::style(env("APP_URL").'/assets/backend/libs/ladda/ladda-themeless.min.css') !!}
    {!! Html::style(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.min.css') !!}
@stop



@section('body')
    <div class="row">

        {{--<form method="POST" action="{{route('admin.orders.saveimport')}}" accept-charset="UTF-8"--}}
              {{--id="import_form" class="form-horizontal">--}}
            <input type="hidden" class="_token" value="{{ csrf_token() }}">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="exampleInputEmail1" class="col-sm-3 control-label">库存：</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="store_nums" placeholder=""
                               value="" required>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <label for="exampleInputEmail1" class="col-sm-3 control-label">是否注册：</label>
                    <div class="col-sm-9">
                        <select class="form-control status" name="status">
                            <option value="" >请选择</option>
                            <option value="2" >未注册</option>
                            <option value="3" >已注册</option>
                        </select>
                    </div>
                </div>


            </div>
        {{--</form>--}}
    </div>
@stop

{!! Html::style(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.min.css') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/datepicker/bootstrap-datetimepicker.zh-CN.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.form.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/webuploader-0.1.5/webuploader.js') !!}
@section('footer')
    <button type="button" class="btn btn-link exit" data-dismiss="modal">取消</button>
    {{--<button type="submit" class="btn btn-primary" data-toggle="form-submit" data-target="#delivers_from">保存--}}
    {{--</button>--}}
    <button type="button"  id="send" class="ladda-button btn btn-primary " data-style="slide-right" data-toggle="" data-target="#import_form">导出
    </button>
    <script type="text/javascript">
         $('#send').click(function(){
            var data={
               'brand_id':$('.brand_id').val(),
               'status':$('.status').val(),
               'stime':$('#stime').val(),
               'etime':$('#etime').val(),
               '_token':$('._token').val()
            }
            var url='/admin/store/registrations/get_regexport';
            $.ajax({
                type: 'POST',
                url: url,
                data:data,
                success: function(date){
//                    console.log(date);
                    window.location.href="{{route('admin.orders.download',['url'=>''])}}"+"/"+date;

                    setTimeout(function(){
                        $('.close').trigger('click');
//                        $('.batch').ladda().ladda('stop');

                    },2000)
                }});

        });
        $(function(){
            $('.status').change(function(e){
                if($(".status option:selected").val()==3){
                    $('.time').removeClass('hidden')
                }else{
                    $('.time').addClass('hidden')
                }
            })

        })
    </script>

@stop






