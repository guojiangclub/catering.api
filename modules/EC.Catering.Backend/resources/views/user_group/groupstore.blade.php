<div class="ibox float-e-margins">
    <div class="ibox-content" style="display: block;">
        @if($action=='edit')
            {!! Form::model($group_edit, ['route' => ['admin.users.groupstore'], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'POST','id'=>'base-form']) !!}
        @else
            {!! Form::open( [ 'url' => [route('admin.users.groupstore')], 'method' => 'POST','id' => 'base-form','class'=>'form-horizontal'] ) !!}
        @endif
        <input type="hidden" name="id" value="{{$group_edit->id}}"/>
        <div class="form-group">
            {!! Form::label('name', '会员等级名称', ['class' => 'col-lg-2 control-label']) !!}

            <div class="col-lg-10">
                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => '']) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('name', $title, ['class' => 'col-lg-2 control-label']) !!}
            <div class="col-lg-3">

                <div class="col-md-5">{!! Form::text('min', null, ['class' => 'form-control', 'placeholder' => '','style'=>'margin-left: 0px']) !!}</div>
                <div class="col-sm-1">~</div>
                <div class="col-md-5">{!! Form::text('max', null, ['class' => 'form-control', 'placeholder' => '']) !!}</div>
            </div>
        </div>


        <div class="form-group">
            {!! Form::label('name', '等级', ['class' => 'col-lg-2 control-label']) !!}
            <div class="col-lg-10">
                @if($group_edit->grade!=1)
                    {!! Form::text('grade', null, ['class' => 'form-control', 'placeholder' => '']) !!}
                @else
                    <input type="text" value="{{$group_edit->grade}}" name="grade" class="form-control"/>
                @endif
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-2 control-label">会员权益：</label>
            <div class="col-lg-10">
                <select class="form-control" id="rights_ids" name="rights_ids[]" multiple>
                    @if($action=='edit')
                        @foreach($rights as $right)
                            <option value="{{ $right->id }}"
                                    @if(in_array($right->id, $group_edit->rights_ids)) selected @endif>{{ $right->name }}</option>
                        @endforeach
                    @else
                        @foreach($rights as $right)
                            <option value="{{ $right->id }}">{{ $right->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('pic', '背景图', ['class' => 'col-lg-2 control-label']) !!}

            <div class="col-lg-10">
                <input type="hidden" name="pic"
                       value="{{$group_edit->pic}}">
                <img class="shop_show_logo" src="{{$group_edit->pic}}" alt="" style="max-width: 250px;">
                <div id="logoPicker">选择图片</div>
                <div class="clearfix"></div>
            </div>
        </div>

        <div class="hr-line-dashed"></div>

        <div class="form-group">
            <label class="col-lg-2 control-label"></label>
            <div class="col-lg-10">
                <input type="submit" class="btn btn-success" value="保存"/>
                <a href="{{route('admin.users.grouplist')}}" class="btn btn-danger">取消</a>
            </div>
        </div>

        {!! Form::close() !!}
    </div>
</div>
{!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.form.min.js') !!}
{!! Html::script(env("APP_URL").'/assets/backend/libs/webuploader-0.1.5/webuploader.js') !!}
@include('catering-backend::user_group.script')