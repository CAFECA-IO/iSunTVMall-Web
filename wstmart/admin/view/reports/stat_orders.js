$(function(){
	var laydate = layui.laydate;
	laydate.render({
	    elem: '#startDate'
	});
	laydate.render({
	    elem: '#endDate'
	});
    loadStat();
});
function loadStat(){
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	$.post(WST.U('admin/reports/statOrders'),WST.getParams('.ipt'),function(data,textStatus){
	    layer.close(loading);
	    var json = WST.toAdminJson(data);
	    var myChart = echarts.init(document.getElementById('main'));
	    myChart.clear();
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
			        data:[WST.lang('label_goods_cat_template_pc'),WST.lang('label_goods_cat_template_wechat'),WST.lang('touch_screen_terminal'),WST.lang('product_weapp'),WST.lang('product_app2'),WST.lang('product_app1'),WST.lang('total_orders')]
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
			            name:WST.lang('label_goods_cat_template_pc'),
			            type:'line',
			            stack: WST.lang('label_finance_money_src'),
			            data:json.data['p0']
			        },
			        {
			            name:WST.lang('label_goods_cat_template_wechat'),
			            type:'line',
			            stack: WST.lang('label_finance_money_src'),
			            data:json.data['p1']
			        },
			        {
			            name:WST.lang('touch_screen_terminal'),
			            type:'line',
			            stack: WST.lang('label_finance_money_src'),
			            data:json.data['p2']
			        },
			        {
			            name:WST.lang('product_weapp'),
			            type:'line',
			            stack: WST.lang('label_finance_money_src'),
			            data:json.data['p5']
			        },
			        {
			            name:WST.lang('product_app2'),
			            type:'line',
			            stack: WST.lang('label_finance_money_src'),
			            data:json.data['p3']
			        },
			        {
			            name:WST.lang('product_app1'),
			            type:'line',
			            stack: WST.lang('label_finance_money_src'),
			            data:json.data['p4']
			        },
			        {
			            name:WST.lang('total_orders'),
			            type:'line',
			            data:json.data['total']
			        },

			        {
			            name:WST.lang('sales_source_segmentation'),
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
			                {value:json.data.map.p0, name:WST.lang('label_goods_cat_template_pc')},
			                {value:json.data.map.p1, name:WST.lang('label_goods_cat_template_wechat')},
			                {value:json.data.map.p2, name:WST.lang('touch_screen_terminal')},
			                {value:json.data.map.p5, name:WST.lang('product_weapp')},
			                {value:json.data.map.p3, name:WST.lang('product_app2')},
			                {value:json.data.map.p4, name:WST.lang('product_app1')}
			            ]
			        }
			    ]
			};       
			myChart.setOption(option);
			var gettpl = document.getElementById('stat-tblist').innerHTML;
			console.log(json.data.days);
			layui.laytpl(gettpl).render(json.data, function(html){
	       		$('#list-box').html(html);
	       		$('#mainTable').removeClass('hide');
		    });
	    }else{
	    	WST.msg(WST.lang('there_is_no_relevant_data'));
	    }

	});  
}

function toExport(){
    var params = WST.getParams('.ipt');
    var box = WST.confirm({content:WST.lang('are_you_sure_you_want_to_export_this_data'),yes:function(){
        layer.close(box);
        location.href=WST.U('admin/reports/toExportStatOrders',params);
    }});
}