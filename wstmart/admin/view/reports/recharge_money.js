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
            {title:WST.lang('label_cashdraws_user_name'), name:'loginName' ,width:100, renderer:function(val,item,rowIndex){
                if(item['targetType']==1){
                    return WST.blank(item['userName'])+"("+item['loginName']+")";
                }else{
                    return WST.blank(item['userName'])+"("+item['loginName']+")";
                }
            }},
            {title:WST.lang('label_finance_user_type'), name:'targetType' ,width:60,renderer:function(val,item,rowIndex){
                $("#totalRechargeMoney").html(item.totalRechargeMoney);
                $("#totalGiveMoney").html(item.totalGiveMoney);
                return (item['targetType']==1)?"【"+WST.lang('label_finance_user_type2')+"】":"【"+WST.lang('label_finance_user_type1')+"】";
            }},
            {title:WST.lang('recharge_description'), name:'remark' ,width:200},
            {title:WST.lang('label_chargeitem_money'), name:'money' ,width:60},
            {title:WST.lang('label_chargeitem_gift_money'), name:'giveMoney' ,width:60},
            {title:WST.lang('recharge_time'), name:'createTime',width:60}
        ];
 
    mmg = $('.mmg').mmGrid({height: (h-87),indexCol: true,indexColWidth:50,  cols: cols,method:'POST',
        url: WST.U('admin/reports/rechargeMoneyByPage',WST.getParams('.ipt')), fullWidthRows: true, autoLoad: true,
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
        location.href=WST.U('admin/reports/toExportRechargeMoney',params);
    }});
}