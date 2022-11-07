function loadStat(){
    var loading = WST.load({msg:WST.lang('getting_data')});
	$.post(WST.U('supplier/reports/getStatOrders'),WST.getParams('.j-ipt'),function(data,textStatus){
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
			        data:[WST.lang('label_report_wait_pay_order'),WST.lang('label_report_cancel_order'),WST.lang('label_report_reject_order'),WST.lang('label_report_order'),WST.lang('label_total_order')]
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
			            name:WST.lang('label_report_wait_pay_order'),
			            type:'line',
			            stack: WST.lang('type'),
			            data:json.data['-2']
			        },
			        {
			            name:WST.lang('label_report_cancel_order'),
			            type:'line',
			            stack: WST.lang('type'),
			            data:json.data['-1']
			        },
			        {
			            name:WST.lang('label_report_reject_order'),
			            type:'line',
			            stack: WST.lang('type'),
			            data:json.data['-3']
			        },
			        {
			            name:WST.lang('label_report_order'),
			            type:'line',
			            stack: WST.lang('type'),
			            data:json.data['1']
			        },
			        {
			            name:WST.lang('label_total_order'),
			            type:'line',
			            data:json.data['total']
			        },
			        {
			            name:WST.lang('order_type'),
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
			                {value:json.data.map['-1'], name:WST.lang('label_report_cancel_order')},
			                {value:json.data.map['-3'], name:WST.lang('label_report_reject_order')},
			                {value:json.data.map['1'], name:WST.lang('label_report_order')},
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
	    	WST.msg(WST.lang('no_query_data'),{icon:5});
	    }
	});
}

function toExport(){
    var params = WST.getParams('.j-ipt');
    var box = WST.confirm({content:WST.lang("confirm_export_data"),yes:function(){
        layer.close(box);
        location.href=WST.U('supplier/reports/toExportStatOrders',params);
    }});
}
