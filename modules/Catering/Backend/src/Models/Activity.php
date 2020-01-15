<?php

namespace GuoJiangClub\Catering\Backend\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
	protected $table = 'st_activity';

	protected $guarded = ['id'];

	public function getSubActivityIdsAttribute($value)
	{
		if (is_string($value)) {
			return array_filter(explode(',', $value));
		}

		return $value;
	}

	public function setSubActivityIdsAttribute($value)
	{
		if (is_array($value)) {
			$this->attributes['sub_activity_ids'] = implode(',', $value);
		}
	}
}