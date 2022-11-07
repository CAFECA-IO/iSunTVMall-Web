
function getShopsCats(objId,pVal,objVal){
	$('#'+objId).empty();
	$.post(WST.AU('integral://shops/getShopCats'),{parentId:pVal},function(data,textStatus){
	     var json = WST.toAdminJson(data);
	     var html = [],cat;
	     html.push("<option value='' >-"+WST.lang('integral_please_select')+"-</option>");
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
    params.goodsName = $('#sgoodsName').val();
    params.goodsCatIdPath = WST.ITGetAllGoodsCatVals('cat_0','j-goodsCats').join('_');
    if(params.goodsCatIdPath=='' && params.goodsName==''){
		 WST.msg(WST.lang('integral_tips1'),{icon:2});
		 return;
	}
	$('#goodsId').empty();
	$.post(WST.AU("integral://shops/searchGoods"),params,function(data,textStatus){
	    var json = WST.toAdminJson(data);
	    if(json.status==1 && json.data){
	    	var html = [];
	    	var option1 = null;
            sgoods = json.data;
	    	for(var i=0;i<json.data.length;i++){
	    		if(i==0)option1 = json.data[i];
                html.push('<option value="'+json.data[i].goodsId+'" gt="'+json.data[i].goodsType+'" mp="'+json.data[i].marketPrice+'" sp="'+json.data[i].marketPrice+'">'+json.data[i].goodsName+'</option>');
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


function editInit(p){
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
		formData: {dir:'integral',isWatermark:1,isThumb:1},
		accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
		callback:function(f){
			var json = WST.toAdminJson(f);
			if(json.status==1){
				$('#uploadMsg').empty().hide();
				$('#preview').attr('src',WST.conf.RESOURCE_PATH+"/"+json.savePath+json.thumb);
				$('#goodsImg').val(json.savePath+json.name);
				$('#msg_goodsImg').hide();
			}
		},
		progress:function(rate){
			$('#uploadMsg').show().html(WST.lang('integral_has_upload')+rate+"%");
		}
	});
	 /* 表单验证 */
    $('#integralform').validator({
          valid: function(form){
            var params = WST.getParams('.ipt');
			if(params.goodsId==''){
				WST.msg(WST.lang('integral_tips2'),{icon:2});
				return;
			}
			$.post(WST.AU("integral://goods/edit"),params,function(data,textStatus){
			    var json = WST.toAdminJson(data);
			    if(json.status==1){
		            WST.msg(json.msg,{icon:1},function(){
		            	location.href = WST.AU('integral://goods/pageByAdmin','p='+p);
		            });
			    }else{
			    	WST.msg(json.msg,{icon:2});
			    }
			});

      }

	});
}
