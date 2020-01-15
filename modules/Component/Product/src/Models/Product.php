<?php

namespace GuoJiangClub\Catering\Component\Product\Models;

use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountItemContract;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements DiscountItemContract
{
	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'goods_product');
	}

	const PRODUCT_SINGLE_DISCOUNT_PREFIX = 'product_single_discount_';

	public function getStockQtyAttribute()
	{
		return $this->store_nums;
	}

	public function photo()
	{
		return $this->hasOne('GuoJiangClub\Catering\Component\Product\Models\GoodsPhoto', 'sku', 'sku');
	}

	public function goods()
	{
		return $this->belongsTo(Goods::class);
	}

	public function getNameAttribute()
	{
		return $this->goods->name;
	}

	public function getIsLargessAttribute()
	{
		return $this->goods->is_largess;
	}

	public function getRedeemPointAttribute()
	{
		return $this->goods->redeem_point;
	}

	public function reduceStock($quantity)
	{
		$this->store_nums = $this->store_nums - $quantity;
		$this->goods->reduceStock($quantity);
	}

	public function restoreStock($quantity)
	{
		$this->store_nums = $this->store_nums + $quantity;
		$this->goods->restoreStock($quantity);
	}

	public function increaseSales($quantity)
	{
		$this->goods->sale = $this->goods->sale + $quantity;
	}

	public function restoreSales($quantity)
	{
		$this->goods->sale = $this->goods->sale - $quantity;
	}

	public function getIsInSale($quantity)
	{
		return $this->goods->is_del == 0 AND $this->stock_qty >= $quantity;
	}

	public function getPhotoUrlAttribute()
	{
		if ($specIds = $this->specID) {
			if (in_array(2, $specIds)) {
				foreach ($specIds as $value) {
					$specValue = SpecsValue::find($value);
					if ($specValue->spec_id != 2) {
						continue;
					}
					if ($specRelation = SpecRelation::where('goods_id', $this->goods_id)->where('spec_value_id', $value)->first()) {
						return $specRelation->img;
					}
				}
			} else {
				return $this->goods->img;
			}
		}

		return "";
	}

	public function getCategories()
	{
		return $this->goods->getCategories();
	}

	public function getMainCategory()
	{
		if (count($category = $this->getCategories()) > 0) {
			$sorted = $category->sortByDesc(function ($cate, $key) {
				return $cate->level;
			})->first();

			return $sorted->name;
		}

		return '';
	}

	public function getDetailIdAttribute()
	{
		return $this->goods_id;
	}

	public function getSpecsTextAttribute()
	{
		$specText = [];

		if ($specIds = $this->specID) {
			foreach ($specIds as $value) {
				$specValue = SpecsValue::find($value);
				if ($specRelation = SpecRelation::where('goods_id', $this->goods_id)->where('spec_value_id', $value)->first() AND $specRelation->alias) {
					$specText[$specValue->spec_id] = $specRelation->alias;
				} else {
					$specText[$specValue->spec_id] = $specValue->name;
				}
			}
			if (array_key_exists(1, $specText)) {
				krsort($specText);
			} else {
				ksort($specText);
			}
		}

		return implode(' ', array_values($specText));
	}

	public function getKeyCode($type = 'sku')
	{
		if ($type == 'spu') {
			return $this->goods_id;
		}

		return $this->sku;
	}

	public function getSpecIDAttribute($value)
	{
		return json_decode($value);
	}

	public function getItemType()
	{
		return 'product';
	}

	public function getChildKeyCodes()
	{
		return [];
	}

	public function getMarketPriceAttribute($value)
	{
		if (empty($value)) {
			return $this->goods->market_price;
		}

		return $value;
	}

	private function getSingleDiscountFromCache()
	{
		$key = self::PRODUCT_SINGLE_DISCOUNT_PREFIX . $this->id;

		if (!\Cache::has($key)) {
			return false;
		}

		return \Cache::get($key);
	}
}