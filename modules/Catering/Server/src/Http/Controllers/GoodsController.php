<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Category\Models\Category;
use GuoJiangClub\Catering\Component\Discount\Services\DiscountService;
use GuoJiangClub\Catering\Component\Order\Models\Comment;
use GuoJiangClub\Catering\Component\Product\Models\Attribute;
use GuoJiangClub\Catering\Component\Product\Models\Specification;
use GuoJiangClub\Catering\Component\Product\Repositories\GoodsRepository;
use GuoJiangClub\Catering\Component\Product\Repositories\ProductRepository;
use GuoJiangClub\Catering\Server\Transformers\CommentTransformer;
use GuoJiangClub\Catering\Server\Transformers\GoodsTransformer;
use GuoJiangClub\Catering\Component\Discount\Repositories\CouponRepository;
use DB;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class GoodsController extends Controller
{
    protected $goodsRepository;
    protected $couponRepository;
    protected $productRepository;
    protected $discountService;
    protected $Specs;

    public function __construct(GoodsRepository $goodsRepository,
                                CouponRepository $couponRepository,
                                ProductRepository $productRepository,
                                DiscountService $discountService,
                                Specification $specification)

    {
        $this->goodsRepository   = $goodsRepository;
        $this->couponRepository  = $couponRepository;
        $this->productRepository = $productRepository;
        $this->discountService   = $discountService;
        $this->Specs             = $specification;
    }

    public function index()
    {
        //1. get sort parameter
        $orderBy          = request('orderBy') ? request('orderBy') : 'sort';
        $sort             = request('sort') ? request('sort') : 'desc';
        $hasFlag          = false;
        $categoryGoodsIds = [];
        //2. get category parameter and get all sub categories
        if ($categoryId = request('c_id')) {

            $categoryIds = [];
            if (str_contains($categoryId, ',')) {
                $paramCategories = explode(',', $categoryId);
                foreach ($paramCategories as $paramCategory) {
                    $categoryIds = array_merge($categoryIds, Category::getIdsByParentID($paramCategory));
                }
            } else {
                $categoryIds = Category::getIdsByParentID($categoryId);
            }
            $categoryGoodsIds = DB::table('el_goods_category')->whereIn('category_id', $categoryIds)->select('goods_id')->distinct()->get()
                ->pluck('goods_id')->toArray();

            $hasFlag = true;
        }

        //3. get specification parameters
        $specGoodIds = $categoryGoodsIds;
        if ($specArray = request('specs')) {
            $k       = 0;
            $tempIds = [];
            foreach ($specArray as $key => $item) {
                if ($key == 'size') {
                    $tempIds[$k] = DB::table('el_goods_spec_relation')->where('spec_value_id', $item)->select('goods_id')->distinct()->get()->pluck('goods_id')->toArray();
                } else {
                    $specValueIds = DB::table('el_goods_specs_value')->where('color', $item)->select('id')->get()->pluck('id')->toArray();
                    $tempIds[$k]  = DB::table('el_goods_spec_relation')->whereIn('spec_value_id', $specValueIds)->select('goods_id')->distinct()->get()->pluck('goods_id')->toArray();
                }
                $k++;
            }

            $tmp_arr = [];
            if (count($tempIds) > 0) {
                foreach ($tempIds as $key => $val) {
                    if ($key == 0) {
                        $tmp_arr = $val;
                    } else {
                        $tmp_arr = array_intersect($tmp_arr, $val);
                    }
                }
            }

            $hasFlag = true;
        }

        if (!empty($tempIds)) {
            $specGoodIds = array_intersect($specGoodIds, $tmp_arr);
        }

        //4. get goods by attribute
        $attrGoodsIds = $specGoodIds;

        if ($attrArray = request('attr')) {
            if (!is_array($attrArray)) {
                $attrArray = explode(',', $attrArray);
            }

            foreach ($attrArray as $key => $item) {
                $attrarr[] = $item;
            }

            foreach ($attrarr as $k => $item) {
                $tempIds[$k] = DB::table('el_goods_attribute_relation')->where('attribute_value_id', $item)->select('goods_id')->distinct()->get()->pluck('goods_id')->toArray();
            }

            $tmp_arr = [];
            if (count($tempIds) > 0) {
                foreach ($tempIds as $key => $val) {
                    if ($key == 0) {
                        $tmp_arr = $val;
                    } else {
                        $tmp_arr = array_intersect($tmp_arr, $val);
                    }
                }
            } else {
                $tempIds = [];
            }
            $hasFlag = true;
        }

        if (!empty($tempIds)) {
            $attrGoodsIds = array_intersect($attrGoodsIds, $tmp_arr);
        }

        $goodIds = $this->getAttributeValueGoodsIds($attrGoodsIds, $hasFlag);

        //5. get goods list
        $goodsList = $this->goodsRepository->scopeQuery(function ($query) use ($goodIds, $hasFlag) {
            if (!empty($goodIds) OR $hasFlag) {
                $query = $query->whereIn('id', $goodIds);
            }

            if (!empty(request('brand_id'))) {
                $query->where('brand_id', request('brand_id'));
            }

            if (!empty(request('price'))) {
                list($min, $max) = explode('-', request('price'));
                $query = $query->where('sell_price', '>=', $min);
                $query = $query->where('sell_price', '<=', $max);
            }

            if (!empty($keyword = request('keyword'))) {
                $query = $query->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', '%' . $keyword . '%')->orWhere('tags', 'like', '%' . $keyword . '%');
                });
            }

            if ($shop_id = request('shop_id')) {
                $query = $query->whereHas('o2oGoods', function ($query) use ($shop_id) {
                    $query->where('shop_id', $shop_id)->where('status', 1);
                });
            }

            return $query->where(['is_del' => 0, 'is_largess' => request('is_largess') ? request('is_largess') : 0]);
        })->orderBy($orderBy, $sort)->paginate(16);

        $ruleGoods        = [];
        $ruleGoods['spu'] = [];
        $percentageGroup  = [];
        if ($user = request()->user('api') AND
            $userRole = $user->roles->first() AND
            $userRole->name == 'employee'
        ) {
            $ruleGoods       = $this->discountService->getGoodsByRole('employee');
            $percentageGroup = $ruleGoods['percentageGroup'];
        }

        foreach ($goodsList as $item) {
            $item->insourced_price = -1;
            $discounts             = $this->discountService->getDiscountsByGoods($item);
            $item->discount_tags   = $this->getDiscountTags($discounts);
        }

        $filters = $this->generateFilterConditions();

        return $this->response()->paginator($goodsList, new GoodsTransformer('list'))->setMeta(['filter' => $filters]);
    }

    private function generateFilterConditions()
    {
        //如果是分类页面进入，则需要获取分类下所有商品的模型ID
        if ($categoryId = request('c_id')) {
            $categoryIds      = Category::getIdsByParentID($categoryId);
            $categoryGoodsIds = DB::table('el_goods_category')->whereIn('category_id', $categoryIds)->select('goods_id')->distinct()->get()
                ->pluck('goods_id')->toArray();

            $modelIds = $this->goodsRepository->findWhereIn('id', $categoryGoodsIds, ['model_id'])->pluck('model_id')->unique()->toArray();

            $getAttrList = Attribute::ofModelIds($modelIds)->get();

            $attrFilterID = DB::table('el_goods_attribute_relation')->whereIn('goods_id', $categoryGoodsIds)->select('attribute_value_id')->distinct()->get()->pluck('attribute_value_id')->toArray();

            foreach ($getAttrList as $item) {
                $AttributeValue = $item->values->whereIn('id', $attrFilterID);
                foreach ($AttributeValue as $kitem) {
                    $attrArray[$item->name][$kitem->id] = $kitem->name;
                }
            }

            $attrArray = !isset($attrArray) ? [] : $attrArray;

            $attrFilters = ['attr' => ['keys' => array_keys($attrArray), 'values' => $attrArray]];

            $specArray   = [];
            $getSpecList = $this->Specs->with('values')->get();

            $SizeFilterID = DB::table('el_goods_spec_relation')->whereIn('el_goods_spec_relation.goods_id', $categoryGoodsIds)->select('spec_value_id')->distinct()->get()->pluck('spec_value_id')->toArray();
            foreach ($getSpecList as $item) {
                $alias     = $item->type == 2 ? 'color' : 'size';
                $specValue = $item->values->whereIn('id', $SizeFilterID);
                foreach ($specValue as $kitem) {
                    /*$specArray[$item->name][$kitem->id] = $kitem->name;*/
                    $itemName = $item->name . ':' . $alias;

                    if ($kitem->color) {
                        if (!isset($specArray[$itemName]) OR
                            (isset($specArray[$itemName]) AND !in_array($kitem->color, $specArray[$itemName]))
                        ) {
                            $specArray[$itemName][$kitem->color] = $kitem->color;
                        }
                    } else {
                        $specArray[$itemName][$kitem->id] = $kitem->name;
                    }
                }
            }

            $specArray   = !isset($specArray) ? [] : $specArray;
            $specFilters = ['specs' => ['keys' => array_keys($specArray), 'values' => $specArray]];

            return array_merge($attrFilters, $specFilters);
        }

        return [];
    }

    private function getAttributeValueGoodsIds($goodIds, &$hasFlag)
    {
        $attrGoodsIds = $goodIds;
        if (request('attrValue') AND $attrArray = array_unique(request('attrValue'))) {
            foreach ($attrArray as $key => $value) {
                /*if (empty($key)) {*/
                /*$attributesIds = DB::table('el_goods_attribute_value')->where('name',$value)->get()->pluck('id')->toArray();
                $tempAttrIds[$value] = DB::table('el_goods_attribute_relation')->whereIn('attribute_value_id',$attributesIds)->select('goods_id')->distinct()->get()->pluck('goods_id')->toArray();*/
                $tempAttrIds[$value] = DB::table('el_goods_attribute_relation')
                    ->where('attribute_value', 'like', '%' . $value . '%')
                    ->select('goods_id')
                    ->distinct()->get()->pluck('goods_id')->toArray();
                /*} else {
                    $tempAttrIds[$key] = DB::table('el_goods_attribute_relation')->where('attribute_id', $key)->where('attribute_value', $value)->select('goods_id')->distinct()->get()->pluck('goods_id')->toArray();
                }*/
            }

            if (!empty($tempAttrIds)) {
                $attrGoodsIds = empty($attrGoodsIds) ? array_first($tempAttrIds) : $attrGoodsIds;
                foreach ($tempAttrIds as $key => $value) {
                    $attrGoodsIds = array_intersect($attrGoodsIds, $value);
                }
            }

            $hasFlag = true;
        }

        return $attrGoodsIds;
    }

    public function show($id)
    {
        $goods                  = $this->goodsRepository->find($id);
        $ruleGoods              = [];
        $ruleGoods['spu']       = [];
        $goods->insourced_price = -1;
        $goods->single_price    = $goods->min_price;
        //限购
        $goods->user_limit = 0;

        return $this->response()->item($goods, new GoodsTransformer())->setMeta([
            'attributes'     => $goods->attr,
            'singleDiscount' => null,
            'suit'           => null,
            'seckill'        => [],
            'groupon'        => [],
            'multiGroupon'   => [],
            'discounts'      => null,
        ]);
    }

    public function getStock($id)
    {
        $goods    = $this->goodsRepository->findOneById($id);
        $specs    = [];
        $stores   = [];
        $skuPhoto = collect();

        if ($goods AND count($products = $goods->products()->with('photo')->get())) {
            $grouped = $goods->specificationValue->groupBy('spec_id');

            //秒杀活动

            $seckill = $this->seckillItemRepository->getSeckillItemByGoodsID($id);

            $singleDiscount = $this->discountService->getSingleDiscountByGoods($goods);

            foreach ($products as $key => $val) {
                $specArray = $val->specID;
                asort($specArray);

                $spec_id                   = implode('-', $specArray);
                $stores[$spec_id]['id']    = $val->id;
                $stores[$spec_id]['store'] = $val->is_show == 1 ? $val->store_nums : 0;
                if ($seckill And $seckill->init_status == SeckillItem::ING) {
                    $stores[$spec_id]['price'] = $seckill->seckill_price;
                } else {
                    $stores[$spec_id]['price'] = $this->discountService->getProductPriceFromSingleDiscount($val, $singleDiscount);
                }

                $stores[$spec_id]['sku']          = $val->sku;
                $stores[$spec_id]['ids']          = $val->specID;
                $stores[$spec_id]['redeem_point'] = $goods->redeem_point;

                //产品图片
                if ($photo = $val->photo) {
                    $skuPhotoData['spec_value_id'] = $spec_id;
                    $skuPhotoData['photo']         = $photo->url;
                    $skuPhoto->push($skuPhotoData);
                }
            }

            /*  $filtered = $grouped->filter(function ($value, $key) {
                  return $key < 2;
              })->all();

              if (count($filtered) > 0) {
                  $grouped = $grouped->sortByDesc(function ($item, $key) {
                      return $key;
                  });
              }*/

            $i = 1;
            foreach ($grouped as $key => $item) {

                $keys = $grouped->keys()->toArray();
                if (in_array(2, $keys)) {   //如果有颜色规格，因为颜色ID=2，为了保证 颜色排前面，需要这样处理sort
                    $sort = $key == 1 ? $key + 2 : $key;
                } else {
                    $sort = $key;
                }

                $specs[$sort]['id'] = $key;

                $spec = Specification::find($key);

                if (count($grouped) == 1) {  //如果是单规格
                    if ($key == 2) {  //如果是颜色
                        $specs[$sort]['label_key'] = 'color';
                    } else {  //否则是其他规格
                        $specs[$sort]['label_key'] = 'size';
                    }
                } else {  //如果是双规格
                    if (in_array(2, $keys)) { //如果有颜色规格
                        if ($key == 2) {  //如果是颜色
                            $specs[$sort]['label_key'] = 'color';
                        } else {  //否则是其他规格
                            $specs[$sort]['label_key'] = 'size';
                        }
                    } else {  //没有颜色规格
                        if ($i == 1) {
                            $specs[$sort]['label_key'] = 'color';
                        } else {
                            $specs[$sort]['label_key'] = 'size';
                        }
                    }
                }
                $i++;

                $specs[$sort]['label'] = $spec->name;
                $specs[$sort]['list']  = [];
                $item                  = $item->sortBy('pivot.sort')->values();
                foreach ($item as $k => $value) {
                    $list          = [];
                    $list['id']    = $value->id;
                    $list['value'] = $value->name;

                    if ($value->spec_id == 2)    //颜色
                    {
                        $list['color'] = '#' . $value->rgb;

                        //图片数据,兼容Osprey 从sku获取图片
                        $filter = $skuPhoto->filter(function ($item) use ($value) {
                            $specIdArr = explode('-', $item['spec_value_id']);

                            return in_array($value->id, $specIdArr);
                        })->first();

                        if ($filter) {
                            $list['img'] = $filter['photo'];
                        } else {
                            $list['img'] = $this->getImageCdnUrl($value->pivot->img);
                        }

                        $list['spec_img'] = $this->getImageCdnUrl($value->pivot->img);
                    }
                    $list['alias'] = $value->pivot->alias;
                    array_push($specs[$sort]['list'], $list);
                }
            }
        }

        return $this->api([
            'specs'  => $specs,
            'stores' => $stores,
        ]);
    }

//    public function getStock($id)
//    {
//        $spec_list = array();
//        $default_list = array();
//        /*$id=request('goods_id');*/
//
//        /*$product=$this->productRepository->findWhere(['goods_id'=>$id]);*/
//        $product = Product::where('goods_id', $id)->get();
//        //return $product;
//        /*$tsts=[];
//        $default_product_all = $product->filter(function($value, $key){
//            \Log::info('循环进来了吗');
//            $tsts[]=$key;
//            return $key->store_nums > 0;
//        });*/
//        $default_product_all = collect();
//        foreach ($product as $item) {
//            if ($item->store_nums > 0) {
//                $default_product_all->push($item);
//            }
//        }
//        $default_product_all = $default_product_all->first();
//        if (count($default_product_all)) {
//            //获取默认选中规格
//            $pect_def = json_decode($default_product_all['spec_array']);
//            if (!empty($default_product_all->sku)) {
//                /* $photo_url = $default_product_all->photo->url;*/
//                $photo_url = '';
//            } else {
//                $photo_url = '';
//            }
//            if ($default_product_all->is_show == 1) {
//                $default_list[0][$pect_def[0]->value] = [
//                    'store_nums' => $default_product_all->store_nums,
//                    'value' => $pect_def[1]->value,
//                    'id' => $default_product_all->id,
//                    'sku' => $default_product_all->sku,
//                    'is_show' => $default_product_all->is_show,
//                    'sell_price' => $default_product_all->sell_price,
//                    'photo_url' => $photo_url
//                ];
//            }
//
//
//            foreach ($product as $item) {
//                /*if ($item->store_nums > 0) {*/
//                $spec = json_decode($item->spec_array);
//                if (!empty($item->sku)) {
//                    $photo_url = '';
//                } else {
//                    $photo_url = '';
//                }
//
//                $spec_list[$spec[0]->value][] =
//                    [
//                        'store_nums' => $item->store_nums,
//                        'value' => $spec[1]->value,
//                        'id' => $item->id,
//                        'sku' => $item->sku,
//                        'sell_price' => $item->sell_price,
//                        'is_show' => $item->is_show,
//                        'photo_url' => $photo_url
//
//                    ];
//
//
//                if ($item->photo) {
//                    $photo_url = $item->photo->url;
//                } else {
//                    $photo_url = '';
//                }
//
//                $spec_list[$spec[1]->value][] =
//                    [
//                        'store_nums' => $item->store_nums,
//                        'value' => $spec[0]->value,
//                        'id' => $item->id,
//                        'is_show' => $item->is_show,
//                        'sku' => $item->sku,
//                        'sell_price' => $item->sell_price,
//                        'photo_url' => $photo_url
//
//                    ];
//                /*}*/
//            }
//
//
//        }
//        /*return response()->json([
//            'error' => '',
//            'status' => true,
//            'data' => [
//                'spec_list' => $spec_list,
//                'default_list'=>$default_list
//            ],
//            'error_code' => 0
//        ]);*/
//        return $this->api([
//            'spec_list' => $spec_list,
//            'default_list' => $default_list
//        ]);
//    }

    public function getComments($id)
    {
        $goods    = $this->goodsRepository->find($id);
        $limit    = request('limit') ? request('limit') : 15;
        $comments = $goods->comments()->where('status', Comment::STATUS_SHOW)->paginate($limit);

        return $this->response()->paginator($comments, new CommentTransformer());
    }

    public function getGoodsByCoupon($coupon_id)
    {
        $goods_list  = [];
        $coupon_list = $this->couponRepository->with('discount.rules', 'discount.actions')->findWhere(['id' => $coupon_id, 'user_id' => request()->user()->id])->first();
        if (!$coupon_list || count($coupon_list) == 0) {
            return $this->api('', false, 400, '未获取到优惠券信息');
        }
        foreach ($coupon_list->discount->rules as $key => $val) {
            if ($val->type == 'contains_product' && isset($val->configuration)) {
                $configuration = json_decode($coupon_list->discount->rules[$key]->configuration, true);
                if (!empty($configuration['spu'])) {
                    $spu = explode(',', $configuration['spu']);
                    if (count($spu)) {
                        $goods = $this->goodsRepository->findWhereIn('id', $spu);
                        foreach ($goods as $value) {
                            $goods_list[] = [
                                'goods_id'   => $value->id,
                                'name'       => $value->name,
                                'tags'       => $value->tags,
                                'img'        => $value->img,
                                'sell_price' => $value->sell_price,

                            ];
                        }
                    }
                }

                if (!empty($configuration['sku'])) {
                    $sku = explode(',', $configuration['sku']);
                    if (count($sku) && is_array($sku)) {
                        $product = $this->productRepository->with(['photo', 'goods'])->findWhereIn('sku', $sku);
                        foreach ($product as $item) {
                            $goods_list[] = [
                                'goods_id'   => $item->goods_id,
                                'name'       => isset($item->goods->name) ? $item->goods->name : '',
                                'tags'       => isset($item->goods->tags) ? $item->goods->tags : '',
                                'img'        => isset($item->photo->url) ? $item->photo->url : '',
                                'sell_price' => isset($item->goods->sell_price) ? $item->goods->sell_price : '',

                            ];
                        }
                    }
                }
            }
        }

        return $this->api($goods_list);
    }

    public function getDiscountTags($discounts)
    {
        $tagCollection = new Collection();

        if ($discounts instanceof Collection) {
            $discounts->each(function ($item, $key) use ($tagCollection) {
                /*if (!empty($item->tags)) {
                    $tags = explode(",", $item->tags);
                    foreach ($tags as $tag) {
                        $tagCollection->push($tag);
                    }
                }*/
                $tagCollection->push($item->coupon_based == 1 ? '券' : '促');
            });
        }

        return $tagCollection->unique();
    }

    private function getImageCdnUrl($value)
    {
        $replace_url = settings('store_img_replace_url') ? settings('store_img_replace_url') : url('/');
        if (settings('store_img_cdn_status') AND $url = settings('store_img_cdn_url')) {
            $value = str_replace('http://' . $replace_url, $url, $value);
        }

        return $value;
    }

    public function goodsPurchase($goods_id)
    {
        if (!isset(request()->user()->id)) {
            return $this->failed('您还没有登录');
        }

        return $this->success([]);
    }

    /**
     * 到货提醒
     */
    public function goodsRemind(Request $request)
    {
        $input = $request->except('_token', 'file');
        if (!$input['goods_id']) {

            return $this->api([], false, 500, '参数缺失');
        }

        if (!$input['goods_sku']) {

            return $this->api([], false, 500, '参数缺失');
        }

        $is_remind = $this->goodsRemind->findWhere(['user_id' => request()->user()->id, 'goods_id' => $input['goods_id'], 'goods_sku' => $input['goods_sku']])->first();
        if ($is_remind) {

            return $this->api([], false, 500, '请勿重复添加');
        }

        $this->goodsRemind->create([
            'user_id'   => request()->user()->id,
            'goods_id'  => $input['goods_id'],
            'goods_sku' => $input['goods_sku'],
            'is_remind' => 0,
        ]);

        return $this->api([], false, 200, '添加成功');
    }

}
