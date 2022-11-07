var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('shop_number'), name:'shopSn', width: 130},
            {title:WST.lang('brand_apply_shop_name'), name:'shopName' ,width:100},
            {title:WST.lang('owner_name'), name:'shopkeeper', width: 130},
            {title:WST.lang('shopkeeper_contact_number'), name:'telephone' ,width:100},
            {title:WST.lang('number_of_orders_to_be_settled'), name:'noSettledOrderNum' ,width:60},
            {title:WST.lang('amount_to_be_settled'), name:'waitSettlMoney' ,width:40, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('commission_to_be_settled'), name:'noSettledOrderFee' ,width:40, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('op'), name:'' ,width:120, align:'center', renderer: function(val,item,rowIndex){
                var h = "<span id='s_"+item['shopId']+"' dataval='"+item['shopName']+"'></span><a class='btn btn-blue' href='javascript:toView(" + item['shopId'] + ")'><i class='fa fa-search'></i>"+WST.lang('order_list')+"</a>&nbsp;&nbsp;";
	            return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-163,indexCol: true,indexColWidth:50,  indexColWidth:50,cols: cols,method:'POST',multiSelect:true,
        url: WST.U('admin/settlements/pageShopQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    $('#headTip').WSTTips({width:200,height:35,callback:function(v){
         var diff = v?163:135;
         mmg.resize({height:h-diff})
    }});
    loadShopGrid(p);
}
function toView(id){
   location.href=WST.U('admin/settlements/toOrders','id='+id);
}
function initOrderGrid(id,p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_goodsappraises_order_no'), name:'orderNo', width: 130},
            {title:WST.lang('label_orderrefunds_payment_type'), name:'payTypeName' ,width:100},
            {title:WST.lang('label_order_goods_money'), name:'goodsMoney', width: 130},
            {title:WST.lang('label_order_delivery_money'), name:'deliverMoney' ,width:100, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('amount_to_be_settled'), name:'waitSettlMoney' ,width:40, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('label_orderrefunds_total_money'), name:'totalMoney' ,width:60, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('amount_actually_paid'), name:'realTotalMoney' ,width:40, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('refunded_amount'), name:'refundedPayMoney' ,width:40, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+(parseFloat(val)+parseFloat(item['refundedScoreMoney']));
            }},
            {title:WST.lang('conversion_amount_of_invalid_points'), name:'refundedGetScoreMoney' ,width:100, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+parseFloat(item['refundedGetScoreMoney']);
            }},
            {title:WST.lang('commission'), name:'commissionFee' ,width:40, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('label_order_time'), name:'createTime' ,width:120, align:'center'}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-90,indexCol: true, indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/settlements/pageShopOrderQuery','id='+id), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadOrderGrid(p);
}
function loadShopGrid(p){
    p=(p<=1)?1:p;
	var areaIdPath = WST.ITGetAllAreaVals('areaId1','j-areas').join('_');
	mmg.load({page:p,shopName:$('#shopName').val(),areaIdPath:areaIdPath});
}
function loadOrderGrid(p){
	var id = $('#id').val();
    p=(p<=1)?1:p;
	mmg.load({page:p,orderNo:$('#orderNo').val(),payType:$('#payType').val(),id:id});
}
