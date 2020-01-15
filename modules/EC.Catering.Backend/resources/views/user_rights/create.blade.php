
    <div class="ibox float-e-margins">
        <div class="ibox-content" style="display: block;">
            {!! Form::open( [ 'url' => [route('admin.users.rights.store')], 'method' => 'POST','id' => 'base-form','class'=>'form-horizontal'] ) !!}
            
            <div class="form-group">
                {!! Form::label('name', '权益名称', ['class' => 'col-md-2 control-label']) !!}

                <div class="col-md-10">
                   <input class="form-control" name="name" type="text">
                </div>
            </div><!--form control-->


            <div class="form-group">
                {!! Form::label('name','排序：', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-10">
                    <input class="form-control" name="sort" type="text">
                </div>
            </div><!--form control-->




                    <div class="form-group">
                        {!! Form::label('pic', '图片', ['class' => 'col-md-2 control-label']) !!}

                        <div class="col-md-10">
                            <input type="hidden" name="img">
                            <img class="shop_show_logo" alt=""
                                 style="max-width: 250px;">
                            <div id="logoPicker">选择图片</div>
                            <div class="clearfix"></div>
                        </div>
                    </div>

            <div class="hr-line-dashed"></div>

            <div class="form-group">
                <label class="col-md-2 control-label"></label>
                <div class="col-md-10">
                    <input type="submit" class="btn btn-success" value="保存"/>
                </div>
            </div>

            {!! Form::close() !!}
        </div>
    </div>

            {!! Html::script(env("APP_URL").'/assets/backend/libs/jquery.form.min.js') !!}
            @include('catering-backend::user_rights.script')