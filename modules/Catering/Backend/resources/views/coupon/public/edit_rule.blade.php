<h4>基本规则</h4>
<hr class="hr-line-solid">
<!--订单总金额-->
@if($item_total=$discount->discount_item_total)
    <div class="form-group">
    <div class="col-sm-10 col-sm-offset-2">
        <label>
            <input checked type="checkbox" name="rules[1][type]"
                   value="item_total" class="switch-input"> 订单总金额满
        </label>
        <span class="sw-hold" style="display: none">XX</span>
        <input type="text" name="rules[1][value]" class="sw-value" value="{{$item_total->RulesValue / 100}}">元
    </div>
</div>
@else
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
@endif
<!--订单总金额 end-->
<div class="hr-line-dashed"></div>

{{--<!--指定店铺-->
@if($contains_market_shop=$discount->contains_market_shop)
    <div class="form-group">
    <div class="col-sm-10 col-sm-offset-2">
        <label>
            <input type="checkbox" value="contains_market_shop" name="rules[2][type]" class="switch-input" checked> 指定店铺
        </label>

        <fieldset class="sw-value">
            <div class="form-group">
                <label class="col-sm-2 control-label">店铺：</label>
                <div class="col-sm-10">
                    <a class="btn btn-success" id="chapter-create-btn" data-toggle="modal" data-target="#spu_modal" data-backdrop="static" data-keyboard="false" data-url="{{route('admin.shitang.getShop',['action' => 'add'])}}">
                        点击添加店铺
                    </a>
                    (已添加
                    <i class="countSpu">{{$contains_market_shop->RulesValue['shop_ids'] ? count(explode(',',$contains_market_shop->RulesValue['shop_ids'])) : 0 }}</i>
                    个店铺，<a data-toggle="modal" data-target="#spu_modal" data-backdrop="static" data-keyboard="false" data-url="{{route('admin.shitang.getShop', ['action' => 'view'])}}">点击查看</a>)
                    <input type="hidden" id="selected_spu" name="rules[2][value][shop_ids]" value="{{$contains_market_shop->RulesValue['shop_ids']}}">
                </div>
            </div>
        </fieldset>
    </div>
</div>
@else
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
@endif
<!--指定店铺 end-->
<div class="hr-line-dashed"></div>

<!--排除指定店铺 start-->
@if($exclude_market_shop=$discount->exclude_market_shop)
    <div class="form-group">
    <div class="col-sm-10 col-sm-offset-2">
        <label>
            <input type="checkbox" value="exclude_market_shop" name="rules[3][type]" class="switch-input" checked> 排除指定店铺
        </label>

        <fieldset class="sw-value">
            <div class="form-group">
                <label class="col-sm-2 control-label">店铺：</label>
                <div class="col-sm-10">
                    <a class="btn btn-success" id="chapter-create-btn" data-toggle="modal" data-target="#spu_modal" data-backdrop="static" data-keyboard="false" data-url="{{route('admin.shitang.getShop',['action' => 'exclude'])}}">
                        点击添加店铺
                    </a>
                    (已添加
                    <i class="countExcludeSpu">{{$exclude_market_shop->RulesValue['shop_ids'] ? count(explode(',',$exclude_market_shop->RulesValue['shop_ids'])) : 0 }}</i>
                    个店铺，<a data-toggle="modal" data-target="#spu_modal" data-backdrop="static" data-keyboard="false" data-url="{{route('admin.shitang.getShop', ['action' => 'view_exclude'])}}">点击查看</a>)
                    <input type="hidden" id="exclude_spu" name="rules[3][value][shop_ids]" value="{{$exclude_market_shop->RulesValue['shop_ids']}}">
                </div>
            </div>
        </fieldset>
    </div>
</div>
@else
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
@endif
<!--排除指定店铺 end-->
<div class="hr-line-dashed"></div>

<!--指定集市 start-->
@if($contains_market = $discount->contains_market)
<div class="form-group">
    <div class="col-sm-10 col-sm-offset-2">
        <label>
            <input type="checkbox" checked value="contains_market" name="rules[4][type]" class="switch-input"> 指定集市
        </label>

        <fieldset class="sw-value">
            <div class="form-group">
                <label class="col-sm-2 control-label">集市：</label>
                <div class="col-sm-10">
                    <a class="btn btn-success" id="chapter-create-btn" data-toggle="modal" data-target="#market_modal" data-backdrop="static" data-keyboard="false" data-url="{{route('admin.shitang.getMarket',['action' => 'add'])}}">
                        点击添加集市
                    </a>
                    (已添加
                    <i class="countMarket">{{$contains_market->RulesValue['market_ids'] ? count(explode(',',$contains_market->RulesValue['market_ids'])) : 0 }}</i>
                    个集市，<a data-toggle="modal" data-target="#market_modal" data-backdrop="static" data-keyboard="false" data-url="{{route('admin.shitang.getMarket', ['action' => 'view'])}}">点击查看</a>)
                    <input type="hidden" id="selected_market" name="rules[4][value][market_ids]" value="{{$contains_market->RulesValue['market_ids']}}">
                </div>
            </div>
        </fieldset>
    </div>
</div>
@else
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
@endif
<!--指定集市 end-->
<div class="hr-line-dashed"></div>--}}
