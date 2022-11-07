function login(typ){
	var params = WST.getParams('.ipt');
	if(!$('#loginName').isValid())return;
	if(!$('#loginPwd').isValid())return;
	if(!$('#verifyCode').isValid())return;
    if(window.conf.IS_CRYPT=='1'){
        var public_key=$('#token').val();
        var exponent="10001";
   	    var rsa = new RSAKey();
        rsa.setPublic(public_key, exponent);
        params.loginPwd = rsa.encrypt(params.loginPwd);
    }
    params.typ=typ;
	var ll = WST.load({msg:WST.lang('logging_tips')});
	$.post(WST.U('home/users/checkLogin'),params,function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status=='1'){
			WST.msg(json.msg, {icon: 1});
			var url = json.url;
        	if(typ==2){
                location.href=WST.U('home/shops/index');
    		}else if(WST.blank(url)){
        		location.href = url;
        	}else{
    			if(typ==1){
                	location.href=WST.U('home/users/index');	
    			}else{
                	parent.location.reload();
    			}
        	}
		}else{
			layer.close(ll);
			WST.msg(json.msg, {icon: 5});
			WST.getVerify('#verifyImg','#verifyCode');
		}
		
	});
	return true;
}

function login2(typ){
	var params = WST.getParams('.ipt');
	if(!$('#loginNamea').isValid())return;
	if(!$('#mobileCode').isValid())return;
	params.typ=typ;
	var ll = WST.load({msg:WST.lang('logging_tips')});
	$.post(WST.U('home/users/checkLoginByPhone'),params,function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status=='1'){
			WST.msg(json.msg, {icon: 1});
			var url = json.url;
			if(typ==2){
				location.href=WST.U('home/shops/index');
			}else if(WST.blank(url)){
				location.href = url;
			}else{
				if(typ==1){
					location.href=WST.U('home/users/index');
				}else{
					parent.location.reload();
				}
			}
		}else{
			layer.close(ll);
			WST.msg(json.msg, {icon: 5});
			WST.getVerify('#verifyImg');
		}

	});
	return true;
}

function showProtocol(){
	layer.open({
	    type: 2,
	    title: WST.lang('user_register_protocol_title'),
	    shadeClose: true,
	    shade: 0.8,
	    area: ['1000px', ($(window).height() - 50) +'px'],
	    content: [WST.U('home/users/protocol')],
	    btn: [WST.lang('agree_and_regist')],
	    yes: function(index, layero){
	    	layer.close(index);
	    }
	});
}

var time = 0;
var isSend = false;
var isUse = false;
var index2 = null;
function getVerifyCode(type){
	$("#mobileCode").val("");
	var params = {};
		params.userPhone = $.trim($("#loginName").val());
	if(type==2){
		params.userPhone = $.trim($("#loginNamea").val());
	}
	if(params.userPhone==''){
		WST.msg(WST.lang('phone_number_data_tips'), {icon: 5});
		return;
	}
	params.areaCode = $.trim($("#areaCode").val());
	if(isSend )return;
	isSend = true;
	if(window.conf.SMS_VERFY=='1'){
		var html = [];
			html.push('<table class="wst-smsverfy"><tr><td width="80" align="right">');
			html.push(WST.lang('verifycode')+'：</td><td><input type="text" id="smsVerfyl" size="12" class="wst-text" maxLength="8" style="height:30px;line-height:30px;">');
			html.push('<img style="vertical-align:middle;cursor:pointer;height:30px;" class="verifyImgd" src="'+WST.DOMAIN+'/wstmart/Home/View/default/images/clickForVerify.png" title="刷新验证码" onclick="javascript:WST.getVerify(\'.verifyImgd\',\'#smsVerfyl\')"/>');
			html.push('</td></tr></table>');
		index2 = layer.open({
			title:WST.lang('verify_require'),
			type: 1,
			area: ['420px', '150px'], //宽高
			content: html.join(''),
			btn: [WST.lang('send_verify')],
			success: function(layero, index){
				WST.getVerify('.verifyImgd','#smsVerfyl');
			},
			yes: function(index, layero){
				isSend = true;
				params.smsVerfy = $.trim($('#smsVerfyl').val());
			 	if(params.smsVerfy==''){
   			  		WST.msg(WST.lang('verify_require'), {icon: 5});
   			   		return;
   			 	}
				getPhoneVerifyCode(params,type);
			},
			cancel:function(){
				isSend = false;
			}
		});
	}else{
		isSend = true;
		getPhoneVerifyCode(params,type);
	}
}

function getPhoneVerifyCode(params,type){
	WST.msg(WST.lang('sending_msg'),{time:600000});
	var url = 'home/users/getPhoneVerifyCode';
	if(type == 2){
		url = 'home/users/getPhoneVerifyCode2';
	}
	$.post(WST.U(url),params,function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status!=1){
			WST.msg(json.msg, {icon: 5});
			WST.getVerify('.verifyImgd');
			$('#smsVerfyl').val('');
			time = 0;
			isSend = false;
		}if(json.status==1){
			WST.msg(WST.lang('send_msg_done'));
			time = 120;
			$('#timeTips').html(WST.lang('get_phone_check_code1'));
			$('#mobileCode').val(json.phoneVerifyCode);
			var task = setInterval(function(){
				time--;
				$('#timeTips').html(WST.lang('get_phone_check_code2')+'('+time+")");
				if(time==0){
					isSend = false;						
					clearInterval(task);
					$('#timeTips').html(WST.lang('get_check_code_again'));
				}
			},1000);
		}
		if(json.status!=-2)layer.close(index2);
	});
}
function initRegist(){
	// 阻止按下回车键时触发短信验证码弹窗
	document.onkeydown=function(event){
            var e = event || window.event || arguments.callee.caller.arguments[0];        
             if(e && e.keyCode==13){ // enter 键
             	$('#reg_butt').submit();
             	return false;
            }
    }
	$('#reg_form').validator({
	    rules: {
	    	loginName: function(element) {
	    		//if(this.test(element, "mobile")===true){
	    			$("#mobileCodeDiv").show();
	    			$("#refreshCode").hide();
	    			$("#authCodeDiv").hide();
	    			$("#nameType").val('3');
	    		//}
	            //return this.test(element, "mobile")===true || WST.lang('please_input_valid_phone_number');
	        },
	        //自定义remote规则（注意：虽然remote规则已经内置，但这里的remote会优先于内置）
	        remote: function(element){
	        	return $.post(WST.U('home/users/checkLoginKey'),{"loginName":element.value},function(data,textStatus){

	        	});
	        }
	    },
	    fields: {
	        'loginName': 'required; loginName; remote;',
	        'loginPwd' : WST.lang('loginpwd_placeholder')+':required; password;',
	        'reUserPwd': WST.lang('confirm_password')+':required; match(loginPwd);',
	        
	        'mobileCode': WST.lang('sms_verification_code')+':required;',
	        'verifyCode': WST.lang('verify_require')+':required;'
	    },
	    // 表单验证通过后，ajax提交
	    valid: function(form){
	        var me = this;
	        // ajax提交表单之前，先禁用submit
	        me.holdSubmit();
	        var params = WST.getParams('.wst_ipt');
	        if(WST.conf.IS_CRYPT=='1'){
	            var public_key=$('#token').val();
	            var exponent="10001";
	       	    var rsa = new RSAKey();
	            rsa.setPublic(public_key, exponent);
	            params.loginPwd = rsa.encrypt(params.loginPwd);
	            params.reUserPwd = rsa.encrypt(params.reUserPwd);
	        }
	        $("#reg_butt").css('color', '#999').text(WST.lang('submiting_tips'));
	        $.post(WST.U('home/users/toRegist'),params,function(data,textStatus){
	    		var json = WST.toJson(data);
	    		if(json.status>0){
	    			WST.msg(WST.lang('login_success_jumping'), {icon: 1}, function(){
	    				var url = json.url;
	                	if(WST.blank(url)){
	                		location.href = url;
	                	}else{
	                		location.href=WST.U('home/users/index');
	                	}
	       			});
	    		}else{
	    			me.holdSubmit(false);
	    			WST.msg(json.msg, {icon: 5});
	    		}

	    	});
	    }
	});
}

function register(){
	var params = {};
	var loginName = $('#loginName2').val();
	var loginPwd = $('#loginPwd2').val();
	var reUserPwd = $('#reUserPwd2').val();
	var verifyCode = $('#verifyCode').val();
	if($('#protocol2').prop('checked')==false){
		WST.msg(WST.lang('please_agree_register_protocol'),'info');
		return false;
	}
	if(loginName.length < 6){
		WST.msg(WST.lang('require_login_name_desc'),'info');
		return false;
	}
	if(loginPwd.length < 6 || loginPwd.length > 16){
		WST.msg(WST.lang('require_password_desc'),'info');
		return false;
	}
	if(reUserPwd.length < 6 || reUserPwd.length > 16){
		WST.msg(WST.lang('require_confirm_password'),'info');
		return false;
	}
	if(loginPwd != reUserPwd){
		WST.msg(WST.lang('password_not_same'),'info');
		return false;
	}
	params.loginPwd = loginPwd;
	params.reUserPwd = reUserPwd;
	if(WST.conf.IS_CRYPT=='1'){
		var public_key=$('#token').val();
		var exponent="10001";
		var rsa = new RSAKey();
		rsa.setPublic(public_key, exponent);
		params.loginPwd = rsa.encrypt(loginPwd);
		params.reUserPwd = rsa.encrypt(reUserPwd);
	}
	params.loginName = loginName;
	params.verifyCode = verifyCode;
	$("#reg_butt2").css('color', '#999').text(WST.lang('submiting_tips'));
	$.post(WST.U('home/users/toRegistByAccount'),params,function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status>0){
			WST.msg(WST.lang('login_success_jumping'), {icon: 1}, function(){
				var url = json.url;
				if(WST.blank(url)){
					location.href = url;
				}else{
					location.href=WST.U('home/users/index');
				}
			});
		}else{
			WST.getVerify("#verifyImg","#verifyCode");
			WST.msg(json.msg, {icon: 5});
		}

	});
}