@role(['super_admin','admin'])
<div class="pull-right" style="margin-bottom:10px">
    <div class="btn-group">
        <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown"
                aria-expanded="false">
            会员 <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
            <li><a href="{{route('admin.users.index')}}">所有会员</a></li>
            <li><a href="{{route('admin.users.create')}}">创建会员</a></li>
            <li class="divider"></li>
            <li><a href="{{route('admin.users.banned')}}">禁用的会员</a></li>
            <li><a href="{{route('admin.users.deleted')}}">删除的会员</a></li>
        </ul>
    </div>
</div>

<div class="clearfix"></div>

@endrole