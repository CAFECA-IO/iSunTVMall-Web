var mmg1,mmg2,mmg3,mmg4,mmg5,mmg6,mmg7,mmg8,mmg9,mmg10,mmg,h;
function initTab(p){
	var element = layui.element;
	var isInit1 = isInit2 = isInit3 = isInit4 = isInit5 = isInit6 = isInit7 = isInit8 = isInit9 = isInit10 =  false;
	element.on('tab(msgTab)', function(data){
        var tabId = $(this).attr("id");
        console.log(tabId);
        if(tabId=="users"){//用户资金
            if(!isInit1){
               isInit1 = true;
               userGridInit(p);
            }else{
               loadUserGrid(p);
            }
        }else if(tabId=="shops"){//商家资金
            if(!isInit2){
               isInit2 = true;
               shopGridInit(p);
            }else{
               loadShopGrid(p);
            }
        }else if(tabId=="suppliers"){//供货商资金
            if(!isInit3){
                isInit3 = true;
                supplierGridInit(p);
            }else{
                loadSupplierGrid(p);
            }
        }else if(tabId=="rechangeMoney"){//充值记录
            if(!isInit4){
                isInit4 = true;
                rechangeGridInit(p);
            }else{
                loadRechangeGrid(p);
            }
        }else if(tabId=="renewMoney"){//年费记录
            if(!isInit5){
                isInit5 = true;
                renewGridInit(p);
            }else{
                loadRenewGrid(p);
            }
        }else if(tabId=="refundMoney"){//退款记录
            if(!isInit6){
                isInit6 = true;
                refundGridInit(p);
            }else{
                loadRefundGrid(p);
            }
        }else if(tabId=="cashDraw"){//提现记录
            if(!isInit7){
                isInit7 = true;
                cashDrawGridInit(p);
            }else{
                loadCashDrawGrid(p);
            }
        }else if(tabId=="moneyList"){//资金流水
            if(!isInit8){
                isInit8 = true;
                moneyGridInit(p);
            }else{
                loadMoneyGrid(p);
            }
        }else if(tabId=="scoreList"){//积分流水
            if(!isInit9){
                isInit9 = true;
                scoreGridInit(p);
            }else{
                loadScoreGrid(p);
            }
        }else if(tabId=="commissionList"){//订单佣金
            if(!isInit10){
                isInit10 = true;
                commissionGridInit(p);
            }else{
                loadCommissionGrid(p);
            }
        }
        
	});
}
function phaseSummary(type,flag){
    if(flag==1)var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/logmoneys/phaseSummary'),{type:type},function(data,textStatus){
        if(flag==1)layer.close(loading);
        var json = WST.toAdminJson(data);
        if(json.status=='1'){
            $("#s_rechangeMoney").html(json.data.rechangeMoney);
            $("#s_giveMoney").html(json.data.giveMoney);
            $("#s_renewMoney").html(json.data.renewMoney);
            $("#s_cashDraw").html(json.data.cashDraw);
            $("#s_refundMoney").html(json.data.refundMoney);
            $("#s_giveScore").html(json.data.giveScore);
            $("#s_exchangeScore").html(json.data.exchangeScore);
            $("#s_commission").html(json.data.commission);
        }else{
            WST.msg(json.msg,{icon:2});
        }
    });
}
//用户资金
function userGridInit(p){
	h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_finance_account'), name:'loginName', width: 50,sortable: true},
            {title:WST.lang('label_finance_account_name'), name:'userName' ,width:80,sortable: true},
            {title:WST.lang('label_finance_last_money'), name:'userMoney' ,width:200,sortable: true,renderer: function (rowdata, rowindex, value){
	        	return WST.lang('currency_symbol')+rowindex['userMoney'];
	        }},
            {title:WST.lang('label_finance_block_money'), name:'lockMoney' ,width:70,sortable: true,renderer: function (rowdata, rowindex, value){
	        	return WST.lang('currency_symbol')+rowindex['lockMoney'];
	        }},
            {title:WST.lang('label_finance_charge_gift_money'), name:'rechargeMoney' ,width:70,sortable: true,renderer: function (rowdata, rowindex, value){
                return WST.lang('currency_symbol')+rowindex['rechargeMoney'];
            }}
            ];
 
    mmg1 = $('.mmg1').mmGrid({
        height: h-402,
        indexCol: true,
        indexColWidth:50, 
        cols: cols,
        method:'POST',
        url: WST.U('admin/logmoneys/pageQueryByUser'), 
        fullWidthRows:true, 
        autoLoad: false,
        remoteSort:true ,
        sortName: 'userMoney',
        sortStatus: 'desc',
        plugins: [
            $('#pg1').mmPaginator({})
        ]
    });
    loadUserGrid(p);
}
function loadUserGrid(p){
    p=(p<=1)?1:p;
    mmg1.load({page:p,key:$('#key1').val()});
}
//商家资金
function shopGridInit(p){
	h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_finance_account'), name:'loginName', width: 50},
            {title:WST.lang('label_finance_user_type2'), name:'shopName' ,width:80},
            {title:WST.lang('label_finance_last_money'), name:'shopMoney' ,width:200,renderer: function (rowdata, rowindex, value){
	        	return WST.lang('currency_symbol')+rowindex['shopMoney'];
	        }},
            {title:WST.lang('label_finance_block_money'), name:'lockMoney' ,width:70,renderer: function (rowdata, rowindex, value){
	        	return WST.lang('currency_symbol')+rowindex['lockMoney'];
	        }},
            {title:WST.lang('label_finance_charge_gift_money'), name:'rechargeMoney' ,width:70,sortable: true,renderer: function (rowdata, rowindex, value){
                return WST.lang('currency_symbol')+rowindex['rechargeMoney'];
            }}
            ];
 
    mmg2 = $('.mmg2').mmGrid({height: h-402,indexCol: true,indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/logmoneys/pageQueryByShop'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg2').mmPaginator({})
        ]
    });
    loadShopGrid(p);
}
function loadShopGrid(p){
    p=(p<=1)?1:p;
    mmg2.load({page:p,key:$('#key2').val()});
}

//供货商资金
function supplierGridInit(p){
    h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_finance_account'), name:'loginName', width: 50},
            {title:WST.lang('label_finance_user_type3'), name:'supplierName' ,width:80},
            {title:WST.lang('label_finance_last_money'), name:'supplierMoney' ,width:200,renderer: function (rowdata, rowindex, value){
                return WST.lang('currency_symbol')+rowindex['supplierMoney'];
            }},
            {title:WST.lang('label_finance_block_money'), name:'lockMoney' ,width:70,renderer: function (rowdata, rowindex, value){
                return WST.lang('currency_symbol')+rowindex['lockMoney'];
            }},
            {title:WST.lang('label_finance_charge_gift_money'), name:'rechargeMoney' ,width:70,sortable: true,renderer: function (rowdata, rowindex, value){
                return WST.lang('currency_symbol')+rowindex['rechargeMoney'];
            }}
            ];
 
    mmg3 = $('.mmg3').mmGrid({height: h-402,indexCol: true,indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/logmoneys/pageQueryBySupplier'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg3').mmPaginator({})
        ]
    });
    loadSupplierGrid(p);
}
function loadSupplierGrid(p){
    p=(p<=1)?1:p;
    mmg3.load({page:p,key:$('#key3').val()});
}

//充值记录
function rechangeGridInit(p){
    var h = WST.pageHeight();
    var laydate = layui.laydate;
    laydate.render({
        elem: '#startDate4'
    });
    laydate.render({
        elem: '#endDate4'
    });
    var cols = [
            {title:WST.lang('label_finance_money_src'), name:'dataSrc', width: 30},
            {title:WST.lang('label_finance_money_target'), name:'loginName', width: 120},
            {title:WST.lang('label_finance_money'), name:'money' ,width:20,renderer: function (rowdata, rowindex, value){
                $("#totalRechangeMoney").html(rowindex['totalRechangeMoney']);
                if(rowindex['moneyType']==1){
                    return '<font color="red">+'+WST.lang('currency_symbol')+rowindex['money']+'</font>';
                }else{
                    return '<font color="green">-'+WST.lang('currency_symbol')+rowindex['money']+'</font>';
                }
            }},
            {title:WST.lang('label_finance_money_txt'), name:'remark',width:370},
            {title:WST.lang('label_finance_money_outer_no'), name:'tradeNo',width:120},
            {title:WST.lang('label_finance_date'), name:'createTime' ,width:60}
            ];
 
    mmg4 = $('.mmg4').mmGrid({height: h-402,indexCol: true,indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/logmoneys/rechangepageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg4').mmPaginator({})
        ]
    });
    loadRechangeGrid(p);
}

function loadRechangeGrid(p){
    p=(p<=1)?1:p;
    mmg4.load({page:p,key:$('#key4').val(),type:$('#type4').val(),startDate:$('#startDate4').val(),endDate:$('#endDate4').val()});
}


//年费记录
function renewGridInit(p){
    var h = WST.pageHeight();
    var laydate = layui.laydate;
    laydate.render({
        elem: '#startDate5'
    });
    laydate.render({
        elem: '#endDate5'
    });
    var cols = [
            {title:WST.lang('label_finance_shop_name'), name:'shopName' ,width:60,renderer:function(val,item,rowIndex){
                $("#totalRenewMoney").html(item.totalRenewMoney);
                return item['shopName'];
            }},
            {title:WST.lang('label_finance_payment_target'), name:'type', width: 130,sortable:true, renderer:function(val,item,rowIndex){
                return (val==3)?WST.lang('label_finance_user_type3'):WST.lang('label_finance_user_type2');
            }},
            {title:WST.lang('label_finance_payment_desc'), name:'remark' ,width:200,renderer: function (rowdata, item, value){
                    return (item['isRefund']==1)?item['remark']+WST.lang('label_finance_has_refund'):item['remark'];
            }},
            {title:WST.lang('label_finance_year_money'), name:'money' ,width:60,sortable: true,renderer: function (rowdata, item, value){
                    return WST.lang('currency_symbol')+item['money'];
                }},
            {title:WST.lang('label_finance_money_outer_no'), name:'', width: 120,renderer:function(val,item,rowIndex){
                    return WST.blank(item['tradeNo'],'-');
            }},
            {title:WST.lang('label_finance_payment_time'), name:'createTime',width:60},
            {title:WST.lang('start_date'), name:'startDate',width:60},
            {title:WST.lang('end_date'), name:'endDate',width:60}
        ];
 
    mmg5 = $('.mmg5').mmGrid({height: h-402,indexCol: true,indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/shops/statRenewMoneyByPage'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg5').mmPaginator({})
        ]
    });
    loadRenewGrid(p);
}

function loadRenewGrid(p){
    p=(p<=1)?1:p;
    mmg5.load({page:p,type:$('#type5').val(),key:$('#key5').val(),startDate:$('#startDate5').val(),endDate:$('#endDate5').val()});
}


//提现记录
function cashDrawGridInit(p){
    var h = WST.pageHeight();
    var laydate = layui.laydate;
    laydate.render({
        elem: '#startDate7'
    });
    laydate.render({
        elem: '#endDate7'
    });
    var cols = [
            {title:WST.lang('label_cashdraws_no'), name:'cashNo', width: 90,sortable: true},
            {title:WST.lang('label_cashdraws_user_name'), name:'loginName' ,width:100, renderer:function(val,item,rowIndex){
                $("#totalCashDrawMoney").html(item.totalCashDrawMoney);
                $('#totalCashDrawCommission').html(item.totalCashDrawCommission);
                if(item['targetType']==1){
                    return WST.blank(item['userName'])+"("+item['loginName']+")";
                }else{
                    return WST.blank(item['userName'])+"("+item['loginName']+")";
                }
            }},
            {title:WST.lang('label_cashdraws_user_type'), name:'targetTypeName' ,width:40,sortable: true},
            {title:WST.lang('cashdraws_bank'), name:'accTargetName' ,width:60,sortable: true},
            {title:WST.lang('cashdraws_bank_no'), name:'accNo' ,width:90,sortable: true},
            {title:WST.lang('cashdraws_bank_user'), name:'accUser' ,width:40,sortable: true},
            {title:WST.lang('label_cashdraws_money'), name:'money' ,width:30,sortable: true, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('cashdraws_fee'), name:'commission' ,width:30,sortable: true, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('cashdraws_fee_rate'), name:'commissionRate' ,width:30,sortable: true, renderer:function(val,item,rowIndex){
                return val+'%';
            }},
            {title:WST.lang('label_cashdraws_apply_time'), name:'createTime',sortable: true ,width:60}
        ];
 
    mmg7 = $('.mmg7').mmGrid({height: h-402,indexCol: true,indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/Cashdraws/statCashDrawal'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg7').mmPaginator({})
        ]
    });
    loadCashDrawGrid(p);
}

function loadCashDrawGrid(p){
    p=(p<=1)?1:p;
    mmg7.load({page:p,key:$('#key7').val(),type:$('#type7').val(),startDate:$('#startDate7').val(),endDate:$('#endDate7').val()});
}


//资金流水
function moneyGridInit(p){
    var h = WST.pageHeight();
    var laydate = layui.laydate;
    laydate.render({
        elem: '#startDate8'
    });
    laydate.render({
        elem: '#endDate8'
    });
    var cols = [
            {title:WST.lang('label_finance_money_src'), name:'dataSrc', width: 30},
            {title:WST.lang('label_finance_query_plo'), name:'loginName', width: 120},
            {title:WST.lang('label_finance_money'), name:'money' ,width:20,renderer: function (rowdata, rowindex, value){
                if(rowindex['moneyType']==1){
                    return '<font color="red">+'+WST.lang('currency_symbol')+rowindex['money']+'</font>';
                }else{
                    return '<font color="green">-'+WST.lang('currency_symbol')+rowindex['money']+'</font>';
                }
            }},
            {title:WST.lang('label_finance_money_txt'), name:'remark',width:370},
            {title:WST.lang('label_finance_money_outer_no'), name:'tradeNo',width:120},
            {title:WST.lang('label_finance_date'), name:'createTime' ,width:60}
            ];
 
    mmg8 = $('.mmg8').mmGrid({height: h-402,indexCol: true,indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/logmoneys/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg8').mmPaginator({})
        ]
    });
    loadMoneyGrid(p);
}

function loadMoneyGrid(p){
    p=(p<=1)?1:p;
    mmg8.load({page:p,key:$('#key8').val(),type:$('#type8').val(),startDate:$('#startDate8').val(),endDate:$('#endDate8').val()});
}

//积分流水
function scoreGridInit(p){
    var h = WST.pageHeight();
    var laydate = layui.laydate;
    laydate.render({
        elem: '#startDate9'
    });
    laydate.render({
        elem: '#endDate9'
    });
    var cols = [
            {title:WST.lang('label_finance_money_src'), name:'dataSrc', width: 60},
            {title:WST.lang('label_finance_score_change'), name:'dataSrc',width: 60,renderer: function (val,item,rowIndex){
                $("#totalInScore").html(item.totalInScore);
                $("#totalOutScore").html(item.totalOutScore);
                if(item['scoreType']==1){
                    return '<font color="red">+'+item['score']+'</font>';
                }else{
                    return '<font color="green">-'+item['score']+'</font>';
                }
            }},
            {title:WST.lang('label_finance_money_txt'), name:'dataRemarks',width: 60},
            {title:WST.lang('label_finance_date'), name:'createTime',width: 40}
            ];
 
    mmg9 = $('.mmg9').mmGrid({height: h-402,indexCol: true,indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/Userscores/statPageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg9').mmPaginator({})
        ]
    });
    loadScoreGrid(p);
}

function loadScoreGrid(p){
    p=(p<=1)?1:p;
    mmg9.load({page:p,key:$('#key9').val(),startDate:$('#startDate9').val(),endDate:$('#endDate9').val()});
}


//订单佣金
function commissionGridInit(p){
    var h = WST.pageHeight();
    var laydate = layui.laydate;
    laydate.render({
        elem: '#startDate10'
    });
    laydate.render({
        elem: '#endDate10'
    });
    var cols = [
            {title:WST.lang('label_finance_seltment_no'), name:'settlementNo', width: 130,sortable:true},
            {title:WST.lang('label_finance_seltment_target'), name:'type', width: 130,sortable:true, renderer:function(val,item,rowIndex){
                return (val==3)?WST.lang('label_finance_user_type3'):WST.lang('label_finance_user_type2');
            }},
            {title:WST.lang('label_finance_seltment_apply_shop'), name:'shopName' ,width:100,sortable:true},
            {title:WST.lang('label_finance_seltment'), name:'settlementMoney' ,width:100,sortable:true, renderer:function(val,item,rowIndex){
                $("#totalCommission").html(item.totalCommission);
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('label_finance_back_menoy'), name:'backMoney' ,width:40,sortable:true, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('label_finance_seltment_fee'), name:'commissionFee' ,width:60,sortable:true, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('label_finance_seltment_time'), name:'createTime',sortable:true}
        ];
 
    mmg10 = $('.mmg10').mmGrid({height: h-402,indexCol: true,indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/settlements/statPageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg10').mmPaginator({})
        ]
    });
    loadCommissionGrid(p);
}

function loadCommissionGrid(p){
    p=(p<=1)?1:p;
    mmg10.load({page:p,key:$('#key10').val(),type:$('#type10').val(),startDate:$('#startDate10').val(),endDate:$('#endDate10').val()});
}
