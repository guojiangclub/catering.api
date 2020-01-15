<?php

namespace GuoJiangClub\Catering\Backend\Extensions;

use Encore\Admin\Form\Field;

class UEditor extends Field
{
	protected $view = 'backend-shitang::extensions.ueditor';

	protected static $css = [
	];

	protected static $js = [
		'/laravel-u-editor/ueditor.config.js',
		'/laravel-u-editor/ueditor.all.min.js',
		'/laravel-u-editor/lang/zh-cn/zh-cn.js',
	];

	public function render()
	{
		$name = $this->formatName($this->column);

		$token = csrf_token();

		$this->script = <<<EOT
UE.delEditor('{$name}_container');
var ue = UE.getEditor('{$name}_container', {
    autoHeightEnabled: false,
    initialFrameHeight: 1000,
    initialFrameWidth:1350
});

ue.ready(function () {
    ue.execCommand('serverparam', '_token', '{$token}');
});
EOT;

		return parent::render();
	}
}