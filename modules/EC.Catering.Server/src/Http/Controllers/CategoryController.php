<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/9/22
 * Time: 14:24
 */

namespace ElementVip\Server\Http\Controllers;

use ElementVip\Component\Category\Models\Category as modelCategory;
use ElementVip\Server\Transformers\CatgoryTransformer;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function __construct()
    {
        //  modelCategory::fixTree();
    }

    /**
     * default method two level category will be sort and return
     */
    public function index()
    {
        $group = request('group_id') ? request('group_id') : 0;
        $level = request('level') ? request('level') : 2;
        return $this->response()->collection(modelCategory::getAllInformationByGroupId($group, $level), new CatgoryTransformer());
    }


    /**
     *  1.according to the groupId get the category
     *  2.if the request exist the limitLevel, category will be sort and return the needed level
     *
     */
    public function getCategroiesByGroupId(Request $request)
    {


        if ($request->has('groupId')) {

            if ($request->has('limitLevel')) {

                $res = modelCategory::getAllInformationByGroupId($request->groupId, $request->limitLevel);
            } else {

                $res = modelCategory::getAllInformationByGroupId($request->groupId);
            }

            return $this->response->collection($res, new CatgoryTransformer());
        }

    }

    /**
     *  1.according to the parentIdOrName get the subCategory
     *  2.if the request exist the limitLevel, category will be sort and return the needed level
     *
     */


    public function getSubCategroiesByNameOrId(Request $request)
    {

        if ($request->has('parentIdOrName')) {

            if ($request->has('limitLevel')) {
                $res = modelCategory::getSubAllInformationByNameOrId($request->parentIdOrName, $request->limitLevel);
            } else {
                $res = modelCategory::getSubAllInformationByNameOrId($request->parentIdOrName);
            }

            return $this->response->collection($res, new CatgoryTransformer());

        }
    }

    public function getSubIdsById(Request $request)
    {

        if ($request->has('id')) {
            if ($request->has('excludeParent') && 1 == $request->excludeParent) {
                $excludeParent = 1;
            } else {
                $excludeParent = 0;
            }
            return modelCategory::getSubIdsById($request->id, $excludeParent);
        }


    }

    public function getAncestors($id)
    {
        return modelCategory::getAncestorsSelf($id);
    }

}