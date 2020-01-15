<?php

namespace GuoJiangClub\Catering\Core\Auth;

use GuoJiangClub\Catering\Component\User\Models\Relations\BelongsToGroupTrait;
use GuoJiangClub\Catering\Component\User\Models\User as BaseUser;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use GuoJiangClub\Catering\Component\Point\Traits\PointTrait;

class User extends BaseUser
{
	use HasApiTokens, PointTrait, BelongsToGroupTrait;

	/**
	 * @param $value
	 *
	 * @return \Illuminate\Contracts\Routing\UrlGenerator|mixed|string
	 */
	public function getAvatarAttribute($value)
	{
		if (!empty($value)) {
			if (str_contains($value, 'http://wx.qlogo.cn')) {
				return str_replace('http://wx.qlogo.cn', 'https://wx.qlogo.cn', $value);
			}

			return url($value);
		}

		return $value;
	}

	public function getNickNameAttribute($value)
	{
		if (empty($value)) {
			return $value;
		}

		if (Str::contains($value, 'base64:')) {
			return base64_decode(str_replace('base64:', '', $value));
		}

		return $value;
	}

}