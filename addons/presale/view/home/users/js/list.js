function queryByPage(p){
	$('#list').html('<tr><td colspan="11"><img src="'+WST.conf.ROOT+'/wstmart/home/view/default/img/loading.gif">'+WST.lang('presale_loading_data')+'</td></tr>');
	var params = WST.getParams('.u-query');
	params.page = p;
	$.post(WST.AU('presale://users/pageQuery'),params,function(data,textStatus){
	    $('#loading').hide();
	    var json = WST.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1){
	    	//json = json.data;
	    	if(params.page>json.last_page && json.last_page >0){
               queryByPage(json.last_page);
               return;
            }
	       	var gettpl = document.getElementById('tblist').innerHTML;
	       	laytpl(gettpl).render(json.data, function(html){
	       		$(html).insertAfter('#loadingBdy');
	       		$('.gImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.RESOURCE_PATH+'/'+WST.conf.GOODS_LOGO});
	       	});
	       	laypage({
		        	 cont: 'pager',
		        	 pages:json.last_page,
		        	 curr: json.current_page,
		        	 skin: '#e23e3d',
		        	 groups: 3,
		        	 jump: function(e, first){
		        		 if(!first){
		        			 queryByPage(e.curr);
		        		 }
		        	 }
		    });
       	}
	});
}

//去支付
function toView(porderId){
    location.href=WST.AU('presale://users/toView',{'porderId':porderId});
}


//去支付
function choicePay(pkey){
    location.href=WST.AU('presale://carts/succeed',{'pkey':pkey});
}
