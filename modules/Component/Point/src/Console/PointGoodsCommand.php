<?php

namespace GuoJiangClub\Catering\Component\Point\Console;

use GuoJiangClub\Catering\Component\Product\Models\Goods;
use Illuminate\Console\Command;

class PointGoodsCommand extends Command
{

	protected $signature = 'point:goods_point';

	protected $description = 'create goods default point.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$goods = Goods::where('is_del', 0)->get();
		foreach ($goods as $item) {
			$item->hasOnePoint()->delete();
			$item->hasOnePoint()->create([
				'item_id' => $item->id,
				'type'    => 1,
				'status'  => 1,
				'value'   => 100,
			]);
		}
	}

}