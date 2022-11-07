var myChart;
$(function(){
	var laydate = layui.laydate;
	laydate.render({
	    elem: '#startDate'
	});
	laydate.render({
	    elem: '#endDate'
	});
    myChart = echarts.init(document.getElementById('main'));
    loadStat();
});
function loadStat(){
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	$.post(WST.U('admin/reports/statNewUser'),WST.getParams('.ipt'),function(data,textStatus){
	    layer.close(loading);
	    var json = WST.toAdminJson(data);
	    if(json.status=='1'){
			var option = {
			    tooltip: {
			        trigger: 'axis'
			    },
			    legend: {
			        data:[WST.lang('label_finance_user_type1'),WST.lang('shop')]
			    },
			    toolbox: {
			        show : true,
			        feature : {
			            mark : {show: true},
			            dataView : {show: false, readOnly: false},
			            magicType : {show: true, type: ['line', 'bar']},
			            restore : {show: true},
			            saveAsImage : {show: true}
			        }
			    },
			    xAxis: {
			        type: 'category',
			        boundaryGap: false,
			        data: json.data.days
			    },
			    yAxis: {
			        type: 'value'
			    },
			    series: [
			        {
			            name:WST.lang('label_finance_user_type1'),
			            type:'line',
			            data:json.data.u0
			        },
			        {
			            name:WST.lang('shop'),
			            type:'line',
			            data:json.data.u1
			        }
			    ]
			};
                    
			myChart.setOption(option);
	    }
	});  
}