<script>
    $(function () {
	    var myChart3 = echarts.init(document.getElementById('main-growth'));
	    var option4 = {
		    tooltip: {
			    /*show:false*/
			    trigger: 'axis',
			    formatter: function (params, ticket, callback) {
				    // var htmlStr = '<div>';
				    var htmlStr = '';
				    var color = params.color;

				    for (var i = 0; i < params.length; i++) {
					    var param = params[i];
					    var xName = param.name;//x轴的名称
					    var seriesName = param.seriesName;//图例名称
					    var value = param.value;//y轴值
					    var color = param.color;//图例颜色

					    if (i === 0) {
						    htmlStr += xName + '<br/>';//x轴的名称
					    }
					    htmlStr += '<div>';
					    //为了保证和原来的效果一样，这里自己实现了一个点的效果
					    htmlStr += '<span style="margin-right:5px;display:inline-block;width:6px;height:6px;border-radius:3px;background-color:' + color + ';"></span>';

					    //圆点后面显示的文本
					    htmlStr += value + '人';

					    htmlStr += '</div>';
				    }
				    return htmlStr;


			    },
			    axisPointer: {
				    show: true,
				    type: 'line'
			    }
		    },
		    xAxis: {
			    data: [],
			    axisLine: {
				    lineStyle: {
					    color: '#9B9B9B'
				    }
			    }
		    },
		    yAxis: {
			    name: '人',
			    nameTextStyle: {
				    padding: [0, 60, 0, 0]
			    },
			    /*max:1400,*/
			    splitNumber: 7,
			    splitLine: {
				    lineStyle: {
					    color: '#F3F3F3'
				    }
			    },
			    axisLine: {
				    lineStyle: {
					    color: '#9B9B9B'
				    }
			    }

		    },
		    series: [{
			    type: 'line',
			    data: [],
			    symbol: 'circle',
			    symbolSize: 6
		    }],
		    color: '#F5A623',
		    textStyle: {
			    fontSize: 12,
			    color: '#4A4A4A'
		    }
	    };
	    var option5 = {
		    tooltip: {
			    /*show:false*/
			    trigger: 'axis',
			    formatter: function (params, ticket, callback) {
				    // var htmlStr = '<div>';
				    var htmlStr = '';
				    var color = params.color;

				    for (var i = 0; i < params.length; i++) {
					    var param = params[i];
					    var xName = param.name;//x轴的名称
					    var seriesName = param.seriesName;//图例名称
					    var value = param.value;//y轴值
					    var color = param.color;//图例颜色

					    if (i === 0) {
						    htmlStr += xName + '<br/>';//x轴的名称
					    }
					    htmlStr += '<div>';
					    //为了保证和原来的效果一样，这里自己实现了一个点的效果
					    htmlStr += '<span style="margin-right:5px;display:inline-block;width:6px;height:6px;border-radius:3px;background-color:' + color + ';"></span>';

					    //圆点后面显示的文本
					    htmlStr += value + '人';

					    htmlStr += '</div>';
				    }
				    return htmlStr;


			    },
			    axisPointer: {
				    show: true,
				    type: 'line'
			    }
		    },
		    xAxis: {
			    type: 'category',
			    data: [],
			    axisLine: {
				    lineStyle: {
					    color: '#9B9B9B'
				    }
			    }
		    },
		    yAxis: {
			    type: 'value',
			    name: '人',
			    nameTextStyle: {
				    padding: [0, 60, 0, 0]
			    },
			    /*max:1400,*/
			    splitNumber: 7,
			    splitLine: {
				    lineStyle: {
					    color: '#F3F3F3'
				    }
			    },
			    axisLine: {
				    lineStyle: {
					    color: '#9B9B9B'
				    }
			    }
		    },
		    series: [{
			    type: 'line',
			    data: [],
			    smooth: true,
			    // showSymbol:false
			    symbol: 'circle',
			    symbolSize: 6
		    }],
		    color: '#F5A623',
		    textStyle: {
			    fontSize: 12,
			    color: '#4A4A4A'
		    }
	    };

	    myChart3.setOption(option5);
	    $(window).resize(function () {
		    myChart3.resize();
	    });
	    //点击日按钮
	    $('#Gday').click(function () {
		    myChart3.clear();//清空当前实例，会移除实例中所有的组件和图表
		    myChart3.setOption(option5);
		    $('#Gday').addClass('active');
		    $('#Gmonth').removeClass('active')
	    });
	    //改变图表尺寸，在容器大小发生改变时需要手动调用
	    $(window).resize(function () {
		    myChart3.resize();
	    });

	    //点击月按钮
	    $('#Gmonth').click(function () {
		    myChart3.clear();//清空当前实例，会移除实例中所有的组件和图表
		    myChart3.setOption(option4);
		    $('#Gmonth').addClass('active');
		    $('#Gday').removeClass('active')
	    });
	    //改变图表尺寸，在容器大小发生改变时需要手动调用
	    $(window).resize(function () {
		    myChart3.resize();
	    });

	    // mouseover 事件
	    $('.help-side').mouseenter(function () {
		    $(this).css({
			    'width': '150px',
			    'transition': 'all .5s'
		    });
		    /* $(this).addClass('wid-h');*/
	    });

	    // mouseout 事件
	    $('.help-side').mouseleave(function () {
		    $(this).css({
			    'width': '50px'
		    });
		    /* $(this).removeClass('wid-h');*/
	    });
	    //点击事件
	    $('.help-side').click(function () {
		    if ($('.help-box').hasClass('eshow')) {
			    // mouseover 事件
			    $('.help-side').mouseenter(function () {
				    $(this).css({
					    'width': '150px',
					    'transition': 'all .5s'
				    });
				    /* $(this).addClass('wid-h');*/
			    });

			    // mouseout 事件
			    $('.help-side').mouseleave(function () {
				    $(this).css({
					    'width': '50px'
				    });
				    /* $(this).removeClass('wid-h');*/
			    });
			    $('.help-box').addClass('ehide');
			    $('.help-box').removeClass('eshow');
			    // $('.help-side').addClass('wid-h').removeClass('wid-r');
			    $('.help-box').animate({
				    height: 'hide'
			    });
			    $(this).animate({
				    width: '50px'
			    })

		    } else {
			    $('.help-side').off('mouseenter').off('mouseleave');
			    $('.help-box').addClass('eshow');
			    $('.help-box').removeClass('ehide');
			    $(this).animate({
				    width: '240px',
				    transition: ' all 0.5s'
			    });
			    setTimeout(
				    function () {
					    $('.help-box').animate({
						    height: 'show'
					    });
				    }, 800
			    )

		    }
	    });
	    $('.new-side').mouseenter(function () {
		    $(this).css({
			    'width': '150px',
			    'transition': 'all .5s'
		    });
	    });
	    $('.new-side').mouseleave(function () {
		    $(this).css({
			    'width': '50px'
		    });
	    });

	    var myChart4 = echarts.init(document.getElementById('official_account_growth'));
	    var option6 = {
		    tooltip: {
			    /*show:false*/
			    trigger: 'axis',
			    formatter: function (params, ticket, callback) {
				    // var htmlStr = '<div>';
				    var htmlStr = '';
				    var color = params.color;

				    for (var i = 0; i < params.length; i++) {
					    var param = params[i];
					    var xName = param.name;//x轴的名称
					    var seriesName = param.seriesName;//图例名称
					    var value = param.value;//y轴值
					    var color = param.color;//图例颜色

					    if (i === 0) {
						    htmlStr += xName + '<br/>';//x轴的名称
					    }
					    htmlStr += '<div>';
					    //为了保证和原来的效果一样，这里自己实现了一个点的效果
					    htmlStr += '<span style="margin-right:5px;display:inline-block;width:6px;height:6px;border-radius:3px;background-color:' + color + ';"></span>';

					    //圆点后面显示的文本
					    htmlStr += value + '人';

					    htmlStr += '</div>';
				    }
				    return htmlStr;


			    },
			    axisPointer: {
				    show: true,
				    type: 'line'
			    }
		    },
		    xAxis: {
			    data: [],
			    axisLine: {
				    lineStyle: {
					    color: '#9B9B9B'
				    }
			    }
		    },
		    yAxis: {
			    name: '人',
			    nameTextStyle: {
				    padding: [0, 60, 0, 0]
			    },
			    /*max:1400,*/
			    splitNumber: 7,
			    splitLine: {
				    lineStyle: {
					    color: '#F3F3F3'
				    }
			    },
			    axisLine: {
				    lineStyle: {
					    color: '#9B9B9B'
				    }
			    }

		    },
		    series: [{
			    type: 'line',
			    data: [],
			    symbol: 'circle',
			    symbolSize: 6
		    }],
		    color: '#F5A623',
		    textStyle: {
			    fontSize: 12,
			    color: '#4A4A4A'
		    }
	    };
	    var option7 = {
		    tooltip: {
			    /*show:false*/
			    trigger: 'axis',
			    formatter: function (params, ticket, callback) {
				    // var htmlStr = '<div>';
				    var htmlStr = '';
				    var color = params.color;

				    for (var i = 0; i < params.length; i++) {
					    var param = params[i];
					    var xName = param.name;//x轴的名称
					    var seriesName = param.seriesName;//图例名称
					    var value = param.value;//y轴值
					    var color = param.color;//图例颜色

					    if (i === 0) {
						    htmlStr += xName + '<br/>';//x轴的名称
					    }
					    htmlStr += '<div>';
					    //为了保证和原来的效果一样，这里自己实现了一个点的效果
					    htmlStr += '<span style="margin-right:5px;display:inline-block;width:6px;height:6px;border-radius:3px;background-color:' + color + ';"></span>';

					    //圆点后面显示的文本
					    htmlStr += value + '人';

					    htmlStr += '</div>';
				    }
				    return htmlStr;


			    },
			    axisPointer: {
				    show: true,
				    type: 'line'
			    }
		    },
		    xAxis: {
			    type: 'category',
			    data: [],
			    axisLine: {
				    lineStyle: {
					    color: '#9B9B9B'
				    }
			    }
		    },
		    yAxis: {
			    type: 'value',
			    name: '人',
			    nameTextStyle: {
				    padding: [0, 60, 0, 0]
			    },
			    /*max:1400,*/
			    splitNumber: 7,
			    splitLine: {
				    lineStyle: {
					    color: '#F3F3F3'
				    }
			    },
			    axisLine: {
				    lineStyle: {
					    color: '#9B9B9B'
				    }
			    }
		    },
		    series: [{
			    type: 'line',
			    data: [],
			    smooth: true,
			    // showSymbol:false
			    symbol: 'circle',
			    symbolSize: 6
		    }],
		    color: '#F5A623',
		    textStyle: {
			    fontSize: 12,
			    color: '#4A4A4A'
		    }
	    };
	    myChart4.setOption(option7);
	    $(window).resize(function () {
		    myChart4.resize();
	    });


	    //点击日按钮
	    $('#official_account_day').click(function () {
		    myChart4.clear();//清空当前实例，会移除实例中所有的组件和图表
		    myChart4.setOption(option7);
		    $('#official_account_day').addClass('active');
		    $('#official_account_month').removeClass('active')
	    });
	    //改变图表尺寸，在容器大小发生改变时需要手动调用
	    $(window).resize(function () {
		    myChart4.resize();
	    });

	    //点击月按钮
	    $('#official_account_month').click(function () {
		    myChart4.clear();//清空当前实例，会移除实例中所有的组件和图表
		    myChart4.setOption(option6);
		    $('#official_account_month').addClass('active');
		    $('#official_account_day').removeClass('active')
	    });
	    //改变图表尺寸，在容器大小发生改变时需要手动调用
	    $(window).resize(function () {
		    myChart4.resize();
	    });

	    $.get('{{route('admin.users.data.getMonthData')}}', function (result) {
		    if (result.status) {
			    option5.xAxis.data = result.data.dayList;
			    option5.series[0].data = result.data.daysUserTotal;
			    myChart3.setOption(option5);

			    option4.xAxis.data = result.data.monthList;
			    option4.series[0].data = result.data.monthUserTotal;

			    option7.xAxis.data = result.data.dayList;
			    option7.series[0].data = result.data.daysUserBindTotal;
			    myChart4.setOption(option7);

			    option6.xAxis.data = result.data.monthList;
			    option6.series[0].data = result.data.monthUserBindTotal;
		    }
	    })
    });
</script>