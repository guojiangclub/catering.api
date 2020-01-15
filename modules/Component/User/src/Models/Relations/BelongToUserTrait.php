<?php

namespace GuoJiangClub\Catering\Component\User\Models\Relations;

use GuoJiangClub\Catering\Component\User\Models\User;

trait BelongToUserTrait
{
	public function user()
	{
		return $this->belongsTo(User::class);
	}
}