<?php

namespace GuoJiangClub\Catering\Component\Category\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use DB;

class Category extends Model
{
	use NodeTrait;

	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'category');
	}

	public static function getIdsByParentID($parentID)
	{
		$subIds = self::where('path', 'like', '%/' . $parentID . '/%')->get()->pluck('id')->toArray();

		return $subIds;
	}

	/**
	 * get all ids by categoryId
	 *
	 * @param      $categoryId
	 * @param bool $excludeSelf whether to exclude self
	 *
	 * @return array
	 */
	public static function getSubIdsById($categoryId, $excludeSelf = false)
	{
		$subIds = self::descendantsOf($categoryId)->pluck('id')->toArray();
		if ($excludeSelf) {
			return $subIds;
		}

		return array_merge([$categoryId], $subIds);
	}

	/**
	 *  get all information by categoryGroupId
	 *
	 */

	public static function getAllInformationByGroupId($groupId = 0, $depth = 2)
	{
		$sub = self::withDepth();

		$query = self::from(DB::raw("({$sub->toSql()}) as sub"))
			->where('depth', '<', $depth)->orderBy('sort', 'Asc');

		if (!empty($groupId)) {
			$query = $query->where('group_id', '=', $groupId);
		}

		return $query->get()->toTree();
	}

	/**
	 *  according to the $catkeyword to get allInformation
	 *  @$catKeyword id or name
	 * @ bool $excludeSelf  whether to exclude self
	 *
	 * */
	public static function getSubAllInformationByNameOrId($catKeyword, $depth = 0)
	{

		$rootId = self::Where('name', '=', $catKeyword)->orWhere('id', $catKeyword)->value('id');
		if (!empty($rootId)) {
			if (empty($depth)) {
				$res = Category::descendantsOf($rootId)->toTree($rootId);
			} else {
				$sub       = self::withDepth();
				$depthRoot = self::withDepth()->find($rootId)->depth;
				$res       = self::from(DB::raw("({$sub->toSql()}) as sub"))->mergeBindings($sub)
					->where('depth', '<', $depth + $depthRoot)->orderBy('sort', 'Asc')->get()->toTree($rootId);
			}
		} else {
			/**
			 * if not found the parent , it will return the whole tree
			 */
			$tree = self::get()->orderBy('sort', 'Asc')->toTree();
			$res  = $tree;
		}

		return $res;
	}

	public static function getAncestorsSelf($id, $exclude = false)
	{
		$node     = self::find($id);
		$children = $node->ancestors()->get();
		if (!$exclude) {
			$children->push($node);
		}

		return $children;
	}
}
