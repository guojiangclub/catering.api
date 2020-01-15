<?php

namespace GuoJiangClub\Catering\Component\User\Models\Relations;

trait BelongsToGroupTrait
{
	public function group()
	{
		return $this->belongsTo('GuoJiangClub\Catering\Component\User\Models\Group');
	}
}