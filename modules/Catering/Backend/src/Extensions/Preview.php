<?php

namespace GuoJiangClub\Catering\Backend\Extensions;

use Encore\Admin\Admin;

class Preview
{
	protected $id;

	public function __construct($id)
	{
		$this->id = $id;
	}

	public function script()
	{
		return <<<SCRIPT
		$(document).on('click.modal.data-api', '[data-toggle="modal-preview"]', function (e) {
			var obj = $(this),
		    modalUrl = $(this).data('url');

	    if (modalUrl) {
		    var target = $(obj.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, '')));
		    target.modal('show');
		    target.html('').load(modalUrl, function () {

		    });
	    }
    });
SCRIPT;
	}

	protected function render()
	{
		Admin::script($this->script());

		return "<a data-target='#preview_modal{$this->id}' data-url='" . route('admin.grand.activity.preview', [$this->id]) . "' data-toggle='modal-preview' data-id='{$this->id}'><i class='fa fa-print'></i></a><div id='preview_modal{$this->id}' class='modal inmodal fade'></div>";
	}

	public function __toString()
	{
		return $this->render();
	}
}