function goList(){
    location.href=WST.AU('txlive://txlives/molists');
}
function favorite(shopId,obj) {
    var url = 'txlive://txlive/txlives/';
    var isFollow = $(obj).attr('isFollow');
    var param = {};
    param.type = 1;
    if (isFollow > 0) {
        param.id = isFollow;
        url += 'moFavoritesCancel';
    } else if (isFollow <= 0) {
        param.id = shopId;
        url += 'moFavoritesAdd';
    }
    $.post(WST.AU(url), param,function(data){
        var json = WST.toJson(data);
        if (json.status == 1) {
            WST.msg(json.msg,'success');
            checkFavorite(shopId);
        } else {
            WST.msg(json.msg,'info');
        }
    });
}
function checkFavorite(shopId){
    var param = {};
    param.shopId = shopId;
    $.post(WST.AU('txlive://txlive/txlives/moCheckFavorite'), param,function(data){
        var json = WST.toJson(data);
        if (json.status == 1) {
            var isFollow = json.data.isFollow;
            $(".follow").attr("isFollow",isFollow);
            if(isFollow==0){
                $(".follow").html('关注');
            }else{
                $(".follow").html('已关注');
            }
        }
    });
}
function goodShow(){
    $("#frame-good").empty();
    var param = {};
    param.roomId = $('#roomId').val();
    $.post(WST.AU('txlive://txlive/txlives/moGetLiveGoods'), param,function(data){
        var json = WST.toJson(data);
        if (json.status == 1) {
            var gettpl = document.getElementById('list').innerHTML;
            laytpl(gettpl).render(json.data, function(html){
                $('#frame-good').append(html);
            });
            $('.shopping-num').html(json.data.length);
            jQuery('#cover').attr("onclick","javascript:goodHide();").show();
            jQuery('#frame-good').animate({"bottom": 0}, 500);
        }
    });
}
function goodHide(){
    jQuery('#frame-good').animate({'bottom': '-100%'}, 500);
    jQuery('#cover').hide();
}
