
function getPayUrl(){
	var params = {};
		params.payObj = "recharge";
		params.targetType = 1;
		params.needPay = $.trim($("#needPay").val());
		params.payCode = $.trim($("#payCode").val());
		params.itmeId = $.trim($("#itmeId").val());
	if(params.itmeId==0 && params.needPay<=0){
		WST.msg(WST.lang('require_recharge_money'), {icon: 5});
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
			WST.msg(WST.lang('recharge_error'), {icon: 5});
		}
	});
}

function inEffect(obj,n){
	$(obj).addClass('j-selected').siblings('.wst-frame'+n).removeClass('j-selected');
}
function changeSelected(n,index,obj){
	$('#'+index).val(n);
	if(n==0){
		$(".j-charge-other").hide();
		$(".j-charge-money").show();

	}else{
		$(".j-charge-other").show();
		$(".j-charge-money").hide();
	}
	inEffect(obj,2);
}
$(function(){
	$(".wst-frame2:first").click();
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
