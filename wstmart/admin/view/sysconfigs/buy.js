var layer = layui.layer;
$(function(){
	form = layui.form;
  	form.render();
	form.on('switch(isOpenScorePay)', function(data){
	  	if(this.checked){
	  		WST.showHide(1,'.scoreToMoneyTr')
	  	}else{
	  		WST.showHide(0,'.scoreToMoneyTr')
	  	}
	});
	form.on('switch(isOrderScore)', function(data){
	  	if(this.checked){
	  		WST.showHide(1,'#moneyToScoreTr')
	  	}else{
	  		WST.showHide(0,'#moneyToScoreTr')
	  	}
	});
	form.on('switch(isAppraisesScore)', function(data){
	  	if(this.checked){
	  		WST.showHide(1,'#appraisesScoreTr')
	  	}else{
	  		WST.showHide(0,'#appraisesScoreTr')
	  	}
	});
});

function edit(){
	if(!WST.GRANT.GWSZ_02)return;
	var params = WST.getParams('.ipt');
	if(parseInt(params.drawCashCommission<0) || parseInt(params.drawCashCommission)>100){
		WST.msg(WST.lang('sys_config_cash_draw_fee_tips'),{icon:1});
		return;
	}
	// 售后有效日 需大于整个流程售后流程限定日
	var afterSaleServiceDays = parseInt(params.afterSaleServiceDays);

	var shopAcceptDays = parseInt(params.shopAcceptDays);
	var userSendDays = parseInt(params.userSendDays);
	var shopReceiveDays = parseInt(params.shopReceiveDays);
	var shopSendDays = parseInt(params.shopSendDays);
	var userReceiveDays = parseInt(params.userReceiveDays);
	var totalAfterSaleDays = shopAcceptDays+userSendDays+shopReceiveDays+shopSendDays+userReceiveDays;
	if(afterSaleServiceDays<=totalAfterSaleDays){
		return WST.msg(WST.lang("sys_config_after_sale_tips"));
	}

	var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/sysconfigs/editBuyConfig'),params,function(data,textStatus){
          layer.close(loading);
          var json = WST.toAdminJson(data);
          WST.msg(json.msg,{icon:1});
   });
}
