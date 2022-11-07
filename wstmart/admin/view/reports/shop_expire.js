var mmg;
function initGrid(){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('shop_number'), name:'shopSn', width: 30},
        {title:WST.lang('store_account_number'), name:'loginName',width: 100},
        {title:WST.lang('brand_apply_shop_name'), name:'shopName',width: 100},
        {title:WST.lang('industry'), name:'tradeName',width: 100},
        {title:WST.lang('shop_address'), name:'shopAddress',width:200 },
        {title:WST.lang('due_date'), name:'expireDate' ,width: 100,sortable: true},
            ];
    mmg = $('.mmg').mmGrid({height: (h-87),indexCol: true,indexColWidth:50,  cols: cols,method:'POST',
        url: WST.U('admin/reports/shopExpireByPage',WST.getParams('.ipt')), fullWidthRows: true, autoLoad: true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
}
function loadGrid(){
    var params = WST.getParams('.j-ipt');
    params.areaIdPath = WST.ITGetAllAreaVals('areaId1','j-areas').join('_');
    params.page = 1;
	mmg.load(params);
}
function toolTip(){
    WST.toolTip();
}
function toExport(){
    var params = WST.getParams('.j-ipt');
    params.areaIdPath = WST.ITGetAllAreaVals('areaId1','j-areas').join('_');
    var box = WST.confirm({content:WST.lang('are_you_sure_you_want_to_export_this_data'),yes:function(){
        layer.close(box);
        location.href=WST.U('admin/reports/toExportShopExpire',params);
    }});
}
