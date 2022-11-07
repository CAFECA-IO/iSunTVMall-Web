var time = 0;
var isSend = false;
var myorm;
var emailForm;
var phoneForm;
var getemailForm;
var getphoneForm;
$(function(){
    $('#phoneVerify').validator({
      valid: function(form){
    	  var n=$('#VerifyId').val();
    	  getPhoneVerifys(n);
      }
    });
})
function vePayForm(){
	//修改密码
	myorm = $('#payform').validator({
          valid: function(form){
            var params = WST.getParams('.ipt');
            var loading = WST.msg(WST.lang('ucenter_loading'), {icon: 16,time:60000});
            $.post(WST.U('home/users/payPassEdit'),params,function(data,textStatus){
              layer.close(loading);
              var json = WST.toJson(data);
              if(json.status=='1'){
                  WST.msg(json.msg,{icon:1,time:2000},function(){
                	   location.href=WST.U('home/users/security');
                  });
              }else{
                    WST.msg(json.msg,{icon:2});
              }
            });
      }
    })
}
function veMyorm(){
  //修改密码
  myorm = $('#myorm').validator({
            fields: {
                newPass: {
                  rule:"required;length[6~20]",
                  msg:{required:WST.lang('ucenter_please_enter_a_new_password')},
                  tip:WST.lang('ucenter_please_enter_a_new_password')
                },
                reNewPass: {
                  rule:"required;length[6~20];match[newPass]",
                  msg:{required:WST.lang('ucenter_please_enter_the_new_password_again'),match:WST.lang('ucenter_two_input_passwords_do_not_match')},
                  tip:WST.lang('ucenter_please_enter_the_new_password_again')
                },
            },
          valid: function(form){
            var params = WST.getParams('.ipt');
            var loading = WST.msg(WST.lang('ucenter_loading'), {icon: 16,time:60000});
            $.post(WST.U('home/users/passEdit'),params,function(data,textStatus){
              layer.close(loading);
              var json = WST.toJson(data);
              if(json.status=='1'){
                  WST.msg(WST.lang('op_ok'),{icon:1,time:2000},function(){
                    location.href=WST.U('home/users/security');
                  });
              }else{
                    WST.msg(json.msg,{icon:2});
              }
            });
      }
    })
}
function veemailForm(){
    //绑定邮箱
	emailForm = $('#emailForm').validator({
    	rules: {
            remote: function(element){
            	return $.post(WST.U('home/users/checkEmail'),{"loginName":element.value},function(data,textStatus){
            	});
            }	
    	},
        fields: {
        	userEmail: {
		        rule:"required;email;remote;",
		        msg:{required:WST.lang('ucenter_please_input_email'),email:WST.lang('ucenter_please_enter_a_valid_email_address')},
		        tip:WST.lang('ucenter_please_input_email'),
            },
            secretCode: {
                rule:"required",
                msg:{required:WST.lang('ucenter_please_input_check_code')},
                tip:WST.lang('ucenter_please_input_check_code'),
  	            target:"#secretErr"
              },
            loginPwd: {
              rule:"required",
              msg:{required:WST.lang('ucenter_please_enter_the_login_password')},
              tip:WST.lang('ucenter_please_enter_the_login_password')
            }
        },
        
      valid: function(form){
        var params = WST.getParams('.ipt');
        var loading = WST.msg(WST.lang('ucenter_loading'), {icon: 16,time:60000});
        $.post(WST.U('addon/ucenter-ucenter-emailedit'),params,function(data,textStatus){
          layer.close(loading);
          var json = WST.toJson(data);
          if(json.status=='1'){
             var redirect = WST.U('home/users/doneEmailBind');
             var edit = $('#editEmail').val();
             if(edit)redirect=WST.U('home/users/editEmail3');
  			     WST.msg(lang('ucenter_verification_passed'),{icon:1},function(){location.href=redirect});
          }else{
                WST.msg(json.msg,{icon:2});
          }
        });
      }
    });
}
function sendEmail(edit){
  var url = 'home/users/getEmailVerify';
  if(isSend )return;
  if(!$('#verifyCode').isValid())return;
  if(!edit){
      if(!$('#userEmail').isValid())return;
  }else{
      url = 'home/users/getEmailVerifyt';
  }
  var loading = WST.msg(WST.lang('ucenter_loading'), {icon: 16,time:60000});
  var params = WST.getParams('.ipt');
  $.post(WST.U(url),params,function(data,textStatus){
    layer.close(loading);
    var json = WST.toJson(data);
    if(json.status=='1'){
      WST.msg(lang('ucenter_msg1'));
      isSend = true;
      time = 120;
      $('#timeSend').attr('disabled', 'disabled').css('background','#e8e6e6');
      $('#timeSend').html(WST.lang('ucenter_msg2'));
      var task = setInterval(function(){
        time--;
        $('#timeSend').html(WST.lang('ucenter_msg3', [time]));
        if(time==0){
          isSend = false;           
          clearInterval(task);
          $('#timeSend').html(WST.lang('ucenter_msg4'));
          $('#timeSend').removeAttr('disabled').css('background','#e23e3d');
        }
      },1000);
    }else{
          WST.msg(json.msg,{icon:2});
          WST.getVerify('#verifyImg');
    }
  });
}




function vephoneForm(){
    //绑定手机号
	phoneForm = $('#phoneForm').validator({
      valid: function(form){
        var me = this;
        // ajax提交表单之前，先禁用submit
        me.holdSubmit();
        var params = WST.getParams('.ipt');
        var loading = WST.msg(WST.lang('ucenter_loading'), {icon: 16,time:60000});
        $.post(WST.U('home/users/phoneEdito'),params,function(data,textStatus){
          layer.close(loading);
          var json = WST.toJson(data);
          if(json.status=='1'){
  	           location.href=WST.U('home/users/editPhoneSu','pr='+json.process);
          }else{
                WST.msg(json.msg,{icon:2});
                WST.getVerify('#verifyImg'); 
          }
        });
      }
    });
}

function vegetemailForm(){
    //修改邮箱
	getemailForm = $('#getemailForm').validator({
		fields: {
            loginPwd: {
              rule:"required",
              msg:{required:WST.lang('ucenter_please_enter_the_login_password')},
              tip:WST.lang('ucenter_please_enter_the_login_password')
            }
        },
      valid: function(form){
    	 
        var params = WST.getParams('.ipt');
        var loading = WST.msg(WST.lang('ucenter_loading'), {icon: 16,time:60000});
        $.post(WST.U('addon/ucenter-ucenter-checkemailedit'),params,function(data,textStatus){
          layer.close(loading);
          var json = WST.toJson(data);
          if(json.status=='1'){
              WST.msg(WST.lang('ucenter_verification_passed'),{icon:1},function(){location.href=WST.U('addon/ucenter-ucenter-editemail2')})
          }else{
                WST.msg(json.msg,{icon:2});
                WST.getVerify('#verifyImg');
          }
        });
      }
    });
}

function editEmailForm(){
    //绑定邮箱
	emailForm = $('#emailForm').validator({
    	rules: {
            remote: function(element){
            	return $.post(WST.U('home/users/checkEmail'),{"loginName":element.value},function(data,textStatus){
            	});
            }	
    	},
        fields: {
        	userEmail: {
		        rule:"required;email;remote;",
		        msg:{required:WST.lang('ucenter_please_input_email'),email:WST.lang('ucenter_please_enter_a_valid_email_address')},
		        tip:WST.lang('ucenter_please_input_email'),
            },
            secretCode: {
                rule:"required",
                msg:{required:WST.lang('ucenter_please_input_check_code')},
                tip:WST.lang('ucenter_please_input_check_code'),
  	            target:"#secretErr"
              }
    	},
        
      valid: function(form){
        var params = WST.getParams('.ipt');
        var loading = WST.msg(WST.lang('ucenter_loading'), {icon: 16,time:60000});
        $.post(WST.U('addon/ucenter-ucenter-emaileditt'),params,function(data,textStatus){
          layer.close(loading);
          var json = WST.toJson(data);
          if(json.status=='1'){
        	  	var redirect=WST.U('addon/ucenter-ucenter-editemail3');
  				WST.msg(WST.lang('ucenter_verification_passed'),{icon:1},function(){location.href=redirect});
          }else{
                WST.msg(json.msg,{icon:2});
          }
        });
      }
    });
}
