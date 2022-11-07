jQuery.noConflict();
function inChoice(n){
	if(n==1){
		location.href = WST.AU('thirdlogin://thirdlogin/mobileBindLogin');
	}else{
		location.href = WST.AU('thirdlogin://thirdlogin/mobileBindReg');
	}
}
function inReturn(){
	$('#login-w').html(WST.lang('thirdlogin_associated_account_number'));
	WST.showHide('','#login0,#login1,#return');
	WST.showHide(1,'#choice');
}
function backLogin(){
   location.href = WST.AU('thirdlogin://thirdlogin/mobileBind');
}

function login(){
    var loginName = $('#loginName').val();
    var loginPwd = $('#loginPwd').val();
    var loginVerfy = $('#loginVerfy').val();
    if(loginName==''){
        WST.msg(WST.lang('thirdlogin_please_enter_the_account_number'),'info');
        return false;
    }
    if(loginPwd==''){
        WST.msg(WST.lang('thirdlogin_tips7'),'info');
        return false;
    }
    if(loginVerfy==''){
        WST.msg(WST.lang('thirdlogin_please_input_verification_code'),'info');
        return false;
    }
    if(window.conf.IS_CRYPTPWD==1){
        var public_key=$('#key').val();
        var exponent="10001";
        var rsa = new RSAKey();
        rsa.setPublic(public_key, exponent);
        var loginPwd = rsa.encrypt(loginPwd);
    }
    WST.load(WST.lang('thirdlogin_loading'));
    var param = {};
    param.loginName = loginName;
    param.loginPwd = loginPwd;
    param.verifyCode = loginVerfy;
    $('#loginButton').addClass("active").attr('disabled', 'disabled');
    $.post(WST.U('mobile/users/checkLogin'), param, function(data){
        var json = WST.toJson(data);
        if( json.status == 1 ){
            WST.msg(json.msg,'success');
            var url = json.url;
            setTimeout(function(){
                if(WST.blank(url)){
                    location.href = url;
                }else{
                    location.href = WST.U('mobile/users/index');
                }
            },2000);
        }else{
            WST.msg(json.msg,'warn');
            WST.getVerify("#verifyImg1");
            $('#loginVerfy').val('');
            $('#loginButton').removeAttr('disabled').removeClass("active");
        }
        WST.noload();
        data = json = null;
    });
}

function login2(){
    var loginName = $('#regName').val();
    var mobileCode = $('#phoneCode').val();
    var areaCode = $('#areaCode').val();
    if(loginName==''){
        WST.msg(WST.lang('thirdlogin_please_input_mobile_phone_number'),'info');
        return false;
    }
    if(mobileCode==''){
        WST.msg(WST.lang('thirdlogin_please_input_SMS_verification_code'),'info');
        return false;
    }

    WST.load(WST.lang('thirdlogin_loading'));
    var param = {};
    param.loginNamea = loginName;
    param.mobileCode = mobileCode;
    param.areaCode = areaCode;
    $('#loginButton2').addClass("active").attr('disabled', 'disabled');
    $.post(WST.U('mobile/users/checkLoginByPhone'), param, function(data){
        var json = WST.toJson(data);
        if( json.status == 1 ){
            WST.msg(json.msg,'success');
            var url = json.url;
            setTimeout(function(){
                if(WST.blank(url)){
                    location.href = url;
                }else{
                    location.href = WST.U('mobile/users/index');
                }
            },2000);
        }else{
            WST.msg(json.msg,'warn');
            WST.getVerify("#verifyImg1");
            $('#loginButton2').removeAttr('disabled').removeClass("active");
        }
        WST.noload();
        data = json = null;
    });
}


var nameType = 3;
function onTesting(obj){
    //不能输入中文
    WST.isChinese(obj,1);
    var data = $(obj).val();
    var  regMobile = /^\d{6,11}$/;
    if(regMobile.test(data)){//手机
        nameType = 3;
        $.post(WST.U('mobile/users/checkUserPhone'), {userPhone:data}, function(data){
            var json = WST.toJson(data);
            if( json.status == 1 ){
            }else{
                var dia=$.dialog({
                    title:'',
                    content:'<p style="text-align: center;">'+WST.lang('thirdlogin_msg1')+'</p>',
                    button:[WST.lang('thirdlogin_ok')]
                });
            }
            data = json = null;
        });
    }
}
function register(){
    var regName = $('#regName').val();
    var regPwd = $('#regPwd').val();
    var regVerfy = $('#regVerfy').val();
    var phoneCode = $('#phoneCode').val();
    var areaCode = $('#areaCode').val();
    var param = {};
    if($('#defaults2').hasClass('ui-icon-unchecked-s')){
        WST.msg(WST.lang('thirdlogin_msg2'),'info');
        return false;
    }
    if(regName==''){
        WST.msg(WST.lang('thirdlogin_please_enter_the_account_number'),'info');
        return false;
    }
    if(regName.length < 6){
        WST.msg(WST.lang('thirdlogin_msg3'),'info');
        return false;
    }
    if(regPwd==''){
        WST.msg(WST.lang('thirdlogin_tips7'),'info');
        return false;
    }
    if(regPwd.length < 6 || regPwd.length > 16){
        WST.msg(WST.lang('thirdlogin_msg4'),'info');
        return false;
    }
    if(phoneCode==''){
        WST.msg(WST.lang('thirdlogin_please_input_SMS_verification_code'),'info');
        return false;
    }
    param.mobileCode = phoneCode;
    if(window.conf.IS_CRYPTPWD==1){
        var public_key=$('#key').val();
        var exponent="10001";
        var rsa = new RSAKey();
        rsa.setPublic(public_key, exponent);
        regPwd = rsa.encrypt(regPwd);
    }
    WST.load(WST.lang('thirdlogin_loading'));
    param.nameType = nameType;
    param.loginName = regName;
    param.loginPwd = regPwd;
    param.areaCode = areaCode;
    $('#regButton').addClass("active").attr('disabled', 'disabled');
    $.post(WST.U('mobile/users/register'), param, function(data){
        var json = WST.toJson(data);
        if( json.status == 1 ){
            WST.msg(json.msg,'success');
            var url = json.url;
            setTimeout(function(){
                if(WST.blank(url)){
                    location.href = url;
                }else{
                    location.href = WST.U('mobile/users/index');
                }
            },2000);
        }else{
            WST.msg(json.msg,'warn');
            WST.getVerify("#verifyImg0");
            $('#regButton').removeAttr('disabled').removeClass("active");
        }
        WST.noload();
        data = json = null;
    });
}
function register2(){
    var regName = $('#regName2').val();
    var regPwd = $('#regPwd2').val();
    var reRegPwd = $('#reRegPwd').val();
    var verifyCode = $('#verifyCode').val();
    var param = {};
    if($('#defaults').hasClass('ui-icon-unchecked-s')){
        WST.msg(WST.lang('thirdlogin_msg2'),'info');
        return false;
    }
    if(regName==''){
        WST.msg(WST.lang('thirdlogin_enter_one_user_name'),'info');
        return false;
    }
    if(regPwd==''){
        WST.msg(WST.lang('thirdlogin_tips7'),'info');
        return false;
    }
    if(reRegPwd==''){
        WST.msg(WST.lang('thirdlogin_please_enter_the_confirmation_password'),'info');
        return false;
    }
    if(verifyCode==''){
        WST.msg(WST.lang('thirdlogin_msg5'),'info');
        return false;
    }
    if(regName.length < 6){
        WST.msg(WST.lang('thirdlogin_msg6'),'info');
        return false;
    }
    if(regPwd.length < 6 || regPwd.length > 16){
        WST.msg(WST.lang('thirdlogin_msg4'),'info');
        return false;
    }
    if(reRegPwd.length < 6 || reRegPwd.length > 16){
        WST.msg(WST.lang('thirdlogin_msg7'),'info');
        return false;
    }
    if(regPwd != reRegPwd){
        WST.msg(WST.lang('thirdlogin_msg8'),'info');
        return false;
    }
    if(window.conf.IS_CRYPTPWD==1){
        var public_key=$('#key').val();
        var exponent="10001";
        var rsa = new RSAKey();
        rsa.setPublic(public_key, exponent);
        regPwd = rsa.encrypt(regPwd);
    }
    WST.load(WST.lang('thirdlogin_loading'));
    param.nameType = nameType;
    param.loginName = regName;
    param.loginPwd = regPwd;
    param.verifyCode = verifyCode;
    $('#regButton2').addClass("active").attr('disabled', 'disabled');
    $.post(WST.U('mobile/users/registerByAccount'), param, function(data){
        var json = WST.toJson(data);
        if( json.status == 1 ){
            WST.msg(json.msg,'success');
            var url = json.url;
            setTimeout(function(){
                if(WST.blank(url)){
                    location.href = url;
                }else{
                    location.href = WST.U('mobile/users/index');
                }
            },2000);
        }else{
            WST.msg(json.msg,'warn');
            WST.getVerify("#verifyImg4");
            $('#verifyCode').val('');
            $('#regButton2').removeAttr('disabled').removeClass("active");
        }
        WST.noload();
        data = json = null;
    });
}
var time = 0;
var isSend = false;
function obtainCode(type){
    var userPhone = $('#regName').val();
    var areaCode = $('#areaCode').val();
    if(userPhone ==''){
        WST.msg(WST.lang('thirdlogin_msg9'),'info');
        $('#regName').focus();
        return false;
    }
    if(WST.conf.SMS_VERFY==1){
        var smsVerfy = $('#smsVerfy').val();
        if(smsVerfy ==''){
            WST.msg(WST.lang('thirdlogin_please_input_verification_code'),'info');
            $('#smsVerfy').focus();
            return false;
        }
    }
    var param = {};
    param.userPhone = userPhone;
    param.areaCode = areaCode;
    param.smsVerfy = smsVerfy;
    if(isSend)return;
    isSend = true;
    var url = 'mobile/users/getPhoneVerifyCode';
    if(type==2){
        url = 'mobile/users/getPhoneVerifyCode2';
    }
    $.post(WST.U(url), param, function(data){
        var json = WST.toJson(data);
        if( json.status == 1 ){
            WST.msg(json.msg,'success');
            time = 120;
            var href = $('.send').attr('href');
            $('.send').attr('href', 'javascript:void(0)').html(WST.lang('thirdlogin_msg10'));
            var task = setInterval(function(){
                time--;
                $('.send').html(''+time+WST.lang('thirdlogin_msg11'));
                if(time==0){
                    isSend = false;
                    clearInterval(task);
                    $('#obtain').attr('href', href).html(WST.lang('thirdlogin_msg12'));
                }
            },1000);
        }else{
            WST.msg(json.msg,'warn');
            WST.getVerify("#verifyImg3");
            $('#smsVerfy').val('');
            isSend = false;
        }
        data = json = null;
    });
}
//弹框
function wholeShow(type){
    jQuery('#'+type).animate({"right": 0}, 500);
}
function wholeHide(type){
    var dataWidth = $('#'+type).css('width');
    jQuery('#'+type).animate({'right': '-'+dataWidth}, 500);
}
//协议
function inAgree(obj,type){
    if(type==1){
        if($('#defaults').hasClass('wst-active')){
            $(obj).addClass('ui-icon-unchecked-s');
            $(obj).removeClass('ui-icon-success-block wst-active');
        }else{
            $(obj).removeClass('ui-icon-unchecked-s');
            $(obj).addClass('ui-icon-success-block wst-active');
        }
    }else{
        if($('#defaults2').hasClass('wst-active')){
            $(obj).addClass('ui-icon-unchecked-s');
            $(obj).removeClass('ui-icon-success-block wst-active');
        }else{
            $(obj).removeClass('ui-icon-unchecked-s');
            $(obj).addClass('ui-icon-success-block wst-active');
        }
    }
}
$(document).ready(function(){
    var h = WST.pageHeight();
    $('#protocol .content').css('overflow-y','scroll').css('height',h-61);
    $("#protocol").css('right','-100%');
});