<h4>优惠动作</h4>
<hr class="hr-line-solid">

<div class="form-group">
    <label class="col-sm-2 control-label">金额类型：</label>
    <div class="col-sm-10">
        <input type="hidden" value="{{$discount->discountAction->id or 0}}" name="action_id">
        <select class="form-control m-b action-select" name="action[type]" onchange="actionChange(this)">
            <?php $type = $discount->discountAction ? $discount->discountAction->type : null; ?>
            <option selected="selected" value="hot_order_fixed_discount"
                    {{$type == 'hot_order_fixed_discount'?'selected' : ''}}>订单减金额
            </option>
            {{--<option value="market_order_percentage_discount"
                    {{$type == 'market_order_percentage_discount'?'selected' : ''}}>订单打折
            </option>--}}
        </select>
    </div>
</div>

<div class="form-group">
    <div class="col-sm-10 col-sm-offset-2" id="promotion-action">

    </div>
</div>
@include('backend-shitang::coupon.public.template')