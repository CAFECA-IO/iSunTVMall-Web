// 最近会话列表
function getChatList(){
	var url = APIS['userChatList'];
	$.get(url, function(data){
		var _data = WST.imToJson(data);
		var code = [];
		if(data.status==1 && data.data && data.data.length>0){
			for(var i=0;i<data.data.length;++i){
				var _chatListItem = data.data[i];
				code.push('<li class="cl_li">');
				code.push('<div class="chk-box">');
				code.push('<i id="s_'+ _chatListItem.shopId +'" class="ui-icon-chooseg ui-icon-unchecked-s"></i></div>');
				code.push('<a href="'+moUserChatPage(_chatListItem.shopId)+'">');
				
				code.push('<div class="cl_imgbox"><img src="'+_chatListItem.shopImg+'" />');
				if(_chatListItem.unReadNum>0){
					code.push('<span class="un_read">'+_chatListItem.unReadNum+'</span>')
				}
				code.push('</div><div class="cl_infobox">');
				code.push('<p class="cl_sname"><span>'+_chatListItem.shopName+'</span>');
				code.push('<span class="fr c12_999">'+_chatListItem.createTime+'</span></p>');
				code.push('<span class="cl_desc c12_999">'+_chatListItem.content.content+'</p>');
				code.push('</div><div class="wst-clear"></div></a></li>');
			}  
			$('.cl_ul').html(code.join(''));
			bindClick();
		}else{
			$('.cl_ul').html('<div style="font-size:16px;text-align: center;margin-top: 50%;background-color:#f8f8f8">'+WST.lang('wstim_no_session_record')+'</div>');
		}
	})
}
// 进入"手机版用户聊天页"
function moUserChatPage(shopId){
	return APIS['moUserIndex']+'?shopId='+shopId;
}

function bindClick(){
	//选中
    $('.ui-icon-chooseg').click(function(){
        if( $(this).attr('class').indexOf('wst-active') == -1 ){
        	var checked = 1;
        	imChangeIconStatus($(this), 1);//选中
        }else{
        	var checked = 0;
        	imChangeIconStatus($(this), 2);//取消选中
        }
    });
}

$(function(){
	//选中合计
    $('.ui-icon-choose').click(function(){
    	imChangeIconStatus($(this), 1);
        var goodsCount = $('.ui-icon-chooseg').length;//个数
        var ids = [];
        if( $(this).attr('class').indexOf('wst-active') == -1 ){
        	//选中所有
            for(var i=0; i<goodsCount; i++){
            	imChangeIconStatus($('.ui-icon-chooseg').eq(i), 2);
                var cid = $('.ui-icon-chooseg').eq(i).attr('id');
                ids.push(cid);
			}
        }else{
        	//取消选中所有
            for(var i=0; i<goodsCount; i++){
            	imChangeIconStatus($('.ui-icon-chooseg').eq(i), 2, 'wst-active');
                var cid = $('.ui-icon-chooseg').eq(i).attr('id');
                ids.push(cid);
            }
		}
    });
})

//变换选中框的状态
function imChangeIconStatus(obj, toggle, status){
    if(toggle==1){
        if( obj.attr('class').indexOf('ui-icon-unchecked-s') > -1 ){
            obj.removeClass('ui-icon-unchecked-s').addClass('ui-icon-success-block wst-active');
        }else{
            obj.removeClass('ui-icon-success-block wst-active').addClass('ui-icon-unchecked-s');
        }
    }else if(toggle==2){
        if(status == 'wst-active'){
            obj.removeClass('ui-icon-unchecked-s').addClass('ui-icon-success-block wst-active');
        }else{
            obj.removeClass('ui-icon-success-block wst-active').addClass('ui-icon-unchecked-s');
        }
    }
}
// 删除
function del(){
	var ids = [];
    $('.ui-icon-chooseg').each(function(item, k){
        if( $(this).attr('class').indexOf('wst-active') != -1 ){
			ids.push($(this).attr('id'));
        }
	});
	if(ids.length==0)return layer.msg(WST.lang('wstim_tips16'));
	//询问框
	layer.open({
		content: WST.lang('wstim_tips15')
		,btn: [WST.lang('wstim_ok'), WST.lang('wstim_cancel')]
		,yes: function(index){
		  var url = APIS['delDialog'];
		  $.post(url,{ids:ids.join(',')},function(res){
			layer.close(index);
			var json = WST.imToJson(res);
			if(json.status==1){
				ids.map(function(x){
					$('#'+x).parent().parent().remove();
				})
			}else{
				layer.msg(json.msg);
			}
		  })
		  
		}
	  });
}