function receiveCoupon(id,obj){
	if(WST.conf.IS_LOGIN==0){
		WST.loginWindow();
		return;
	}
    var params = {couponId:id}
	var loading = WST.load({msg:WST.lang('coupon_robing')});
	$.post(WST.AU("coupon://coupons/receive"),params,function(data,textStatus){
		layer.close(loading);
	    json = WST.toJson(data);
		if(json.status==1){
		    WST.msg(json.msg,{icon:1});
		    $('#receive_'+id).show();
			if(json.data.status!=0){
				var str = WST.lang('coupon_has_received');
				if(json.data.status==-1){
					str = WST.lang('coupon_receive_finished');
					$('#receive_'+id).removeClass('receive').addClass('coupon_none');
				}
				$(obj).html(str);
				$(obj).parent().addClass('right-out');
				$(obj).parent().parent().addClass('coupon_out');
				$(obj).parent().parent().find('.coupon-left').addClass('left-out');
				$(obj).removeAttr('onclick');
			}
		}else{
		    WST.msg(json.msg,{icon:2});
		}
	});
}
function receive(id,shopId,obj){
	if(WST.conf.IS_LOGIN==0){
		WST.loginWindow();
		return;
	}
    var params = {couponId:id}
	var loading = WST.load({msg:WST.lang('coupon_robing')});
	$.post(WST.AU("coupon://coupons/receive"),params,function(data,textStatus){
		layer.close(loading);
	    json = WST.toJson(data);
		if(json.status==1){
		    WST.msg(json.msg,{icon:1});
		    if(shopId!=''){
				var num = $('#shopCouponNum_'+shopId).html();
				$('#shopCouponNum_'+shopId).html(parseInt(num,10)+1);
			}
			if(json.data.status!=0){
				var str = (json.data.status==-1)?WST.lang('coupon_receive_finished'):WST.lang('coupon_has_received');
				$(obj).html(str);
				$(obj).removeAttr('onclick');
				$(obj).css('color','#666');
			}
		}else{
		    WST.msg(json.msg,{icon:2});
		}
	});
}
function goodsDetailCouponInit(){
	getCouponByGoods();
}


function getCouponByGoods(){
	var params = {goodsId:goodsInfo.id}
	$.post(WST.AU("coupon://coupons/getCouponsByGoods"),params,function(data,textStatus){
	    json = WST.toJson(data);
		if(json.status==1 && json.data && json.data.length>0){
            var gettpl = document.getElementById('couponlist1').innerHTML;
	       	laytpl(gettpl).render(json.data, function(html){
	       		$('#couponPropBox').html(html);
	       	});
            $('#couponProp').show();
            if(json.data.length>3){
            	$('.j-coupon-show').show();
            	$('#couponPropMoreBox').html($('#couponPropBox').children().clone());
            	$('.j-coupon-show').click(function(){
            		$('#couponPropMoreBox').toggle();
            		$(this).children('img').toggleClass('coupon-show-img')
            	});
            }
		}
	});
}
function getCouponByShop(shopId){
	var params = {shopId:shopId}
	$.post(WST.AU("coupon://coupons/getCouponsByShop"),params,function(data,textStatus){
	    json = WST.toJson(data);
		if(json.status==1 && json.data && json.data.coupons.length>0){
            var gettpl = document.getElementById('couponlist_'+shopId).innerHTML;
            laytpl(gettpl).render(json.data.coupons, function(html){
	       		$('#shopCouponPanel_'+shopId).html(html);
	       	});
	       	$('#shopCouponNum_'+shopId).html(json.data.receive);
		}
	});
}
function showShowCoupons(shopId){
   var promotion = $('#promotion_'+shopId);
   var promotionCont = promotion.find('.promotion-cont');
   if(promotion.attr('show')=='' || promotion.attr('show')=='0'){
   	   promotion.attr('show',1);
   	   getCouponByShop(shopId);
   	   promotion.show();
   	   promotionCont.slideDown(200);
   	   $(document).click(function(){
   	   	   promotion.attr('show',0);
   	   	   promotionCont.slideUp(200,function(){
   	   	   	   promotion.hide();
   	   	   })
   	   })
       promotionCont.click(function(event) {
		   event.stopPropagation();
	   })

   }else{
       promotion.attr('show',0);
       promotionCont.slideUp(200,function(){
   	   	   promotion.hide();
   	   })
   }
}

function couponGoodsInit(){
	$(".wst-coupon-goimg").hover(function(){
		$(this).find(".js-cart").slideDown(100);
	},function(){
		$(this).find(".js-cart").slideUp(100);
	});
}
