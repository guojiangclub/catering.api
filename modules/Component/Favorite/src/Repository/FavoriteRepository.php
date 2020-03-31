<?php

namespace GuoJiangClub\Catering\Component\Favorite\Repository;

use GuoJiangClub\Catering\Component\Favorite\Models\Favorite;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Traits\CacheableRepository;

class FavoriteRepository extends BaseRepository
{
    use CacheableRepository;

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Favorite::class;
    }

    public function getFavorite($user_id, $type, $limit)
    {
        return Favorite::where('user_id', $user_id)->where('favoriteable_type', $type)->with('favoriteable')->paginate($limit);
    }

    public function storeFavorite($user_id, $id, $type)
    {
        if (Favorite::where('user_id', $user_id)->where('favoriteable_id', $id)->where('favoriteable_type', $type)->first()) {
            Favorite::where('user_id', $user_id)->where('favoriteable_id', $id)->where('favoriteable_type', $type)->delete();
            return true;
        } else {
            $this->create([
                'user_id' => $user_id,
                'favoriteable_id' => $id,
                'favoriteable_type' => $type
            ]);
            return true;
        }
    }

    public function delFavorites($user_id, array $ids, $type)
    {
        return Favorite::where('user_id', $user_id)->whereIn('favoriteable_id', $ids)->where('favoriteable_type', $type)->delete();
    }

    public function isFavorite($user_id, $id, $type)
    {
        return $this->findWhere(['user_id' => $user_id, 'favoriteable_id' => $id, 'favoriteable_type' => $type])->first();
        //return Favorite::where('user_id', $user_id)->where('favoriteable_id', $id)->where('favoriteable_type', $type)->first();
    }

}