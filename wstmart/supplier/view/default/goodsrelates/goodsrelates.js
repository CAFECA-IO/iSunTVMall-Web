/**删除批量上传的图片**/
var mmg;
var form;


function shopPage(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('label_goodsrelate_shop_img'), name:'shopImg', width: 80, renderer: function(val,item,rowIndex){
                return "<span class='weixin'><a style='color:blue' target='_blank' href='"+WST.U("home/shops/index","shopId="+item['shopId'])+"'><img class='img' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['shopImg']+"'><img class='imged' style='height:200px;width:200px;border:0px; background:#fff' src='"+WST.conf.RESOURCE_PATH+"/"+item['shopImg']+"'></a></span>";
            }},
        {title:WST.lang('label_goodsrelate_shop_name'), name:'shopName', width: 200, renderer: function(val,item,rowIndex){
        	return "<a style='color:#666' target='_blank' href='"+WST.U("home/shops/index","shopId="+item['shopId'])+"'>"+val+"</a> ";

        }},
        {title:WST.lang('label_goodsrelate_shop_sn'), name:'shopSn', width: 100},
        {title:WST.lang('label_goodsrelate_shop_address'), name:'shopAddress', width: 300},
        {title:WST.lang('label_goodsrelate_goods_num'), name:'gnum', width: 80},
        {title:WST.lang('op'), name:'' ,width:200, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                h += "<a class='btn btn-blue' href='"+WST.U("supplier/Suppliergoodscopyrelates/goodsIndex","shopId="+item['shopId']+"&p="+WST_CURR_PAGE)+"'><i class='fa fa-search'></i>"+WST.lang('goodsrelate_view_goods')+"</a> ";
                return h;
            }}
    ];

    mmg = $('.mmg').mmGrid({height: h-150,indexCol: true, cols: cols,method:'POST',
        url: WST.U('supplier/Suppliergoodscopyrelates/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function loadGrid(p){
    p = (p<=1)?1:p;
    mmg.load({shopName:$('#shopName').val(),page:p});
}

function goodsPageQuery(p){
    var h = WST.pageHeight();
    var shopId = $("#shopId").val();
    var cols = [
        {title:WST.lang('label_goods_img'), name:'goodsName', width: 80, renderer: function(val,item,rowIndex){
                return "<span class='weixin'><a style='color:blue' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'><img class='img' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'><img class='imged' style='height:200px;width:200px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></a></span>";
            }},
        {title:WST.lang('label_goods_name'), name:'goodsName', width: 250, renderer: function(val,item,rowIndex){
        	return "<a style='color:#666' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'>"+val+"</a> ";

        }},
        {title:WST.lang('label_goods_sn'), name:'goodsSn', width: 200},
        {title:WST.lang('label_price')+'('+WST.lang('currency_symbol')+')', name:'shopPrice', width: 30},
        {title:WST.lang('label_goods_num'), name:'saleNum', width: 30},
        {title:WST.lang('create_time'), name:'createTime', width: 30}
    ];

    mmg = $('.mmg').mmGrid({height: h-150,indexCol: true, cols: cols,method:'POST',
        url: WST.U('supplier/Suppliergoodscopyrelates/goodsPageQuery','shopId='+shopId), fullWidthRows: true, autoLoad: false,checkCol:true,multiSelect:true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p)

}
function goodsPageLoadGrid(p){
    p = (p<=1)?1:p;
    mmg.load({goodsName:$('#goodsName').val(),page:p});
}



function shopPageQuery(p){
    var h = WST.pageHeight();
    var goodsId = $("#goodsId").val();
    var cols = [
        {title:WST.lang('label_goodsrelate_shop_img'), name:'shopImg', width: 80, renderer: function(val,item,rowIndex){
                return "<span class='weixin'><a style='color:blue' target='_blank' href='"+WST.U("home/shops/index","shopId="+item['shopId'])+"'><img class='img' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['shopImg']+"'><img class='imged' style='height:200px;width:200px;border:0px; background:#fff' src='"+WST.conf.RESOURCE_PATH+"/"+item['shopImg']+"'></a></span>";
            }},
        {title:WST.lang('label_goodsrelate_shop_name'), name:'shopName', width: 200, renderer: function(val,item,rowIndex){
            return "<a style='color:#666' target='_blank' href='"+WST.U("home/shops/index","shopId="+item['shopId'])+"'>"+val+"</a> ";

        }},
        {title:WST.lang('label_goodsrelate_shop_sn'), name:'shopSn', width: 100},
        {title:WST.lang('label_goodsrelate_shop_address'), name:'shopAddress', width: 300},
        {title:WST.lang('label_goods')+WST.lang('create_time'), name:'createTime', width: 120},
    ];

    mmg = $('.mmg').mmGrid({height: h-150,indexCol: true, cols: cols,method:'POST',
        url: WST.U('supplier/Suppliergoodscopyrelates/shopPageQuery','goodsId='+goodsId), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function shopPageLoadGrid(p){
    p = (p<=1)?1:p;
    mmg.load({shopName:$('#shopName').val(),page:p});
}
