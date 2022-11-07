function loadStat(){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('goods'), name:'goodsName', width: 300},
        {title:WST.lang('goods_number'), name:'goodsSn', width: 200},
        {title:WST.lang('sales_volume'), name:'goodsNum', width: 30},
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',
        url: WST.U('shop/reports/getTopSaleGoods'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid();
}
function loadGrid(){
    mmg.load({startDate:$('#startDate').val(),endDate:$('#endDate').val(),page:1});
}

function toExport(){
    var params = WST.getParams('.j-ipt');
    var box = WST.confirm({content:WST.lang('renew_tips6'),yes:function(){
        layer.close(box);
        location.href=WST.U('shop/reports/toExportTopSaleGoods',params);
    }});
}