

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
    location.href=WST.U('supplier/settlements/view','id='+val);
}
function getQueryPage(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('label_settlement_no'), name:'settlementNo', width: 100},
        {title:WST.lang('type'), name:'', width: 30,renderer:function(val,item,rowIndex){
                if(item['settlementType']==1){
                    return WST.lang('settlement_type1');
                }else{
                    return WST.lang('settlement_type2');
                }
            }},
        {title:WST.lang('label_settlement_money'), name:'settlementMoney', width: 60,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('label_settlement_commission_fee'), name:'commissionFee', width: 60,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('label_settlement_back_money'), name:'backMoney', width: 60,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('create_time'), name:'createTime', width: 120},
        {title:WST.lang('label_settlement_settle_status'), name:'', width: 50,renderer:function(val,item,rowIndex){
                if(item['settlementStatus']==1){
                    return WST.lang('yes_settle');
                }else{
                    return WST.lang('no_settle');
                }
            }},
        {title:WST.lang('label_settlement_time'), name:'', width: 100,renderer:function(val,item,rowIndex){
                return WST.blank(item['settlementTime'],'-');
            }},
        {title:WST.lang('remark'), name:'', width: 200,renderer:function(val,item,rowIndex){
                return WST.blank(item['remarks'],'-');
            }},
    ];

    mmg1 = $('.mmg1').mmGrid({height: h-122,indexCol: true, cols: cols,method:'POST',nowrap:true,
        url: WST.U('supplier/settlements/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg1').mmPaginator({})
        ]
    });
    loadGrid(p);
}

function getUnSettledOrderPage(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('label_order_no'), name:'orderNo', width: 200},
        {title:WST.lang('label_pay_time'), name:'createTime', width: 120},
        {title:WST.lang('label_pay_type'), name:'payTypeNames', width: 50},
        {title:WST.lang('label_goods_money'), name:'goodsMoney', width: 100,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('label_deliver_money'), name:'deliverMoney', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('label_total_money'), name:'totalMoney', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('label_real_total_money'), name:'realTotalMoney', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('label_settlement_refunded_pay_money'), name:'refundedPayMoney' ,width:40, renderer:function(val,item,rowIndex){
            return WST.lang('currency_symbol')+(parseFloat(val));
        }},
        {title:WST.lang('label_settlement_pay_commission'), name:'commissionFee', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('label_settlement_expected_time'), name:'afterSaleEndTime', width: 50}
    ];

    mmg2 = $('.mmg2').mmGrid({height: h-121,indexCol: true, cols: cols,method:'POST',
        url: WST.U('supplier/settlements/pageUnSettledQuery'), fullWidthRows: true, autoLoad: false,multiSelect:true,
        plugins: [
            $('#pg2').mmPaginator({})
        ]
    });
    loadUnSettleGrid(p)
}
function getSettleOrderPage(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('label_order_no'), name:'orderNo', width: 100},
        {title:WST.lang('label_pay_type'), name:'payTypeNames', width: 60},
        {title:WST.lang('label_goods_money'), name:'goodsMoney', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('label_deliver_money'), name:'deliverMoney', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('label_total_money'), name:'totalMoney', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('label_real_total_money'), name:'realTotalMoney', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('label_settlement_refunded_pay_money'), name:'refundedPayMoney' ,width:40, renderer:function(val,item,rowIndex){
            return WST.lang('currency_symbol')+(parseFloat(val));
        }},
        {title:WST.lang('label_settlement_pay_commission'), name:'commissionFee', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('label_settlement_no'), name:'settlementNo', width: 100},
        {title:WST.lang('label_settlement_time'), name:'', width: 120,renderer:function(val,item,rowIndex){
                return WST.blank(item['settlementTime'],'-');
            }},
    ];

    mmg3 = $('.mmg3').mmGrid({height: h-122,indexCol: true, cols: cols,method:'POST',
        url: WST.U('supplier/settlements/pageSettledQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg3').mmPaginator({})
        ]
    });
    loadSettleGrid(p)
}
