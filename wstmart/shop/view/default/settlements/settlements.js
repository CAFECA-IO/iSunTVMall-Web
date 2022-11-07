

function loadGrid(p){
    p=(p<=1)?1:p;
    mmg1.load({page:p,settlementNo:$('#settlementNo_0').val(),isFinish:$('#isFinish_0').val()});
}
function loadUnSettleGrid(p){
    p=(p<=1)?1:p;
    mmg2.load({page:p,orderNo:$('#orderNo_1').val()});
}
function loadSettleGrid(p){
    p=(p<=1)?1:p;
    mmg3.load({page:p,settlementNo:$('#settlementNo_2').val(),orderNo:$('#orderNo_2').val(),isFinish:$('#isFinish_2').val()});
}
function view(val){
    location.href=WST.U('shop/settlements/view','id='+val);
}
function getQueryPage(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('settlement_no'), name:'settlementNo', width: 100},
        {title:WST.lang('type'), name:'', width: 30,renderer:function(val,item,rowIndex){
                if(item['settlementType']==1){
                    return WST.lang('timing');
                }else{
                    return WST.lang('manual');
                }
            }},
        {title:WST.lang('settlement_amount'), name:'settlementMoney', width: 60,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('settlement_commission'), name:'commissionFee', width: 60,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('refund_amount'), name:'backMoney', width: 60,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('creation_time'), name:'createTime', width: 120},
        {title:WST.lang('settlement_status'), name:'', width: 50,renderer:function(val,item,rowIndex){
                if(item['settlementStatus']==1){
                    return WST.lang('settled');
                }else{
                    return WST.lang('unsettled');
                }
            }},
        {title:WST.lang('settlement_time'), name:'', width: 100,renderer:function(val,item,rowIndex){
                return WST.blank(item['settlementTime'],'-');
            }},
        {title:WST.lang('remarks'), name:'', width: 200,renderer:function(val,item,rowIndex){
                return WST.blank(item['remarks'],'-');
            }},
    ];

    mmg1 = $('.mmg1').mmGrid({height: h-122,indexCol: true, cols: cols,method:'POST',nowrap:true,
        url: WST.U('shop/settlements/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg1').mmPaginator({})
        ]
    });
    loadGrid(p);
}

function getUnSettledOrderPage(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('order_number'), name:'orderNo', width: 200},
        {title:WST.lang('order_time'), name:'createTime', width: 120},
        {title:WST.lang('payment_method'), name:'payTypeNames', width: 50},
        {title:WST.lang('total_amount_of_goods'), name:'goodsMoney', width: 100,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('freight'), name:'deliverMoney', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('total_order_amount'), name:'totalMoney', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('amount_actually_paid'), name:'realTotalMoney', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('refunded_amount'), name:'refundedPayMoney' ,width:40, renderer:function(val,item,rowIndex){
            return WST.lang('currency_symbol')+(parseFloat(val)+parseFloat(item['refundedScoreMoney']));
        }},
        {title:WST.lang('conversion_amount_of_invalid_points'), name:'refundedGetScoreMoney' ,width:100, renderer:function(val,item,rowIndex){
            return WST.lang('currency_symbol')+parseFloat(item['refundedGetScoreMoney']);
        }},
        {title:WST.lang('commission_payable'), name:'commissionFee', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('estimated_settlement_date'), name:'afterSaleEndTime', width: 50}
    ];

    mmg2 = $('.mmg2').mmGrid({height: h-121,indexCol: true, cols: cols,method:'POST',
        url: WST.U('shop/settlements/pageUnSettledQuery'), fullWidthRows: true, autoLoad: false,multiSelect:true,
        plugins: [
            $('#pg2').mmPaginator({})
        ]
    });
    loadUnSettleGrid(p)
}
function getSettleOrderPage(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('order_number'), name:'orderNo', width: 100},
        {title:WST.lang('payment_method'), name:'payTypeNames', width: 60},
        {title:WST.lang('total_amount_of_goods'), name:'goodsMoney', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('freight'), name:'deliverMoney', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('total_order_amount'), name:'totalMoney', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('amount_actually_paid'), name:'realTotalMoney', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('refunded_amount'), name:'refundedPayMoney' ,width:40, renderer:function(val,item,rowIndex){
            return WST.lang('currency_symbol')+(parseFloat(val)+parseFloat(item['refundedScoreMoney']));
        }},
        {title:WST.lang('conversion_amount_of_invalid_points'), name:'refundedGetScoreMoney' ,width:100, renderer:function(val,item,rowIndex){
            return WST.lang('currency_symbol')+parseFloat(item['refundedGetScoreMoney']);
        }},
        {title:WST.lang('commission_payable'), name:'commissionFee', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('settlement_no'), name:'settlementNo', width: 100},
        {title:WST.lang('settlement_time'), name:'', width: 120,renderer:function(val,item,rowIndex){
                return WST.blank(item['settlementTime'],'-');
            }},
    ];

    mmg3 = $('.mmg3').mmGrid({height: h-122,indexCol: true, cols: cols,method:'POST',
        url: WST.U('shop/settlements/pageSettledQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg3').mmPaginator({})
        ]
    });
    loadSettleGrid(p)
}