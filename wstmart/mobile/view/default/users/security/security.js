var time = 0;
var isSend = false;
$(document).ready(function(){
	WST.initFooter('user');
});
//修改登录密码
function inLogin(){
	location.href = WST.U('mobile/users/editLoginPass');
}
function editLogin(type){
	if(type==1){
		var orloginPwd = $('#orloginPwd').val();
	    if(orloginPwd==''){
	    	WST.msg(WST.lang('please_input_original_password'),'info');
		    $('#orloginPwd').focus();
	        return false;
	    }
	}
	var loginPwd = $('#loginPwd').val();
	var cologinPwd = $('#cologinPwd').val();
	if(loginPwd==''){
    	WST.msg(WST.lang('require_new_password'),'info');
    	$('#loginPwd').focus();
        return false;
    }
	if(cologinPwd==''){
    	WST.msg(WST.lang('please_input_confirm_password'),'info');
    	$('#cologinPwd').focus();
        return false;
    }
	if(loginPwd.length < 6){
    	WST.msg(WST.lang('require_password_desc'),'info');
    	$('#cologinPwd').focus();
        return false;
    }
	if(cologinPwd!=loginPwd){
    	WST.msg(WST.lang('password_not_same'),'info');
    	$('#cologinPwd').focus();
        return false;
    }
    if(window.conf.IS_CRYPTPWD==1){
        var public_key=$('#key').val();
        var exponent="10001";
   	    var rsa = new RSAKey();
        rsa.setPublic(public_key, exponent);
        if(type==1)var orloginPwd = rsa.encrypt(orloginPwd);
        var loginPwd = rsa.encrypt(loginPwd);
    }
	WST.load('设置中···');
    var param = {};
    param.type = type;
    if(type==1) param.oldPass = orloginPwd;
    param.newPass = loginPwd;
	$('#modifyPwd').addClass("active").attr('disabled', 'disabled');
    $.post(WST.U('mobile/users/editloginPwd'), param, function(data){
        var json = WST.toJson(data);
        if( json.status == 1 ){
        	WST.msg(json.msg,'success');
            setTimeout(function(){
            	location.href = WST.U('mobile/users/security');
            },2000);
        }else{
        	WST.msg(json.msg,'warn');
        	$('#modifyPwd').removeAttr('disabled').removeClass("active");
        }
        WST.noload();
        data = json = null;
    });
}
//修改密码
function inPay(){
	location.href = WST.U('mobile/users/editPayPass');
}
function editPay(type){
	if(type==1){
		var orpayPwd = $('#orpayPwd').val();
	    if(orpayPwd==''){
	    	WST.msg(WST.lang('please_input_original_password'),'info');
		    $('#orpayPwd').focus();
	        return false;
	    }
	}
	var payPwd = $('#payPwd').val();
	var copayPwd = $('#copayPwd').val();
	if(payPwd==''){
    	WST.msg(WST.lang('require_new_password'),'info');
    	$('#payPwd').focus();
        return false;
    }
	if(copayPwd==''){
    	WST.msg(WST.lang('please_input_confirm_password'),'info');
    	$('#copayPwd').focus();
        return false;
    }
	if(payPwd.length !=6){
    	WST.msg(WST.lang('please_input_pay_password'),'info');
    	$('#copayPwd').focus();
        return false;
    }
	if(copayPwd!=payPwd){
    	WST.msg(WST.lang('password_not_same'),'info');
    	$('#copayPwd').focus();
        return false;
    }
    if(window.conf.IS_CRYPTPWD==1){
        var public_key=$('#key').val();
        var exponent="10001";
   	    var rsa = new RSAKey();
        rsa.setPublic(public_key, exponent);
        if(type==1)var orpayPwd = rsa.encrypt(orpayPwd);
        var payPwd = rsa.encrypt(payPwd);
    }
	WST.load(WST.lang('submiting_tips'));
    var param = {};
    param.type = type;
    if(type==1)param.oldPass = orpayPwd;
    param.newPass = payPwd;
	$('#modifyPwd').addClass("active").attr('disabled', 'disabled');
    $.post(WST.U('mobile/users/editpayPwd'), param, function(data){
        var json = WST.toJson(data);
        if( json.status == 1 ){
        	WST.msg(json.msg,'success');
            setTimeout(function(){
            	location.href = WST.U('mobile/users/security');
            },2000);
        }else if(json.status == -2){
        	WST.msg(json.msg,'warn');
        	$('#modifyPwd').removeAttr('disabled').removeClass("active");
        }else{
        	WST.msg(json.msg,'warn');
        	$('#modifyPwd').removeAttr('disabled').removeClass("active");
        }
        WST.noload();
        data = json = null;
    });
}
//找回支付密码
function backpayCode(){
	if(WST.conf.SMS_VERFY==1){
		var smsVerfy = $('#smsVerfy').val();
	    if(smsVerfy ==''){
	    	WST.msg(WST.lang('verify_require'),'info');
		    $('#smsVerfy').focus();
	        return false;
	    }
	}
    var param = {};
	param.smsVerfy = smsVerfy;
	if(isSend)return;
	isSend = true;
    $.post(WST.U('mobile/users/backpayCode'), param, function(data){
        var json = WST.toJson(data);
        if( json.status == 1 ){
        	WST.msg(json.msg,'success');
			time = 120;
			$('#obtain').attr('disabled', 'disabled').html(WST.lang('get_time_tips',[120]));
			var task = setInterval(function(){
				time--;
				$('#obtain').html(WST.lang('get_time_tips',[time]));
				if(time==0){
					isSend = false;
					clearInterval(task);
					$('#obtain').removeAttr('disabled').html(WST.lang('resend'));
				}
			},1000);
        }else{
        	WST.msg(json.msg,'warn');
        	WST.getVerify("#verifyImg");
			$('#smsVerfy').val('');
        	isSend = false;
        }
        data = json = null;
    });
}
function backPaypwd(type){
	if(type==1){
		var payPwd = $('#payPwd').val();
		var copayPwd = $('#copayPwd').val();
		if(payPwd==''){
	    	WST.msg(WST.lang('require_new_password'),'info');
	    	$('#payPwd').focus();
	        return false;
	    }
		if(copayPwd==''){
	    	WST.msg(WST.lang('please_input_confirm_password'),'info');
	    	$('#copayPwd').focus();
	        return false;
	    }
		if(payPwd.length !=6){
	    	WST.msg(WST.lang('please_input_pay_password'),'info');
	    	$('#copayPwd').focus();
	        return false;
	    }
		if(copayPwd!=payPwd){
	    	WST.msg(WST.lang('password_not_same'),'info');
	    	$('#copayPwd').focus();
	        return false;
	    }
	    if(window.conf.IS_CRYPTPWD==1){
	        var public_key=$('#key').val();
	        var exponent="10001";
	   	    var rsa = new RSAKey();
	        rsa.setPublic(public_key, exponent);
	        var payPwd = rsa.encrypt(payPwd);
	    }
		WST.load(WST.lang('submiting_tips'));
	    var param = {};
	    param.newPass = payPwd;
		$('#modifyPwd').addClass("active").attr('disabled', 'disabled');
	    $.post(WST.U('mobile/users/resetbackPay'), param, function(data){
	        var json = WST.toJson(data);
	        if( json.status == 1 ){
	        	WST.msg(json.msg,'success');
	            setTimeout(function(){
	            	location.href = WST.U('mobile/users/security');
	            },2000);
	        }else{
	        	WST.msg(json.msg,'warn');
	        	$('#modifyPwd').removeAttr('disabled').removeClass("active");
	        }
	        WST.noload();
	        data = json = null;
	    });
	}else{
		var phoneCode = $('#phoneCode').val();
	    if(phoneCode==''){
	    	WST.msg(WST.lang('require_mobile_verifycode'),'info');
	    	$('#phoneCode').focus();
	        return false;
	    }
	    var param = {};
	    param.phoneCode = phoneCode;
		$('#modifyPhone').addClass("active").attr('disabled', 'disabled');
	    $.post(WST.U('mobile/users/verifybackPay'), param, function(data){
	        var json = WST.toJson(data);
	        if( json.status == 1 ){
	        	WST.msg(json.msg,'success');
	            setTimeout(function(){
	            	location.href = WST.U('mobile/users/backPayPass');
	            },2000);
	        }else{
	        	WST.msg(json.msg,'warn');
	        	$('#modifyPhone').removeAttr('disabled').removeClass("active");
	        }
	        data = json = null;
	    });	
	}
}
//修改手机
function inPhone(){
	location.href = WST.U('mobile/users/editPhone');
}
//发送短信
function obtainCode(type){
	var userPhone = $('#userPhone').val();
	var areaCode = $('#areaCode').val();
	if(type==0){
	    if(userPhone ==''){
	    	WST.msg(WST.lang('phone_number_data_tips'),'info');
		    $('#userPhone').focus();
	        return false;
	    }
	}
	if(WST.conf.SMS_VERFY==1){
		var smsVerfy = $('#smsVerfy').val();
	    if(smsVerfy ==''){
	    	WST.msg(WST.lang('verify_require'),'info');
		    $('#smsVerfy').focus();
	        return false;
	    }
	}
    var param = {};
	param.areaCode = areaCode;
	param.userPhone = userPhone;
	param.smsVerfy = smsVerfy;
	if(isSend)return;
	isSend = true;
    $.post(WST.U('mobile/users/'+((type==0)?"sendCodeTie":"sendCodeEdit")), param, function(data){
        var json = WST.toJson(data);
        if( json.status == 1 ){
        	WST.msg(json.msg,'success');
			time = 120;
			var href = $('.send').attr('href');
			$('.send').attr('href','javascript:void(0)').text(WST.lang('get_time_tips',[120]));
			var task = setInterval(function(){
				time--;
				$('.send').text(WST.lang('get_time_tips',[time]));
				if(time==0){
					isSend = false;
					clearInterval(task);
					$('.send').attr('href',href).text(WST.lang('resend'));
				}
			},1000);
        }else{
        	WST.msg(json.msg,'warn');
        	WST.getVerify("#verifyImg");
			$('#smsVerfy').val('');
        	isSend = false;
        }
        data = json = null;
    });
}
//修改手机号码
function editPhone(type){
	if(type==0){
		var userPhone = $('#userPhone').val();
	    if(userPhone==''){
	    	WST.msg(WST.lang('phone_number_data_tips'),'info');
		    $('#userPhone').focus();
	        return false;
	    }
	}
	var phoneCode = $('#phoneCode').val();
    if(phoneCode==''){
    	WST.msg(WST.lang('require_mobile_verifycode'),'info');
    	$('#phoneCode').focus();
        return false;
    }
    var param = {};
    param.phoneCode = phoneCode;
	$('#modifyPhone').addClass("active").attr('disabled', 'disabled');
    $.post(WST.U('mobile/users/'+((type==0)?"phoneEdit":"phoneEdito")), param, function(data){
        var json = WST.toJson(data);
        if( json.status == 1 ){
        	WST.msg(json.msg,'success');
        	if(type==0){
                setTimeout(function(){
                	location.href = WST.U('mobile/users/security');
                },2000);
        	}else{
                setTimeout(function(){
                	location.href = WST.U('mobile/users/editPhoneo');
                },2000);
        	}

        }else{
        	WST.msg(json.msg,'warn');
        	$('#modifyPhone').removeAttr('disabled').removeClass("active");
        }
        data = json = null;
    });
}