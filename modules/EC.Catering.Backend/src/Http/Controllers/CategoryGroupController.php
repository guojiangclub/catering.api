<?php

namespace GuoJiangClub\EC\Catering\Backend\Http\Controllers;

use GuoJiangClub\EC\Catering\Backend\Models\CategoryGroup;
use Illuminate\Http\Request;
use Encore\Admin\Facades\Admin as LaravelAdmin;
use Encore\Admin\Layout\Content;

class CategoryGroupController extends Controller
{
	public function index()
	{
		$group = CategoryGroup::all();

		return LaravelAdmin::content(function (Content $content) use ($group) {

			$content->header('分类组');

			$content->breadcrumb(
				['text' => '分类管理', 'url' => 'store/category_group', 'no-pjax' => 1],
				['text' => '分类组', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '分类管理']

			);

			$content->body(view('catering-backend::category_group.index', compact('group')));
		});
	}

	public function create()
	{
		return LaravelAdmin::content(function (Content $content) {

			$content->header('新建分类组');

			$content->breadcrumb(
				['text' => '分类管理', 'url' => 'store/category_group', 'no-pjax' => 1],
				['text' => '新建分类组', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '分类管理']

			);

			$content->body(view('catering-backend::category_group.create'));
		});
	}

	public function store(Request $request)
	{
		$input = $request->except('_token');

		if ($groupID = request('id')) {
			$group = CategoryGroup::find($groupID);
			$group->fill($input);
			$group->save();
		} else {
			$group = CategoryGroup::create($input);
		}

		return response()->json(['status'       => true
		                         , 'error_code' => 0
		                         , 'error'      => ''
		                         , 'data'       => $group]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		$group = CategoryGroup::find($id);

		return LaravelAdmin::content(function (Content $content) use ($group) {

			$content->header('修改分类组');

			$content->breadcrumb(
				['text' => '分类管理', 'url' => 'store/category_group', 'no-pjax' => 1],
				['text' => '修改分类组', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '分类管理']

			);

			$content->body(view('catering-backend::category_group.edit', compact('group')));
		});
//        return view('catering-backend::category_group.edit', compact('group'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$result = CategoryGroup::find($id)->category()->get();

		if (count($result)) {
			return response()->json(['status'       => false
			                         , 'error_code' => 0
			                         , 'error'      => '该分组下存在分类'
			                         , 'data'       => $result]);
		} else {

			CategoryGroup::destroy($id);

			return response()->json(['status'       => true
			                         , 'error_code' => 0
			                         , 'error'      => ''
			                         , 'data'       => '']);
		}
	}
}
