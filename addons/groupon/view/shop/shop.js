var mmg;
function initGrid(p){
   var h = WST.pageHeight();
   var cols = [
       {title:WST.lang('group_goods_image'), name:'goodsName', width: 30,renderer:function(val,item,rowIndex){
               var html = [];
               html.push('<div class="goods-img"><a href="'+WST.AU("groupon://goods/detail","id="+item["grouponId"])+'" target="_blank">');
               html.push("<span class='weixin'><img class='img' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'><img class='imged' style='height:200px;width:200px;max-width: 200px;max-height: 200px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></a></div>");
               return html.join('');
           }},
        {title:WST.lang('group_goods_name'), name:'goodsName', width: 300,renderer:function(val,item,rowIndex){
        	if(item['grouponStatus']==1){
                 return "<a style='color:blue' href='"+WST.AU("groupon://goods/detail","id="+item["grouponId"])+"' target='_blank'>"+val+"</a>";
        	}else{
                 return val;
        	}
        }},
        {title:WST.lang('group_goods_product'), name:'goodsSn', width: 90},
        {title:WST.lang('group_buy_price'), name:'grouponPrice', width: 50,renderer:function(val,item,rowIndex){
        	return WST.lang('currency_symbol')+item['grouponPrice'];
        }},
        {title:WST.lang('group_start_time'), name:'startTime', width: 100},
        {title:WST.lang('group_end_time'), name:'endTime', width: 100},
        {title:WST.lang('group_number'), name:'grouponNum', width: 30},
        {title:WST.lang('group_has_group_number'), name:'orderNum', width: 30,renderer:function(val,item,rowIndex){
            return "<a style='color:blue' href='"+WST.AU("groupon://shops/orders","grouponId="+item['grouponId'])+"'>"+item['orderNum']+"</a>";
        }},
        {title:WST.lang('group_status'), name:'grouponStatus', width: 70,renderer:function(val,item,rowIndex){
        	if(item['grouponStatus']==0){
                return "<span class='statu-wait'><i class='fa fa-clock-o'></i>"+WST.lang('group_wait_audit')+"</span>";
            }else if(item['grouponStatus']==-1){
                return "<span class='statu-no' title='"+item['illegalRemarks']+"'><i class='fa fa-ban'></i>"+WST.lang('group_audit_fail')+"</span>";
            }else{
               if(item['status']==0){
                   return "<span class='lbel lbel-info'>"+WST.lang('group_status_2')+"</span>";
               }else if(item['status']==1){
                   return "<span class='lbel lbel-success'>"+WST.lang('group_status_1')+"</span>";
               }else{
                   return "<span class='lbel lbel-gray'>"+WST.lang('group_status_3')+"</span>";
               }
            }
        }},
        {title:WST.lang('group_operation'), name:'' ,width:200,renderer:function(val,item,rowIndex){
        	var html = [];
            if(item['grouponStatus']==1){
	           html.push();
	        }
	        html.push(" <a class='btn btn-blue' href='javascript:toEdit("+item["grouponId"]+")'><i class='fa fa-pencil'></i>"+WST.lang('group_edit')+"</a>");
			if(item['isSale']>0){
				html.push(" <a class='btn btn-red' href='javascript:changeSale(" + item['grouponId'] + ",0)'><i class='fa fa-ban'></i>"+WST.lang('group_unsale')+"</a>");
			}else{
				html.push(" <a class='btn btn-blue' href='javascript:changeSale(" + item['grouponId'] + ",1)'><i class='fa fa-check'></i>"+WST.lang('group_onsale')+"</a>");
			}
	        html.push(" <a class='btn btn-red' href='javascript:del("+item["grouponId"]+")'><i class='fa fa-trash-o'></i>"+WST.lang('group_del')+"</a>");
	        return html.join('');
        }}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',
        url: WST.AU('groupon://shops/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}

function loadGrid(p){
    var params = {};
    params = WST.getParams('.s-query');
    params.key = $.trim($('#key').val());
    p=(p<=1)?1:p;
    params.page=p;
    mmg.load(params);
}

function getShopsCats(objId,pVal,objVal){
	$('#'+objId).empty();
	$.post(WST.U('shop/shopcats/listQuery'),{parentId:pVal},function(data,textStatus){
	     var json = WST.toJson(data);
	     var html = [],cat;
	     html.push("<option value='' >"+WST.lang('group_please_select')+"</option>");
	     if(json.status==1 && json.list){
	    	 json = json.list;
			 for(var i=0;i<json.length;i++){
			     cat = json[i];
			     html.push("<option value='"+cat.catId+"' "+((objVal==cat.catId)?"selected":"")+">"+cat.catName+"</option>");
			 }
	     }
	     $('#'+objId).html(html.join(''));
	});
}
var sgoods = [];
function searchGoods(){
	var params = {};
	params.shopCatId1 = $('#shopCatId1').val();
	params.shopCatId2 = $('#shopCatId2').val();
    params.goodsName = $('#sgoodsName').val();
    if(params.shopCatId1=='' && params.goodsName==''){
		 WST.msg(WST.lang('group_select_goods_cat_tips'),{icon:2});
		 return;
	}
	$('#goodsId').empty();
    var loading = WST.load({msg:WST.lang('group_searching')});
	$.post(WST.AU("groupon://shops/searchGoods"),params,function(data,textStatus){
		layer.close(loading);
	    var json = WST.toJson(data);
	    if(json.status==1 && json.data){
	    	var html = [];
	    	var option1 = null;
            sgoods = json.data;
	    	for(var i=0;i<json.data.length;i++){
	    		if(i==0)option1 = json.data[i];
                html.push('<option value="'+json.data[i].goodsId+'">'+json.data[i].goodsName+'</option>');
	    	}
	    	$('#goodsId').html(html.join(''));
	    	var n = 0;
			for(var i in WST.conf.sysLangs){
				n = WST.conf.sysLangs[i]['id'];
				$('#langParams'+n+'goodsName').val(option1.goodsName);
				$('#langParams'+n+'goodsSeoDesc').val(option1.goodsSeoDesc);
				$('#langParams'+n+'goodsSeoKeywords').val(option1.goodsSeoKeywords);
			}
	    	$('#marketPrice').html(WST.lang('currency_symbol')+option1.marketPrice);
			$('#goodsImg').val(option1.goodsImg);
			$('#preview').attr('src',WST.conf.RESOURCE_PATH+"/"+option1.goodsImg);
	    }
	});
}
function changeGoods(obj){
    var option1 = null
	for(var i=0;i<sgoods.length;i++){
		if(obj.value==sgoods[i].goodsId)option1 = sgoods[i];
	}
	var n = 0;
	for(var i in WST.conf.sysLangs){
		n = WST.conf.sysLangs[i]['id'];
		$('#langParams'+n+'goodsName').val(option1.goodsName);
		$('#langParams'+n+'goodsSeoDesc').val(option1.goodsSeoDesc);
		$('#langParams'+n+'goodsSeoKeywords').val(option1.goodsSeoKeywords);
	}
	$('#marketPrice').html(WST.lang('currency_symbol')+option1.marketPrice);
	$('#goodsImg').val(option1.goodsImg);
	$('#preview').attr('src',WST.conf.RESOURCE_PATH+"/"+option1.goodsImg);
}
function toEdit(id){
    location.href = WST.AU('groupon://shops/edit','id='+id+'&p='+WST_CURR_PAGE);
}
function toView(id){
	location.href = WST.AU('groupon://goods/detail','id='+id);
}

function save(p){
    $('#grouponform').isValid(function(v){
		if(v){
			var params = WST.getParams('.ipt');
			if(params.goodsId==''){
				WST.msg(WST.lang('group_select_want_buy_group'),{icon:2});
				return;
			}
			var loading = WST.load({msg:WST.lang('group_submitting')});
			$.post(WST.AU("groupon://shops/toEdit"),params,function(data,textStatus){
				layer.close(loading);
			    var json = WST.toJson(data);
			    if(json.status==1){
		            WST.msg(json.msg,{icon:1},function(){
		            	location.href = WST.AU('groupon://shops/groupon','p='+p);
		            });
			    }else{
			    	WST.msg(json.msg,{icon:2});
			    }
			});
		}
	});
}
function del(id){
	var loading = WST.load({msg:WST.lang('group_submitting')});
	$.post(WST.AU("groupon://shops/del"),{id:id},function(data,textStatus){
		layer.close(loading);
	    var json = WST.toJson(data);
		if(json.status==1){
		    WST.msg(json.msg,{icon:1},function(){
		        loadGrid(WST_CURR_PAGE);
		    });
	    }else{
			WST.msg(json.msg,{icon:2});
		}
	});
}
function listByPage(p){
	$('#loading').show();
	var params = {};
	params = WST.getParams('.s-ipt');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(WST.AU('groupon://shops/pageQueryByGoods'),params,function(data,textStatus){
		$('#loading').hide();
	    var json = WST.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1){
	    	json = json.data;
	       	var gettpl = document.getElementById('tblist').innerHTML;
	       	laytpl(gettpl).render(json.data, function(html){
	       		$(html).insertAfter('#loadingBdy');
	       		$('.gImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:WST.conf.RESOURCE_PATH+'/'+WST.conf.GOODS_LOGO});
	       	});
	       	if(json.last_page>1){
	       		laypage({
		        	 cont: 'pager',
		        	 pages:json.last_page,
		        	 curr: json.current_page,
		        	 skin: '#e23e3d',
		        	 groups: 3,
		        	 jump: function(e, first){
		        		 if(!first){
		        			 listByPage(e.curr);
		        		 }
		        	 }
		        });
	       	}else{

	       		$('#pager').empty();
	       	}
       	}
	});
}
function view(id){
    location.href=WST.U('shop/orders/view','id='+id);
}

function changeSale(id,type){
	$.post(WST.AU('groupon://shops/changeSale'),{id:id,type:type},function(data){
		var json = WST.toJson(data);
		if(json.status>0){
			WST.msg(json.msg, {icon: 1});
			loadGrid(WST_CURR_PAGE);
		}else{
			WST.msg(json.msg, {icon: 2});
		}
	});
}
