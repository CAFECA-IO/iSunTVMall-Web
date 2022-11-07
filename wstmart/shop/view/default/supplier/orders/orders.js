var laytpl = layui.laytpl;
var WST_CURR_PAGE = 1;
// 提醒发货
function noticeDeliver(id){
	var box = WST.confirm({content:WST.lang('are_you_sure_you_want_to_remind_delivery'),yes:function(){
			layer.close(box);
			var ll = WST.load({msg:WST.lang('are_you_sure_you_want_to_remind_delivery')});
			$.post(WST.U('shop/supplierorders/noticeDeliver'),{id:id},function(data){
				var json = WST.toJson(data);
				if(json.status>0){
					WST.msg(json.msg,{icon:1});
					waitReceiveByPage(WST_CURR_PAGE);
				    layer.close(ll);
				}else{
					WST.msg(json.msg,{icon:2});
				}
			});
     }});
}

function waitPayByPage(p){
	$('#loading').show();
	var params = {};
	params = WST.getParams('.s-query');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(WST.U('shop/supplierorders/waitPayByPage'),params,function(data,textStatus){
		$('#loading').hide();
	    var json = WST.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1){
	    	json = json.data;
	    	if(params.page>json.last_page && json.last_page >0){
               waitPayByPage(json.last_page);
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
		        	 skin: '#e23e3d',
		        	 groups: 3,
		        	 jump: function(e, first){
		        		 if(!first){
		        			 waitPayByPage(e.curr);
		        		 }
		        	 }
		    });
       	}
	});
}
function waitReceiveByPage(p){
	$('#loading').show();
	var params = {};
	params = WST.getParams('.s-query');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(WST.U('shop/supplierorders/waitReceiveByPage'),params,function(data,textStatus){
		$('#loading').hide();
	    var json = WST.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1){
	    	json = json.data;
	    	if(params.page>json.last_page && json.last_page >0){
               waitReceiveByPage(json.last_page);
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
		        	 skin: '#e23e3d',
		        	 groups: 3,
		        	 jump: function(e, first){
		        		 if(!first){
		        			 waitReceiveByPage(e.curr);
		        		 }
		        	 }
		    });
       	}
	});
}
function toReceive(id){
	WST.confirm({content:WST.lang('su_or_msg1'),yes:function(){
		var ll = WST.load({msg:WST.lang('are_you_sure_you_want_to_remind_delivery')});
		$.post(WST.U('shop/supplierorders/receive'),{id:id},function(data){
			var json = WST.toJson(data);
			if(json.status>0){
				WST.msg(json.msg,{icon:1});
				waitReceiveByPage(WST_CURR_PAGE);
			    layer.close(ll);
			}else{
				WST.msg(json.msg,{icon:2});
			}
		});
	}})
}
function waitAppraiseByPage(p){
	$('#loading').show();
	var params = {};
	params = WST.getParams('.s-query');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(WST.U('shop/supplierorders/waitAppraiseByPage'),params,function(data,textStatus){
		$('#loading').hide();
	    var json = WST.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1){
	    	json = json.data;
	    	if(params.page>json.last_page && json.last_page >0){
               waitAppraiseByPage(json.last_page);
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
		        skin: '#e23e3d',
		        groups: 3,
		        jump: function(e, first){
		        	if(!first){
		        		waitAppraiseByPage(e.curr);
		        	}
		        }
		    });
       	}
	});
}
function finishByPage(p){
	$('#loading').show();
	var params = {};
	params = WST.getParams('.s-query');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(WST.U('shop/supplierorders/finishByPage'),params,function(data,textStatus){
		$('#loading').hide();
	    var json = WST.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1){
	    	json = json.data;
            if(params.page>json.last_page && json.last_page >0){
                finishByPage(json.last_page);
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
		        skin: '#e23e3d',
		        groups: 3,
		        jump: function(e, first){
		        	if(!first){
		        		finishByPage(e.curr);
		        	}
		        }
		    });
       	}
	});
}
function cancel(id,type){
	var ll = WST.load({msg:WST.lang('loading')});
	$.post(WST.U('shop/supplierorders/toCancel'),{id:id},function(data){
		layer.close(ll);
		var w = WST.open({
			    type: 1,
			    title:WST.lang('cancellation_of_order'),
			    shade: [0.6, '#000'],
			    border: [0],
			    content: data,
			    area: ['500px', '260px'],
			    btn: [WST.lang('submit'), WST.lang('close_window')],
		        yes: function(index, layero){
		        	var reason = $.trim($('#reason').val());
		        	ll = WST.load({msg:WST.lang('loading')});
				    $.post(WST.U('shop/supplierorders/cancellation'),{id:id,reason:reason},function(data){
				    	layer.close(w);
				    	layer.close(ll);
				    	var json = WST.toJson(data);
						if(json.status==1){
							WST.msg(json.msg, {icon: 1});
							if(type==0){
								waitPayByPage(WST_CURR_PAGE);
							}else{
								waitReceiveByPage(WST_CURR_PAGE);
							}
						}else{
							WST.msg(json.msg, {icon: 2});
						}
				   });
		        }
			});
	});
}
function toReject(id){
	var ll = WST.load({msg:WST.lang('loading')});
	$.post(WST.U('shop/supplierorders/toReject'),{id:id},function(data){
		layer.close(ll);
		var w = WST.open({
			    type: 1,
			    title:WST.lang('reject_order'),
			    shade: [0.6, '#000'],
			    border: [0],
			    content: data,
			    area: ['500px', '300px'],
			    btn: [WST.lang('submit'), WST.lang('close_window')],
		        yes: function(index, layero){
		        	var params = {};
		        	params.reason = $.trim($('#reason').val());
		        	params.content = $.trim($('#content').val());
		        	params.id = id;
		        	if(params.id==10000 && params.conten==''){
		        		WST.msg(WST.lang('please_input_rejection_reason'),{icon:2});
		        		return;
		        	}
		        	ll = WST.load({msg:WST.lang('loading')});
				    $.post(WST.U('shop/supplierorders/reject'),params,function(data){
				    	layer.close(w);
				    	layer.close(ll);
				    	var json = WST.toJson(data);
						if(json.status==1){
							WST.msg(json.msg, {icon: 1});
							waitReceiveByPage(WST_CURR_PAGE);
						}else{
							WST.msg(json.msg, {icon: 2});
						}
				   });
		        }
			});
	});
}
function changeRejectType(v){
	if(v==10000){
		$('#rejectTr').show();
	}else{
		$('#rejectTr').hide();
	}
}
function cancelByPage(p){
	$('#loading').show();
	var params = {};
	params = WST.getParams('.s-query');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(WST.U('shop/supplierorders/cancelByPage'),params,function(data,textStatus){
		$('#loading').hide();
	    var json = WST.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1){
	    	json = json.data;
            if(params.page>json.last_page && json.last_page >0){
                cancelByPage(json.last_page);
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
		        skin: '#e23e3d',
		        groups: 3,
		        jump: function(e, first){
		        	if(!first){
		        		cancelByPage(e.curr);
		        	}
		        }
		    });
       	}
	});
}
function abnormalByPage(p){
	$('#loading').show();
	var params = {};
	params = WST.getParams('.s-query');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(WST.U('shop/supplierorders/abnormalByPage'),params,function(data,textStatus){
		$('#loading').hide();
	    var json = WST.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1){
	    	json = json.data;
	    	if(params.page>json.last_page && json.last_page >0){
               abnormalByPage(json.last_page);
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
		        skin: '#e23e3d',
		        groups: 3,
		        jump: function(e, first){
		        	if(!first){
		        		abnormalByPage(e.curr);
		        	}
		        }
		    });
       	}
	});
}
function refund(id,src){
    var ll = WST.load({msg:WST.lang('loading')});
	$.post(WST.U('shop/supplierorders/toRefund'),{id:id},function(data){
		layer.close(ll);
		var w = WST.open({
			    type: 1,
			    title:WST.lang('apply_for_a_refund'),
			    shade: [0.6, '#000'],
			    border: [0],
			    content: data,
			    area: ['500px', '320px'],
			    btn: [WST.lang('submit'), WST.lang('close_window')],
		        yes: function(index, layero){
		        	var params = {};
		        	params.reason = $.trim($('#reason').val());
		        	params.content = $.trim($('#content').val());
		        	params.money = $.trim($('#money').val());
		        	params.id = id;
		        	if(params.money<0){
		        		WST.msg(WST.lang('su_or_msg1'),{icon:2});
		        		return;
		        	}
		        	if(params.id==10000 && params.conten==''){
		        		WST.msg(WST.lang('please_enter_the_reason'),{icon:2});
		        		return;
		        	}
		        	ll = WST.load({msg:WST.lang('loading')});
				    $.post(WST.U('shop/Supplierorderrefunds/refund'),params,function(data){
				    	layer.close(ll);
				    	var json = WST.toJson(data);
						if(json.status==1){
							WST.msg(json.msg, {icon: 1});
							layer.close(w);
							if(src=='abnormal'){
                                abnormalByPage(WST_CURR_PAGE);
							}else{
                                cancelByPage(WST_CURR_PAGE);
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
	location.href=WST.U('shop/supplierorders/detail','id='+id+'&src='+src+'&p='+WST_CURR_PAGE);
}
function complain(id,src){
	location.href=WST.U('shop/supplierordercomplains/complain','orderId='+id+'&src='+src+'&p='+WST_CURR_PAGE);
}
function afterSale(id,src){
	location.href=WST.U('shop/supplierorderservices/index','orderId='+id+'&src='+src+'&p='+WST_CURR_PAGE);
}


/******************** 评价页面 ***********************/
function appraisesShowImg(id){
  layer.photos({
      photos: '#'+id
    });
}
function toAppraise(id){
  location.href=WST.U("shop/supplierorders/orderAppraise",{'oId':id});
}
//文件上传
function upload(n){
    var uploader =WST.upload({
        pick:'#filePicker'+n,
        formData: {dir:'appraises',isThumb:1},
        fileNumLimit:5,
        accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
        callback:function(f,file){
          var json = WST.toJson(f);
          if(json.status==1){
          var tdiv = $("<div style='width:75px;float:left;margin-right:5px;'>"+
                       "<img class='appraise_pic"+n+"' width='75' height='75' src='"+WST.conf.RESOURCE_PATH+"/"+json.savePath+json.thumb+"' v='"+json.savePath+json.name+"'></div>");
          var btn = $('<div style="position:relative;top:-80px;left:60px;cursor:pointer;" ><img src="'+WST.conf.ROOT+'/wstmart/shop/view/default/img/seller_icon_error.png"></div>');
          tdiv.append(btn);
          $('#picBox'+n).append(tdiv);
          btn.on('click','img',function(){
            uploader.removeFile(file);
            $(this).parent().parent().remove();
            uploader.refresh();
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

function validator(n){
  $('#appraise-form'+n).validator({
         fields: {
                  score:  {
                    rule:"required",
                    msg:{required:WST.lang('su_or_msg3')},
                    ok:"",
                    target:"#score_error"+n,
                  },

              },

            valid: function(form){
              var params = {};
              //获取该评价的内容
              params.content = $('#content'+n).val();
              // 获取该评价附件
              var photo=[];
              var images = [];
              $('.appraise_pic'+n).each(function(k,v){
                  var img = $(this).attr('v');
                      // 用于评价成功后的显示
                      photo.push(WST.conf.RESOURCE_PATH+'/'+img);
                  images.push(img);
              });
              params.images = images.join(',');
              //获取评分
              params.goodsScore = $('.goodsScore'+n).find('[name=score]').val();
              params.timeScore = $('.timeScore'+n).find('[name=score]').val();
              params.serviceScore = $('.serviceScore'+n).find('[name=score]').val();
              params.goodsId = $('#gid'+n).val();
              params.orderId = $('#oid'+n).val();
              params.goodsSpecId = $('#gsid'+n).val();
              params.orderGoodsId = $('#ogId'+n).val();

              $.post(WST.U('shop/suppliergoodsAppraises/add'),params,function(data,dataStatus){
                var json = WST.toJson(data);
                if(json.status==1){
                   var thisbox = $('#app-box'+n);
                   var html = '<div class="appraise-area"><div class="appraise-item"><div class="appraise-title">'+WST.lang('product_rating')+'：</div>';
                       html += '<div class="appraise-content">';
                       // 商品评分
                       for(var i=1;i<=params.goodsScore;i++){
                          html +='<img src="'+WST.conf.STATIC+'/plugins/raty/img/star-on-big.png">';
                       }
                       html +='</div></div><div class="wst-clear"></div><div class="appraise-item"><div class="appraise-title"> '+WST.lang('timeliness_score')+'：</div>'
                       html +='<div class="appraise-content">'
                       // 时效评分
                       for(var i=1;i<=params.timeScore;i++){
                          html +='<img src="'+WST.conf.STATIC+'/plugins/raty/img/star-on-big.png">';
                       }
                       html +='</div></div><div class="wst-clear"></div><div class="appraise-item"><div class="appraise-title">'+WST.lang('service_score')+'：</div>';
                       html +='<div class="appraise-content">';
                       // 服务评分
                       for(var i=1;i<=params.serviceScore;i++){
                          html +='<img src="'+WST.conf.STATIC+'/plugins/raty/img/star-on-big.png">';
                       }
                       html +='</div></div><div class="wst-clear"></div><div class="appraise-item"><div class="appraise-title">'+WST.lang('comment_content')+'：</div>';
                       // 评价内容
                       html +='<div class="appraise-content">';
                        // 获取当前年月日
                       var  oDate = new Date();
                       var year = oDate.getFullYear()+'-';    //获取系统的年；
                       var month = oDate.getMonth()+1+'-';     //获取系统月份，由于月份是从0开始计算，所以要加1
                       var day = oDate.getDate();        // 获取系统日，
                       html +='<p>'+params.content+'['+year+month+day+']</p>';
                       html +='</div></div><div class="wst-clear"></div><div class="appraise-item"><div class="appraise-title"> </div>';
                       // 评价附件
                       html +='<div class="appraise-content">';
                       // 当前生成的相册id
                       var imgBoxId = "appraise-img-"+n;
                       html +='<div id='+imgBoxId+'>'
                       var count = photo.length;
                       for(var m=0;m<count;m++){
                          html += '<img src="'+photo[m].replace('.','_thumb.')+'" layer-src="'+photo[m]+'" width="75" height="75" style="margin-right:5px;">';
                       }
                       html +='</div></div></div></div>';
                       thisbox.html(html);
                       // 调用相册层
                       appraisesShowImg(imgBoxId);

                }else{
                  WST.msg(json.msg,{icon:2});
                }
              });

        }
  });
}

/* 用户评价管理 */
function showImg(id){
  layer.photos({
      photos: '#img-file-'+id
    });
}
function userAppraise(p){
  $('#list').html('<img src="'+WST.conf.ROOT+'/wstmart/shop/view/default/supplier/img/loading.gif">'+ WST.lang('loading'));
  var params = {};
  params = WST.getParams('.s-query');
  params.key = $.trim($('#key').val());
  params.page = p;
  $.post(WST.U('shop/suppliergoodsAppraises/userAppraise'),params,function(data,textStatus){
      var json = WST.toJson(data);
      if(!json.data.data){
      	$('#list').html('');
      }
      if(json.status==1){
          if(params.page>json.data.last_page && json.data.last_page >0){
              userAppraise(json.data.last_page);
              return;
          }
          var gettpl = document.getElementById('tblist').innerHTML;
          laytpl(gettpl).render(json.data.data, function(html){
            $('#list').html(html);
            for(var g=0;g<=json.data.data.length;g++){
              showImg(g);
            }
           $('.j-lazyImg').lazyload({ effect: "fadeIn",failurelimit : 10,threshold: 200,placeholder:window.conf.RESOURCE_PATH+'/'+window.conf.GOODS_LOGO});
          });
          laypage({
               cont: 'pager',
               pages:json.data.last_page,
               curr: json.data.current_page,
               skin: '#e23e3d',
               groups: 3,
               jump: function(e, first){
                    if(!first){
                      userAppraise(e.curr);
                    }
               }
           });
        }
  });
}
/**************** 用户投诉页面 *****************/
function userComplainInit(){
	 var uploader =WST.upload({
        pick:'#filePicker',
        formData: {dir:'complains',isThumb:1},
        fileNumLimit:5,
        accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
        callback:function(f,file){
          var json = WST.toJson(f);
          if(json.status==1){
          var tdiv = $("<div style='width:75px;float:left;margin-right:5px;'>"+
                       "<img class='complain_pic"+"' width='75' height='75' src='"+WST.conf.RESOURCE_PATH+"/"+json.savePath+json.thumb+"' v='"+json.savePath+json.name+"'></div>");
          var btn = $('<div style="position:relative;top:-80px;left:60px;cursor:pointer;" ><img src="'+WST.conf.ROOT+'/wstmart/shop/view/default/img/seller_icon_error.png"></div>');
          tdiv.append(btn);
          $('#picBox').append(tdiv);
          btn.on('click','img',function(){
            uploader.removeFile(file);
            $(this).parent().parent().remove();
            uploader.refresh();
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
function saveComplain(historyURL){
   /* 表单验证 */
  $('#complainOrderForm').isValid(function(v){
		if(v){
              var params = WST.getParams('.ipt');
              var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
              var img = [];
              $('.complain_pic').each(function(){
                    img.push($(this).attr('v'));
              });
              params.complainAnnex = img.join(',');
              $.post(WST.U('shop/supplierordercomplains/saveComplain'),params,function(data,textStatus){
                    layer.close(loading);
                    var json = WST.toJson(data);
                    if(json.status=='1'){
                        WST.msg(WST.lang('su_or_msg4'), {icon: 6},function(){
                         location.href = WST.U('shop/supplierordercomplains/index');
                       });
                    }else{
                          WST.msg(json.msg,{icon:2});
                    }
              });
        }
  });
}

/*********************** 用户投诉列表页面 ***************************/
function toView(id){
  location.href=WST.U('shop/supplierordercomplains/getUserComplainDetail','id='+id+'&p='+WST_CURR_PAGE);
}
function complainByPage(p){
  $('#list').html('<img src="'+WST.conf.ROOT+'/wstmart/shop/view/default/supplier/img/loading.gif">'+WST.lang('loading'));
  var params = {};
  params = WST.getParams('.s-query');
  params.key = $.trim($('#key').val());
  params.page = p;
  $.post(WST.U('shop/supplierordercomplains/queryUserComplainByPage'),params,function(data,textStatus){
      var json = WST.toJson(data);
      if(json.status==1){
          if(params.page>json.data.last_page && json.data.last_page >0){
              complainByPage(json.data.last_page);
              return;
          }
          var gettpl = document.getElementById('tblist').innerHTML;
          laytpl(gettpl).render(json.data.data, function(html){
            $('#list').html(html);
          });
          if(json.data.last_page>1){
            laypage({
               cont: 'pager',
               pages:json.data.last_page,
               curr: json.data.current_page,
               skin: '#e23e3d',
               groups: 3,
               jump: function(e, first){
                    if(!first){
                      complainByPage(e.curr);
                    }
                  }
            });


          }else{
            $('#pager').empty();
          }
        }
  });
}
//导出订单
function toExport(typeId,status,type){
	var params = {};
	params = WST.getParams('.s-query');
	params.typeId = typeId;
	params.orderStatus = status;
	params.type = type;
	var box = WST.confirm({content:WST.lang('are_you_sure_you_want_to_export_the_order'),yes:function(){
		layer.close(box);
		location.href=WST.U('shop/supplierorders/toExport',params);
         }});
}

//调用腾讯地图
function storeMap(){
	var latitude = parseFloat($("#latitude").val());
	var longitude = parseFloat($("#longitude").val());
	var storeName = $("#storeName").val();
	var center = new qq.maps.LatLng(latitude,longitude);
	//定义map变量 调用 qq.maps.Map() 构造函数   获取地图显示容器
	var map = new qq.maps.Map(document.getElementById("container"), {
		center: center,
		zoom:15
	});
	//添加文本备注
	var infoWin = new qq.maps.InfoWindow({
		map: map
	});
	infoWin.open();
	infoWin.setContent(storeName);
	infoWin.setPosition(map.getCenter());
}
