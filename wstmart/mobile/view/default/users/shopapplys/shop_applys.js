jQuery.noConflict();

function save(){
  var linkPhone = $.trim($('#linkPhone').val());
  if(linkPhone==''){
    WST.msg(WST.lang('require_contact_type_title'),'info');
    return;
  }
  var linkman = $.trim($('#linkman').val());
  if(linkman==''){
    WST.msg(WST.lang('please_input_linkman'),'info');
    return;
  }
  var applyIntention = $.trim($('#applyIntention').val());
  if(applyIntention == ''){
      WST.msg(WST.lang('require_business_scope'),'info');
      return;
  }
  var param = {};
  param.linkman = linkman;
  param.linkPhone = linkPhone;
  param.applyIntention = applyIntention;
  $.post(WST.U('mobile/shopapplys/add'),param,function(data){
    var json = WST.toJson(data);
    if(data.status==1){
      location.reload();
    }else{
      WST.msg(json.msg,'info');
    }
  });
}