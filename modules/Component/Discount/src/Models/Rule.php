<?php

namespace GuoJiangClub\Catering\Component\Discount\Models;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
	public $timestamps = false;

	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'discount_rule');
	}

	public function discount()
	{
		return $this->belongsTo(Discount::class);
	}

	public function getCartQuantity()
	{
		if ($this->type == 'cart_quantity') {
			$configuration = json_decode($this->configuration, true);

			return $configuration['count'];
		}

		return 0;
	}

	public function getItemsTotal()
	{
		if ($this->type == 'item_total') {
			$configuration = json_decode($this->configuration, true);

			return $configuration['amount'];
		}

		return 0;
	}

	public function getRoleName()
	{
		if ($this->type == 'contains_role') {

			$configuration = json_decode($this->configuration, true);

			return $configuration['name'];
		}

		return '';
	}
}