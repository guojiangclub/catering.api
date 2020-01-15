<?php

namespace GuoJiangClub\Catering\Component\User\Models;

use GuoJiangClub\Catering\Component\User\Models\Relations\BelongsToGroupTrait;
use GuoJiangClub\Catering\Component\User\Models\Traits\UserGroupTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use GuoJiangClub\Catering\Component\Point\Traits\PointTrait;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{

	use HasApiTokens, Notifiable, BelongsToGroupTrait;
	use PointTrait;
	use UserGroupTrait;

	const STATUS_FORBIDDEN = 2;

	protected $appends = ['isNew', 'grade'];
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	/*protected $fillable = [
		'name', 'email', 'password',
	];*/

	protected $guarded = ['id'];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password', 'remember_token',
	];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		$this->setTable($prefix . 'user');
	}

	public function getGradeAttribute()
	{
		if ($group = $this->group) {
			return $group->grade;
		}

		return 0;
	}

	public function setPasswordAttribute($value)
	{
		if (\Hash::needsRehash($value)) {
			return $this->attributes['password'] = bcrypt($value);
		}

		return $this->attributes['password'] = $value;
	}

	public function setPasswordFromHash($value)
	{
		return $this->attributes['password'] = $value;
	}

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

	/*public function setNickNameAttribute($value)
	{
		$this->attributes['nick_name'] = 'base64:' . base64_encode($value);
	}*/

	public function getIsNewAttribute()
	{
		return !isset($this->password);
	}

	public function group()
	{
		return $this->belongsTo(Group::class);
	}

	public function attr()
	{
		return $this->hasMany(UserAttr::class);
	}

	public function setUserAttr($key, $value = '')
	{
		if (!empty($key)) {
			$this->attr()->create([
				'key'   => $key,
				'value' => $value,
			]);
		}
	}

	public function getUserAttr($key)
	{
		if (!empty($key)) {
			if ($attr = $this->attr()->where('key', $key)->orderBy('created_at', 'desc')->first()) {
				return $attr->value;
			}
		}

		return false;
	}

	public function loginLog()
	{
		return $this->hasMany(UserLoginLog::class);
	}
}
