var grid;
$(function(){
    var laydate = layui.laydate;
    laydate.render({
        elem: '#startDate'
    });
    laydate.render({
        elem: '#endDate'
    });
    
})
function toView(id,sId){
	location.href=WST.U('admin/orders/view','id='+id+'&serviceId='+sId+'&src=orderrefunds&p='+WST_CURR_PAGE);
}

function initRefundGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_orderrefunds_order_no'), name:'orderNo',sortable: true, renderer: function(val,item,rowIndex){
            	var h = "";
	            h += "<img class='order-source2' src='"+WST.conf.ROOT+"/wstmart/admin/view/img/order_source_"+item['orderSrc']+".png'>";   
                h += "<a style='cursor:pointer' onclick='javascript:showDetail("+ item['orderId'] +");'>"+item['orderNo']+"</a>";
	            return h;
            }},
            {title:WST.lang('label_orderrefunds_apply_user'), name:'loginName',sortable: true},
            {title:WST.lang('label_orderrefunds_shop'), name:'shopName',sortable: true},
            {title:WST.lang('label_orderrefunds_order_src'), name:'orderCodeTitle',width:40,sortable: true,hidden: true},
            {title:WST.lang('label_orderrefunds_delevery'), name:'deliverType',width:40,sortable: true,hidden: true},
            {title:WST.lang('label_orderrefunds_order_real_money'), name:'realTotalMoney', width:40,sortable: true,renderer: function(val,item,rowIndex){
            	return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('label_orderrefunds_refund_money1'), name:'backMoney',width:70,sortable: true, renderer: function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('label_orderrefunds_refund_score1'), name:'useScore',width:40,sortable: true},
            {title:WST.lang('label_orderrefunds_apply_titme'), name:'createTime',sortable: true},
            {title:WST.lang('label_orderrefunds_payment_src'), name:'refundTo',sortable: true},
            {title:WST.lang('label_orderrefunds_refund_type'), name:'isRefund', width:40,sortable: true,renderer: function(val,item,rowIndex){
                if(item['serviceId']>0)return (item['isServiceRefund']==1)?WST.lang('label_orderrefunds_refund_type1'):WST.lang('label_orderrefunds_refund_type0');
            	return (item['isRefund']==1)?WST.lang('label_orderrefunds_refund_type1'):WST.lang('label_orderrefunds_refund_type0');
            }},
            {title:WST.lang('label_orderrefunds_hander_txt1'), name:'refundRemark',hidden: true},
            {title:WST.lang('op'), name:'op' ,width:120, align:'center', renderer: function(val,item,rowIndex){
                var h = '';
	            if((item['serviceId']==0 && item['isRefund']==0) || (item['serviceId']>0 && item['isServiceRefund']==0)){
	            	if(WST.GRANT.TKDD_04)h += "<a class='btn btn-blue' href='javascript:toRefund(" + item['refundId'] + ", "+ item['serviceId'] +")'><i class='fa fa-search'></i>"+WST.lang('label_orderrefunds_hander')+"</a> ";
	            }
	            h += "<a class='btn btn-blue' href='javascript:toView(" + item['orderId'] + ", "+ item['serviceId'] +")'><i class='fa fa-search'></i>"+WST.lang('view')+"</a> ";
	            return h;
	        }}
            ];
 
    mmg = $('.mmg').mmGrid({height: (h-160),indexCol: true, indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/orderrefunds/refundPageQuery'), fullWidthRows: true, autoLoad: false,nowrap:true,
        remoteSort:true ,
        sortName: 'createTime',
        sortStatus: 'desc',
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    $('#headTip').WSTTips({width:200,height:35,callback:function(v){
       var diff = v?160:130;
       mmg.resize({height:h-diff})
    }});
    loadRefundGrid(p);
}
function loadRefundGrid(page){
	var p = WST.getParams('.j-ipt');
	page=(page<=1)?1:page;
	p.page = page;
	mmg.load(p);
}
var w;
function toRefund(id,sId){
	var ll = WST.msg(WST.lang('loading'));
	$.post(WST.U('admin/orderrefunds/toRefund',{id:id,serviceId:sId}),{},function(data){
		layer.close(ll);
		w =WST.open({type: 1,title:WST.lang('label_orderrefunds_refund_title1'),shade: [0.6, '#000'],offset:'50px',border: [0],content:data,area: ['700px', '600px']});
	});
}
function orderRefund(id){
	$('#editFrom').isValid(function(v){
		if(v){
        	var params = {};
        	params.content = $.trim($('#content').val());
        	params.id = id;
        	ll = WST.msg(WST.lang('loading'));
		    $.post(WST.U('admin/orderrefunds/orderRefund'),params,function(data){
		    	layer.close(ll);
		    	var json = WST.toAdminJson(data);
				if(json.status==1){
                    layer.close(w);
					WST.msg(json.msg, {icon: 1,time:2500},function(){
                        loadRefundGrid(WST_CURR_PAGE);
                    });
				}else{
					WST.msg(json.msg, {icon: 2});
				}
		   });
		}
    })
}
function showDetail(id){
    parent.showBox({title:WST.lang('label_orderrefunds_order_view'),type:2,content:WST.U('admin/orders/view',{id:id,from:1}),area: ['1020px', '500px'],btn:[WST.lang('close')]});
}
function toExport(){
	var params = {};
	params = WST.getParams('.j-ipt');
	var box = WST.confirm({content:WST.lang('label_orderrefunds_export_tips'),yes:function(){
		layer.close(box);
		location.href=WST.U('admin/orderrefunds/toExport',params);
    }});
}