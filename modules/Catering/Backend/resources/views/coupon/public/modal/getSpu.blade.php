@extends('store-backend::layouts.bootstrap_modal')

@section('modal_class')
    modal-lg
@stop
@section('title')
    @if($action == 'view')
        查看已选择店铺
    @elseif($action == 'view_exclude')
        查看已排除店铺
    @else
        选择店铺
    @endif
@stop

@section('after-styles-end')
    {!! Html::style(env("APP_URL").'/assets/backend/libs/ladda/ladda-themeless.min.css') !!}
@stop


@section('body')
    <div class="row">
        <div class="col-md-12" style="margin-top: 10px">
            <label class="col-sm-2 control-label" style="margin-top: 8px">所属集市：</label>
            <div class="col-sm-3">
                <select class="form-control" name="market_id">
                    <option value="">请选择</option>
                    @foreach($markets as $market)
                        <option value="{{ $market->id }}">{{ $market->name }}</option>
                    @endforeach
                </select>
            </div>
            <label class="col-sm-2 control-label" style="margin-top: 8px">店铺名称：</label>
            <div class="col-sm-3">
                <input type="text" class="form-control" name="name" placeholder="" />
            </div>
            <button type="button" id="send" class="ladda-button btn btn-primary">搜索</button>
        </div>


        <div class="clearfix"></div>
        <div class="hr-line-dashed "></div>

        <div class="panel-body">
            <h3 class="header">请选择店铺：</h3>
            <div class="table-responsive" id="goodsList">
                <table class="table table-hover table-striped">
                    <thead>
                    <!--tr-th start-->
                    <tr>
                        <th>店铺名称</th>
                        <th>所属集市</th>
                    </tr>
                    <!--tr-th end-->
                    </thead>

                    <tbody class="page-goods-list">

                    </tbody>
                </table>
            </div><!-- /.box-body -->

            <div class="pages">

            </div>
        </div>
    </div>


    <script type="text/html" id="page-temp">
        <tr>
            <td>
                {#name#}
            </td>
            <td>
                {#market_name#}
            </td>
            <td>
                <button onclick="changeSelect(this, '{{$action}}')" class="btn btn-circle {#class#}"
                        type="button" data-id="{#id#}"><i class="fa fa-{#icon#}"></i>
                </button>
            </td>
        </tr>
    </script>
@stop
{!! Html::script(env("APP_URL").'/assets/backend/libs/ladda/spin.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/ladda/ladda.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/ladda/ladda.jquery.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/loader/jquery.loader.min.js') !!}


@section('footer')
    <input type="hidden" id="temp_selected_spu">
    <input type="hidden" id="temp_exclude_spu">

    <button type="button" class="btn btn-link" data-dismiss="modal">取消</button>

    <button type="button" onclick="sendIds('{{$action}}');" class="ladda-button btn btn-primary"> 确定
    </button>

    @include('backend-shitang::coupon.public.modal.script')
    {!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.el/common.js') !!}
    {!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.el/jquery.http.js') !!}
    {!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.el/page/jquery.pages.js') !!}
    <script>
        var action = '{{$action}}';
        var paraDiscount = {_token: _token};

        function getList() {

	        var postUrl = '{{route('admin.shitang.getShopData')}}';

	        if (action == 'exclude' || action == 'view_exclude') {
		        var selected_spu = $('#exclude_spu').val();
	        } else {
		        var selected_spu = $('#selected_spu').val();
	        }

	        $('.pages').pages({
		        page: 1,
		        url: postUrl,
		        get: $.http.post.bind($.http),
		        body: {
			        _token: _token,
			        action: action,
			        ids: paraDiscount.ids,
			        market_id: $("select[name=market_id] option:selected").val(),
			        name: $("input[name=name]").val()
		        },
		        marks: {
			        total: 'data.last_page',
			        index: 'data.current_page',
			        data: 'data'
		        }
	        }, function (data) {
		        var html = '';
		        var ids = data.ids;

		        data.data.forEach(function (item) {
			        if (!~ids.indexOf(String(item.id))) {
				        item.class = 'btn-warning unselect';
				        item.icon = 'times';

			        } else {
				        item.class = 'btn-info select';
				        item.icon = 'check';
			        }

			        item.market_name = item.market.name;

			        html += $.convertTemplate('#page-temp', item, '');
		        });
		        $('.page-goods-list').html(html);
	        });
        }

        $(document).ready(function () {

	        if (action == 'exclude' || action == 'view_exclude') {
		        $('#temp_exclude_spu').val($('#exclude_spu').val());
		        paraDiscount.ids = $('#temp_exclude_spu').val();
	        } else {
		        $('#temp_selected_spu').val($('#selected_spu').val());
		        paraDiscount.ids = $('#temp_selected_spu').val();

	        }

	        getList();
        });

        $('#send').on('click', function () {
	        getList();
        });
    </script>
@stop






