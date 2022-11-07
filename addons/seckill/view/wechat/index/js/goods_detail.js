var descFlag = 0, timer = null;
var cAjax = null;
var currPage = totalPage = 0;

jQuery.noConflict();
// 获取图片宽高
function gGetImgWH(img_url, index){
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
  function gViewImg(index){
	var pswpElement = document.querySelectorAll('.pswp')[0];
	// build items array
	if(!goodsInfo.gallery || goodsInfo.gallery.length==0)return;
	// define options (if needed)
	var options = {
	  index: index
	};
	// Initializes and opens PhotoSwipe
	var gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, goodsInfo.gallery, options);
	gallery.init();
  }

function shareShow(){
	jQuery('#cover').attr("onclick","javascript:shareHide();").show();
	jQuery('#frame-share').animate({"bottom": 0}, 500);
}
function shareHide(){
	var cartHeight = parseInt($("#frame-cart").css('height'))+52+'px';
	jQuery('#frame-share').animate({'bottom': '-'+cartHeight}, 500);
	jQuery('#cover').hide();
}

function dialogShare(){
	reloadPoster(1);
}

function reloadPoster(isNew){
	var id = goodsInfo.seckillId;
	$('#Load').show();
	$.post(WST.AU('seckill://goods/wxCreatePoster'), {id:id,isNew:isNew},function(data){
	    $('#Load').hide();
	    var json = WST.toJson(data);
	    if(json.status==1){
	      	$("#shareImg").html("<img src='"+window.conf.ROOT+"/"+json.data.shareImg+"?v="+Math.random()+"' style='width:3rem;height:4.96rem;border-radius: 6px;'/>");
        	$(".reload-btn-box").show();
        	$("#wst-di-qrcod").dialog("show");
	    }
	});
}
$(function(){
	beginCountDown();
	//图片
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


	jQuery(".weui-loadmore").show();
	setTimeout(function(){
		getGoodsDesc();
	},1000);
	WST.imgAdapt('j-imgAdapt');

	$(".ui-dialog-close").click(function(){
	  $("#wst-di-qrcod").removeClass("show");
	});
	shareHide();

	//弹框的高度
    var dataHeight = $("#frame").css('height');
    var cartHeight = parseInt($("#frame-cart").css('height'))+52+'px';
    if(parseInt(dataHeight)>230){
        $('#content').css('overflow-y','scroll').css('height','200');
    }
    if(parseInt(cartHeight)>420){
        $('#standard').css('overflow-y','scroll').css('height','260');
    }
    $("#frame").css('bottom','-100%');
    $("#frame-cart").css('bottom','-100%');
	echo.init({
		offset:2500
	});//图片懒加载
});

function inMore(){
	if($("#arrow").css("display")=='none'){
		jQuery('#arrow').show(200);
		$("#layer").show();
	}else{
		jQuery('#arrow').hide(100);
		$("#layer").hide();
	}
}
//弹框
function dataShow(){
	jQuery('#cover').attr("onclick","javascript:dataHide();").show();
	jQuery('#frame').animate({"bottom": 0}, 500);
}
function dataHide(){
	jQuery('#frame').animate({'bottom': '-100%'}, 500);
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
	jQuery('#frame-cart').animate({'bottom': '-100%'}, 500);
	jQuery('#cover').hide();
}

//商品详情
function getGoodsDesc(){
	if(jQuery(".wst-goods-desc").is(':hidden')){
		var goodsId = $("#goodsId").val();
		$.post(WST.AU('seckill://goods/getGoodsDesc'),{goodsId:goodsId},function(data,textStatus){
			var json = WST.toJson(data);
			$(".weui-loadmore").hide();
	      	$(".wst-goods-desc").html(json.data.goodsDesc).show();
	      	echo.init();//图片懒加载
	    });
	}
}

function beginCountDown(){
	var obj = $("#seckillTime");
	var nowTime = obj.data("ntime");
	var stime = obj.data("stime");
	var etime = obj.data("etime");
	var vstatus = obj.data("status");
	var nowTime = new Date(Date.parse(nowTime.replace(/-/g, "/")));
	var startTime = new Date(Date.parse(stime.replace(/-/g, "/")));
	var endTime = new Date(Date.parse(etime.replace(/-/g, "/")));
	if(vstatus=='0'){
		$(".status").html("<span >距开始</span>");
		timer = countDown(nowTime,startTime);
	}else{
		$(".status").html("<span >距结束</span>");
		timer = countDown(nowTime,endTime);
	}
}

function countDown(nowTime,endTime){
  var opts = {
    nowTime:nowTime,
    endTime: endTime,
    countDownType: 1,
    callback: function(data){
        if(data.last>0){
          $(".lab_timer").html('<span>'+data.hour+'</span><em>:</em><span>'+data.mini+'</span><em>:</em><span>'+data.sec+'</span><em>:</em><span>'+data.msec+'</span>');
        }else{
          $(".seckill_items_timer").html('<strong class="status_tip">已结束</strong>');
        }           
    }
  };
  return WST.countDown(opts);
}

//加入购物车
function addCart(){
	if(WST.conf.IS_LOGIN==0){
		WST.inLogin();
		return;
	}
	WST.load('数据处理中');
	var buyNum = $("#buyNum").val()?$("#buyNum").val():1;
	$.post(WST.AU('seckill://carts/addCart'),{id:goodsInfo.seckillId,buyNum:buyNum,rnd:Math.random()},function(data,textStatus){
	     var json = WST.toJson(data);
	     if(json.status==1){
    		setTimeout(function(){
    			location.href=WST.AU('seckill://carts/wxsettlement');
    		},1000);
	     }else{
	     	WST.noload();
	    	WST.msg(json.msg,'info');
	     }
	});
}
