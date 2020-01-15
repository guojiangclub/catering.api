<?php

namespace GuoJiangClub\EC\Catering\Backend\Http\Controllers;

use GuoJiangClub\Catering\Component\User\Models\UserRights;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use iBrand\Backend\Http\Controllers\ModelForm;

class UserRightsController extends Controller
{
	use ModelForm;

	public function index()
	{
		return Admin::content(function (Content $content) {
			$content->description('会员权益管理');

			$content->breadcrumb(
				['text' => '会员权益', 'no-pjax' => 1, 'left-menu-active' => '会员权益']
			);

			$content->body($this->grid()->render());
		});
	}

	public function create()
	{
		return Admin::content(function (Content $content) {
			$content->description('添加会员权益');

			$content->breadcrumb(
				['text' => '添加会员权益', 'no-pjax' => 1, 'left-menu-active' => '会员权益']
			);

			$content->body($this->form()->render());
		});
	}

	public function edit($id)
	{
		return Admin::content(function (Content $content) use ($id) {
			$content->description('修改会员权益');

			$content->breadcrumb(
				['text' => '修改会员权益', 'no-pjax' => 1, 'left-menu-active' => '会员权益']
			);

			$content->body($this->form()->edit($id));
		});
	}

	public function form()
	{
		return Admin::form(UserRights::class, function (Form $form) {
			$form->text('name', '权益名称')->rules('required', ['name.required' => '请填写 权益名称']);
			$form->number('sort', '排序')->default(99)->rules('required', ['sort.required' => '请填写 排序']);
			$form->image('img', '图片')->uniqueName()->removable()->rules('required', ['img.required' => '请上传 图片']);
			$form->radio('status', '状态')->default(1)->options([1 => '启用', 0 => '下架']);

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
		return Admin::grid(UserRights::class, function (Grid $grid) {
			$grid->id()->sortable();
			$grid->column('name', '权益名称');
			$grid->column('img', '图片')->display(function ($img) {
				return '<img src="' . $img . '" width="80">';
			});
			$grid->column('status', '状态')->display(function ($status) {
				switch ($status) {
					case 0:
						$statusText = '下架';
						break;
					case 1:
						$statusText = '启用';
						break;
				}

				return $statusText;
			});

			$grid->column('sort', '排序')->sortable();

			$grid->disableExport();
			$grid->actions(function ($actions) {
				$actions->disableView();
			});

			$grid->filter(function ($filter) {
				$filter->disableIdFilter();
			});
		});
	}
}