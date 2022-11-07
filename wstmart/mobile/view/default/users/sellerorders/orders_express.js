jQuery.noConflict();
function showExpressBox(obj){
    jQuery('#cover').attr("onclick","javascript:hideExpressBox();").show();
    jQuery('#expressBox').animate({"bottom": 0}, 500);
    var chooseOrderExpressId = parseInt($(obj).attr('orderExpressId'));
    var expressId = parseInt($(obj).attr('expressId'));
    $('#chooseOrderExpressId').val(chooseOrderExpressId);
    $('.express-active').each(function(idx,item){
        if(parseInt($(item).val())==expressId){
            $(item).prop('checked',true);
        }
    });
}
function hideExpressBox(){
    jQuery('#expressBox').animate({'bottom': '-100%'}, 500);
    jQuery('#cover').hide();
}
function chooseExpress(){
    var chooseOrderExpressId = parseInt($('#chooseOrderExpressId').val());
    var expressId = 0;
    var expressText = '';
    $('.express-active').each(function(idx,item){
        if($(item).prop('checked')){
            expressText = $(item).parent().parent().find('.name').html();
            expressId = $(item).val();
        }
    });
    $('.deliver-container').each(function(idx,item){
        if(parseInt($(item).attr('orderExpressId'))==chooseOrderExpressId){
            $(item).find('.expressId').val(expressId);
            $(item).find('.expressText').html(expressText);
            $(item).find('.express-menu').attr('expressId',expressId);
        }
    });
    jQuery('#expressBox').animate({'bottom': '-100%'}, 500);
    jQuery('#cover').hide();
}
//保存物流信息
function saveExpress(){
    var orderId = $('#orderId').val();
    var orderExpressIdArr = [];
    var expressIdArr = [];
    var expressNoArr = [];
    var flag = false;
    $('.orderExpressId').each(function(idx,item){
        var orderExpressId = $(item).val();
        orderExpressIdArr.push(orderExpressId);
    });
    $('.expressId').each(function(idx,item){
        var expressId = $(item).val();
        if(expressId==''){
            WST.msg(WST.lang('require_express_company'),'warn');
            flag = true;
        }
        expressIdArr.push(expressId);
    });
    $('.expressNo').each(function(idx,item){
        var expressNo = $(item).val();
        if(expressNo==''){
            WST.msg(WST.lang('please_input_logistics_order_no'),'warn');
            flag = true;
        }
        expressNoArr.push(expressNo);
    });
    if(flag)return;
    var param = {};
    param.orderId = orderId;
    param.orderExpressId = orderExpressIdArr.join(',');
    param.expressId = expressIdArr.join(',');
    param.expressNo = expressNoArr.join(',');
    $('.wst-ad-submit .button').addClass("active").attr('disabled', 'disabled');
    $.post(WST.U('mobile/orders/editOrderExpressInfo'), param, function(data){
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
