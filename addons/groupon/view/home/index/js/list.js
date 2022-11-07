$(function(){
	$('.goodsImg2').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 100,placeholder:WST.conf.RESOURCE_PATH+'/'+WST.conf.GOODS_LOGO});//商品默认图片
	var nowTime = new Date(Date.parse($('#groupon-container').attr('sc').replace(/-/g, "/")));
	$('.goods').each(function(){
        var g = $(this);
        var startTime = new Date(Date.parse(g.attr('sv').replace(/-/g, "/")));
        var endTime = new Date(Date.parse(g.attr('ev').replace(/-/g, "/")));
        var groupStatus = g.attr('st');
        if(groupStatus==-1){
        	g.find('.goods-txt').addClass('goods-txt2').removeClass('goods-txt');
			g.find('.countDown').html(WST.lang('group_finished'));
        }else{
	        if(startTime.getTime()> nowTime){
	            var opts = {
		            nowTime: nowTime,
				    endTime: startTime,
				    callback: function(data){
				    	if(data.last>0){
				    		var html = [];
					    	if(data.day>0)html.push(data.day+WST.lang('group_day'));
					    	html.push(data.hour+WST.lang('group_hour')+data.mini+WST.lang('group_minute')+data.sec+WST.lang('group_second'));
					        g.find('.time-before').html(WST.lang('group_start_time_tips',[html.join('')]));
				    	}else{
				    		var opts2 = {
					            nowTime: data.nowTime,
							    endTime: endTime,
							    callback: function(data2){
							    	if(data2.last>0){
							    		g.find('.time-in').show();
							    		g.find('.time-before').hide();
							    	}else{
							    		g.find('.goods-txt').addClass('goods-txt2').removeClass('goods-txt');
							    		g.find('.countDown').html(WST.lang('group_finished'));
							    	}
							    	
							    }
							}
				    	    WST.countDown(opts2);
				    	}
				    		
				    }
				};
				WST.countDown(opts);
	        }else if(startTime.getTime()<= nowTime && endTime.getTime() >=nowTime){
	            var opts = {
		            nowTime: nowTime,
				    endTime: endTime,
				    callback: function(data){
				    	if(data.last>0){
				    		g.find('.time-in').show();
							g.find('.time-before').hide();
				    	}else{
				    		g.find('.p-add-cart').remove();
				    		g.find('.goods-txt').addClass('goods-txt2').removeClass('goods-txt');
				    		g.find('.countDown').html(WST.lang('group_finished'));
				    	}
				    	
				    }
				};
				WST.countDown(opts);
	        }else{
	        	g.find('.p-add-cart').remove();
	        	g.find('.goods-txt').addClass('goods-txt2').removeClass('goods-txt');
	        	g.find('.countDown').html(WST.lang('group_finished'));
	        }
		}
	})
});
$('.goods').hover(function(){
	$(this).find('.sale-num').hide();
	$(this).find('.p-add-cart').show();
},function(){
	$(this).find('.sale-num').show();
	$(this).find('.p-add-cart').hide();
})
