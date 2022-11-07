function save(){
    /* 表单验证 */
    $('#feedbackForm').validator({
        fields: {
            feedbackContent: {
                rule:"required",
                msg:{required:WST.lang('require_feedback_suggestion')},
                tip:WST.lang('require_feedback_suggestion'),
                target:'#feedbackContentMsg'
            },
            feedbackType: {
                rule:"checked;",
                msg:{checked:WST.lang('require_feedback_type')},
                tip:WST.lang('require_feedback_type'),
            },
            contact: {
                rule:"required",
                msg:{required:WST.lang('require_contact_type_title')},
                tip:WST.lang('require_contact_type_title'),
            }
        },
        valid: function(form){
            var params = WST.getParams('.ipt');
            var loading = WST.msg(WST.lang('submiting_tips'), {icon: 16,time:60000});
            $.post(WST.U('home/feedbacks/add'),params,function(data,textStatus){
                layer.close(loading);
                var json = WST.toJson(data);
                if(json.status=='1'){
                    WST.msg(json.msg, {icon: 6},function(){
                        location.href = WST.U('home/feedbacks/index');
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            });
        }
    });
}