function loadStat(){
    var loading = WST.load({msg:WST.lang('loading')});
	$.post(WST.U('shop/reports/getStatSales'),WST.getParams('.j-ipt'),function(data,textStatus){
	    layer.close(loading);
	    var json = WST.toJson(data);
	    var myChart = echarts.init(document.getElementById('main'));
	    myChart.clear();
	    $('#mainTable').addClass('hide');
	    if(json.status=='1' && json.data){
			var option = {
			    tooltip : {
			        trigger: 'axis'
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
			    calculable : true,
			    xAxis : [
			        {
			            type : 'category',
			            data : json.data.days
			        }
			    ],
			    yAxis : [
			        {
			            type : 'value'
			        }
			    ],
			    series : [
			        {
			            name:WST.lang('sales_volume'),
			            type:'line',
			            data:json.data.dayVals
			        }
			    ]
			};
			myChart.setOption(option);
			var gettpl = document.getElementById('stat-tblist').innerHTML;
			laytpl(gettpl).render(json.data.list, function(html){
	       		$('#list-box').html(html);
	       		$('#mainTable').removeClass('hide');
		    });
	    }else{
	    	WST.msg(WST.lnag('data_not_exist'),{icon:5});
	    }
	}); 
}

function toExport(){
    var params = WST.getParams('.j-ipt');
    var box = WST.confirm({content:WST.lang('are_you_sure_you_want_to_export_the_statistics'),yes:function(){
        layer.close(box);
        location.href=WST.U('shop/reports/toExportStatSales',params);
    }});
}