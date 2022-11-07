jQuery.noConflict();
//地址选择
function inOption(obj,n){
    $(obj).addClass('active').siblings().removeClass('active');
    $('.area_'+n).removeClass('hide').siblings('.list').addClass('hide');
    var level = $('#level').val();
    var n = n+1;
    for(var i=n; i<=level; i++){
        $('.area_'+i).remove();
        $('.active_'+i).remove();
    }
}
function inChoice(obj,id,val,level){
    $('#level').val((level+1));
    $(obj).addClass('active').siblings().removeClass('active');
    $('#'+id).attr('areaId',val);
    $('.active_'+level).removeClass('active').html($(obj).html());
    WST.ITAreas({id:id,val:val,className:'j-areas'});
}
/**
 * 循环创建地区
 * @param id            当前分类ID
 * @param val           当前分类值
 * @param className     样式，方便将来获取值
 */
WST.ITAreas = function(opts){
    opts.className = opts.className?opts.className:"j-areas";
    var obj = $('#'+opts.id);
    obj.attr('lastarea',1);
    $.post(WST.U('mobile/areas/listQuery'),{parentId:opts.val},function(data,textStatus){
        var json = WST.toJson(data);
        if(json.data && json.data.length>0){
            json = json.data;
            var html = [],tmp;
            var tid = opts.id+"_"+opts.val;
            var level = parseInt(obj.attr('level'),10);
            $('.area_'+level).addClass('hide');
            var level = level+1;
            html.push('<div id="'+tid+'" class="list '+opts.className+' area_'+level+'" areaId="0" level="'+level+'">');
            for(var i=0;i<json.length;i++){
                tmp = json[i];
                html.push("<p onclick='javascript:inChoice(this,\""+tid+"\","+tmp.areaId+","+level+");'>"+tmp.areaName+"</p>");
            }
            html.push('</div>');
            $(html.join('')).insertAfter('#'+opts.id);
            var h = WST.pageHeight();
            var listh = h/2-106;
            $(".wst-fr-box .list").css('overflow-y','scroll').css('height',listh+'px');
            $(".wst-fr-box .option").append('<p class="ui-nowrap-flex term active_'+level+' active" onclick="javascript:inOption(this,'+level+')">'++WST.lang('please_select')++'</p>');
        }else{
            opts.isLast = true;
            opts.lastVal = opts.val;
            $('#areaId').val(opts.lastVal);
            var ht = '';
            $('.wst-fr-box .term').each(function(){
                ht += $(this).html();
            });
            $('#addresst').html(ht);
            dataHide();
        }
    });
}
//弹框
function dataShow(){
    jQuery('#frame').show();
    jQuery('#cover').attr("onclick","javascript:dataHide();").show();
    jQuery('#frame').animate({"bottom": 0}, 500);
}
function dataHide(){
    var dataHeight = $("#frame").css('height');
    jQuery('#frame').animate({'bottom': '-'+dataHeight}, 500);
    jQuery('#cover').hide();
    setTimeout(function(){
        jQuery('#frame').hide();
    },500);
}
//保存收货地址
function saveAddress(){
    var orderId = $('#orderId').val();
    var userName = $('#username').val();
    var userPhone = $('#cellphone').val();
    var areaId = $('#areaId').val();
    var userAddress = $('#address_detailed').val();
    if(userName==''){
        WST.msg(WST.lang('require_consignee'),'info');
        return false;
    }
    if(userPhone==''){
        WST.msg(WST.lang('require_contact_number'),'info');
        return false;
    }
    if(areaId==''){
        WST.msg(WST.lang('require_address'),'info');
        return false;
    }
    if(userAddress==''){
        WST.msg(WST.lang('require_detail_address'),'info');
        return false;
    }
    var param = {};
    param.orderId = orderId;
    param.userName = userName;
    param.areaId = areaId;
    param.userPhone = userPhone;
    param.userAddress = userAddress;
    $('.wst-ad-submit .button').addClass("active").attr('disabled', 'disabled');
    $.post(WST.U('mobile/orders/editOrderReceiveInfo'), param, function(data){
        var json = WST.toJson(data);
        if( json.status == 1 ){
            WST.msg(json.msg,'success');
            setTimeout(function(){
                location.href = WST.U('mobile/orders/sellerorder','type='+$('#type').val());
            },1500);
        }else{
            WST.msg(json.msg,'warn');
            setTimeout(function(){
                $('.wst-ad-submit .button').removeAttr('disabled').removeClass("active");
            },1500);
        }
        data = json = null;
    });
}
