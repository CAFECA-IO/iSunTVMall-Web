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
        {title:WST.lang('label_order_no'), name:'orderNo' ,width:140,sortable:true},
        {title:WST.lang('label_order_invoice_money'), name:'realTotalMoney' ,width:200 ,sortable:true},
        {title:WST.lang('label_invoice_head'), name:'invoiceHead' ,width:200,sortable:true},
        {title:WST.lang('label_invoice_code'), name:'invoiceCode' ,width:200,sortable:true},
        {title:WST.lang('label_pay_time'), name:'createTime', width: 150,sortable:true}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',checkCol:true,multiSelect:true,nowrap:true,
        url: WST.U('supplier/orderinvoices/querySupplierInvoicesByPage',{isMakeInvoice:0}), fullWidthRows: true, autoLoad: false,remoteSort: true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function initGrid2(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('label_order_no'), name:'orderNo' ,width:140,sortable:true},
        {title:WST.lang('label_order_invoice_money'), name:'realTotalMoney' ,width:200 ,sortable:true},
        {title:WST.lang('label_invoice_head'), name:'invoiceHead' ,width:200,sortable:true},
        {title:WST.lang('label_invoice_code'), name:'invoiceCode' ,width:200,sortable:true},
        {title:WST.lang('label_pay_time'), name:'createTime', width: 150,sortable:true}
    ];

    mmg = $('.mmg2').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',checkCol:true,multiSelect:true,nowrap:true,
        url: WST.U('supplier/orderinvoices/querySupplierInvoicesByPage',{isMakeInvoice:1}), fullWidthRows: true, autoLoad: false,remoteSort: true,
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
     var box = WST.confirm({content:WST.lang("export_invoice_tips"),yes:function(){
            layer.close(box);
            location.href=WST.U('supplier/orderinvoices/toExport',params);
     }});
}

//批量设置
function toBatchSet(isMakeInvoice){
    var rows = mmg.selectedRows();
    if(rows.length==0){
        WST.msg(WST.lang('pls_select_set_order'),{icon:2});
        return;
    }
    var ids = [];
    for(var i=0;i<rows.length;i++){
        ids.push(rows[i]['orderId']);
    }
    var msg = (isMakeInvoice==1)?WST.lang("confirm_set_invoice_tip1"):WST.lang("confirm_set_invoice_tip2");
    var box = WST.confirm({content:msg,yes:function(){
            var loading = WST.msg(WST.lang('submitting_data'), {icon: 16,time:60000});
            $.post(WST.U('supplier/orderinvoices/setByBatch'),{ids:ids.join(','),isMakeInvoice:isMakeInvoice},function(data,textStatus){
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
