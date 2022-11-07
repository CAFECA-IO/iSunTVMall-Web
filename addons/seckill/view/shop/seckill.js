var mmg,mmg2,mmg3;
var secCanEdit = $("#secCanEdit").val();
//秒杀分页查询
function initGrid(p){
   var h = WST.pageHeight();
   var cols = [
       	{title:WST.lang('seckill_activity_title'), name:'title', width: 30},
       	{title:WST.lang('seckill_activity_status'), name:'goodsName', width: 300, width: 70,renderer:function(val,item,rowIndex){
          if(item['seckillStatus']==-2){
            return "<span class='lbel lbel-info' style='background:#da8223'>"+WST.lang('seckill_wait_submit_audit')+"</span>";
          }else{
            if(item['status']==1){
              return "<span class='lbel lbel-success'>"+WST.lang('seckill_curr_status_1')+"</span>";
            }else if(item['status']==0){
              return "<span class='lbel lbel-info'>"+WST.lang('seckill_curr_status_2')+"</span>";
            }else{
              return "<span class='lbel lbel-gray'>"+WST.lang('seckill_curr_status_3')+"</span>";
            }
          }
        	
        }},
       	{title:WST.lang('seckill_start_time'), name:'startDate', width: 90},
       	{title:WST.lang('seckill_end_time'), name:'endDate', width: 50},
        {title:WST.lang('seckill_tips1'), name:'isSale', width: 100, width: 70,renderer:function(val,item,rowIndex){
        	return "<input type='checkbox' "+((val==1)?"checked":"")+" name='isSale' lay-skin='switch' lay-filter='isSale' value='1' lay-text='"+WST.lang('seckill_tips2')+"' data='"+item["id"]+"'>"
        }},
        {title:WST.lang('seckill_audit_status'), name:'seckillStatus', width: 70,renderer:function(val,item,rowIndex){
        	if(item['seckillStatus']==0){
                return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('seckill_wait_audit')+"</span>";
            }else if(item['seckillStatus']==-2){
                return "<span class='statu-wait' style='color:#da8223'><i class='fa fa-clock-o'></i> "+WST.lang('seckill_wait_submit_audit')+"</span>";
            }else if(item['seckillStatus']==-1){
                return "<span class='statu-no' title='"+item['illegalRemarks']+"'><i class='fa fa-ban'></i> "+WST.lang('seckill_audit_not_pass')+"</span>";
            }else if(item['seckillStatus']==1){
               	return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('seckill_audit_pass')+"</span>";
            }
        }},
        {title:WST.lang('seckill_operation'), name:'' ,width:160,renderer:function(val,item,rowIndex){
        	var html = [];
	       	html.push("<a class='btn btn-blue' style='margin-bottom: 4px;' href='"+WST.AU("seckill://shop/seckillTimes","id="+item["id"])+"'><i class='fa fa-search'></i>"+WST.lang('seckill_tips3')+"</a> ");
          html.push('<div class="btn-group">');
          html.push('<button type="button" class="btn btn-default dropdown-toggle wst-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">');
          html.push('<i class="fa fa-pencil"></i>'+WST.lang('seckill_operation')+' <span class="caret"></span>');
          html.push('</button>');
          html.push('<ul class="dropdown-menu wst-dropdown-menu">');
          if(item["seckillStatus"]==-2){
              html.push('  <li><a href="javascript:submitAudit('+item["id"]+')"><i class="fa  fa-level-up"></i> '+WST.lang('seckill_tips4')+'</a></li>');
              html.push('  <li role="separator" class="divider"></li>');
          }
          html.push('  <li><a href="'+WST.AU("seckill://shop/edit","id="+item["id"])+'"><i class="fa fa-pencil"></i> '+WST.lang('seckill_edit')+'</a></li>');
          html.push('  <li><a href="javascript:del('+item["id"]+')"><i class="fa fa-trash-o"></i> '+WST.lang('seckill_del')+'</a></li>');
          html.push('</ul>');
          html.push('</div>');
	        return html.join('');
        }}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',
        url: WST.AU('seckill://shop/seckillPageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    mmg.on('loadSuccess',function(){
   		layui.form.render();
      	layui.form.on('switch(isSale)', function(data){
          	var id = $(this).attr("data");
          	if(this.checked){
          		toggleSet(id,1);
          	}else{
      			toggleSet(id,0);
          	}
      	});
    });
    loadGrid(p);
}

function loadGrid(p){
    var params = {};
    params = WST.getParams('.s-query');
    p=(p<=1)?1:p;
    params.page=p;
    mmg.load(params);
}

//秒杀上下架设置
function toggleSet(id,isSale){
    var loading = WST.msg(WST.lang('seckill_submitting'), {icon: 16,time:60000});
    $.post(WST.AU('seckill://shop/toggleSet'),{id:id,isSale:isSale},function(data,textStatus){
        layer.close(loading);
        var json = WST.toJson(data);
        if(json.status=='1'){
            WST.msg(json.msg,{icon:1});
        }else{
            WST.msg(json.msg,{icon:2});
        }
    });
}
//提交审核
function submitAudit(id){
  WST.confirm({content:WST.lang('seckill_tips5'), yes:function(tips){
    var loading = WST.msg(WST.lang('seckill_loading_data'), {icon: 16,time:60000});
      $.post(WST.AU('seckill://shop/submitAudit'),{id:id},function(data,textStatus){
          layer.close(loading);
          var json = WST.toJson(data);
          if(json.status==1){
              WST.msg(json.msg,{icon:1});
              loadGrid(0);
          }else{
              WST.msg(json.msg,{icon:2,time:2000});
          }
      });
  }});
}
//秒杀商品分页查询
function initGrid2(p){

   var h = WST.pageHeight();
   var cols = [
       	{title:WST.lang('seckill_goods_image'), name:'goodsName', width: 30,renderer:function(val,item,rowIndex){
           var html = [];
           html.push('<div class="goods-img">');
           html.push("<span class='weixin'><img class='img' style='height:40px;width:40px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'><img class='imged' style='height:200px;width:200px;max-width: 200px;max-height: 200px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></div>");
           return html.join('');
       	}},
       	{title:WST.lang('seckill_goods_name'), name:'goodsName', width: 240},
       	{title:WST.lang('seckill_buy_price'), name:'shopPrice', width: 80},
       	{title:WST.lang('seckill_tips6'), name:'secPrice', width: 80,renderer:function(val,item,rowIndex){
     		  if(secCanEdit==1){
            return '<input class="text w50" style="margin-right:0px;width:80px;" maxlength="10" onchange="goodsSet(this, \'secPrice\', '+item['id']+' );" onblur="WST.limitDecimal(this,2);" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)" autocomplete="off" value="'+item['secPrice']+'" type="text" data-shopPrice="'+item['shopPrice']+'"/><i id="secPrice_'+item['id']+'" class="fa fa-check-circle" style="display:none;color:#31c15a;"></i>';
          }else{
            return val;
          }
        }},
        {title:WST.lang('seckill_tips7')+' <input type="text" style="width:60px;margin:0;" onkeypress="return WST.isNumberKey(event)" maxlength="10" onkeyup="javascript:WST.isChinese(this,1)" onblur="batchSet(this,1)"/>', name:'secNum', width: 80,renderer:function(val,item,rowIndex){
     		  if(secCanEdit==1){
            return '<input class="secNum text w50" style="margin-right:0px;width:80px;" data-id="'+item['id']+'" maxlength="10" onchange="goodsSet(this, \'secNum\', '+item['id']+' );" onkeypress="return WST.isNumberKey(event)" onkeyup="javascript:WST.isChinese(this,1)" autocomplete="off" value="'+item['secNum']+'" type="text"/><i id="secNum_'+item['id']+'" class="fa fa-check-circle" style="display:none;color:#31c15a;"></i>';
          }else{
            return val;
          }
        }},
        {title:WST.lang('seckill_tips8')+' <input type="text" style="width:60px;margin:0;" onkeypress="return WST.isNumberKey(event)" maxlength="2" onkeyup="javascript:WST.isChinese(this,1)" onblur="batchSet(this,2)"/>', name:'secLimit', width: 80,renderer:function(val,item,rowIndex){
     		  if(secCanEdit==1){
            return '<input class="secLimit text w50" style="margin-right:0px;width:80px;" data-id="'+item['id']+'" maxlength="2" onchange="goodsSet(this, \'secLimit\', '+item['id']+' );" onkeypress="return WST.isNumberKey(event)" onkeyup="javascript:WST.isChinese(this,1)" autocomplete="off" value="'+item['secLimit']+'" type="text"/><i id="secLimit_'+item['id']+'" class="fa fa-check-circle" style="display:none;color:#31c15a;"></i>';
          }else{
            return val;
          }
        }},
        {title:WST.lang('seckill_operation'), name:'' ,width:80,renderer:function(val,item,rowIndex){
        	var html = [];
	       	html.push(" <a class='btn btn-red' href='javascript:delGoods("+item["id"]+")'><i class='fa fa-trash-o'></i>"+WST.lang('seckill_del')+"</a>");
	        return html.join('');
        }}
    ];

    mmg2 = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',
        url: WST.AU('seckill://shop/queryGoodsByPage'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg2').mmPaginator({})
        ]
    });
    loadGrid2(p);
}


function loadGrid2(p){
    var params = {};
    params = WST.getParams('.s-query');
    p=(p<=1)?1:p;
    params.page=p;
    mmg2.load(params);
}

function initGrid3(p){
   var h = WST.pageHeight();
   var seckillId = $("#seckillId").val();
   var cols = [
       	{title:WST.lang('seckill_time_name'), name:'title', width: 90},
       	{title:WST.lang('seckill_time_start'), name:'startTime', width: 50},
        {title:WST.lang('seckill_time_end'), name:'endTime', width: 100},
        {title:WST.lang('seckill_goods_number'), name:'gcnt', width: 100},
        {title:WST.lang('seckill_operation'), name:'' ,width:160,renderer:function(val,item,rowIndex){
        	var html = [];
	       	html.push("<a class='btn btn-blue' href='"+WST.AU("seckill://shop/setGoods",{"seckillId":seckillId,"timeId":item["id"]})+"'><i class='fa fa-plus'></i>"+WST.lang('seckill_tips3')+"</a>");
	        return html.join('');
        }}
    ];

    mmg3 = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',
        url: WST.AU('seckill://shop/seckillTimesPageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg3').mmPaginator({})
        ]
    });
    loadGrid3(p);
}

function loadGrid3(p){
    var params = {};
    params = WST.getParams('.s-query');
    p=(p<=1)?1:p;
    params.page=p;
    mmg3.load(params);
}

//秒杀待添加商品分页查询
function searchGoodsByPage(p){
	var params = WST.getParams(".s-query2");
		params.seckillId = $("#seckillId").val();
		params.timeId = $("#timeId").val();
		params.isCheck = $("input[name=isCheck]").prop('checked')?1:0;
		params.page = p;
	$("#list2").html('<div><img src="'+WST.conf.ROOT+'/wstmart/shop/view/default/img/loading.gif">'+WST.lang('seckill_loading_data')+'</div>');
    $.post(WST.AU('seckill://shop/searchGoodsByPage'),params,function(data,textStatus){
    	var json = WST.toJson(data);
	    if(json.status==1 && json.data){
	    	var gettpl = $('#tblist2').html();
	       	laytpl(gettpl).render(json.data, function(html){
	       		$('#list2').html(html);
	       		$('.j-lazyGoodsImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.ROOT+'/'+window.conf.GOODS_LOGO});//商品默认图片
	       	});
	       	if(json.last_page>1){
	            laypage({

	               cont: 'pager2', 
	               pages:json.last_page, 
	               curr: json.current_page,
	               groups: 3,
	               jump: function(e, first){
	                  if(!first){
	                    searchGoodsByPage(e.curr);
	                  }
	                } 
	            });
	      	}else{
	            $('#pager2').empty();
	     	}
	    }
	});
}
//秒杀添加/下架商品
function checkGoods(obj,goodsId){
	var params = {};
		params.seckillId = $("#seckillId").val();
		params.timeId = $("#timeId").val();
		params.goodsId = goodsId;
	$.post(WST.AU('seckill://shop/checkGoods'),params,function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status==1){
			if($(obj).hasClass("on")){
				$(obj).removeClass("on").html('<i class="fa fa-plus"></i>'+WST.lang('seckill_select'));
			}else{
				$(obj).addClass("on").html('<i class="fa fa-check"></i>'+WST.lang('seckill_has_select'));
			}
		}
	});
}
//打开商品查询弹窗
function openSearch(){
	$("#list2").html('<div><img src="'+WST.conf.ROOT+'/wstmart/shop/view/default/img/loading.gif">'+WST.lang('seckill_loading_data')+'</div>');
	var seckillId = $("#seckillId").val();
  var timeId = $("#timeId").val();
  var box = parent.showBox({
	    type: 2,
	    title: WST.lang('seckill_add_goods'),
	    shade: 0.5,
	    scrollbar :false,
	    area: ['1000px', '700px'],
	    content: WST.AU('seckill://shop/toSecGoods',{"seckillId":seckillId,"timeId":timeId}),
	    btn: [WST.lang('seckill_confirm_and_close')],
	    success: function(layero, index){
		  },
      end: function(index, layero){
      	$("#cat1").val(0);
      	$("#cat2").html('<option value="">'+WST.lang('seckill_please_select')+'</option>');
      	$("#goodsName").val("");
      	$("input[name=isCheck]").prop('checked',false);
      	loadGrid2(0);
      	layui.form.render();
      }
	});
}
//查询店铺商品分类
function getCat(val){
  	if(val==''){
  		$('#cat2').html("<option value='' >"+WST.lang('seckill_please_select')+"</option>");
  		return;
  	}
  	$.post(WST.U('shop/shopcats/listQuery'),{parentId:val},function(data,textStatus){
       	var json = WST.toJson(data);
       	var html = [],cat;
       	html.push("<option value='' >"+WST.lang('seckill_please_select')+"</option>");
       	if(json.status==1 && json.list){
	        json = json.list;
	       	for(var i=0;i<json.length;i++){
	           	cat = json[i];
	           	html.push("<option value='"+cat.catId+"'>"+cat.catName+"</option>");
	        }
       	}
       	$('#cat2').html(html.join(''));
  	});
}
//删除秒杀商品
function delGoods(id){
	WST.confirm({content:WST.lang('seckill_confirm_del'), yes:function(tips){
		var loading = WST.msg(WST.lang('seckill_submitting'), {icon: 16,time:60000});
	    $.post(WST.AU('seckill://shop/delGoods'),{id:id},function(data,textStatus){
	        layer.close(loading);
	        var json = WST.toJson(data);
	        if(json.status==1){
	            WST.msg(json.msg,{icon:1});
	            loadGrid2(0);
	        }else{
	            WST.msg(json.msg,{icon:2});
	        }

	    });
	}});
}
//保存秒杀活动
function save(){
    $('#seckillform').isValid(function(v){
		if(v){
			var params = WST.getParams('.ipt');
			var loading = WST.load({msg:WST.lang('seckill_submitting')});
			$.post(WST.AU("seckill://shop/toEdit"),params,function(data,textStatus){
				layer.close(loading);
			    var json = WST.toJson(data);
			    if(json.status==1){
		            WST.msg(json.msg,{icon:1},function(){
		            	location.href = WST.AU('seckill://shop/index');
		            });
			    }else{
			    	WST.msg(json.msg,{icon:2});
			    }
			});
		}
	});
}

//秒杀商品设置
function goodsSet(obj,iname,id){
	var ival = 0;
	if(iname=='secPrice'){
		var shopPrice = parseFloat($(obj).data("shopPrice"),10);
		var ival = parseFloat($(obj).val(),10);
		if(ival>=shopPrice){
			WST.msg(WST.lang('seckill_price_tips'),{icon:2});
			return;
		}
	}else{
		ival = parseInt($(obj).val(),10);
	}
    //var loading = WST.msg(WST.lang('seckill_submitting'), {icon: 16,time:60000});
    $.post(WST.AU('seckill://shop/goodsSet'),{id:id,iname:iname,ival:ival},function(data,textStatus){
        //layer.close(loading);
        var json = WST.toJson(data);
        if(json.status==1){
          $("#"+iname+"_"+id).show();
          setTimeout(function(){
            $("#"+iname+"_"+id).hide();
          },1500)
        }else{
          WST.msg(json.msg,{icon:2});
        }
    });
}


function batchSet(obj,type){
  var ival = $(obj).val();
  if(ival !='' ){
    var ival = parseInt($(obj).val(),10);
    var iname = "";
    var ids = [];
    if(type==1){
      $(".secNum").val(ival);
      iname = "secNum";
    }else if(type==2){
      $(".secLimit").val(ival);
      iname = "secLimit";
    }
    $("."+iname).each(function(){
      ids.push($(this).data("id"));
    });
    var seckillId = $("#seckillId").val();
    $.post(WST.AU('seckill://shop/goodsSet'),{seckillId:seckillId,ids:ids,iname:iname,ival:ival,isBatch:1},function(data,textStatus){
        var json = WST.toJson(data);
        if(json.status==1){
            $("i[id^='"+iname+"_']").show();
            setTimeout(function(){
               $("i[id^='"+iname+"_']").hide();
            },1500)
        }else{
          WST.msg(json.msg,{icon:2});
        }
    });
  }
}

//删除秒杀活动
function del(id){
	WST.confirm({content:WST.lang('seckill_confirm_del'),yes:function(){
   		var loading = WST.load({msg:WST.lang('seckill_submitting')});
		$.post(WST.AU("seckill://shop/del"),{id:id},function(data,textStatus){
			layer.close(loading);
		    var json = WST.toJson(data);
			if(json.status==1){
			    WST.msg(json.msg,{icon:1},function(){
			        loadGrid(0);
			    });
		    }else{
				WST.msg(json.msg,{icon:2});
			}
		});
    }});
}



