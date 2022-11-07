var WST_CURR_PAGE = 1;
function queryByList(p){
     var params = {};
     params.page = p;
     var load = WST.load({msg:WST.lang('loading_tips')})
     $.post(WST.U('home/Messages/pageQuery'),params,function(data,textStatus){
    	   layer.close(load);
         var json = WST.toJson(data);
	       if(json.data){
		        json = json.data;
            if(params.page>json.last_page && json.last_page >0){
               queryByList(json.last_page);
               return;
            }
	          var gettpl = document.getElementById('msg').innerHTML;
	          //复选框为未选中状态
	          $('#all').attr('checked',false);
	          laytpl(gettpl).render(json.data, function(html){
	             $('#msg_box').html(html);
	          });
            laypage({
              cont: 'wst-page', 
              pages:json.last_page, 
              curr: json.current_page,
              skin: '#e23e3d',
              groups: 3,
              jump: function(e, first){
                  if(!first){
                      queryByList(e.curr);
                  }
              } 
            });
              
	        }
  });
}

function showMsg(id){
  location.href=WST.U('home/messages/showMsg','msgId='+id+'&p='+WST_CURR_PAGE);
}

function delMsg(obj,id){
WST.confirm({content:WST.lang('confirm_del_inquiry'), yes:function(tips){
  var ll = WST.load({msg:WST.lang('submiting_tips')});
  $.post(WST.U('home/messages/del'),{id:id},function(data,textStatus){
    layer.close(ll);
      layer.close(tips);
    var json = WST.toJson(data);
    if(json.status=='1'){
      WST.msg(WST.lang('operation_success'), {icon: 1}, function(){
         queryByList(WST_CURR_PAGE);
      });
    }else{
         WST.msg(WST.lang('operation_fail'), {icon: 5});
    }
  });
}});
}
function batchDel(){
    var ids = WST.getChks('.chk');
    if(ids==''){
      WST.msg(WST.lang('please_select_need_del_message'), {icon: 5});
      return;
    }
    WST.confirm({content:WST.lang('confirm_del_inquiry'), yes:function(tips){
        var params = {};
        params.ids = ids;
        var load = WST.load({msg:WST.lang('submiting_tips')});
        $.post(WST.U('home/messages/batchDel'),params,function(data,textStatus){
          layer.close(load);
          var json = WST.toJson(data);
          if(json.status=='1'){
            WST.msg(WST.lang('operation_success'),{icon:1},function(){
                 queryByList(WST_CURR_PAGE);
            });
          }else{
            WST.msg(WST.lang('operation_fail'),{icon:5});
          }
        });
    }});
}
function batchRead(){
    var ids = WST.getChks('.chk');
    if(ids==''){
      WST.msg(WST.lang('please_select_message'), {icon: 5});
      return;
    }
    WST.confirm({content:WST.lang('message_confirm_mark_read'), yes:function(tips){
        var params = {};
        params.ids = ids;
        var load = WST.load({msg:WST.lang('submiting_tips')});
        $.post(WST.U('home/messages/batchRead'),params,function(data,textStatus){
          layer.close(load);
          var json = WST.toJson(data);
          if(json.status=='1'){
            WST.msg(WST.lang('operation_success'),{icon:1},function(){
                 queryByList(WST_CURR_PAGE);
            });
          }else{
            WST.msg(WST.lang('operation_fail'),{icon:5});
          }
        });
    }});
}
