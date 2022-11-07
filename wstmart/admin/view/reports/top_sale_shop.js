var mmg;
function initSaleGrid(){
    var laydate = layui.laydate;
    laydate.render({
        elem: '#startDate'
    });
    laydate.render({
        elem: '#endDate'
    });
    var h = WST.pageHeight();
    var cols = [
            {title:'&nbsp;', name:'shopImg', width: 30, renderer:function(val,item,rowIndex){
                return "<span class='weixin'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['shopImg']
            	+"'><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['shopImg']+"'></span></span>";
            }},
            {title:WST.lang('shop'), name:'shopName', width: 130},
            {title:WST.lang('sales_volume'), name:'totalMoney', width: 130, renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
            {title:WST.lang('total_amount_of_online_payment'), name:'onLinePayMoney', width: 130, renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
            {title:WST.lang('real_income_of_online_payment'), name:'onLinePayTrueMoney', width: 130, renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
            {title:WST.lang('cash_on_delivery_actual_revenue'), name:'offLinePayMoney', width: 130, renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
            {title:WST.lang('number_of_orders'), name:'orderNum', width: 50}
            ];
 
    mmg = $('.mmg').mmGrid({height: (h-139),indexCol: true, indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/reports/topShopSalesByPage',WST.getParams('.ipt')), fullWidthRows: true, autoLoad: true,
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
        location.href=WST.U('admin/reports/toExportShopSales',params);
    }});
}