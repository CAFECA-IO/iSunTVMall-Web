var mmg;
$(function(){
    var laydate = layui.laydate;
    laydate.render({
        elem: '#startDate'
    });
    laydate.render({
        elem: '#endDate'
    });
})
function initGrid(page){
	var p = WST.arrayParams('.j-ipt');
	var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_ordercomplains_order_no'), name:'orderNo', width: 50,sortable:true, renderer:function(val,item,rowIndex){
                var h = "";
	            h += "<img class='order-source2' src='"+WST.conf.ROOT+"/wstmart/admin/view/img/order_source_"+item['orderSrc']+".png'>";	
	            h += "<a style='cursor:pointer' onclick='javascript:showDetail("+ item['orderId'] +");'>"+item['orderNo']+"</a>";
	            return h;
            }},
            {title:WST.lang('label_order_user'), name:'userName', width: 120,sortable:true},
            {title:WST.lang('label_finance_user_type3'), name:'supplierName', width: 90,sortable:true},
            {title:WST.lang('label_order_real_money2'), name:'realTotalMoney', width: 30,sortable:true, renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
            {title:WST.lang('label_orderrefunds_payment_type'), name:'payType' , width: 30,sortable:true},
            {title:WST.lang('label_order_delivery_type'), name:'deliverType', width: 30,sortable:true},
            {title:WST.lang('label_order_order_src'), name:'orderCodeTitle', width: 30,sortable:true},
            {title:WST.lang('label_order_time'), name:'createTime', width: 100,sortable:true},
            {title:WST.lang('label_order_status'), name:'orderStatus', width: 30,sortable:true, renderer:function(val,item,rowIndex){
            	if(item['orderStatus']==-1 || item['orderStatus']==-3){
                    return "<span class='statu-no'><i class='fa fa-ban'></i> "+item.status+"</span>";
                }else if(item['orderStatus']==2){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+item.status+"</span>";
            	}else{
            	 	return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+item.status+"</span>";
            	}
            }},
            {title:WST.lang('op') , width: 150,name:'status', renderer:function(val,item,rowIndex){
            	var h = "";
	            h += "<a class='btn btn-blue' href='javascript:toView(" + item['orderId'] + ")'><i class='fa fa-search'></i>"+WST.lang('view')+"</a> ";
                if(item['orderStatus']!=-1 && item['orderStatus']!=2)h += "<a class='btn btn-blue' href='javascript:changeOrderStatus(" + item['orderId'] + ")'><i class='fa fa-exclamation-triangle'></i>"+WST.lang('label_order_change_status')+"</a> ";
	            return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: (h-90),indexCol: true,indexColWidth:50, cols: cols,method:'POST',nowrap:true,
        url: WST.U('admin/supplierorders/pageQuery',p.join('&')), fullWidthRows: true, autoLoad: false,remoteSort: true,sortName:'createTime',sortStatus:'desc',
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(page);
}

function toView(id){
	location.href=WST.U('admin/supplierorders/view','id='+id+'&src=orders&p='+WST_CURR_PAGE);
}
function toBack(p,src){
    if(src=='orders'){
        location.href=WST.U('admin/supplierorders/index','p='+p);
    }else{
        location.href=WST.U('admin/supplierorderrefunds/refund','p='+p);
    }
}
function loadGrid(page){
	var p = WST.getParams('.j-ipt');
    page=(page<=1)?1:page;
    p.page = page;
	mmg.load(p);
}
function toExport(){
	var params = {};
	params = WST.getParams('.j-ipt');
	var box = WST.confirm({content:WST.lang('require_order_export_tips'),yes:function(){
		layer.close(box);
		location.href=WST.U('admin/supplierorders/toExport',params);
    }});
}
function showDetail(id){
    parent.showBox({title:WST.lang('label_order_view'),type:2,content:WST.U('admin/supplierorders/view',{id:id,from:1}),area: ['1020px', '500px'],btn:[WST.lang('close')]});
}
function loadMore(){
    var h = WST.pageHeight();
    if($('#moreItem').hasClass('hide')){
        $('#moreItem').removeClass('hide');
        mmg.resize({height:h-119});
    }else{
        $('#moreItem').addClass('hide');
        mmg.resize({height:h-89});
    }
}

function changeOrderStatus(id){
    location.href=WST.U('admin/supplierorders/changeOrderStatus','id='+id+'&p='+WST_CURR_PAGE);
}
function initChange(){
   var form = layui.form;
   form.on('radio(orderStatus)', function(data){
      if(data.value==0){
          $('.result_-1').hide();
      }else{
          $('.result_0').hide();
      }
      $('.result_'+data.value).show();
   });
}
function changeOrder(orderId,p){
   var params = {}
   params.orderId = orderId;
   params.orderStatus = $('input[name="orderStatus"]:checked').val();
   if(params.orderStatus==-1){
       params.realTotalMoney = $('#realTotalMoney_0').val();
   }else if(params.orderStatus==0){
       params.payFrom = $('#payFrom_0').val();
       params.realTotalMoney = $('#realTotalMoney_0').val();
       params.trade_no = $('#trade_no_0').val();
   }
   var ll = WST.msg(WST.lang('loading'));
   $.post(WST.U('admin/supplierorders/changeOrder'),params,function(data){
        layer.close(ll);
        var json = WST.toAdminJson(data);
        if(json.status>0){
            WST.msg(json.msg, {icon: 1,time:1000},function(){
                if(json.data && json.data.refundId){
                    refund(json.data.refundId,p);
                }else{
                    location.href=WST.U('admin/supplierorders/index','p='+p);
                }
            });
        }else{
            WST.msg(json.msg, {icon: 2});
        }
    });
}
function refund(id,p){
    params ={id:id}
    var ll = WST.msg(WST.lang('label_order_refund_loading'));
    $.post(WST.U('admin/supplierorderrefunds/orderRefund'),params,function(data){
        layer.close(ll);
        var json = WST.toAdminJson(data);
        if(json.status==1){
            WST.msg(json.msg, {icon: 1,time:1000},function(){
                location.href = WST.U('admin/supplierorders/index','p='+p);
            });
        }else{
            WST.msg(json.msg, {icon: 2});
        }
   });
}