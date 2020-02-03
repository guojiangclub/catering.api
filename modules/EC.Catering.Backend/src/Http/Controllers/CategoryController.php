<?php

namespace GuoJiangClub\EC\Catering\Backend\Http\Controllers;

use GuoJiangClub\EC\Catering\Backend\Models\Category;
use GuoJiangClub\EC\Catering\Backend\Models\CategoryGroup;
use GuoJiangClub\Catering\Component\Product\Models\GoodsCategory;
use Illuminate\Http\Request;
use GuoJiangClub\EC\Catering\Backend\Repositories\CategoryRepository;
use Encore\Admin\Facades\Admin as LaravelAdmin;
use Encore\Admin\Layout\Content;

class CategoryController extends Controller
{
	protected $categoryRepository;
	protected $wx;
	protected $group_id;

	public function __construct(CategoryRepository $categoryRepository)
	{
		$this->categoryRepository = $categoryRepository;

		$this->group_id = CategoryGroup::first()->id;
	}

	public function index()
	{
		$group_id   = $this->group_id;
		$categories = $this->categoryRepository->getLevelCategory($group_id);

		return LaravelAdmin::content(function (Content $content) use ($categories, $group_id) {

			$content->header('分类列表');

			$content->breadcrumb(
				['text' => '分类列表', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '分类管理']
			);

			$content->body(view('catering-backend::category.index', compact('categories', 'group_id')));
		});
	}

	public function create()
	{
		$group_id   = $this->group_id;
		$categories = $this->categoryRepository->getLevelCategory($group_id, 0, '&nbsp;&nbsp;');
		foreach ($categories as $k => $c) {
			if ($c->level > 1) {
				unset($categories[$k]);
			}
		}
		$category = new Category();

		return LaravelAdmin::content(function (Content $content) use ($categories, $group_id, $category) {

			$content->header('添加分类');

			$content->breadcrumb(
				['text' => '添加分类', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '分类管理']
			);

			$content->body(view('catering-backend::category.create', compact('categories', 'category', 'group_id')));
		});
	}

	public function store(Request $request)
	{
		$input = $request->except('_token');
		if (!$input['name']) {
			return response()->json(['status'       => false
			                         , 'error_code' => 0
			                         , 'error'      => '请填写分类名称'
			                         , 'data'       => []]);
		}

		$category = Category::create([
			'group_id'  => $input['group_id'],
			'name'      => $input['name'],
			'parent_id' => $input['parent_id'],
			'sort'      => $input['sort'],
		]);

		$this->categoryRepository->setCategoryLevel($category->id, $input['parent_id']);

		return response()->json(['status'       => true
		                         , 'error_code' => 0
		                         , 'error'      => ''
		                         , 'data'       => $category]);
	}

	public function edit($id)
	{
		$category   = $this->categoryRepository->find($id);
		$categories = $this->categoryRepository->getLevelCategory($category->group_id, 0, '&nbsp;&nbsp;');
		foreach ($categories as $k => $c) {
			if ($c->level > 1) {
				unset($categories[$k]);
			}
		}

		return LaravelAdmin::content(function (Content $content) use ($categories, $category) {

			$content->header('修改分类');

			$content->breadcrumb(
				['text' => '修改分类', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '分类管理']
			);

			$content->body(view('catering-backend::category.edit', compact('categories', 'category')));
		});
	}

	public function update(Request $request, $id)
	{
		$input = $request->except('_token');
		if (!$input['name']) {
			return response()->json(['status'       => false
			                         , 'error_code' => 0
			                         , 'error'      => '请填写分类名称'
			                         , 'data'       => []]);
		}
		$category = $this->categoryRepository->update($input, $id);

		$this->categoryRepository->setCategoryLevel($category->id, $input['parent_id']);
		$this->categoryRepository->setSonCategoryLevel($category->id);

		return response()->json(['status'       => true
		                         , 'error_code' => 0
		                         , 'error'      => ''
		                         , 'data'       => $category]);
	}

	public function check()
	{
		$status = true;
		$id     = request('id');
		$ids    = Category::where('parent_id', $id)->pluck('id')->toArray();
		array_push($ids, $id);
		$goods = GoodsCategory::whereIn('category_id', $ids);
		if ($goods->first()) {
			$status = false;
		}

		return response()->json(['status'       => $status
		                         , 'error_code' => 0
		                         , 'error'      => '']);
	}

	public function destroy()
	{
		$status = false;
		$id     = request('id');
		if ($this->categoryRepository->delCategory($id)) {
			$status = true;
		}

		return response()->json(['status'       => $status
		                         , 'error_code' => 0
		                         , 'error'      => '']);
	}

	public function category_sort(Request $request)
	{
		$input = $request->except('_token');
		$id    = $request->input('id');
		$this->categoryRepository->update($input, $id);

		return response()->json([
			'error'      => '',
			'status'     => true,
			'data'       => '1',
			'error_code' => 0,
		]);
	}

	public function goods_category($type)
	{

		$categoryData   = json_encode($this->categoryRepository->getSortCategory(), JSON_UNESCAPED_UNICODE);
		$categoryParent = json_encode($this->categoryRepository->getCategoryParent(), JSON_UNESCAPED_UNICODE);

//        $categoryData =$this->categoryRepository->getSortCategory();
//        $categoryParent =$this->categoryRepository->getCategoryParent();

		// print_r($categoryData);
		//  print_r($categoryParent);
		// dd($categoryData);

		return view('backend.store.category.goods_category', compact('categoryData', 'categoryParent', 'type'));
	}
}
