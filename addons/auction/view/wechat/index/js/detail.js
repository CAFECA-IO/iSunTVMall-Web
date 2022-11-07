jQuery.noConflict();
var currPage = totalPage = 0;
var loading = false;

// 获取图片宽高
function gGetImgWH(img_url, index) {
	// 创建对象
	var img = new Image();
	// 改变图片的src
	img.src = img_url;
	// 判断是否有缓存
	if (img.complete) {
		// 打印
		goodsInfo.gallery[index].w = img.width;
		goodsInfo.gallery[index].h = img.height;
	} else {
		// 加载完成执行
		img.onload = function () {
			//  打印
			goodsInfo.gallery[index].w = img.width;
			goodsInfo.gallery[index].h = img.height;
		}
	}
}

// 查看大图
function gViewImg(index) {
	var pswpElement = document.querySelectorAll('.pswp')[0];
	// build items array
	if (!goodsInfo.gallery || goodsInfo.gallery.length == 0) return;
	// define options (if needed)
	var options = {
		index: index
	};
	// Initializes and opens PhotoSwipe
	var gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, goodsInfo.gallery, options);
	gallery.init();
}


$(document).ready(function(){
	$("embed").removeAttr('height').css('width','100%');
	time();
	//商品图片
	new Swiper('.swiper-container', {
		autoplay: false,
		autoHeight: true, //高度随内容变化
		width: window.innerWidth,
		on: {
			resize: function(){
				this.params.width = window.innerWidth;
				this.update();
			},
		} ,
		pagination: {
			el: '.swiper-pagination',
			type: 'bullets',
		},
	});
	var w = WST.pageWidth();
	var h = WST.pageHeight();
    //弹框的高度
    var dataHeight = $("#frame").css('height');
    var cartHeight = parseInt($("#frame-cart").css('height'))+52+'px';
    if(parseInt(dataHeight)>230){
        $('#content').css('overflow-y','scroll').css('height','200');
    }
    if(parseInt(cartHeight)>420){
        $('#standard').css('overflow-y','scroll').css('height','260');
    }
    var dataHeight = $("#frame").css('height');
    var cartHeight = parseInt($("#frame-cart").css('height'))+52+'px';
    $("#frame").css('bottom','-'+dataHeight);
    $("#frame-cart").css('bottom','-'+cartHeight);
    //记录出价
    $("#record").css('right',-w);
    $('#record .contents').css('overflow-y','scroll').css('height',h);
    $('#rule .content').css('overflow-y','scroll').css('height',h);
    
    //规则弹出层
    $("#rule").css('right',-w);
    
    //页码
    $('#record .contents').scroll(function(){
        if (loading) return;
        if ((300 + $('#record .contents').scrollTop()) >= $('#record .contents').height()) {
            currPage = Number( $('#currPage').val() );
            totalPage = Number( $('#totalPage').val() );
            if( totalPage > 0 && currPage < totalPage ){
            	recordList();
            }
        }
    });
});
//弹框
function dataShow(){
	jQuery('#cover').attr("onclick","javascript:dataHide();").show();
	jQuery('#frame').animate({"bottom": 0}, 500);
}
function dataHide(){
	var dataHeight = $("#frame").css('height');
	jQuery('#frame').animate({'bottom': '-'+dataHeight}, 500);
	jQuery('#cover').hide();
}
//弹框
var type;
function cartShow(t){
	type = t;
	jQuery('#cover').attr("onclick","javascript:cartHide();").show();
	jQuery('#frame-cart').animate({"bottom": 0}, 500);
}
function cartHide(){
	var cartHeight = parseInt($("#frame-cart").css('height'))+52+'px';
	jQuery('#frame-cart').animate({'bottom': '-'+cartHeight}, 500);
	jQuery('#cover').hide();
}
//弹框
function wholeShow(type){
    jQuery('#'+type).animate({"right": 0}, 500);
    if(type=='record'){
    	$('#currPage').val('');
		$('#record-list').html('').css({height:  WST.pageHeight() - $('#record .title').height()-10, paddingBottom:10 });
    	recordList();
    }
}
//记录列表
function recordList(){
    loading = true;
    var param = {};
    param.id = goodsInfo.auctionId;
	param.pagesize = 20;
	param.page = Number( $('#currPage').val() ) + 1;
    $.post(WST.AU('auction://auction/pageQueryByAuctionLog'), param,function(data){
        var json = WST.toJson(data);
        $('#currPage').val(json.data.current_page);
        $('#totalPage').val(json.data.last_page);
        var gettpl = document.getElementById('list').innerHTML;
        laytpl(gettpl).render(json.data.data, function(html){
            $('#record-list').append(html);
        })
        loading = false;
        echo.init();//图片懒加载
    });
}
function wholeHide(type){
    var dataHeight = $('#'+type).css('height');
    var dataWidth = $('#'+type).css('width');
    jQuery('#'+type).animate({'right': '-'+dataWidth}, 500);
}
//交保证金
function addBond(){
	if(WST.conf.IS_LOGIN==0){
		WST.inLogin();
		return;
	}
	location.href=WST.AU('auction://auction/wxsucceed',{'auctionId':goodsInfo.auctionId});
}
function goGoods(id){
    location.href=WST.AU('auction://goods/wxdetail','id='+id);
}
//出价
function addOffer(){
	if(WST.conf.IS_LOGIN==0){
		WST.inLogin();
		return;
	}
	var buyNum = $("#buyNum").val()?$("#buyNum").val():$("#currPrices").val();
	$.post(WST.AU('auction://auction/addAcution'),{id:goodsInfo.auctionId,payPrice:buyNum,rnd:Math.random()},function(data,textStatus){
	     var json = WST.toJson(data);
	     if(json.status==1){
	    	 WST.msg(json.msg,'success');
	    	 cartHide();
    		 setTimeout(function(){
    			 location.href=WST.AU('auction://goods/wxdetail','id='+goodsInfo.auctionId);
    		 },1000);
	     }else{
	    	 WST.msg(json.msg,'info');
	     }
	});
}
function time(){
	var g = $('#groupon-time');
	var nowTime = new Date(Date.parse(g.attr('sc').replace(/-/g, "/")));
    var startTime = new Date(Date.parse(g.attr('sv').replace(/-/g, "/")));
    var endTime = new Date(Date.parse(g.attr('ev').replace(/-/g, "/")));
    if(startTime.getTime()> nowTime.getTime()){
        var opts = {
        	nowTime:nowTime,
			endTime: startTime,
			callback: function(data){
			    if(data.last>0){
			    	var html = [];
				    if(data.day>0)html.push(data.day+WST.lang('auction_day'));
				    html.push(data.hour+WST.lang('auction_hour')+data.mini+WST.lang('auction_minute')+data.sec+WST.lang('auction_second'));
				    $('#grouptime').html(WST.lang('auction_start_time_tips',[+html.join('')]));
			    }else{
			    	var opts2 = {
	                    nowTime: data.nowTime,
						endTime: endTime,
						callback: function(data2){
						    if(data2.last>=0){
						    	var html = [];
							    if(data2.day>0)html.push(data2.day+WST.lang('auction_day'));
							    html.push(data2.hour+WST.lang('auction_hour')+data2.mini+WST.lang('auction_minute')+data2.sec+WST.lang('auction_second'));
							    $('#grouptime').html(WST.lang('auction_surplus')+html.join(''));
							    $('.wst-goods_buym').removeClass('active').removeAttr('disabled');
						    }else{
						    	$('#grouptime').html(WST.lang('auction_has_end'));
						    }
						    	
						}
					}
			    	WST.countDown(opts2);
			    }		
			}
		};
		WST.countDown(opts);
    }else if(startTime.getTime()<= nowTime.getTime() && endTime.getTime() >=nowTime.getTime()){
        var opts = {
        	nowTime:nowTime,
			endTime: endTime,
			callback: function(data){
			    if(data.last>0){
			    	var html = [];
				    if(data.day>0)html.push(data.day+WST.lang('auction_day'));
				    html.push(data.hour+WST.lang('auction_hour')+data.mini+WST.lang('auction_minute')+data.sec+WST.lang('auction_second'));
				    $('#grouptime').html(WST.lang('auction_surplus')+html.join(''));
				    $('.wst-goods_buym').removeClass('active').removeAttr('disabled');
			    }else{
			    	$('.wst-goods_buym').removeClass('active').addClass('disable').attr('disabled', 'disabled');
			    	$('#grouptime').html(WST.lang('auction_has_end'));
			    }			    	
			}
		};
		WST.countDown(opts);
    }else{
		$('.wst-goods_buym').removeClass('active').addClass('disable').attr('disabled', 'disabled');
        $('#grouptime').html(WST.lang('auction_has_end'));
    }
}



function dialogShare(){
	reloadPoster(1);
}
function reloadPoster(isNew){
	var id = goodsInfo.auctionId;
	$('#Load').show();
	$.post(WST.AU('auction://goods/wxCreatePoster'), {id:id,isNew:isNew},function(data){
 		$('#Load').hide();
        var json = WST.toJson(data);
        if(json.status==1){
        	$("#shareImg").html("<img src='"+window.conf.ROOT+"/"+json.data.shareImg+"?v="+Math.random()+"' style='width:3rem;height:4.96rem;border-radius: 6px;'/>");
        	$(".reload-btn-box").show();
        	$("#wst-di-qrcod").dialog("show");
        }
    });
}