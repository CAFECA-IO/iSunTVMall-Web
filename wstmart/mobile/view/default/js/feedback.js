jQuery.noConflict();
/*********************** 反馈问题类型 ****************************/
//弹框
function dataShow(){
    jQuery('#cover').attr("onclick","javascript:dataHide(0);").show();
    jQuery('#frame').animate({"bottom": 0}, 500);
}
function dataHide(type){
	if(type==1){
	    var flag=false,chk;
	    $('.active').each(function(k,v){
	      if($(this).prop('checked')){
	        flag = true
	        $('#feedbackType').val($(this).val());
	        chk = $(this).parent().siblings().html();
	      }
	    });
	    if(!flag){
	      WST.msg(WST.lang('require_feedback_type'));
	      return;
	    }
	    $('#complainText').html(chk);
	}
    jQuery('#frame').animate({'bottom': '-100%'}, 500);
    jQuery('#cover').hide();
}

function save(){
  // 验证反馈问题类型
  var type = $('#feedbackType').val();
  if(type==''){
    dataShow();
    WST.msg(WST.lang('require_feedback_type'),'info');
    return;
  }
  var feedbackContent = $.trim($('#feedback').val());
  if(feedbackContent==''){
    WST.msg(WST.lang('require_feedback_suggestion'),'info');
    return;
  }
  var contact = $.trim($('#contact').val());
  if(contact == ''){
      WST.msg(WST.lang('require_contact_type_title'),'info');
      return;
  }
  var param = {};
  param.feedbackType = type;
  param.feedbackContent = feedbackContent;
  param.contactInfo = contact;
  $.post(WST.U('mobile/feedbacks/add'),param,function(data){
    var json = WST.toJson(data);
    if(data.status==1){
      WST.msg(json.msg,'success');
      setTimeout(function(){location.href=WST.U('mobile/users/index')},1000);
    }else{
      WST.msg(json.msg,'info');
    }
  });
}