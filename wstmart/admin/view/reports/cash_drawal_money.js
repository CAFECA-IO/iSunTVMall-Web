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
            {title:WST.lang('label_cashdraws_no'), name:'cashNo', width: 90,sortable: true},
            {title:WST.lang('label_cashdraws_user_name'), name:'loginName' ,width:100, renderer:function(val,item,rowIndex){
                $("#totalMoney").html(item.totalMoney);
                if(item['targetType']==1){
                    return WST.blank(item['userName'])+"("+item['loginName']+")";
                }else{
                    return WST.blank(item['userName'])+"("+item['loginName']+")";
                }
            }},
            {title:WST.lang('label_finance_user_type'), name:'targetTypeName' ,width:60,sortable: true},
            {title:WST.lang('cashdraws_bank'), name:'accTargetName' ,width:60,sortable: true},
            {title:WST.lang('bank_card_number'), name:'accNo' ,width:40,sortable: true},
            {title:WST.lang('cashdraws_bank_user'), name:'accUser' ,width:40,sortable: true},
            {title:WST.lang('label_cashdraws_money'), name:'money' ,width:40,sortable: true, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('withdrawal_time'), name:'createTime',sortable: true ,width:60}
        ];
 
    mmg = $('.mmg').mmGrid({height: (h-139),indexCol: true,indexColWidth:50,  cols: cols,method:'POST',
        url: WST.U('admin/reports/cashDrawalMoneyByPage',WST.getParams('.ipt')), fullWidthRows: true, autoLoad: true,
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
        location.href=WST.U('admin/reports/toExportCashDrawalMoney',params);
    }});
}