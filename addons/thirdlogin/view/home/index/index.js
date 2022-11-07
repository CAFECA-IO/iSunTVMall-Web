
function toChoice(obj,type){
	$(obj).addClass('selected').siblings().removeClass('selected');
	if(type==1){
		$('#binding').show();
		$('#register').hide();
	}else{
		$('#binding').hide();
		$('#register').show();
	}
}
function login(typ){
	var params = {};
	params.typ = typ;
	if(!$('#loginName1').isValid())return;
	if(!$('#loginPwd1').isValid())return;
	if(!$('#verifyCode1').isValid())return;
	params.loginName = $('#loginName1').val();
	params.loginPwd = $('#loginPwd1').val();
	params.verifyCode = $('#verifyCode1').val();
	params.rememberPwd = $('#rememberPwd1').attr('checked')?1:0;
    if(window.conf.IS_CRYPT=='1'){
        var public_key=$('#token').val();
        var exponent="10001";
   	    var rsa = new RSAKey();
        rsa.setPublic(public_key, exponent);
        params.loginPwd = rsa.encrypt(params.loginPwd);
        params.rememberPwd = rsa.encrypt(params.rememberPwd);
    }
	var ll = WST.msg(WST.lang('thirdlogin_loading'),{time:600000});
	$.post(WST.U('home/users/checkLogin'),params,function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status=='1'){
			if(typ==2){
				location.href=WST.U('home/shops/index');
			}else if(typ==1){
				location.href=WST.U('home/users/index');
			}else{
				parent.location.reload();
			}
		}else{
			layer.close(ll);
			WST.msg(json.msg, {icon: 5});
			WST.getVerify('#verifyImg1','#verifyCode1');
		}
		
	});
	return true;
}


function showProtocol(){
	layer.open({
	    type: 2,
	    title: WST.lang('thirdlogin_user_registration_agreement'),
	    shadeClose: true,
	    shade: 0.8,
	    area: ['1000px', ($(window).height() - 50) +'px'],
	    content: [WST.U('home/users/protocol')],
	    btn: [WST.lang('thirdlogin_agree_and_register')],
	    yes: function(index, layero){
	    	layer.close(index);
	    }
	});
}

var time = 0;
var isSend = false;
var isUse = false;
var index2 = null;
function getVerifyCode(){
	var params = {};
		params.userPhone = $.trim($("#loginName").val());
	if(params.userPhone==''){
		WST.msg(WST.lang('thirdlogin_please_input_mobile_phone_number'), {icon: 5});
		return;
	}
	params.areaCode = $.trim($("#areaCode").val());
	if(isSend )return;
	isSend = true;
	if(WST.conf.SMS_VERFY=='1'){
		var html = [];
			html.push('<table class="wst-smsverfy"><tr><td width="80" align="right">');
			html.push(WST.lang('thirdlogin_verification_Code')+'：</td><td><input type="text" id="smsVerfyl" size="12" class="wst-text" maxLength="8">');
			html.push('<img style="vertical-align:middle;cursor:pointer;height:39px;" class="verifyImgd" src="'+WST.DOMAIN+'/wstmart/Home/View/default/images/clickForVerify.png" title="'+WST.lang('thirdlogin_refresh_captcha')+'" onclick="javascript:WST.getVerify(\'.verifyImgd\')"/>');
			html.push('</td></tr></table>');
		index2 = layer.open({
			title:WST.lang('thirdlogin_please_input_verification_code'),
			type: 1,
			area: ['420px', '150px'], //宽高
			content: html.join(''),
			btn: [WST.lang('thirdlogin_send_verification_code')],
			success: function(layero, index){
				WST.getVerify('.verifyImgd');
			},
			yes: function(index, layero){
				isSend = true;
				params.smsVerfy = $.trim($('#smsVerfyl').val());
			 	if(params.smsVerfy==''){
   			  		WST.msg(WST.lang('thirdlogin_please_enter_the_verification_code'), {icon: 5});
   			   		return;
   			 	}
				getPhoneVerifyCode(params);
			},
			cancel:function(){
				isSend = false;
			}
		});
	}else{
		isSend = true;
		getPhoneVerifyCode(params);
	}
}

function getPhoneVerifyCode(params){
	WST.msg(WST.lang('thirdlogin_loading'),{time:600000});
	$.post(WST.U('home/users/getPhoneVerifyCode'),params,function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status!=1){
			WST.msg(json.msg, {icon: 5});
			time = 0;
			isSend = false;
		}if(json.status==1){
			WST.msg(WST.lang('thirdlogin_tips2'));
			time = 120;
			$('#timeTips').css('width','100px');
			$('#timeTips').html(WST.lang('thirdlogin_op1')+'(120)');
			$('#mobileCode').val(json.phoneVerifyCode);
			var task = setInterval(function(){
				time--;
				$('#timeTips').html(WST.lang('thirdlogin_op1')+'('+time+")");
				if(time==0){
					isSend = false;						
					clearInterval(task);
					$('#timeTips').html(WST.lang('thirdlogin_tips3'));
				}
			},1000);
		}
		if(json.status!=-2)layer.close(index2);
	});
}
function initRegist(){
	$('#reg_form').validator({
	    rules: {
	    	loginName: function(element) {
	    		//if(this.test(element, "mobile")===true){
	    			if(WST.conf.SMS_OPEN=='1'){
		    			$("#mobileCodeDiv").show();
		    			$("#authCodeDiv").hide();
		    			$("#nameType").val('3');
	    			}else{
		    			$("#nameType").val('2');
	    			}
	    		//}
	            //return this.test(element, "mobile")===true || WST.lang('thirdlogin_tips4');
	        },
	        //自定义remote规则（注意：虽然remote规则已经内置，但这里的remote会优先于内置）
	        remote: function(element){
	        	return $.post(WST.U('home/users/checkLoginKey'),{"loginName":element.value},function(data,textStatus){
	        	
	        	});
	        }
	    },
	    fields: {
	        'loginName': 'required; loginName; remote;',
	        'loginPwd' : WST.lang('thirdlogin_password')+':required; password;',
	        'reUserPwd': WST.lang('thirdlogin_confirm_password')+':required; match(loginPwd);',
	        'mobileCode': {rule:"required(mobileCode)",msg:{required:WST.lang('thirdlogin_please_input_SMS_verification_code')}},
	        'verifyCode': {rule:"required(verifyCode)",msg:{required:WST.lang('thirdlogin_please_input_verification_code')}}
	    },
	    // 表单验证通过后，ajax提交
	    valid: function(form){
	        var me = this;
	        // ajax提交表单之前，先禁用submit
	        me.holdSubmit();
	        var params = WST.getParams('.wst_ipt');
	        if(window.conf.IS_CRYPT=='1'){
	            var public_key=$('#token').val();
	            var exponent="10001";
	       	    var rsa = new RSAKey();
	            rsa.setPublic(public_key, exponent);
	            params.loginPwd = rsa.encrypt(params.loginPwd);
	            params.reUserPwd = rsa.encrypt(params.reUserPwd);
	        }
	        params.nameType=3;
	        $("#reg_butt").css('color', '#999').text(WST.lang('thirdlogin_loading'));
	        $.post(WST.U('home/users/toRegist'),params,function(data,textStatus){
	    		var json = WST.toJson(data);
	    		if(json.status>0){
	    			WST.msg(WST.lang('thirdlogin_tips5'), {icon: 6}, function(){
	    				location.href=WST.U('home/users/index');
	       			});
	    		}else{
	    			me.holdSubmit(false);
	    			WST.getVerify('#verifyImg');
	    			WST.msg(json.msg, {icon: 5});
	    		}
	    		
	    	});
	    }
	});
}