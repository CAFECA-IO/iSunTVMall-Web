// 列表
function queryByPage(p){
	$('#loading').show();
	var params = {};
	params = WST.getParams('.s-ipt');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(WST.U('supplier/orderservices/pageQuery'),params,function(data,textStatus){
		$('#loading').hide();
	    var json = WST.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1){
	    	json = json.data;
            if(params.page>json.data.last_page && json.data.last_page >0){
                queryByPage(json.data.last_page);
                return;
            }
	       	var gettpl = document.getElementById('tblist').innerHTML;
	       	laytpl(gettpl).render(json.data, function(html){
	       		$(html).insertAfter('#loadingBdy');
         		$('.gImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.RESOURCE_PATH+'/'+WST.conf.GOODS_LOGO});
	       	});
       		laypage({
	        	 cont: 'pager',
	        	 pages:json.last_page,
	        	 curr: json.current_page,
	        	 skin: '#1890ff',
	        	 groups: 3,
	        	 jump: function(e, first){
	        		 if(!first){
	        			 queryByPage(e.curr);
	        		 }
	        	 }
	        });
       	}
	});
}

// 处理退换货
function isArgee(val){
    $('#isSupplierAgree').val(val);
    if(val==1){
        $('#j-agree-box').show();
        $('#j-disagree-box').hide();
    }else{
        $('#j-agree-box').hide();
        $('#j-disagree-box').show();
    }
}
function beforeCommit(){
    var isSupplierAgree = parseInt($('#isSupplierAgree').val());
    if(isSupplierAgree!==0 && isSupplierAgree!==1){
        return WST.msg(WST.lang('select_is_handle'));
    }
    var _type = $('#goodsServiceType').val();
    if(_type==0 || _type==2){
        // 商家同意
        commit();
    }else{
        // 退款
        refund();
    }
}
// 退款
function refund(){
    var postData = {
        id:$('#id').val(),
        isSupplierAgree:$('#isSupplierAgree').val()
    }
    console.log('postData', postData);
    if(postData.isSupplierAgree!=0 && postData.isSupplierAgree!=1){
        return WST.msg(WST.lang('select_is_handle'));
    }
    if(postData.isSupplierAgree==0){
        // 不受理
        var disagreeRemark =  $('#disagreeRemark').val();
        if(disagreeRemark.length==0){
            return WST.msg(WST.lang('require_order_service_disagree'));
        }
        postData.disagreeRemark = disagreeRemark;
    }
    $.post(WST.U("supplier/orderservices/dealRefund"),postData,function(res){
        var json = WST.toJson(res);
        WST.msg(json.msg);
        if(json.status==1){
            return goBack();
        }
    });
}


// 提交换货
function commit(){
    var postData = {
        id:$('#id').val(),
        isSupplierAgree:$('#isSupplierAgree').val()
    }
    if(postData.isSupplierAgree!=0 && postData.isSupplierAgree!=1){
        return WST.msg(WST.lang('select_is_handle'));
    }
    if(postData.isSupplierAgree==1){
        // 受理
        var supplierAddress =  $('#supplierAddress').val();
        var supplierName =  $('#supplierName').val();
        var supplierPhone =  $('#supplierPhone').val();
        if(supplierAddress.length==0){
            return WST.msg(WST.lang('require_order_service_supp_addr'));
        }
        if(supplierName.length==0){
            return WST.msg(WST.lang('require_order_service_supp_name'));
        }
        if(supplierPhone.length==0){
            return WST.msg(WST.lang('require_order_service_supp_phone'));
        }
        postData.supplierAddress = supplierAddress;
        postData.supplierName = supplierName;
        postData.supplierPhone = supplierPhone;
    }
    if(postData.isSupplierAgree==0){
        // 不受理
        var disagreeRemark =  $('#disagreeRemark').val();
        if(disagreeRemark.length==0){
            return WST.msg(WST.lang('require_order_service_disagree'));
        }
        postData.disagreeRemark = disagreeRemark;
    }

    $.post(WST.U("supplier/orderservices/dealApply"),postData,function(res){
        var json = WST.toJson(res);
        WST.msg(json.msg);
        if(json.status==1){
            return goBack();
        }
    });
}
// 确认收货
function receive(p){
    var postData = {
        id:$('#id').val(),
        isSupplierAccept:$('#isSupplierAccept').val(),
        supplierRejectType:$('#supplierRejectType').val(),
        supplierRejectOther:$('#supplierRejectOther').val()
    }
    if(postData.supplierRejectType=='10000' && postData.supplierRejectOther.length==0){
        return WST.msg(WST.lang('require_order_service_reject_reason'));
    }
    $.post(WST.U('supplier/orderservices/supplierReceive'),postData,function(res){
        var json = WST.toJson(res);
        WST.msg(json.msg);
        if(json.status==1){
            return goBack();
        }
    })
}
// 是否确认收货
function isSupplierAccept(val){
    $('#isSupplierAccept').val(val);
    if(val==-1){
        $('#j-receive-box').show();
    }else{
        $('#j-receive-box').hide();
    }
}
// 选择拒收类型
function changeRejectType(val){
    if(val==10000){
        // 显示"原因输入框"
        $('#j-receive-input-box').show();
    }else{
        $('#j-receive-input-box').hide();
    }
}



// 商家发货相关

// 选择物流方式
function supplierExpressType(val){
    $('#supplierExpressType').val(val);
    if(val==1){
        $('.j-express-box').show();
    }else{
        $('.j-express-box').hide();
    }
}

// 发货
function send(p){
    var postData = WST.getParams('.ex-ipt');
    postData.id = $('#id').val();
    if(postData.supplierExpressType==1){
        if(postData.supplierExpressId==0)return WST.msg(WST.lang('require_order_service_express'));
        if(postData.supplierExpressNo.length==0)return WST.msg(WST.lang('require_order_service_express_no'));
    }
    $.post(WST.U('supplier/orderservices/supplierSend'),postData,function(res){
        var json = WST.toJson(res);
        WST.msg(json.msg);
        if(json.status==1){
            return goBack(p);
        }
    });
}


function goBack(){
    history.go(-1);
    // location.href =
}
