<?php

namespace GuoJiangClub\Catering\Component\Product\Listeners;

class ProductEventListener
{

	public function onProductCommented($orderItem, $point)
	{
		if ($orderItem->type == 'GuoJiangClub\Catering\Component\Product\Models\Product') {
			$model = $orderItem->getModel()->goods;
		} else {
			$model = $orderItem->getModel();
		}
		$model->comments += 1;
		$model->grade    += $point;
		$model->save();
	}

	public function subscribe($events)
	{
		$events->listen(
			'product.commented',
			'GuoJiangClub\Catering\Component\Product\Listeners\ProductEventListener@onProductCommented'
		);
	}
}