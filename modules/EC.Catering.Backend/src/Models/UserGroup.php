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

use GuoJiangClub\Catering\Component\User\Models\Group;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class UserGroup extends Group implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

	public function setRightsIdsAttribute($value)
	{
		if (count($value)) {
			$this->attributes['rights_ids'] = json_encode($value);
		}
	}
}
