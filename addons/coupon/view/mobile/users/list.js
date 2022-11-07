jQuery.noConflict();
var cAjax = null;
var currPage = totalPage = 0;
var loading = false;
$(document).ready(function(){
  getcouponList();
  // Tab切换卡
  $('.tab-item').click(function(){
      $(this).addClass('tab-curr').siblings().removeClass('tab-curr');
      var status = $(this).attr('status');
      $('#status').val(status);
      reFlashList();
  });
    $(window).scroll(function(){  
        if (loading) return;
        if ((5 + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
            currPage = Number( $('#currPage').val() );
            totalPage = Number( $('#totalPage').val() );
            if( totalPage > 0 && currPage < totalPage ){
                getcouponList();
            }
        }
    });
});

// 获取优惠券列表
function getcouponList(){
  if(cAjax)cAjax.abort();
  $('#Load').show();
    loading = true;
    var param = {};
    param.status = $('#status').val();
    param.pagesize = 10;
    param.page = Number( $('#currPage').val() ) + 1;
    cAjax = $.post(WST.AU('coupon://users/pageQuery'), param, function(data){
        var json = WST.toJson(data);
        var html = '';
        if(json && json.data && json.data.length>0){
          var gettpl = document.getElementById('shopList').innerHTML;
          laytpl(gettpl).render(json.data, function(html){
            $('#order-box').append(html);
          });

          $('#currPage').val(json.current_page);
          $('#totalPage').val(json.last_page);
        }else{
			    html += '<div class="no-data-box">';
          html += '<div class="no-data"></div>';
          html += '<div class="no-data-txt">'+WST.lang('coupon_no_data')+'</div>';
          html += '</div>';
			    $('#order-box').html(html);
        }
        WST.imgAdapt('j-imgAdapt');
        loading = false;
        $('#Load').hide();
        echo.init();//图片懒加载
        cAjax = null;
    });
}
// 刷新列表页
function reFlashList(){
  $('#currPage').val('0');
  $('#order-box').html(' ');
  getcouponList();
}
// 使用优惠券
function useCoupon(couponId){
  location.href = WST.AU('coupon://coupons/moCouponGoods',{'couponId':couponId});
}
