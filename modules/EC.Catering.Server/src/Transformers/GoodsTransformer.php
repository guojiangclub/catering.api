<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-09-27
 * Time: 17:42
 */

namespace ElementVip\Server\Transformers;

use DB;
use ElementVip\Component\Advertisement\Models\Advertisement;
use ElementVip\Component\Product\Models\Goods;
use ElementVip\Component\Product\Models\Supplier;
use ElementVip\Component\User\Models\User;
use ElementVip\Component\Order\Models\Comment;
use ElementVip\Distribution\Core\Models\Agent;
use ElementVip\Distribution\Server\Services\AgentsService;
use League\Fractal\Resource\Item;

class GoodsTransformer extends BaseTransformer
{

    protected $type;

    public function __construct($type = 'detail')
    {
        $this->type = $type;
    }

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'products', 'photos', 'oneComment', 'guessYouLike', 'whoLike', 'point', 'user'
    ];


    public function transformData($model)
    {
        $tags = explode(',', $model->tags);
        $model->tags = $tags[0] == '' ? [] : $tags;
        $goods = $model->toArray();
        $goods['is_agent_goods'] = false;

        if ($this->type == 'detail') { //做的目的，是防止商品列表页去请求过多的数据
            $goods['shop_show_sell_nums'] = settings('shop_show_sell_nums') ? 1 : 0;
            $distribution_status = settings('distribution_status');
            $goods['can_share'] = false;
            $goods['agent_code'] = '';
            if ($distribution_status) {
                $search = DB::table('el_agent_goods')->where('goods_id', $goods['id'])->first();
                if ($search AND $search->activity == 1) {
                    $goods['is_agent_goods'] = true;

                    if ($user = auth('api')->user() AND
                        $agent = Agent::where(['status' => 1, 'user_id' => $user->id])->first()
                    ) { //判断详情页是否显示分销按钮
                        $goods['can_share'] = true;
                        $goods['agent_code'] = $agent->code;
                        $goods['commission'] = app(AgentsService::class)->getCommissionByGoodsID($model);

                        /*猫大不显示佣金*/
                        $goods['show_commission'] = true;
                        if (env('MAODA_COMMISSION')) {
                            $goods['show_commission'] = false;
                        }
                    }
                }

            }

            /*详情页统一banner*/
            $lists = Advertisement::where(['code' => 'H5DetailBanner', 'status' => 1])->with(['hasManyAdItem' => function ($query) {
                $query = $query->where('status', 1);
                $query->orderBy('sort', 'desc');
            }])->first();

            if ($lists AND count($lists->hasManyAdItem)) {
                foreach ($lists->hasManyAdItem as $item) {
                    $goods['content'] = '<img src="' . $item->image . '"><br>' . $goods['content'];
//                    $goods['contentpc'] = '<img src="' . $item->image . '" style="width:100%">' . $goods['contentpc'];
                }
            }

            $goods['supplier'] = Supplier::find($goods['supplier_id']);
        }

        if ($this->type == 'list') {
            $goods['content'] = '';
            $goods['contentpc'] = '';
            $goods['products'] = '';
        }

        if (isset($goods['seckill_price'])) $goods['sell_price'] = $goods['seckill_price'];
        $goods['shop_show_store'] = settings('shop_show_store') ? settings('shop_show_store') : 0;


        return $goods;
    }

    /**
     * Include Group
     *
     * @return
     */
    public function includePhotos($model)
    {
        $photos = $model->photos()->where('flag', 1)->orderBy('is_default', 'desc')->orderBy('sort', 'desc')->get();
        return $this->collection($photos, new GoodsPhotoTransformer(), '');
    }

    public function includeProducts($model)
    {
        $products = $model->products->filter(function ($item) {
            return $item->store_nums > 0;
        });
        return $this->collection($products, new ProductTransformer(), '');
    }

    public function includeOneComment($model)
    {
        $cacheKey = $model->id . 'oneComment';

        if (cache()->has($cacheKey)) {
            return $this->collection(cache($cacheKey), new CommentTransformer(), '');
        }

        $comments = $model->comments()->where('status', Comment::STATUS_SHOW)->orderBy('recommend', 'desc')->orderBy('created_at', 'desc')->take(1)->get();

        cache([$cacheKey => $comments], 30);

        return $this->collection($comments, new CommentTransformer(), '');
    }

    public function includeGuessYouLike($model)
    {

        $cacheKey = $model->id . 'guessYouLike';

        if (cache()->has($cacheKey)) {
            return $this->collection(cache($cacheKey), new GuessYouLikeTransformer(), '');
        }

        $cid = [];
        $categories = $model->categories;
        foreach ($categories as $category) {
            $cid[] = $category->id;
        }
        $categoryGoodsIds = DB::table('el_goods_category')->whereIn('category_id', $cid)->select('goods_id')->distinct()->get()
            ->pluck('goods_id')->toArray();
        $goods = Goods::select(['id', 'name', 'min_price', 'sell_price', 'img'])->whereIn('id', $categoryGoodsIds)->where('is_del', 0)->get();
        $goods = $goods->shuffle()->take(6);

        cache([$cacheKey => $goods], 30);

        //$goods = collect();

        return $this->collection($goods, new GuessYouLikeTransformer(), '');
    }

    public function includeWhoLike($model)
    {
        /*$cacheKey = $model->id . 'whoLike';

        if (cache()->has($cacheKey)) {
            return $this->collection(cache($cacheKey), new WhoLikeTransformer(), '');
        }

        $pid = [];
        $products = $model->products;
        foreach ($products as $product) {
            $pid[] = $product->id;
        }
        $orderIds = DB::table('el_order_item')->where('type', 'ElementVip\Component\Product\Models\Product')
            ->whereIn('item_id', $pid)->select('order_id')->distinct()->get();
        $orderIds = $orderIds->shuffle()->take(4)->pluck('order_id')->toArray();

        $orderUserIds = DB::table('el_order')->whereIn('id', $orderIds)->select('user_id')->distinct()->get()
            ->pluck('user_id')->toArray();

        $users = User::select(['id', 'nick_name', 'avatar', 'group_id'])->whereIn('id', $orderUserIds)->get();

        cache([$cacheKey => $users], 30);*/

        $users = collect();

        return $this->collection($users, new WhoLikeTransformer(), '');
    }

    public function includePoint($model)
    {
        $point = $model->hasOnePoint;
        if ($point) {
            if ($point->type == 1) {
                $point->value = $point->value * $model->sell_price / 100;
            }
            return $this->item($point, new PointTransformer(), '');
        }
    }

    public function includeUser()
    {
        if ($user = auth('api')->user()) {
            return $this->item($user, new UserTransformer(), '');
        }
    }

}

class GoodsPhotoTransformer extends BaseTransformer
{
    public function transformData($model)
    {
        return $model->toArray();
    }
}

class ProductTransformer extends BaseTransformer
{
    public function transformData($model)
    {
        return $model->toArray();
    }
}

class GuessYouLikeTransformer extends BaseTransformer
{
    public function transformData($model)
    {
        return $model->toArray();
    }
}

class WhoLikeTransformer extends BaseTransformer
{
    public function transformData($model)
    {
        $res = $model->toArray();

        if ($group = $model->group) {
            $res['grade'] = $group->grade;
        } else {
            $res['grade'] = 0;
        }

        return $res;
    }
}