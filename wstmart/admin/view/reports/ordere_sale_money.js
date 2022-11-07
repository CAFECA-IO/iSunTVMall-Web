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
            {title:WST.lang('label_goodsappraises_order_no'), name:'orderNo' ,width:80},
            {title:WST.lang('order_user'), name:'userName' ,width:80,renderer:function(val,item,rowIndex){
                $("#totalScoreMoney").html(item.totalScoreMoney);
                $("#totalRealTotalMoney").html(item.totalRealTotalMoney);
                return val;
            }},
            {title:WST.lang('label_orderrefunds_payment_type'), name:'payType' ,width:80},
            {title:WST.lang('label_order_status'), name:'status' ,width:80},
            {title:WST.lang('label_orderrefunds_total_money'), name:'totalMoney' ,width:80},
            {title:WST.lang('label_order_total_score_money2'), name:'scoreMoney' ,width:80},
            {title:WST.lang('order_paid_amount'), name:'realTotalMoney' ,width:80},
            {title:WST.lang('label_order_time'), name:'createTime',width:80}
        ];
 
    mmg = $('.mmg').mmGrid({height: (h-139),indexCol: true,indexColWidth:50,  cols: cols,method:'POST',
        url: WST.U('admin/reports/ordereSaleMoneyByPage',WST.getParams('.ipt')), fullWidthRows: true, autoLoad: true,
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
        location.href=WST.U('admin/reports/toExportOrdereSaleMoney',params);
    }});
}