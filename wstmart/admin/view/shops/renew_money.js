var mmg;
function initGrid(){
    var laydate = layui.laydate;
    laydate.render({
        elem: '#startDate'
    });
    laydate.render({
        elem: '#endDate'
    });
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('brand_apply_shop_name'), name:'shopName' ,width:60,renderer:function(val,item,rowIndex){
                $("#totalRenewMoney").html(item.totalRenewMoney);
                return item['shopName'];
            }},
            {title:WST.lang('label_finance_payment_desc'), name:'remark' ,width:200,renderer: function (rowdata, item, value){
                    return (item['isRefund']==1)?item['remark']+'('+WST.lang('refunded')+')':item['remark'];
            }},
            {title:WST.lang('label_finance_year_money'), name:'money' ,width:60,sortable: true,renderer: function (rowdata, item, value){
                    return WST.lang('currency_symbol')+item['money'];
                }},
            {title:WST.lang('label_order_outer_no'), name:'', width: 120,renderer:function(val,item,rowIndex){
                    return WST.blank(item['tradeNo'],'-');
            }},
            {title:WST.lang('label_finance_payment_time'), name:'createTime',width:60},
            {title:WST.lang('start_date'), name:'startDate',width:60},
            {title:WST.lang('end_date'), name:'endDate',width:60}
        ];
 
    mmg = $('.mmg').mmGrid({height: (h-87),indexCol: true,indexColWidth:50,  cols: cols,method:'POST',
        url: WST.U('admin/shops/renewMoneyByPage',WST.getParams('.ipt')), fullWidthRows: true, autoLoad: true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });  
}
function loadGrid(){
	var params = WST.getParams('.ipt');
    params.page = 1;
	mmg.load(params);
}
function toolTip(){
    WST.toolTip();
}
function toExport(){
    var params = WST.getParams('.ipt');
    var box = WST.confirm({content:WST.lang('are_you_sure_you_want_to_export_this_data'),yes:function(){
        layer.close(box);
        location.href=WST.U('admin/shops/toExportRenewMoney',params);
    }});
}