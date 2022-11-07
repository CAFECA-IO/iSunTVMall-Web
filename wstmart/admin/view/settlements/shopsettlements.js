var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_finance_seltment_no'), name:'settlementNo', width: 130,sortable:true},
            {title:WST.lang('label_finance_seltment_apply_shop'), name:'shopName' ,width:100,sortable:true},
            {title:WST.lang('label_finance_seltment_money'), name:'settlementMoney' ,width:100,sortable:true, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('label_finance_seltment_fee'), name:'commissionFee' ,width:60,sortable:true, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('label_finance_back_menoy'), name:'backMoney' ,width:40,sortable:true, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('label_orderrefunds_apply_titme'), name:'createTime',sortable:true},
            {title:WST.lang('status'), name:'settlementStatus' ,width:60,sortable:true, renderer:function(val,item,rowIndex){
                return (val==1)?"<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('settled')+"&nbsp;</span>":"<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('unsettled')+"&nbsp;</span>";
            }},
            {title:WST.lang('op'), name:'' ,width:120, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
	            h += "<a class='btn btn-blue' href='javascript:toView(" + item['settlementId'] + ")'><i class='fa fa-search'></i>"+WST.lang('view')+"</a>&nbsp;&nbsp;";

	            return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-185,indexCol: true,indexColWidth:50,  cols: cols,method:'POST',
        url: WST.U('admin/settlements/pageQuery'), fullWidthRows: true, autoLoad: false,remoteSort: true,sortName:'createTime',sortStatus:'desc',
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    $('#headTip').WSTTips({width:200,height:35,callback:function(v){
         var diff = v?182:137;
         mmg.resize({height:h-diff})
    }});
    loadGrid(p);
}

function toView(id){
	location.href=WST.U('admin/settlements/toView','id='+id+'&p='+WST_CURR_PAGE);
}
function loadGrid(p){
    p=(p<=1)?1:p;
	mmg.load({page:p,settlementNo:$('#settlementNo').val(),settlementStatus:$('#settlementStatus').val(),shopName:$('#shopName').val(),startDate:$('#startDate').val(),endDate:$('#endDate').val()});
}

var flag = false;
function intView(id){
    var h = WST.pageHeight();
    var element = layui.element;
    var isInit = false;
    element.on('tab(msgTab)', function(data){
        if(data.index==1){
            if(!isInit){
               isInit = true;
               initGoodsGrid(id);
            }
        }
    });
}
function initGoodsGrid(id){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_goodsappraises_order_no'), name:'orderNo', width: 60},
            {title:WST.lang('label_goods_name'), name:'goodsName' ,width:200},
            {title:WST.lang('product_specifications'), name:'goodsSpecNames',width:200, renderer:function(val,item,rowIndex){
                if(WST.blank(val)!=''){
	            	val = val.split('@@_@@');
	                return val.join('，');
	            }
            }},
            {title:WST.lang('commodity_price'), name:'goodsPrice' ,width:30, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('purchase_quantity'), name:'goodsNum' ,width:20},
            {title:WST.lang('commission_rate'), name:'commissionRate',width:20}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-89,indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/settlements/pageGoodsQuery','id='+id), fullWidthRows: true, autoLoad: true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
}
function toExport(){
    var params = {};
    params = WST.getParams('.j-ipt');
    var box = WST.confirm({content:WST.lang('are_you_sure_you_want_to_export_this_data'),yes:function(){
        layer.close(box);
        location.href=WST.U('admin/settlements/toExport',params);
    }});
}