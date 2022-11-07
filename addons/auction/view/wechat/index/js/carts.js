
//跳转支付
function toPay(payCode){
	var params = {};
	params.auctionId = $.trim($("#auctionId").val());
	params.payObj = $.trim($("#payObj").val());
	params.payFrom = 2;
	var client = (payCode=="weixinpays")?"wx":"";
	$.post(WST.AU('auction://'+payCode+client+'/get'+payCode+"url"),params,function(data) {
		var json = WST.toJson(data);
		if(json.status==1){
			if(payCode=="unionpays"){
				location.href = WST.AU('auction://unionpays/tounionpays',params);
			}else if(payCode=="weixinpays" && client=="wx"){
				location.href = WST.AU('auction://weixinpayswx/topay',params);
			}else{
				location.href = json.url;
			}
		}else{
			WST.msg(json.msg, {icon: 5,time:1500},function(){});
		}
	});
}

//余额支付
function walletPay(type){
	var payPwd = $('#payPwd').val();
	if(!payPwd){
		WST.msg(WST.lang('auction_please_input_paypwd'),'info');
		return;
	}
	if(type==0){
		var payPwd2 = $('#payPwd2').val();
		if(payPwd2==''){
	    	WST.msg(WST.lang('auction_please_input_confirm_paypwd'),'info');
	        return false;
	    }
		if(payPwd!=payPwd2){
	    	WST.msg(WST.lang('auction_password_not_same'),'info');
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
		    	WST.load(WST.lang('auction_password_success_to_pay'));
		    }else{
		    	WST.msg(json.msg,'info');
		    }
		});
	}else{
		WST.load('正在核对密码···');
	}
    var auctionId = $('#auctionId').val();
    var payObj = $('#payObj').val();
    params.payPwd = payPwd;
    params.pkey = $('#pkey').val();
    $('.wst-btn-dangerlo').attr('disabled', 'disabled');
    setTimeout(function(){
	$.post(WST.AU('auction://wallets/paybywallet'),params,function(data,textStatus){
		WST.noload(); 
		var json = WST.toJson(data);
	    if(json.status==1){
	    	WST.msg(json.msg,'success');
	        setTimeout(function(){
	        	if(payObj=='bao'){
		        	location.href=WST.AU('auction://goods/wxdetail','id='+auctionId);
	        	}else{
	        		location.href=WST.AU('auction://users/wxcheckPayStatus','id='+auctionId);
	        	}
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