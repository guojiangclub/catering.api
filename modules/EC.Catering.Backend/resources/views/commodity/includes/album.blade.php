<style type="text/css">
    /*#filePicker,.webuploader-pick{display: block; z-index: 9999; position: absolute;}*/
</style>
<div class="tab-pane" id="tab_4">
    <div class="panel-body">

        <div class="table-responsive">
            <table class="table table-bordered table-stripped" id="mp_menu_table">
                <thead>
                <tr>
                    <th>
                        主图
                    </th>
                    <th>
                        预览
                    </th>
                    <th>
                        图片链接
                    </th>
                    <th>
                        SKU
                    </th>
                    <th>
                        排序(数字越大排在越前)
                    </th>
                    <th>
                        是否显示
                    </th>
                    <th>
                        操作
                    </th>
                </tr>
                </thead>
                <tbody>
                @if(isset($goods_info))
                    @foreach($goods_info->GoodsPhotos as $key => $val)
                        <tr data-id="{{$val['code']}}" class="top_menu" id="menu_id_{{$val['code']}}">
                            <td valign="middle"><input name="_is_default" type="radio" value="{{$val['code']}}" {{$val->CheckedStatus}}  /></td>
                            <td>
                                <img src="{{$val['url']}}" style="max-width: 100px;">
                            </td>
                            <td>
                                <input type="hidden" name="_imglist[{{$val['code']}}][url]" value="{{$val['url']}}">
                                <input type="text" class="form-control" disabled="" value="{{$val['url']}}">
                            </td>
                            <td>
                                <input type="text" class="form-control" name="_imglist[{{$val['code']}}][sku]" value="{{$val['sku']}}">
                            </td>
                            <td>
                                <input type="text" class="form-control" name="_imglist[{{$val['code']}}][sort]" value="{{$val['sort']}}">
                            </td>
                            <td>
                                <input name="_imglist[{{$val['code']}}][flag]" type="radio" value="1" {{$val['flag'] == 1?'checked':''}} /> 是
                                <input name="_imglist[{{$val['code']}}][flag]" type="radio" value="0" {{$val['flag'] == 0?'checked':''}}  /> 否
                            </td>
                            <td>
                                <a href="javascript:;" class="btn btn-white" onclick="delAlbumImg(this)"><i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>
        <div class="form-group">
            {{--<label class="col-sm-2 control-label">产品相册：</label>--}}
            <div class="col-sm-12">
                <div id="upload" class="btn btn-primary">选择图片</div>

                <div class="clearfix"></div>
            </div>
        </div>
        <script type="text/x-template" id="top_menu_template">
            <tr data-id="{MENU_ID}" class="top_menu" id="menu_id_{MENU_ID}">
                <td valign="middle">
                    <input name="_is_default" type="radio" value="{MENU_ID}"  />
                </td>
                <td>
                    <img src="{url}" style="max-width: 100px;">
                </td>
                <td>
                    <input type="hidden" name="_imglist[{MENU_ID}][url]" value="{url}">
                    <input type="text" class="form-control" disabled="" value="{url}">
                </td>
                <td>
                    <input type="text" class="form-control" name="_imglist[{MENU_ID}][sku]" value="">
                </td>
                <td>
                    <input type="text" class="form-control" name="_imglist[{MENU_ID}][sort]" value="9">
                </td>
                <td>
                    <input name="_imglist[{MENU_ID}][flag]" type="radio" value="1" checked=checked /> 是
                    <input name="_imglist[{MENU_ID}][flag]" type="radio" value="0"  /> 否
                </td>
                <td>
                    <a href="javascript:;" class="btn btn-white"
                       onclick="delAlbumImg(this)"><i class="fa fa-trash"></i>
                    </a>
                </td>
            </tr>
        </script>
    </div>

</div><!-- /.tab-pane -->