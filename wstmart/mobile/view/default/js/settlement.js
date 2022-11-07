jQuery.noConflict();
var currShopId = 0;
var currStoreShopId = 0;
var currStoreId = 0;
function onSwitch(obj,n){
	$(obj).children('.ui-icon-push').removeClass('ui-icon-unchecked-s').addClass('ui-icon-checked-s wst-active');
	$(obj).siblings().children('.ui-icon-push').removeClass('ui-icon-checked-s wst-active').addClass('ui-icon-unchecked-s');
}
/* 选择是否需要发票 */
function isInvoice(obj,n){
	$(obj).children('.ui-icon-push').removeClass('ui-icon-unchecked-s').addClass('ui-icon-checked-s wst-active');
	$(obj).siblings().children('.ui-icon-push').removeClass('ui-icon-checked-s wst-active').addClass('ui-icon-unchecked-s');
	$('#isInvoice').val(n);
	$('#isInvoice_'+currShopId).val(n);// 记录用户是否需要开发票
	$('#invoicesh').val(n);
}
/* 发票对象【个人or单位】 */
function invOnSwitch(obj,n){
	$(obj).children('.ui-icon-push').removeClass('ui-icon-unchecked-s').addClass('ui-icon-checked-s wst-active');
	$(obj).siblings().children('.ui-icon-push').removeClass('ui-icon-checked-s wst-active').addClass('ui-icon-unchecked-s');
	if(n==1){
		$('.inv_hidebox').show();
	}else{
		$('.inv_hidebox').hide();
	}
	$('#invoice_obj').val(n);// 记录用户所开发票对象
}
/* 发票抬头列表绑定事件 */
$(function(){
	$('#special_invoice_head').focus(function(){
		$('#special_inv_headlist').show();
	})
	$('#special_invoice_head').blur(function(){
		setTimeout(function(){
			$('#special_inv_headlist').hide();
		},100)
	})

	$('#invoice_head').focus(function(){
		$('#inv_headlist').show();
	})
	$('#invoice_head').blur(function(){
		setTimeout(function(){
			$('#inv_headlist').hide();
		},100)
	})
	// 只要用户编辑了,就视为新增
	$('#invoice_head').bind('input propertychange', function() {
	    $('#invoiceId').val(0);
	});
})
/* 完成发票信息填写 */
function saveInvoice(){
	var param={};
	var invoiceId = $('#invoiceId_'+currShopId).val();// 发票id
	param.invoiceCode = $('#invoice_code').val();// 纳税人识别码
	param.invoiceHead = $('#invoice_head').val();// 发票抬头
	var url = 'mobile/invoices/add';
	if(invoiceId>0){
		url = 'mobile/invoices/edit';
		param.id = invoiceId;
	}
	if($('#invoice_obj').val()!=0){
		$.post(WST.U(url),param,function(data){
			var json = WST.toJson(data);
			if(json.status==1){
				setInvoiceText();
				if(invoiceId==0)$('#invoiceId_'+currShopId).val(json.data.id)
			}else{
				WST.msg(json.msg,'info');
			}
		})
	}else{
		setInvoiceText();
	}

}
// 设置页面显示值
function setInvoiceText(){
	console.log("shop_",currShopId);
	var isInvoice  = $('#isInvoice_'+currShopId).val();
	var invoiceObj = $('#invoice_obj').val();// 发票对象
	var invoiceHead = $('#invoice_head').val();// 发票抬头
	var invoiceType = $('#invoiceType').val();// 发票类型
	var text = WST.lang('no_invoice');
	if(isInvoice==1){
		if(invoiceType==1){
			text = (invoiceObj==0)?(WST.lang('invoice_special_personal_detail')):(WST.lang('invoice_special_detail',['<br />'+invoiceHead+'<br />']));
		}else{
			text = (invoiceObj==0)?(WST.lang('invoice_normal_personal_detail')):(WST.lang('invoice_personal_detail',['<br />'+invoiceHead+'<br />']));
		}
	}
	if((invoiceObj==0)){
		$("#invoiceClient_"+currShopId).val(WST.lang('personal'));
	}else{
		$("#invoiceClient_"+currShopId).val(invoiceHead);
	}
	$('#invoicest_'+currShopId).html(text);
	invoiceHide();
}
function inDetermine(n){
	$('#'+n+' .wst-active').each(function(){
		type = $(this).attr('mode');
		word = $(this).attr('word');
		if(n=='payments')payCode = $(this).attr('payCode');
	});
	if(n=='gives'){
		$('#deliverType_'+currStoreShopId).val(type);

		if(type==1){
			$('#storeId_'+currStoreShopId).val(currStoreId);
			word = $("#storbox .infot").html();
		}else{
			$('#storeId_'+currStoreShopId).val(0);
		}
		$('#'+n+"_"+currStoreShopId+'t').html(word);
	}else{
		$('#'+n+'h').val(type);
		$('#'+n+'t').html(word);
	}
	if(n=='payments'){
		$('#'+n+'w').val(payCode);
	}
	getCartMoney();
	dataHide(n);
}

//计算价格
function getCartMoney(){
	var params = WST.getParams('.j-deliver');
	params.isUseScore = $('#scoreh').val();
	params.useScore = $('#userOrderScore').html();
	params.areaId2 = $('#areaId').val();
	params.deliverType = $('#givesh').val();
	params.sign = $('#sign').val();

	params.couponIds = [];
	$('input[id^="couponId_"]').each(function(){
		var shopId = $(this).attr('id').split('_')[1];
		params.couponIds.push(shopId+':'+$(this).val());
	})
	params.couponIds = params.couponIds.join(',');

	WST.load(WST.lang('calculating_price_tips'));
	if(params.sign==1){
		$.post(WST.U('mobile/carts/getCartMoney'),params,function(data,textStatus){
			WST.noload();
			var json = WST.toJson(data);
			if(json.status==1){
			    json = json.data;
			    for(var key in json.shops){
			    	// 设置每间店铺的运费及总价格
			    	$('#shopF_'+key).html(WST.lang('currency_symbol')+json.shops[key]['freight'].toFixed(2));
			    	$('#shopC_'+key).html(WST.lang('currency_symbol')+json.shops[key]['goodsMoney'].toFixed(2));
			    	$('#shopOS_'+key).html(json.shops[key]['orderScore']);
			    }
			 	$('#totalMoney').html(WST.lang('currency_symbol')+json.realTotalMoney.toFixed(2));
			 	$('#totalPrice').val(json.realTotalMoney);
			 	$('#orderScore').html(json.orderScore);//隐藏功能，不一定会有显示
			 	// 设置可用积分及积分可抵金额
			 	$('#userOrderScore').html(json.maxScore);
				$('#userOrderMoney').html(json.maxScoreMoney);
			}
		});
	}else if(params.sign==2){//虚拟商品
		params.deliverType = 1;
		$.post(WST.U('mobile/carts/getQuickCartMoney'),params,function(data,textStatus){
			WST.noload();
			var json = WST.toJson(data);
			if(json.status==1){
			    json = json.data;
			    for(var key in json.shops){
			    	// 设置每间店铺的运费及总价格
			    	$('#shopC_'+key).html(WST.lang('currency_symbol')+json.shops[key]['goodsMoney'].toFixed(2));
			    	$('#shopOS_'+key).html(json.shops[key]['orderScore']);
			    }
			 	$('#totalMoney').html(WST.lang('currency_symbol')+json.realTotalMoney.toFixed(2));
			 	$('#totalPrice').val(json.realTotalMoney);
			 	$('#orderScore').html(json.orderScore);//隐藏功能，不一定会有显示
			 	// 设置可用积分及积分可抵金额
			 	$('#userOrderScore').html(json.maxScore);
				$('#userOrderMoney').html(json.maxScoreMoney);
			}
		});
	}
}
//提交订单
function submitOrder(){
	var addressId =  $('#addressId').val();
	if($('#givesh').val()==0 && addressId==''){
		WST.msg(WST.lang('please_select_address'),'info');
		return false;
	}
	WST.load(WST.lang('submiting_tips'));
    var param = WST.getParams('.j-ipt');
    param.s_addressId = addressId;
    param.s_areaId = $('#areaId').val();
    param.payType = $('#paymentsh').val();
    param.payCode = $('#paymentsw').val();
    param.isUseScore = $('#scoreh').val();
    param.useScore = $('#userOrderScore').html();

	$('.wst-se-sh .shopn').each(function(){
		shopId = $(this).attr('shopId');
	    param['remark_'+shopId] = $('#remark_'+shopId).val();
	    param['couponId_'+shopId] = $('#couponId_'+shopId).val();
	});
    $('.wst-se-confirm .button').attr('disabled', 'disabled');
	$.post(WST.U('mobile/orders/submit'),param,function(data,textStatus){
		var json = WST.toJson(data);
	    WST.noload();
	    if(json.status==1){
	    	WST.msg(json.msg,'success');
		      setTimeout(function(){
		    	  if(param.payType==1 && $('#totalPrice').val()>0){
		    		  if(param.payCode=='alipays' || param.payCode==''){
			    		  location.href = WST.U('mobile/alipays/toAliPay',{"pkey":json.pkey});
		    		  }else if(param.payCode=='wallets'){
		    			  location.href = WST.U('mobile/wallets/payment',{"pkey":json.pkey});
		    		  }else if(param.payCode=='paypals'){
		    			  location.href = WST.U('mobile/paypals/toPaypal',{"pkey":json.pkey});
		    		  }else if(param.payCode=='weixinpays'){
		    			  location.href = WST.U('mobile/weixinpays/toWeixinPay',{"pkey":json.pkey});
		    		  }else if(param.payCode=='ccgwpays'){
		    			  location.href = WST.U('mobile/ccgwpays/toCcgwPay',{"pkey":json.pkey});
		    		  }
		    	  }else{
		    		  location.href = WST.U('mobile/orders/index');
		    	  }
		      },1000);
	    }else{
	    	WST.msg(json.msg,'info');
	    	$('.wst-se-confirm .button').removeAttr('disabled');
	    }
	});
}
//提交虚拟商品订单
function quickSubmitOrder(){
	WST.load(WST.lang('submiting_tips'));
    var param = WST.getParams('.j-ipt');
    param.payType = $('#paymentsh').val();
    param.payCode = $('#paymentsw').val();
    param.isUseScore = $('#scoreh').val();
    param.useScore = $('#userOrderScore').html();
	$('.wst-se-sh .shopn').each(function(){
		shopId = $(this).attr('shopId');
	    param['remark_'+shopId] = $('#remark_'+shopId).val();
	    param['couponId_'+shopId] = $('#couponId_'+shopId).val();
	});
    $('.wst-se-confirm .button').attr('disabled', 'disabled');
	$.post(WST.U('mobile/orders/quickSubmit'),param,function(data,textStatus){
		var json = WST.toJson(data);
	    WST.noload();
	    if(json.status==1){
	    	WST.msg(json.msg,'success');
		      setTimeout(function(){
		    	  if(param.payType==1 && $('#totalPrice').val()>0){
		    		  if(param.payCode=='alipays' || param.payCode==''){
			    		  location.href = WST.U('mobile/alipays/toAliPay',{"pkey":json.pkey});
		    		  }else if(param.payCode=='wallets'){
		    			  location.href = WST.U('mobile/wallets/payment',{"pkey":json.pkey});
		    		  }else if(param.payCode=='unionpays'){
		    			  location.href = WST.U('mobile/unionpays/toUnionpay',{"pkey":json.pkey});
		    		  }else if(param.payCode=='weixinpays'){
		    			  location.href = WST.U('mobile/weixinpays/toWeixinPay',{"pkey":json.pkey});
		    		  }else if(param.payCode=='ccgwpays'){
		    			  location.href = WST.U('mobile/ccgwpays/toCcgwPay',{"pkey":json.pkey});
		    		  }
		    	  }else{
		    		  location.href = WST.U('mobile/orders/index');
		    	  }
		      },1000);
	    }else{
	    	WST.msg(json.msg,'info');
	    	$('.wst-se-confirm .button').removeAttr('disabled');
	    }
	});
}
function addAddress(type,id){
	location.href = WST.U('mobile/useraddress/index','type='+type+'&addressId='+id);
}
var dataHeight = $(".frame").css('height');
	dataHeight = parseInt(dataHeight)+50+'px';
$(document).ready(function(){
	WST.imgAdapt('j-imgAdapt');
    $(".frame").css('bottom','-'+dataHeight);

    backPrevPage(WST.U('mobile/carts/index'));
	// 地址弹出层
	var h = WST.pageHeight();
	$("#areaFrame").css('bottom','-'+h/2);
	var listh = h/2-106;
	$(".wst-fr-box3 .list").css('overflow-y','scroll').css('height',listh+'px');
	$('#addresst').html($('#areaName').val());
});
//弹框
function dataShow(n,obj){
	jQuery('#cover').attr("onclick","javascript:dataHide('"+n+"');").show();
	jQuery('#'+n).animate({"bottom": 0}, 500);
	//显示已保存的数据
	var type = $('#'+n+'h').val();
	if(n=='gives'){
		currStoreShopId = $(obj).data("shopid");
		type = $('#deliverType_'+currStoreShopId).val();
		getStores(0);
	}
	if(type==0){
		jQuery('i[class*="'+n+'"]').removeClass('ui-icon-checked-s wst-active').addClass('ui-icon-unchecked-s');
		jQuery('.'+n+'0').removeClass('ui-icon-unchecked-s').addClass('ui-icon-checked-s wst-active');
	}else{
		jQuery('i[class*="'+n+'"]').removeClass('ui-icon-checked-s wst-active').addClass('ui-icon-unchecked-s');
		jQuery('.'+n+'1').removeClass('ui-icon-unchecked-s').addClass('ui-icon-checked-s wst-active');
	}
	if(n=='payments'){
		var payCode = $('#'+n+'w').val();
		jQuery('i[class*="'+n+'"]').removeClass('ui-icon-checked-s wst-active').addClass('ui-icon-unchecked-s');
		jQuery('.'+n+'_'+payCode).removeClass('ui-icon-unchecked-s').addClass('ui-icon-checked-s wst-active');
	}else if(n=='invoices'){
		if(type==0){
			jQuery('#j-invoice').hide();
		}else{
			jQuery('#j-invoice').show();
		}
	}
}
function dataHide(n){
	jQuery('#'+n).animate({'bottom': '-'+dataHeight}, 500);
	jQuery('#cover').hide();
}
document.addEventListener('touchmove', function(event) {
    //阻止背景页面滚动,
    if(!jQuery("#cover").is(":hidden")){
        event.preventDefault();
    }
})


/*********************** 发票信息层 ****************************/

function invTypeSwitch(obj, n){
	// 将选中的值置为空
	if(n!=$('#invoiceType_'+currShopId).val()){
		// 将选中的值置为空
		$('.inv_head_input').val('');
		$('#invoice_code').val('');
	}
	$('#invoice_obj').val(n);
	$('#invoiceType_'+currShopId).val(n);
	$(obj).children('.ui-icon-push').removeClass('ui-icon-unchecked-s').addClass('ui-icon-checked-s wst-active');
	$(obj).siblings().children('.ui-icon-push').removeClass('ui-icon-checked-s wst-active').addClass('ui-icon-unchecked-s');
	if(n==0){
		$('#j-special-box').hide();
		$('#j-normal-box').show();
		$('#inv_personal').click();
		// 普通发票
		$('.j-normal').show();
		$('.j-special').hide();
	}else{
		$('#j-special-box').show();
		$('#j-normal-box').hide();
		// 专用发票
		$('.j-normal').hide();
		$('.j-special').show();
	}
}
//弹框
function invoiceShow(){
	var _type = $('#invoiceType_'+currShopId).val();
	if(_type==0){
		// 普票
		if($('#invoice_obj').val()==0){
			$('#j-normal-btn').click();
		}else{
			// 普通发票
			$('.j-normal').show();
			$('.j-special').hide();
		}
	}else{
		// 专票
		$('#j-special-btn').click();
	}
    jQuery('#cover').attr("onclick","javascript:invoiceHide();").show();
    jQuery('#frame').animate({"bottom": 0}, 500);

}
function invoiceHide(){
    jQuery('#frame').animate({'bottom': '-100%'}, 500);
    jQuery('#cover').hide();
}


function getInvoiceList(shopId){
	currShopId = shopId;
	$('#isInvoice_'+shopId).val($("#isInvoice").val());
  	$.post(WST.U('mobile/invoices/pageQuery'),{},function(data){
      var json = WST.toJson(data);
      if(json.status!=-1){
        var gettpl1 = document.getElementById('invoiceBox').innerHTML;
          laytpl(gettpl1).render(json, function(html){
            	$('.inv_list_item').html(html);
			  	invoiceShow();
            	// 点击抬头item
				$('.inv_list_item li').click(function(){
					// 设置值
					$('.inv_head_input').val($(this).html());
					$('#invoice_head').val($(this).html());
					$('#invoiceId_'+shopId).val($(this).attr('invId'));
					$('#invoice_code').val($(this).attr('invCode'));
				})
          });
      }else{
        WST.msg(json.msg,'info');
      }
  });
}



//弹框
function dataShow2(title){
	getStores();
    jQuery('#store').animate({"right": 0}, 500);
}
function dataHide2(){
    jQuery('#store').animate({'right': '-100%'}, 500);
}

function onSwitch2(obj,val){
	$(obj).children('.ui-icon-push').removeClass('ui-icon-unchecked-s').addClass('ui-icon-checked-s wst-active');
	$(obj).siblings().children('.ui-icon-push').removeClass('ui-icon-checked-s wst-active').addClass('ui-icon-unchecked-s');
	if(val==1){
		$("#storbox").show();
	}else{
		$("#storbox").hide();
	}
}

function getStores(val,name){
	var addressId = $('#addressId').val();
	var areaId = (val!=undefined)?val:$('#storeAreaId').val();
    var areaName = (name!=undefined)?name:(($('#areaName').val()!='')?$('#areaName').val():WST.lang('require_select_area'));
	$.post(WST.U('mobile/carts/getStores'),{shopId:currStoreShopId,addressId:addressId,areaId:areaId},function(data,textStatus){
		var json = WST.toJson(data);
	    if(json.status==1){
	    	if(json.data && json.data.length>0){
	    		$("#deliver1").show();
	    		var storeId = $("#storeId_"+currStoreShopId).val();
	    		if(areaId>0){
					$('#storeAreaId').val(areaId);
				}
				$('#addresst').html(areaName);
				$('#areaName').val(areaName);
	    		if(storeId<=0){
	    			currStoreId = json.data[0].storeId;
	    			var gettpl = document.getElementById('tblist').innerHTML;
			       	laytpl(gettpl).render(json.data[0], function(html){
			       		$("#storbox").html(html);
			       	});
	    		}else{
	    			for(var i in json.data){
	    				if(storeId == json.data[i].storeId){
	    					var gettpl = document.getElementById('tblist').innerHTML;
					       	laytpl(gettpl).render(json.data[i], function(html){
					       		$("#storbox").html(html);
					       	});
	    				}
	    			}
	    		}

	    		if($("#deliverType_"+currStoreShopId).val()==1){
	    			$("#storbox").show();
	    		}

	    		var gettpl = document.getElementById('tblist2').innerHTML;
		       	laytpl(gettpl).render(json.data, function(html){
		       		$("#storelist").html(html);
		       	});
	    	}else{
	    		$("#storelist").html("<div class='no-store'>"+WST.lang('curr_area_no_pick_up_tips')+"</div>");
	    	}
	    }else{
	    	WST.msg(json.msg,{icon:2});
	    }
	});
}

function checkStore(obj,storeId){
	currStoreId = storeId;
	$(".store-item").removeClass("currchk");
	$(obj).addClass("currchk");
	$("#storbox").html('<li onclick="dataShow2()" class="li-store" style="margin: 0">'+$("#store-info"+storeId).html()+'</li>');
	dataHide2();
}
// 选择自提点所在地区
function dataShow3(){
	jQuery('#areaFrame').show();
	jQuery('#cover2').attr("onclick","javascript:dataHide3();").show();
	jQuery('#areaFrame').animate({"bottom": 0}, 500);
}
function dataHide3(){
	var dataHeight = $("#areaFrame").css('height');
	jQuery('#areaFrame').animate({'bottom': '-'+dataHeight}, 500);
	jQuery('#cover2').hide();
	setTimeout(function(){
		jQuery('#areaFrame').hide();
	},500);
}
//地址选择
function inOption(obj,n){
	$(obj).addClass('active').siblings().removeClass('active');
	$('.area_'+n).removeClass('hide').siblings('.list').addClass('hide');
	var level = $('#level').val();
	var n = n+1;
	for(var i=n; i<=level; i++){
		$('.area_'+i).remove();
		$('.active_'+i).remove();
	}
}
function inChoice(obj,id,val,level){
	$('#level').val((level+1));
	$(obj).addClass('active').siblings().removeClass('active');
	$('#'+id).attr('areaId',val);
	$('.active_'+level).removeClass('active').html($(obj).html());
	WST.ITAreas({id:id,val:val,className:'j-areas'});
}
/**
 * 循环创建地区
 * @param id            当前分类ID
 * @param val           当前分类值
 * @param className     样式，方便将来获取值
 */
WST.ITAreas = function(opts){
	opts.className = opts.className?opts.className:"j-areas";
	var obj = $('#'+opts.id);
	obj.attr('lastarea',1);
	$.post(WST.U('mobile/areas/listQuery'),{parentId:opts.val},function(data,textStatus){
		var json = WST.toJson(data);
		if(json.data && json.data.length>0){
			json = json.data;
			var html = [],tmp;
			var tid = opts.id+"_"+opts.val;
			var level = parseInt(obj.attr('level'),10);
			$('.area_'+level).addClass('hide');
			var level = level+1;
			html.push('<div id="'+tid+'" class="list '+opts.className+' area_'+level+'" areaId="0" level="'+level+'">');
			for(var i=0;i<json.length;i++){
				tmp = json[i];
				html.push("<p onclick='javascript:inChoice(this,\""+tid+"\","+tmp.areaId+","+level+");'>"+tmp.areaName+"</p>");
			}
			html.push('</div>');
			$(html.join('')).insertAfter('#'+opts.id);
			var h = WST.pageHeight();
			var listh = h/2-106;
			$(".wst-fr-box3 .list").css('overflow-y','scroll').css('height',listh+'px');
			$(".wst-fr-box3 .option").append('<p class="ui-nowrap-flex term active_'+level+' active" onclick="javascript:inOption(this,'+level+')">请选择</p>');
		}else{
			opts.isLast = true;
			opts.lastVal = opts.val;
			var ht = '';
			$('.wst-fr-box3 .term').each(function(){
				ht += $(this).html();
			});
            $('#addresst').html(ht);
			getStores(opts.val,ht);
			$('#storbox').show();
			dataHide3();
		}
	});
}
