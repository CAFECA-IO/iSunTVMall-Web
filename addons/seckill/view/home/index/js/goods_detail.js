var descFlag = 0, timer = null;
var cAjax = null;
var currPage = totalPage = 0;

$(function(){
	beginCountDown();
	$("embed").css('width','100%');
	WST.dropDownLayer(".item",".dorp-down-layer");
	
	//图片放大镜效果
	CloudZoom.quickStart();
	imagesMove({id:'.goods-pics',items:'.items'});
	//选择规格
	$('.spec .j-option').click(function(){
		$(this).addClass('j-selected').siblings().removeClass('j-selected');
	});
	fixedbar();
	$('#tab').TabPanel({tab:0,callback:function(no){
		if(no==2)queryByPage();
		if(no==3)queryConsult();

	}});
    $(".wst-tab-nav li").click(function(){
        $('html, body').animate({
            scrollTop: $("#goods-desc").offset().top  
        }, 200);
	})
});
function fixedbar(){
    var offsetTop = $("#goodsTabs").offset().top;  
    $(window).scroll(function() {  
        var scrollTop = $(document).scrollTop();  
        if (scrollTop > offsetTop){  
        	$("#goodsTabs").addClass("active");
        }else{
        	$("#goodsTabs").removeClass("active");
        }  
    });   
}


function imagesMove(opts){
	var spacing = 16; //间距，只是数值计算
	var tempLength = 0; //临时变量,当前移动的长度
	var viewNum = 5; //设置每次显示图片的个数量
	var moveNum = 5; //每次移动的数量
	var moveTime = 300; //移动速度,毫秒
	var moveTime = 300; //移动速度,毫秒
	var scrollDiv = $(opts.id+" "+opts.items+" ul"); //进行移动动画的容器
	var scrollItems = $(opts.id+" "+opts.items+" ul li"); //移动容器里的集合
	var moveLength = (scrollItems.eq(0).width()+spacing) * moveNum; //计算每次移动的长度
	var countLength = (scrollItems.length - viewNum) * (scrollItems.eq(0).width()+spacing); //计算总长度,总个数*单个长度
	  
	//下一张
	$(opts.id+" .next").bind("click",function(){
		if(tempLength < countLength){
			if((countLength - tempLength) > moveLength){
				scrollDiv.animate({left:"-=" + moveLength + "px"}, moveTime);
				tempLength += moveLength;
			}else{
				scrollDiv.animate({left:"-=" + (countLength - tempLength) + "px"}, moveTime);
				tempLength += (countLength - tempLength);
			}
		}
	});
	//上一张
	$(opts.id+" .prev").bind("click",function(){
		if(tempLength > 0){
			if(tempLength > moveLength){
				scrollDiv.animate({left: "+=" + moveLength + "px"}, moveTime);
				tempLength -= moveLength;
			}else{
				scrollDiv.animate({left: "+=" + tempLength + "px"}, moveTime);
				tempLength = 0;
			}
		}
	});
}


function informs($goodsId){
	if(window.conf.IS_LOGIN==0){
		WST.loginWindow();
		return;
	}
	location.href=WST.U("home/informs/inform",'id='+$goodsId);
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
		$(".status").html("<span >"+WST.lang('seckill_to_start')+"</span>");
		timer = countDown(nowTime,startTime);
	}else{
		$(".status").html("<span >"+WST.lang('seckill_to_end')+"</span>");
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
          $(".seckill_items_timer").html('<strong class="status_tip">'+WST.lang('seckill_curr_status_3')+'</strong>');
        }           
    }
  };
  return WST.countDown(opts);
}


//加入购物车
function addCart(){
	if(WST.conf.IS_LOGIN==0){
		WST.loginWindow();
		return;
	}
	var buyNum = $("#buyNum").val()?$("#buyNum").val():1;
	$.post(WST.AU('seckill://carts/addCart'),{id:goodsInfo.seckillId,buyNum:buyNum,rnd:Math.random()},function(data,textStatus){
	     var json = WST.toJson(data);
	     if(json.status==1){
    		setTimeout(function(){
    			location.href=WST.AU('seckill://carts/settlement');
    		},1000);
	     }else{
	    	WST.msg(json.msg,{icon:2});
	     }
	});
}
