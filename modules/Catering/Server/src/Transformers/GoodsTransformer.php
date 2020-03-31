<?php

namespace GuoJiangClub\Catering\Server\Transformers;

use DB;
use GuoJiangClub\Catering\Component\Product\Models\Goods;
use GuoJiangClub\Catering\Component\Product\Models\Supplier;
use GuoJiangClub\Catering\Component\Order\Models\Comment;

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
        'products', 'photos', 'oneComment', 'guessYouLike', 'whoLike', 'point', 'user',
    ];

    public function transformData($model)
    {
        $tags                    = explode(',', $model->tags);
        $model->tags             = $tags[0] == '' ? [] : $tags;
        $goods                   = $model->toArray();
        $goods['is_agent_goods'] = false;

        if ($this->type == 'detail') { //做的目的，是防止商品列表页去请求过多的数据
            $goods['shop_show_sell_nums'] = settings('shop_show_sell_nums') ? 1 : 0;
            $goods['can_share']           = false;
            $goods['supplier']            = Supplier::find($goods['supplier_id']);
        }

        if ($this->type == 'list') {
            $goods['content']   = '';
            $goods['contentpc'] = '';
            $goods['products']  = '';
        }

        if (isset($goods['seckill_price'])) {
            $goods['sell_price'] = $goods['seckill_price'];
        }
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

        $cid        = [];
        $categories = $model->categories;
        foreach ($categories as $category) {
            $cid[] = $category->id;
        }
        $categoryGoodsIds = DB::table(config('ibrand.app.database.prefix', 'ibrand_') . 'goods_category')->whereIn('category_id', $cid)->select('goods_id')->distinct()->get()
            ->pluck('goods_id')->toArray();
        $goods            = Goods::select(['id', 'name', 'min_price', 'sell_price', 'img'])->whereIn('id', $categoryGoodsIds)->where('is_del', 0)->get();
        $goods            = $goods->shuffle()->take(6);

        cache([$cacheKey => $goods], 30);

        //$goods = collect();

        return $this->collection($goods, new GuessYouLikeTransformer(), '');
    }

    public function includeWhoLike($model)
    {
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