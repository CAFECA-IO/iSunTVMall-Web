function getPayUrl(){
	var params = {};
		params.payObj = "enter";
		params.needPay = $.trim($("#needPay").val());
		params.payCode = $.trim($("#payCode").val());
	if(params.needPay==0){
		WST.msg(WST.lang('annual_fee_error'), {icon: 5});
		return;
	}
	if(params.payCode==""){
		WST.msg(WST.lang('pls_select_pay_type'), {icon: 5});
		return;
	}
	jQuery.post(WST.U('supplier/'+params.payCode+'/get'+params.payCode+"URL"),params,function(data) {
		var json = WST.toJson(data);
		if(json.status==1){
			if(params.payCode=="alipays"){
				$("#alipayform").html(json.result);
			}else{
				location.href = json.url;
			}
		}else{
			WST.msg(WST.lang('pay_annual_fee_fail'), {icon: 5});
		}
	});
}

$(function(){
	$("#wst-check-orders").click(function(){
		$("#wst-orders-box").slideToggle(600);
	});
	$("div[class^=wst-payCode]").click(function(){
		var payCode = $(this).attr("data");
		$("div[class^=wst-payCode]").each(function(){
			$(this).removeClass().addClass("wst-payCode-"+$(this).attr("data"));
		});
		$(this).removeClass().addClass("wst-payCode-"+payCode+"-curr");
		$("#payCode").val(payCode);
		if(payCode=="paypals"){
			$(".wst-pay-bnt").hide();
			$("#paypalBox").show();
		}else{
			$(".wst-pay-bnt").show();
			$("#paypalBox").hide();
		}
	});
	if($("div[class^=wst-payCode]").length>0){
		$("div[class^=wst-payCode]")[0].click();
	}
});

function renew(){
	var loading = WST.msg(WST.lang('submitting_data'), {icon: 16,time:60000});
	$.post(WST.U('supplier/suppliers/renew'),{},function(data,textStatus){
		layer.close(loading);
		var json = WST.toJson(data);
		if(json.status=='1'){
			WST.msg(json.msg,{icon:1});
		}else{
			WST.msg(json.msg,{icon:2});
		}
	});
}

function payByWallet(){
	var params = WST.getParams('.j-ipt');
	if(params.payPwd==""){
		WST.msg(WST.lang('require_password'), {icon: 5});
		return;
	}
	if(window.conf.IS_CRYPT=='1'){
		var public_key=$('#token').val();
		var exponent="10001";
		var rsa = new RSAKey();
		rsa.setPublic(public_key, exponent);
		params.payPwd = rsa.encrypt(params.payPwd);
	}
	var load = WST.load({msg:WST.lang('checking_pay_pwd')});
	$.post(WST.U('supplier/wallets/payByWallet'),params,function(data,textStatus){
		layer.close(load);
		var json = WST.toJson(data);
		if(json.status==1){
			WST.msg(json.msg, {icon: 1,time:1500},function(){
				window.location = WST.U('supplier/logmoneys/suppliermoneys');
			});
		}else{
			WST.msg(json.msg,{icon:2,time:1500});
		}
	});
}

function setPaypwd(){
	layerbox =	layer.open({
		title:[WST.lang('set_pay_pwd'),'text-align:left'],
		type: 1,
		area: ['450px', '240px'],
		content: $('.j-paypwd-box'),
		btn: [WST.lang('set_pay_pwd_and_pay'), WST.lang('close')],
		yes: function(index, layero){
			var newPass = $.trim($("#payPwd").val());
			var reNewPass = $.trim($("#reNewPass").val());
			if(newPass==""){
				WST.msg(WST.lang("require_pay_pwd"));
				return false;
			}
			if(reNewPass==""){
				WST.msg(WST.lang("require_confirm_pay_pwd"));
				return false;
			}
			if(newPass!=reNewPass){
				WST.msg(WST.lang("pay_pwd_different"));
				return false;
			}
			if(window.conf.IS_CRYPT=='1'){
				var public_key=$('#token').val();
				var exponent="10001";
				var rsa = new RSAKey();
				rsa.setPublic(public_key, exponent);
				newPass = rsa.encrypt(newPass);
				reNewPass = rsa.encrypt(reNewPass);
			}
			var load = WST.load({msg:WST.lang('submitting_data')});
			$.post(WST.U('supplier/users/payPassEdit'),{newPass:newPass,reNewPass:reNewPass},function(data,textStatus){
				layer.close(load);
				var json = WST.toJson(data);
				if(json.status==1){
					WST.msg(json.msg, {icon: 1,time:1500},function(){
						layer.close(layerbox);
						$('#paypwd-box').hide();
						payByWallet();
					});
				}else{
					WST.msg(json.msg,{icon:2,time:1500});
				}
			});

			return false;
		},
		btn2: function(index, layero){
			$('#paypwd-box').hide();
		}
	});
}
