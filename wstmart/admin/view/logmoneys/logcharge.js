var mmg1,mmg2,mmg3,mmg,h;
function flowGridInit(p){
    var h = WST.pageHeight();
    var laydate = layui.laydate;
    laydate.render({
        elem: '#startDate'
    });
    laydate.render({
        elem: '#endDate'
    });
    var cols = [
            {title:WST.lang('label_logmoney_src'), name:'dataSrc', width: 30},
            {title:WST.lang('label_logmoney_user_shop'), name:'loginName', width: 100},
            {title:WST.lang('label_logmoney_money2'), name:'money' ,width:20,renderer: function (rowdata, rowindex, value){
	        	if(rowindex['moneyType']==1){
                    return '<font color="red">+'+WST.lang('currency_symbol')+rowindex['money']+'</font>';
	        	}else{
                    return '<font color="green">-'+WST.lang('currency_symbol')+rowindex['money']+'</font>';
	        	}
	        }},
            {title:WST.lang('label_logmoney_txt'), name:'remark',width:370},
            {title:WST.lang('label_finance_money_outer_no'), name:'tradeNo',width:120},
            {title:WST.lang('label_finance_date'), name:'createTime' ,width:60}
            ];
 
    mmg3 = $('.mmg3').mmGrid({height: h-90,indexCol: true,indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/logmoneys/pageQueryByCharge'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg3').mmPaginator({})
        ]
    });
    loadFlowGrid(p);
}
function loadFlowGrid(p){
    p=(p<=1)?1:p;
	mmg3.load({page:p,dataSrc:4,key:$('#key3').val(),type:$('#type').val(),startDate:$('#startDate').val(),endDate:$('#endDate').val()});
}