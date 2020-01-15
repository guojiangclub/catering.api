<h4>基本规则</h4>
<hr class="hr-line-solid">

<div class="form-group">
    <div class="col-sm-10 col-sm-offset-2">
        <label>
            <input type="checkbox" name="rules[1][type]"
                   value="item_total" class="switch-input"> 订单总金额满
        </label>
        <span class="sw-hold">XX</span>
        <input type="text" style="display: none" name="rules[1][value]" class="sw-value">元
    </div>
</div>

<!--订单总金额 end-->
<div class="hr-line-dashed"></div>


{{--
<!--指定店铺-->
<div class="form-group">
    <div class="col-sm-10 col-sm-offset-2">
        <label>
            <input type="checkbox" value="contains_market_shop" name="rules[2][type]" class="switch-input"> 指定店铺
        </label>

        <fieldset class="sw-value" style="display: none;">
            <div class="form-group">
                <label class="col-sm-2 control-label">店铺：</label>
                <div class="col-sm-10">
                    <a class="btn btn-success" id="chapter-create-btn" data-toggle="modal" data-target="#spu_modal" data-backdrop="static" data-keyboard="false" data-url="{{route('admin.shitang.getShop',['action' => 'add'])}}">
                        点击添加店铺
                    </a>
                    (已添加
                    <i class="countSpu">0</i>
                    个店铺，<a data-toggle="modal" data-target="#spu_modal" data-backdrop="static" data-keyboard="false" data-url="{{route('admin.shitang.getShop', ['action' => 'view'])}}">点击查看</a>)
                    <input type="hidden" id="selected_spu" name="rules[2][value][shop_ids]" value="">
                </div>
            </div>
        </fieldset>
    </div>
</div>
<!--指定店铺 end-->
<div class="hr-line-dashed"></div>

<!--排除指定店铺-->
<div class="form-group">
    <div class="col-sm-10 col-sm-offset-2">
        <label>
            <input type="checkbox" value="exclude_market_shop" name="rules[3][type]" class="switch-input"> 排除指定店铺
        </label>

        <fieldset class="sw-value" style="display: none;">
            <div class="form-group">
                <label class="col-sm-2 control-label">店铺：</label>
                <div class="col-sm-10">
                    <a class="btn btn-success" id="chapter-create-btn" data-toggle="modal" data-target="#spu_modal" data-backdrop="static" data-keyboard="false" data-url="{{route('admin.shitang.getShop',['action' => 'exclude'])}}">
                        点击添加店铺
                    </a>
                    (已添加
                    <i class="countExcludeSpu">0</i>
                    个店铺，<a data-toggle="modal" data-target="#spu_modal" data-backdrop="static" data-keyboard="false" data-url="{{route('admin.shitang.getShop', ['action' => 'view_exclude'])}}">点击查看</a>)
                    <input type="hidden" id="exclude_spu" name="rules[3][value][shop_ids]" value="">
                </div>
            </div>
        </fieldset>
    </div>
</div>
<!--排除指定店铺 end-->
<div class="hr-line-dashed"></div>

<!--指定集市-->
<div class="form-group">
    <div class="col-sm-10 col-sm-offset-2">
        <label>
            <input type="checkbox" value="contains_market" name="rules[4][type]" class="switch-input"> 指定集市
        </label>

        <fieldset class="sw-value" style="display: none;">
            <div class="form-group">
                <label class="col-sm-2 control-label">集市：</label>
                <div class="col-sm-10">
                    <a class="btn btn-success" id="chapter-create-btn" data-toggle="modal" data-target="#market_modal" data-backdrop="static" data-keyboard="false" data-url="{{route('admin.shitang.getMarket',['action' => 'add'])}}">
                        点击添加集市
                    </a>
                    (已添加
                    <i class="countMarket">0</i>
                    个集市，<a data-toggle="modal" data-target="#market_modal" data-backdrop="static" data-keyboard="false" data-url="{{route('admin.shitang.getMarket', ['action' => 'view'])}}">点击查看</a>)
                    <input type="hidden" id="selected_market" name="rules[4][value][market_ids]" value="">
                </div>
            </div>
        </fieldset>
    </div>
</div>
<!--指定集市 end-->
<div class="hr-line-dashed"></div>--}}
