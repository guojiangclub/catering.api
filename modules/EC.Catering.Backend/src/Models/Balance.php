<?php

/*
 * This file is part of ibrand/member-backend.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GuoJiangClub\EC\Catering\Backend\Models;

class Balance extends \GuoJiangClub\Catering\Component\Balance\Model\Balance
{
	public function setValueAttribute($value)
	{
		$this->attributes['value'] = $value * 100;
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
