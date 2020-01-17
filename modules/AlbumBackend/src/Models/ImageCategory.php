<?php

namespace GuoJiangClub\Catering\AlbumBackend\Models;

use Illuminate\Database\Eloquent\Model;

class ImageCategory extends Model
{

	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'images_category');
	}
}