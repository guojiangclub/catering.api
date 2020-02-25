<?php

namespace ElementVip\Server\Http\Controllers;

use ElementVip\Component\Favorite\Repository\FavoriteRepository;
use ElementVip\Server\Transformers\FavoriteTransformer;

class FavoriteController extends Controller
{

    protected $favoriteRepository;

    public function __construct(FavoriteRepository $favoriteRepository)
    {
        $this->favoriteRepository = $favoriteRepository;
    }

    /**
     * 获取所有收藏
     */
    public function getFav(){
        $limit = request('limit') ?: 15;
        $type = request('type') ?: 'goods';
        $fav = $this->favoriteRepository->getFavorite(request()->user()->id, $type, $limit);
        if ($type == 'goods') {
            return $this->response()->paginator($fav, new FavoriteTransformer());
        }
        return $this->response()->paginator($fav, app($type . '_favorite_transformer'));
    }

    /**
     * 添加/删除 我的收藏
     */
    public function storeFav()
    {
        $id = request('favoriteable_id');
        $type = request('favoriteable_type');
        $favorite = $this->favoriteRepository->storeFavorite(request()->user()->id, $id, $type);
        if ($favorite)
            return $this->api();
        return $this->api('', false, 500, '删除失败');
    }

    /**
     * 批量删除我的收藏
     */
    public function delFavs(){
        $ids = request('favoriteable_id');
        $type = request('favoriteable_type');
        $favorite = $this->favoriteRepository->delFavorites(request()->user()->id, $ids, $type);
        if($favorite)
            return $this->api();
        return $this->api('', false, 500, '删除失败');
    }

    /**
     * 判断是否已收藏指定商品
     */
    public function getIsFav(){
        $id = request('favoriteable_id');
        $type = request('favoriteable_type');
        $isFav = $this->favoriteRepository->isFavorite(request()->user()->id, $id, $type);
        return $this->api([
            'is_Fav' => $isFav ? 1 : 0
        ]);
    }

}