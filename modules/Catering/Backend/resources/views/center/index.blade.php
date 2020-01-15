<style type="text/css">
    .coupon-tips {
        font-size: 12px;
        color: #a7b1c2;
    }
</style>

<div class="tabs-container">
    <ul class="nav nav-tabs">
        <li class="{{ Active::query('status','') }}">
            <a href="{{route('admin.shitang.gift.center.index')}}" no-pjax> 所有活动<span class="badge"></span></a>
        </li>
        <li class="{{ Active::query('status','nstart') }}">
            <a href="{{route('admin.shitang.gift.center.index',['status'=>'nstart'])}}" no-pjax> 未开始<span class="badge"></span></a>
        </li>
        <li class="{{ Active::query('status','ing') }}"><a href="{{route('admin.shitang.gift.center.index',['status'=>'ing'])}}" no-pjax> 进行中<span class="badge"></span></a>
        </li>
        <li class="{{ Active::query('status','end') }}">
            <a href="{{route('admin.shitang.gift.center.index',['status'=>'end'])}}" no-pjax>已结束<span class="badge"></span></a>
        </li>
    </ul>

    <div class="tab-content">
        <div id="tab-1" class="tab-pane active">

            {!! Form::open( [ 'route' => ['admin.shitang.gift.center.index'], 'method' => 'GET', 'id' => 'discount-form','class'=>'form-horizontal'] ) !!}
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <a class="btn btn-primary" href="{{ route('admin.shitang.gift.center.create') }}" no-pjax>添加活动</a>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" name="title" value="{{request('title')}}" placeholder="活动名称" class=" form-control"> <span class="input-group-btn">
                                <button type="submit" class="btn btn-primary">查找</button></span></div>
                    </div>

                </div>

                {!! Form::close() !!}

                <div class="hr-line-dashed"></div>

                <div class="table-responsive">
                    @if(count($activities)>0)
                        <table class="table table-hover table-striped">
                            <tbody>
                            <tr>
                                <th>活动名称</th>
                                <th>活动banner</th>
                                <th>有效期</th>
                                <th>关联优惠券</th>
                                <th>小程序跳转路径</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            @foreach ($activities as $item)
                                <tr>
                                    <td>{{$item->title}}</td>
                                    <td><img src="{{ $item->activity_banner }}" width="100"></td>
                                    <td>{{$item->starts_at}} 至 <br> {{$item->ends_at}}</td>
                                    <td>
                                        @foreach($item->items as $value)
                                            <a target="_blank" href="{{ route('admin.shitang.coupon.edit', ['id'=>$value->discount->id]) }}">{{ $value->discount->title }}</a>
                                            <br>
                                        @endforeach
                                    </td>
                                    <td>pages/coupon/center/center</td>
                                    <td>{{ $item->status == 1 ? '启用' : '下架' }}</td>
                                    <td>
                                        <a class="btn btn-xs btn-primary" href="{{route('admin.shitang.gift.center.edit',['id'=>$item->id])}}" no-pjax><i data-toggle="tooltip" data-placement="top" class="fa fa-pencil-square-o" title="编辑"></i></a>
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="10" class="footable-visible">
                                    {!! $activities->render() !!}
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    @else
                        <div>
                            &nbsp;&nbsp;&nbsp;当前无数据
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<div id="modal" class="modal inmodal fade"></div>
<div id="download_modal" class="modal inmodal fade"></div>