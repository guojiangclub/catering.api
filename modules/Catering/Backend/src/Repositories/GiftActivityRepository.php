<?php

namespace GuoJiangClub\Catering\Backend\Repositories;

use GuoJiangClub\Catering\Backend\Models\GiftActivity;
use Prettus\Repository\Eloquent\BaseRepository;

class GiftActivityRepository extends BaseRepository
{
    public function model()
    {
        return GiftActivity::class;
    }

    public function getGiftActivityPaginated($type = 'gift_new_user')
    {
        return $this->model->where('activity_type', $type)->orderBy('created_at', 'desc')->paginate(20);
    }
}