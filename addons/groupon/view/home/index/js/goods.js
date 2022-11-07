$(function(){
	WST.dropDownLayer(".item",".dorp-down-layer");
	$('.item-more').click(function(){
		if($(this).attr('v')==1){
			$('.hideItem').show(300);
			$(this).find("span").html(WST.lang('group_pack_up'));
			$(this).find("i").attr({"class":"drop-up"});
			$(this).attr('v',0);
		}else{
			$('.hideItem').hide(300);
			$(this).find("span").html(WST.lang('group_more_item'));
			$(this).find("i").attr({"class":"drop-down-icon"});
			$(this).attr('v',1);
		}
	});
	$(".gallery-img").mouseenter(function(){
		$(".gallery-li").removeClass("hover");
		$(this).parent().addClass('hover');
		if($(this).hasClass('gvideo')){
			document.getElementById('previewVideo').play();
			$(".wst-video-box").show();
		}else{
			$(".wst-video-box").hide();
		}
	});
	$(".item-more").hover(function(){
		if($(this).find("i").hasClass("drop-down-icon")){
			$(this).find("i").attr({"class":"down-hover"});
		}else{
			$(this).find("i").attr({"class":"up-hover"});
		}
		
	},function(){
		if($(this).find("i").hasClass("down-hover")){
			$(this).find("i").attr({"class":"drop-down"});
		}else{
			$(this).find("i").attr({"class":"drop-up"});
		}
	});
	//图片放大镜效果
	CloudZoom.quickStart();
	imagesMove({id:'.goods-pics',items:'.items'});
	$('#tab').TabPanel({tab:0,callback:function(no){
		if(no==1)queryByPage();
	}});
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
				    if(data.day>0)html.push(data.day+WST.lang('group_day'));
				    html.push(data.hour+WST.lang('group_hour')+data.mini+WST.lang('group_minute')+data.sec+WST.lang('group_second'));
				    g.html(WST.lang('group_start_time_tips',[html.join('')]));
				    $('#addCart2').addClass('un-buy').attr('href','javascript:void(0)');
			    }else{
			    	var opts2 = {
	                    nowTime: data.nowTime,
						endTime: endTime,
						callback: function(data2){
						    if(data2.last>=0){
						    	var html = [];
							    if(data2.day>0)html.push(data2.day+WST.lang('group_day'));
							    html.push(data2.hour+WST.lang('group_hour')+data2.mini+WST.lang('group_minute')+data2.sec+WST.lang('group_second'));
							    g.html(WST.lang('group_surplus')+html.join(''));
							    $('#buyBtn').removeClass('un-buy').attr('href','javascript:addCart(1,"#buyNum")');
                                $('#addCart2').removeClass('un-buy').attr('href','javascript:addCart(1,"#buyNum")');
						    }else{
						    	g.html(WST.lang('group_finished'));
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
				    if(data.day>0)html.push(data.day+WST.lang('group_day'));
				    html.push(data.hour+WST.lang('group_hour')+data.mini+WST.lang('group_minute')+data.sec+WST.lang('group_second'));
				    g.html(WST.lang('group_surplus')+html.join(''));
			    }else{
			    	$('#buyBtn').addClass('un-buy').attr('href','javascript:void(0)');
			    	$('#addCart2').addClass('un-buy').attr('href','javascript:void(0)');
			    	g.html(WST.lang('group_finished'));
			    }			    	
			}
		};
		WST.countDown(opts);
    }else{
        $('#buyBtn').addClass('un-buy').attr('href','javascript:void(0)');
        $('#addCart2').addClass('un-buy').attr('href','javascript:void(0)');
        g.html(WST.lang('group_finished'));
    }
    fixedbar();
});
function 
fixedbar(){
    var offsetTop = $("#goodsTabs").offset().top;  
    $(window).scroll(function() {  
        var scrollTop = $(document).scrollTop();  
        if (scrollTop > offsetTop){  
        	$('#addCart2').show();
            $("#goodsTabs").css("position","fixed");  
        }else{  
        	$('#addCart2').hide();
            $("#goodsTabs").css("position", "static");  
        }  
    });   
}
function imagesMove(opts){
	var tempLength = 0; //临时变量,当前移动的长度
	var viewNum = 5; //设置每次显示图片的个数量
	var moveNum = 2; //每次移动的数量
	var moveTime = 300; //移动速度,毫秒
	var scrollDiv = $(opts.id+" "+opts.items+" ul"); //进行移动动画的容器
	var scrollItems = $(opts.id+" "+opts.items+" ul li"); //移动容器里的集合
	var moveLength = scrollItems.eq(0).width() * moveNum; //计算每次移动的长度
	var countLength = (scrollItems.length - viewNum) * scrollItems.eq(0).width(); //计算总长度,总个数*单个长度
	  
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


/****************** 商品评价 ******************/
function apprfilter(type){
    $('.appr-filterbox li a').removeClass('curr');
    $('#filtertype').val(type);
    queryByPage(1);
}
var _first=true;
function showImg(id){
  layer.photos({
      photos: '#img-file-'+id
    });
}
function queryByPage(p){
  var params = {};
  params.page = p;
  params.goodsId = goodsInfo.id;
  params.anonymous = 1;
  params.type = $('#filtertype').val();
  $.post(WST.U('home/goodsappraises/getById'),params,function(data,textStatus){
      var json = WST.toJson(data);
      if(json.status==1 && json.data.data){
          var gettpl = document.getElementById('tblist').innerHTML;
          laytpl(gettpl).render(json.data.data, function(html){
            $('#ga-box').html(html);
            for(var g=0;g<=json.data.data.length;g++){
              showImg(g);
            }
          });
          //  各评价数.
          $('#totalNum').html(json.data.sum);
          $('#bestNum').html(json.data.bestNum);
          $('#goodNum').html(json.data.goodNum);
          $('#badNum').html(json.data.badNum);
          $('#picNum').html(json.data.picNum);
          // 选中当前筛选条件
          $('#'+params.type).addClass('curr');
          if(_first && json.data.sum>0){
              // 好、中、差评率
              var best = parseInt(json.data.bestNum/json.data.sum*100);
              var good = parseInt(json.data.goodNum/json.data.sum*100);
              var bad = 100-best-good;
              $('.best_percent').html(best);
              $('.good_percent').html(good);
              $('.bad_percent').html(bad);
              // 背景色
              $('#best_percentbg').css({width:best+'%'});
              $('#good_percentbg').css({width:good+'%'});
              $('#bad_percentbg').css({width:bad+'%'});
              _first = false;
          }
          $('.j-lazyImg').lazyload({ effect: "fadeIn",failurelimit : 10,threshold: 200,placeholder:WST.conf.RESOURCE_PATH+'/'+WST.conf.GOODS_LOGO});
          $('.apprimg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 100,placeholder:window.conf.RESOURCE_PATH+'/'+window.conf.USER_LOGO});//会员默认头像
           laypage({
               cont: 'pager', 
               pages:json.data.last_page, 
               curr: json.data.current_page,
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
function addCart(type,iptId){
	if(WST.conf.IS_LOGIN==0){
		WST.loginWindow();
		return;
	}
	var buyNum = $(iptId)[0]?$(iptId).val():1;
	$.post(WST.AU('groupon://carts/addCart'),{id:goodsInfo.grouponId,buyNum:buyNum,rnd:Math.random()},function(data,textStatus){
	     var json = WST.toJson(data);
	     if(json.status==1){
	    	 WST.msg(json.msg,{icon:1},function(){
	    	 	location.href=WST.AU('groupon://carts/settlement');
	    	 });
	     }else{
	    	 WST.msg(json.msg,{icon:2});
	     }
	});
}