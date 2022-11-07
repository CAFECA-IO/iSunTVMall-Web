var mmg;
function initGrid(p){
   var h = WST.pageHeight();
   var cols = [
        {title:WST.lang('combination_main_image'), name:'combineImg', width: 30,renderer:function(val,item,rowIndex){
        	var html = [];
            html.push('<div class="goods-img">');
            html.push("<span class='weixin'><img class='img' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['combineImgThumb']+"'><img class='imged' style='height:200px;width:200px;max-width: 200px;max-height: 200px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['combineImg']+"'></span></div>");
            return html.join('');
        }},
        {title:WST.lang('combination_combine_name'), name:'combineName', width: 300},
        {title:WST.lang('combination_combine_type'), name:'combineType', width: 60,renderer:function(val,item,rowIndex){
        	return (item['combineType']==0)?WST.lang('combination_combine_type_1'):WST.lang('combination_combine_type_2');
        }},
        {title:WST.lang('combination_activity_time'), name:'startTime', width: 240,renderer:function(val,item,rowIndex){
        	return item['startTime']+' '+WST.lang('combination_to_title')+' '+item['endTime'];
        }},
        {title:WST.lang('combination_package_status'), name:'combineStatus', width: 70,renderer:function(val,item,rowIndex){
        	if(item['combineStatus']==1){
                if(item['status']==0){
			        return "<span class='statu-wait'><i class='fa fa-clock-o'>"+WST.lang('combination_package_status_1')+"</span>";
			    }else if(item['status']==1){
			        return "<span class='lbel lbel-success'>"+WST.lang('combination_package_status_2')+"</span>";
			    }else{
			        return "<span class='lbel lbel-gray'>"+WST.lang('combination_package_status_3')+"</span>";
			    }
        	}else{
			    return "<span class='statu-wait'><i class='fa fa-ban'></i>"+WST.lang('combination_package_status_4')+"</span>";
			}
        }},
        {title:WST.lang('combination_operation'), name:'' ,width:200,renderer:function(val,item,rowIndex){
        	var html = [];
	        html.push(" <a class='btn btn-blue' href='javascript:toEdit("+item["combineId"]+")'><i class='fa fa-pencil'></i>"+WST.lang('combination_edit')+"</a>");
			if(item['combineStatus']==1){
				html.push(" <a class='btn btn-red' href='javascript:changeStatus(" + item['combineId'] + ",0)'><i class='fa fa-ban'></i>"+WST.lang('combination_package_status_4')+"</a>");
			}else{
				html.push(" <a class='btn btn-blue' href='javascript:changeStatus(" + item['combineId'] + ",1)'><i class='fa fa-check'></i>"+WST.lang('combination_package_status_5')+"</a>");
			}
	        html.push(" <a class='btn btn-blue' href='javascript:del("+item["combineId"]+")'><i class='fa fa-trash-o'></i>"+WST.lang('combination_del')+"</a>");
	        return html.join("");
        }}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',
        url: WST.AU('combination://shops/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}

function loadGrid(p){
	p=(p<=1)?1:p;
    var params = {};
    params = WST.getParams('.s-ipt');
    params.combineName = $.trim($('#combineName').val());
    params.page=p;
    mmg.load(params);
}
function toEdit(id){
    location.href = WST.AU('combination://shops/edit','id='+id+'&p='+WST_CURR_PAGE);
}

function del(id){
	var box = WST.confirm({content:WST.lang('combination_confirm_del'),yes:function(){
		layer.close(box);
		var loading = WST.load({msg:WST.lang('combination_submitting')});
		$.post(WST.AU("combination://shops/del"),{id:id},function(data,textStatus){
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
	  	  pick:'#combineImgPicker',
	  	  formData: {dir:'combination',isWatermark:1,isThumb:1},
	  	  accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
	  	  callback:function(f){
	  		  var json = WST.toJson(f);
	  		  if(json.status==1){
	  			  $('#uploadMsg').empty().hide();
	              $('#preview').attr('src',WST.conf.RESOURCE_PATH+"/"+json.savePath+json.thumb);
	              $('#combineImg').val(json.savePath+json.name);
	              $('#msg_combineImg').hide();
	  		  }
		  },
		  progress:function(rate){
		      $('#uploadMsg').show().html(WST.lang('combination_has_upload')+rate+"%");
		  }
	});
	if(info.combineId>0){
		createGoodsTable();
	}
}

function initGoodsGrid(goodsType){
	$('#windowType').val(goodsType);
    var cols = [
        {title:WST.lang('combination_goods_image'), name:'goodsImg', width: 30,renderer:function(val,item,rowIndex){
        	var goodsIds = [$('#goodsId').val(),$('#combineGoodsIds').val()];
		    goodsIds = $.grep(goodsIds,function(n,i){
				return n;
		    },false);
		    goodsIds = (goodsIds.length==0)?[]:goodsIds.join(',').split(',');
		    var h = '';
        	if($.inArray(item['goodsId'].toString(),goodsIds)==-1){
	        	if($('#windowType').val()==1){
	        		h+='<input type="radio" name="goods" value="'+item['goodsId']+'"/>&nbsp;';
	        	}else{
	        		h+='<input type="checkbox" class="chkgoods" value="'+item['goodsId']+'"/>&nbsp;';
	        	}
	        }else{
	        	h+='&nbsp;&nbsp;&nbsp;';
	        }
            h+= "<img class='img' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImgThumb']+"'>";
            return h;
        }},
        {title:WST.lang('combination_goods_name'), name:'goodsName', width: 300},
        {title:WST.lang('combination_price'), name:'shopPrice', width: 60,renderer:function(val,item,rowIndex){
        	return WST.lang('currency_symbol')+val;
        }},
        {title:WST.lang('combination_stock'), name:'goodsStock', width: 40}
    ];

    mmg = $('.mmg').mmGrid({height: 305,indexCol: true, cols: cols,method:'POST',
        url: WST.AU('combination://shops/saleGoodsPageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGoodsGrid();
}

function getCat(val){
  if(val==''){
  	$('#cat2').html("<option value='' >"+WST.lang('combination_please_select')+"</option>");
  	return;
  }
  $.post(WST.U('shop/shopcats/listQuery'),{parentId:val},function(data,textStatus){
       var json = WST.toJson(data);
       var html = [],cat;
       html.push("<option value='' >"+WST.lang('combination_please_select')+"</option>");
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

function loadGoodsGrid(p){
	p=(p<=1)?1:p;
    var params = {};
    params = WST.getParams('.s-query');
    params.goodsName = $.trim($('#goodsName').val());
    params.page=p;
    mmg.load(params);

}

function goodsWindow(goodsType){
	var goodsIds = $('#goodsIds').val();
	var layerMapIndex = WST.open({
			type:1,
			area:['800px','515px'],
			offset:'auto',
			title:(goodsType==1)?WST.lang('combination_require_main_goods'):WST.lang('combination_require_combine_goods'),
			content:$('#saleGoodsBox'),
			btn:[WST.lang('combination_confirm'),WST.lang('combination_cancel')],
			end:function(index, layero){
               $('#saleGoodsBox').hide();
			},
			success:function(index, layero){
				initGoodsGrid(goodsType);
			},
			yes: function(index, layero){
				if($('.reduce1')[0]){
					var dgoodsId = $('.reduce1').attr('dataid');
					info['list'][dgoodsId] = {goodsId:dgoodsId,reduceMoney:$('.reduce1').val()}
				}
				$('.reduce0').each(function(){
                    info['list'][$(this).attr('dataid')] = {goodsId:dgoodsId,reduceMoney:$(this).val()}
				})
				var goodsIds = '';
				if(goodsType==1){
                    goodsIds = $("input[name='goods']:checked").val();
                    $('#goodsId').val(goodsIds);
				}else{
                    goodsIds = WST.getChks('.chkgoods');
                    goodsIds.push($('#combineGoodsIds').val());
                    $('#combineGoodsIds').val(goodsIds);
				}
				layer.close(index);
				createGoodsTable();
			},
		    cancel:function(){
		    	mmg = null;
		    }
		});
}

function createGoodsTable(){
	var params = {};
	params.goodsId = $('#goodsId').val();
	params.combineGoodsIds = $('#combineGoodsIds').val();
	$.post(WST.AU("combination://shops/listQueryByGoodsIds"),params,function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status==1 && json.data && json.data.length){
		    var ghtml = ['<table border="1" class="combineTable"><tr class="thead"><th colspan="2">'+WST.lang('combination_goods_name')+'</th><th>'+WST.lang('combination_goods_attr')+'</th><th>'+WST.lang('combination_original_price')+'</th><th>'+WST.lang('combination_reduction_price')+'</th><th>'+WST.lang('combination_operation')+'</th></tr>'];
		    json = json.data;
		    if(params.goodsId!=''){
			    for(var i=0;i<json.length;i++){
			    	if(params.goodsId==json[i]['goodsId']){
		                ghtml.push(
		                	'<tr '+(json[i]['isOut']?'style="color:red"':'')+' id="g_'+json[i]['goodsId']+'">',
		                	'<td><img width="90" height="90" src="'+WST.conf.RESOURCE_PATH+"/"+json[i]['goodsImgThumb']+'"></td>',
		                	'<td>'+json[i]['goodsName']+'</td>',
		                	'<td>主商品</td>',
		                	'<td>'+json[i]['shopPrice']+'</td>'
		                	);
		                if(json[i]['isOut']){
	                        ghtml.push('<td>'+WST.lang('combination_invalid')+'</td>');
		                }else{
		                	ghtml.push('<td><input type="text" style="width:80px" maxlength="10" class="reduce1" dataid="'+json[i]['goodsId']+'" value="'+(info['list'][json[i]['goodsId']]?info['list'][json[i]['goodsId']]['reduceMoney']:0)+'"></td>');
		                }
		                ghtml.push('<td><a href="javascript:delGoodsRow('+json[i]['goodsId']+',1)">'+WST.lang('combination_del')+'</a></td></tr>')
			            break;
			        }
			    }
			}
			if(params.combineGoodsIds!=''){
				for(var i=0;i<json.length;i++){
					 if(params.goodsId==json[i]['goodsId'])continue;
	                 ghtml.push(
	                	'<tr '+(json[i]['isOut']?'style="color:red"':'')+' id="g_'+json[i]['goodsId']+'">',
	                	'<td><img width="90" height="90" src="'+WST.conf.RESOURCE_PATH+"/"+json[i]['goodsImgThumb']+'"></td>',
	                	'<td>'+json[i]['goodsName']+'</td>',
	                	'<td>搭配商品</td>',
	                	'<td>'+json[i]['shopPrice']+'</td>'
	                	);
	                if(json[i]['isOut']){
                        ghtml.push('<td>'+WST.lang('combination_invalid')+'</td>');
	                }else{
	                	ghtml.push('<td><input type="text" style="width:80px" maxlength="10" class="reduce0" dataid="'+json[i]['goodsId']+'" value="'+(info['list'][json[i]['goodsId']]?info['list'][json[i]['goodsId']]['reduceMoney']:0)+'"></td>');
	                }
	                ghtml.push('<td><a href="javascript:delGoodsRow('+json[i]['goodsId']+',0)">'+WST.lang('combination_del')+'</a></td></tr>')
			    }
			}
            $('#combineTable').html(ghtml.join(''));
		}
	});
}
function delGoodsRow(goodsId,goodsType){
	$('#g_'+goodsId).remove();
	if(goodsType==1){
       $('#goodsId').val('');
	}else{
	   var ids = $('#combineGoodsIds').val();
	   ids = ids.split(',');
	   var index = $.inArray(goodsId.toString(),ids);
       ids.splice(index,1);
       $('#combineGoodsIds').val(ids.join(','));
	}
}
function save(p){
    $('#editform').isValid(function(v){
		if(v){
			var params = WST.getParams('.ipt');
			if(params.goodsId==''){
				WST.msg(WST.lang('combination_require_main_goods'),{icon:2});
				return;
			}
			if(params.combineGoodsIds==''){
				WST.msg(WST.lang('combination_select_least_one'),{icon:2});
				return;
			}
			params.reduceMoney = $('.reduce1').val();
			$('.reduce0').each(function(){
				params['reduceMoney_'+$(this).attr('dataid')] = $(this).val();
			})
			var loading = WST.load({msg:WST.lang('combination_submitting')});
			$.post(WST.AU("combination://shops/toEdit"),params,function(data,textStatus){
				layer.close(loading);
			    var json = WST.toJson(data);
			    if(json.status==1){
		            WST.msg(json.msg,{icon:1},function(){
		            	location.href = WST.AU('combination://shops/index','p='+p);
		            });
			    }else{
			    	WST.msg(json.msg,{icon:2});
			    }
			});
		}
	});
}

function changeStatus(id,combineStatus){
	$.post(WST.AU('combination://shops/changeStatus'),{id:id,combineStatus:combineStatus},function(data){
		var json = WST.toJson(data);
		if(json.status>0){
			WST.msg(json.msg, {icon: 1});
			loadGrid(WST_CURR_PAGE);
		}else{
			WST.msg(json.msg, {icon: 2});
		}
	});
}
