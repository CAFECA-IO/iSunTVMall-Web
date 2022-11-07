var mmg;
function initGrid(p){
   var h = WST.pageHeight();
   var cols = [
        {title:WST.lang('presale_goods_img'), name:'goodsImg', width: 30,renderer:function(val,item,rowIndex){
        	var html = [];
            html.push('<div class="goods-img">');
            html.push("<span class='weixin'><img class='img' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'><img class='imged' style='height:200px;width:200px;max-width: 200px;max-height: 200px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></div>");
            return html.join('');
        }},
        {title:WST.lang('presale_goods'), name:'goodsName', width: 300,renderer:function(val,item,rowIndex){
            	return "<div style='line-height:20px;'><a style='color:blue;' target='_blank' href='"+WST.AU("presale://goods/detail","id="+item['id']+"&key="+item['verfiycode'])+"'>"+val+"</a></div>";
        }},
        {title:WST.lang('presale_sale_type'), name:'saleType', width: 60,renderer:function(val,item,rowIndex){
        	return (item['saleType']==0)?WST.lang('presale_sale_type1'):WST.lang('presale_sale_type2');
        }},
        {title:WST.lang('presale_time'), name:'startTime', width: 240,renderer:function(val,item,rowIndex){
        	return item['startTime']+' '+WST.lang('presale_to')+' '+item['endTime'];
        }},
        {title:WST.lang('presale_goods_number'), name:'goodsNum', width: 80},
        {title:WST.lang('presale_number'), name:'orderNum', width: 80,renderer:function(val,item,rowIndex){
            return "<a style='color:blue' href='"+WST.AU("presale://shops/orders","presaleId="+item['id'])+"'>"+item['orderNum']+"</a>";
        }},
        {title:WST.lang('presale_order_status'), name:'combineStatus', width: 70,renderer:function(val,item,rowIndex){
        	if(item['presaleStatus']==1){
                if(item['status']==0){
			        return "<span class='statu-wait'><i class='fa fa-clock-o'>"+WST.lang('presale_status_type2')+"</span>";
			    }else if(item['status']==1){
			    	if(item['isSale']>0){
			            return "<span class='lbel lbel-success'>"+WST.lang('presale_status_type1')+"</span>";
			        }else{
                        return "<span class='statu-no'><i class='fa fa-clock-o'>"+WST.lang('presale_pause')+"</span>";
			        }
			    }else{
			        return "<span class='lbel lbel-gray'>"+WST.lang('presale_status_type3')+"</span>";
			    }
        	}else if(item['presaleStatus']==0){
			    return "<span class='statu-wait'><i class='fa fa-ban'></i>"+WST.lang('presale_status_type6')+"</span>";
			}else if(item['presaleStatus']==-1){
			    return "<span class='statu-no'><i class='fa fa-ban'></i>"+WST.lang('presale_status_type8')+"</span>";
			}
        }},
        {title:WST.lang('presale_operation'), name:'' ,width:200,renderer:function(val,item,rowIndex){
        	var html = [];
	        if(item['presaleStatus']!=1 || (item['presaleStatus']==1 && item['status']!=1)){
	        	html.push(" <a class='btn btn-blue' href='javascript:toEdit("+item["id"]+")'><i class='fa fa-pencil'></i>"+WST.lang('presale_edit')+"</a>");
	            html.push(" <a class='btn btn-blue' href='javascript:del("+item["id"]+")'><i class='fa fa-trash-o'></i>"+WST.lang('presale_del')+"</a>");
	        }
	        if(item['presaleStatus']==1 && item['status']!=-1){
		        if(item['isSale']>0){
					html.push(" <a class='btn btn-red' href='javascript:changeSale(" + item['id'] + ",0)'><i class='fa fa-ban'></i>"+WST.lang('presale_pause')+"</a>");
				}else{
					html.push(" <a class='btn btn-blue' href='javascript:changeSale(" + item['id'] + ",1)'><i class='fa fa-check'></i>"+WST.lang('presale_continue')+"</a>");
				}
			}
	        return html.join("");
        }}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',
        url: WST.AU('presale://shops/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}

function changeSale(id,type){
	$.post(WST.AU('presale://shops/changeSale'),{id:id,type:type},function(data){
		var json = WST.toJson(data);
		if(json.status>0){
			WST.msg(json.msg, {icon: 1});
			loadGrid(WST_CURR_PAGE);
		}else{
			WST.msg(json.msg, {icon: 2});
		}
	});
}

function loadGrid(p){
	p=(p<=1)?1:p;
    var params = {};
    params = WST.getParams('.s-ipt');
    params.goodsName = $.trim($('#goodsName').val());
    params.page=p;
    mmg.load(params);
}
function getShopsCats(objId,pVal,objVal){
	$('#'+objId).empty();
	$.post(WST.U('shop/shopcats/listQuery'),{parentId:pVal},function(data,textStatus){
	     var json = WST.toJson(data);
	     var html = [],cat;
	     html.push("<option value='' >-"+WST.lang('presale_please_select')+"-</option>");
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
		 WST.msg(WST.lang('presale_at_least_select_goods_cat'),{icon:2});
		 return;
	}
	$('#goodsId').empty();
    var loading = WST.load({msg:WST.lang('presale_searching')});
	$.post(WST.AU("presale://shops/searchGoods"),params,function(data,textStatus){
		layer.close(loading);
	    var json = WST.toJson(data);
	    if(json.status==1 && json.data){
	    	var html = [];
            sgoods = json.data;
	    	for(var i=0;i<json.data.length;i++){
	    		if(i==0)currGoods = json.data[i];
                html.push('<option value="'+json.data[i].goodsId+'">'+json.data[i].goodsName+'</option>');
	    	}
	    	$('#goodsId').html(html.join(''));
	    	var n = 0;
			for(var i in WST.conf.sysLangs){
				n = WST.conf.sysLangs[i]['id'];
				$('#langParams'+n+'goodsName').val(currGoods.goodsName);
				$('#langParams'+n+'goodsTips').val(currGoods.goodsTips);
				$('#langParams'+n+'goodsSeoDesc').val(currGoods.goodsSeoDesc);
				$('#langParams'+n+'goodsSeoKeywords').val(currGoods.goodsSeoKeywords);
			}
	    	$('#preview').attr('src',WST.conf.RESOURCE_PATH+"/"+currGoods.goodsImg);
	        $('#goodsImg').val(currGoods.goodsImg);
	        if(currGoods.isSpec==1){
		        $('#shopPrice').html(WST.lang('currency_symbol')+currGoods.minShopPrice+'~'+WST.lang('currency_symbol')+currGoods.maxShopPrice);
			}else{
		        $('#shopPrice').html(WST.lang('currency_symbol')+currGoods.shopPrice);
			}
	    	$('#marketPrice').html(WST.lang('currency_symbol')+currGoods.marketPrice);
	    	realShopPrice($('#reduceMoney').val());
	    }
	});
}
function changeGoods(obj){
	for(var i=0;i<sgoods.length;i++){
		if(obj.value==sgoods[i].goodsId)currGoods = sgoods[i];
	}
	var n = 0;
	for(var i in WST.conf.sysLangs){
		n = WST.conf.sysLangs[i]['id'];
		$('#langParams'+n+'goodsName').val(currGoods.goodsName);
		$('#langParams'+n+'goodsTips').val(currGoods.goodsTips);
		$('#langParams'+n+'goodsSeoDesc').val(currGoods.goodsSeoDesc);
		$('#langParams'+n+'goodsSeoKeywords').val(currGoods.goodsSeoKeywords);
	}
	$('#preview').attr('src',WST.conf.RESOURCE_PATH+"/"+currGoods.goodsImg);
	$('#goodsImg').val(currGoods.goodsImg);
	if(currGoods.isSpec==1){
        $('#shopPrice').html(WST.lang('currency_symbol')+currGoods.minShopPrice+'~'+WST.lang('currency_symbol')+currGoods.maxShopPrice);
	}else{
        $('#shopPrice').html(WST.lang('currency_symbol')+currGoods.shopPrice);
	}
	realShopPrice($('#reduceMoney').val());
}
function realShopPrice(v){
	if($('#goodsId').val()!=0){
	    v = parseFloat(v,10);
	    var shopPrice = (currGoods.isSpec==1)?currGoods.minShopPrice:currGoods.shopPrice;
	    console.log(shopPrice);
	    console.log(v);
	    if((shopPrice-v).toFixed(2)<=0){
	    	WST.msg(WST.lang('presale_reduce_money_must_less_goods_price'),{icon:2});
	    	$('#reduceMoney').val(0);
		    $('#realShopPrice').html('0');
	    }else{
		    if(currGoods.isSpec==1){
		        $('#realShopPrice').html(''+WST.lang('currency_symbol')+(shopPrice-v).toFixed(2)+'~'+WST.lang('currency_symbol')+(currGoods.maxShopPrice-v).toFixed(2));
		    }else{
		    	 $('#realShopPrice').html(''+WST.lang('currency_symbol')+(shopPrice-v).toFixed(2));
		    }
		}
	}else{
		$('#reduceMoney').val(0);
		$('#realShopPrice').html('0');
	}
}
function toEdit(id){
    location.href = WST.AU('presale://shops/edit','id='+id+'&p='+WST_CURR_PAGE);
}

function del(id){
	var box = WST.confirm({content:WST.lang("presale_confirm_delete_goods"),yes:function(){
		layer.close(box);
		var loading = WST.load({msg:WST.lang('presale_submitting')});
		$.post(WST.AU("presale://shops/del"),{id:id},function(data,textStatus){
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
	}});
}
function initForm(){
	$('#mmgridStyle').remove();
	var laydate = layui.laydate;
    laydate.render({
        elem: '#startTime',
        type: 'datetime'
    });
    laydate.render({
        elem: '#endTime',
        type: 'datetime'
    });
    WST.upload({
	  	  pick:'#goodsImgPicker',
	  	  formData: {dir:'presale',isWatermark:1,isThumb:1},
	  	  accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
	  	  callback:function(f){
	  		  var json = WST.toJson(f);
	  		  if(json.status==1){
	  			  $('#uploadMsg').empty().hide();
	              $('#preview').attr('src',WST.conf.RESOURCE_PATH+"/"+json.savePath+json.thumb);
	              $('#goodsImg').val(json.savePath+json.name);
	              $('#msg_goodsImg').hide();
	  		  }
		  },
		  progress:function(rate){
		      $('#uploadMsg').show().html(WST.lang('presale_has_upload')+rate+"%");
		  }
	});
	var form = layui.form;
	form.on('radio(saleType)', function(data){
	  if($(this).val()==1){
	  	$(".depositBox").show();
	    $('.ndepositBox').hide();
	  }else{
	  	$(".depositBox").hide();
	  	$('.ndepositBox').show();
	  }
	});
	form.on('radio(depositType)', function(data){
	  if($(this).val()==1){
	  	$("#depositMoney").hide();
	  	$("#depositRateBox").show();
	  }else{
	  	$("#depositMoney").show();
	  	$("#depositRateBox").hide();
	  }
	});
}

function save(p){
    $('#editform').isValid(function(v){
		if(v){
			var params = WST.getParams('.ipt');
			if(params.goodsId==''){
				WST.msg(WST.lang('presale_select_goods'),{icon:2});
				return;
			}
			var loading = WST.load({msg:WST.lang('presale_submitting')});
			$.post(WST.AU("presale://shops/toEdit"),params,function(data,textStatus){
				layer.close(loading);
			    var json = WST.toJson(data);
			    if(json.status==1){
		            WST.msg(json.msg,{icon:1},function(){
		            	location.href = WST.AU('presale://shops/index','p='+p);
		            });
			    }else{
			    	WST.msg(json.msg,{icon:2});
			    }
			});
		}
	});
}



function pageQueryOrders(p){
	$('#list').html('<tr><td colspan="11"><img src="'+WST.conf.ROOT+'/wstmart/home/view/default/img/loading.gif">'+WST.lang('presale_loading_data')+'</td></tr>');
	var params = WST.getParams('.u-query');
	params.page = p;
	$.post(WST.AU('presale://shops/pageQueryOrders'),params,function(data,textStatus){
	    $('#loading').hide();
	    var json = WST.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1){
	    	if(params.page>json.last_page && json.last_page >0){
               pageQueryOrders(json.last_page);
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
		        			 pageQueryOrders(e.curr);
		        		 }
		        	 }
		    });
       	}
	});
}

//去支付
function toView(porderId){
    location.href=WST.AU('presale://shops/toView',{'porderId':porderId});
}
