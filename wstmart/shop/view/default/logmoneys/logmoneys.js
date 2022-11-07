$(function () {
	$('#tab').TabPanel({tab:0,callback:function(tab){
		switch(tab){
		   case 0:pageQuery(-1);break;
		   case 1:pageQuery(1);break;
		   case 2:pageQuery(0);break;
		}	
	}})
});
function pageQuery(type){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('source/use'), name:'dataSrc', width: 60},
        {title:WST.lang('money'), name:'', width: 50,renderer:function(val,item,rowIndex){
                if(item['moneyType']==1){
                    return "+"+WST.lang('currency_symbol')+item['money'];
                }else{
                    return "-"+WST.lang('currency_symbol')+item['money'];
                }
            }},
        {title:WST.lang('date'), name:'createTime', width: 120},
        {title:WST.lang('external_serial_number'), name:'', width: 120,renderer:function(val,item,rowIndex){
                return WST.blank(item['tradeNo'],'-');
            }},
        {title:WST.lang('remarks'), name:'remark', width: 300,renderer:function(val,item,rowIndex){
            return '<div title="'+val+'">'+val+'</div>';
        }}
    ];

    mmg = $('.mmg').mmGrid({height: h-193,indexCol: true, cols: cols,method:'POST',nowrap:true,
        url: WST.U('shop/logmoneys/pageShopQuery'), fullWidthRows: true, autoLoad: true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
}
function loadGrid(type){
    mmg.load({type:type,page:1});
}