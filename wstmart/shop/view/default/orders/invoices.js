var WST_CURR_PAGE;
var mmg;
$(function(){
    var laydate = layui.laydate;
    laydate.render({
        elem: '#startDate'
    });
    laydate.render({
        elem: '#endDate'
    });
    laydate.render({
        elem: '#startDate2'
    });
    laydate.render({
        elem: '#endDate2'
    });
})

function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('order_number'), name:'orderNo' ,width:140,sortable:true},
        {title:WST.lang('invoice_amount'), name:'realTotalMoney' ,width:200 ,sortable:true},
        {title:WST.lang('invoice_title'), name:'invoiceHead' ,width:200,sortable:true},
        {title:WST.lang('invoice_tax_number'), name:'invoiceCode' ,width:200,sortable:true},
        {title:WST.lang('order_time'), name:'createTime', width: 150,sortable:true}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',checkCol:true,multiSelect:true,nowrap:true,
        url: WST.U('shop/orderinvoices/queryShopInvoicesByPage',{isMakeInvoice:0}), fullWidthRows: true, autoLoad: false,remoteSort: true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function initGrid2(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('order_number'), name:'orderNo' ,width:140,sortable:true},
        {title:WST.lang('invoice_amount'), name:'realTotalMoney' ,width:200 ,sortable:true},
        {title:WST.lang('invoice_title'), name:'invoiceHead' ,width:200,sortable:true},
        {title:WST.lang('invoice_tax_number'), name:'invoiceCode' ,width:200,sortable:true},
        {title:WST.lang('order_time'), name:'createTime', width: 150,sortable:true}
    ];

    mmg = $('.mmg2').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',checkCol:true,multiSelect:true,nowrap:true,
        url: WST.U('shop/orderinvoices/queryShopInvoicesByPage',{isMakeInvoice:1}), fullWidthRows: true, autoLoad: false,remoteSort: true,
        plugins: [
            $('#pg2').mmPaginator({})
        ]
    });
    loadGrid2(p);
}
function loadGrid(page){
    var p = {};
    p.page=(page<=1)?1:page;
    p.startDate = $('#startDate').val();
    p.endDate = $('#endDate').val();
    p.orderNo = $("#orderNo").val();
    mmg.load(p);

}

function loadGrid2(page){
    var p = {};
    p.page=(page<=1)?1:page;
    p.startDate = $('#startDate2').val();
    p.endDate = $('#endDate2').val();
    p.orderNo = $("#orderNo2").val();
    mmg.load(p);
}

//导出发票
function toExport(isMakeInvoice){
    var rows = mmg.selectedRows();
    var ids = [];
    for(var i=0;i<rows.length;i++){
        ids.push(rows[i]['orderId']);
     }
     var params = {};
     params = WST.getParams('.j-ipt');
     params.ids = ids.join(',');
     params.isMakeInvoice = isMakeInvoice;
     var box = WST.confirm({content:WST.lang('export_invoice'),yes:function(){
            layer.close(box);
            location.href=WST.U('shop/orderinvoices/toExport',params);
     }});
}

//批量设置
function toBatchSet(isMakeInvoice){
    var rows = mmg.selectedRows();
    if(rows.length==0){
        WST.msg(WST.lang("please_select_the_order_to_set"),{icon:2});
        return;
    }
    var ids = [];
    for(var i=0;i<rows.length;i++){
        ids.push(rows[i]['orderId']);
    }
    var msg = (isMakeInvoice==1)?WST.lang('are_you_sure_to_set_these_as_invoiced'):WST.lang('are_you_sure_to_set_these_as_not_invoiced');
    var box = WST.confirm({content:msg,yes:function(){
            var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
            $.post(WST.U('shop/orderinvoices/setByBatch'),{ids:ids.join(','),isMakeInvoice:isMakeInvoice},function(data,textStatus){
                layer.close(loading);
                var json = WST.toJson(data);
                if(json.status=='1'){
                    WST.msg(json.msg,{icon:1});
                    layer.close(box);
                    loadGrid(WST_CURR_PAGE);
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            });
        }});
}