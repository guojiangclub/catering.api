<style type="text/css">
    .color-span {
        width: 70px;
        display: block;
        height: 28px;
        line-height: 28px;
        color: #fff;
        text-align: center
    }

    .modal-body {
        padding: 20px 30px 30px 30px !important;
    }
</style>

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
    </div>
</div>

<div id="spu_modal" class="modal inmodal fade"></div>
@include('catering-backend::specs.script')

