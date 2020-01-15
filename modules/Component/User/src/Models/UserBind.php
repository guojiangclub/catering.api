<?php

namespace GuoJiangClub\Catering\Component\User\Models;

use Illuminate\Database\Eloquent\Model;

class UserBind extends Model
{
	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		$this->setTable($prefix . 'user_bind');
	}

	public function scopeByOpenIdAndType($query, $openId, $openType)
	{
		return $query->where('open_id', $openId)->where('type', $openType);
	}

	public static function bindUser($openId, $openType, $userId)
	{
		self::where('open_id', $openId)->where('type', $openType)->update(['user_id' => $userId]);
	}

	public function scopeByUserIdAndType($query, $userId, $openType)
	{
		return $query->where('user_id', $userId)->where('type', $openType);
	}

	public function scopeByAppID($query, $userId, $openType, $appID)
	{
		return $query->where('user_id', $userId)->where('type', $openType)->where('app_id', $appID);
	}
}