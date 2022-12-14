var WST_CURR_PAGE = 1;
// ๆ้ๅ่ดง
function noticeDeliver(id){
	var box = WST.confirm({content:WST.lang('order_confirm_remind_delivery'),yes:function(){
			layer.close(box);
			var ll = WST.load({msg:WST.lang('submiting_tips')});
			$.post(WST.U('home/orders/noticeDeliver'),{id:id},function(data){
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
	params = WST.getParams('.u-query');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(WST.U('home/orders/waitPayByPage'),params,function(data,textStatus){
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
	params = WST.getParams('.u-query');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(WST.U('home/orders/waitReceiveByPage'),params,function(data,textStatus){
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
	WST.confirm({content:WST.lang('order_confirm_received'),yes:function(){
		var ll = WST.load({msg:WST.lang('submiting_tips')});
		$.post(WST.U('home/orders/receive'),{id:id},function(data){
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
	$.post(WST.U('home/orders/waitAppraiseByPage'),params,function(data,textStatus){
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
	params = WST.getParams('.u-query');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(WST.U('home/orders/finishByPage'),params,function(data,textStatus){
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
	var ll = WST.load({msg:WST.lang('loading_tips')});
	$.post(WST.U('home/orders/toCancel'),{id:id},function(data){
		layer.close(ll);
		var w = WST.open({
			    type: 1,
			    title:WST.lang('cancel_order'),
			    shade: [0.6, '#000'],
			    border: [0],
			    content: data,
			    area: ['500px', '260px'],
			    btn: [WST.lang('submit'), WST.lang('close')],
		        yes: function(index, layero){
		        	var reason = $.trim($('#reason').val());
		        	ll = WST.load({msg:WST.lang('submiting_tips')});
				    $.post(WST.U('home/orders/cancellation'),{id:id,reason:reason},function(data){
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
	var ll = WST.load({msg:WST.lang('loading_tips')});
	$.post(WST.U('home/orders/toReject'),{id:id},function(data){
		layer.close(ll);
		var w = WST.open({
			    type: 1,
			    title:WST.lang('order_reject'),
			    shade: [0.6, '#000'],
			    border: [0],
			    content: data,
			    area: ['500px', '300px'],
			    btn: [WST.lang('submit'), WST.lang('close')],
		        yes: function(index, layero){
		        	var params = {};
		        	params.reason = $.trim($('#reason').val());
		        	params.content = $.trim($('#content').val());
		        	params.id = id;
		        	if(params.id==10000 && params.conten==''){
		        		WST.msg(WST.lang('require_order_reject_reason'),{icon:2});
		        		return;
		        	}
		        	ll = WST.load({msg:WST.lang('submiting_tips')});
				    $.post(WST.U('home/orders/reject'),params,function(data){
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
	params = WST.getParams('.u-query');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(WST.U('home/orders/cancelByPage'),params,function(data,textStatus){
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
	params = WST.getParams('.u-query');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(WST.U('home/orders/abnormalByPage'),params,function(data,textStatus){
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
    var ll = WST.load({msg:WST.lang('loading_tips')});
	$.post(WST.U('home/orders/toRefund'),{id:id},function(data){
		layer.close(ll);
		var w = WST.open({
			    type: 1,
			    title:WST.lang('apply_refund'),
			    shade: [0.6, '#000'],
			    border: [0],
			    content: data,
			    area: ['500px', '320px'],
			    btn: [WST.lang('submit'), WST.lang('close')],
		        yes: function(index, layero){
		        	var params = {};
		        	params.reason = $.trim($('#reason').val());
		        	params.content = $.trim($('#content').val());
		        	params.money = $.trim($('#money').val());
		        	params.id = id;
		        	if(params.money<0){
		        		WST.msg(WST.lang('invalid_refund_amount'),{icon:2});
		        		return;
		        	}
		        	if(params.id==10000 && params.conten==''){
		        		WST.msg(WST.lang('require_order_refund_reason'),{icon:2});
		        		return;
		        	}
		        	ll = WST.load({msg:WST.lang('submiting_tips')});
				    $.post(WST.U('home/orderrefunds/refund'),params,function(data){
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
	location.href=WST.U('home/orders/detail','id='+id+'&src='+src+'&p='+WST_CURR_PAGE);
}
function complain(id,src){
	location.href=WST.U('home/ordercomplains/complain','orderId='+id+'&src='+src+'&p='+WST_CURR_PAGE);
}
function afterSale(id,src){
	location.href=WST.U('home/orderservices/index','orderId='+id+'&src='+src+'&p='+WST_CURR_PAGE);
}


/******************** ่ฏไปท้กต้ข ***********************/
function appraisesShowImg(id){
  layer.photos({
      photos: '#'+id
    });
}
function toAppraise(id){
  location.href=WST.U("home/orders/orderAppraise",{'oId':id});
}
//ๆไปถไธไผ?
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
          var btn = $('<div style="position:relative;top:-80px;left:60px;cursor:pointer;" ><img src="'+WST.conf.ROOT+'/wstmart/home/view/default/img/seller_icon_error.png"></div>');
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
          $('#uploadMsg').show().html(WST.lang('has_upload')+rate+"%");
      }
    });
}

function validator(n){
  $('#appraise-form'+n).validator({
         fields: {
                  score:  {
                    rule:"required",
                    msg:{required:WST.lang('order_appraise_score_limit_desc')},
                    ok:"",
                    target:"#score_error"+n,
                  },

              },

            valid: function(form){
              var params = {};
              //่ทๅ่ฏฅ่ฏไปท็ๅๅฎน
              params.content = $('#content'+n).val();
              // ่ทๅ่ฏฅ่ฏไปท้ไปถ
              var photo=[];
              var images = [];
              $('.appraise_pic'+n).each(function(k,v){
                  var img = $(this).attr('v');
                      // ็จไบ่ฏไปทๆๅๅ็ๆพ็คบ
                      photo.push(WST.conf.RESOURCE_PATH+'/'+img);
                  images.push(img);
              });
              params.images = images.join(',');
              //่ทๅ่ฏๅ
              params.goodsScore = $('.goodsScore'+n).find('[name=score]').val();
              params.timeScore = $('.timeScore'+n).find('[name=score]').val();
              params.serviceScore = $('.serviceScore'+n).find('[name=score]').val();
              params.goodsId = $('#gid'+n).val();
              params.orderId = $('#oid'+n).val();
              params.goodsSpecId = $('#gsid'+n).val();
              params.orderGoodsId = $('#ogId'+n).val();

              $.post(WST.U('home/goodsAppraises/add'),params,function(data,dataStatus){
                var json = WST.toJson(data);
                if(json.status==1){
                   var thisbox = $('#app-box'+n);
                   var html = '<div class="appraise-area"><div class="appraise-item"><div class="appraise-title">'+WST.lang('goods_score')+'๏ผ</div>';
                       html += '<div class="appraise-content">';
                       // ๅๅ่ฏๅ
                       for(var i=1;i<=params.goodsScore;i++){
                          html +='<img src="'+WST.conf.STATIC+'/plugins/raty/img/star-on-big.png">';
                       }
                       html +='</div></div><div class="wst-clear"></div><div class="appraise-item"><div class="appraise-title"> '+WST.lang('timeliness_score')+'๏ผ</div>'
                       html +='<div class="appraise-content">'
                       // ๆถๆ่ฏๅ
                       for(var i=1;i<=params.timeScore;i++){
                          html +='<img src="'+WST.conf.STATIC+'/plugins/raty/img/star-on-big.png">';
                       }
                       html +='</div></div><div class="wst-clear"></div><div class="appraise-item"><div class="appraise-title">'+WST.lang('service_score')+'๏ผ</div>';
                       html +='<div class="appraise-content">';
                       // ๆๅก่ฏๅ
                       for(var i=1;i<=params.serviceScore;i++){
                          html +='<img src="'+WST.conf.STATIC+'/plugins/raty/img/star-on-big.png">';
                       }
                       html +='</div></div><div class="wst-clear"></div><div class="appraise-item"><div class="appraise-title">'+WST.lang('appraise_content')+'๏ผ</div>';
                       // ่ฏไปทๅๅฎน
                       html +='<div class="appraise-content">';
                        // ่ทๅๅฝๅๅนดๆๆฅ
                       var  oDate = new Date();
                       var year = oDate.getFullYear()+'-';    //่ทๅ็ณป็ป็ๅนด๏ผ
                       var month = oDate.getMonth()+1+'-';     //่ทๅ็ณป็ปๆไปฝ๏ผ็ฑไบๆไปฝๆฏไป0ๅผๅง่ฎก็ฎ๏ผๆไปฅ่ฆๅ?1
                       var day = oDate.getDate();        // ่ทๅ็ณป็ปๆฅ๏ผ
                       html +='<p>'+params.content+'['+year+month+day+']</p>';
                       html +='</div></div><div class="wst-clear"></div><div class="appraise-item"><div class="appraise-title"> </div>';
                       // ่ฏไปท้ไปถ
                       html +='<div class="appraise-content">';
                       // ๅฝๅ็ๆ็็ธๅid
                       var imgBoxId = "appraise-img-"+n;
                       html +='<div id='+imgBoxId+'>'
                       var count = photo.length;
                       for(var m=0;m<count;m++){
                          html += '<img src="'+photo[m].replace(/(.*)\./,'$1_thumb.')+'" layer-src="'+photo[m]+'" width="75" height="75" style="margin-right:5px;">';
                       }
                       html +='</div></div></div></div>';
                       thisbox.html(html);
                       // ่ฐ็จ็ธๅๅฑ
                       appraisesShowImg(imgBoxId);

                }else{
                  WST.msg(json.msg,{icon:2});
                }
              });

        }
  });
}

/* ็จๆท่ฏไปท็ฎก็ */
function showImg(id){
  layer.photos({
      photos: '#img-file-'+id
    });
}
function userAppraise(p){
  $('#list').html('<img src="'+WST.conf.ROOT+'/wstmart/home/view/default/img/loading.gif">'+WST.lang('loading_tips'));
  var params = {};
  params = WST.getParams('.s-query');
  params.key = $.trim($('#key').val());
  params.page = p;
  $.post(WST.U('home/goodsappraises/userAppraise'),params,function(data,textStatus){
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
/**************** ็จๆทๆ่ฏ้กต้ข *****************/
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
          var btn = $('<div style="position:relative;top:-80px;left:60px;cursor:pointer;" ><img src="'+WST.conf.ROOT+'/wstmart/home/view/default/img/seller_icon_error.png"></div>');
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
          $('#uploadMsg').show().html(WST.lang('has_upload')+rate+"%");
      }
    });
}
function saveComplain(historyURL){
   /* ่กจๅ้ช่ฏ */
  $('#complainOrderForm').isValid(function(v){
		if(v){
              var params = WST.getParams('.ipt');
              var loading = WST.msg(WST.lang('submiting_tips'), {icon: 16,time:60000});
              var img = [];
              $('.complain_pic').each(function(){
                    img.push($(this).attr('v'));
              });
              params.complainAnnex = img.join(',');
              $.post(WST.U('home/orderComplains/saveComplain'),params,function(data,textStatus){
                    layer.close(loading);
                    var json = WST.toJson(data);
                    if(json.status=='1'){
                        WST.msg(WST.lang('complain_information_submit_tips'), {icon: 6},function(){
                         location.href = WST.U('home/ordercomplains/index');
                       });
                    }else{
                          WST.msg(json.msg,{icon:2});
                    }
              });
        }
  });
}

/*********************** ็จๆทๆ่ฏๅ่กจ้กต้ข ***************************/
function toView(id){
  location.href=WST.U('home/ordercomplains/getUserComplainDetail','id='+id+'&p='+WST_CURR_PAGE);
}
function complainByPage(p){
  $('#list').html('<img src="'+WST.conf.ROOT+'/wstmart/home/view/default/img/loading.gif">'+WST.lang('loading_tips'));
  var params = {};
  params = WST.getParams('.s-query');
  params.key = $.trim($('#key').val());
  params.page = p;
  $.post(WST.U('home/ordercomplains/queryUserComplainByPage'),params,function(data,textStatus){
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
//ๅฏผๅบ่ฎขๅ
function toExport(typeId,status,type){
	var params = {};
	params = WST.getParams('.u-query');
	params.typeId = typeId;
	params.orderStatus = status;
	params.type = type;
	var box = WST.confirm({content:WST.lang('order_confirm_export'),yes:function(){
		layer.close(box);
		location.href=WST.U('home/orders/toExport',params);
         }});
}

//่ฐ็จ่พ่ฎฏๅฐๅพ
function storeMap(){
	var latitude = parseFloat($("#latitude").val());
	var longitude = parseFloat($("#longitude").val());
	var storeName = $("#storeName").val();
	var center = new qq.maps.LatLng(latitude,longitude);
	//ๅฎไนmapๅ้ ่ฐ็จ qq.maps.Map() ๆ้?ๅฝๆฐ   ่ทๅๅฐๅพๆพ็คบๅฎนๅจ
	var map = new qq.maps.Map(document.getElementById("container"), {
		center: center,
		zoom:15
	});
	//ๆทปๅ?ๆๆฌๅคๆณจ
	var infoWin = new qq.maps.InfoWindow({
		map: map
	});
	infoWin.open();
	infoWin.setContent(storeName);
	infoWin.setPosition(map.getCenter());
}

