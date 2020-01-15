<?php

namespace GuoJiangClub\Catering\Component\Product\Models;

use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountItemContract;
use GuoJiangClub\Catering\Component\Category\Models\Category;
use GuoJiangClub\Catering\Component\Order\Models\Comment;
use Illuminate\Database\Eloquent\Model as LaravelModel;
use Illuminate\Support\Collection;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use DB;

class Goods extends LaravelModel implements Transformable, DiscountItemContract
{
	use TransformableTrait;

	protected $guarded = ['id'];

	const GOODS_ATTRIBUTE_CACHE = 'goods_attribute_cache';

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'goods');
	}

	public function hasOnePoint()
	{
		return $this->hasOne('GuoJiangClub\Catering\Component\Point\Model\PointGoods', 'item_id')->withDefault();
	}

	public function products()
	{
		return $this->hasMany('GuoJiangClub\Catering\Component\Product\Models\Product');
	}

	public function categories()
	{
		return $this->belongsToMany(Category::class, 'el_goods_category', 'goods_id', 'category_id');
	}

	public function getCategories()
	{
		return $this->categories;
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

	public function getKeyCode($type = null)
	{
		return $this->id;
	}

	public function getStockQtyAttribute()
	{
		return $this->store_nums;
	}

	public function getPhotoUrlAttribute()
	{
		return $this->img;
	}

	public function getSpecsTextAttribute()
	{
		return '';
	}

	public function getDetailIdAttribute()
	{
		return $this->id;
	}

	public function getIsInSale($quantity)
	{
		return $this->is_del == 0 AND $this->stock_qty >= $quantity;
	}

	public function reduceStock($quantity)
	{
		$this->store_nums = $this->store_nums - $quantity;
		$this->calculateStock();
	}

	public function reduceO2oStock($quantity, $shop_id)
	{
		$o2oGoods = $this->o2oGoods()->where('shop_id', $shop_id)->first();

		$o2oGoods->store_nums = $o2oGoods->store_nums - $quantity;
		$o2oGoods->save();

		$o2oProducts = $o2oGoods->o2oProducts->filter(function ($value) use ($shop_id) {
			return $value->shop_id == $shop_id;
		})->first();

		$o2oProducts->store_nums = $o2oProducts->store_nums - $quantity;
		$o2oProducts->save();
	}

	public function restoreStock($quantity)
	{
		$this->store_nums = $this->store_nums + $quantity;
		$this->calculateStock();
	}

	public function restoreO2oStock($quantity, $shop_id)
	{
		$o2oGoods = $this->o2oGoods()->where('shop_id', $shop_id)->first();

		$o2oGoods->store_nums = $o2oGoods->store_nums + $quantity;
		$o2oGoods->save();

		$o2oProducts = $o2oGoods->o2oProducts->filter(function ($value) use ($shop_id) {
			return $value->shop_id == $shop_id;
		})->first();

		$o2oProducts->store_nums = $o2oProducts->store_nums + $quantity;
		$o2oProducts->save();
	}

	public function increaseSales($quantity)
	{
		$this->sale = $this->sale + $quantity;
	}

	public function restoreSales($quantity)
	{
		$this->sale = $this->sale - $quantity;
	}

	public function getAttrAttribute()
	{
		if ($attr = $this->getAttrFromCache($this->id)) {
			if ($attr === 'empty') {
				return [];
			}

			return $attr;
		}

		$prefix                 = config('ibrand.app.database.prefix', 'ibrand_');
		$attributeTable         = $prefix . 'goods_attribute';
		$attributeValueTable    = $prefix . 'goods_attribute_value';
		$attributeRelationTable = $prefix . 'goods_attribute_relation';
		$res1                   = $this->join($attributeRelationTable, $this->getQualifiedKeyName(), '=', $attributeRelationTable . '.goods_id')
			->join($attributeTable, $attributeRelationTable . '.attribute_id', '=', $attributeTable . '.id')
			->where($attributeTable . '.type', 2)
			->where($attributeRelationTable . '.goods_id', $this->id)
			->select($attributeTable . '.id', $attributeTable . '.name', $attributeRelationTable . '.attribute_value')
			->get();
		$res2                   = $this->join($attributeRelationTable, $this->getQualifiedKeyName(), '=', $attributeRelationTable . '.goods_id')
			->join($attributeTable, $attributeRelationTable . '.attribute_id', '=', $attributeTable . '.id')
			->join($attributeValueTable, $attributeRelationTable . '.attribute_value_id', '=', $attributeValueTable . '.id')
			->where($attributeRelationTable . '.goods_id', $this->id)
			->select($attributeTable . '.id', $attributeTable . '.is_chart', $attributeTable . '.name', $attributeRelationTable . '.attribute_value_id', $attributeValueTable . '.name as attribute_value')
			->get();
		foreach ($res2 as $item) {
			if ($item['is_chart'] == 1) {
				$value          = DB::table('el_goods_attribute_value')->select('id', 'name')->where('attribute_id', $item['id'])->get();
				$item['values'] = $value;
			}
		}
		$res = $res1->merge($res2);

		if (count($res) > 0) {
			$this->putAttrToCache($this->id, $res);
		} else { //说明该商品目前没有单品折扣
			$this->putAttrToCache($this->id, 'empty');
		}

		return $res;
	}

	private function getAttrFromCache($goodsId)
	{
		if ($attrs = \Cache::get(self::GOODS_ATTRIBUTE_CACHE) AND $attrs instanceof Collection) {
			return $attrs->get($goodsId);
		}

		return false;
	}

	private function putAttrToCache($goodsId, $attr)
	{
		$attrs = \Cache::get(self::GOODS_ATTRIBUTE_CACHE, collect());

		$attrs->put($goodsId, $attr);

		\Cache::put(self::GOODS_ATTRIBUTE_CACHE, $attrs, 30);
	}

	public function hasManyAttribute()
	{
		return $this->belongsToMany('App\Entities\Attribute', 'goods_attributes', 'goods_id', 'attribute_id')
			->withPivot('attribute_value', 'model_id');
	}

	public function model()
	{
		return $this->belongsTo('App\Entities\Models', 'model_id', 'id');
	}

	public function hasManySpec()
	{
		return $this->belongsToMany('App\Entities\Spec', 'goods_specs', 'goods_id', 'spec_id');
	}

	public function specs()
	{
		return $this->belongsToMany('App\Entities\Spec', 'goods_specs', 'goods_id', 'spec_id')
			->withPivot('spec_value', 'category_id')->withTimestamps();
	}

	public function GoodsComments()
	{
		return $this->hasMany('App\Entities\Comments', 'goods_id');
	}

	public function photos()
	{
		return $this->hasMany(GoodsPhoto::class, 'goods_id');
	}

	public function SearchSpec()
	{
		return $this->hasMany('App\Entities\SearchSpec', 'goods_id');
	}

	public function getArrayTagsAttribute()
	{
		return explode(',', $this->attributes['tags']);
	}

	public function setImglistAttribute($value)
	{
		$this->attributes['imglist'] = serialize($value);
	}

	public function getImglistAttribute($value)
	{
		$data = unserialize($value);

		return $data ? $data : [];
	}

	public function setSpecArrayAttribute($value)
	{
		if (count($value)) {
			$goods_spec_array = [];
			foreach ($value as $key => $val) {
				foreach ($val as $v) {
					$tempSpec = json_decode($v, true);
					if (!isset($goods_spec_array[$tempSpec['id']])) {
						$goods_spec_array[$tempSpec['id']] = ['id' => $tempSpec['id'], 'name' => $tempSpec['name'], 'type' => $tempSpec['type'], 'value' => []];
					}
					$goods_spec_array[$tempSpec['id']]['value'][] = $tempSpec['value'];
				}
			}
			foreach ($goods_spec_array as $key => $val) {
				$val['value']                    = array_unique($val['value']);
				$goods_spec_array[$key]['value'] = join(',', $val['value']);
			}

			return $this->attributes['spec_array'] = json_encode($goods_spec_array, JSON_UNESCAPED_UNICODE);
		} else {
			return $this->attributes['spec_array'] = '';
		}
	}

	/**
	 * 详情页获取产品规格
	 *
	 * @return mixed
	 */
	public function getSpecValueAttribute()
	{
		return json_decode($this->attributes['spec_array'], true);
	}

	public function getCommentGradeAttribute()
	{
		return @(@($this->attributes['grade'] / $this->attributes['comments']) / 5) * 100;
	}

	public function specificationValue()
	{
		return $this->belongsToMany('GuoJiangClub\Catering\Component\Product\Models\SpecsValue', 'el_goods_spec_relation', 'goods_id', 'spec_value_id')
			->withPivot('spec_id', 'alias', 'img', 'sort')->withTimestamps();
	}

	public function specRelationImg()
	{
		return $this->hasMany(SpecRelation::class, 'goods_id');
	}

	public function getItemType()
	{
		return 'goods';
	}

	public function getChildKeyCodes()
	{
		$codes    = [];
		$products = $this->products;
		foreach ($products as $product) {
			$codes[] = $product->getKeyCode();
		}

		return $codes;
	}

	public function getExtraAttribute($value)
	{
		if ($value) {
			return json_decode($value);
		}

		return '';
	}

	public function getSellPriceAttribute($value)
	{
		if ($this->min_price AND $this->min_price > 0) {
			return $this->min_price;
		}

		if ($price = $this->products()->min('sell_price') AND $price > 0) {
			return $price;
		}

		return $value;
	}

	public function getSellPriceScopeAttribute($value)
	{
		if ($this->min_price AND $this->min_price > 0) {

			if ($this->max_price AND $this->max_price > 0 && $this->max_price != $this->min_price && $this->max_price > $this->min_price) {
				return $this->min_price . '-' . $this->max_price;
			}

			return $this->min_price;
		}

		if ($price = $this->products()->min('sell_price') AND $price > 0) {

			if ($max_price = $this->products()->max('sell_price') AND $max_price > 0 && $max_price != $price) {

				return $price . '-' . $max_price;
			}

			return $price;
		}

		return $value;
	}

	// 替换图片CDN
	public function replaceImgCDN($value)
	{
		$parse      = parse_url($value);
		$parse_path = isset($parse['path']) ? $parse['path'] : '';
		$parse_host = isset($parse['host']) ? $parse['host'] : '';
		$app_parse  = parse_url(env('APP_URL'));
		if ($app_parse['host'] !== $parse_host) {
			return $value;
		}
		$cdn_status = settings('store_img_cdn_status') ? settings('store_img_cdn_status') : 0;
		if ($cdn_status && $value) {
			$cdn_url    = settings('store_img_cdn_url') ? settings('store_img_cdn_url') : '';
			$parse_path = isset($parse['path']) ? $parse['path'] : '';

			return $cdn_url . $parse_path;
		}

		return $value;
	}

	public function getImgAttribute($value)
	{
		$replace_url = settings('store_img_replace_url') ? settings('store_img_replace_url') : url('/');

		if (settings('store_img_cdn_status') AND $url = settings('store_img_cdn_url')) {
			$value = str_replace('http://' . $replace_url, $url, $value);
		}

		return $value;
	}

	public function getContentAttribute($value)
	{

		return $this->changeContentImgSrc($value);
	}

	private function changeContentImgSrc($value)
	{

		$replace_url = settings('store_img_replace_url') ? settings('store_img_replace_url') : url('/');

		$value = preg_replace_callback('/(<img).+(src=\"\/uploads\/ueditor\/php\/)(.+\.(jpg|gif|bmp|bnp|png)\"?).+>/i', function ($r) use ($replace_url) {
			return $r[1] . ' ' . str_replace('/uploads/ueditor/php/', $replace_url . '/uploads/ueditor/php/', $r[2]) . $r[3] . ' />';
		}, $value);

		if (settings('store_img_cdn_status') AND $url = settings('store_img_cdn_url')) {
			$value = preg_replace_callback('/<img(.*)src=\"([^\"]+)\"[^>]+>/isU', function ($r) use ($url, $replace_url) {

				$replace = str_replace('http://' . $replace_url, $url, $r[0]);

				return str_replace('https://' . $replace_url, $url, $replace);
			}, $value);

			return $value;
		}

		return $value;
	}

	public function getContentPCAttribute($value)
	{
		return $this->changeContentImgSrc($value);
	}

	public function getCollocationAttribute($value)
	{
		return $this->changeStringImgSrc($value);
	}

	public function changeStringImgSrc($string)
	{
		preg_match_all('|(.*)src="(.*)"(.*)|isU', $string, $main);
		$str = '';
		foreach ($main[1] as $key => $value) {
			$str .= $value;
			if (strpos($main[2][$key], ".jpg") or strpos($main[2][$key], ".gif") or strpos($main[2][$key], ".png")) {
				$main[2][$key] = $this->replaceImgCDN($main[2][$key]);
			}
			$str .= 'src="' . $main[2][$key] . '"';
			$str .= $main[3][$key];
		}

		return $str;
	}

	/**
	 * 根据商品所有的SKU来计算库存
	 */
	public function calculateStock()
	{

		if (count($this->products) > 0) {
			if (($allProductStock = $this->products()->sum('store_nums')) > 0) {
				$this->store_nums = $allProductStock;
				if (settings('store_auto_in_sale')) {
					$this->is_del = 0;
				}
			} else {
				$this->store_nums = 0;
				if (settings('store_auto_out_sale')) {
					$this->is_del = 2;
				}
			}
		} else {
			if ($this->store_nums == 0 AND settings('store_auto_out_sale')) {
				$this->is_del = 2;
			}
		}
	}
}
