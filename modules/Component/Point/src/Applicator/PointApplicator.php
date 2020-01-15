<?php
namespace GuoJiangClub\Catering\Component\Point\Applicator;

use GuoJiangClub\Catering\Component\Discount\Distributors\IntegerDistributor;
use GuoJiangClub\Catering\Component\Point\Contract\PointSubjectContract;
use GuoJiangClub\Catering\Component\Order\Models\Adjustment;
use GuoJiangClub\Catering\Component\Product\Models\Goods;

class PointApplicator
{

    private $distributor;

    public function __construct(
        IntegerDistributor $distributor)
    {
        $this->distributor = $distributor;
    }

    public function apply(PointSubjectContract $subject, $point)
    {
        $uid = $subject->user_id;

        $adjustment = new Adjustment([
            'type' => Adjustment::ORDER_POINT_DISCOUNT_ADJUSTMENT,
            'label' => '使用积分',
            'origin_type' => 'point',
            'origin_id' => $uid
        ]);

        $amount = (-1) * $point * (settings('point_proportion') ? settings('point_proportion') : 0);

        if ($amount != 0) {

            $adjustment->amount = $amount;

            $subject->addAdjustment($adjustment);

            $splitDiscountAmount = $this->distributePercentage($amount, $subject);

            $i = 0;

            foreach ($subject->getItems() as $item) {
                $goods = Goods::find($item->getItemId());
                if ($goods->hasOnePoint->can_use_point) {
                    $splitAmount = $splitDiscountAmount[$i++];

                    $item->divide_order_discount += $splitAmount;
                    $item->recalculateAdjustmentsTotal();

                    $item->use_point = (-1) * $splitAmount / settings('point_proportion');
                }
            }
        }

        return true;
    }

    public function distributePercentage($amount, $subject)
    {
        $itemsTotals = [];
        foreach ($subject->getItems() as $item) {
            $goods = Goods::find($item->getItemId());
            if ($goods->hasOnePoint->can_use_point) {
                $itemsTotals[] = $item->getTotal();
            }
        }

        $total = array_sum($itemsTotals);
        $distributedAmounts = [];

        foreach ($itemsTotals as $element) {
            $distributedAmounts[] = (int)round(($element * $amount) / $total, 0, PHP_ROUND_HALF_DOWN);
        }

        $missingAmount = $amount - array_sum($distributedAmounts);
        for ($i = 0; $i < abs($missingAmount); $i++) {
            $distributedAmounts[$i] += $missingAmount >= 0 ? 1 : -1;
        }

        return $distributedAmounts;
    }

}