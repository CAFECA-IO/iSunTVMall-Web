var cAjax = tAjax = null;
var currPage = totalPage = 0;
var liveStatus = {
    101: "直播中",
    102: "未开始",
    103: "直播结束",
    104: "禁播",
    105: "暂停中",
    106: "异常",
    107: "已过期"
}
function searchRooms(){
  $(".nav-item").removeClass('js-show');
  $(".nav-item .block").removeClass("block");
  $(".wst-mask").hide();
  $(".sort").removeClass("onsort");
  $(".choice").removeClass("active");
  $('#totalPage').val(0);//排序条件
  $('#currPage').val(0);//当前页归零
  $('.content').html('<div id="rooms-list" class="rooms-list"></div>');
  roomsList();
}
//获取直播间列表
function roomsList(){
    $('#Load').show();
    loading = true;
    var param = {};
    param.keyword = $('#keyword').val();
    param.pagesize = 10;
    param.page = Number( $('#currPage').val() ) + 1;
    $.post(WST.AU('txlive://txlives/moPageQuery'), param,function(data){
        var json = WST.toJson(data);
        $('#currPage').val(json.data.current_page);
        $('#totalPage').val(json.data.last_page);
        var gettpl = document.getElementById('list').innerHTML;
        laytpl(gettpl).render(json.data.data, function(html){
            $('#rooms-list').append(html);
        });
        if(json.data.data.length>0){
            if(json.data.current_page>=json.data.last_page){
                $('.wst-load-text').html('加载完啦');
            }else{
                $('.wst-load-text').html('加载中');
            }
        }else{
            $('.wst-load-text').html('');
        }
        WST.imgAdapt('j-imgAdapt');
        loading = false;
        $('#Load').hide();
        echo.init();//图片懒加载
    });
}

$(document).ready(function(){
    $('#totalPage').val(0);//排序条件
    $('#currPage').val(0);//当前页归零
    $('.content').html('<div id="rooms-list" class="rooms-list"></div>');
    roomsList();
    $(window).scroll(function(){
        if (loading) return;
        if ((5 + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
            currPage = Number( $('#currPage').val() );
            totalPage = Number( $('#totalPage').val() );
            if( totalPage > 0 && currPage < totalPage ){
                roomsList();
            }
        }
    });
});
function goRoom(id){
    location.href=WST.AU('txlive://txlives/modetail','roomId='+id);
}
