var expressType = 0;
$(function(){
})

function goBack(){
    location.href = WST.U('mobile/orderservices/detail',{id:$('#id').val()});
}
// 快递方式
function deliverType(val){
    expressType = val;
    if(val==0){
        $('#j-express').hide();
    }else{
        $('#j-express').show();
    }
}
// 用户发货
function userExpress(){
    var params = {
        expressId:$('#expressId').val(),
        expressNo:$('#expressNo').val(),
        expressType:expressType
    }

    if(params.expressType==1){
        if(!params.expressId || params.expressId==0){
            return WST.msg(WST.lang('require_logistics_company'));
        }
        // 需要快递
        if(!params.expressNo || params.expressNo.length==0){
            return WST.msg(WST.lang('please_input_logistics_order_no'));
        }
    }
    params.id = $('#id').val();
    $.post(WST.U('mobile/orderservices/userExpress'),params,function(res){
        var json = WST.toJson(res);
        WST.msg(res.msg);
        if(json.status==1){
            goBack();
        }
    });
}