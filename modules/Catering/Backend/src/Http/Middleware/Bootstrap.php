<?php

namespace GuoJiangClub\Catering\Backend\Http\Middleware;

use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;

class Bootstrap
{
	public function handle(Request $request, \Closure $next)
	{
		Admin::js('/laravel-u-editor/ueditor.config.js');
		Admin::js('/laravel-u-editor/ueditor.all.min.js');
		Admin::js('/laravel-u-editor/lang/zh-cn/zh-cn.js');

		if (file_exists($bootstrap = __DIR__ . '/../../Extensions/' . 'bootstrap.php')) {
			require $bootstrap;
		}

		$script = <<<EOT
$('body').on('ifChanged', '.grid-select-all', function(event) {
    if (this.checked) {
        $('.grid-row-checkbox').iCheck('check');
    } else {
        $('.grid-row-checkbox').iCheck('uncheck');
    }
});

$('body').on('ifChanged', '.grid-row-checkbox', function () {
	if (this.checked) {
	    $(this).closest('tr').css('background-color', '#ffffd5');
	} else {
	    $(this).closest('tr').css('background-color', '');
	}
});
EOT;

		Admin::script($script);

		return $next($request);
	}
}