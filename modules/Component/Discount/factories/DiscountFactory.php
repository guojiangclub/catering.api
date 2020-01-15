<?php

use Faker\Factory;

$faker = Factory::create('zh_CN');

$factory->defineAs(\GuoJiangClub\Catering\Component\Discount\Models\Discount::class, 'discount', function () use ($faker) {
	return [
		'title'       => $faker->word,
		'usage_limit' => $faker->numberBetween(3, 100),
		'used'        => $faker->randomDigitNotNull,
		'label'       => $faker->word,
		'starts_at'   => $faker->dateTimeBetween('-1 month', '+3 months'),
		'ends_at'     => $faker->dateTimeBetween('-2 month', '+4 months'),
	];
});

$factory->defineAs(\GuoJiangClub\Catering\Component\Discount\Models\Rule::class, 'discount_rule', function () use ($faker) {

	$rules = ['cart_quantity', 'item_total', 'contains_category', 'contains_product'];
	$rule  = $rules[array_rand($rules, 1)];
	if ($rule == 'cart_quantity') {
		$configuration = ['count' => $faker->randomDigitNotNull];
	} else {
		if ($rule == 'item_total') {
			$configuration = ['amount' => $faker->numberBetween(1000, 100000)];
		} else {
			if ($rule == 'contains_category') {
				$configuration = ['items' => $faker->randomElements(\GuoJiangClub\Catering\Component\Category\Models\Category::all()->pluck('id')->toArray(), 5)];
			} else {
				if ($rule == 'contains_product') {
					$configuration = ['sku'   => $faker->randomElements(\GuoJiangClub\Catering\Component\Product\Models\Goods::all()->pluck('id')->toArray(), 5)
					                  , 'spu' => $faker->randomElements(\GuoJiangClub\Catering\Component\Product\Models\Product::all()->pluck('sku')->toArray(), 5)];
				}
			}
		}
	}

	return [
		'type'          => $rule,
		'configuration' => json_encode($configuration),
	];
});

$factory->defineAs(\GuoJiangClub\Catering\Component\Discount\Models\Action::class, 'discount_action', function () use ($faker) {

	$actions = ['order_fixed_discount', 'order_percentage_discount', 'unit_fixed_discount', 'unit_percentage_discount'];

	$action = $actions[array_rand($actions, 1)];

	if ($action == 'order_fixed_discount') {
		$configuration = ['amount' => $faker->numberBetween(1000, 10000)];
	} elseif ($action == 'order_percentage_discount') {
		$configuration = ['percentage' => $faker->numberBetween(5, 20)];
	} elseif ($action == 'unit_fixed_discount') {
		$configuration = ['amount' => $faker->numberBetween(500, 5000)];
	} else {
		$configuration = ['percentage' => $faker->numberBetween(5, 20)];
	}

	return [
		'type'          => $action,
		'configuration' => json_encode($configuration),
	];
});

$factory->defineAs(\GuoJiangClub\Catering\Component\Discount\Models\Coupon::class, 'discount_coupon', function () use ($faker) {
	return [
		'code' => $faker->word,
	];
});