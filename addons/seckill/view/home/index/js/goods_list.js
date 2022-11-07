var cAjax = tAjax = null;
function searchSeckill(){
	$('.content').html('<ul id="goods-list" class="goods-list"></ul>');
	$("#currPage").val(0);
	$("#totalPage").val(0);
	goodsList();
}
//获取秒杀分页商品列表
function goodsList(){
    $('.content').dropload({
      scrollArea : window,
      autoLoad : true,
      threshold: 1000,
      domDown : {//上拉
          domClass   : 'dropload-down',
          domRefresh : '<div class="dropload-refresh f15 wst-align-center"><i class="icon icon-20"></i>'+WST.lang('seckill_list_load_tips_1')+'</div>',
          domLoad    : '<div class="dropload-load f15 wst-align-center"><span class="loading"></span>'+WST.lang('seckill_list_load_tips_2')+'</div>',
          domNoData  : '<div class="dropload-noData wst-align-center">'+WST.lang('seckill_list_load_tips_3')+'</div>'
      },
      loadDownFn : function(me){
        if(cAjax)cAjax.abort();
        var keyword = $('#search-ipt').val();
        var limit =20;
        var page = Number( $('#currPage').val() ) + 1;
        var timeId = $('#timeId').val();
        var catId = $('#catId').val();
        var shopId = $('#shopId').val();
        cAjax = $.post(WST.AU('seckill://goods/pageQuery'), {
                timeId:timeId,
                catId:catId,
                page:page,
                limit:limit,
                keyword:keyword,
                shopId:shopId
        },function(data){
            var json = WST.toJson(data);
            var html = '';
            if(json && json.data && json.data.length>0){
              var gettpl = document.getElementById('list').innerHTML;
              laytpl(gettpl).render(json.data, function(html){
                  $('#goods-list').append(html);
                  $('#goods-list').append("<div class='wst-clear'></div>");
                  $('.j-lazyImg').lazyload({ effect: "fadeIn",failurelimit : 10,threshold: 200,placeholder:WST.conf.RESOURCE_PATH+'/'+WST.conf.GOODS_LOGO});
              });
              $('#currPage').val(json.current_page);
              $('#totalPage').val(json.last_page);
            }else{
              // 锁定
              me.lock();
              // 无数据
              me.noData();
            }
            // 每次数据加载完，必须重置
            me.resetload();
          });
        }
    });
}


function notice(){
	layer.open({
		type: 1,
		title: false, //不显示标题栏
		closeBtn: false,
		area: '300px;',
		shade: 0.5,
		id: 'wst_layuipro',
		btn: [WST.lang('seckill_check_new_seckill')],
		btnAlign: 'c',
		moveType: 1, //拖拽模式，0或者1
		content: '<div style="padding: 50px; line-height: 22px; background-color: #8ab2e4; color: #fff; font-weight: 300;">'+WST.lang('seckill_to_look_new_tips')+'</div>',
		yes: function(layero){
		  	location.href = WST.AU('seckill://goods/lists');
		}
	});
}

function countDown(nowTime,endTime){
	var timeId = $("#timeId").val();
  	var opts = {
	    nowTime:nowTime,
	    endTime: endTime,
	    callback: function(data){
	        if(data.last>0){
	          	$(".lab_timer_"+timeId).html('<span>'+data.hour+'</span><em>:</em><span>'+data.mini+'</span><em>:</em><span>'+data.sec+'</span>');
	        }else{
	        	notice();
	          	$(".seckill_items_timer_"+timeId).html('<strong class="status_tip">'+WST.lang('seckill_curr_status_3')+'</strong><label class="lab_timer"></label>');
	        }           
	    }
  	};
  	return WST.countDown(opts);
}

$(function(){
	$(window).scroll(function() {
		var scrollTop = $(window).scrollTop();
		var navTop = $("#wst-nav-items").offset().top;
	    if (scrollTop > navTop) {
	        $("#wst-timeline").addClass("fixed").attr("style","position: fixed; top: 0px; z-index: 998;");
	    	$(".wst-container").attr("style","margin-top:80px;");
	    }else{
	    	$("#wst-timeline").removeClass("fixed").attr("style","");
	    	$(".wst-container").attr("style","");
	    }
	});

	//头部选择 
	var timer = null;
	$('.times').click(function(){
	    if(timer)clearInterval(timer);
	    if(tAjax)tAjax.abort();
	    var vstatus = $(this).data("status");
	    var len = $('.times').length;
	    var w = $(this).width();
	    $("#currStatus").val(vstatus);
	    $('.times').removeClass("timeline_item_selected");
	    $(this).addClass("timeline_item_selected");
		if(vstatus=='0'){
			$(".status_tip").html(WST.lang('seckill_to_start'));
		}else{
			$(".status_tip").html(WST.lang('seckill_to_end'));
		}
	   	//var index = $(this).index();
	    //var width = w*(index);
	    //$('.timeline_list').attr("style","transition-duration: 200ms; transform: translate3d(-"+width+"px, 0px, 0px);")

	    $('#totalPage').val(0);//排序条件
	    $('#currPage').val(0);//当前页归零
	    var timeId = $(this).data("timeid");
	    var stime = $(this).data("stime");
	    var etime = $(this).data("etime");
	    $("#timeId").val(timeId);
	    $('.content').html('<div id="goods-list" class="goods-list"></div>');
	    tAjax = $.post(WST.AU('seckill://goods/getNowTime'),{},function(data,textStatus){
	      	var nowTime = new Date(Date.parse(data.nowTime.replace(/-/g, "/")));
	      	var startTime = new Date(Date.parse(stime.replace(/-/g, "/")));
	      	var endTime = new Date(Date.parse(etime.replace(/-/g, "/")));
	      	if(vstatus=='0'){
	        	timer = countDown(nowTime,startTime);
	      	}else{
	        	timer = countDown(nowTime,endTime);
	      	}
	    });
	    goodsList();
	});

  	$(".timeline_list .timeline_item_selected").click();
});
$('.goods').hover(function(){
	$(this).find('.sale-num').hide();
	$(this).find('.p-add-cart').show();
},function(){
	$(this).find('.sale-num').show();
	$(this).find('.p-add-cart').hide();
})
