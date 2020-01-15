<?php

namespace GuoJiangClub\Catering\Backend\Http\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use GuoJiangClub\Catering\Backend\Models\Banners;
use iBrand\Backend\Http\Controllers\Controller;
use iBrand\Backend\Http\Controllers\ModelForm;

class BannerController extends Controller
{
	use ModelForm;

	public function index()
	{
		return Admin::content(function (Content $content) {
			$content->description('轮播图');

			$content->breadcrumb(
				['text' => '轮播图列表', 'no-pjax' => 1, 'left-menu-active' => '轮播图']
			);

			$content->body($this->grid()->render());
		});
	}

	public function create()
	{
		return Admin::content(function (Content $content) {
			$content->description('添加轮播图');

			$content->breadcrumb(
				['text' => '添加轮播图', 'no-pjax' => 1, 'left-menu-active' => '轮播图']
			);

			$content->body($this->form()->render());
		});
	}

	public function edit($id)
	{
		return Admin::content(function (Content $content) use ($id) {
			$content->description('修改轮播图');

			$content->breadcrumb(
				['text' => '修改轮播图', 'no-pjax' => 1, 'left-menu-active' => '轮播图']
			);

			$content->body($this->form()->edit($id));
		});
	}

	public function form()
	{
		return Admin::form(Banners::class, function (Form $form) {
			$form->text('blank_url', '跳转连接')->rules('required', ['blank_url.required' => ['请填写 跳转连接']]);
			$form->radio('blank_type', '跳转类型')->options(['self' => '活动详情页', 'other_mini_program' => '其他小程序', 'other_links' => '外链', 'share' => '分享'])->default('self');
			$form->image('img', '图片')->uniqueName()->rules('required', ['img.required' => '请上传 图片']);
			$form->number('sort', '排序')->default(99);
			$form->radio('status', '状态')->options([0 => '禁用', 1 => '启用'])->default(1);

			$form->saved(function (Form $form) {
				$img = $form->model()->img;
				if ($img && !str_contains($img, config('ibrand.backend.disks.admin.url'))) {
					$form->model()->img = config('ibrand.backend.disks.admin.url') . '/' . $img;
				}

				$form->model()->save();
			});

			$form->tools(function (Form\Tools $tools) {
				$tools->disableDelete();
				$tools->disableView();
			});

			$form->footer(function (Form\Footer $footer) {
				$footer->disableReset();
			});
		});
	}

	public function grid()
	{
		return Admin::grid(Banners::class, function (Grid $grid) {
			$grid->id()->sortable();
			$grid->column('img', '图片')->display(function ($img) {
				if ($img) {
					return '<img src="' . $img . '" width="100" />';
				}

				return '';
			});
			$grid->column('status', '状态')->display(function ($status) {
				return $status ? '启用' : '禁用';
			});
			$grid->sort()->sortable();

			$grid->disableExport();
			$grid->disableFilter();
			$grid->actions(function ($actions) {
				$actions->disableView();
			});
		});
	}
}