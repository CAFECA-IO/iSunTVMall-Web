var cAjax = tAjax = null;
var currPage = totalPage = 0;
function searchGoods(){
  $(".nav-item").removeClass('js-show');
  $(".nav-item .block").removeClass("block");
  $(".wst-mask").hide();
  $(".sort").removeClass("onsort");
  $(".choice").removeClass("active");
  $('#totalPage').val(0);//排序条件
  $('#currPage').val(0);//当前页归零
  $('.content').html('<div id="appr-list" class="appr-list appr-items"></div>');
  goodsList();
}
//获取商品列表

function goodsList(){
    $('.content').dropload({
      scrollArea : window,
      autoLoad : true,
      threshold: 100,
      domDown : {//上拉
          domClass   : 'dropload-down',
          domRefresh : '<div class="dropload-refresh f15 "><i class="icon icon-20"></i>上拉加载更多</div>',
          domLoad    : '<div class="dropload-load f15"><span class="weui-loading"></span>正在加载中...</div>',
          domNoData  : '<div class="dropload-noData" style="text-align:center;">没有更多商品了</div>'
      },
      loadDownFn : function(me){
        if(cAjax)cAjax.abort();
        var keyword = $('#keyword').val();
        var pagesize =10;
        var page = Number( $('#currPage').val() ) + 1;
        var timeId = $('#timeId').val();
        var shopId = $('#shopId').val();
        cAjax = $.post(WST.AU('seckill://goods/pageQuery'), {
                timeId:timeId,
                page:page,
                pagesize:pagesize,
                keyword:keyword,
                shopId:shopId
        },function(data){
            var json = WST.toJson(data);
            var html = '';
            if(json && json.data && json.data.length>0){
              var gettpl = document.getElementById('list').innerHTML;
              laytpl(gettpl).render(json.data, function(html){
                  $('#goods-list').append(html);
              });
              $('#currPage').val(json.current_page);
              $('#totalPage').val(json.last_page);
            }else{
              // 锁定
              me.lock();
              // 无数据
              me.noData();
            }
            echo.init();//图片懒加载
            // 每次数据加载完，必须重置
            me.resetload();
          });
        }
    });
}

function countDown(nowTime,endTime){
  var opts = {
    nowTime:nowTime,
    endTime: endTime,
    callback: function(data){
        if(data.last>0){
          // html.push(data.hour+"小时"+data.mini+"分"+data.sec+"秒");
          $(".lab_timer").html('<span>'+data.hour+'</span><em>:</em><span>'+data.mini+'</span><em>:</em><span>'+data.sec+'</span>');
        }else{
          $(".seckill_items_timer").html('<strong class="status_tip">已结束</strong><label class="lab_timer"></label>');
          location.href = WST.AU('seckill://goods/wxlists');
        }           
    }
  };
  return WST.countDown(opts);
}

$(document).ready(function(){
  //头部选择 
  var timer = null;
  $('.times').click(function(){
    if(timer)clearInterval(timer);
    if(tAjax)tAjax.abort();
    var vstatus = $(this).data("status");
    if(vstatus=='0'){
      $(".seckill_items_tit").html("即将开场 先下单先得哦");
      $(".status_tip").html("距开始");
    }else{
      $(".seckill_items_tit").html("抢购中 先下单先得哦");
      $(".status_tip").html("距结束");
    }
    $("#currStatus").val(vstatus);
    $('.times').removeClass("active");
    $(this).addClass("active");
    var w = $(this).width();
    var index = parseInt($(this).data("key"),10);
        index = (index>2)?index:0
    var width = w*(index-2);
    //$('.ui-tab-nav').animate({scrollLeft: width+'px'}, 1000);
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

  $(".ui-tab-nav .curr-item").click();

});