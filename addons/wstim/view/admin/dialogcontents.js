
// 点击加载更多聊天记录
function clickMore(obj){
    var p = $(obj).find('p');
    if(!p.hasClass('loading')){
        $(obj).find('.txt').html(WST.lang('wstim_click_to_load_more_chats'));
        p.addClass('loading');
    }
    var userId = $('#userId').val();
    var shopId = $('#shopId').val();
    var currPage = $('#currPage').val();
    var totalPage = $('#totalPage').val();
    if(parseInt(currPage)+1>totalPage){
        $(obj).find('.txt').html(WST.lang('wstim_there_is_no_chat_record'));
        p.removeClass('loading');
        return;
    }
    var params = {
        id:userId,
        shopId:shopId,
        page:parseInt(currPage)+1,
    };
    var url = APIS['adminDialogContentQuery'];
    $.post(url,params,function(data){
        var loginName = $('#loginName').val();
        $('#currPage').val(data.current_page);
        $('#totalPage').val(data.last_page);
        var rows = data.data;
        var code = [];
        for(var i in rows){
            var _obj = rows[i];
            dealContent(_obj);
            if(_obj.from!=userId){
                code.push(
                    '<div class="dialog_content_item">',
                      '<div class="send_box">',
                         '<p class="reply_name">'+_obj.userName+'：</p>',
                         '<div class="dcs_content dcs_send">',
                             _obj.content,
                         '</div>',
                         '<span class="msg_send_time">'+_obj.createTime+'</span>',
                      '</div>',
                   '</div>'
                      );
            }else{
                code.push(
                    '<div class="dialog_content_item">',
                      '<div class="reply_box">',
                         '<p class="reply_name">'+_obj.userName+'：</p>',
                         '<div class="dcr_content">',
                           _obj.content,
                         '</div>',
                         '<span class="msg_reply_time">'+_obj.createTime+'</span>',
                      '</div></div>'
                        );
            }
        }
        if(rows && rows.length>0){
            code.unshift('<div class="chat-more tc" id="clickMore" onclick="clickMore(this)"><p class=""><span class="icon"></span><span class="txt">'+WST.lang('wstim_click_to_load_more')+'</span></p></div>');
            $('#scrollDiv').prepend(code.join(''));
            $('#scrollDiv').scrollTop(0); // 滚动到顶部
            $(obj).remove();
        }else{
            $(obj).find('.txt').html(WST.lang('wstim_there_is_no_chat_record'));
            p.removeClass('loading');
        }
    },'json');
}