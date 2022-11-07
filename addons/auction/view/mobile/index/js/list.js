jQuery.noConflict();
//获取店铺列表
function goodsList(){
	$('#Load').show();
    loading = true;
    var param = {};
    param.catId = $('#goodsCatId').val();
    param.goodsName = $('#keyword').val();
	param.pagesize = 10;
	param.page = Number( $('#currPage').val() ) + 1;
    $.post(WST.AU('auction://goods/moGrouplists'), param,function(data){
        var json = WST.toJson(data);
        $('#currPage').val(json.current_page);
        $('#totalPage').val(json.last_page);
        var gettpl = document.getElementById('list').innerHTML;
        laytpl(gettpl).render(json, function(html){
            $('#goods-list').append(html);
        });
        WST.imgAdapt('j-imgAdapt');
        loading = false;
        $('#Load').hide();
        echo.init();//图片懒加载
        time(json.current_page);
    });
}
var currPage = totalPage = 0;
var loading = false;
$(document).ready(function(){
	WST.initFooter('home');
	goodsList();
    var dataHeight = $("#frame").css('height');
    $('.goodscate1').css('overflow-y','scroll').css('height',WST.pageHeight()-50);
    $("#frame").css('top',0);
     var dataWidth = $("#frame").css('width');
    $("#frame").css('right','-'+dataWidth);

    $(window).scroll(function(){ 
        if (loading) return;
        if ((5 + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
            currPage = Number( $('#currPage').val() );
            totalPage = Number( $('#totalPage').val() );
            if( totalPage > 0 && currPage < totalPage ){
            	goodsList();
            }
        }
    });
});
function goGoods(id){
    location.href=WST.AU('auction://goods/modetail','id='+id);
}
function searchGoods(){
	var data = $('#wst-search').val();
	location.href = WST.AU('auction://goods/molists','goodsName='+data);
}
function time(n){
	var nowTime = new Date(Date.parse($('#groupon-container').attr('sc').replace(/-/g, "/")));
	$('.goods_'+n).each(function(){
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
						if(data.day>0)html.push("<span>"+data.day+" </span>"+WST.lang('auction_day')+" ");
						html.push("<span>"+data.hour+"</span>"+" : "+"<span>"+data.mini+"</span>"+" : "+"<span>"+data.sec+"</span>");
				        g.find('.countDown_'+n).html(WST.lang('auction_start_time_tips',[html.join('')]));
			    	}else{
			    		var opts2 = {
				            nowTime: data.nowTime,
						    endTime: endTime,
						    callback: function(data2){
						    	if(data2.last>0){
						    		var html = [];
							    	if(data2.day>0)html.push("<span>"+data2.day+" </span>"+WST.lang('auction_day')+" ");
							    	html.push("<span>"+data2.hour+"</span>"+" : "+"<span>"+data2.mini+"</span>"+" : "+"<span>"+data2.sec+"</span>");
							        g.find('.countDown_'+n).html(WST.lang('auction_surplus')+" "+html.join(''));
						    	}else{
						    		g.addClass('wst-shl-list2');
						    		g.find('.countDown_'+n).html(WST.lang('auction_has_end'));
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
				    	if(data.day>0)html.push("<span>"+data.day+" </span>"+WST.lang('auction_day')+" ");
						html.push("<span>"+data.hour+"</span>"+" : "+"<span>"+data.mini+"</span>"+" : "+"<span>"+data.sec+"</span>");
				        g.find('.countDown_'+n).html(WST.lang('auction_surplus')+" "+html.join(''));
			    	}else{
			    		g.addClass('wst-shl-list2');
			    		g.find('.countDown_'+n).html(WST.lang('auction_has_end'));
			    	}
			    	
			    }
			};
			WST.countDown(opts);
        }else{
        	g.addClass('wst-shl-list2');
        	g.find('.countDown_'+n).html(WST.lang('auction_has_end'));
        }
		
	})
}
//弹框
function dataShow(){
    jQuery('#cover').attr("onclick","javascript:dataHide();").show();
    jQuery('#frame').animate({"right": 0}, 500);
}
function dataHide(){
    var dataHeight = $("#frame").css('height');
    var dataWidth = $("#frame").css('width');
    jQuery('#frame').animate({'right': '-'+dataWidth}, 500);
    jQuery('#cover').hide();
}

function showRight(obj, index){
	$('.wst-goodscate').removeClass('wst-goodscate_selected');
	$(obj).addClass('wst-goodscate_selected');
    $('.goodscate1').eq(index).show().siblings('.goodscate1').hide();
}
/*分类*/
function goodsCat(goodsCatId){
    $('#goodsCatId').val(goodsCatId);
    $('#currPage').val('');
    $('#goods-list').html('');
    goodsList();
    dataHide();
}
