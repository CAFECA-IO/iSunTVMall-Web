var scrollFlag = true;
jQuery.noConflict();
// 获取图片宽高
function gViewImg(index,obj){
	var pswpElement = document.querySelectorAll('.pswp')[0];
	var gallery = $(obj).parent().data("gallery");
	if(gallery!=''){
	    gallery = gallery.split(',').map(function(imgUrl,i){
	      imgUrl = WST.conf.RESOURCE_PATH+"/"+imgUrl;
	      var _obj = { src:imgUrl, w:0, h:0 };
	      return _obj;
	    })
	  }
	// build items array
	if(!gallery || gallery.length==0)return;
	// define options (if needed)
	var options = {
	  index: index
	};
	// Initializes and opens PhotoSwipe
	var gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, gallery, options);
	gallery.init();
	gallery.listen('imageLoadComplete', function(index, item) {
       	if (item.h < 1 || item.w < 1) {
		  var img = new Image();
		  img.onload = function(){
			item.w = img.width;
			item.h = img.height;
			gallery.invalidateCurrItems()
			gallery.updateSize(true);
		  }
		  img.src = item.src
		}
    });
}
//切换
function pageSwitch(obj,type){
	scrollFlag = false;
	$('.ui-tab-nav li.switch').removeClass('active');
	$(obj).addClass('active');
	if(type!=1){
		$('#goods'+type).show().siblings('section.ui-container').hide();
	}else{
		$('#goods'+type).show().siblings('section.ui-container').hide();
		$('#goods2').show();
	}
	if(type==1){
		var offsetTop = $("#goods1").offset().top;
		var scrollTop = $(window).scrollTop()-100;
		if (scrollTop > offsetTop){
			$("#goods-header").show();
		}else{
			$("#goods-header").hide();
		}
	}
	if(type==2){
		$('#goods2').css('border-top','0.47rem solid transparent');
	}else{
		$('#goods2').css('border-top','0');
	}
	$(window).scrollTop(0);
	if(type==1){
		scrollFlag = true;
	}
	if(type==2 || type==3){
		$("#goods-header").show();
	}
}
//商品评价列表
function evaluateList(){
    loading = true;
    var param = {};
    param.goodsId = $('#goodsId').val();
	param.type = $('#evaluateType').val();
	param.pagesize = 10;
	param.page = Number( $('#currPage').val() ) + 1;
    $.post(WST.U('mobile/goodsappraises/getById'), param,function(data){
        var json = WST.toJson(data);
        $('#currPage').val(json.data.current_page);
        $('#totalPage').val(json.data.last_page);
        var gettpl = document.getElementById('list').innerHTML;
        laytpl(gettpl).render(json.data.data, function(html){
            $('#evaluate-list').append(html);
        });
        loading = false;
        echo.init();//图片懒加载
    });
}
var currPage = totalPage = 0;
var loading = false;
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
    evaluateList();
	fixedHeader();
	WST.imgAdapt('j-imgAdapt');
	var detail = $(".wst-go-details");
	var offSet = detail.offset().top;
	var tab0 = $('.ui-tab-nav li').get(0);
	var tab1 = $('.ui-tab-nav li').get(1);
	$(window).scroll(function(){
		if(scrollFlag){
			if ($(window).scrollTop() > offSet){
				if($(tab0).hasClass('active')){
					$(tab0).removeClass('active');
					$(tab1).addClass('active');
				}
			}else{
				if($(tab1).hasClass('active')){
					$(tab0).addClass('active');
					$(tab1).removeClass('active');
				}
			}
		}
        if (loading) return;
        if ((5 + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
            currPage = Number( $('#currPage').val() );
            totalPage = Number( $('#totalPage').val() );
            if( totalPage > 0 && currPage < totalPage ){
            	evaluateList();
            }
        }
    });
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
	echo.init({
		offset:2500
	});//图片懒加载
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
//加入购物车
function addCart(goodsType){
	if(WST.conf.IS_LOGIN==0){
		WST.inLogin();
		return;
	}
	var buyNum = $("#buyNum").val()?$("#buyNum").val():1;
	$.post(WST.AU('groupon://carts/addCart'),{id:goodsInfo.grouponId,buyNum:buyNum,rnd:Math.random()},function(data,textStatus){
	     var json = WST.toJson(data);
	     if(json.status==1){
	    	 WST.msg(json.msg,'success');
	    	 cartHide();
    		 setTimeout(function(){
    			 location.href=WST.AU('groupon://carts/moSettlement','goodsType='+goodsType);
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
    var groupStatus = g.attr('st');
    if(groupStatus==-1){
    	$('#buyBtn').removeClass('active').attr('disabled', 'disabled');
		$('#grouptime').html(WST.lang('group_finished'));
    }else{
	    if(startTime.getTime()> nowTime.getTime()){
	        var opts = {
	        	nowTime:nowTime,
				endTime: startTime,
				callback: function(data){
				    if(data.last>0){
				    	var html = [];
					    if(data.day>0)html.push(data.day+WST.lang('group_day')+" ");
					    html.push(data.hour+" : "+data.mini+" : "+data.sec);
					    $('#grouptime').html(WST.lang('group_start_time_tips',[html.join('')]));
					    $('#buyBtn').removeClass('active').attr('disabled', 'disabled');
				    }else{
				    	var opts2 = {
		                    nowTime: data.nowTime,
							endTime: endTime,
							callback: function(data2){
							    if(data2.last>=0){
							    	var html = [];
								    if(data2.day>0)html.push(data2.day+WST.lang('group_day')+" ");
								    html.push(data2.hour+" : "+data2.mini+" : "+data2.sec);
								    $('#grouptime').html(WST.lang('group_surplus')+" "+html.join(''));
								    $('#buyBtn').removeAttr('disabled');
							    }else{
							    	$('#buyBtn').removeClass('active').attr('disabled', 'disabled');
							    	$('#grouptime').html(WST.lang('group_finished'));
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
					    if(data.day>0)html.push(data.day+WST.lang('group_day')+" ");
					    html.push(data.hour+" : "+data.mini+" : "+data.sec);
					    $('#grouptime').html(WST.lang('group_surplus')+" "+html.join(''));
					    $('#buyBtn').removeAttr('disabled');
				    }else{
				    	$('#buyBtn').removeClass('active').attr('disabled', 'disabled');
				    	$('#grouptime').html(WST.lang('group_finished'));
				    }
				}
			};
			WST.countDown(opts);
	    }else{
	        $('#buyBtn').removeClass('active').attr('disabled', 'disabled');
	        $('#grouptime').html(WST.lang('group_finished'));
	    }
	}
}

function inMore(){
	if($("#arrow").css("display")=='none'){
		jQuery('#arrow').show(200);
		$("#layer").show();
	}else{
		jQuery('#arrow').hide(100);
		$("#layer").hide();
	}
}

//导航
function fixedHeader(){
	var offsetTop = $("#goods1").offset().top;
	$(window).scroll(function() {
		if($("#goods1").css("display")!='none'){
			var scrollTop = $(window).scrollTop()-100;
			if (scrollTop > offsetTop){
				$("#goods-header").show();
			}else{
				$("#goods-header").hide();
			}
		}else{
			$("#goods-header").show();
		}
	});
}

function evaluateSwitch(obj,type){
	$('#evaluateType').val(type);
	$(obj).addClass('active').siblings('.wst-ev-term .ui-col').removeClass('active');
	$('#currPage').val('0');
	$('#totalPage').val('0');
	$('#evaluate-list').html('');
	evaluateList();
}

function dialogShare(){
	reloadPoster(1);
}
function reloadPoster(isNew){
	var id = goodsInfo.grouponId;
	$('#Load').show();
	$.post(WST.AU('groupon://goods/moCreatePoster'), {id:id,isNew:isNew},function(data){
 		$('#Load').hide();
        var json = WST.toJson(data);
        if(json.status==1){
        	$("#shareImg").html("<img src='"+window.conf.ROOT+"/"+json.data.shareImg+"?v="+Math.random()+"' style='width:3rem;height:4.96rem;border-radius: 6px;'/>");
        	$(".reload-btn-box").show();
        	$("#wst-di-qrcod").dialog("show");
        }
    });
}
