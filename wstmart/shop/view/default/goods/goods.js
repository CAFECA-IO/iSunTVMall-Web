/**删除批量上传的图片**/
var WSTHook_beforeEidtGoods = [];
var mmg;
var form;
$(function(){
	form = layui.form;
	form.on('radio(isFreeShipping)', function(data){
	  if($(this).val()==1){
	  	$("#shippingFeeTypeTr").hide();
	  	$("#shopExpressTr").hide();
	  }else{
	  	$("#shippingFeeTypeTr").show();
	  	$("#shopExpressTr").show();
	  }
	});
});
function delBatchUploadImg(obj,fn){
	var c = WST.confirm({content:WST.lang('are_you_sure_to_delete'),yes:function(){
		$(obj).parent().remove("li");
		layer.close(c);
		fn();
	}});
}
function lastGoodsCatCallback(opts){
	if(opts.isLast && opts.val){
	    getSpecAttrs(opts.val);
	}else{
		$('#specBtns').hide();
		$('#specsAttrBox').empty();
		$("#selMouldBox").hide();
	}
}
/**初始化**/
function initEdit(){
	$('#tab').TabPanel({tab:0,callback:function(no){
		if(no==1){
			$('.j-specImg').children().each(function(){
				if(!$(this).hasClass('webuploader-pick'))$(this).css({width:'80px',height:'25px'});
			});
		}
		if(!initBatchUpload && no==2){
			initBatchUpload = true;
			var uploader = batchUpload({uploadPicker:'#batchUpload',uploadServer:WST.U('shop/index/uploadPic'),formData:{dir:'goods',isWatermark:1,isThumb:1},uploadSuccess:function(file,response){
				var json = WST.toJson(response);
				if(json.status==1){
					$li = $('#'+file.id);
					$li.append('<input type="hidden" class="j-gallery-img" iv="'+json.savePath + json.thumb+'" v="' +json.savePath + json.name+'"/>');
					//$li.append('<span class="btn-setDefault">默认</span>' );
	                var delBtn = $('<span class="btn-del">'+WST.lang('del')+'</span>');
	                $li.append(delBtn);
	                delBtn.on('click',function(){
	                	delBatchUploadImg($(this),function(){
	                		if($('.filelist').find('li').length==0){
	                			$("#batchUpload").find('.placeholder').removeClass( 'element-invisible' );
						        $('.filelist').parent().removeClass('filled');
						        $('.filelist').hide();
						        $("#batchUpload").find('.statusBar').addClass( 'element-invisible' );
	                		}
	                		uploader.removeFile(file);
	        				uploader.refresh();
	                	});
	    			});
	                $('.filelist li').css('border','1px solid rgb(59, 114, 165)');
				}else{
					WST.msg(json.msg,{icon:2});
				}
			}});
		}
		$('.btn-del').click(function(){
			delBatchUploadImg($(this),function(){
				if($('.filelist').find('li').length==0){
        			$("#batchUpload").find('.placeholder').removeClass( 'element-invisible' );
			        $('.filelist').parent().removeClass('filled');
			        $('.filelist').hide();
			        $("#batchUpload").find('.statusBar').addClass( 'element-invisible' );
			        uploader.refresh();
        		}
        		$(this).parent().remove();
        	});
		})
	}});
	WST.upload({
	  	  pick:'#goodsImgPicker',
	  	  formData: {dir:'goods',isWatermark:1,isThumb:1},
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
		      $('#uploadMsg').show().html(WST.lang('upload_rate')+'rate+"%"');
		  }
	});
	WST.upload({
	  	  pick:'#goodsVideoPicker',
	  	  formData: {dir:'goods'},
	  	  server:WST.U('shop/index/uploadVideo'),
	  	  accept: {extensions: '3gp,mp4,rmvb,mov,avi,m4v',mimeTypes: 'video/*,audio/*,application/*'},
	  	  callback:function(f){
	  		  var json = WST.toJson(f);
	  		  if(json.status==1){
	  			  $('#uploadVideoMsg').empty().hide();
	  			  $('#goodsVideoPlayer').attr('src',WST.conf.RESOURCE_PATH+"/"+json.savePath+json.name);
	              $('#goodsVideo').val(json.savePath+json.name);
	              $('.vedio-del').css('display','inline-block');
	              $('#msg_goodsVideo').hide();
	  		  }
		  },
		  progress:function(rate){
		      $('#uploadVideoMsg').show().html(''+WST.lang('upload_rate')+''+rate+"%");
		  }
	});
	KindEditor.ready(function(K) {
		for(var key in WST.conf.sysLangs){
		  K.create('#langParams'+WST.conf.sysLangs[key].id+'goodsDesc', {
			  height:'350px',
			  width:'800px',
			  uploadJson : WST.conf.ROOT+'/shop/goods/editorUpload',
			  allowFileManager : false,
			  allowImageUpload : true,
			  themeType : "default",
		      items:[     'source', 'undo', 'redo',  'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
		                'plainpaste', 'wordpaste', 'justifyleft', 'justifycenter', 'justifyright',
		                'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
		                'superscript', 'clearhtml', 'quickformat', 'selectall',  'fullscreen',
		                'formatblock', 'fontname', 'fontsize',  'forecolor', 'hilitecolor', 'bold',
		                'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', 'image','multiimage','media','table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
		                'anchor', 'link', 'unlink'
		      ],
			  afterBlur: function(){ this.sync(); }
		  });
		}
	});
	if(OBJ.goodsId>0){
		var goodsCatIds = OBJ.goodsCatIdPath.split('_');
		getBrands('brandId',goodsCatIds[0],OBJ.brandId);
		if(goodsCatIds.length>1){
			var objId = goodsCatIds[0];
			$('#cat_0').val(objId);
			var opts = {id:'cat_0',val:goodsCatIds[0],childIds:goodsCatIds,className:'j-goodsCats',afterFunc:'lastGoodsCatCallback'}
        	WST.ITSetGoodsCats(opts);
	    }
		getShopsCats('shopCatId2',OBJ.shopCatId1,OBJ.shopCatId2);
	}
}
function clearVedio(obj){
	$(obj).hide();
	$('#goodsVideoPlayer').attr('src','');
	$('#goodsVideo').val('');
}
/**获取本店分类**/
function getShopsCats(objId,pVal,objVal){
	$('#'+objId).empty();
	$.post(WST.U('shop/shopcats/listQuery'),{parentId:pVal},function(data,textStatus){
	     var json = WST.toJson(data);
	     var html = [],cat;
	     html.push("<option value='' >-"+WST.lang('select')+"-</option>");
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
/**获取品牌**/
function getBrands(objId,catId,objVal){
	$('#'+objId).empty();
	$.post(WST.U('shop/brands/listQuery'),{catId:catId},function(data,textStatus){
	     var json = WST.toJson(data);
	     var html = [],cat;
	     html.push("<option value='' >-"+WST.lang('select')+"-</option>");
	     if(json.status==1 && json.list){
	    	 json = json.list;
			 for(var i=0;i<json.length;i++){
			     cat = json[i];
			     html.push("<option value='"+cat.brandId+"' "+((objVal==cat.brandId)?"selected":"")+">"+cat.brandName+"</option>");
			 }
	     }
	     $('#'+objId).html(html.join(''));
	});
}
function toEdit(id,src){
	location.href = WST.U('shop/goods/edit','id='+id+'&src='+src+'&p='+WST_CURR_PAGE);
}
function toAdd(src){
    location.href = WST.U('shop/goods/add','src='+src);
}
/**保存商品数据**/
function save(p){
	var va = $("input[name='defaultSpec']:checked").val();
	if(va){
		$("#marketPrice").val($("#marketPrice_"+va).val());
		$("#shopPrice").val( $("#specPrice_"+va).val());
		$("#costPrice").val( $("#costPrice_"+va).val());
		$("#goodsWeight").val( $("#specWeight_"+va).val());
		$("#goodsVolume").val( $("#specVolume_"+va).val());
	}
	$('#editform').isValid(function(v){
		if(v){
			var params = WST.getParams('.j-ipt');
			params['langParams'] = 'true';
			params.goodsCatId = WST.ITGetGoodsCatVal('j-goodsCats');
			params.specNum = specNum;
			var specsName,specImg;
			$('.j-speccat').each(function(){
				for(var key in WST.conf.sysLangs){
					specsName = 'specName_'+$(this).attr('cat')+'_'+$(this).attr('num')+'_'+WST.conf.sysLangs[key]['id'];
					if($(this)[0].checked){
						params[specsName] = $.trim($('#'+specsName).val());
					}
				}
				specImg = 'specImg_'+$(this).attr('cat')+'_'+$(this).attr('num');
				if($(this)[0].checked)params[specImg] = $.trim($('#'+specImg).attr('v'));
			});
			var gallery = [];
			$('.j-gallery-img').each(function(){
				gallery.push($(this).attr('v'));
			});
			params.gallery = gallery.join(',');
			var specsIds = [];
			var specidsmap = [];
			$('.j-ws').each(function(){
				specsIds.push($(this).attr('v'));
				specidsmap.push(WST.blank($(this).attr('sid'))+":"+$(this).attr('v'));
			});
			var specmap = [];
			for(var key in id2SepcNumConverter){
				specmap.push(key+":"+id2SepcNumConverter[key]);
			}
			params.specsIds = specsIds.join(',');
			params.specidsmap = specidsmap.join(',');
			params.specmap = specmap.join(',');
			if(WSTHook_beforeEidtGoods.length>0){
				for(var i=0;i<WSTHook_beforeEidtGoods.length;i++){
					delete window['callback_'+WSTHook_beforeEidtGoods[i]];
					params = window[WSTHook_beforeEidtGoods[i]](params);
					if(window['callback_'+WSTHook_beforeEidtGoods[i]]){
						window['callback_'+WSTHook_beforeEidtGoods[i]]();
						return;
					}
				}
			}
			var memberGroupId = [];
			var memberReduceMoney = [];
			$('.member-group-id').each(function(idx,item){
				memberGroupId.push($(item).val());
			});
			$('.member-reduce-money').each(function(idx,item){
				memberReduceMoney.push($(item).val());
			});
			params.memberGroupId = memberGroupId.join(',');
			params.memberReduceMoney = memberReduceMoney.join(',');
			var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		    $.post(WST.U('shop/goods/'+((params.goodsId==0)?"toAdd":"toEdit")),params,function(data,textStatus){
		    	layer.close(loading);
		    	var json = WST.toJson(data);
		    	if(json.status=='1'){
		    		WST.msg(json.msg,{icon:1},function(){
						if(params.goodsType==1){
                            location.href=WST.U('shop/goodsvirtuals/stock','id='+json.data.id);
						}else{
							location.href=WST.U('shop/goods/'+src,"p="+p);
						}
					});
		    	}else{
		    		WST.msg(json.msg,{icon:2});
		    	}
		    });
		}
	});
}
var id2SepcNumConverter = {};
/**添加普通规格值**/
function addSpec(opts){
	var html = [];
	html.push('<div class="spec-item">',
	          '<div style="float:left;padding: 5px;"><input type="checkbox" class="j-speccat j-speccat_'+opts.catId+' j-spec_'+opts.catId+'_'+specNum+'" cat="'+opts.catId+'" num="'+specNum+'" onclick="javascript:addSpecSaleCol()" '+opts.checked+'/></div>');
    html.push('<div style="float:left;width:150px">');
    for(var key in WST.conf.sysLangs){
	    html.push('<input type="text" class="spec-ipt" id="specName_'+opts.catId+'_'+specNum+'_'+WST.conf.sysLangs[key]['id']+'" maxLength="50" value="'+WST.blank(opts.langs[WST.conf.sysLangs[key]['id']])+'" onblur="batchChangeTxt(this.value,'+opts.catId+','+specNum+')" placeholder="'+ WST.conf.sysLangs[key]['name']+'"/>');
	}
	html.push('</div>');
	html.push('<div class="item-del" style="float:left" onclick="delSpec(this,'+opts.catId+','+specNum+')"></div>',
	          '</div>');
	$(html.join('')).insertBefore('#specAddBtn_'+opts.catId);
	if(opts.itemId){
		id2SepcNumConverter[opts.itemId] = opts.catId+'_'+specNum;
	}

	specNum++;
}
/**删除普通规格值**/
function delSpec(obj,catId,num){
	if($('.j-spec_'+catId+'_'+num)[0].checked){
		$('.j-spec_'+catId+'_'+num)[0].checked = false;
		addSpecSaleCol();
	}
	$(obj).parent().remove();
}
/**添加带图片的规格值**/
function addSpecImg(opts){
	var html = [];
	html.push('<div class="spec-item-img"><div style="float:left;padding: 5px;"><input type="checkbox" class="j-speccat j-speccat_'+opts.catId+' j-spec_'+opts.catId+'_'+specNum+'" cat="'+opts.catId+'" num="'+specNum+'" onclick="javascript:addSpecSaleCol()" '+opts.checked+'/></div>');
    html.push('<div style="float:left;width:150px">');
    for(var key in WST.conf.sysLangs){
         html.push('<input type="text" class="spec-ipt" id="specName_'+opts.catId+'_'+specNum+'_'+WST.conf.sysLangs[key]['id']+'" maxLength="50" value="'+WST.blank(opts.langs[WST.conf.sysLangs[key]['id']])+'" onblur="batchChangeTxt(this.value,'+opts.catId+','+specNum+')" placeholder="'+ WST.conf.sysLangs[key]['name']+'"/>');
    }
    html.push('</div>');
    html.push('<div id="uploadMsg_'+opts.catId+'_'+specNum+'"  style="float:left;padding: 5px;">',
	            (opts.specImg)?'<img height="25"  width="25" id="specImg_'+opts.catId+'_'+specNum+'" src="'+WST.conf.RESOURCE_PATH+"/"+opts.specImg+'" v="'+opts.specImg+'"/><span class="item-del" onclick="clearSpecImg(this,'+specNum+')"></span>':"",
	            '</div><div id="specImgPicker_'+specNum+'" class="j-specImg" style="float:left">'+WST.lang('upload')+'</div>'
	         );
	if($('#specTby').children().size()==0){
    	html.push('<div style="float:left"><button type="button" class="btn btn-success" id="specImgBtn" onclick="addSpecImg({catId:'+opts.catId+',checked:\'\',langs:{}})"><i class="fa fa-plus"></i>'+WST.lang('add')+'</button></div>');
    }else{
    	html.push('<div style="float:left"><button type="button" class="btn btn-primary" id="specImgBtn" onclick="delSpecImg(this,'+opts.catId+','+specNum+')"><i class="fa fa-trash-o"></i>'+WST.lang('del')+'</button></div>');
    }
    html.push('<div style="clear:both;"></div></div>');
	$('#specTby').append(html.join(''));
	WST.upload({
		  num:specNum,
		  cat:opts.catId,
	  	  pick:'#specImgPicker_'+specNum,
	  	  formData: {dir:'goods',isThumb:1},
	  	  accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
	  	  callback:function(f){
	  		  var json = WST.toJson(f);
	  		  if(json.status==1){
	  			$('#uploadMsg_'+this.cat+"_"+this.num).html('<img id="specImg_'+this.cat+"_"+this.num+'" v="'+json.savePath+json.thumb+'" src="'+WST.conf.RESOURCE_PATH+"/"+json.savePath+json.thumb+'" height="25"  width="25"/><span class="item-del" onclick="clearSpecImg(this,'+this.num+')"></span>');
	  		  }
		  },
		  progress:function(rate){
		      $('#uploadMsg_'+this.cat+"_"+this.num).html(''+WST.lang('upload_rate')+rate+"%");
		  }
	});
	if(opts.itemId){
		id2SepcNumConverter[opts.itemId] = opts.catId+'_'+specNum;
	}
	specNum++;
}
/**删除带图片的规格值**/
function delSpecImg(obj,catId,num){
	if($('.j-spec_'+catId+'_'+num)[0].checked){
		$('.j-spec_'+catId+'_'+num)[0].checked = false;
		addSpecSaleCol();
	}
	$(obj).parent().parent().remove();
}
function clearSpecImg(obj,num){
	$(obj).parent().empty();
}
/**给销售规格表填上值**/
function fillSepcSale(){
	var ids = '',tmpids = [];
	for(var i=0;i<OBJ.saleSpec.length;i++){
		tmpids = [];
		ids = OBJ.saleSpec[i].specIds;
		ids = ids.split(':');
		for(var j=0;j<ids.length;j++){
			tmpids.push(id2SepcNumConverter[ids[j]]);
		}
		tmpids = tmpids.join('-');
		if(OBJ.saleSpec[i].isDefault)$('#isDefault_'+tmpids).attr('checked',true);
		$('#productNo_'+tmpids).val(OBJ.saleSpec[i].productNo);
		$('#marketPrice_'+tmpids).val(OBJ.saleSpec[i].marketPrice);
		$('#specWeight_'+tmpids).val(OBJ.saleSpec[i].specWeight);
		$('#specVolume_'+tmpids).val(OBJ.saleSpec[i].specVolume);
		$('#specPrice_'+tmpids).val(OBJ.saleSpec[i].specPrice);
		$('#costPrice_'+tmpids).val(OBJ.saleSpec[i].costPrice);
		$('#specStock_'+tmpids).val(OBJ.saleSpec[i].specStock);
		$('#warnStock_'+tmpids).val(OBJ.saleSpec[i].warnStock);
		$('#saleNum_'+tmpids).html(OBJ.saleSpec[i].saleNum);
		$('#saleNum_'+tmpids).attr('sid',OBJ.saleSpec[i].id);
	}
}
/**生成销售规格表**/
function addSpecSaleCol(){
	//获取规格分类和规格值
	var catId,snum,specCols = {},obj = [],langId = WST.conf.sysLangs[WST.conf.sysLang]['id'];
	$('.j-speccat').each(function(){
		if($(this)[0].checked){
			catId = $(this).attr('cat');
			snum = $(this).attr('num');
			if(!specCols[catId]){
				specCols[catId] = [];
				specCols[catId].push({id:catId+"_"+snum,val:$.trim($('#specName_'+catId+"_"+snum+'_'+langId).val())});
			}else{
				specCols[catId].push({id:catId+"_"+snum,val:$.trim($('#specName_'+catId+"_"+snum+'_'+langId).val())});
			}
	    }
	});
	//创建表头
	$('.j-saleTd').remove();
	var html = [],specArray = [];;
	for(var key in specCols){
		html.push('<th class="j-saleTd">'+$('#specCat_'+key).html()+'</th>');
		specArray.push(specCols[key]);
	}
	if(html.length==0){
        $('#goodsStock').removeAttr('disabled');
		$('#shopPrice').removeAttr('disabled');
		$('#marketPrice').removeAttr('disabled');
		$('#warnStock').removeAttr('disabled');
		return;
	}
	$(html.join('')).insertBefore('#thCol');
	//组合规格值
	this.combined = function(doubleArrays){
        var len = doubleArrays.length;
        if (len >= 2) {
            var arr1 = doubleArrays[0];
            var arr2 = doubleArrays[1];
            var len1 = doubleArrays[0].length;
            var len2 = doubleArrays[1].length;
            var newlen = len1 * len2;
            var temp = new Array(newlen),ntemp;
            var index = 0;
            for (var i = 0; i < len1; i++) {
            	if(arr1[i].length){
            		for (var k = 0; k < len2; k++) {
            			ntemp = arr1[i].slice();
            			ntemp.push(arr2[k]);
		                temp[index] = ntemp;
		                index++;
            		}
            	}else{
	                for (var j = 0; j < len2; j++) {
	                    temp[index] = [arr1[i],arr2[j]];
	                    index++;
	                }
            	}
            }
            var newArray = new Array(len - 1);
            newArray[0] = temp;
            if (len > 2) {
                var _count = 1;
                for (var i = 2; i < len; i++) {
                    newArray[_count] = doubleArrays[i];
                    _count++;
                }
            }
            return this.combined(newArray);
        }else {
            return doubleArrays[0];
        }
    }

	var specsRows = this.combined(specArray);
	//生成规格值表
	html = [];
	var id=[],key=1,specHtml = [];
	var productNo = $('#productNo').val(),specProductNo = '';
	for(var i=0;i<specsRows.length;i++){
		id = [],specHtml = [];
		html.push('<tr class="j-saleTd">');

		if(specsRows[i].length){
			for(var j=0;j<specsRows[i].length;j++){
				id.push(specsRows[i][j].id);
				specHtml.push('<td class="j-td_'+specsRows[i][j].id+'">' + specsRows[i][j].val + '</td>');
	        }
		}else{
			id.push(specsRows[i].id);
			specHtml.push('<td class="j-td_'+specsRows[i].id+'">' + specsRows[i].val + '</td>');
		}
		id = id.join('-');
		//if(OBJ.goodsId==0){
			specProductNo = productNo+'-'+key;
		//}
		html.push('  <td><input type="radio" id="isDefault_'+id+'" name="defaultSpec" class="j-ipt" value="'+id+'"/></td>');
		html.push(specHtml.join(''));
		html.push('  <td><input type="text" class="spec-sale-goodsNo j-ipt" id="productNo_'+id+'" value="'+specProductNo+'" onblur="checkProductNo(this)" ></td>',
	              '  <td><input type="text" class="spec-sale-ipt j-ipt" id="marketPrice_'+id+'" onblur="WST.limitDecimal(this,2);javascript:WST.limitDecimal(this,2)" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></td>',
	              '  <td><input type="text" class="spec-sale-ipt j-ipt" id="specPrice_'+id+'" onblur="WST.limitDecimal(this,2);javascript:WST.limitDecimal(this,2)" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></td>',
				 '  <td><input type="text" class="spec-sale-ipt j-ipt" id="costPrice_'+id+'" onblur="WST.limitDecimal(this,2);javascript:WST.limitDecimal(this,2)" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></td>',
	              '  <td><input type="text" class="spec-sale-ipt j-ipt" id="specStock_'+id+'" onkeypress="return WST.isNumberKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></td>',
	              '  <td><input type="text" class="spec-sale-ipt j-ipt" id="warnStock_'+id+'" onkeypress="return WST.isNumberKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></td>',
	              '  <td><input type="text" class="spec-sale-ipt j-ipt" id="specWeight_'+id+'" onblur="WST.limitDecimal(this,2);javascript:WST.limitDecimal(this,2)" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></td>',
	              '  <td><input type="text" class="spec-sale-ipt j-ipt" id="specVolume_'+id+'" onblur="WST.limitDecimal(this,2);javascript:WST.limitDecimal(this,2)" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></td>',
	              '  <td class="j-ws" v="'+id+'" id="saleNum_'+id+'">0</td>',
	              '</tr>');
		key++;
	}
	$('#spec-sale-tby').append(html.join(''));
	//判断是否禁用商品价格和库存字段
	if($('#spec-sale-tby').html()!=''){
		$('#goodsStock').prop('disabled',true);
		$('#shopPrice').prop('disabled',true);
		$('#marketPrice').prop('disabled',true);
		$('#warnStock').prop('disabled',true);
		$('#goodsWeight').prop('disabled',true);
		$('#goodsVolume').prop('disabled',true);
	}
	//设置销售规格表值
	if(OBJ.saleSpec)fillSepcSale();
}
/**根据批量修改销售规格值**/
function batchChange(v,id){
	if($.trim(v)!=''){
		$('input[type=text][id^="'+id+'_"]').val(v);
	}
}
/**根据规格值修改 销售规格表 里的值**/
function batchChangeTxt(v,catId,num){
	$('.j-td_'+catId+"_"+num).each(function(){
		$(this).html(v);
	});
}
/**检测商品销售规格值是否重复**/
function checkProductNo(obj){
	v = $.trim(obj.value);
	var num = 0;
	$('input[type=text][id^="productNo_"]').each(function(){
		if(v==$.trim($(this).val()))num++;
	});
	if(num>1){
		WST.msg(WST.lang('the_same_article_number_already_exists'),{icon:2});
		obj.value = '';
	}
}
/**获取商品规格和属性**/
function getSpecAttrs(goodsCatId){
	$('#specsAttrBox').empty();
	$('#specBtns').hide();
	specNum = 0;
	$.post(WST.U('shop/goods/getSpecAttrs'),{goodsCatId:goodsCatId,goodsType:$('#goodsType').val()},function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status==1 && json.data){
			var html = [],tmp,str;
			if(json.data.spec0 || json.data.spec1 || json.data.attrs){
	    		getMouldList(goodsCatId);
			}
			if(json.data.spec0 || json.data.spec1){
				html.push('<div class="spec-head">'+WST.lang('product_specifications')+'</div>');
				html.push('<div class="spec-body">');
				if(json.data.spec0){
					tmp = json.data.spec0;
					html.push('<div id="specCat_'+tmp.catId+'">'+tmp.catName+'</div>');
					html.push('<div id="specTby"></div>');
				}
				if(json.data.spec1){
					for(var i=0;i<json.data.spec1.length;i++){
						tmp = json.data.spec1[i];
						html.push('<div class="spec-line"></div>',
						          '<div id="specCat_'+tmp.catId+'">'+tmp.catName+'</div>',
						          '<div style="height:auto;">',
						          '<button type="button" class="btn btn-success" id="specAddBtn_'+tmp.catId+'" onclick="javascript:addSpec({catId:'+tmp.catId+',checked:\'\',langs:{}})"><i class="fa fa-plus"></i>'+WST.lang('add')+'</button>',
						          '<div style="clear:both;"></div></div>'
								);
					}
				}
				html.push('</div>');
				html.push($('#specTips').html());
				html.push('<div id="specSaleHead" class="spec-head">'+WST.lang('sales_specifications')+'</div>',
				          '<table class="specs-sale-table">',
				          '  <thead id="spec-sale-hed">',
				          '   <tr>',
				          '     <th>'+WST.lang('recommend')+'<br/>'+WST.lang('specifications')+'</th>',
				          '     <th id="thCol"><font color="red">*</font>'+WST.lang('product_code')+'</th>',
				          '     <th><font color="red">*</font>'+WST.lang('market_price')+'<br/><input type="text" class="spec-sale-ipt" onblur="WST.limitDecimal(this,2);batchChange(this.value,\'marketPrice\')" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
				          '     <th><font color="red">*</font>'+WST.lang('our_price')+'<br/><input type="text" class="spec-sale-ipt" onblur="WST.limitDecimal(this,2);batchChange(this.value,\'specPrice\')" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
						'     <th><font color="red">*</font>'+WST.lang('cost_price')+'<br/><input type="text" class="spec-sale-ipt" onblur="WST.limitDecimal(this,2);batchChange(this.value,\'costPrice\')" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
				          '     <th><font color="red">*</font>'+WST.lang('stock')+'<br/><input type="text" class="spec-sale-ipt" onblur="batchChange(this.value,\'specStock\')" onkeypress="return WST.isNumberKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
				          '     <th><font color="red">*</font>'+WST.lang('early_warning_inventory')+'<br/><input type="text" class="spec-sale-ipt" onblur="batchChange(this.value,\'warnStock\')" onkeypress="return WST.isNumberKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
				          '     <th><font color="red">*</font>'+WST.lang('commodity_weight')+'<br/><input type="text" class="spec-sale-ipt" onblur="batchChange(this.value,\'specWeight\')" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
				          '     <th><font color="red">*</font>'+WST.lang('commodity_volume')+'<br/><input type="text" class="spec-sale-ipt" onblur="batchChange(this.value,\'specVolume\')" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
				          '     <th>'+WST.lang('sales_volume')+'</th>',
				          '   </tr>',
				          '  </thead>',
				          '  <tbody id="spec-sale-tby"></tbody></table>'
						);
			}
			if(json.data.attrs){
				html.push('<div class="spec-head">'+WST.lang('commodity_attributes')+'</div>');
				html.push('<div class="spec-body">');
				html.push('<table class="attr-table">');
				for(var i=0;i<json.data.attrs.length;i++){
					tmp = json.data.attrs[i];
					html.push('<tr><th width="180" nowrap valign="top" style="padding-top:6px;">'+tmp.attrName+'：</th><td>');
					if(tmp.attrType==1){
						str = tmp.attrVal.split(',');
						html.push('<div id="attrlable_'+tmp.attrId+'" class="labelattr '+((str.length>12)?"labelhide":"")+'">');
						for(var j=0;j<str.length;j++){
						    html.push('<label><input type="checkbox" class="j-ipt j-mould" name="attr_'+tmp.attrId+'" value="'+str[j]+'"/>'+str[j]+'</label>');
						}
						html.push('</div>');
					    if(str.length>12)html.push('<a id="attra_'+tmp.attrId+'" href="javascript:showHideAttr('+tmp.attrId+')" class="labela"></a>');
					}else if(tmp.attrType==2){
						html.push('<select name="attr_'+tmp.attrId+'" id="attr_'+tmp.attrId+'" class="j-ipt j-mould">');
						html.push('<option value="">'+WST.lang('select')+'</option>');
						str = tmp.attrVal.split(',');
						for(var j=0;j<str.length;j++){
							html.push('<option value="'+str[j]+'">'+str[j]+'</option>');
						}
						html.push('</select>');
					}else{
						for(var key in WST.conf.sysLangs){
							html.push('<div><input type="text" name="attr_'+tmp.attrId+'_'+WST.conf.sysLangs[key]['id']+'" id="attr_'+tmp.attrId+'_'+WST.conf.sysLangs[key]['id']+'" class="spec-sale-text j-ipt j-mould" placeholder="'+ WST.conf.sysLangs[key]['name']+'"/></div>');
						}
					}
					html.push('</td></tr>');
				}
				html.push('</table>');
				html.push('</div>');
			}
			$('#specsAttrBox').html(html.join(''));

			//如果是编辑的话，第一次要设置之前设置的值
			if(OBJ.goodsId>0 && specNum==0){
				//设置规格值
				if(OBJ.spec0){
					for(var i=0;i<OBJ.spec0.length;i++){
					   if(OBJ.spec0[i].catId==json.data.spec0.catId)addSpecImg({catId:OBJ.spec0[i].catId,checked:'checked',langs:OBJ.spec0[i].langParams,itemId:OBJ.spec0[i].itemId,specImg:OBJ.spec0[i].itemImg});
					}
				}
				if(OBJ.spec1){
					for(var i=0;i<OBJ.spec1.length;i++){
						for(var j=0;j<json.data.spec1.length;++j){
							if(OBJ.spec1[i].catId==json.data.spec1[j].catId){
					    		addSpec({catId:OBJ.spec1[i].catId,checked:'checked',langs:OBJ.spec1[i].langParams,itemId:OBJ.spec1[i].itemId});
								break;
							}
						}
					}
				}
				addSpecSaleCol();
				//设置商品属性值
				var tmp = null;
				if(OBJ.attrs){
					for(var akey in OBJ.attrs){
						if(OBJ.attrs[akey].attrType==1){
							tmp = OBJ.attrs[akey]['langParams'][WST.conf.sysLangs[WST.conf.sysLang]['id']];
							tmp = tmp?OBJ.attrs[akey].attrVal.split(','):'';
							WST.setValue("attr_"+OBJ.attrs[akey].attrId,tmp);
						}else{
                            if(OBJ.attrs[akey].attrType==2){
                            	tmp = OBJ.attrs[akey]['langParams'][WST.conf.sysLangs[WST.conf.sysLang]['id']];
                                WST.setValue("attr_"+OBJ.attrs[akey].attrId,tmp);
                            }else{
                                for(var key in WST.conf.sysLangs){
							        WST.setValue("attr_"+OBJ.attrs[akey].attrId+"_"+WST.conf.sysLangs[key]['id'],OBJ.attrs[akey]['langParams'][WST.conf.sysLangs[key]['id']]);
							    }
                            }
						}
					}
				}

			}
			//给没有初始化的规格初始化一个输入框
			if(json.data.spec0 && !$('.j-speccat_'+json.data.spec0.catId)[0]){
				addSpecImg({catId:json.data.spec0.catId,checked:'',langs:{}});
			}
			if(json.data.spec1){
				for(var i=0;i<json.data.spec1.length;i++){
					if(!$('.j-speccat_'+json.data.spec1[i].catId)[0])addSpec({catId:json.data.spec1[i].catId,checked:'',langs:{}});
				}
			}
			$('#specBtns').show();
		}
	});
}
function showHideAttr(id){
   var obj = $('#attrlable_'+id);
   if(obj.hasClass('labelhide')){
   	    obj.animate({height:'100%'},500,function(){
            obj.removeClass('labelhide');
            $('#attra_'+id).text(WST.lang('hide'));
   	    })
   }else{
        obj.animate({height:'90px'},500,function(){
        	obj.addClass('labelhide');
        	$('#attra_'+id).text(WST.lang('more'));
        })

   }
}
function changeGoodsType(v){
	if(v==0){
	    $('#goodsStockTr').show();
	    $('#goodsWeightTr').show();
	    $('#goodsVolumeTr').show();
		$('#isFreeShippingTr').show();
		$('#shippingFeeTypeTr').show();
		$('#shopExpressTr').show();
	    $('#goodsStock').removeAttr('disabled');
    }else{
    	$('#goodsStockTr').hide();
    	$('#goodsWeightTr').hide();
	    $('#goodsVolumeTr').hide();
	    $('#isFreeShippingTr').hide();
	    $('#shippingFeeTypeTr').hide();
	    $('#shopExpressTr').hide();
    	$('#goodsStock').prop('disabled',true);
    }
    var goodsCatId =WST.ITGetGoodsCatVal('j-goodsCats');
    getSpecAttrs(goodsCatId);
}
function toStock(id,src){
    location.href=WST.U('shop/goodsvirtuals/stock','id='+id+"&src="+src);
}
function toDetail(goodsId,key){
    window.open(WST.U('home/goods/detail','goodsId='+goodsId+"&key="+key));
}
function toolTip(){
    WST.toolTip();
}
function saleByPage(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('product_picture'), name:'goodsName', width: 80, renderer: function(val,item,rowIndex){
                var thumb = item['goodsImg'];
	        	thumb = thumb.replace('.','_thumb.');
            	return "<span class='weixin'><a style='color:blue' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb
            	+"'></a><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></span>";
        }},
        {title:WST.lang('goods_name'), name:'goodsName', width: 250, renderer: function(val,item,rowIndex){
        	return "<a style='color:#666' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'>"+val+"</a> ";

        }},
        {title:WST.lang('goods_number'), name:'goodsSn', width: 100},
        {title:WST.lang('price'), name:'shopPrice', width: 50},
        {title:WST.lang('recommend'), name:'isRecom', width: 30,renderer:function(val,item,rowIndex){
                if(item['isRecom']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i>"+WST.lang("no")+"</span>";
                }
            }},
        {title:WST.lang('boutique'), name:'isBest', width: 30,renderer:function(val,item,rowIndex){
                if(item['isBest']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i>"+WST.lang("no")+"</span>";
                }
            }},
        {title:WST.lang('new_products'), name:'isNew', width: 30,renderer:function(val,item,rowIndex){
                if(item['isNew']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i>"+WST.lang("no")+"</span>";
                }
            }},
        {title:WST.lang('hot_sale'), name:'isHot', width: 30,renderer:function(val,item,rowIndex){
                if(item['isHot']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i>"+WST.lang("no")+"</span>";
                }
            }},
        {title:WST.lang('number_of_collections'), name:'collectNum', width: 30},
        {title:WST.lang('sales_volume'), name:'saleNum', width: 40},
        {title:WST.lang('merchandise_inventory'), name:'goodsStock', width: 40},
        {title:WST.lang('op'), name:'' ,width:200, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                h += "<a class='btn btn-blue' href='javascript:toEdit("+ item['goodsId']+",\"sale\")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                if(item['goodsType']==1)h += "<a class='btn btn-blue' href='javascript:toStock(" + item['goodsId'] + ",\"sale\")'><i class='fa fa-pencil'></i>"+WST.lang('card_coupon')+"</a> ";
                h += "<a class='btn btn-red' href='javascript:del(" + item['goodsId'] + ",\"sale\")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                if(item['hook']!=undefined)h += item['hook'];
                return h;
            }}
    ];

    mmg = $('.mmg').mmGrid({height: h-150,indexCol: true, cols: cols,method:'POST',
        url: WST.U('shop/goods/saleByPage'), fullWidthRows: true, autoLoad: false,checkCol:true,multiSelect:true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function auditByPage(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('product_picture'), name:'goodsName', width: 80, renderer: function(val,item,rowIndex){
               var thumb = item['goodsImg'];
	        	thumb = thumb.replace('.','_thumb.');
            	return "<span class='weixin'><a style='color:blue' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb
            	+"'></a><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></span>";
        }},
        {title:WST.lang('goods_name'), name:'goodsName', width: 250, renderer: function(val,item,rowIndex){
        	return "<a style='color:#666' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'>"+val+"</a> ";

        }},
        {title:WST.lang('goods_number'), name:'goodsSn', width: 200},
        {title:WST.lang('price'), name:'shopPrice', width: 30},
        {title:WST.lang('recommend'), name:'isRecom', width: 30,renderer:function(val,item,rowIndex){
                if(item['isRecom']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i>"+WST.lang("no")+"</span>";
                }
            }},
        {title:WST.lang('boutique'), name:'isBest', width: 30,renderer:function(val,item,rowIndex){
                if(item['isBest']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i>"+WST.lang("no")+"</span>";
                }
            }},
        {title:WST.lang('new_products'), name:'isNew', width: 30,renderer:function(val,item,rowIndex){
                if(item['isNew']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i>"+WST.lang("no")+"</span>";
                }
            }},
        {title:WST.lang('hot_sale'), name:'isHot', width: 30,renderer:function(val,item,rowIndex){
                if(item['isHot']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i>"+WST.lang("no")+"</span>";
                }
            }},
        {title:WST.lang('number_of_collections'), name:'collectNum', width: 30},
        {title:WST.lang('sales_volume'), name:'saleNum', width: 30},
        {title:WST.lang('stock'), name:'goodsStock', width: 30},
        {title:WST.lang('op'), name:'' ,width:180, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                h += "<a class='btn btn-blue' href='javascript:toEdit("+ item['goodsId']+",\"audit\")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                if(item['goodsType']==1)h += "<a class='btn btn-blue' href='javascript:toStock(" + item['goodsId'] + ",\"sale\")'><i class='fa fa-pencil'></i>"+WST.lang('card_coupon')+"</a> ";
                h += "<a class='btn btn-red' href='javascript:del(" + item['goodsId'] + ",\"audit\")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
    ];

    mmg = $('.mmg').mmGrid({height: h-150,indexCol: true, cols: cols,method:'POST',
        url: WST.U('shop/goods/auditByPage'), fullWidthRows: true, autoLoad: false,checkCol:true,multiSelect:true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p)

}
function storeByPage(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('product_picture'), name:'goodsName', width: 80, renderer: function(val,item,rowIndex){
                var thumb = item['goodsImg'];
	        	thumb = thumb.replace('.','_thumb.');
            	return "<span class='weixin'><a style='color:blue' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb
            	+"'></a><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></span>";
        }},
        {title:WST.lang('goods_name'), name:'goodsName', width: 250, renderer: function(val,item,rowIndex){
        	return "<a style='color:#666' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'>"+val+"</a> ";

        }},
        {title:WST.lang('goods_number'), name:'goodsSn', width: 100},
        {title:WST.lang('price'), name:'shopPrice', width: 50},
        {title:WST.lang('recommend'), name:'isRecom', width: 30,renderer:function(val,item,rowIndex){
                if(item['isRecom']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i>"+WST.lang("no")+"</span>";
                }
            }},
        {title:WST.lang('boutique'), name:'isBest', width: 30,renderer:function(val,item,rowIndex){
                if(item['isBest']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i>"+WST.lang("no")+"</span>";
                }
            }},
        {title:WST.lang('new_products'), name:'isNew', width: 30,renderer:function(val,item,rowIndex){
                if(item['isNew']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i>"+WST.lang("no")+"</span>";
                }
            }},
        {title:WST.lang('hot_sale'), name:'isHot', width: 30,renderer:function(val,item,rowIndex){
                if(item['isHot']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i>"+WST.lang("no")+"</span>";
                }
            }},
        {title:WST.lang('number_of_collections'), name:'collectNum', width: 40},
        {title:WST.lang('sales_volume'), name:'saleNum', width: 50},
        {title:WST.lang('stock'), name:'goodsStock', width: 50},
        {title:WST.lang('op'), name:'' ,width:210, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                h += "<a class='btn btn-blue' href='javascript:toEdit("+ item['goodsId']+",\"store\")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                if(item['goodsType']==1)h += "<a class='btn btn-blue' href='javascript:toStock(" + item['goodsId'] + ",\"sale\")'><i class='fa fa-pencil'></i>"+WST.lang('card_coupon')+"</a> ";
                h += "<a class='btn btn-red' href='javascript:del(" + item['goodsId'] + ",\"store\")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
    ];

    mmg = $('.mmg').mmGrid({height: h-150,indexCol: true, cols: cols,method:'POST',
        url: WST.U('shop/goods/storeByPage'), fullWidthRows: true, autoLoad: false,checkCol:true,multiSelect:true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p)
}
function illegalByPage(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('product_picture'), name:'goodsName', width: 80, renderer: function(val,item,rowIndex){
                var thumb = item['goodsImg'];
	        	thumb = thumb.replace('.','_thumb.');
            	return "<span class='weixin'><a style='color:blue' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb
            	+"'></a><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></span>";
        }},
        {title:WST.lang('goods_name'), name:'goodsName', width: 100, renderer: function(val,item,rowIndex){
        	return "<a style='color:#666' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'>"+val+"</a> ";

        }},
        {title:WST.lang('goods_number'), name:'goodsSn', width: 30},
        {title:WST.lang('reasons_for_violation'), name:'illegalRemarks', width: 350},
        {title:WST.lang('op'), name:'' ,width:100, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                h += "<a class='btn btn-blue' href='javascript:toEdit("+ item['goodsId']+",\"illegal\")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                h += "<a class='btn btn-blue' href='javascript:del(" + item['goodsId'] + ",\"illegal\")'><i class='fa fa-pencil'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',
        url: WST.U('shop/goods/illegalByPage'), fullWidthRows: true, autoLoad: false,checkCol:true,multiSelect:true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p)
}
function del(id,func){
	var c = WST.confirm({content:WST.lang('are_you_sure_to_delete'),yes:function(){
		layer.close(c);
		var load = WST.load({msg:WST.lang('loading')});
		$.post(WST.U('shop/goods/del'),{id:id},function(data,textStatus){
			layer.close(load);
		    var json = WST.toJson(data);
		    if(json.status==1){
		    	loadGrid(WST_CURR_PAGE);
		    }else{
		    	WST.msg(json.msg,{icon:2});
		    }
		});
	}});
}
function loadGrid(p){
    p = (p<=1)?1:p;
    mmg.load({cat1:$('#cat1').val(),cat2:$('#cat2').val(),goodsType:$('#goodsType').val(),goodsName:$('#goodsName').val(),page:p,goodsAttr:$('#goodsAttr').val()});
}
//商品编辑页返回
function toBack(p,src){
    p = (p<=1)?1:p;
    if(src){
        location.href = WST.U('shop/goods/'+src,'p='+p);
	}else{
        location.reload();
	}
}
// 批量 上架/下架
function changeSale(i,func){
    var rows = mmg.selectedRows();
    if(rows.length==0){
        WST.msg(WST.lang('please_select_the_item_to_modify'),{icon:2});
        return;
    }
    var ids = [];
    for(var s=0;s<rows.length;s++){
        ids.push(rows[s]['goodsId']);
    }
	var params = {};
	params.ids = ids;
	params.isSale = i;
	$.post(WST.U('shop/goods/changeSale'), params, function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status=='1'){
			WST.msg(WST.lang('op_ok'),{icon:1},(function(){
                loadGrid(WST_CURR_PAGE);
			}));
	    }else if(json.status=='-2'){
	    	WST.msg(json.msg, {icon: 5});
	    }else if(json.status=='2'){
	    	WST.msg(json.msg, {icon: 5},function(){
                loadGrid(WST_CURR_PAGE);
	    	});
	    }else if(json.status=='-3'){
	    	WST.msg(json.msg, {icon: 5,time:3000});
	    }else{
	    	WST.msg(WST.lang('op_err'), {icon: 5});
	    }
	});
}

// 批量设置 精品/新品/推荐/热销
function changeGoodsStatus(isWhat,func){
    var rows = mmg.selectedRows();
    if(rows.length==0){
        WST.msg(WST.lang('please_select_the_item_to_modify'),{icon:2});
        return;
    }
    var ids = [];
    for(var s=0;s<rows.length;s++){
        ids.push(rows[s]['goodsId']);
    }
	var params = {};
	params.ids = ids;
	params.is = isWhat;
	$.post(WST.U('shop/goods/changeGoodsStatus'),params,function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status=='1'){
			WST.msg(WST.lang('op_ok'),{icon:1},function(){
				   $('#all').prop('checked',false);
                loadGrid(WST_CURR_PAGE);
			});
		}else{
			WST.msg(WST.lang('op_err'),{icon:5});
		}
	});
}

// 双击设置
function changSaleStatus(isWhat, obj, id){
	var params = {};
	status = $(obj).attr('status');
	params.status = status;
	params.id = id;
	switch(isWhat){
	   case 'r':params.is = "isRecom";break;
	   case 'b':params.is = "isBest";break;
	   case 'n':params.is = "isNew";break;
	   case 'h':params.is = "isHot";break;
	}
	var load = WST.load({msg:WST.lang('loading')});
	$.post(WST.U('shop/goods/changSaleStatus'),params,function(data,textStatus){
		layer.close(load);
		var json = WST.toJson(data);
		if(json.status==1){
			if(status==0){
				$(obj).attr('status',1);
				$(obj).removeClass('wrong').addClass('right');
			}else{
				$(obj).attr('status',0);
				$(obj).removeClass('right').addClass('wrong');
			}
		}else{
			WST.msg(WST.lang('op_err'),{icon:5});
		}
	});
}

//双击修改
function toEditGoodsBase(fv,goodsId,flag){
	if((fv==2 || fv==3) && flag==1){
		WST.msg(WST.lang('goods_tips7'), {icon: 5});
		return;
	}else{
		$("#ipt_"+fv+"_"+goodsId).show();
		$("#span_"+fv+"_"+goodsId).hide();
		$("#ipt_"+fv+"_"+goodsId).focus();
		$("#ipt_"+fv+"_"+goodsId).val($("#span_"+fv+"_"+goodsId).html());
	}
}
function endEditGoodsBase(fv,goodsId){
	$('#span_'+fv+'_'+goodsId).html($('#ipt_'+fv+'_'+goodsId).val());
	$('#span_'+fv+'_'+goodsId).show();
    $('#ipt_'+fv+'_'+goodsId).hide();
}
function editGoodsBase(fv,goodsId){
    var vtext = $.trim($('#ipt_'+fv+'_'+goodsId).val());
	if(fv==2){
        if(vtext=='' || parseFloat(vtext,10)<=0){
        	WST.msg(WST.lang('price_must_be_greater_than0'), {icon: 5});
        	return;
        }
	}else if(fv==3){
        if(vtext=='' || parseInt(vtext,10)<0 || vtext.indexOf('.')>-1){
        	WST.msg(WST.lang('inventory_must_be_a_positive_integer'), {icon: 5});
        	return;
        }
	}
	var params = {};
	(fv==2)?params.shopPrice=vtext:params.goodsStock=vtext;
	params.goodsId = goodsId;
	$.post(WST.U('shop/Goods/editGoodsBase'),params,function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status>0){
			$('#img_'+fv+'_'+goodsId).fadeTo("fast",100);
			endEditGoodsBase(fv,goodsId);
			$('#img_'+fv+'_'+goodsId).fadeTo("slow",0);
		}else{
			WST.msg(json.msg, {icon: 5});
		}
	});
}

function benchDel(func,flag){
    var rows = mmg.selectedRows();
    if(rows.length==0){
        WST.msg(WST.lang('please_select_the_item_to_delete'),{icon:2});
        return;
    }
    var ids = [];
    for(var s=0;s<rows.length;s++){
        ids.push(rows[s]['goodsId']);
    }
	var params = {};
	params.ids = ids;
	var load = WST.load({msg:WST.lang('loading')});
	$.post(WST.U('shop/goods/batchDel'),params,function(data,textStatus){
		layer.close(load);
		var json = WST.toJson(data);
		if(json.status=='1'){
			WST.msg(WST.lang('op_ok'),{icon:1},function(){
				   $('#all').prop('checked',false);
                loadGrid(WST_CURR_PAGE);
			});
		}else{
			WST.msg(WST.lang('op_err'),{icon:5});
		}
	});
}

function getCat(val){
  if(val==''){
  	$('#cat2').html("<option value='' >-"+WST.lang('select')+"-</option>");
  	return;
  }
  $.post(WST.U('shop/shopcats/listQuery'),{parentId:val},function(data,textStatus){
       var json = WST.toJson(data);
       var html = [],cat;
       html.push("<option value='' >-"+WST.lang('select')+"-</option>");
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
function resetForm(){
	location.reload();
}



var goodsTotal,num=0,vtype=0,creatQrcodeCnt=0;
var goodsList = [];
var goodsDir = "";
var grqmap = [],errRqlist = [];
function toExplode(){

    var box = WST.open({title:WST.lang('export_product_QR_code'),type:1,content:layui.$('#exportBox'),area: ['400px', '180px'],btn:[WST.lang('confirm_export'),WST.lang('cancel')],yes:function(){
        vtype = $("#vtype").val();
        grqmap = [];
        errRqlist = [];
        var ids = [];
        var params = {};
        var rows = mmg.selectedRows();
	    if(rows.length>0){
	        for(var s=0;s<rows.length;s++){
		        ids.push(rows[s]['goodsId']);
		    }
		    params.ids = ids
	    }else{
	    	params = {cat1:$('#cat1').val(),cat2:$('#cat2').val(),goodsType:$('#goodsType').val(),goodsName:$('#goodsName').val(),goodsAttr:$('#goodsAttr').val()}
	    }


        var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
        $.post(WST.U('shop/goods/checkExportGoods'),params,function(data,textStatus){
                  layer.close(loading);
                  var json = WST.toJson(data);
                  if(json.status==1){
                        goodsList = json.data.glist;
                        goodsDir = json.data.gdir;
                        goodsTotal = goodsList.length;
                        for(var i in goodsList){
                            grqmap[goodsList[i]['goodsId']] = goodsList[i];
                        }
                        layer.close(box);
                        if(goodsTotal>0){
                            createGoodsQrcode();
                        }else{
                            WST.msg(WST.lang('data_not_exist'),{icon:1});
                        }
                  }else{
                        WST.msg(json.msg,{icon:2});
                  }
            });
        }
    });
}

function createGoodsQrcode(){
    var goodsId = goodsList[num].goodsId;
    WST.msg(WST.lang('goods_tips8', [(num+1), num,goodsTotal]));
    $.post(WST.U('shop/goods/createGoodsQrcode'),{vtype:vtype,goodsId:goodsId,dir:goodsDir},function(data,textStatus){
        var json = WST.toJson(data);

        if(json.status!=1){
            errRqlist.push("<div style='line-height:30px;padding:0 20px;color:red;'> "+WST.lang("goods_tips9", [grqmap[goodsId]["goodsSn"], json.msg])+"</div>");
        }else{
            creatQrcodeCnt++
        }
        if(num < goodsTotal-1){
            num++
            layer.closeAll();
            createGoodsQrcode();
            return;
        }else{
            num=0;
            if(creatQrcodeCnt>0){
                WST.msg(WST.lang('goods_tip10', [creatQrcodeCnt]) ,{icon:1});
                packageDownQrcode();
            }else{
                if(errRqlist.length>0){
                    WST.open({title:WST.lang('remind'),
                    type:1,
                    content:errRqlist.join(""),
                    area: ['600px', '400px'],
                    btn:[WST.lang('ok')],
                    yes:function(){layer.closeAll();}})
                }
                WST.msg(WST.lang('data_not_exist'),{icon:1});
            }
        }

    });
}


function packageDownQrcode(){
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('shop/goods/packageDownQrcode'),{dir:goodsDir},function(data,textStatus){
        var json = WST.toJson(data);
        if(json.status=='1'){
            layer.close(loading);
            if(errRqlist.length>0){
                WST.open({title:WST.lang('remind'),
                type:1,
                content:errRqlist.join(""),
                area: ['600px', '400px'],
                btn:[WST.lang('ok')],
                yes:function(){layer.closeAll();}})
            }

            window.location = window.conf.DOMAIN+"/"+json.data;
        }else{
            WST.msg(json.msg,{icon:2});
        }
    });
}





function getMouldList(goodsCatId){
	$.post(WST.U('shop/moulds/getMouldList'),{goodsCatId:goodsCatId},function(data,textStatus){
		$("#selMouldBox").show();
    	var json = WST.toJson(data);
    	var html = [];
    	if(json.data && json.data.length>0){
			$("#mouldCol1").show();
			$("#updateMould").show();
		}else{
			$("#mouldCol1").hide();
			$("#updateMould").hide();
		}
		if(json.status==1 && json.data){

			for(var i=0;i<json.data.length;i++){
				var obj = json.data[i];

				html.push('<div id="mouldItem'+obj['id']+'" class="mouldItem">');
					html.push('<div class="itemBox"  data="'+obj['id']+'">');
						html.push('<span class="mouldName" id="mTitle'+obj['id']+'">'+obj['mouldName']+' </span>');
					html.push('</div>');
					html.push('<i class="fa fa fa-trash-o del" onclick="delMould('+obj['id']+')"></i>');
					html.push('<i class="fa fa-edit edit" onclick="editMouldName('+obj['id']+')"></i>');
				html.push('</div>');
			}

		}
		$("#mouldItemBox").html(html.join(""));
		$(".itemBox").on({
			click : function(){
				var mouldId = $(this).attr("data");
				$("#mouldId").val(mouldId);
				$("#mouldItemBox").toggle();
				$("#mouldTitle").html($("#mTitle"+mouldId).html());
				getMould(mouldId);
			}
		});
 	});
}


function toUpdateMould(){
	var mouldId = $("#mouldId").val();
	saveMould(mouldId);
}

function editMouldName(mouldId){
	var mouldName = $("#mTitle"+mouldId).html();
	$("#mouldName").val(mouldName);
	if(mouldName==""){
		WST.msg(WST.lang('please_enter_a_template_name'));return;
	}
	var title =WST.lang('edit');
	var box = WST.open({title:title,type:1,content:$('#specMouldBox'),area: ['750px', '180px'],btn:[WST.lang('ok'),WST.lang('cancel')],
		end:function(){
			$('#specMouldBox').hide();
		},yes:function(){
			var params = {};
			params.mouldId = mouldId;
		    params.mouldName = $("#mouldName").val();
		    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		 	$.post(WST.U('shop/moulds/editMouldName'),params,function(data,textStatus){
		 		layer.close(loading);
		    	var json = WST.toJson(data);
				if(json.status=='1'){
					WST.msg(WST.lang('op_ok'),{icon:1});
					$('#specMouldBox').hide();
					$("#mouldItemBox").hide();
					var goodsCatId = WST.ITGetGoodsCatVal('j-goodsCats');
					getMouldList(goodsCatId);
					layer.close(box);
			  	}else{
			    	WST.msg(json.msg,{icon:2});
				}
		 	});
		}
	});
}


function delMould(mouldId){
	var c = WST.confirm({content:WST.lang('are_you_sure_to_delete'),yes:function(){
		layer.close(c);
		var load = WST.load({msg:WST.lang('loading')});
		$.post(WST.U('shop/moulds/del'),{mouldId:mouldId},function(data,textStatus){
			layer.close(load);
		    var json = WST.toJson(data);
		    if(json.status==1){
		    	var currMouldId = $("#mouldId").val();
		    	if(currMouldId==mouldId){
		    		$("#mouldId").val(0);
		    		$("#updateMould").hide();
		    		$("#mouldTitle").html(WST.lang('please_select_a_specification_property_template'));
		    	}
		    	$("#mouldItem"+mouldId).remove();
		    	$("#mouldItemBox").toggle();
		    	var goodsCatId = WST.ITGetGoodsCatVal('j-goodsCats');
		    	getMouldList(goodsCatId);
		    }else{
		    	WST.msg(json.msg,{icon:2});
		    }
		});
	}});
}
var mouldBox = null;
function toSaveMould(mouldId){
	$("#mouldName").val("");
	var title = WST.lang('add');
	mouldBox = WST.open({title:title,type:1,content:$('#specMouldBox'),area: ['750px', '180px'],btn:[WST.lang('ok'),WST.lang('cancel')],
		end:function(){
			$('#specMouldBox').hide();
		},yes:function(){
			saveMould(mouldId)
		}
	});

}

function saveMould(mouldId){

	var specsIds = [];
	var specidsmap = [];
	var params = WST.getParams('.j-mould');
	params.mouldId = mouldId;
	params.specNum = specNum;
	var specsName,specImg;
	$('.j-speccat').each(function(){
        for(var key in WST.conf.sysLangs){
		    specsName = 'specName_'+$(this).attr('cat')+'_'+$(this).attr('num')+'_'+WST.conf.sysLangs[key]['id'];
		    params[specsName] = $.trim($('#'+specsName).val());
        }
		specImg = 'specImg_'+$(this).attr('cat')+'_'+$(this).attr('num');
		params[specImg] = $.trim($('#'+specImg).attr('v'));

		specsIds.push($(this).attr('cat')+':'+$(this).attr('num'));
	});


	var specmap = [];
	for(var key in id2SepcNumConverter){
		specmap.push(key+":"+id2SepcNumConverter[key]);
	}
	params.specsIds = specsIds.join(',');
	params.specmap = specmap.join(',');
	var mouldName = $("#mouldName").val();
    params.mouldName = mouldName;
    params.goodsCatId = WST.ITGetGoodsCatVal('j-goodsCats');
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
 	$.post(WST.U('shop/moulds/addMould'),params,function(data,textStatus){
 		layer.close(loading);
    	var json = WST.toJson(data);
		if(json.status=='1'){
			WST.msg(WST.lang('op_ok'),{icon:1});
			layer.close(mouldBox);
			$("#mouldItemBox").hide();
			$("#mouldId").val(json.data);
			$("#mouldTitle").html(mouldName);
			var goodsCatId = WST.ITGetGoodsCatVal('j-goodsCats');
			getMouldList(goodsCatId);

	  	}else{
	    	WST.msg(json.msg,{icon:2});
		}
 	});
}

function getMould(mouldId){
	$('#specsAttrBox').empty();
	$('#specBtns').hide();
	specNum = 0;
	$.post(WST.U('shop/moulds/get'),{mouldId:mouldId},function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status==1 && json.data){
			loadMould(json)
		}

	});
}


function loadMould(json){
	var html = [],tmp,str;
	if(json.data.spec0 || json.data.spec1){
		html.push('<div class="spec-head">'+WST.lang('product_specifications')+'</div>');
		html.push('<div class="spec-body">');
		if(json.data.spec0){
			tmp = json.data.spec0;
			html.push('<div id="specCat_'+tmp.catId+'">'+tmp.catName+'</div>');
			html.push('<div id="specTby"></div>');
		}
		if(json.data.spec1){
			for(var i=0;i<json.data.spec1.length;i++){
				tmp = json.data.spec1[i];
				html.push('<div class="spec-line"></div>',
				          '<div id="specCat_'+tmp.catId+'">'+tmp.catName+'</div>',
				          '<div style="height:auto;">',
				          '<button type="button" class="btn btn-success" id="specAddBtn_'+tmp.catId+'" onclick="javascript:addSpec({catId:'+tmp.catId+',checked:\'\',langs:{}})"><i class="fa fa-plus"></i>'+ WST.lang('add')+'</button>',
				          '<div style="clear:both;"></div></div>'
						);
			}
		}
		html.push('</div>');
		html.push($('#specTips').html());
		html.push('<div id="specSaleHead" class="spec-head">'+WST.lang('sales_specifications')+'</div>',
		          '<table class="specs-sale-table">',
		          '  <thead id="spec-sale-hed">',
		          '   <tr>',
		          '     <th>'+WST.lang('recommend')+'<br/>'+WST.lang('specifications')+'</th>',
		          '     <th id="thCol"><font color="red">*</font>货号</th>',
		          '     <th><font color="red">*</font>'+WST.lang('market_price')+'<br/><input type="text" class="spec-sale-ipt" onblur="WST.limitDecimal(this,2);batchChange(this.value,\'marketPrice\')" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
		          '     <th><font color="red">*</font>'+WST.lang('our_price')+'<br/><input type="text" class="spec-sale-ipt" onblur="WST.limitDecimal(this,2);batchChange(this.value,\'specPrice\')" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
				 '     <th><font color="red">*</font>'+WST.lang('cost_price')+'<br/><input type="text" class="spec-sale-ipt" onblur="WST.limitDecimal(this,2);batchChange(this.value,\'costPrice\')" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
		          '     <th><font color="red">*</font>'+WST.lang('stock')+'<br/><input type="text" class="spec-sale-ipt" onblur="batchChange(this.value,\'specStock\')" onkeypress="return WST.isNumberKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
		          '     <th><font color="red">*</font>'+WST.lang('early_warning_inventory')+'<br/><input type="text" class="spec-sale-ipt" onblur="batchChange(this.value,\'warnStock\')" onkeypress="return WST.isNumberKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
		          '     <th><font color="red">*</font>'+WST.lang('commodity_weight')+'<br/><input type="text" class="spec-sale-ipt" onblur="batchChange(this.value,\'specWeight\')" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
		          '     <th><font color="red">*</font>'+WST.lang('commodity_volume')+'<br/><input type="text" class="spec-sale-ipt" onblur="batchChange(this.value,\'specVolume\')" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
		          '     <th>'+WST.lang('sales_volume')+'</th>',
		          '   </tr>',
		          '  </thead>',
		          '  <tbody id="spec-sale-tby"></tbody></table>'
				);
	}
	if(json.data.attrs){
		html.push('<div class="spec-head">'+WST.lang('commodity_attributes')+'</div>');
		html.push('<div class="spec-body">');
		html.push('<table class="attr-table">');
		for(var i=0;i<json.data.attrs.length;i++){
			tmp = json.data.attrs[i];
			html.push('<tr><th width="180" nowrap valign="top" style="padding-top:6px;">'+tmp.attrName+'：</th><td>');
			if(tmp.attrType==1){
				str = tmp.attrVal.split(',');
				html.push('<div id="attrlable_'+tmp.attrId+'" class="labelattr '+((str.length>12)?"labelhide":"")+'">');
				for(var j=0;j<str.length;j++){
				    html.push('<label><input type="checkbox" class="j-ipt j-mould" name="attr_'+tmp.attrId+'" value="'+str[j]+'"/>'+str[j]+'</label>');
				}
				html.push('</div>');
			    if(str.length>12)html.push('<a id="attra_'+tmp.attrId+'" href="javascript:showHideAttr('+tmp.attrId+')" class="labela"></a>');
			}else if(tmp.attrType==2){
				html.push('<select name="attr_'+tmp.attrId+'" id="attr_'+tmp.attrId+'" class="j-ipt j-mould">');
				html.push('<option value="">'+WST.lang('select')+'</option>');
				str = tmp.attrVal.split(',');
				for(var j=0;j<str.length;j++){
					html.push('<option value="'+str[j]+'">'+str[j]+'</option>');
				}
				html.push('</select>');
			}else{
                for(var lkey in WST.conf.sysLangs){
					html.push('<div><input type="text" name="attr_'+tmp.attrId+'_'+ WST.conf.sysLangs[lkey]['id']+'" id="attr_'+tmp.attrId+'_'+ WST.conf.sysLangs[lkey]['id']+'" class="spec-sale-text j-ipt j-mould" placeholder="'+WST.conf.sysLangs[lkey]['name']+'"/></div>');
				}
			}
			html.push('</td></tr>');
		}
		html.push('</table>');
		html.push('</div>');
	}
	$('#specsAttrBox').html(html.join(''));

	var specAttrObj = json.data.specAttrObj;


	//设置规格值
	if(specAttrObj.spec0){
		for(var i=0;i<specAttrObj.spec0.length;i++){
		   if(specAttrObj.spec0[i].catId==json.data.spec0.catId)addSpecImg({catId:specAttrObj.spec0[i].catId,checked:'checked',langs:specAttrObj.spec0[i].langParams,itemId:specAttrObj.spec0[i].itemId,specImg:specAttrObj.spec0[i].itemImg});
		}
	}
	if(specAttrObj.spec1){
		for(var i=0;i<specAttrObj.spec1.length;i++){
			for(var j=0;j<json.data.spec1.length;++j){
				if(specAttrObj.spec1[i].catId==json.data.spec1[j].catId){
		    		addSpec({catId:specAttrObj.spec1[i].catId,checked:'checked',langs:specAttrObj.spec1[i].langParams,itemId:specAttrObj.spec1[i].itemId});
					break;
				}
			}
		}
	}
	addSpecSaleCol();
	//设置商品属性值
	var tmp = null;
	if(specAttrObj.attrs){
		for(var i in specAttrObj.attrs){
			if(specAttrObj.attrs[i].attrType==1){
				tmp = specAttrObj.attrs[i]['langParams'][WST.conf.sysLangs[WST.conf.sysLang]['id']];
				tmp = tmp?tmp.split(','):'';
				WST.setValue("attr_"+specAttrObj.attrs[i].attrId,tmp);
			}else{
				if(specAttrObj.attrs[i].attrType==2){
					tmp = specAttrObj.attrs[i]['langParams'][WST.conf.sysLangs[WST.conf.sysLang]['id']];
                    WST.setValue("attr_"+specAttrObj.attrs[i].attrId,tmp = specAttrObj.attrs[i].attrVal);
				}else{
					for(var key in WST.conf.sysLangs){
	                    WST.setValue("attr_"+specAttrObj.attrs[i].attrId+'_'+WST.conf.sysLangs[key]['id'],specAttrObj.attrs[i]['langParams'][WST.conf.sysLangs[key]['id']]);
	                }
				}
			}
		}
	}


	//给没有初始化的规格初始化一个输入框
	if(json.data.spec0 && !$('.j-speccat_'+json.data.spec0.catId)[0]){
		addSpecImg({catId:json.data.spec0.catId,checked:'',langs:{}});
	}
	if(json.data.spec1){
		for(var i=0;i<json.data.spec1.length;i++){
			if(!$('.j-speccat_'+json.data.spec1[i].catId)[0])addSpec({catId:json.data.spec1[i].catId,checked:'',langs:[]});
		}
	}
	$('#specBtns').show();
}
