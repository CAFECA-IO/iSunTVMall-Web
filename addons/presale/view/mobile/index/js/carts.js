
//跳转支付
function toPay(payCode){
	var params = {};
	params.pkey = $.trim($("#pkey").val());
	var client = (payCode=="alipays" || payCode=="weixinpays")?"mo":"";
	$.post(WST.AU('presale://'+payCode+client+'/get'+payCode+"url"),params,function(data) {
		var json = WST.toJson(data);
		if(json.status==1){
			if(payCode=="weixinpays"){
				location.href = WST.AU('presale://weixinpaysmo/topay',params);
			}else if(payCode=="paypals"){
				location.href = WST.AU('presale://paypalsmo/topay',params);
			}else if(payCode=="alipays"){
				location.href = WST.AU('presale://alipaysmo/toalipay',params);
			}else if(payCode=="ccgwpays"){
				location.href = WST.AU('presale://ccgwpaysmo/topay',params);
			}else{
				location.href = json.url;
			}
		}else{
			WST.msg(json.msg, {icon: 5,time:1500});
		}
	});
}

//余额支付
function walletPay(type){
	var payPwd = $('#payPwd').val();
	if(!payPwd){
		WST.msg(WST.lang('presale_please_input_paypwd'),'info');
		return;
	}
	if(type==0){
		var payPwd2 = $('#payPwd2').val();
		if(payPwd2==''){
	    	WST.msg(WST.lang('presale_please_input_confirm_paypwd'),'info');
	        return false;
	    }
		if(payPwd!=payPwd2){
	    	WST.msg(WST.lang('presale_password_not_same'),'info');
	        return false;
	    }
	}
    if(window.conf.IS_CRYPTPWD==1){
        var public_key=$('#key').val();
        var exponent="10001";
   	    var rsa = new RSAKey();
        rsa.setPublic(public_key, exponent);
        var payPwd = rsa.encrypt(payPwd);
    }
	var params = {};
	if(type==0){
		params.newPass = payPwd;
		$.post(WST.U('mobile/users/editpayPwd'),params,function(data,textStatus){
			WST.noload();
			var json = WST.toJson(data);
		    if(json.status==1){
		    	WST.load(WST.lang('presale_success_set_password_and_paying'));
		    }else{
		    	WST.msg(json.msg,'info');
		    }
		});
	}else{
		WST.load(WST.lang('presale_checking_password'));
	}
    var porderId = $('#porderId').val();
    var payObj = $('#payObj').val();
    params.payPwd = payPwd;
    params.pkey = $('#pkey').val();
    $('.wst-btn-dangerlo').attr('disabled', 'disabled');
    setTimeout(function(){
	$.post(WST.AU('presale://wallets/paybywallet'),params,function(data,textStatus){
		WST.noload();
		var json = WST.toJson(data);
	    if(json.status==1){
	    	WST.msg(WST.lang("presale_pay_success"),'success');
	        setTimeout(function(){
	        	location.href=WST.AU('presale://users/molist');
	        },2000);
	    }else{
	    	WST.msg(json.msg,'info');
	        setTimeout(function(){
	            $('.wst-btn-dangerlo').removeAttr('disabled');
	        },2000);
	    }
	});
    },1000);
}
