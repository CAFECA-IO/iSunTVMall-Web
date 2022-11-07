var mmg;
function initGrid(p){
   var h = WST.pageHeight();
   var cols = [
        {title:WST.lang('coupon_receive_user'), name:'loginName', width: 120},
        {title:WST.lang('coupon_receive_time'), name:'createTime', width: 120},
        {title:WST.lang('coupon_status'), name:'isUse', width: 30,renderer:function(val,item,rowIndex){
        	return (item['isUse']==0)?"-":WST.lang('coupon_status_2');
        }},
        {title:WST.lang('coupon_use_time'), name:'useTime', width: 120},
        {title:WST.lang('coupon_relate_order'), name:'orderNo', width: 120}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',nowrap:true,
        url: WST.AU('coupon://shops/pageQueryByCoupons'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function loadGrid(p){
	var params={};
	p=(p<=1)?1:p;
	params.page=p;
	params.isUse = $('#isUse').val();
	params.couponId = $('#couponId').val();
    mmg.load(params);
}

function toBack(p){
    location.href = WST.AU('coupon://shops/index','p='+p);
}


