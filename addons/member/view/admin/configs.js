function addRecommendItem(){
    var html = '';
    html += "<div class='recommend-item'>";
    html += WST.lang('addon_member_admin_invitate');
    html += "<input type='text' class='recommendNum' value='' maxLength='5' style='width:60px;' onkeyup='javascript:WST.isChinese(this,1)' onkeypress='return WST.isNumberKey(event)'/>";
    html += WST.lang('addon_member_admin_invitate1');
    html += "<input type='text' class='recommendScore' value='' maxLength='5' style='width:60px;' onkeyup='javascript:WST.isChinese(this,1)' onkeypress='return WST.isNumberKey(event)'/>";
    html += WST.lang('addon_member_admin_invitate2');
    html += "<button type='button' class='btn btn-success del-btn' onclick='delRecommendItem(this)'><i class='fa fa-trash-o'></i>"+WST.lang('addon_member_admin_del')+"</button>";
    html += "</div>";
    $('.recommend-item-container').append(html);
}

function delRecommendItem(obj){
    $(obj).parent().remove();
}

function edit(){
    if(!WST.GRANT.MEMBER_YXSZ_02)return;
    var recommendNum = [];
    var recommendScore = [];
    var flag = false;
    var params = WST.getParams('.ipt');
    if(params.recommendSwitch==1){
        $(".recommendNum").each(function(idx,item){
            var num = $(item).val();
            if(num==''){
                WST.msg(WST.lang('addon_member_require_invitate_num'),{icon:2});
                flag = true;
            }
            recommendNum.push(num);
        });
        $(".recommendScore").each(function(idx,item){
            var score = $(item).val();
            if(score==''){
                WST.msg(WST.lang('addon_member_require_invitate_score'),{icon:2});
                flag = true;
            }
            recommendScore.push(score);
        });
    }
    if(params.registerSwitch==1 && params.registerScore==''){
        WST.msg(WST.lang('addon_member_require_regist_score'),{icon:2});
        flag = true;
    }
    if(flag)return;
    params.recommendNum = recommendNum;
    params.recommendScore = recommendScore;
    var loading = WST.msg(WST.lang('addon_member_loading'), {icon: 16,time:60000});
    $.post(WST.AU('member://admin/edit'),params,function(data,textStatus){
        layer.close(loading);
        var json = WST.toAdminJson(data);
        if(json.status==1){
            WST.msg(json.msg,{icon:1});
        }
    });
}