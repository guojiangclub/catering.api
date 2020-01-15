<?php

namespace GuoJiangClub\Catering\Backend\Repositories;

use GuoJiangClub\Catering\Core\Models\GiftCouponReceive;
use Prettus\Repository\Eloquent\BaseRepository;

class GiftCouponReceiveRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return GiftCouponReceive::class;
    }


    public function getCouponsRecord($id, $type, $mobile = null, $limit = 15)
    {
        $query = $this->model
            ->where('origin_id', $id)
            ->where('origin_type', $type)
            ->with('coupon')
            ->with('user');

        if (!empty($mobile)) {
            $query = $query->whereHas('user', function ($query) use ($mobile) {
                return $query->where('mobile', 'like', '%' . $mobile . '%');
            });
        }

        return $query->OrderBy('created_at', 'desc')->paginate($limit);

    }


}
