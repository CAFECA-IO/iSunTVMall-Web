var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('supp_settlement_sn'), name:'supplierSn', width: 130},
            {title:WST.lang('supp_settlement_name'), name:'supplierName' ,width:100},
            {title:WST.lang('label_supp_settlement_keeper'), name:'supplierkeeper', width: 130},
            {title:WST.lang('label_supp_settlement_keeper_phone'), name:'telephone' ,width:100},
            {title:WST.lang('label_supp_settlement_no_settle_order_num'), name:'noSettledOrderNum' ,width:60},
            {title:WST.lang('label_supp_settlement_wait_settle_money'), name:'waitSettlMoney' ,width:40, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('label_supp_settlement_no_settle_order_fee'), name:'noSettledOrderFee' ,width:40, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('op'), name:'' ,width:120, align:'center', renderer: function(val,item,rowIndex){
                var h = "<span id='s_"+item['supplierId']+"' dataval='"+item['supplierName']+"'></span><a class='btn btn-blue' href='javascript:toView(" + item['supplierId'] + ")'><i class='fa fa-search'></i>"+WST.lang('supp_settlement_order_list')+"</a>&nbsp;&nbsp;";
	            return h;
            }}
            ];

    mmg = $('.mmg').mmGrid({height: h-163,indexCol: true,indexColWidth:50,cols: cols,method:'POST',multiSelect:true,
        url: WST.U('admin/suppliersettlements/pageSupplierQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    $('#headTip').WSTTips({width:200,height:35,callback:function(v){
         var diff = v?163:135;
         mmg.resize({height:h-diff})
    }});
    loadSupplierGrid(p);
}
function toView(id){
   location.href=WST.U('admin/suppliersettlements/toOrders','id='+id);
}
function initOrderGrid(id,p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_supp_settlement_order_no'), name:'orderNo', width: 130},
            {title:WST.lang('supp_settlement_pay_type'), name:'payTypeName' ,width:100},
            {title:WST.lang('label_supp_settlement_goods_money'), name:'goodsMoney', width: 130},
            {title:WST.lang('label_supp_settlement_deliver_money'), name:'deliverMoney' ,width:100, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('label_supp_settlement_wait_settle_money'), name:'waitSettlMoney' ,width:40, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('label_supp_settlement_total_money'), name:'totalMoney' ,width:60, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('label_supp_settlement_real_total_money'), name:'realTotalMoney' ,width:40, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('label_supp_settlement_refund_pay_money'), name:'refundedPayMoney' ,width:40, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+(parseFloat(val));
            }},
            {title:WST.lang('label_supp_settlement_order_commission_fee'), name:'commissionFee' ,width:40, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('label_supp_settlement_order_create_time'), name:'createTime' ,width:120, align:'center'}
            ];

    mmg = $('.mmg').mmGrid({height: h-90,indexCol: true, indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/suppliersettlements/pageSupplierOrderQuery','id='+id), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadOrderGrid(p);
}
function loadSupplierGrid(p){
    p=(p<=1)?1:p;
	var areaIdPath = WST.ITGetAllAreaVals('areaId1','j-areas').join('_');
	mmg.load({page:p,supplierName:$('#supplierName').val(),areaIdPath:areaIdPath});
}
function loadOrderGrid(p){
	var id = $('#id').val();
    p=(p<=1)?1:p;
	mmg.load({page:p,orderNo:$('#orderNo').val(),payType:$('#payType').val(),id:id});
}
