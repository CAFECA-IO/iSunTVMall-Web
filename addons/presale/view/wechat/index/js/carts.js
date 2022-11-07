
//跳转支付
function toPay(payCode){
	var params = {};
	params.pkey = $.trim($("#pkey").val());
	params.payObj = $.trim($("#payObj").val());
	params.payFrom = 2;
	var client = (payCode=="weixinpays")?"wx":"";
	$.post(WST.AU('presale://'+payCode+client+'/get'+payCode+"url"),params,function(data) {
		var json = WST.toJson(data);
		if(json.status==1){
			if(payCode=="weixinpays"){
				location.href = WST.AU('presale://weixinpayswx/topay',params);
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
		WST.msg('请输入支付密码','info');
		return;
	}
	if(type==0){
		var payPwd2 = $('#payPwd2').val();
		if(payPwd2==''){
	    	WST.msg('确认密码不能为空','info');
	        return false;
	    }
		if(payPwd!=payPwd2){
	    	WST.msg('确认密码不一致','info');
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
		$.post(WST.U('wechat/users/editpayPwd'),params,function(data,textStatus){
			WST.noload(); 
			var json = WST.toJson(data);
		    if(json.status==1){
		    	WST.load('成功设置密码，<br>订单支付中···');
		    }else{
		    	WST.msg(json.msg,'info');
		    }
		});
	}else{
		WST.load('正在核对密码···');
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
	    	WST.msg("支付成功",'success');
	        setTimeout(function(){
	        	location.href=WST.AU('presale://users/wxlist');
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