var time = 0;
var isSend = false;
$(function(){
    //第一步
    $('#forgetPwdForm').validator({
      valid: function(form){
    	  forgetPwd();
      }
    });
   //手机发送验证
    $('#phoneVerify').validator({
        valid: function(form){
      	  phoneVerify2();
        }
      });
    //重置密码
    $('#forgetPwdForm3').validator({
        fields: {
        	loginPwd: {
              rule:"required;length[6~16]",
              msg:{required:WST.lang('require_new_password')},
              tip:WST.lang('require_new_password')
            },
            repassword: {
              rule:"required;length[6~16];match[loginPwd]",
              msg:{required:WST.lang('require_new_password2'),match:WST.lang('password_not_match')},
              tip:WST.lang('require_new_password2')
            },
        },
        valid: function(form){
        	forgetPwd();
        }
    });
})
function forgetPwd(){
    var params = WST.getParams('.ipt');
    if(window.conf.IS_CRYPT=='1' && params.step=='3'){
        var public_key=$('#token').val();
        var exponent="10001";
   	    var rsa = new RSAKey();
        rsa.setPublic(public_key, exponent);
        params.loginPwd = rsa.encrypt(params.loginPwd);
        params.repassword = rsa.encrypt(params.repassword);
    }
    var step = $('#step').val();
    var modes = $('#modes').val();
    var loading = WST.msg(WST.lang('submiting_tips'), {icon: 16,time:60000});
    $.post(WST.U('home/users/findPass'),params,function(data,textStatus){
      layer.close(loading);
      var json = WST.toJson(data);
      if(json.status=='1'){
      	if(step==1){
		    location.href=WST.U('home/users/forgetpasst','',true);
	    }else{
	        setTimeout(function(){
	        	if(step==2){
	        		if(modes==1){
	        			location.href=json.url;
	        		}else{
	        			disableBtn();
	        		}
	        	}else if(step==3){
	        		location.href=WST.U('home/users/forgetpassf','',true);
	        	}
	        },1000);
	    }
      }else{
            WST.msg(json.msg,{icon:2});
            WST.getVerify('#verifyImg','#verifyCode');
      }
    });
}

//第二步
$('#type').change(function(){
    if ($('#type').val() == 'phone') {
        $('.phone-verify').show();
        $('.email-verify').hide();
        $('#modes').val(1);
    }else{
        $('.phone-verify').hide();
        $('.email-verify').show();
        $('#modes').val(2);
    }
})
function phoneVerify(){
	if(window.conf.SMS_VERFY==1){
		WST.getVerify("#verifyImg2");
		WST.open({type: 1,title:WST.lang('verify_require'),shade: [0.6, '#000'],border: [0],content: $('#phoneVerify'),area: ['500px', '160px']});
	}else{
		phoneVerify2();
	}
}
function phoneVerify2(){
	WST.msg(WST.lang('sending_msg'),{time:600000});
	var time = 0;
	var isSend = false;
	var params = WST.getParams('.ipt');
	$.post(WST.U('home/users/getfindPhone'),params,function(data,textStatus){
		var json = WST.toJson(data);
		if(isSend )return;
		isSend = true;
		if(json.status!=1){
			WST.msg(json.msg, {icon: 5});
			WST.getVerify("#verifyImg2","#smsVerfy");
			$('#smsVerfy').val('');
			time = 0;
			isSend = false;
		}if(json.status==1){
			WST.msg(WST.lang('send_msg_done'));
			layer.closeAll('page'); 
			time = 120;
			$('#timeObtain').attr('disabled', 'disabled').css('background','#e8e6e6');
			$('#timeObtain').html(WST.lang('get_phone_check_code1')).css('width','130px');
			var task = setInterval(function(){
				time--;
				$('#timeObtain').html(WST.lang('get_phone_check_code2')+'('+time+")");
				if(time==0){
					isSend = false;						
					clearInterval(task);
					$('#timeObtain').html(WST.lang('get_check_code_again')).css('width','100px');
					$('#timeObtain').removeAttr('disabled').css('background','#e23e3d');
				}
			},1000);
		}
	});
}
function forgetPhone(){
	if(!$('#Checkcode').isValid())return;
	forgetPwd();
}
function forgetEmail(){
	if(!$('#verifyCode').isValid())return;
	forgetPwd();
}
/*重置密码*/
function resetPass(){
	if(!$('#secretCode').isValid())return;
	var secretCode = $('#secretCode').val();
	$.post(WST.U('home/users/forgetPasss'),{secretCode:secretCode},function(data){
		var json = WST.toJson(data);
		if(json.status==1){
			location.href=WST.U('home/users/resetpass','',true);
		}else{
			WST.msg(json.msg,{icon:2});
			return false;
		}
	})
}

/*禁用发送按钮*/
function disableBtn(){
	time = 120;
	$('#sendEmailBtn').attr('disabled', 'disabled').css({'background':'#e8e6e6','color':'#a7a7a7'});
	$('#sendEmailBtn').html(WST.lang('get_email_check_code1')).css('width','130px');
	var task = setInterval(function(){
		time--;
		$('#sendEmailBtn').html(WST.lang('get_email_check_code2')+'('+time+")");
		if(time==0){
			isSend = false;						
			clearInterval(task);
			$('#sendEmailBtn').html(WST.lang('get_check_code_again')).css('width','100px');
			$('#sendEmailBtn').removeAttr('disabled').css({'background':'#f0efef','color':'#110f0f'});
		}
	},1000);
}