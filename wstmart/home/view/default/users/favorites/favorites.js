var WST_CURR_PAGE = 1;
//关注的商品列表
function freGoodsList(pages){
	var param = {};
	param.pagesize = 8;
	param.page = pages;
    $.post(WST.U('home/favorites/listGoodsQuery'),param,function(data){
        var json = WST.toJson(data);
        if(json.status==1){
        	json = json.data;
        	if(param.page>json.last_page && json.last_page >0){
               freGoodsList(json.last_page);
               return;
            }
	        var gettpl = document.getElementById('list').innerHTML;
	        laytpl(gettpl).render(json.data, function(html){
	            $('#list-goods').html(html);
	        });
	        laypage({
	               cont: 'goodsPage',
	               pages:json.last_page, 
	               curr: json.current_page,
	               skip: true, //是否开启跳页
	               skin: '#f46442',
	               groups: 3,
	               prev: '<<',
	               next: '>>',
	               jump: function(e, first){
	                    if(!first){
	                    	freGoodsList(e.curr);
	                    }
	               } 
	        });
	    	$(".wst-fav-goimg").hover(function(){
	    		$(this).find(".js-operate").slideDown();
	    	},function(){
	    		$(this).find(".js-operate").slideUp();
	    	});
	    	$('.goodsImg2').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.RESOURCE_PATH+'/'+window.conf.GOODS_LOGO});//商品默认图片
        }
    });
}
function getGoods(id){
	location.href=WST.U('home/goods/detail','goodsId='+id);
}
function cancelFavorite(id,type){
	WST.confirm({content:WST.lang('cancel_attention_confirm_inquiry'), yes:function(tips){
	    var load = WST.load({msg:WST.lang('submiting_tips')});
		var param = {};
		param.id = id;
		param.type = type;
	    $.post(WST.U('home/favorites/cancel'),param,function(data,textStatus){
	      layer.close(load);
	      var json = WST.toJson(data);
	      if(json.status=='1'){
	        WST.msg(json.msg,{icon:1},function(){
	        	if(type==0){
	        		freGoodsList(WST_CURR_PAGE);
	        	}else{
	        		freShopList(WST_CURR_PAGE);
	        	}
	        	
	        });
	      }else{
	        WST.msg(json.msg,{icon:5});
	      }
	    });
	}});
}
//关注的店铺列表
function freShopList(pages){
	var param = {};
	param.pagesize = 5;
	param.page = pages;
    $.post(WST.U('home/favorites/listShopQuery'),param,function(data){
        var json = WST.toJson(data);
        if(json.status==1){
        	json = json.data;
        	if(param.page>json.last_page && json.last_page >0){
               freShopList(json.last_page);
               return;
            }
	        var gettpl = document.getElementById('list').innerHTML;
	        laytpl(gettpl).render(json.data, function(html){
	            $('#list-shops').html(html);
	        });
	        //商品滑动
	    	var goodsNum = json.data.length;
	    	for(var i=0;i<goodsNum;++i){
		    	$("#js-goods"+i).als({
		    		visible_items: 5,
		    		scrolling_items: 1,
		    		orientation: "horizontal",
		    		circular: "yes",
		    		autoscroll: "no",
		    		start_from: 2
		    	});
	    	}
	    	$('.goodsImg2').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.RESOURCE_PATH+'/'+window.conf.GOODS_LOGO});//商品默认图片
	        $('.shopsImg2').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.RESOURCE_PATH+'/'+window.conf.SHOP_LOGO});//店铺默认头像
	        laypage({
	            cont: 'shopsPage',
	            pages:json.last_page, 
	            curr: json.current_page,
	            skip: true, //是否开启跳页
	            skin: '#f46442',
	            groups: 3,
	            prev: '<<',
	            next: '>>',
	            jump: function(e, first){
	                if(!first){
	                    freShopList(e.curr);
	                }
	            } 
	        });
        }
    });
}
function getShop(id){
	location.href=WST.U('home/shops/index','shopId='+id);
}