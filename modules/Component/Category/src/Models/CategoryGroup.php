<?php

namespace GuoJiangClub\Catering\Component\Category\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryGroup extends Model
{
	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'category_group');
	}
}