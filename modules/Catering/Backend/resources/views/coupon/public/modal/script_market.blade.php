<script>

    function changeMarketSelect(_self, action) {
	    if (action == 'exclude' || action == 'view_exclude') {
		    var dom = $('#temp_exclude_market');
	    } else {
		    var dom = $('#temp_selected_market');
	    }

	    if ($(_self).hasClass('select')) {

		    var btnVal = $(_self).data('id');
		    var string = dom.val();
		    var ids = string.split(',');
		    var index = ids.indexOf(String(btnVal));

		    if (!!~index) {
			    ids.splice(index, 1);
		    }

		    var str = ids.join(',');
		    $(_self).removeClass('select btn-info').addClass('btn-warning unselect').find('i').removeClass('fa-check').addClass('fa-times');

		    dom.val(str);

	    } else {
		    var btnVal = $(_self).data('id');
		    var str = dom.val() + ',' + btnVal;

		    if (str.substr(0, 1) == ',') str = str.substr(1);

		    $(_self).addClass('select btn-info').removeClass('btn-warning unselect').find('i').addClass('fa-check').removeClass('fa-times');

		    dom.val(str);
	    }
	    console.log(str);
	    paraDiscount.ids = str;
	    console.log(paraDiscount);
    }

    function sendMarketIds(action) {
	    if (action == 'exclude' || action == 'view_exclude') {
		    var string = $('#temp_exclude_market').val();
		    $('#exclude_market').val(string);
	    } else {
		    var string = $('#temp_selected_market').val();
		    $('#selected_market').val(string);
	    }

	    console.log(string);
	    if (string) {
		    var count = string.split(',').length;
	    } else {
		    count = 0
	    }

	    console.log(count);
	    if (action == 'exclude' || action == 'view_exclude') {
		    $('.countExcludeMarket').html(count);
	    } else {
		    $('.countMarket').html(count);
	    }


	    $('#market_modal').modal('hide');
    }
</script>