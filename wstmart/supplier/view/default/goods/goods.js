/**删除批量上传的图片**/
var mmg;
var form;
$(function(){
	form = layui.form;
	form.on('radio(isFreeShipping)', function(data){
	  if($(this).val()==1){
	  	$("#shippingFeeTypeTr").hide();
	  	$("#supplierExpressTr").hide();
	  }else{
	  	$("#shippingFeeTypeTr").show();
	  	$("#supplierExpressTr").show();
	  }
	});
});
function delBatchUploadImg(obj,fn){
	var c = WST.confirm({content:WST.lang('goods_confirm_del_goods_img'),yes:function(){
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
			var uploader = batchUpload({uploadPicker:'#batchUpload',uploadServer:WST.U('supplier/index/uploadPic'),formData:{dir:'goods',isWatermark:1,isThumb:1},uploadSuccess:function(file,response){
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
		      $('#uploadMsg').show().html(WST.lang('upload_rate')+rate+"%");
		  }
	});
	WST.upload({
	  	  pick:'#goodsVideoPicker',
	  	  formData: {dir:'goods'},
	  	  server:WST.U('supplier/index/uploadVideo'),
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
		      $('#uploadVideoMsg').show().html(WST.lang('upload_rate')+rate+"%");
		  }
	});
	KindEditor.ready(function(K) {
		for(var key in WST.conf.sysLangs){
		  K.create('#langParams'+WST.conf.sysLangs[key].id+'goodsDesc', {
			  height:'350px',
			  width:'800px',
			  uploadJson : WST.conf.ROOT+'/supplier/goods/editorUpload',
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
		getSuppliersCats('supplierCatId2',OBJ.supplierCatId1,OBJ.supplierCatId2);
	}
}
function clearVedio(obj){
	$(obj).hide();
	$('#goodsVideoPlayer').attr('src','');
	$('#goodsVideo').val('');
}
/**获取本店分类**/
function getSuppliersCats(objId,pVal,objVal){
	$('#'+objId).empty();
	$.post(WST.U('supplier/suppliercats/listQuery'),{parentId:pVal},function(data,textStatus){
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
	$.post(WST.U('supplier/brands/listQuery'),{catId:catId},function(data,textStatus){
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
	location.href = WST.U('supplier/goods/edit','id='+id+'&src='+src+'&p='+WST_CURR_PAGE);
}
function toAdd(src){
    location.href = WST.U('supplier/goods/add','src='+src);
}
/**保存商品数据**/
function save(p){
	var va = $("input[name='defaultSpec']:checked").val();
	if(va){
		$("#marketPrice").val($("#marketPrice_"+va).val());
		$("#supplierPrice").val( $("#specPrice_"+va).val());
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
				if($(this)[0].checked){
					params[specImg] = $.trim($('#'+specImg).attr('v'));
				}
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
			delete window['callback_checkWholesale'];
			params = checkWholesale(params);
			if(window['callback_checkWholesale']){
				window['callback_checkWholesale']();
				return;
			}
			var loading = WST.msg(WST.lang('submitting_data'), {icon: 16,time:60000});
		    $.post(WST.U('supplier/goods/'+((params.goodsId==0)?"toAdd":"toEdit")),params,function(data,textStatus){
		    	layer.close(loading);
		    	var json = WST.toJson(data);
		    	if(json.status=='1'){
		    		WST.msg(json.msg,{icon:1},function(){
						if(params.goodsType==1){
                            location.href=WST.U('supplier/goodsvirtuals/stock','id='+json.data.id);
						}else{
							location.href=WST.U('supplier/goods/'+src,"p="+p);
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
	html.push('</div>',
	          '<div class="item-del" onclick="delSpec(this,'+opts.catId+','+specNum+')"></div>',
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
	html.push('<div id="uploadMsg_'+opts.catId+'_'+specNum+'" style="float:left;padding: 5px;">',
	            (opts.specImg)?'<img height="25"  width="25" id="specImg_'+opts.catId+'_'+specNum+'" src="'+WST.conf.RESOURCE_PATH+"/"+opts.specImg+'" v="'+opts.specImg+'"/><span class="item-del" onclick="clearSpecImg(this,'+specNum+')"></span>':"",
	            '</div><div style="padding-left:5px;"><div id="specImgPicker_'+specNum+'" class="j-specImg" style="float:left">'+WST.lang('goods_upload_img')+'</div>'
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
		      $('#uploadMsg_'+this.cat+"_"+this.num).html(WST.lang('upload_rate')+rate+"%");
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
		$('#supplierPrice').removeAttr('disabled');
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
		$('#supplierPrice').prop('disabled',true);
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
		WST.msg(WST.lang('goods_product_no_tips'),{icon:2});
		obj.value = '';
	}
}
/**获取商品规格和属性**/
function getSpecAttrs(goodsCatId){
	$('#specsAttrBox').empty();
	$('#specBtns').hide();
	specNum = 0;
	$.post(WST.U('supplier/goods/getSpecAttrs'),{goodsCatId:goodsCatId,goodsType:$('#goodsType').val()},function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status==1 && json.data){
			var html = [],tmp,str;
			if(json.data.spec0 || json.data.spec1){
				html.push('<div class="spec-head">'+WST.lang('label_goods_spec')+'</div>');
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
				html.push('<div id="specSaleHead" class="spec-head">'+WST.lang('goods_sale_spec')+'</div>',
				          '<table class="specs-sale-table">',
				          '  <thead id="spec-sale-hed">',
				          '   <tr>',
				          '     <th>'+WST.lang('goods_is_recom')+'<br/>'+WST.lang('label_goods_spec')+'</th>',
				          '     <th id="thCol"><font color="red">*</font>'+WST.lang('label_product_no')+'</th>',
				          '     <th><font color="red">*</font>'+WST.lang('label_goods_market_price')+'<br/><input type="text" class="spec-sale-ipt" onblur="WST.limitDecimal(this,2);batchChange(this.value,\'marketPrice\')" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
				          '     <th><font color="red">*</font>'+WST.lang('goods_spec_price')+'<br/><input type="text" class="spec-sale-ipt" onblur="WST.limitDecimal(this,2);batchChange(this.value,\'specPrice\')" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
						'     <th><font color="red">*</font>'+WST.lang('label_cost_price')+'<br/><input type="text" class="spec-sale-ipt" onblur="WST.limitDecimal(this,2);batchChange(this.value,\'costPrice\')" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
				          '     <th><font color="red">*</font>'+WST.lang('label_goods_stock')+'<br/><input type="text" class="spec-sale-ipt" onblur="batchChange(this.value,\'specStock\')" onkeypress="return WST.isNumberKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
				          '     <th><font color="red">*</font>'+WST.lang('label_warn_stock')+'<br/><input type="text" class="spec-sale-ipt" onblur="batchChange(this.value,\'warnStock\')" onkeypress="return WST.isNumberKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
				          '     <th><font color="red">*</font>'+WST.lang('label_goods_weight')+'(kg)<br/><input type="text" class="spec-sale-ipt" onblur="batchChange(this.value,\'specWeight\')" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
				          '     <th><font color="red">*</font>'+WST.lang('label_goods_volume')+'(m³)<br/><input type="text" class="spec-sale-ipt" onblur="batchChange(this.value,\'specVolume\')" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)"></th>',
				          '     <th>'+WST.lang('label_goods_num')+'</th>',
				          '   </tr>',
				          '  </thead>',
				          '  <tbody id="spec-sale-tby"></tbody></table>'
						);
			}
			if(json.data.attrs){
				html.push('<div class="spec-head">'+WST.lang('label_goods_attr')+'</div>');
				html.push('<div class="spec-body">');
				html.push('<table class="attr-table">');
				for(var i=0;i<json.data.attrs.length;i++){
					tmp = json.data.attrs[i];
					html.push('<tr><th width="120" nowrap valign="top" style="padding-top:6px;">'+tmp.attrName+'：</th><td>');
					if(tmp.attrType==1){
						str = tmp.attrVal.split(',');
						html.push('<div id="attrlable_'+tmp.attrId+'" class="labelattr '+((str.length>12)?"labelhide":"")+'">');
						for(var j=0;j<str.length;j++){
						    html.push('<label><input type="checkbox" class="j-ipt" name="attr_'+tmp.attrId+'" value="'+str[j]+'"/>'+str[j]+'</label>');
						}
						html.push('</div>');
					    if(str.length>12)html.push('<a id="attra_'+tmp.attrId+'" href="javascript:showHideAttr('+tmp.attrId+')" class="labela">'+WST.lang('goods_more')+'</a>');
					}else if(tmp.attrType==2){
						html.push('<select name="attr_'+tmp.attrId+'" id="attr_'+tmp.attrId+'" class="j-ipt">');
						html.push('<option value="">'+WST.lang('select')+'</option>');
						str = tmp.attrVal.split(',');
						for(var j=0;j<str.length;j++){
							html.push('<option value="'+str[j]+'">'+str[j]+'</option>');
						}
						html.push('</select>');
					}else{
						for(var key in WST.conf.sysLangs){
						    html.push('<input type="text" name="attr_'+tmp.attrId+'_'+WST.conf.sysLangs[key]['id']+'" id="attr_'+tmp.attrId+'_'+WST.conf.sysLangs[key]['id']+'" class="spec-sale-text j-ipt" placeholder="'+ WST.conf.sysLangs[key]['name']+'"/>');
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
					console.log(OBJ.attrs);
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
						           WST.setValue("attr_"+OBJ.attrs[akey].attrId,OBJ.attrs[akey].attrVal);
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
            $('#attra_'+id).text(WST.lang('goods_hide'));
   	    })
   }else{
        obj.animate({height:'90px'},500,function(){
        	obj.addClass('labelhide');
        	$('#attra_'+id).text(WST.lang('goods_more'));
        })

   }
}
function changeGoodsType(v){
	if(v==0){
	    $('#goodsStockTr').show();
	    $('#goodsWeightTr').show();
	    $('#goodsVolumeTr').show();
	    $('#goodsStock').removeAttr('disabled');
    }else{
    	$('#goodsStockTr').hide();
    	$('#goodsWeightTr').hide();
	    $('#goodsVolumeTr').hide();
    	$('#goodsStock').prop('disabled',true);
    }
    var goodsCatId =WST.ITGetGoodsCatVal('j-goodsCats');
    getSpecAttrs(goodsCatId);
}
function toDetail(goodsId,key){
    //window.open(WST.U('supplier/goods/detail','goodsId='+goodsId+"&key="+key));
}
function checkCopyShops(goodsId,src){
    location.href = WST.U('supplier/Suppliergoodscopyrelates/shopIndex','goodsId='+goodsId+'&src='+src+'&p='+WST_CURR_PAGE);
}
function toolTip(){
    WST.toolTip();
}
function saleByPage(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('label_goods_img'), name:'goodsName', width: 80, renderer: function(val,item,rowIndex){
                var thumb = item['goodsImg'];
	        	thumb = thumb.replace('.','_thumb.');
            	return "<span class='weixin'><a style='color:blue' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb
            	+"'></a><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></span>";
        }},
        {title:WST.lang('label_goods_name'), name:'goodsName', width: 250, renderer: function(val,item,rowIndex){
        	return "<a style='color:#666' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'>"+val+"</a> ";

        }},
        {title:WST.lang('label_goods_sn'), name:'goodsSn', width: 100},
        {title:WST.lang('label_price')+'('+WST.lang('currency_symbol')+')', name:'supplierPrice', width: 50},
        {title:WST.lang('goods_is_recom'), name:'isRecom', width: 30,renderer:function(val,item,rowIndex){
                if(item['isRecom']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('no')+"</span>";
                }
            }},
        {title:WST.lang('goods_is_best'), name:'isBest', width: 30,renderer:function(val,item,rowIndex){
                if(item['isBest']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('no')+"</span>";
                }
            }},
        {title:WST.lang('goods_is_new'), name:'isNew', width: 30,renderer:function(val,item,rowIndex){
                if(item['isNew']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('no')+"</span>";
                }
            }},
        {title:WST.lang('goods_is_hot'), name:'isHot', width: 30,renderer:function(val,item,rowIndex){
                if(item['isHot']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('no')+"</span>";
                }
            }},
        {title:WST.lang('label_goods_num'), name:'saleNum', width: 40},
        {title:WST.lang('label_goods_stock'), name:'goodsStock', width: 40},
        {title:WST.lang('op'), name:'' ,width:200, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                h += "<a class='btn btn-blue' href='javascript:checkCopyShops("+ item['goodsId']+",\"sale\")'><i class='fa fa-search'></i>"+WST.lang('goods_view_distribution_shop')+"</a> ";
                h += "<a class='btn btn-blue' href='javascript:toEdit("+ item['goodsId']+",\"sale\")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                h += "<a class='btn btn-red' href='javascript:del(" + item['goodsId'] + ",\"sale\")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
    ];

    mmg = $('.mmg').mmGrid({height: h-150,indexCol: true, cols: cols,method:'POST',
        url: WST.U('supplier/goods/saleByPage'), fullWidthRows: true, autoLoad: false,checkCol:true,multiSelect:true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function auditByPage(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('label_goods_img'), name:'goodsName', width: 80, renderer: function(val,item,rowIndex){
                var thumb = item['goodsImg'];
	        	thumb = thumb.replace('.','_thumb.');
            	return "<span class='weixin'><a style='color:blue' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb
            	+"'></a><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></span>";
        }},
        {title:WST.lang('label_goods_name'), name:'goodsName', width: 250, renderer: function(val,item,rowIndex){
        	return "<a style='color:#666' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'>"+val+"</a> ";

        }},
        {title:WST.lang('label_goods_sn'), name:'goodsSn', width: 200},
        {title:WST.lang('label_price')+'('+WST.lang('currency_symbol')+')', name:'supplierPrice', width: 30},
        {title:WST.lang('goods_is_recom'), name:'isRecom', width: 30,renderer:function(val,item,rowIndex){
                if(item['isRecom']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('no')+"</span>";
                }
            }},
        {title:WST.lang('goods_is_best'), name:'isBest', width: 30,renderer:function(val,item,rowIndex){
                if(item['isBest']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('no')+"</span>";
                }
            }},
        {title:WST.lang('goods_is_new'), name:'isNew', width: 30,renderer:function(val,item,rowIndex){
                if(item['isNew']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('no')+"</span>";
                }
            }},
        {title:WST.lang('goods_is_hot'), name:'isHot', width: 30,renderer:function(val,item,rowIndex){
                if(item['isHot']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('no')+"</span>";
                }
            }},
        {title:WST.lang('label_goods_num'), name:'saleNum', width: 30},
        {title:WST.lang('label_goods_stock'), name:'goodsStock', width: 30},
        {title:WST.lang('op'), name:'' ,width:180, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                h += "<a class='btn btn-blue' href='javascript:checkCopyShops("+ item['goodsId']+",\"audit\")'><i class='fa fa-search'></i>"+WST.lang('goods_view_distribution_shop')+"</a> ";
                h += "<a class='btn btn-blue' href='javascript:toEdit("+ item['goodsId']+",\"audit\")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                h += "<a class='btn btn-red' href='javascript:del(" + item['goodsId'] + ",\"audit\")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
    ];

    mmg = $('.mmg').mmGrid({height: h-150,indexCol: true, cols: cols,method:'POST',
        url: WST.U('supplier/goods/auditByPage'), fullWidthRows: true, autoLoad: false,checkCol:true,multiSelect:true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p)

}
function storeByPage(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('label_goods_img'), name:'goodsName', width: 80, renderer: function(val,item,rowIndex){
                var thumb = item['goodsImg'];
	        	thumb = thumb.replace('.','_thumb.');
            	return "<span class='weixin'><a style='color:blue' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb
            	+"'></a><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></span>";
        }},
        {title:WST.lang('label_goods_name'), name:'goodsName', width: 250, renderer: function(val,item,rowIndex){
        	return "<a style='color:#666' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'>"+val+"</a> ";

        }},
        {title:WST.lang('label_goods_sn'), name:'goodsSn', width: 100},
        {title:WST.lang('label_price')+'('+WST.lang('currency_symbol')+')', name:'supplierPrice', width: 50},
        {title:WST.lang('goods_is_recom'), name:'isRecom', width: 30,renderer:function(val,item,rowIndex){
                if(item['isRecom']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('no')+"</span>";
                }
            }},
        {title:WST.lang('goods_is_best'), name:'isBest', width: 30,renderer:function(val,item,rowIndex){
                if(item['isBest']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('no')+"</span>";
                }
            }},
        {title:WST.lang('goods_is_new'), name:'isNew', width: 30,renderer:function(val,item,rowIndex){
                if(item['isNew']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('no')+"</span>";
                }
            }},
        {title:WST.lang('goods_is_hot'), name:'isHot', width: 30,renderer:function(val,item,rowIndex){
                if(item['isHot']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('no')+"</span>";
                }
            }},
        {title:WST.lang('label_goods_num'), name:'saleNum', width: 50},
        {title:WST.lang('label_goods_stock'), name:'goodsStock', width: 50},
        {title:WST.lang('op'), name:'' ,width:210, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                h += "<a class='btn btn-blue' href='javascript:checkCopyShops("+ item['goodsId']+",\"store\")'><i class='fa fa-search'></i>"+WST.lang('goods_view_distribution_shop')+"</a> ";
                h += "<a class='btn btn-blue' href='javascript:toEdit("+ item['goodsId']+",\"store\")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                h += "<a class='btn btn-red' href='javascript:del(" + item['goodsId'] + ",\"store\")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
    ];

    mmg = $('.mmg').mmGrid({height: h-150,indexCol: true, cols: cols,method:'POST',
        url: WST.U('supplier/goods/storeByPage'), fullWidthRows: true, autoLoad: false,checkCol:true,multiSelect:true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p)
}
function illegalByPage(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('label_goods_img'), name:'goodsName', width: 80, renderer: function(val,item,rowIndex){
                var thumb = item['goodsImg'];
	        	thumb = thumb.replace('.','_thumb.');
            	return "<span class='weixin'><a style='color:blue' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb
            	+"'></a><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></span>";
        }},
        {title:WST.lang('label_goods_name'), name:'goodsName', width: 100, renderer: function(val,item,rowIndex){
        	return "<a style='color:#666' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'>"+val+"</a> ";

        }},
        {title:WST.lang('label_goods_sn'), name:'goodsSn', width: 100},
        {title:WST.lang('label_goods_illegal_remark'), name:'illegalRemarks', width: 300},
        {title:WST.lang('op'), name:'' ,width:100, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                h += "<a class='btn btn-blue' href='javascript:checkCopyShops("+ item['goodsId']+",\"illegal\")'><i class='fa fa-search'></i>"+WST.lang('goods_view_distribution_shop')+"</a> ";
                h += "<a class='btn btn-blue' href='javascript:toEdit("+ item['goodsId']+",\"illegal\")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                h += "<a class='btn btn-blue' href='javascript:del(" + item['goodsId'] + ",\"illegal\")'><i class='fa fa-pencil'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',
        url: WST.U('supplier/goods/illegalByPage'), fullWidthRows: true, autoLoad: false,checkCol:true,multiSelect:true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p)
}
function del(id,func){
	var c = WST.confirm({content:WST.lang('goods_confirm_delete'),yes:function(){
		layer.close(c);
		var load = WST.load({msg:WST.lang('loading')});
		$.post(WST.U('supplier/goods/del'),{id:id},function(data,textStatus){
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
        location.href = WST.U('supplier/goods/'+src,'p='+p);
	}else{
        location.reload();
	}
}
// 批量 上架/下架
function changeSale(i,func){
    var rows = mmg.selectedRows();
    if(rows.length==0){
        WST.msg(WST.lang('select_edit_goods'),{icon:2});
        return;
    }
    var ids = [];
    for(var s=0;s<rows.length;s++){
        ids.push(rows[s]['goodsId']);
    }
	var params = {};
	params.ids = ids;
	params.isSale = i;
	$.post(WST.U('supplier/goods/changeSale'), params, function(data,textStatus){
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
        WST.msg(WST.lang('select_edit_goods'),{icon:2});
        return;
    }
    var ids = [];
    for(var s=0;s<rows.length;s++){
        ids.push(rows[s]['goodsId']);
    }
	var params = {};
	params.ids = ids;
	params.is = isWhat;
	$.post(WST.U('supplier/goods/changeGoodsStatus'),params,function(data,textStatus){
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
	var load = WST.load({msg:WST.lang('submitting_data')});
	$.post(WST.U('supplier/goods/changSaleStatus'),params,function(data,textStatus){
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
		WST.msg(WST.lang('goods_edit_attr_tips'), {icon: 5});
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
        	WST.msg(WST.lang('label_goods_price_tips'), {icon: 5});
        	return;
        }
	}else if(fv==3){
        if(vtext=='' || parseInt(vtext,10)<0 || vtext.indexOf('.')>-1){
        	WST.msg(WST.lang('goods_stock_tips'), {icon: 5});
        	return;
        }
	}
	var params = {};
	(fv==2)?params.supplierPrice=vtext:params.goodsStock=vtext;
	params.goodsId = goodsId;
	$.post(WST.U('supplier/Goods/editGoodsBase'),params,function(data,textStatus){
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
        WST.msg(WST.lang('goods_delete_tips'),{icon:2});
        return;
    }
    var ids = [];
    for(var s=0;s<rows.length;s++){
        ids.push(rows[s]['goodsId']);
    }
	var params = {};
	params.ids = ids;
	var load = WST.load({msg:WST.lang('submitting_data')});
	$.post(WST.U('supplier/goods/batchDel'),params,function(data,textStatus){
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
  $.post(WST.U('supplier/suppliercats/listQuery'),{parentId:val},function(data,textStatus){
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
