<?php

namespace GuoJiangClub\Catering\Backend\Extensions;

use Encore\Admin\Form\Field;

class WangEditor extends Field
{
	protected $view = 'backend-shitang::extensions.wang-editor';

	protected static $css = [
		'/assets/backend/wangEditor/release/wangEditor.min.css',
	];

	protected static $js = [
		'/assets/backend/wangEditor/release/wangEditor.min.js',
	];

	public function render()
	{
		$name = $this->formatName($this->column);

		$token = csrf_token();

		$this->script = <<<EOT

var E = window.wangEditor
var editor = new E('#{$this->id}');
editor.customConfig.zIndex = 0
editor.customConfig.uploadImgServer = '/admin/upload/image?_token={$token}'
editor.customConfig.uploadImgMaxLength = 1
editor.customConfig.uploadFileName = 'image_upload'
editor.customConfig.onchange = function (html) {
    $('input[name=\'$name\']').val(html);
}
editor.create()

EOT;

		return parent::render();
	}
}