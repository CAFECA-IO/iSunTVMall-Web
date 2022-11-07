$(function(){
	$('.goodsImg2').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 100,placeholder:WST.conf.RESOURCE_PATH+'/'+WST.conf.GOODS_LOGO});//商品默认图片
	var nowTime = new Date(Date.parse($('#auction-container').attr('sc').replace(/-/g, "/")));
	$('.goods').each(function(){
        var g = $(this);
        var startTime = new Date(Date.parse(g.attr('sv').replace(/-/g, "/")));
        var endTime = new Date(Date.parse(g.attr('ev').replace(/-/g, "/")));
        if(startTime.getTime()> nowTime){
            var opts = {
	            nowTime: nowTime,
			    endTime: startTime,
			    callback: function(data){
			    	if(data.last>0){
			    		var html = [];
				    	if(data.day>0)html.push(data.day+WST.lang('auction_day'));
				    	html.push(data.hour+WST.lang('auction_hour')+data.mini+WST.lang('auction_minute')+data.sec+WST.lang('auction_second'));
				        g.find('.countDown').html(WST.lang('auction_start_time_tips',[html.join('')]));
			    	}else{
			    		var opts2 = {
				            nowTime: data.nowTime,
						    endTime: endTime,
						    callback: function(data2){
						    	if(data2.last>0){
						    		var html = [];
							    	if(data2.day>0)html.push(data2.day+WST.lang('auction_day'));
							    	html.push(data2.hour+WST.lang('auction_hour')+data2.mini+WST.lang('auction_minute')+data2.sec+WST.lang('auction_second'));
							        g.find('.countDown').html(WST.lang('auction_surplus')+html.join(''));
						    	}else{
						    		g.addClass('out');
						    		g.find('.countDown').html(WST.lang('auction_has_end'));
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
			    		var html = [];
				    	if(data.day>0)html.push(data.day+WST.lang('auction_day'));
				    	html.push(data.hour+WST.lang('auction_hour')+data.mini+WST.lang('auction_minute')+data.sec+WST.lang('auction_second'));
				        g.find('.countDown').html(WST.lang('auction_surplus')+html.join(''));
			    	}else{
			    		g.addClass('out');
			    		g.find('.countDown').html(WST.lang('auction_has_end'));
			    	}
			    	
			    }
			};
			WST.countDown(opts);
        }else{
        	g.addClass('out');
        	g.find('.countDown').html(WST.lang('auction_has_end'));
        }
		
	})
});
