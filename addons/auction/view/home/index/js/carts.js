function getPayUrl(){
	var params = {};
		params.payObj = $.trim($("#payObj").val());
		params.payCode = $.trim($("#payCode").val());
		params.auctionId = $.trim($("#auctionId").val());
	if(params.payCode==""){
		WST.msg(WST.lang('auction_please_select_pay_type'), {icon: 5});
		return;
	}

	jQuery.post(WST.AU('auction://'+params.payCode+'/get'+params.payCode+"url"),params,function(data) {
		var json = WST.toJson(data);
		if(json.status==1){
			if(params.payCode=="weixinpays" || params.payCode=="ccgwpays" || params.payCode=="wallets"){
				location.href = json.url;
			}else if(params.payCode=="unionpays"){
				location.href = WST.AU('auction://unionpays/tounionpays',params);
			}else{
				$("#alipayform").html(json.result);
			}
		}else{
			WST.msg(json.msg?json.msg:WST.lang('auction_transfer_payment_failed'), {icon: 5,time:2500},function(){});
		}
	});
}

function payByWallet(){
    var params = WST.getParams('.j-ipt');
	var load = WST.load({msg:WST.lang('auction_checking_pay_password_tips')});
    if(window.conf.IS_CRYPT=='1'){
        var public_key=$('#token').val();
        var exponent="10001";
   	    var rsa = new RSAKey();
        rsa.setPublic(public_key, exponent);
        params.payPwd = rsa.encrypt(params.payPwd);
    }
	$.post(WST.AU('auction://wallets/paybywallet'),params,function(data,textStatus){
		layer.close(load);   
		var json = WST.toJson(data);
	    if(json.status==1){
	    	WST.msg(json.msg, {icon: 1,time:1500},function(){
	    		if(params.payObj=="bao"){
	    			window.location = WST.AU('auction://wallets/paySuccess');
	    		}else{
	    			window.location = WST.AU('auction://users/checkPayStatus',{"id":params.auctionId});
	    		}
	    	});
	    }else{
	    	WST.msg(json.msg,{icon:2,time:1500});
	    }
	});
}

function setPaypwd(){
	layerbox =	layer.open({
		title:[WST.lang('auction_set_pay_password'),'text-align:left'],
		type: 1,
		area: ['450px', '240px'],
		content: $('.j-paypwd-box'),
		btn: [WST.lang('auction_set_pay_password_and_pay_order'), WST.lang('auction_close')],
		yes: function(index, layero){
			var newPass = $.trim($("#payPwd").val());
			var reNewPass = $.trim($("#reNewPass").val());
			if(newPass==""){
				WST.msg(WST.lang('auction_please_input_paypwd'));
				return false;
			}
			if(reNewPass==""){
				WST.msg(WST.lang('auction_please_input_confirm_paypwd'));
				return false;
			}
			if(newPass!=reNewPass){
				WST.msg(WST.lang('auction_password_not_same'));
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
			var load = WST.load({msg:WST.lang('auction_submitting_paypwd')});
			$.post(WST.U('home/users/payPassEdit'),{newPass:newPass,reNewPass:reNewPass},function(data,textStatus){
				layer.close(load);   
				var json = WST.toJson(data);
			    if(json.status==1){
			    	WST.msg(json.msg, {icon: 1,time:1500},function(){
			    		layer.close(layerbox);
		                payByWallet();
			    	});
			    }else{
			    	WST.msg(json.msg,{icon:2,time:1500});
			    }
			});
			
	    	return false;
	  	},
	  	btn2: function(index, layero){}
	});
}

var invoicebox;
function changeInvoice(t,str,obj){
	var param = {};
	param.isInvoice = $('#isInvoice').val();
	param.invoiceId = $('#invoiceId').val();
	var loading = WST.load({msg:WST.lang('auction_loading_data')});
	$.post(WST.U('home/invoices/index'),param,function(data){
		layer.close(loading);
		// layer弹出层
		invoicebox =	layer.open({
			title:WST.lang('auction_invoice_information'),
			type: 1,
			area: ['628px', '420px'], //宽高
			content: data,
			success :function(){
				if(param.invoiceId>0){
				    $('.inv_codebox').show();
				    $('#invoice_num').val($('#invoiceCode_'+param.invoiceId).val());
				 }
			},
		});
	});
}
function layerclose(){
  layer.close(invoicebox);
}