var grid;
$(function(){initGrid()})
function initGrid(){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('auction_label_bidders'), name:'loginName', width: 100},
            {title:WST.lang('auction_bidding_price'), name:'shopName', width: 100, renderer: function(val,item,rowIndex){
            	return WST.lang('currency_symbol')+item['payPrice']+((item['isTop']==1)?("&nbsp;&nbsp;<span class='label label-success'>"+WST.lang('auction_highest_price')+"</span>"):"");
            }},
            {title:WST.lang('auction_bidding_time'), name:'createTime', width: 100},
            {title:WST.lang('auction_order_no'), name:'orderNo', width: 100}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'GET',
        url: WST.AU('auction://goods/pageAuctionLogQueryByAdmin','id='+$('#id').val()), fullWidthRows: true, autoLoad: true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
}
function loadGrid(){
	var params = {};
	params.id = $('#id').val();
	params.key = $('#key').val();
	mmg.load(params);
}