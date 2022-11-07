function loadStat(){
    var loading = WST.load({msg:WST.lang('loading')});
	$.post(WST.U('shop/reports/getStatOrders'),WST.getParams('.j-ipt'),function(data,textStatus){
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
			        y: 'top',
			        feature : {
			            mark : {show: true},
			            dataView : {show: false, readOnly: false},
			            magicType : {show: true, type: ['line', 'bar', 'tiled']},
			            restore : {show: true},
			            saveAsImage : {show: true}
			        }
			    },
			    calculable : true,
			    legend: {
			        data:[WST.lang('order_to_be_paid'),WST.lang('cancellation_of_order'),WST.lang('reject_order'),WST.lang('normal_order'),WST.lang('total_order')]
			    },
			    xAxis : [
			        {
			            type : 'category',
			            splitLine : {show : false},
			            data : json.data.days
			        }
			    ],
			    yAxis : [
			        {
			            type : 'value',
			            position: 'right'
			        }
			    ],
			    series : [
			        {
			            name:WST.lang('order_to_be_paid'),
			            type:'line',
			            stack: WST.lang('type'),
			            data:json.data['-2']
			        },
			        {
			            name:WST.lang('cancellation_of_order'),
			            type:'line',
			            stack: WST.lang('type'),
			            data:json.data['-1']
			        },
			        {
			            name:WST.lang('reject_order'),
			            type:'line',
			            stack: WST.lang('type'),
			            data:json.data['-3']
			        },
			        {
			            name:WST.lang('normal_order'),
			            type:'line',
			            stack: WST.lang('type'),
			            data:json.data['1']
			        },
			        {
			            name:WST.lang('total_order'),
			            type:'line',
			            data:json.data['total']
			        },
			        {
			            name:WST.lang('order_type_breakdown'),
			            type:'pie',
			            tooltip : {
			                trigger: 'item',
			                formatter: '{a} <br/>{b} : {c} ({d}%)'
			            },
			            center: [160,130],
			            radius : [0, 50],
			            itemStyle :　{
			                normal : {
			                    labelLine : {
			                        length : 20
			                    }
			                }
			            },
			            data:[
			                {value:json.data.map['-1'], name:WST.lang('cancellation_of_order')},
			                {value:json.data.map['-3'], name:WST.lang('reject_order')},
			                {value:json.data.map['1'], name:WST.lang('normal_order')},
			            ]
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
	    	WST.msg(WST.lang('data_not_exist'),{icon:5});
	    }
	}); 
}

function toExport(){
    var params = WST.getParams('.j-ipt');
    var box = WST.confirm({content:WST.lang('renew_tips6'),yes:function(){
        layer.close(box);
        location.href=WST.U('shop/reports/toExportStatOrders',params);
    }});
}