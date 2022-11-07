var WSTHook_beforeStatCartMoney = [],WSTHook_beforeStatGoodsMoney = [];
function checkChks(obj,cobj){
	WST.checkChks(obj,cobj);
	var ids = [];
	$(cobj).each(function(){
		id = $(this).val();
		if(obj.checked){
			$(this).addClass('selected');
		}else{
			$(this).removeClass('selected');
		}
		var cid = $(this).find(".j-chk").val();
		if(cid!='' && typeof(cid)!='undefined'){
			ids.push(cid);
		    statCartMoney();
	    }
	});
	batchChangeCartGoods(ids.join(','),obj.checked?1:0);
}
function batchChangeCartGoods(ids,isCheck){
    $.post(WST.U('home/carts/batchChangeCartGoods'),{ids:ids,isCheck:isCheck,rnd:Math.random()},function(data,textStatus){
	     var json = WST.toJson(data);
	     if(json.status!=1){
	    	 WST.msg(json.msg,{icon:2});
	     }
	});
}
function statCartMoney(){
	var cartMoney = 0,goodsTotalPrice,id;
	$('.j-gchk').each(function(){
		id = $(this).val();
		if(WSTHook_beforeStatGoodsMoney.length>0){
			for(var i=0;i<WSTHook_beforeStatGoodsMoney.length;i++){
				delete window['callback_'+WSTHook_beforeStatGoodsMoney[i]];
				window[WSTHook_beforeStatGoodsMoney[i]](id);
				if(window['callback_'+WSTHook_beforeStatGoodsMoney[i]]){
					window['callback_'+WSTHook_beforeStatGoodsMoney[i]]();
					return;
				 }
			}
		}
		goodsTotalPrice = parseFloat($(this).attr('mval'))*parseInt($('#buyNum_'+id).val());
		$('#tprice_'+id).html(goodsTotalPrice.toFixed(2));
		if($(this).prop('checked')){
			cartMoney = cartMoney + goodsTotalPrice;
		}
	});
	var minusMoney = 0;
	if(WSTHook_beforeStatCartMoney.length>0){
		for(var i=0;i<WSTHook_beforeStatCartMoney.length;i++){
			delete window['callback_'+WSTHook_beforeStatCartMoney[i]];
			minusMoney = window[WSTHook_beforeStatCartMoney[i]](cartMoney);
			if(window['callback_'+WSTHook_beforeStatCartMoney[i]]){
				window['callback_'+WSTHook_beforeStatCartMoney[i]]();
				return;
			 }
			cartMoney = cartMoney - minusMoney;
		}
	}
	$('#totalMoney').html(cartMoney.toFixed(2));
	checkGoodsBuyStatus();
}
function checkGoodsBuyStatus(){
	var cartNum = 0,stockNum = 0,cartId = 0;
	$('.j-gchk').each(function(){
		cartId = $(this).val();
		cartNum = parseInt($('#buyNum_'+cartId).val(),10);
		stockNum = parseInt($(this).attr('sval'),10);;
		if(stockNum < 0 || stockNum < cartNum){
			if($(this).prop('checked')){
				$(this).parent().parent().css('border','2px solid red');
			}else{
				$(this).parent().parent().css('border','0px solid #eeeeee');
				$(this).parent().parent().css('border-bottom','1px solid #eeeeee');
			}
			if(stockNum < 0){
				$('#gchk_'+cartId).attr('allowbuy',0);
				$('#err_'+cartId).css('color','red').html(WST.lang('cart_under_stock'));
			}else{
				$('#gchk_'+cartId).attr('allowbuy',1);
				$('#err_'+cartId).css('color','red').html(WST.lang('cart_buy_over_stock'));
			}
		}else{
			$('#gchk_'+cartId).attr('allowbuy',10);
			$(this).parent().parent().css('border','0px solid #eeeeee');
			$(this).parent().parent().css('border-bottom','1px solid #eeeeee');
			$('#err_'+cartId).html('');
		}
	});
}
function toSettlement(){
	var isChk = false;
	$('.j-gchk').each(function(){
		if($(this).prop('checked'))isChk = true;
	});
	if(!isChk){
		WST.msg(WST.lang('cart_select_settlement_goods'),{icon:1});
		return;
	}
	var msg = '';
	$('.j-gchk').each(function(){
		if($(this).prop('checked')){
			if($(this).attr('allowbuy')==0){
				msg = WST.lang('cart_select_goods_under_stock');
				return;
			}else if($(this).attr('allowbuy')==1){
				msg = WST.lang('cart_select_goods_buynum_gt_stock');
				return;
			}
		}
	})
	if(msg!=''){
		WST.msg(msg,{icon:2});
		return;
	}
	location.href=WST.U('home/carts/settlement');
}

function addrBoxOver(t){
	$(t).addClass('radio-box-hover');
	$(t).find('.operate-box').show();
}
function addrBoxOut(t){
	$(t).removeClass('radio-box-hover');
	$(t).find('.operate-box').hide();
}



function setDeaultAddr(id){
	$.post(WST.U('home/useraddress/setDefault'),{id:id},function(data){
		var json = WST.toJson(data);
		if(json.status==1){
			getAddressList();
			changeAddrId(id);
		}
	});
}


function changeAddrId(id){
	$.post(WST.U('home/useraddress/getById'),{id:id},function(data){
		var json = WST.toJson(data);
		if(json.status==1){
			inEffect($('#addr-'+id),1);
			$('#s_addressId').val(json.data.addressId);
			$("select[id^='area_0_']").remove();
			var areaIdPath = json.data.areaIdPath.split("_");
			// 设置收货地区市级id
			$('#s_areaId').val(areaIdPath[1]);

	     	$('#area_0').val(areaIdPath[0]);

			$("input[id^='deliverType_']").val(0);
			$("input[id^='storeId_']").val(0);
			$("input[id^='store_areaId_']").val(json.data.areaId);
			$("input[id^='store_areaIdPath_']").val(json.data.areaIdPath);

			// 计算运费
	     	getCartMoney();
	     	var aopts = {id:'area_0',val:areaIdPath[0],childIds:areaIdPath,className:'j-areas'}
	 		WST.ITSetAreas(aopts);
			WST.setValues(json.data);
		}
	})
}

function delAddr(id){
	WST.confirm({content:WST.lang('confirm_del_inquiry'),yes:function(index){
		$.post(WST.U('home/useraddress/del'),{id:id},function(data,textStatus){
		     var json = WST.toJson(data);
		     if(json.status==1){
		    	 WST.msg(json.msg,{icon:1});
		    	 getAddressList();
		     }else{
		    	 WST.msg(json.msg,{icon:2});
		     }
		});
	}});
}

function getAddressList(obj){
	var id = $('#s_addressId').val();
	var load = WST.load({msg:WST.lang('loading_tips')});
	$.post(WST.U('home/useraddress/listQuery'),{rnd:Math.random()},function(data,textStatus){
		 layer.close(load);
	     var json = WST.toJson(data);
	     if(json.status==1){
	    	 if(json.data && json.data && json.data.length){
	    		 var html = [],tmp;
	    		 for(var i=0;i<json.data.length;i++){
	    			 tmp = json.data[i];
	    			 var selected = (id==tmp.addressId)?'j-selected':'';
	    			 html.push(
	    					 '<div class="wst-frame1 '+selected+'" onclick="javascript:changeAddrId('+tmp.addressId+')" id="addr-'+tmp.addressId+'" >'+tmp.userName+'<i></i></div>',
	    					 '<li class="radio-box" onmouseover="addrBoxOver(this)" onmouseout="addrBoxOut(this)">',
	    					 tmp.userName,
	    					 '&nbsp;&nbsp;',
	    					 tmp.areaName+tmp.userAddress,
	    					 '&nbsp;&nbsp;&nbsp;&nbsp;',
	    					 '+'+tmp.areaCode,
	    					 '&nbsp;',
	    					 tmp.userPhone
	    					 )
	    			if(tmp.isDefault==1){
	    				html.push('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="j-default">'+WST.lang('default_address')+'</span>')
	    			}
	    			html.push('<div class="operate-box">');
	    			if(tmp.isDefault!=1){
	    				html.push('<a href="javascript:;" onclick="setDeaultAddr('+tmp.addressId+')">'+WST.lang('set_default_address')+'</a>&nbsp;&nbsp;');
	    			}
	    			html.push('<a href="javascript:void(0)" onclick="javascript:toEditAddress('+tmp.addressId+',this,1,1)">'+WST.lang('edit')+'</a>&nbsp;&nbsp;');
	    			if(json.data.length>1){
	    				html.push('<a href="javascript:void(0)" onclick="javascript:delAddr('+tmp.addressId+',this)">'+WST.lang('del')+'</a></div>');
	    			}
	    			html.push('<div class="wst-clear"></div>','</li>');
	    		 }
	    		 html.push('<a style="color:#1c9eff" onclick="editAddress()" href="javascript:;">'+WST.lang('stow_address')+'</a>');


	    		 $('#addressList').html(html.join(''));
	    	 }else{
	    		 $('#addressList').empty();
	    	 }
	     }else{
	    	 $('#addressList').empty();
	     }
	})
}
function inEffect(obj,n){
	$(obj).addClass('j-selected').siblings('.wst-frame'+n).removeClass('j-selected');
}
function editAddress(){
	var isNoSelected = false;
	$('.j-areas').each(function(){
		isSelected = true;
		if($(this).val()==''){
			isNoSelected = true;
			return;
		}
	})
	if(isNoSelected){
		WST.msg(WST.lang('select_complete_address'),{icon:2});
		return;
	}
	layer.close(layerbox);
	var load = WST.load({msg:WST.lang('submiting_tips')});
	var params = WST.getParams('.j-eipt');
	params.areaId = WST.ITGetAreaVal('j-areas');
	$.post(WST.U('home/useraddress/'+((params.addressId>0)?'toEdit':'add')),params,function(data,textStatus){
		layer.close(load);
		var json = WST.toJson(data);
	     if(json.status==1){
	    	 $('.j-edit-box').hide();
	    	 $('.j-list-box').hide();
	    	 $('.j-show-box').show();
	    	 if(params.addressId==0){
	    		 $('#s_addressId').val(json.data.addressId);
	    		 $('#s_address').siblings('.operate-box').find('a').attr('onclick','toEditAddress('+json.data.addressId+',this,1,1,1)');
	    	 }else{
	    		 $('#s_addressId').val(params.addressId);
	    		 $('#s_address').siblings('.operate-box').find('a').attr('onclick','toEditAddress('+params.addressId+',this,1,1,1)');
	    	 }

	    	 var areaIds = WST.ITGetAllAreaVals('area_0','j-areas');
	    	 $('#s_areaId').val(areaIds[1]);
	    	 getCartMoney();
	    	 var areaNames = [];
	    	 $('.j-areas').each(function(){
	    		 areaNames.push($('#'+$(this).attr('id')+' option:selected').text());
	    	 })
	    	 $('#s_userName').html(params.userName+'<i></i>');
	    	 $('#s_address').html(params.userName+'&nbsp;&nbsp;&nbsp;'+areaNames.join('')+'&nbsp;&nbsp;'+params.userAddress+'&nbsp;&nbsp;+'+params.areaCode+'&nbsp;'+params.userPhone);



	    	 if(params.isDefault==1){
	    		 $('#isdefault').html('默认地址').addClass('j-default');
	    	 }else{
	    		 $('#isdefault').html('').removeClass('j-default');
	    	 }
	    	 $("input[id^='store_areaId_']").val(areaIds[2]);
	    	 $("input[id^='store_areaIdPath_']").val(areaIds.join("_"));
	    	 $("input[id^='deliver_info_']").html("").hide();
	    	 $("div[id^='deliverType0_']").click();
	     }else{
	    	 WST.msg(json.msg,{icon:2});
	     }
	});
}
var layerbox;
function showEditAddressBox(){
	getAddressList();
	toEditAddress();
}
function emptyAddress(obj,n){
	inEffect(obj,n);
	$('#addressForm')[0].reset();
	$('#s_addressId').val(0);
	$('#addressId').val(0);
	$("select[id^='area_0_']").remove();

	layerbox =	layer.open({
					title:WST.lang('user_address'),
					type: 1,
					area: ['800px', '300px'],
					content: $('.j-edit-box')
					});
}
function toEditAddress(id,obj,n,flag,type){
	inEffect(obj,n);
	id = (id>0)?id:$('#s_addressId').val();
	$.post(WST.U('home/useraddress/getById'),{id:id},function(data,textStatus){
	     var json = WST.toJson(data);
	     if(json.status==1){
	     	if(flag){
		     	layerbox =	layer.open({
					title:WST.lang('user_address'),
					type: 1,
					area: ['800px', '300px'], //宽高
					content: $('.j-edit-box')
				});
	     	}
	     	if(type!=1){
				 $('.j-list-box').show();
		    	 $('.j-show-box').hide();
	     	}
	    	 WST.setValues(json.data);
	    	 $("select[id^='area_0_']").remove();
	    	 if(id>0){
		    	 var areaIdPath = json.data.areaIdPath.split("_");
		     	 $('#area_0').val(areaIdPath[0]);
		     	 var aopts = {id:'area_0',val:areaIdPath[0],childIds:areaIdPath,className:'j-areas'}
		 		 WST.ITSetAreas(aopts);
	    	 }
	     }else{
	    	 WST.msg(json.msg,{icon:2});
	     }
	});
}
function getCartMoney(){
	var params = WST.getParams('.j-deliver');;
	params.isUseScore = $('#isUseScore').prop('checked')?1:0;
	params.useScore = $('#useScore').val();
	params.areaId2 = $('#s_areaId').val();
	params.rnd = Math.random();
	//params.deliverType = $('#deliverType').val();
	var couponIds = [];
	$('.j-shop').each(function(){
		couponIds.push($(this).attr('dataval')+":"+$('#couponId_'+$(this).attr('dataval')).val());
	});
	params.couponIds = couponIds.join(',');
	var load = WST.load({msg:WST.lang('calculating_price_tips')});
	$.post(WST.U('home/carts/getCartMoney'),params,function(data,textStatus){
		layer.close(load);
		var json = WST.toJson(data);
		if(json.status==1){
		    json = json.data;
		    var shopFreight = 0;
		    for(var key in json.shops){
		    	// 设置每间店铺的运费及总价格
		    	$('#shopF_'+key).html(json.shops[key]['freight']);
		    	$('#shopC_'+key).html(json.shops[key]['goodsMoney']);
		    	shopFreight = shopFreight + json.shops[key]['freight'];
		    }
		    $('#maxScoreSpan').html(json.maxScore);
		    $('#maxScoreMoneySpan').html(json.maxScoreMoney);
		    $('#isUseScore').attr('dataval',json.maxScore);
		    $('#deliverMoney').html(shopFreight);
		    $('#useScore').val(json.useScore);
		    $('#orderScore').html(json.orderScore);
		    $('#scoreMoney2').html(json.scoreMoney);
		 	$('#totalMoney').html(json.realTotalMoney);
		}
	});
}

var currStoreShopId = 0;
var currStoreId = 0;

function checkSupportStores(){
	$.post(WST.U('home/carts/checkSupportStores'),{},function(data,textStatus){
		var json = WST.toJson(data);
	    if(json.status==1){
	    	if(json.data){
	    		for(var i in json.data){
	    			if(json.data[i]==1){
	    				$("#deliver_btn_"+i).show();
	    			}else{
	    				$("#deliver_btn_"+i).hide();
	    			}
	    		}
	    	}else{
	    		$("div[id^='deliver_btn_']").hide();
	    	}
	    }else{
	    	WST.msg(json.msg,{icon:2});
	    }
	});
}
function changeDeliverType(n,shopId,index,obj){
	currStoreShopId = shopId;
	if(n==1){
		checkStores(shopId);
	}else{
		$('#'+index+'_'+shopId).val(n);
		$(obj).addClass('j-selected').siblings('.wst-frame2').removeClass('j-selected');
		$("#deliver_info_"+shopId).hide();
		getCartMoney();
	}

}

function lastAreaCallback(opts){
	if(opts.isLast && opts.val){
	    getStores(opts.val);
	}
}

function getStores(areaId){

	$.post(WST.U('home/carts/getStores'),{shopId:currStoreShopId,areaId:areaId},function(data,textStatus){
		var json = WST.toJson(data);
	    if(json.status==1){
	    	if(json.data && json.data.length>0){
	    		var gettpl = document.getElementById('tblist').innerHTML;
		       	laytpl(gettpl).render(json.data, function(html){
		       		$("#storelist").html(html);
		       	});
	    	}else{
	    		$("#storelist").html("<div>"+WST.lang('curr_area_no_pick_up_tips')+"</div>");
	    	}
	    }else{
	    	WST.msg(json.msg,{icon:2});
	    }
	});
}

function checkStores(shopId){
	currStoreId = $("#storeId_"+shopId).val();
	$('select[id^="storearea_0_"]').remove();
	var areaIdPath = $("#store_areaIdPath_"+shopId).val();
	    areaIdPath = areaIdPath.split("_");
	$('#storearea_0').val(areaIdPath[0]);

 	var aopts = {id:'storearea_0',val:areaIdPath[0],childIds:areaIdPath,className:'j-storeareas',afterFunc:'lastAreaCallback'}
	WST.ITSetAreas(aopts);

	getStores($("#store_areaId_"+shopId).val());
	layerbox =	layer.open({
					title:WST.lang('select_pick_up'),
					type: 1,
					area: ['800px', '600px'],
					content: $('.j-store-box'),
					btn:[WST.lang('confirm'),WST.lang('cancel')],
				    cancel:function(){
				    	//$('#menuBox').hide();
				    },
				    yes:function(){
				    	var areaIds = WST.ITGetAllAreaVals('storearea_0','j-storeareas');
				    	console.log(areaIds);
				    	var storeId = $("input[name='storeId']:checked").val();

						if(!(storeId>0)){
							WST.msg(WST.lang('select_pick_up'));
							return;
						}

				    	$("#storeId_"+shopId).val(storeId);
				    	$("#deliverType_"+shopId).val(1);
				    	$("#store_areaId_"+shopId).val(areaIds[2]);
						$("#store_areaIdPath_"+shopId).val(areaIds.join("_"));


					 	$("#deliver_btn_"+shopId).addClass('j-selected').siblings('.wst-frame2').removeClass('j-selected');
					 	var deliverInfo = $('#storeName_'+storeId).html() +"&nbsp;&nbsp;"+ $('#storeAddress_'+storeId).html();
					 	$("#deliver_info_"+shopId).html(deliverInfo).show();
					 	getCartMoney();
						layer.close(layerbox);
				  	}
				});
}


function submitOrder(){
	var params = WST.getParams('.j-ipt');
	params.isUseScore = $('#isUseScore').prop('checked')?1:0
	var load = WST.load({msg:WST.lang('submiting_tips')});
	$.post(WST.U('home/orders/submit'),params,function(data,textStatus){
		layer.close(load);
		var json = WST.toJson(data);
	    if(json.status==1){
	    	 WST.msg(json.msg,{icon:1},function(){
	    		 location.href=WST.U('home/orders/succeed',{'pkey':json.pkey});
	    	 });
	    }else{
	    	WST.msg(json.msg,{icon:2});
	    }
	});
}


var invoicebox;
var currShopId = 0;
function changeInvoice(t,str,shopId,obj){
	currShopId = shopId;
	var param = {};
	param.isInvoice = $('#isInvoice_'+shopId).val();
	param.invoiceId = $('#invoiceId_'+shopId).val();
	var loading = WST.load({msg:WST.lang('loading_tips')});
	$.post(WST.U('home/invoices/index'),param,function(data){
		layer.close(loading);
		// layer弹出层
		invoicebox =	layer.open({
			title:WST.lang('invoice_information'),
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


function changeInvoiceItem(t,obj){
	$(obj).addClass('inv_li_curr').siblings().removeClass('inv_li_curr');
	$('.inv_editing').remove();// 删除正在编辑中的发票信息
	$('.inv_add').show();
	$('#invoiceId_'+currShopId).val(t);
	if(t==0){
		// 为个人时，隐藏识别号
		$('.inv_codebox').css({display:'none'});
		$('#invoice_num').val(' ');
	}else{
		$('#invoice_num').val($('#invoiceCode_'+t).val());
		$('.inv_codebox').css({display:'block'});
	}
	$("#invoice_obj_"+currShopId).val(t);
}
// 是否需要开发票
function changeInvoiceItem1(t,obj){
	$(obj).addClass('inv_li_curr').siblings().removeClass('inv_li_curr');
	$('#isInvoice_'+currShopId).val(t);
}
// 显示发票增加
function invAdd(){
	$("#invoiceId_"+currShopId).val(0);
	$("#invoice_obj_"+currShopId).val(1);
	$('#invoice_num').val('');
	$('.inv_li').removeClass('inv_li_curr');// 移除当前选中样式
	$('.inv_ul').append('<li class="inv_li inv_li_curr inv_editing"><input type="text" id="invoiceHead" placeholder="'+WST.lang('add_invoice_head')+'" value="" style="width:65%;height:21px;padding:1px;"><i></i><div style="top:8px;" class="inv_opabox"><a href="javascript:void(0)" onCLick="addInvoice()">'+WST.lang('save')+'</a></div></li>');
	$('.inv_ul').scrollTop($('.inv_ul')[0].scrollHeight);// 滚动到底部
	$('.inv_add').hide();// 隐藏新增按钮
	$('.inv_codebox').css({display:'block'});// 显示`纳税人识别号`
}
// 执行发票抬头新增
function addInvoice(){
	var head = $('#invoiceHead').val();
	if(head.length==0){
		WST.msg(WST.lang('invoice_head_required'));
		return;
	}
	var loading = WST.load({msg:WST.lang('submiting_tips')});
	$.post(WST.U('home/Invoices/add'),{invoiceHead:head},function(data){
		var json = WST.toJson(data);
		layer.close(loading);
		if(json.status==1){
			$('#invoiceId_'+currShopId).val(json.data.id);
			WST.msg(json.msg,{icon:1});
			$('.inv_editing').remove();
			var code = [];
			code.push('<li class=\'inv_li inv_li_curr\' onClick="changeInvoiceItem(\''+json.data.id+'\',this)">');
     		code.push('<input type="text" value="'+head+'" readonly="readonly" class="invoice_input" id="invoiceHead_'+json.data.id+'" />');
			code.push('<input type="hidden" id="invoiceCode_'+json.data.id+'" value=""} /><i></i>');
			code.push('<div class="inv_opabox">');
			code.push('<a href=\'javascript:void(0)\' onClick="invEdit(\''+json.data.id+'\',this)" class="edit_btn">'+WST.lang('edit')+'</a>');
			code.push('<a href=\'javascript:void(0)\' onClick="editInvoice(\''+json.data.id+'\',this)" style="display:none;" class="save_btn">'+WST.lang('save')+'</a>');
			code.push('<a href=\'javascript:void(0)\' onClick="delInvoice(\''+json.data.id+'\',this)">'+WST.lang('del')+'</a></div></li>');
			$('.inv_li:first').after(code.join(''));
			// 显示新增按钮
			$('.inv_add').show();
		}else{
			WST.msg(json.msg,{icon:2});
		}
	});
}
// 显示发票修改
function invEdit(id,obj){
	var input = $(obj).parent().parent().find('.invoice_input');
	input.removeAttr('readonly').focus();
	input.mouseup(function(){return false});
	$(obj).parent().parent().mouseup(function(){
		input.attr('readonly','readonly');
		$(obj).show().siblings('.save_btn').hide();
	});
	$(obj).hide().siblings('.save_btn').show();
	var invoice_code = $('#invoiceCode_'+id).val();
	$('.inv_codebox').css({display:'block'})
	$('#invoice_num').val(invoice_code);// 显示`纳税人识别号`)
}
// 完成发票修改
function editInvoice(id,obj){
	var head = $('#invoiceHead_'+id).val();
	if(head.length==0){
		WST.msg(WST.lang('invoice_head_required'));
		return;
	}
	var loading = WST.load({msg:WST.lang('submiting_tips')});
	$.post(WST.U('home/Invoices/edit'),{invoiceHead:head,id:id},function(data){
		var json = WST.toJson(data);
		layer.close(loading);
		if(json.status==1){
			var input = $(obj).parent().parent().find('.invoice_input');
			input.attr('readonly','readonly')
			$(obj).hide().siblings('.edit_btn').show();
			WST.msg(json.msg,{icon:1});
		}else{
			WST.msg(json.msg,{icon:2});
		}
	});
}

// 设置页面显示值
function setInvoiceText(invoiceHead){
	var isInvoice  = $('#isInvoice_'+currShopId).val();
	var invoiceObj = $('#invoice_obj_'+currShopId).val();// 发票对象
	var text = WST.lang('no_invoice');
	if(isInvoice==1){
		text = (invoiceObj==0)?WST.lang('invoice_personal_detail'):WST.lang('invoice_head_detail',[invoiceHead]);
	}
	$('#invoice_info_'+currShopId).html(text);
	layerclose();
}

// 保存纳税人识别号
function saveInvoice(){
	var isInv = $('#isInvoice_'+currShopId).val();
	var num = $('#invoice_num').val();
	var id = $('#invoiceId_'+currShopId).val();
	var invoiceHead = $('#invoiceHead').val();// 发票抬头
	var url = WST.U('home/Invoices/add');
	var params = {};
	if(id>0){
		url = WST.U('home/Invoices/edit');
		invoiceHead = $('#invoiceHead_'+id).val();// 发票抬头
		params.id = id;
	}
	params.invoiceHead = invoiceHead;
	params.invoiceCode = num;
	if($('#invoice_obj_'+currShopId).val()!=0){
		var loading = WST.load({msg:WST.lang('submiting_tips')});
		$.post(url,params,function(data){
			var json = WST.toJson(data);
			layer.close(loading);
			if(json.status==1){
				// 判断用户是否需要发票
				setInvoiceText(invoiceHead);
				if(id==0)$('#invoiceId_'+currShopId).val(json.data.id)
			}else{
				WST.msg(json.msg,{icon:2});
			}
		});
	}else{
		setInvoiceText('');
	}
}
// 删除发票信息
function delInvoice(id,obj){
	WST.confirm({content:WST.lang('confirm_del_inquiry'),yes:function(index){
		$.post(WST.U('home/invoices/del'),{id:id},function(data,textStatus){
		     var json = WST.toJson(data);
		     if(json.status==1){
		    	 WST.msg(json.msg,{icon:1});
		    	 $(obj).parent().parent().remove();
		    	 $('#invoiceId_'+currShopId).val(0);
		    	 // 选中 `个人`
		    	 $('.inv_li:first').click();
		     }else{
		    	 WST.msg(json.msg,{icon:2});
		     }
		});
	}});
}





function changeSelected(n,index,obj){
	$('#'+index).val(n);
	inEffect(obj,2);
}

function getPayUrl(){
	var params = {};
		params.payObj = "orderPay";
		params.pkey = $("#pkey").val();
		params.payCode = $.trim($("#payCode").val());
	if(params.payCode==""){
		WST.msg(WST.lang('please_select_pay_type'), {icon: 5});
		return;
	}

	jQuery.post(WST.U('home/'+params.payCode+'/get'+params.payCode+"Url"),params,function(data) {
		var json = WST.toJson(data);
		if(json.status==1){
			if(params.payCode=="weixinpays" || params.payCode=="ccgwpays" || params.payCode=="wallets"){
				location.href = json.url;
			}else{
				if(params.payCode=="unionpays"){
					location.href = WST.U('home/unionpays/tounionpays',params);
				}else{
					$("#alipayform").html(json.result);
				}
			}
		}else{
			WST.msg(json.msg?json.msg:WST.lang('transfer_payment_failed'), {icon: 5,time:2500},function(){
				window.location = WST.U('home/orders/waitReceive');
			});
		}
	});
}

function payByWallet(){
    var params = WST.getParams('.j-ipt');
	if(params.payPwd==""){
		WST.msg(WST.lang('password_require'), {icon: 5});
		return;
	}
    if(window.conf.IS_CRYPT=='1'){
        var public_key=$('#token').val();
        var exponent="10001";
   	    var rsa = new RSAKey();
        rsa.setPublic(public_key, exponent);
        params.payPwd = rsa.encrypt(params.payPwd);
    }
	var load = WST.load({msg:WST.lang('checking_pay_password_tips')});
	$.post(WST.U('home/wallets/payByWallet'),params,function(data,textStatus){
		layer.close(load);
		var json = WST.toJson(data);
	    if(json.status==1){
	    	WST.msg(json.msg, {icon: 1,time:1500},function(){
                window.location = WST.U('home/orders/waitReceive');
	    	});
	    }else{
	    	WST.msg(json.msg,{icon:2,time:1500});
	    }
	});
}

function checkScoreBox(v){
    if(v){
    	var val = $('#isUseScore').attr('dataval');
    	$('#useScore').val(val);
        $('#scoreMoney').show();

    }else{
    	$('#scoreMoney').hide();
    }
    getCartMoney();
}

function setPaypwd(){
	layerbox =	layer.open({
		title:[WST.lang('set_pay_password'),'text-align:left'],
		type: 1,
		area: ['450px', '240px'],
		content: $('.j-paypwd-box'),
		btn: [WST.lang('set_pay_password_and_pay_order'), WST.lang('close')],
		yes: function(index, layero){
			var newPass = $.trim($("#payPwd").val());
			var reNewPass = $.trim($("#reNewPass").val());
			if(newPass==""){
				WST.msg(WST.lang('please_input_paypwd'));
				return false;
			}
			if(reNewPass==""){
				WST.msg(WST.lang('please_input_confirm_paypwd'));
				return false;
			}
			if(newPass!=reNewPass){
				WST.msg(WST.lang('password_not_same'));
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
			var load = WST.load({msg:WST.lang('submitting_paypwd')});
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
// 移入我的关注
function moveToFavorites(goodsIds,cartIds){
    WST.confirm({content:WST.lang('moving_not_show_cart'),yes:function(index){
        $.post(WST.U('home/carts/moveToFavorites'),{goodsIds:goodsIds,cartIds:cartIds},function(data,textStatus){
            var json = WST.toJson(data);
            if(json.status==1){
                WST.msg(json.msg,{icon:1});
                location.href=WST.U('home/carts/index');
            }else{
                WST.msg(json.msg,{icon:2});
            }
        });
    }});
}
