<?php

namespace GuoJiangClub\Catering\Backend\Extensions;

use Encore\Admin\Grid\Tools\BatchAction;

class BatchGenerateQrCode extends BatchAction
{
	public function script()
	{
		return <<<EOT

$('{$this->getElementClass()}').on('click', function() {

    swal({
      title: "是否确认操作",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "确认",
      closeOnConfirm: false,
      cancelButtonText: "取消"
    },
    function(){
        $.ajax({
	        method: 'post',
	        url: '{$this->resource}/generate/qrCode',
	        data: {
	            _token:LA.token,
	            ids: selectedRows()
	        },
	        success: function (data) {
	            $.pjax.reload('#pjax-container');
	            if (typeof data === 'object') {
                    if (data.status) {
                        swal(data.message, '', 'success');
                    } else {
                        swal(data.message, '', 'error');
                    }
                }
	        }
        });
    });
});

EOT;
	}
}