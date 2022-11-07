var time = 0;
var isSend = false;
var myorm;
var emailForm;
var phoneForm;
var getemailForm;
var getphoneForm;
var getpayForm;
var payForm;
$(function(){
    $('#phoneVerify').validator({
      valid: function(form){
    	  var n=$('#VerifyId').val();
    	  getPhoneVerifys(n);
      }
    });
})
function vePayForm(){
	//修改支付密码
	myorm = $('#payform').validator({
          valid: function(form){
            var params = WST.getParams('.ipt');
            if(window.conf.IS_CRYPT=='1'){
                var public_key=$('#token').val();
                var exponent="10001";
           	    var rsa = new RSAKey();
                rsa.setPublic(public_key, exponent);
                if(params.oldPass)params.oldPass = rsa.encrypt(params.oldPass);
                params.newPass = rsa.encrypt(params.newPass);
                params.reNewPass = rsa.encrypt(params.reNewPass);
            }
            var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
            $.post(WST.U('shop/users/payPassEdit'),params,function(data,textStatus){
              layer.close(loading);
              var json = WST.toJson(data);
              if(json.status=='1'){
                  WST.msg(json.msg,{icon:1,time:2000},function(){
                	   location.href=WST.U('shop/users/security');
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
                  msg:{required:WST.lang('please_enter_a_new_password')},
                  tip:WST.lang('please_enter_a_new_password')
                },
                reNewPass: {
                  rule:"required;length[6~20];match[newPass]",
                  msg:{required:WST.lang('please_enter_the_new_password_again'),match:WST.lang('the_two_passwords_are_inconsistent')},
                  tip:WST.lang('please_enter_the_new_password_again')
                },
            },
          valid: function(form){
            var params = WST.getParams('.ipt');
            if(window.conf.IS_CRYPT=='1'){
                var public_key=$('#token').val();
                var exponent="10001";
           	    var rsa = new RSAKey();
                rsa.setPublic(public_key, exponent);
                if(params.oldPass)params.oldPass = rsa.encrypt(params.oldPass);
                params.newPass = rsa.encrypt(params.newPass);
                params.reNewPass = rsa.encrypt(params.reNewPass);
            }
            var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
            $.post(WST.U('shop/users/passEdit'),params,function(data,textStatus){
              layer.close(loading);
              var json = WST.toJson(data);
              if(json.status=='1'){
                  WST.msg(WST.lang('op_ok'),{icon:1,time:2000},function(){
                    location.href=WST.U('shop/users/security');
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
            	return $.post(WST.U('shop/users/checkEmail'),{"loginName":element.value},function(data,textStatus){
            	});
            }	
    	},
        fields: {
        	userEmail: {
		        rule:"required;email;remote;",
		        msg:{required:WST.lang('please_input_email'),email:WST.lang('please_enter_a_valid_email_address')},
		        tip:WST.lang('please_input_email'),
            },
            secretCode: {
              rule:"required",
              msg:{required:WST.lang('please_input_check_code')},
              tip:WST.lang('please_input_check_code'),
	            target:"#secretErr"
            }
        },
        
      valid: function(form){
        var params = WST.getParams('.ipt');
        var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
        $.post(WST.U('shop/users/emailEdit'),params,function(data,textStatus){
          layer.close(loading);
          var json = WST.toJson(data);
          if(json.status=='1'){
             var redirect = WST.U('shop/users/doneEmailBind');
             var edit = $('#editEmail').val();
             if(edit)redirect=WST.U('shop/users/editEmail3');
  			     WST.msg(WST.lang('verification_passed'),{icon:1},function(){location.href=redirect});
          }else{
                WST.msg(json.msg,{icon:2});
                WST.getVerify('#verifyImg');
          }
        });
      }
    });
}
function sendEmail(edit){
  var url = 'shop/users/getEmailVerify';
  if(isSend )return;
  if(!$('#verifyCode').isValid())return;
  if(!edit){
      if(!$('#userEmail').isValid())return;
  }else{
      url = 'shop/users/getEmailVerifyt';
  }
  var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
  var params = WST.getParams('.ipt');
  $.post(WST.U(url),params,function(data,textStatus){
    layer.close(loading);
    var json = WST.toJson(data);
    if(json.status=='1'){
      WST.msg(WST.lang('email_has_been_sent,_please_check'));
      isSend = true;
      time = 120;
      $('#timeSend').attr('disabled', 'disabled').css({'background':'#e8e6e6','width':'135px','border-color':'#e8e6e6','color':'#000'});
      $('#timeSend').html(WST.lang('send_verification_email')+'(120)');
      var task = setInterval(function(){
        time--;
        $('#timeSend').html(WST.lang('send_verification_email')+'('+time+")");
        if(time==0){
          isSend = false;           
          clearInterval(task);
          $('#timeSend').html(WST.lang('resend_verification_email'));
          $('#timeSend').removeAttr('disabled').css({'background':'#e23e3d','width':'135px','border':'#e23e3d'});
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
        var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
        $.post(WST.U('shop/users/phoneEdito'),params,function(data,textStatus){
          layer.close(loading);
          var json = WST.toJson(data);
          if(json.status=='1'){
  	           location.href=WST.U('shop/users/editPhoneSu','pr='+json.process);
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
      valid: function(form){
        var params = WST.getParams('.ipt');
        var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
        $.post(WST.U('shop/users/emailEditt'),params,function(data,textStatus){
          layer.close(loading);
          var json = WST.toJson(data);
          if(json.status=='1'){
              WST.msg(WST.lang('verification_passed'),{icon:1},function(){location.href=WST.U('shop/users/editEmail2')})
          }else{
                WST.msg(json.msg,{icon:2});
                WST.getVerify('#verifyImg');
          }
        });
      }
    });
}
function vegetphoneForm(){
    //修改手机号
	getphoneForm = $('#getphoneForm').validator({
      valid: function(form){
        var params = WST.getParams('.ipt');
        var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
        $.post(WST.U('shop/users/phoneEditt'),params,function(data,textStatus){
          layer.close(loading);
          var json = WST.toJson(data);
          if(json.status=='1'){
  	        	location.href=WST.U('shop/users/editPhoneSut');
          }else{
                WST.msg(json.msg,{icon:2});
                WST.getVerify('#verifyImg');
          }
        });
      }
    });
}
function vegetpayForm(){
    //重置支付密码
	getpayForm = $('#getpayForm').validator({
      valid: function(form){
        var params = WST.getParams('.ipt');
        var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
        $.post(WST.U('shop/users/payEditt'),params,function(data,textStatus){
          layer.close(loading);
          var json = WST.toJson(data);
          if(json.status=='1'){
  	        	location.href=WST.U('shop/users/editPaySut');
          }else{
                WST.msg(json.msg,{icon:2});
                WST.getVerify('#verifyImg');
          }
        });
      }
    });
}
function vepayForm(){
    //设置支付密码
	payForm = $('#payForm').validator({
      valid: function(form){
        var me = this;
        me.holdSubmit();
        var params = WST.getParams('.ipt');
        if(window.conf.IS_CRYPT=='1'){
            var public_key=$('#token').val();
            var exponent="10001";
       	    var rsa = new RSAKey();
            rsa.setPublic(public_key, exponent);
            params.newPass = rsa.encrypt(params.newPass);
            params.reNewPass = rsa.encrypt(params.reNewPass);
        }
        var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
        $.post(WST.U('shop/users/payEdito'),params,function(data,textStatus){
          layer.close(loading);
          var json = WST.toJson(data);
          if(json.status=='1'){
  	           location.href=WST.U('shop/users/editPaySu','pr='+json.process);
          }else{
                WST.msg(json.msg,{icon:2});
                WST.getVerify('#verifyImg'); 
          }
        });
      }
    });
}
//发送手机验证码
function getPhoneVerify(n){
	if(!$('#userPhone').isValid())return;
	$('#VerifyId').val(n);
	if(window.conf.SMS_VERFY==1){
		WST.open({type: 1,title:WST.lang('please_enter_the_verification_code'),shade: [0.6, '#000'],border: [0],content: $('#phoneVerify'),area: ['600px', '240px'],end: function () {
                $('#phoneVerify').hide();
            }});
	}else{
		getPhoneVerifys(n);
	}
}
function getPhoneVerifys(n){
	WST.msg(WST.lang('loading'),{time:600000});
	var time = 0;
	var isSend = false;
	var params = WST.getParams('.ipt');
	$.post(WST.U('shop/users/getPhoneVerify'+n),params,function(data,textStatus){
		var json = WST.toJson(data);
		if(isSend )return;
		isSend = true;
		if(json.status!=1){
			WST.msg(json.msg, {icon: 5});
			WST.getVerify('#verifyImg');
			time = 0;
			isSend = false;
		}if(json.status==1){
			WST.msg(WST.lang('SMS_has_been_sent_please_check'));
			layer.closeAll('page'); 
			time = 120;
			$('#timeObtain').attr('disabled', 'disabled').css({'background':'#e8e6e6','border-color':'#e8e6e6','color':'#000'});
			$('#timeObtain').html(WST.lang('Get mobile phone verification code')+'(120)').css('width','150px');
			var task = setInterval(function(){
				time--;
				$('#timeObtain').html(WST.lang('Get mobile phone verification code')+'('+time+")");
				if(time==0){
					isSend = false;						
					clearInterval(task);
					$('#timeObtain').html(WST.lang('reacquire_captcha')).css('width','120px');
					$('#timeObtain').removeAttr('disabled').css({'background':'#e23e3d','border-color':'#e23e3d'});
				}
			},1000);
		}
	});
}
