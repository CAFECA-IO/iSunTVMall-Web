function loadStat(){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('label_goods'), name:'goodsName', width: 300},
        {title:WST.lang('label_goods_sn'), name:'goodsSn', width: 200},
        {title:WST.lang('label_goods_num'), name:'goodsNum', width: 30},
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',
        url: WST.U('supplier/reports/getTopSaleGoods'), fullWidthRows: true, autoLoad: false,
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
    var box = WST.confirm({content:WST.lang("confirm_export_data"),yes:function(){
        layer.close(box);
        location.href=WST.U('supplier/reports/toExportTopSaleGoods',params);
    }});
}
