var laytpl = layui.laytpl;
var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('order_number'), name:'orderNo' ,width:140,sortable:true,renderer: function(val,item,rowIndex){
            if(item['orderCodeTitle'] != ""){
                return item['orderNo']+"【"+item['orderCodeTitle']+"】";
            }else{
                return item['orderNo'];
            }
        }},
        {title:WST.lang('complainants'), name:'loginName' ,width:70,sortable:true,renderer: function(val,item,rowIndex){
            return WST.blank(item['userName'],item['loginName']);
        }},
        {title:WST.lang('reasons_for_complaints'), name:'complainContent' ,width:200,sortable:true,renderer: function(val,item,rowIndex){
            return WST.cutStr(item['complainContent'],50);
        }},
        {title:WST.lang('complaint_time'), name:'complainTime' ,width:100,sortable:true},
        {title:WST.lang('complaint_status'), name:'complainStatus' ,width:150,sortable:true},
        {title:WST.lang('op'), name:'' ,width:100, align:'center', renderer: function(val,item,rowIndex){
            var h = "";
            h += "<a  class='btn btn-blue' onclick='javascript:toView("+item['complainId']+")'><i class='fa fa-search'></i>"+WST.lang('see')+"</a> ";
            if(item['needReply']==1)h += "<a  class='btn btn-green' onclick='javascript:toRespond(" + item['complainId'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('responding_to_a_lawsuit')+"</a> ";
            return h;
        }}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',checkCol:true,multiSelect:true,nowrap:true,
        url: WST.U('shop/ordercomplains/queryShopComplainByPage'), fullWidthRows: true, autoLoad: false,remoteSort: true,
        plugins: [
            $('#pg').mmPaginator()
        ]
    });
    loadGrid(p);
}

function loadGrid(p){
    p = (p<=1)?1:p;
    mmg.load({orderNo:$("#orderNo").val(),page:p});
}
function waituserPayByPage(p){
	$('#loading').show();
	var params = {};
	params = WST.getParams('.s-ipt');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(WST.U('shop/orders/waituserPayByPage'),params,function(data,textStatus){
		$('#loading').hide();
	    var json = WST.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1){
	    	json = json.data;
	    	if(params.page>json.last_page && json.last_page >0){
               waituserPayByPage(json.last_page);
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
		        		waituserPayByPage(e.curr);
		        	}
		        }
		    });
       	}
	});
}
function waitDivleryByPage(p){
	$('#loading').show();
	var params = {};
	params = WST.getParams('.s-ipt');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(WST.U('shop/orders/waitDeliveryByPage'),params,function(data,textStatus){
		$('#loading').hide();
	    var json = WST.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1){
	    	json = json.data;
	    	if(params.page>json.last_page && json.last_page >0){
               waitDivleryByPage(json.last_page);
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
		        		waitDivleryByPage(e.curr);
		        	}
		        }
		    });
       	}
	});
}
function deliveredByPage(p){
  $('#loading').show();
  var params = {};
  params = WST.getParams('.s-ipt');
  params.key = $.trim($('#key').val());
  params.page = p;
  $.post(WST.U('shop/orders/deliveredByPage'),params,function(data,textStatus){
    $('#loading').hide();
      var json = WST.toJson(data);
      console.log(json);
      $('.j-order-row').remove();
      if(json.status==1){
        json = json.data;
        if(params.page>json.last_page && json.last_page >0){
            deliveredByPage(json.last_page);
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
                   deliveredByPage(e.curr);
               }
            }
        });
     }
  });
}
function editOrderMoney(id){
	var ll = WST.load({msg:WST.lang('loading')});
	$.post(WST.U('shop/orders/getMoneyByOrder'),{id:id},function(data){
    	layer.close(ll);
    	var json = WST.toJson(data);
		if(json.status>0 && json.data){
			var tmp = json.data;
			$('#m_orderNo').html(tmp.orderNo);
			$('#m_goodsMoney').html(tmp.goodsMoney);
			$('#m_deliverMoney').html(tmp.deliverMoney);
			$('#m_totalMoney').html(tmp.totalMoney);
			$('#m_realTotalMoney').html(tmp.realTotalMoney);
			WST.open({type: 1,title:WST.lang('modify_order_price'),shade: [0.6, '#000'],border: [0],
				content: $('#editMoneyBox'),area: ['550px', '350px'],btn: [WST.lang('ok'),WST.lang('cancel')],
				yes:function(index, layero){
					var newOrderMoney = $('#m_newOrderMoney').val();
					WST.confirm({content:WST.lang('are_you_sure_the_revised_order_price', [newOrderMoney]),yes:function(cf){

						var ll = WST.load({msg:WST.lang('loading')});
						$.post(WST.U('shop/orders/editOrderMoney'),{id:id,orderMoney:newOrderMoney},function(data){
							var json = WST.toJson(data);
							if(json.status>0){
								$('#newOrderMoney').val();
								WST.msg(json.msg,{icon:1});
								waituserPayByPage(WST_CURR_PAGE);
								layer.close(cf);
								layer.close(index);
						    	layer.close(ll);
							}else{
								WST.msg(json.msg,{icon:2});
							}
						});
					}});
				}
			});
            $('#m_newOrderMoney').val("");
		}
    });
}

    function deliver(id,deliverType){
	if(deliverType==1){
        WST.confirm({content:WST.lang('customer_has_taken_delivery'), yes:function(tips){
            var ll = WST.load(WST.lang('loading'));
            $.post(WST.U('shop/orders/deliver'),{id:id,expressId:0,expressNo:''},function(data){
				var json = WST.toJson(data);
				if(json.status>0){
					WST.msg(json.msg,{icon:1});
					waitDivleryByPage(WST_CURR_PAGE);
					layer.close(tips);
				    layer.close(ll);
				}else{
					WST.msg(json.msg,{icon:2});
				}
			});
        }});
	}else{
        $.post(WST.U('shop/orders/waitdeliverbyid'),{id:id},function(json){
        	json = WST.toJson(json);
            if(json.status > 0){
            	json = json.data;
                $('#goods_info').empty();
                $('.user_name').empty();
                $('.user_address').empty();
                $('.user_phone').empty();
                $.each(json.list, function(idx, obj) {
                    $('#goods_info').append("<tr><td class='delivery_select'><input class='chk' "+(obj.hasDeliver?'disabled':'')+" type='checkbox' value='"+obj.id+"'/></td><td class='delivery_good'><img src='"+WST.conf.RESOURCE_PATH+'/'+obj.goodsImg+"'/></td><td>"+obj.goodsName+"</td><td>"+obj.goodsNum+"</td><td>"+(obj.hasDeliver?WST.lang('delivered'):'')+"</td></tr>");
                });
				$('.user_name').append(json.userName);
				$('.user_address').append(json.userAddress);
				$('.user_phone').append(json.userPhone);
            }
        });
		WST.open({type: 1,title:WST.lang('please_input_delivery_information'),shade: [0.6, '#000'], border: [0],offset:10,
			content: $('#deliverBox'),area: ['1000px', ''],btn: [WST.lang('confirm_delivery'),WST.lang('cancel')],
			yes:function(index, layero){
                var params = {};
                params.id = id;
                params.expressId = $('#expressId').val();
                params.expressNo = $('#expressNo').val();
                params.deliverType = $("input[name='delivery_type']:checked").val();
                if(params.deliverType == 1){
                    if(params.expressId == '' || params.expressNo == ''){
                        WST.msg(WST.lang('please_fill_in_the_express_information'),{icon:2});
                        return;
                    }
                }
                var selectIds = WST.getChks('.chk');
                if(selectIds.length==0){
                    WST.msg(WST.lang('please_select_the_goods_to_be_delivered'),{icon:2});
                    return;
                }
                params.selectOrderGoodsIds = selectIds.join(',');
                var ll = WST.load({msg:WST.lang('loading')});
				$.post(WST.U('shop/orders/deliver'),params,function(data){
					var json = WST.toJson(data);
					if(json.status>0){
						$('#deliverForm')[0].reset();
                        $('.deliver_express').show();
						WST.msg(json.msg,{icon:1});
						waitDivleryByPage(WST_CURR_PAGE);
						layer.close(index);
				    	layer.close(ll);
					}else{
						WST.msg(json.msg,{icon:2});
					}
				});
			}
	    });
    }
}
function finisedByPage(p){
	$('#loading').show();
	var params = {};
	params = WST.getParams('.s-ipt');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(WST.U('shop/orders/finishedByPage'),params,function(data,textStatus){
		$('#loading').hide();
	    var json = WST.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1){
	    	json = json.data;
	    	$('.order_remaker').remove();
            if(params.page>json.data.last_page && json.data.last_page >0){
                finisedByPage(json.data.last_page);
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
	        			 finisedByPage(e.curr);
	        		 }
	        	 }
	        });
       	}
	});
}
function failureByPage(p){
	$('#loading').show();
	var params = {};
	params = WST.getParams('.s-ipt');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(WST.U('shop/orders/failureByPage'),params,function(data,textStatus){
		$('#loading').hide();
	    var json = WST.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1){
	    	json = json.data;
	    	$('.order_remaker').remove();
            if(params.page>json.last_page && json.last_page >0){
                failureByPage(json.last_page);
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
	        			 failureByPage(e.curr);
	        		 }
	        	 }
	        });
       	}
	});
}
function refundByPage(p){
	$('#loading').show();
	var params = {};
	params = WST.getParams('.s-ipt');
	params.key = $.trim($('#key').val());
	params.page = p;
	params.refund = 1;
	$.post(WST.U('shop/orders/refundByPage'),params,function(data,textStatus){
		$('#loading').hide();
		var json = WST.toJson(data);
		$('.j-order-row').remove();
		if(json.status==1){
			json = json.data;
			$('.order_remaker').remove();
			if(params.page>json.last_page && json.last_page >0){
				refundByPage(json.last_page);
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
						refundByPage(e.curr);
					}
				}
			});
		}
	});
}
function refund(id,type){
    var ll = WST.load({msg:WST.lang('loading')});
	$.post(WST.U('shop/orders/toShopRefund'),{id:id},function(data){
		layer.close(ll);
		var w = WST.open({
			    type: 1,
			    title:WST.lang('refund_operation'),
			    shade: [0.6, '#000'],
			    border: [0],
			    content: data,
			    area: ['500px', '370px'],
			    btn: [WST.lang('submit'), WST.lang('close_window')],
		        yes: function(index, layero){
		        	var params = {};
		        	params.refundStatus = $('#refundStatus1')[0].checked?1:-1;
		        	params.content = $.trim($('#shopRejectReason').val());
		        	params.id = id;
		        	if(params.refundStatus==-1 && params.content==''){
		        		WST.msg(WST.lang('please_enter_the_reason_for_disagreement'),{icon:2});
		        		return;
		        	}
		        	ll = WST.load({msg:WST.lang('loading')});
				    $.post(WST.U('shop/orderrefunds/shoprefund'),params,function(data){
				    	layer.close(ll);
				    	var json = WST.toJson(data);
						if(json.status==1){
							WST.msg(json.msg, {icon: 1});
							layer.close(w);
							if(type=='failure'){
								failureByPage(WST_CURR_PAGE);
							}else{
								refundByPage(WST_CURR_PAGE);
							}

						}else{
							WST.msg(json.msg, {icon: 2});
						}
				   });
		        }
			});
	});
}
function view(id,src){
	location.href=WST.U('shop/orders/view','id='+id+'&src='+src+'&p='+WST_CURR_PAGE);
}


/********** 订单投诉列表 ***********/
function toView(id){
  location.href=WST.U('shop/ordercomplains/getShopComplainDetail','id='+id+'&p='+WST_CURR_PAGE);
}
function toBack(p){
    location.href=WST.U('shop/ordercomplains/shopcomplain','p='+p);
}
function toBacks(p,src){
    location.href=WST.U('shop/orders/'+src,'p='+p);
}
function toRespond(id){
  location.href=WST.U('shop/ordercomplains/respond','id='+id+'&p='+WST_CURR_PAGE);
}

function complainByPage(p){
  $('#list').html('<img src="'+WST.conf.ROOT+'/wstmart/shop/view/default/img/loading.gif">' + WST.lang('loading'));
  var params = {};
  params = WST.getParams('.s-query');
  params.key = $.trim($('#key').val());
  params.page = p;
  $.post(WST.U('shop/ordercomplains/queryShopComplainByPage'),params,function(data,textStatus){
      var json = WST.toJson(data);
      if(json.status==1 && json.data){
      	  var json = json.data;
          var gettpl = document.getElementById('tblist').innerHTML;
          laytpl(gettpl).render(json.data, function(html){
            $('#list').html(html);
          });
          laypage({
               cont: 'pager',
               pages:json.last_page,
               curr: json.current_page,
               skin: '#1890ff',
               groups: 3,
               jump: function(e, first){
                    if(!first){
                      complainByPage(e.curr);
                    }
               }
          });
        }
  });
}


/************  应诉页面  ************/
function respondInit(){
$('.gImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.RESOURCE_PATH+'/'+WST.conf.GOODS_LOGO});
  parent.showImg({photos: $('#photos-complain')});

  var uploader =WST.upload({
        pick:'#filePicker',
        formData: {dir:'complains',isThumb:1},
        fileNumLimit:5,
        accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
        callback:function(f,file){
          $('#annex').show();
          var json = WST.toJson(f);
          if(json.status==1){
          var tdiv = $("<div style='width:75px;float:left;margin-right:5px;'>"+
                       "<img class='respond_pic"+"' width='75' height='75' src='"+WST.conf.RESOURCE_PATH+"/"+json.savePath+json.thumb+"' v='"+json.savePath+json.name+"'></div>");
          var btn = $('<div style="position:relative;top:-80px;left:60px;cursor:pointer;" ><img src="'+WST.conf.ROOT+'/wstmart/shop/view/default/img/seller_icon_error.png"></div>');
          tdiv.append(btn);
          $('#picBox').append(tdiv);
          btn.on('click','img',function(){
              uploader.removeFile(file);
              $(this).parent().parent().remove();
              uploader.refresh();
              if($('#picBox').children().size()<=0)$('#annex').hide();
          });
          }else{
              WST.msg(json.msg,{icon:2});
          }
      },
      progress:function(rate){
          $('#uploadMsg').show().html(WST.lang('upload_rate')+rate+"%");
      }
    });
}
function saveRespond(p){
    $('#respondForm').isValid(function(v){
		if(v){
          var params = WST.getParams('.ipt');
          var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
          var img = [];
          $('.respond_pic').each(function(){
              img.push($(this).attr('v'));
          });
          params.respondAnnex = img.join(',');
          $.post(WST.U('shop/orderComplains/saveRespond'),params,function(data,textStatus){
                layer.close(loading);
                var json = WST.toJson(data);
                if(json.status=='1'){
                    WST.msg(WST.lang('submit_reply_tips'), {icon: 6},function(){
                       location.href = WST.U('shop/ordercomplains/shopComplain','p='+p);
                   });
                }else{
                      WST.msg(json.msg,{icon:2});
                }
          });
        }
    });
}
//导出订单
function toExport(typeId,status,type){
	var params = {};
	params = WST.getParams('.s-ipt');
	params.typeId = typeId;
	params.orderStatus = status;
	params.type = type;
	var box = WST.confirm({content:WST.lang('are_you_sure_you_want_to_export_the_order'),yes:function(){
		layer.close(box);
		location.href=WST.U('shop/orders/toExport',params);
         }});
}
//导出订单
function toDeliverTemplate(){
	var params = {};
	params = WST.getParams('.s-ipt');
	var box = WST.confirm({content:WST.lang('only_unshipped_items_will_be_exported'),yes:function(){
		layer.close(box);
		location.href=WST.U('shop/orders/toDeliverTemplate',params);
         }});
}
//导入订单
function toExportDeliver(){
	location.href=WST.U('shop/imports/toDeliverExport');
}


/**
 * 查询订单信息
 */
function getVerificatOrder(){
	var params = {};
	var verificationCode = $("#verificationCode").val();
	$('#orderInfo').html("");
	if(verificationCode.length<10){
		WST.msg(WST.lang('please_enter_the_correct_verification_code'),{icon:2});
		return;
	}
	params.verificationCode = verificationCode;
	$('#loading').show();
	$.post(WST.U('shop/orders/getVerificatOrder'),params,function(data,textStatus){
	    $('#loading').hide();
	    var json = WST.toJson(data);
	    if(json.status=='1'){
	       	var gettpl = document.getElementById('tblist').innerHTML;
	       	laytpl(gettpl).render(json.data, function(html){
	       		$('#orderInfo').html(html);
	       		$('.gImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.ROOT+'/'+WST.conf.GOODS_LOGO});
	       	});
	    }else{
	      	WST.msg(json.msg,{icon:2});
	    }
	});
}

/**
 * 验证确认核销
 */
function orderVerificat() {
	var params = {};
	var verificationCode = $("#verificationCode").val();
	if(verificationCode.length<10){
		WST.msg(WST.lang('please_enter_the_correct_verification_code'),{icon:2});
		return;
	}
	params.verificationCode = verificationCode;
	var box = WST.confirm({content:WST.lang('are_you_sure_you_want_to_cancel'),yes:function(){
		layer.close(box);
		$.post(WST.U('shop/orders/orderVerificat'),params,function(data,textStatus){
		    var json = WST.toJson(data);
		    if(json.status=='1'){
		       	WST.msg(json.msg);
		       	getVerificatOrder();
		    }else{
		      	WST.msg(json.msg,{icon:2});
		    }
		});
  	}});

}
function toEditOrderReceiveInfo(orderId){
	$.post(WST.U('shop/orders/getOrderReceiveInfo'),{orderId:orderId},function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status==1){
			layerbox =	layer.open({
				title:WST.lang('modify_shipping_address'),
				type: 1,
				area: ['800px', '300px'], //宽高
				content: $('.j-edit-box')
			});
			$('.j-edit-box').removeClass('hide');
			WST.setValues(json.data);
			$('#orderId').val(orderId);
			$('input[name="addrUserPhone"]').val(json.data.userPhone);
			$("select[id^='area_0_']").remove();
			var areaIdPath = json.data.areaIdPath.split("_");
			$('#area_0').val(areaIdPath[0]);
			var aopts = {id:'area_0',val:areaIdPath[0],childIds:areaIdPath,className:'j-areas'}
			WST.ITSetAreas(aopts);
		}else{
			WST.msg(json.msg,{icon:2});
		}
	});
}
//保存收货地址
function saveAddress(){
	var isNoSelected = false;
	$('.j-areas').each(function(){
		isSelected = true;
		if($(this).val()==''){
			isNoSelected = true;
			return;
		}
	})
	if(isNoSelected){
		WST.msg(WST.lang('please_select_the_full_shipping_address'),{icon:2});
		return;
	}
	var load = WST.load({msg:WST.lang('loading')});
	var orderId = $('#orderId').val();
	var params = WST.getParams('.j-eipt');
	params.areaId = WST.ITGetAreaVal('j-areas');
	params.orderId = orderId;
	if(params.userName==''){
		WST.msg(WST.lang('consignee_name_cannot_be_empty'),{icon:2});
		return;
	}
	if(params.userPhone==''){
		WST.msg(WST.lang('contact_number_cannot_be_empty'),{icon:2});
		return;
	}
	if(isNoSelected){
		WST.msg(WST.lang('please_select_an_address'),{icon:2});
		return;
	}
	if(params.userAddress==''){
		WST.msg(WST.lang('please_fill_in_the_detailed_address'),{icon:2});
		return;
	}
	$.post(WST.U('shop/orders/editOrderReceiveInfo'), params, function(data){
		layer.close(load);
		var json = WST.toJson(data);
		if( json.status == 1 ){
			WST.msg(json.msg, {icon: 1});
			setTimeout(function(){
				layer.close(layerbox);
			},1500);
		}else{
			WST.msg(json.msg,{icon:2});
		}
	});
}
function toEditOrderExpressInfo(orderId){
	$('#express-box').empty();
	$.post(WST.U('shop/orders/getOrderExpressInfo'),{orderId:orderId},function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status==1){
			if(json && json.data && json.data.length>0) {
				var gettpl = document.getElementById('expressList').innerHTML;
				laytpl(gettpl).render(json.data, function (html) {
					$('#express-box').append(html);
				});
				for(var i=0;i<json.data.length;i++){
					var expressId = json.data[i].expressId;
					$('.expressId').each(function(idx,item){
						if(i==idx){
							$(item).find('option').each(function(idx2,item2){
								if(parseInt($(item2).val()) == expressId){
									$(item2).prop("selected",true);
								}
							});
						}
					});
				}
			}
		}else{
			WST.msg(json.msg,{icon:2});
		}
	});
	var box = WST.open({type: 1,title:WST.lang('modify_logistics_information'),shade: [0.6, '#000'], border: [0],offset:10,
		content: $('#deliverBox'),area: ['1000px', '50%'],btn: [WST.lang('save'),WST.lang('cancel')],
		yes:function(index, layero){
			var params = {};
			var orderExpressIdArr = [];
			var expressIdArr = [];
			var expressNoArr = [];
			var flag = false;
			$('.orderExpressId').each(function(idx,item){
				var orderExpressId = $(item).val();
				orderExpressIdArr.push(orderExpressId);
			});
			$('.expressId').each(function(idx,item){
				var expressId = 0;
				$(item).find('option').each(function(idx2,item2) {
					if($(item2).prop('selected')){
						expressId = parseInt($(item2).val());
					}
				});
				if(expressId==0){
					WST.msg(WST.lang('please_choose_express_company'),{icon:2});
					flag = true;
				}
				expressIdArr.push(expressId);
			});
			$('.expressNo').each(function(idx,item){
				var expressNo = $(item).val();
				if(expressNo==''){
					WST.msg(WST.lang('please_fill_in_the_express_number'),{icon:2});
					flag = true;
				}
				expressNoArr.push(expressNo);
			});
			if(flag)return;
			params.orderId = orderId;
			params.orderExpressId = orderExpressIdArr.join(',');
			params.expressId = expressIdArr.join(',');
			params.expressNo = expressNoArr.join(',');
			$.post(WST.U('shop/orders/editOrderExpressInfo'), params, function(data){
				var json = WST.toJson(data);
				if( json.status == 1 ){
					WST.msg(json.msg, {icon: 1});
					setTimeout(function(){
						layer.close(box);
					},1500);
				}else{
					WST.msg(json.msg,{icon:2});
				}
			});
		}
	});

}
