<script>
    //  统一上下架
    $('.checkbox').on('ifChecked', function (event) {
        var val = $(this).val();
        $(this).parents('.goods' + val).addClass('selected');
    });

    $('.checkbox').on('ifUnchecked', function (event) {
        var val = $(this).val();
        $(this).parents('.goods' + val).removeClass('selected');
    });

    $('.edit_goods').on('click', function () {
        if ($('.checkbox ').length <= 0) {
            swal("操作失败", "当前无可数据", "warning");
            return false;
        }

        var num = $('.selected').length;
        if (num == 0) {
            swal("注意", "请勾选需要操作的商品", "warning");
            return false;
        }
        $('.batch').ladda().ladda('start');

        var arr = [];
        for (var i = 0; i < num; i++) {
            var gid = $('.selected').eq(i).attr('gid');
            arr[i] = gid;
        }
        arr_list = arr.join(',');
        console.log(JSON.stringify(arr));
        {{--console.log({{route('admin.goods.edit_goods','+arr+')}});--}}
        $('.edit_goods').attr('data-url', "/admin/store/goods/edit_goods?arr=" + arr);
//    console.log(arr);

    });
    // 商品批量上下架
    $('.lineGoods').on('click', function () {
        if ($('.checkbox ').length <= 0) {
            swal("操作失败", "当前无可数据", "warning");
            return false;
        }

        var num = $('.selected').length;
        if (num == 0) {
            swal("注意", "请勾选需要操作的商品", "warning");
            return false;
        }
        $('.batch').ladda().ladda('start');

        var arr = [];
        for (var i = 0; i < num; i++) {
            var gid = $('.selected').eq(i).attr('gid');
            arr[i] = gid;
        }

        if ($(this).attr('status') == 1) {
            var url = "{{route('admin.goods.index',['lineGoods'=>1])}}";
        }
        if (($(this).attr('status') == 2)) {
            var url = "{{route('admin.goods.index',['lineGoods'=>2])}}";
        }

        $.ajax({
            type: 'GET',
            url: url,
            data: {gid: arr},
            success: function (date) {
                parent.location.reload();
            }
        });
    });


    /**
     * 导出搜索、勾选商品
     */
    $(document).on('click.modal.data-api', '[data-toggle="modal-filter"]', function (e) {
        var $this = $(this),
                href = $this.attr('href'),
                modalUrl = $(this).data('url');

        var param = funcUrlDel('page');
        var num = $('.selected').length;

        if (param == '' && num == 0) {
            swal("注意", "请先进行搜索或勾选商品再使用此功能", "warning");
            return;
        }
        var gids = '';
        if (num != 0) {
            for (var i = 0; i < num; i++) {
                var gid = $('.selected').eq(i).attr('gid');
                gids += 'ids[]=' + gid + '&';
            }
        }

        var url = '{{route('admin.goods.getExportData')}}';
        var type = $(this).data('type');

        if (param == '') {
            url = url + '?type=' + type + '&' + gids;
        } else {
            url = url + '?' + param + '&type=' + type + '&' + gids;
        }

        $(this).data('link', url);

        if (modalUrl) {
            var $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, '')));
            $target.modal('show');
            $target.html('').load(modalUrl, function () {

            });
        }
    });


    /**
     * 批量修改商品标题
     */
    $(document).on('click.modal.data-api', '[data-toggle="modal-modify-title"]', function (e) {
        var $this = $(this),
                href = $this.attr('href'),
                modalUrl = $(this).data('link');

        var num = $('.selected').length;

        if (num == 0) {
            swal("注意", "请先勾选商品再使用此功能", "warning");
            return;
        }
        var gids = '';

        for (var i = 0; i < num; i++) {
            var gid = $('.selected').eq(i).attr('gid');
            gids += 'ids[]=' + gid + '&';
        }

        modalUrl = modalUrl + '?' + gids;
        $(this).data('url', modalUrl);

        var $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, '')));
        $target.modal('show');
        $target.html('').load(modalUrl, function () {

        });

    });
</script>