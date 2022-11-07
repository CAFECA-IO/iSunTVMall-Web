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
            {title:'&nbsp;', name:'goodsName', width: 20, renderer: function(val,item,rowIndex){
            	var thumb = item['goodsImg'];
	        	thumb = thumb.replace('.','_thumb.');
                return "<span class='weixin'><a target='_blank' href='"+WST.U("home/goods/detail","goodsId="+item['goodsId'])+"'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb
            	+"'><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></span>";
            }},
            {title:WST.lang('label_goods_name'), name:'goodsName', width: 430, renderer: function(val,item,rowIndex){
                return "<a style='color:#666' target='_blank' href='"+WST.U("home/goods/detail","goodsId="+item['goodsId'])+"'>"+item['goodsName']+"</a>";
            }},
            {title:WST.lang('label_goods_no'), name:'goodsSn', width: 80},
            {title:WST.lang('label_goods_shop'), name:'shopName', width: 150},
            {title:WST.lang('label_goods_sale_num'), name:'goodsNum' , width: 20}
            ];
 
    mmg = $('.mmg').mmGrid({height: (h-139),indexCol: true,indexColWidth:50,  cols: cols,method:'POST',
        url: WST.U('admin/reports/topSaleGoodsByPage',WST.getParams('.ipt')), fullWidthRows: true, autoLoad: true,
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
        location.href=WST.U('admin/reports/toExportSaleGoods',params);
    }});
}