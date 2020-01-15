<?php

namespace GuoJiangClub\Catering\Component\User\Models\Traits;

use GuoJiangClub\Catering\Component\User\Models\ElGroup;

trait UserGroupTrait
{
	/*用户分组关联关系*/
	public function groups()
	{
		return $this->belongsToMany(ElGroup::class, 'el_group_users', 'user_id', 'group_id');
	}

	public function hasGroup($name, $requireAll = false)
	{
		if (is_array($name)) {
			foreach ($name as $roleName) {
				$hasGroup = $this->hasGroup($roleName);

				if ($hasGroup && !$requireAll) {
					return true;
				} elseif (!$hasGroup && $requireAll) {
					return false;
				}
			}

			return $requireAll;
		} else {
			foreach ($this->groups as $role) {
				if ($role->name == $name) {
					return true;
				}
			}
		}

		return false;
	}

	public function attachGroups($groups)
	{
		foreach ($groups as $group) {
			$this->attachGroup($group);
		}
	}

	public function detachGroups($groups = null)
	{
		if (!$groups) {
			$groups = $this->groups()->get();
		}

		foreach ($groups as $group) {
			$this->detachGroup($group);
		}
	}

	public function attachGroup($group)
	{
		if (is_object($group)) {
			$group = $group->getKey();
		}

		if (is_array($group)) {
			$group = $group['id'];
		}

		$this->groups()->attach($group);
	}

	/**
	 * Alias to eloquent many-to-many relation's detach() method.
	 *
	 * @param mixed $role
	 */
	public function detachGroup($group)
	{
		if (is_object($group)) {
			$group = $group->getKey();
		}

		if (is_array($group)) {
			$group = $group['id'];
		}

		$this->groups()->detach($group);
	}
}