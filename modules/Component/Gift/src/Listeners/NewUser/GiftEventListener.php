<?php
namespace GuoJiangClub\Catering\Component\Gift\Listeners\NewUser;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;

class GiftEventListener
{

    protected $pointRepository;

    public function __construct(PointRepository $pointRepository)
    {
        $this->pointRepository = $pointRepository;
    }

    public function subscribe($events)
    {
        $events->listen(
            'gift.new.user.point',
            'GuoJiangClub\Catering\Component\Gift\Listeners\NewUser\PointEventListener'
        );

        $events->listen(
            'gift.new.user.coupon',
            'GuoJiangClub\Catering\Component\Gift\Listeners\NewUser\CouponEventListener'
        );

    }
}