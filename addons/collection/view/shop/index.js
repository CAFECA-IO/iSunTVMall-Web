
function save(){
    var params = WST.getParams('.j-ipt');
        params.goodsCatId = WST.ITGetGoodsCatVal('j-goodsCats');
    if(!params.goodsCatId){
      WST.msg(WST.lang('collection_tip1'),{icon:2});return;
    }
    if(params.goodsUrl==""){
      WST.msg(WST.lang('collection_tip2'),{icon:2});return;
    }
    var loading = WST.msg(WST.lang('collection_loading'), {icon: 16,time:60000});
    $.post(WST.U('addon/collection-collection-save'),params,function(data,textStatus){
      layer.close(loading);
      var json = WST.toJson(data);
      if(json.status=='1'){
        WST.msg(json.msg,{icon:1});
      }else{
        WST.msg(json.msg,{icon:2});
      }
    });
}