<?php

namespace GuoJiangClub\Catering\Component\Discount\Console;

use GuoJiangClub\Catering\Component\Discount\Models\Action;
use GuoJiangClub\Catering\Component\Discount\Models\Coupon;
use GuoJiangClub\Catering\Component\Discount\Models\Discount;
use GuoJiangClub\Catering\Component\Discount\Models\Rule;
use Faker\Factory;
use Illuminate\Console\Command;

class DiscountCommand extends Command
{

	protected $signature = 'discount:factory
     {--coupon : Create  coupons}
     {--discount : Create  discounts}
     ';

	protected $description = 'create discount test data.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		if ($this->option('coupon')) {
			return $this->generateCouponData();
		}

		if ($this->option('discount')) {
			return $this->generateDiscountsData();
		}
		/*$this->generateContainsProductData();
		$this->generateItemTotalRuleData();
		$this->generateOrderFixedDiscountActionData();
		$this->generateOrderPercentageDiscountActionData();
		return $this->generateCartQuantityData();*/
	}

	private function generateCartQuantityData()
	{
		$faker = Factory::create('zh_CN');

		$discount = factory(Discount::class, 'discount')->create([
			'usage_limit' => null,
			'starts_at'   => $faker->dateTimeBetween('-1 month', 'now'),
			'ends_at'     => $faker->dateTimeBetween('now', '+1 months'),
			'label'       => 'test',
		]);

		/*rule of CartQuantityRuleChecker*/
		$configuration = ['count' => 3];
		$discount_rule = factory(Rule::class, 'discount_rule')->create([
			'discount_id'   => $discount->id,
			'type'          => 'cart_quantity',
			'configuration' => json_encode($configuration),
		]);
	}

	private function generateContainsProductData()
	{
		$faker = Factory::create('zh_CN');

		$discount = factory(Discount::class, 'discount')->create([
			'usage_limit' => null,
			'starts_at'   => $faker->dateTimeBetween('-1 month', 'now'),
			'ends_at'     => $faker->dateTimeBetween('now', '+1 months'),
			'label'       => 'test',
		]);

		/*rule of ContainsProductRuleChecker*/
		$orderItemIds = [4, 5, 6, 7, 8, 9];

		$configuration = ['items' => $orderItemIds];

		$discount_rule = factory(Rule::class, 'discount_rule')->create([
			'discount_id'   => $discount->id,
			'type'          => 'contains_product',
			'configuration' => json_encode($configuration),
		]);
	}

	private function generateItemTotalRuleData()
	{
		$faker = Factory::create('zh_CN');

		$discount = factory(Discount::class, 'discount')->create([
			'usage_limit' => null,
			'starts_at'   => $faker->dateTimeBetween('-1 month', 'now'),
			'ends_at'     => $faker->dateTimeBetween('now', '+1 months'),
			'label'       => 'test',
		]);

		$configuration = ['amount' => $faker->randomFloat(2, 100, 1000)];

		$discount_rule = factory(Rule::class, 'discount_rule')->create([
			'discount_id'   => $discount->id,
			'type'          => 'item_total',
			'configuration' => json_encode($configuration),
		]);
	}

	private function generateOrderFixedDiscountActionData()
	{
		$faker = Factory::create('zh_CN');

		$discount = factory(Discount::class, 'discount')->create([
			'usage_limit' => null,
			'starts_at'   => $faker->dateTimeBetween('-1 month', 'now'),
			'ends_at'     => $faker->dateTimeBetween('now', '+1 months'),
			'label'       => 'test',
		]);

		$configuration = ['amount' => $faker->randomFloat(2, 10, 100)];

		$discount_action = factory(Action::class, 'discount_action')->create([
			'discount_id'   => $discount->id,
			'type'          => 'order_fixed_discount',
			'configuration' => json_encode($configuration),
		]);
	}

	private function generateOrderPercentageDiscountActionData()
	{
		$faker = Factory::create('zh_CN');

		$discount = factory(Discount::class, 'discount')->create([
			'usage_limit' => null,
			'starts_at'   => $faker->dateTimeBetween('-1 month', 'now'),
			'ends_at'     => $faker->dateTimeBetween('now', '+1 months'),
			'label'       => 'test',
		]);

		$configuration = ['percentage' => ($faker->numberBetween(5, 25))];

		$discount_action = factory(Action::class, 'discount_action')->create([
			'discount_id'   => $discount->id,
			'type'          => 'order_percentage_discount',
			'configuration' => json_encode($configuration),
		]);
	}

	private function generateCouponData($num = 20)
	{
		$faker = Factory::create('zh_CN');

		$discounts = factory(Discount::class, 'discount', $num)->create([
			'usage_limit'  => null,
			'starts_at'    => $faker->dateTimeBetween('-1 month', 'now'),
			'ends_at'      => $faker->dateTimeBetween('now', '+1 months'),
			'coupon_based' => true,
		]);

		$discount_rule = factory(Rule::class, 'discount_rule', $num + 10)->create(
			['discount_id' => $discounts->random()->id]
		)->each(function ($rule) use ($discounts) {
			$rule->discount_id = $discounts->random()->id;
			$rule->save();
		});
		/*
				$discount_action = factory(Action::class, 'discount_action', 20)->create(
					['discount_id' => $discounts->random()->id]
				)->each(function ($action) use ($discounts) {
					$action->discount_id = $discounts->random()->id;
					$action->save();
				});

		*/
		/**
		 * 求差集
		 * 一个discount只包含一个action 所以 action < discount
		 */
		$discountIds = Discount::pluck('id')->toArray();

		$discount_action = factory(Action::class, 'discount_action', $num)->create(
			['discount_id' => $discounts->random()->id]
		)->each(function ($action) use ($discountIds, $faker) {

			$currentDiscountIds  = Action::pluck('discount_id')->toArray();
			$diff                = array_diff($discountIds, $currentDiscountIds);
			$action->discount_id = $faker->randomElement($diff);

			$action->save();
		});

		$coupon = factory(Coupon::class, 'discount_coupon', 50)->create(
			['discount_id' => $discounts->random()->id
			 , 'user_id'   => $faker->numberBetween(1, 10)]
		)->each(function ($coupon) use ($discounts, $faker) {
			$coupon->discount_id = $discounts->random()->id;
			$coupon->user_id     = $faker->numberBetween(1, 10);
			$coupon->save();
		});
	}

	private function generateDiscountsData()
	{
		$faker = Factory::create('zh_CN');

		$discounts = factory(Discount::class, 'discount', 10)->create([
			'usage_limit' => null,
			'starts_at'   => $faker->dateTimeBetween('-1 month', 'now'),
			'ends_at'     => $faker->dateTimeBetween('now', '+1 months'),
		]);

		$discount_rule = factory(Rule::class, 'discount_rule', 20)->create(
			['discount_id' => $discounts->random()->id]
		)->each(function ($rule) use ($discounts) {
			$rule->discount_id = $discounts->random()->id;
			$rule->save();
		});

		$discount_action = factory(Action::class, 'discount_action', 20)->create(
			['discount_id' => $discounts->random()->id]
		)->each(function ($action) use ($discounts) {
			$action->discount_id = $discounts->random()->id;
			$action->save();
		});
	}
}