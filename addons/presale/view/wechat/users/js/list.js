jQuery.noConflict();
//获取店铺列表
function orderList(){
	$('#Load').show();
    loading = true;
    var param = {};
	param.pagesize = 10;
    param.ftype = $('#ftype').val();
	param.page = Number( $('#currPage').val() ) + 1;
    $.post(WST.AU('presale://users/pageQuery'), param,function(data){
        var json = WST.toJson(data);
        $('#currPage').val(json.current_page);
        $('#totalPage').val(json.last_page);
        var gettpl = document.getElementById('list').innerHTML;
        laytpl(gettpl).render(json.data, function(html){
            $('#goods-list').append(html);
        });
        WST.imgAdapt('j-imgAdapt');
        loading = false;
        $('#Load').hide();
        echo.init();//图片懒加载
    });
}
var currPage = totalPage = 0;
var loading = false;
$(document).ready(function(){
    if(WST.conf.IS_LOGIN==0){//是否登录
        WST.inLogin();
        return;
    }
	WST.initFooter('user');
	orderList();

    // Tab切换卡
    $('.tab-item').click(function(){
        $(this).addClass('tab-curr').siblings().removeClass('tab-curr');
        var type = $(this).attr('type');
        $('#ftype').val(type);
        $('#currPage').val('0');
        $('#goods-list').html('');
        orderList();
    });

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
            	orderList();
            }
        }
    });
});

//去支付
function choicePay(pkey){
    location.href=WST.AU('presale://users/wxpayTypes',{'pkey':pkey});
}



/*********************** 订单详情 ****************************/
//弹框
function dataShow(title){
    jQuery('#cover').attr("onclick","javascript:dataHide();").show();
    jQuery('#frame').animate({"right": 0}, 500);
    setTimeout(function(){$('#shopBox').hide();},600)
    $('#wordTitle').html(title);
}
function dataHide(){
    $('#shopBox').show();
    jQuery('#frame').animate({'right': '-100%'}, 500);
    jQuery('#cover').hide();
}


//查看订单
function toDetail(porderId){
  $.post(WST.AU('presale://users/getOrderDetail'),{porderId:porderId},function(data){
      var json = WST.toJson(data);
      if(json.status!=-1){
        var gettpl1 = document.getElementById('detailBox').innerHTML;
          laytpl(gettpl1).render(json, function(html){
            $('#content').html(html);
            // 弹出层滚动条
            var clientH = WST.pageHeight();// 屏幕高度
            var boxheadH = $('#boxTitle').height();// 弹出层标题高度
            var contentH = $('#content').height(); // 弹出层内容高度
            $('#content').css('height',clientH-boxheadH+'px');
            dataShow('订单详情');
          });
      }else{
        WST.msg(json.msg,'info');
      }
  });
}



function toPresaleGoods(id){
    location.href = WST.AU('presale://goods/wxdetail','id='+id);
}