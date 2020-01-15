<?php

namespace GuoJiangClub\EC\Catering\Backend\Http\Controllers;

use Illuminate\Http\Request;
use GuoJiangClub\EC\Catering\Backend\Repositories\BrandRepository;
use Encore\Admin\Facades\Admin as LaravelAdmin;
use Encore\Admin\Layout\Content;

class BrandController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */

	protected $brandRepository;

	public function __construct(BrandRepository $brandRepository)
	{
		$this->brandRepository = $brandRepository;
	}

	public function index()
	{
		$brand = $this->brandRepository->all();

		return LaravelAdmin::content(function (Content $content) use ($brand) {

			$content->header('品牌列表');

			$content->breadcrumb(
				['text' => '品牌管理', 'url' => 'store/brand', 'no-pjax' => 1],
				['text' => '品牌列表', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '品牌管理']

			);

			$content->body(view('catering-backend::brand.index', compact('brand')));
		});
//        return view("catering-backend::brand.index",compact('brand'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		return LaravelAdmin::content(function (Content $content) {

			$content->header('添加品牌');

			$content->breadcrumb(
				['text' => '品牌管理', 'url' => 'store/brand', 'no-pjax' => 1],
				['text' => '添加品牌', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '品牌管理']

			);

			$content->body(view('catering-backend::brand.create'));
		});
//        return view('catering-backend::brand.create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$input = $request->except('id', '_token', 'file');
		if (request('id')) {
			$this->brandRepository->update($input, request('id'));
			$id = request('id');
		} else {
			$band = $this->brandRepository->create($input);
			$id   = $band->id;
		}

		return response()->json(['status'       => true
		                         , 'error_code' => 0
		                         , 'error'      => ''
		                         , 'data'       => $id]);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		//
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
		$brand_list = $this->brandRepository->findByField('id', $id)->first();

		return LaravelAdmin::content(function (Content $content) use ($brand_list) {

			$content->header('编辑品牌');

			$content->breadcrumb(
				['text' => '品牌管理', 'url' => 'store/brand', 'no-pjax' => 1],
				['text' => '编辑品牌', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '品牌管理']

			);

			$content->body(view('catering-backend::brand.edit', compact('brand_list')));
		});
//        return view('catering-backend::brand.edit',compact('brand_list'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param int                      $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		//
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
		$this->brandRepository->delete($id);

		return redirect()->back()->withFlashSuccess('品牌已删除');
	}
}
