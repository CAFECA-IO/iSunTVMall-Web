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
            {title:WST.lang('user'), name:'userName', width: 80, renderer: function(val,item,rowIndex){
                $("#totalScore").html(item['totalScore']);
                $("#totalScoreMoney").html(item['totalScoreMoney']);
                
                return val;
            }},
            {title:WST.lang('label_hook_desc'), name:'dataRemarks', width: 150},
            {title:WST.lang('product_fraction'), name:'score', width: 80},
            {title:WST.lang('deduction_amount'), name:'scoreMoney', width: 50},
            {title:WST.lang('date'), name:'createTime' , width: 50}
            ];
 
    mmg = $('.mmg').mmGrid({height: (h-87),indexCol: true,indexColWidth:50,  cols: cols,method:'POST',
        url: WST.U('admin/reports/scoreConsumeByPage',WST.getParams('.ipt')), fullWidthRows: true, autoLoad: true,
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
        location.href=WST.U('admin/reports/toExportScoreConsume',params);
    }});
}