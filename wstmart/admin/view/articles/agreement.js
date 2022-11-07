function saveAgreement(){
    var params = WST.getParams('.ipt');
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/articles/editUserAgreement'),params,function(data,textStatus){
          layer.close(loading);
          var json = WST.toAdminJson(data);
          if(json.status=='1'){
                WST.msg(json.msg,{icon:1});
          }else{
                WST.msg(json.msg,{icon:2});
          }
    });
}