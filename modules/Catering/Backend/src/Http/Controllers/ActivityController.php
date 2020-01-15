<?php

namespace GuoJiangClub\Catering\Backend\Http\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use GuoJiangClub\Catering\Backend\Extensions\BatchGenerateQrCode;
use GuoJiangClub\Catering\Backend\Extensions\Preview;
use GuoJiangClub\Catering\Backend\Models\Activity;
use iBrand\Backend\Http\Controllers\Controller;
use iBrand\Backend\Http\Controllers\ModelForm;
use Illuminate\Http\Request;
use EasyWeChat;
use Storage;

class ActivityController extends Controller
{
	use ModelForm;

	public function index()
	{
		return Admin::content(function (Content $content) {
			$content->description('活动列表');

			$content->breadcrumb(
				['text' => '活动列表', 'no-pjax' => 1, 'left-menu-active' => '活动管理']
			);

			$content->body($this->grid()->render());
		});
	}

	public function create()
	{
		return Admin::content(function (Content $content) {
			$content->description('添加活动');

			$content->breadcrumb(
				['text' => '添加活动', 'no-pjax' => 1, 'left-menu-active' => '活动管理']
			);

			$content->body($this->form()->render());
		});
	}

	public function edit($id)
	{
		return Admin::content(function (Content $content) use ($id) {
			$content->description('修改活动');

			$content->breadcrumb(
				['text' => '修改活动', 'no-pjax' => 1, 'left-menu-active' => '活动管理']
			);

			$content->body($this->form($id)->edit($id));
		});
	}

	public function form($id = null)
	{
		return Admin::form(Activity::class, function (Form $form) use ($id) {
			$form->text('title', '活动标题')->rules('required', ['title.required' => '请填写 活动标题']);
			$form->text('apply_url', '报名地址')->rules('required', ['apply_url.required' => '请填写 报名地址']);
			$form->image('img', '添加banner')->uniqueName()->rules('required', ['img.required' => '请上传 banner图']);
			$form->text('address', '活动地址')->rules('required', ['address.required' => '请填写 详细地址'])->default('港汇恒隆广场')->readOnly();
			$form->text('floor_no', '所在楼层')->rules('required', ['floor_no.required' => '请填写 所在楼层']);
			$form->dateRange('started_at', 'ended_at', '活动时间')->rules(['started_atstart' => 'required', 'ended_atend' => 'required'], ['started_atstart.required' => '请选择 活动开始时间', 'ended_atend.required' => '请选择 活动结束时间']);
			$form->editor('content', '活动内容')->rules('required', ['content.required' => '请填写 活动内容']);
			if ($id) {
				$form->multipleSelect('sub_activity_ids', '关联活动')->options(Activity::where('status', 1)->where('id', '!=', $id)->pluck('title', 'id'))->placeholder('请选择 关联活动');
			} else {
				$form->multipleSelect('sub_activity_ids', '关联活动')->options(Activity::where('status', 1)->pluck('title', 'id'))->placeholder('请选择 关联活动');
			}

			$form->radio('status', '状态')->default(1)->options([1 => '发布', 0 => '下架']);
			$form->radio('is_recommended', '推荐')->default(0)->options([1 => '是', 0 => '否']);
			$form->radio('is_notice', '重大活动提醒')->default(0)->options([1 => '是', 0 => '否']);
			//$form->number('collect_count', '收藏数量');

			$form->model()->address = '港汇恒隆广场';
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
		return Admin::grid(Activity::class, function (Grid $grid) {
			$grid->id()->sortable();
			$grid->column('title', '活动标题');
			$grid->column('img', '展示图片')->display(function ($img) {
				if ($img) {
					return '<img src="' . $img . '" width="100" />';
				}

				return '';
			});
			$grid->column('column_not_in_table', '跳转连接')->display(function () {
				return '/pages/index/detail/main?id=' . $this->id;
			});
			$grid->column('address', '详细地址');
			$grid->column('floor_no', '所在楼层');
			$grid->column('started_at', '活动开始时间')->display(function ($started_at) {
				return date('Y-m-d', strtotime($started_at));
			});
			$grid->column('ended_at', '活动结束时间')->display(function ($ended_at) {
				return date('Y-m-d', strtotime($ended_at));
			});
			$grid->column('status', '状态')->display(function ($status) {
				return $status ? '发布' : '下架';
			});
			$grid->column('is_recommended', '是否推荐')->display(function ($is_recommended) {
				return $is_recommended ? '是' : '否';
			});
			$grid->column('is_notice', '提醒')->display(function ($is_notice) {
				return $is_notice ? '是' : '否';
			});
			//$grid->column('collect_count', '收藏数量');
			$grid->column('qr_code_url', '小程序码')->display(function ($qr_code_url) {
				if ($qr_code_url) {
					return '<img src="' . $qr_code_url . '" width="70" />';
				}

				return '';
			});

			$grid->tools(function ($tools) {
				$tools->batch(function ($batch) {
					$batch->add('活动小程序码', new BatchGenerateQrCode());
				});
			});

			$grid->disableExport();
			$grid->actions(function ($actions) {
				$actions->disableView();

				$actions->append(new Preview($actions->getKey()));
			});

			$grid->filter(function ($filter) {
				$filter->disableIdFilter();
				$filter->like('title', '活动标题');
			});
		});
	}

	public function generateQrCode(Request $request)
	{
		$input = $request->except('_token');
		if (!empty($input['ids'])) {
			$dir         = 'activity_qr_code';
			$page        = settings('activity_qr_code_pages');
			$width       = 260;
			$miniProgram = EasyWeChat::miniProgram('shitang');
			foreach ($input['ids'] as $id) {
				$activity = Activity::find($id);
				if (!is_null($activity->qr_code_url)) {
					continue;
				}

				$response = $miniProgram->app_code->getUnlimit($id, ['width' => $width, 'page' => $page]);
				if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {

					$path = $response->saveAs(storage_path('app/public/' . $dir), $id . '_activity_qr_code.png');
					Activity::where('id', $id)->update(['qr_code_url' => Storage::disk('public')->url($dir . '/' . $path)]);
				}
			}
		}

		return response()->json(['status' => true, 'message' => '操作成功']);
	}

	public function preview($id)
	{
		$activity = Activity::find($id);

		return view('backend-grand::preview.index', compact('activity'));
	}
}