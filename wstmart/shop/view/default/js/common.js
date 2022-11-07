/**WST.GRANR--权限函数，保留，请勿覆盖**/
$(function() {
    $('.goodsImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 100,placeholder:window.conf.RESOURCE_PATH+'/'+window.conf.GOODS_LOGO});//商品默认图片
    $('.shopsImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 100,placeholder:window.conf.RESOURCE_PATH+'/'+window.conf.SHOP_LOGO});//店铺默认头像
    $('.usersImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 100,placeholder:window.conf.RESOURCE_PATH+'/'+window.conf.USER_LOGO});//会员默认头像
});
WST.tips = function(content, selector, options){
	var opts = {};
	opts = $.extend(opts, {tips:1, time:2000, maxWidth: 260}, options);
	return layer.tips(content, selector, opts);
}
WST.open = function(options){
	var opts = {};
	opts = $.extend(opts, {offset:'200px'}, options);
	return layer.open(opts);
}
WST.confirm = function(options){
	var opts = {};
	opts = $.extend(opts, {title:WST.lang('sys_tips'),offset:'200px'}, options);
	return layer.confirm(opts.content,{icon: 3, title:opts.title,offset:opts.offset},options.yes,options.cancel);
}
WST.msg = function(msg, options, func){
	var opts = {};
	if(options){
		if(options.icon==1){
			options.icon='wst1';
			options.time = 1000;
		}else if(options.icon==2 || options.icon==5){
			options.icon='wst2';
		}else if(options.icon==3){
			options.icon='wst3';
		}else if(options.icon==16){
			options.icon='wstloading';
			options.time = 0;
		}
	}
	//有抖動的效果,第二位是函數
	if(typeof(options)!='function'){
		opts = $.extend(opts,{time:2000,shade: [0.4, '#000000'],offset:'200px'},options);
		return layer.msg(msg,opts, func);
	}else{
		return layer.msg(msg, options);
	}
}
WST.pageSizeOptions = [50,100,150,200];
WST.pageSize = 50;
WST.toJson = function(str){
    var json = {};
    try{
        if(typeof(str )=="object"){
            json = str;
        }else{
            json = eval("("+str+")");
        }
        if(json.status && json.status=='-999'){
            WST.msg(WST.lang('login_out'),{icon:5},function(){
                if(window.parent){
                    window.parent.location.reload();
                }else{
                    location.reload();
                }
            });
        }else if(json.status && json.status=='-998'){
            WST.msg(WST.lang('no_privelege'));
            return;
        }
    }catch(e){
        WST.msg(WST.lang('sys_error')+e.getMessage,{icon:5});
        json = {};
    }
    return json;
}
WST.upload = function(opts){
	var _opts = {};
	_opts = $.extend(_opts,{duplicate:true,auto: true,swf: WST.conf.STATIC +'/plugins/webuploader/Uploader.swf',server:WST.U('shop/index/uploadPic'),duplicate:true},opts);
	var uploader = WebUploader.create(_opts);
	uploader.on('uploadSuccess', function( file,response ) {
	    var json = WST.toJson(response._raw);
	    if(_opts.callback)_opts.callback(json,file);
	});
	uploader.on('uploadError', function( file ) {
		if(_opts.uploadError)_opts.uploadError();
	});
	uploader.on( 'uploadProgress', function( file, percentage ) {
		percentage = parseFloat(percentage.toFixed(2),10)*100;
		if(_opts.progress)_opts.progress(percentage);
	});
    return uploader;
}
WST.getVerify = function(id){
    $(id).attr('src',WST.U('shop/index/getVerify','rnd='+Math.random()));
}
WST.getAreas = function(obj,id,val,fval,callback){
	var params = {};
	params.parentId = id;
	$("#"+obj).empty();
	$("#"+obj).html("<option value=''>"+WST.lang('select')+"</option>");
	var s = [];
	if(fval!=''){
		s = fval.split(',');
		for(var i=0;i<s.length;i++){
			$("#"+s[i]).empty();
			$("#"+s[i]).html("<option value=''>"+WST.lang('select')+"</option>");
		}
	}
	if(id == 0 || id == ''){
		s = fval.split(',');
		for(var i=0;i<s.length;i++){
			$("#"+s[i]).empty();
			$("#"+s[i]).html("<option value=''>"+WST.lang('select')+"</option>");
		}
		return;
	}
	$.post(WST.U('shop/areas/listQuery'),params,function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status==1 && json.data){
			var opts,html=[];
			html.push("<option value=''>"+WST.lang('select')+"</option>");
			for(var i=0;i<json.data.length;i++){
				opts = json.data[i];
				html.push('<option value="'+opts.areaId+'" '+((val==opts.areaId)?'selected':'')+'>'+opts.areaName+'</option>');
			}
			$("#"+obj).html(html.join(''));
			if(typeof(callback)=='function')callback();
		}
	});
}
$(function(){
	if(WST.conf && WST.conf.GRANT)WST.getGrants(WST.conf.GRANT);
	WST.hidePageLoading();
})
WST.getGrants = function(grant){
	WST['GRANT'] = {};
	if(!grant)return;
	var str = grant.split(',');
	for(var i=0;i<str.length;i++){
		WST['GRANT'][str[i]] = true;
	}
}
/**
 * 把对象变成数组
 */
WST.arrayParams = function(v){
	var p = WST.getParams(v);
	var params = [];
	for(var key in p){
		params.push(key+"="+p[key]);
	}
	return params;
}
/**
 * 循环调用及设置商品分类
 * @param id           当前分类ID
 * @param val          当前分类值
 * @param childIds     分类路径值【数组】
 * @param isRequire    是否要求必填
 * @param className    样式，方便将来获取值
 * @param beforeFunc   运行前回调函数
 * @param afterFunc    运行后回调函数
 */
WST.ITSetGoodsCats = function(opts){
	var obj = $('#'+opts.id);
	obj.attr('lastgoodscat',1);
	var level = $('#'+opts.id).attr('level')?(parseInt($('#'+opts.id).attr('level'),10)+1):1;
	if(opts.childIds.length>0){
		opts.childIds.shift();
		if(opts.beforeFunc){
			if(typeof(opts.beforeFunc)=='function'){
				opts.beforeFunc({id:opts.id,val:opts.val});
			}else{
			   var fn = window[opts.beforeFunc];
			   fn({id:opts.id,val:opts.val});
			}
		}
		$.post(WST.U('shop/goodscats/listQuery'),{parentId:opts.val},function(data,textStatus){
		     var json = WST.toJson(data);
		     if(json.data && json.data.length>0){
			     json = json.data;
		         var html = [];
		         var tid = opts.id+"_"+opts.val;
		         html.push("<select id='"+tid+"' level='"+level+"' class='"+opts.className+"' "+(opts.isRequire?" data-rule='required;' ":"")+">");
			     html.push("<option value=''>-"+WST.lang('select')+"-</option>");
			     for(var i=0;i<json.length;i++){
			       	 var cat = json[i];
			       	 html.push("<option value='"+cat.catId+"' "+((opts.childIds[0]==cat.catId)?"selected":"")+">"+cat.catName+"</option>");
			     }
			     html.push('</select>');
			     $(html.join('')).insertAfter(obj);
			     var tidObj = $('#'+tid);
			     if(tidObj.val()!=''){
			    	obj.removeAttr('lastgoodscat');
			    	tidObj.attr('lastgoodscat',1);
				    opts.id = tid;
				    opts.val = tidObj.val();
				    WST.ITSetGoodsCats(opts);
				 }
			     tidObj.change(function(){
				    opts.id = tid;
				    opts.val = $(this).val();
				    WST.ITGoodsCats(opts);
				 })
		     }else{
		    	 opts.isLast = true;
		    	 opts.lastVal = opts.val;
		     }
		     if(opts.afterFunc){
		    	 if(typeof(opts.afterFunc)=='function'){
		    		 opts.afterFunc(opts);
		    	 }else{
		    	     var fn = window[opts.afterFunc];
		    	     fn(opts);
		    	 }
		     }
		});
	}
}

/**
 * 循环创建商品分类
 * @param id            当前分类ID
 * @param val           当前分类值
 * @param className     样式，方便将来获取值
 * @param isRequire     是否要求必填
 * @param beforeFunc    运行前回调函数
 * @param afterFunc     运行后回调函数
 */
WST.ITGoodsCats = function(opts){
	opts.className = opts.className?opts.className:"j-goodsCats";
	var obj = $('#'+opts.id);
	obj.attr('lastgoodscat',1);
	var level = parseInt(obj.attr('level'),10)+1;
	$("select[id^='"+opts.id+"_']").remove();
	if(opts.isRequire)$('.msg-box[for^="'+opts.id+'_"]').remove();
	if(opts.beforeFunc){
		if(typeof(opts.beforeFunc)=='function'){
			opts.beforeFunc({id:opts.id,val:opts.val});
		}else{
		   var fn = window[opts.beforeFunc];
		   fn({id:opts.id,val:opts.val});
		}
	}
	opts.lastVal = opts.val;
	if(opts.val==''){
		obj.removeAttr('lastgoodscat');
		var lastId = 0,level = 0,tmpLevel = 0,lasObjId;
		$('.'+opts.className).each(function(){
			tmpLevel = parseInt($(this).attr('level'),10);
			if(level <= tmpLevel && $(this).val()!=''){
				level = tmpLevel;
				lastId = $(this).val();
				lasObjId = $(this).attr('id');
			}
		})
		$('#'+lasObjId).attr('lastgoodscat',1);
		opts.id = lasObjId;
    	opts.val = $('#'+lasObjId).val();
	    opts.isLast = true;
	    opts.lastVal = opts.val;
		if(opts.afterFunc){
			if(typeof(opts.afterFunc)=='function'){
				opts.afterFunc(opts);
			}else{
	    	    var fn = window[opts.afterFunc];
	    	    fn(opts);
			}
	    }
		return;
	}
	$.post(WST.U('shop/goodscats/listQuery'),{parentId:opts.val},function(data,textStatus){
	     var json = WST.toJson(data);
	     if(json.data && json.data.length>0){
	    	json = json.data;
	        var html = [];
	        var tid = opts.id+"_"+opts.val;
	        html.push("<select id='"+tid+"' level='"+level+"' class='"+opts.className+"' "+(opts.isRequire?" data-rule='required;' ":"")+">");
		    html.push("<option value='' >-"+WST.lang('select')+"-</option>");
		    for(var i=0;i<json.length;i++){
		       	 var cat = json[i];
		       	 html.push("<option value='"+cat.catId+"'>"+cat.catName+"</option>");
		    }
		    html.push('</select>');
		    $(html.join('')).insertAfter(obj);
		    $("#"+tid).change(function(){
		    	opts.id = tid;
		    	opts.val = $(this).val();
		    	if(opts.val!=''){
		    		obj.removeAttr('lastgoodscat');
		    	}
		    	WST.ITGoodsCats(opts);
		    })
	     }else{
	    	 opts.isLast = true;
	    	 opts.lastVal = opts.val;
	     }
	     if(opts.afterFunc){
	    	 if(typeof(opts.afterFunc)=='function'){
	    		 opts.afterFunc(opts);
	    	 }else{
	    	     var fn = window[opts.afterFunc];
	    	     fn(opts);
	    	 }
	     }
	});
}
/**
 * 获取最后已选分类的id
 */
WST.ITGetAllGoodsCatVals = function(srcObj,className){
	var goodsCatId = '';
	$('.'+className).each(function(){
		if($(this).attr('lastgoodscat')=='1')goodsCatId = $(this).attr('id')+'_'+$(this).val();
	});
	goodsCatId = goodsCatId.replace(srcObj+'_','');
	return goodsCatId.split('_');
}
/**
 * 获取最后分类值
 */
WST.ITGetGoodsCatVal = function(className){
	var goodsCatId = '';
	$('.'+className).each(function(){
		if($(this).attr('lastgoodscat')=='1')goodsCatId = $(this).val();
	});
	return goodsCatId;
}
/********************* 选项卡切换隐藏 **********************/
$.fn.TabPanel = function(options){
    var defaults = {tab: 0};
    var opts = $.extend(defaults, options);
    var t = this;

    $(t).find('.wst-tab-nav li').click(function(){
        $(this).addClass("on").siblings().removeClass();
        var index = $(this).index();
        $(t).find('.wst-tab-content .wst-tab-item').eq(index).show().siblings().hide();
        if(opts.callback)opts.callback(index);
    });
    $(t).find('.wst-tab-nav li').eq(opts.tab).click();
}
/**
 * 循环创建地区
 * @param id            当前分类ID
 * @param val           当前分类值
 * @param className     样式，方便将来获取值
 * @param isRequire     是否要求必填
 * @param beforeFunc    运行前回调函数
 * @param afterFunc     运行后回调函数
 */
WST.ITAreas = function(opts){
	opts.className = opts.className?opts.className:"j-areas";
	var obj = $('#'+opts.id);
	obj.attr('lastarea',1);
	var level = parseInt(obj.attr('level'),10)+1;
	$("select[id^='"+opts.id+"_']").remove();
	if(opts.isRequire)$('.msg-box[for^="'+opts.id+'_"]').remove();
	if(opts.beforeFunc){
		if(typeof(opts.beforeFunc)=='function'){
			opts.beforeFunc({id:opts.id,val:opts.val});
		}else{
		   var fn = window[opts.beforeFunc];
		   fn({id:opts.id,val:opts.val});
		}
	}
	opts.lastVal = opts.val;
	if(opts.val==''){
		obj.removeAttr('lastarea');
		var lastId = 0,level = 0,tmpLevel = 0,lasObjId;
		$('.'+opts.className).each(function(){
			tmpLevel = parseInt($(this).attr('level'),10);
			if(level <= tmpLevel && $(this).val()!=''){
				level = tmpLevel;
				lastId = $(this).val();
				lasObjId = $(this).attr('id');
			}
		})
		$('#'+lasObjId).attr('lastarea',1);
		opts.id = lasObjId;
    	opts.val = $('#'+lasObjId).val();
	    opts.isLast = true;
	    opts.lastVal = opts.val;
		if(opts.afterFunc){
			if(typeof(opts.afterFunc)=='function'){
				opts.afterFunc(opts);
			}else{
	    	    var fn = window[opts.afterFunc];
	    	    fn(opts);
			}
	    }
		return;
	}
	$.post(WST.U('shop/areas/listQuery'),{parentId:opts.val},function(data,textStatus){
	     var json = WST.toJson(data);
	     if(json.data && json.data.length>0){
	    	 json = json.data;
	         var html = [],tmp;
	         var tid = opts.id+"_"+opts.val;
	         html.push("<select id='"+tid+"' level='"+level+"' class='"+opts.className+"' "+(opts.isRequire?" data-rule='required;' ":"")+">");
		     html.push("<option value='' >-"+WST.lang('select')+"-</option>");
		     for(var i=0;i<json.length;i++){
		    	 tmp = json[i];
		       	 html.push("<option value='"+tmp.areaId+"'>"+tmp.areaName+"</option>");
		     }
		     html.push('</select>');
		     $(html.join('')).insertAfter(obj);
		     $("#"+tid).change(function(){
		    	opts.id = tid;
		    	opts.val = $(this).val();
		    	if(opts.val!=''){
		    		obj.removeAttr('lastarea');
		    	}
		    	WST.ITAreas(opts);
		     })
	     }else{
	    	 opts.isLast = true;
	    	 opts.lastVal = opts.val;
	     }
	     if(opts.afterFunc){
	    	 if(typeof(opts.afterFunc)=='function'){
	    		 opts.afterFunc(opts);
	    	 }else{
	    	     var fn = window[opts.afterFunc];
	    	     fn(opts);
	    	 }
	     }
	});
}
/**
 * 循环调用及设置地区
 * @param id           当前地区ID
 * @param val          当前地区值
 * @param childIds     地区路径值【数组】
 * @param isRequire    是否要求必填
 * @param className    样式，方便将来获取值
 * @param beforeFunc   运行前回调函数
 * @param afterFunc    运行后回调函数
 */
WST.ITSetAreas = function(opts){
	var obj = $('#'+opts.id);
	obj.attr('lastarea',1);
	var level = $('#'+opts.id).attr('level')?(parseInt($('#'+opts.id).attr('level'),10)+1):1;
	if(opts.childIds.length>0){
		opts.childIds.shift();
		if(opts.beforeFunc){
			if(typeof(opts.beforeFunc)=='function'){
				opts.beforeFunc({id:opts.id,val:opts.val});
			}else{
			   var fn = window[opts.beforeFunc];
			   fn({id:opts.id,val:opts.val});
			}
		}
		$.post(WST.U('shop/areas/listQuery'),{parentId:opts.val},function(data,textStatus){
		     var json = WST.toJson(data);
		     if(json.data && json.data.length>0){
		    	 json = json.data;
		         var html = [],tmp;
		         var tid = opts.id+"_"+opts.val;
		         html.push("<select id='"+tid+"' level='"+level+"' class='"+opts.className+"' "+(opts.isRequire?" data-rule='required;' ":"")+">");
			     html.push("<option value=''>-"+WST.lang('select')+"-</option>");
			     for(var i=0;i<json.length;i++){
			    	 tmp = json[i];
			       	 html.push("<option value='"+tmp.areaId+"' "+((opts.childIds[0]==tmp.areaId)?"selected":"")+">"+tmp.areaName+"</option>");
			     }
			     html.push('</select>');
			     $(html.join('')).insertAfter(obj);
			     var tidObj = $('#'+tid);
			     if(tidObj.val()!=''){
			    	obj.removeAttr('lastarea');
			    	tidObj.attr('lastarea',1);
				    opts.id = tid;
				    opts.val = tidObj.val();
				    WST.ITSetAreas(opts);
				 }
			     tidObj.change(function(){
				    opts.id = tid;
				    opts.val = $(this).val();
				    WST.ITAreas(opts);
				 })
		     }else{
		    	 opts.isLast = true;
		    	 opts.lastVal = opts.val;
		     }
		     if(opts.afterFunc){
		    	 if(typeof(opts.afterFunc)=='function'){
		    		 opts.afterFunc(opts);
		    	 }else{
		    	     var fn = window[opts.afterFunc];
		    	     fn(opts);
		    	 }
		     }
		});
	}
}
/**
 * 获取最后地区的值
 */
WST.ITGetAreaVal = function(className){
	var areaId = '';
	$('.'+className).each(function(){
		if($(this).attr('lastarea')=='1')areaId = $(this).val();
	});
	return areaId;
}
/**
 * 获取最后已选分类的id
 */
WST.ITGetAllAreaVals = function(srcObj,className){
	var areaId = '';
	$('.'+className).each(function(){
		if($(this).attr('lastarea')=='1')areaId = $(this).attr('id')+'_'+$(this).val();
	});
	areaId = areaId.replace(srcObj+'_','');
	return areaId.split('_');
}

WST.hidePageLoading = function(){
	$(window).load(function () {
        window.setTimeout(function () {
            $('#j-loader').fadeOut();
        }, 10);
    });
}

$.fn.WSTTips = function(opts){
	var obj = this;
	var pobj = obj.parent();
	var isShow = true;
	var log = {w:pobj.width(),h:pobj.height()};
	$(document.body).css('overflow-y','hidden');
	obj.click(function(){
		if(isShow){
            pobj.animate({height:opts.height,width:opts.width},300,function(){
            	isShow = false;
            	if(opts.callback)opts.callback(isShow);
            });
		}else{
			pobj.animate({width:'99%'},300,function(){
                pobj.animate({height:'100%'},100,function(){
                	isShow = true;
                	if(opts.callback)opts.callback(isShow);
                });
			});
		}
		$(document).css('overflow-y','auto');
	})
	
}

WST.toolTip = function(){
    $('body').mousemove(function(e){
    	var windowH = $(window).height();  
        if(e.pageY >= windowH*0.72){
        var top = windowH*0.28;
        	$('.imged').css('margin-top',-top);
        }else{
        var top = windowH*0.055;
        	$('.imged').css('margin-top',-top);
        }
    });
}
WST.shopsCats = function(objId,pVal,objVal){
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
WST.load = function(options){
    var opts = {};
    opts = $.extend(opts,{time:0,icon:'wstloading',shade: [0.4, '#000000'],offset: '200px',area: ['280px', '75px']},options);
    return layer.msg(opts.msg, opts);
}
/**
 * 获取用户信息
 */
var newOrderNum = 0;
WST.getSysMessages = function(val){
	if(WST.conf.IS_LOGIN==0)return;
	$.post(WST.U('shop/index/getSysMessages'),{tasks:val},function(data){
		var json = WST.toJson(data);
		var totalNum = 0
		if(json.message){
			$('#m-msg').hide();
			if(parseInt(json.message.num,10)>0){
				$('#m-msg').show();
				$('#m-msg span').html(json.message.num);
			}
			totalNum = parseInt(json.message.num,10);
		}
		if(json.shoporder){
			for(var key in json.shoporder){
				if($('#m-'+key)[0]){
					$('#m-'+key).hide();
					if(json.shoporder[key]!='0'){
						$('#m-'+key).show();
					    $('#m-'+key+" span").html(json.shoporder[key]);
					}
				}
				totalNum += parseInt(json.shoporder[key],10);
			}
            if(newOrderNum<parseInt(json.shoporder['24'],10)){
            	$('#orderTipVoice')[0].loop = false;
				try{
                    $('#orderTipVoice')[0].play()
            	}catch(e){
                    console.log('无法播放音频，请检查浏览器是否设置了允许播放音频');
            	}
				newOrderNum = parseInt(json.shoporder['24'],10);
			}
		}
		if(totalNum>0){
			$('.msg-num').show();
		}else{
			$('.msg-num').hide();
		}
		$('.msg-num').html(totalNum);
	});
}

WST.dropDownLayer = function(dropdown,layer){
	$(dropdown).hover(function () {
        $(this).find(layer).show();
    }, function () {
    	$(this).find(layer).hide();
    });
	$(layer).hover(function () {
		$(this).find(layer).show();
    }, function () {
    	$(this).find(layer).hide();
    });
}


WST.createShopQrcode = function(vtype){
	var params = {};
	params.vtype = vtype;
	var qrbox = (vtype==1)?"moQrcode":"weQrcode";
	var loading = "<img src='"+WST.conf.ROOT+"/wstmart/shop/view/default/img/loading.gif'>";
	if(vtype==1){
		$('#'+qrbox).html(loading+WST.lang('gen_wap_code'));
	}else{
		$('#'+qrbox).html(loading+WST.lang('weapp_code'));
	}
    $.post(WST.U('shop/shops/createShopQrcode'),params,function(data,textStatus){
    	
    	var json = WST.toJson(data);
    	if(json.status=='1'){
    		var html = [];
    		html.push("<img src='"+WST.conf.RESOURCE_PATH+"/"+json.data+"' width='200' style='border:1px solid #f2f2f2;'>");
    		html.push("<a href='"+WST.conf.RESOURCE_PATH+"/"+json.data+"' download style='font-weight:bold;margin-left:20px;'>"+WST.lang('down_weapp_code')+"</a>");
    		$('#'+qrbox).html(html);
    	}
    });
}

/*! nice-validator 1.0.4
 * (c) 2012-2016 Jony Zhang <niceue@live.com>, MIT Licensed
 * https://github.com/niceue/nice-validator
 */
!function(e){"object"==typeof module&&module.exports?module.exports=e(require("jquery")):"function"==typeof define&&define.amd?require(["jquery"],e):e(jQuery)}(function(e,t){"use strict";function i(t,n){function s(){a._init(a.$el[0],n,!!arguments[0])}var a=this;return a instanceof i?(a.$el=e(t),void(a.$el.length?(s(),i.pending&&e(window).on("validatorready",s)):J(t)&&(G[t]=n))):new i(t,n)}function n(t){function i(){var t=this.options;for(var i in t)i in Y&&(this[i]=t[i]);e.extend(this,{_valHook:function(){return null!==this.element.getAttribute("contenteditable")?"text":"val"},getValue:function(){var t=this.element;return"number"===t.type&&t.validity&&t.validity.badInput?"NaN":e(t)[this._valHook()]()},setValue:function(t){e(this.element)[this._valHook()](this.value=t)},getRangeMsg:function(e,t,i){function n(e,t){return o?e>t:e>=t}if(t){var s,a=this,r=a.messages[a._r]||"",l=t[0].split("~"),o="false"===t[1],u=l[0],d=l[1],c="rg",f=[""],g=U(e)&&+e===+e;return 2===l.length?u&&d?(g&&n(e,+u)&&n(+d,e)&&(s=!0),f=f.concat(l),c=o?"gtlt":"rg"):u&&!d?(g&&n(e,+u)&&(s=!0),f.push(u),c=o?"gt":"gte"):!u&&d&&(g&&n(+d,e)&&(s=!0),f.push(d),c=o?"lt":"lte"):(e===+u&&(s=!0),f.push(u),c="eq"),r&&(i&&r[c+i]&&(c+=i),f[0]=r[c]),s||a._rules&&(a._rules[a._i].msg=a.renderMsg.apply(null,f))}},renderMsg:function(){var e=arguments,t=e[0],i=e.length;if(t){for(;--i;)t=t.replace("{"+i+"}",e[i]);return t}}})}function n(i,n,s){this.key=i,this.validator=t,e.extend(this,s,n)}return i.prototype=t,n.prototype=new i,n}function s(e,t){if(Q(e)){var i,n=t?t===!0?this:t:s.prototype;for(i in e)g(i)&&(n[i]=r(e[i]))}}function a(e,t){if(Q(e)){var i,n=t?t===!0?this:t:a.prototype;for(i in e)n[i]=e[i]}}function r(t){switch(e.type(t)){case"function":return t;case"array":var i=function(){return t[0].test(this.value)||t[1]||!1};return i.msg=t[1],i;case"regexp":return function(){return t.test(this.value)}}}function l(t){var i,n,s;if(t&&t.tagName){switch(t.tagName){case"INPUT":case"SELECT":case"TEXTAREA":case"BUTTON":case"FIELDSET":i=t.form||e(t).closest("."+k);break;case"FORM":i=t;break;default:i=e(t).closest("."+k)}for(n in G)if(e(i).is(n)){s=G[n];break}return e(i).data(h)||e(i)[h](s).data(h)}}function o(e,t){var i=U(z(e,M+"-"+t));if(i&&(i=new Function("return "+i)()))return r(i)}function u(e,t,i){var n=t.msg,s=t._r;return Q(n)&&(n=n[s]),J(n)||(n=z(e,O+"-"+s)||z(e,O)||(i?J(i)?i:i[s]:"")),n}function d(e){var t;return e&&(t=I.exec(e)),t&&t[0]}function c(e){return"INPUT"===e.tagName&&"checkbox"===e.type||"radio"===e.type}function f(e){return Date.parse(e.replace(/\.|\-/g,"/"))}function g(e){return/^\w+$/.test(e)}function m(e){var t="#"===e.charAt(0);return e=e.replace(/([:.{(|)}\/\[\]])/g,"\\$1"),t?e:'[name="'+e+'"]:first'}var p,h="validator",v="."+h,_=".rule",y=".field",b=".form",k="nice-"+h,w="msg-box",x="aria-required",V="aria-invalid",M="data-rule",O="data-msg",F="data-tip",$="data-ok",C="data-timely",E="data-target",A="data-display",j="data-must",T="novalidate",N=":verifiable",S=/(&)?(!)?\b(\w+)(?:\[\s*(.*?\]?)\s*\]|\(\s*(.*?\)?)\s*\))?\s*(;|\|)?/g,q=/(\w+)(?:\[\s*(.*?\]?)\s*\]|\(\s*(.*?\)?)\s*\))?/,R=/(?:([^:;\(\[]*):)?(.*)/,D=/[^\x00-\xff]/g,I=/top|right|bottom|left/,H=/(?:(cors|jsonp):)?(?:(post|get):)?(.+)/i,L=/[<>'"`\\]|&#x?\d+[A-F]?;?|%3[A-F]/gim,B=e.noop,P=e.proxy,U=e.trim,W=e.isFunction,J=function(e){return"string"==typeof e},Q=function(e){return e&&"[object Object]"===Object.prototype.toString.call(e)},X=document.documentMode||+(navigator.userAgent.match(/MSIE (\d+)/)&&RegExp.$1),z=function(e,i,n){return e&&e.tagName?n===t?e.getAttribute(i):void(null===n?e.removeAttribute(i):e.setAttribute(i,""+n)):null},G={},K={debug:0,theme:"default",ignore:"",focusInvalid:!0,focusCleanup:!1,stopOnError:!1,beforeSubmit:null,valid:null,invalid:null,validation:null,validClass:"n-valid",invalidClass:"n-invalid",bindClassTo:null},Y={timely:1,display:null,target:null,ignoreBlank:!1,showOk:!0,dataFilter:function(e){if(J(e)||Q(e)&&("error"in e||"ok"in e))return e},msgMaker:function(t){var i;return i='<span role="alert" class="msg-wrap n-'+t.type+'">'+t.arrow,t.result?e.each(t.result,function(e,n){i+='<span class="n-'+n.type+'">'+t.icon+'<span class="n-msg">'+n.msg+"</span></span>"}):i+=t.icon+'<span class="n-msg">'+t.msg+"</span>",i+="</span>"},msgWrapper:"span",msgArrow:"",msgIcon:'<span class="n-icon"></span>',msgClass:"",msgStyle:"",msgShow:null,msgHide:null},Z={default:{formClass:"n-default",msgClass:"n-right"}};return e.fn.validator=function(t){var n=this,s=arguments;return n.is(N)?n:(n.is("form")||(n=this.find("form")),n.length||(n=this),n.each(function(){var n=e(this).data(h);if(n)if(J(t)){if("_"===t.charAt(0))return;n[t].apply(n,[].slice.call(s,1))}else t&&(n._reset(!0),n._init(this,t));else new i(this,t)}),this)},e.fn.isValid=function(e,i){var n,s,a=l(this[0]),r=W(e);return!a||(r||i!==t||(i=e),a.checkOnly=!!i,s=a.options,n=a._multiValidate(this.is(N)?this:this.find(N),function(t){t||!s.focusInvalid||a.checkOnly||a.$el.find("["+V+"]:first").focus(),r&&(e.length?e(t):t&&e()),a.checkOnly=!1}),r?this:n)},e.expr.pseudos.verifiable=function(e){var t=e.nodeName.toLowerCase();return("input"===t&&!{submit:1,button:1,reset:1,image:1}[e.type]||"select"===t||"textarea"===t||"true"===e.contentEditable)&&!e.disabled},e.expr.pseudos.filled=function(t){return!!U(e(t).val())},i.prototype={_init:function(t,i,r){var l,o,u,d=this;W(i)&&(i={valid:i}),i=d._opt=i||{},u=z(t,"data-"+h+"-option"),u=d._dataOpt=u&&"{"===u.charAt(0)?new Function("return "+u)():{},o=d._themeOpt=Z[i.theme||u.theme||K.theme],l=d.options=e.extend({},K,Y,o,d.options,i,u),r||(d.rules=new s(l.rules,(!0)),d.messages=new a(l.messages,(!0)),d.Field=n(d),d.elements=d.elements||{},d.deferred={},d.errors={},d.fields={},d._initFields(l.fields)),d.$el.data(h)||(d.$el.data(h,d).addClass(k+" "+l.formClass).on("form-submit-validate",function(e,t,i,n,s){d.vetoed=s.veto=!d.isValid,d.ajaxFormOptions=n}).on("submit"+v+" validate"+v,P(d,"_submit")).on("reset"+v,P(d,"_reset")).on("showmsg"+v,P(d,"_showmsg")).on("hidemsg"+v,P(d,"_hidemsg")).on("focusin"+v+" click"+v,N,P(d,"_focusin")).on("focusout"+v+" validate"+v,N,P(d,"_focusout")).on("keyup"+v+" input"+v+" compositionstart compositionend",N,P(d,"_focusout")).on("click"+v,":radio,:checkbox","click",P(d,"_focusout")).on("change"+v,'select,input[type="file"]',"change",P(d,"_focusout")),d._NOVALIDATE=z(t,T),z(t,T,T)),J(l.target)&&d.$el.find(l.target).addClass("msg-container")},_guessAjax:function(t){function i(t,i,n){return!!(t&&t[i]&&e.map(t[i],function(e){return~e.namespace.indexOf(n)?1:null}).length)}var n=this;if(!(n.isAjaxSubmit=!!n.options.valid)){var s=(e._data||e.data)(t,"events");n.isAjaxSubmit=i(s,"valid","form")||i(s,"submit","form-plugin")}},_initFields:function(e){function t(e,t){if(null===t||r){var i=a.elements[e];i&&a._resetElement(i,!0),delete a.fields[e]}else a.fields[e]=new a.Field(e,J(t)?{rule:t}:t,a.fields[e])}var i,n,s,a=this,r=null===e;if(r&&(e=a.fields),Q(e))for(i in e)if(~i.indexOf(","))for(n=i.split(","),s=n.length;s--;)t(U(n[s]),e[i]);else t(i,e[i]);a.$el.find(N).each(function(){a._parse(this)})},_parse:function(e){var t,i,n,s=this,a=e.name,r=z(e,M);if(r&&z(e,M,null),e.id&&("#"+e.id in s.fields||!a||null!==r&&(t=s.fields[a])&&r!==t.rule&&e.id!==t.key)&&(a="#"+e.id),a)return t=s.getField(a,!0),t.rule=r||t.rule,(i=z(e,A))&&(t.display=i),t.rule&&((null!==z(e,j)||/\b(?:match|checked)\b/.test(t.rule))&&(t.must=!0),/\brequired\b/.test(t.rule)&&(t.required=!0,z(e,x,!0)),(n=z(e,C))?t.timely=+n:t.timely>3&&z(e,C,t.timely),s._parseRule(t),t.old={}),J(t.target)&&z(e,E,t.target),J(t.tip)&&z(e,F,t.tip),s.fields[a]=t},_parseRule:function(i){var n=R.exec(i.rule);n&&(i._i=0,n[1]&&(i.display=n[1]),n[2]&&(i._rules=[],n[2].replace(S,function(){var n=arguments;n[4]=n[4]||n[5],i._rules.push({and:"&"===n[1],not:"!"===n[2],or:"|"===n[6],method:n[3],params:n[4]?e.map(n[4].split(", "),U):t})})))},_multiValidate:function(i,n){var s=this,a=s.options;return s.hasError=!1,a.ignore&&(i=i.not(a.ignore)),i.each(function(){if(s._validate(this),s.hasError&&a.stopOnError)return!1}),n&&(s.validating=!0,e.when.apply(null,e.map(s.deferred,function(e){return e})).done(function(){n.call(s,!s.hasError),s.validating=!1})),e.isEmptyObject(s.deferred)?!s.hasError:t},_submit:function(i){var n=this,s=n.options,a=i.target,r="submit"===i.type&&!i.isDefaultPrevented();i.preventDefault(),p&&~(p=!1)||n.submiting||"validate"===i.type&&n.$el[0]!==a||W(s.beforeSubmit)&&s.beforeSubmit.call(n,a)===!1||(n.isAjaxSubmit===t&&n._guessAjax(a),n._debug("log","\n<<< event: "+i.type),n._reset(),n.submiting=!0,n._multiValidate(n.$el.find(N),function(t){var i,l=t||2===s.debug?"valid":"invalid";t||(s.focusInvalid&&n.$el.find("["+V+"]:first").focus(),i=e.map(n.errors,function(e){return e})),n.submiting=!1,n.isValid=t,W(s[l])&&s[l].call(n,a,i),n.$el.trigger(l+b,[a,i]),n._debug("log",">>> "+l),t&&(n.vetoed?e(a).ajaxSubmit(n.ajaxFormOptions):r&&!n.isAjaxSubmit&&document.createElement("form").submit.call(a))}))},_reset:function(e){var t=this;t.errors={},e&&(t.reseting=!0,t.$el.find(N).each(function(){t._resetElement(this)}),delete t.reseting)},_resetElement:function(e,t){this._setClass(e,null),this.hideMsg(e),t&&z(e,x,null)},_focusin:function(e){var t,i,n=this,s=n.options,a=e.target;n.validating||"click"===e.type&&document.activeElement===a||(s.focusCleanup&&"true"===z(a,V)&&(n._setClass(a,null),n.hideMsg(a)),i=z(a,F),i?n.showMsg(a,{type:"tip",msg:i}):(z(a,M)&&n._parse(a),(t=z(a,C))&&(8!==t&&9!==t||n._focusout(e))))},_focusout:function(t){var i,n,s,a,r,l,o,u,d,f=this,g=f.options,m=t.target,p=t.type,h="focusin"===p,v="validate"===p,_=0;if("compositionstart"===p&&(f.pauseValidate=!0),"compositionend"===p&&(f.pauseValidate=!1),!f.pauseValidate&&(n=m.name&&c(m)?f.$el.find('input[name="'+m.name+'"]').get(0):m,s=f.getField(n))){if(i=s._e,s._e=p,d=s.timely,!v){if(!d||c(m)&&"click"!==p)return;if(r=s.getValue(),s.ignoreBlank&&!r&&!h)return void f.hideMsg(m);if("focusout"===p){if("change"===i)return;if(2===d||8===d){if(!r)return;a=s.old,s.isValid&&!a.showOk?f.hideMsg(m):f._makeMsg(m,s,a)}}else{if(d<2&&!t.data)return;if(l=+new Date,l-(m._ts||0)<100)return;if(m._ts=l,"keyup"===p){if("input"===i)return;if(o=t.keyCode,u={8:1,9:1,16:1,32:1,46:1},9===o&&!r)return;if(o<48&&!u[o])return}h||(_=d<100?"click"===p||"SELECT"===m.tagName?0:400:d)}}g.ignore&&e(m).is(g.ignore)||(clearTimeout(s._t),_?s._t=setTimeout(function(){f._validate(m,s)},_):(v&&(s.old={}),f._validate(m,s)))}},_setClass:function(t,i){var n=e(t),s=this.options;s.bindClassTo&&(n=n.closest(s.bindClassTo)),n.removeClass(s.invalidClass+" "+s.validClass),null!==i&&n.addClass(i?s.validClass:s.invalidClass)},_showmsg:function(t,i,n){var s=this,a=t.target;e(a).is(N)?s.showMsg(a,{type:i,msg:n}):"tip"===i&&s.$el.find(N+"["+F+"]",a).each(function(){s.showMsg(this,{type:i,msg:n})})},_hidemsg:function(t){var i=e(t.target);i.is(N)&&this.hideMsg(i)},_validatedField:function(t,i,n){var s=this,a=s.options,r=i.isValid=n.isValid=!!n.isValid,l=r?"valid":"invalid";n.key=i.key,n.ruleName=i._r,n.id=t.id,n.value=i.value,s.elements[i.key]=n.element=t,s.isValid=s.$el[0].isValid=r?s.isFormValid():r,r?n.type="ok":(s.submiting&&(s.errors[i.key]=n.msg),s.hasError=!0),i.old=n,W(i[l])&&i[l].call(s,t,n),W(a.validation)&&a.validation.call(s,t,n),e(t).attr(V,!r||null).trigger(l+y,[n,s]),s.$el.triggerHandler("validation",[n,s]),s.checkOnly||(s._setClass(t,n.skip||"tip"===n.type?null:r),s._makeMsg.apply(s,arguments))},_makeMsg:function(t,i,n){i.msgMaker&&(n=e.extend({},n),"focusin"===i._e&&(n.type="tip"),this[n.showOk||n.msg||"tip"===n.type?"showMsg":"hideMsg"](t,n,i))},_validatedRule:function(i,n,s,a){n=n||c.getField(i),a=a||{};var r,l,o,d,c=this,f=n._r,g=n.timely,m=9===g||8===g,p=!1;if(null===s)return c._validatedField(i,n,{isValid:!0,skip:!0}),void(n._i=0);if(s===t?o=!0:s===!0||""===s?p=!0:J(s)?r=s:Q(s)&&(s.error?r=s.error:(r=s.ok,p=!0)),l=n._rules[n._i],l.not&&(r=t,p="required"===f||!p),l.or)if(p)for(;n._i<n._rules.length&&n._rules[n._i].or;)n._i++;else o=!0;else l.and&&(n.isValid||(o=!0));o?p=!0:(p&&n.showOk!==!1&&(d=z(i,$),r=null===d?J(n.ok)?n.ok:r:d,!J(r)&&J(n.showOk)&&(r=n.showOk),J(r)&&(a.showOk=p)),p&&!m||(r=(u(i,n,r||l.msg||c.messages[f])||c.messages.fallback).replace(/\{0\|?([^\}]*)\}/,function(e,t){return c._getDisplay(i,n.display)||t||c.messages[0]})),p||(n.isValid=p),a.msg=r,e(i).trigger((p?"valid":"invalid")+_,[f,r])),!m||o&&!l.and||(p||n._m||(n._m=r),n._v=n._v||[],n._v.push({type:p?o?"tip":"ok":"error",msg:r||l.msg})),c._debug("log","   "+n._i+": "+f+" => "+(p||r)),(p||m)&&n._i<n._rules.length-1?(n._i++,c._checkRule(i,n)):(n._i=0,m?(a.isValid=n.isValid,a.result=n._v,a.msg=n._m||"",n.value||"focusin"!==n._e||(a.type="tip")):a.isValid=p,c._validatedField(i,n,a),delete n._m,delete n._v)},_checkRule:function(i,n){var s,a,r,l=this,u=n.key,d=n._rules[n._i],c=d.method,f=d.params;l.submiting&&l.deferred[u]||(r=n.old,n._r=c,r&&!n.must&&!d.must&&d.result!==t&&r.ruleName===c&&r.id===i.id&&n.value&&r.value===n.value?s=d.result:(a=o(i,c)||l.rules[c]||B,s=a.call(n,i,f,n),a.msg&&(d.msg=a.msg)),Q(s)&&W(s.then)?(l.deferred[u]=s,n.isValid=t,!l.checkOnly&&l.showMsg(i,{type:"loading",msg:l.messages.loading},n),s.then(function(s,a,r){var o,u=U(r.responseText),c=n.dataFilter;/jsonp?/.test(this.dataType)?u=s:"{"===u.charAt(0)&&(u=e.parseJSON(u)),o=c.call(this,u,n),o===t&&(o=c.call(this,u.data,n)),d.data=this.data,d.result=n.old?o:t,l._validatedRule(i,n,o)},function(e,t){l._validatedRule(i,n,l.messages[t]||t)}).always(function(){delete l.deferred[u]})):l._validatedRule(i,n,s))},_validate:function(e,t){var i=this;if(!e.disabled&&null===z(e,T)&&(t=t||i.getField(e),t&&(t._rules||i._parse(e),t._rules)))return i._debug("info",t.key),t.isValid=!0,t.element=e,t.value=t.getValue(),t.required||t.must||t.value||c(e)?(i._checkRule(e,t),t.isValid):(i._validatedField(e,t,{isValid:!0}),!0)},_debug:function(e,t){window.console&&this.options.debug&&console[e](t)},test:function(e,i){var n,s,a,r,l=this,o=q.exec(i);return o&&(a=o[1],a in l.rules&&(r=o[2]||o[3],r=r?r.split(", "):t,s=l.getField(e,!0),s._r=a,s.value=s.getValue(),n=l.rules[a].call(s,e,r))),n===!0||n===t||null===n},_getDisplay:function(e,t){return J(t)?t:W(t)?t.call(this,e):""},_getMsgOpt:function(t,i){var n=i?i:this.options;return e.extend({type:"error",pos:d(n.msgClass),target:n.target,wrapper:n.msgWrapper,style:n.msgStyle,cls:n.msgClass,arrow:n.msgArrow,icon:n.msgIcon},J(t)?{msg:t}:t)},_getMsgDOM:function(i,n){var s,a,r,l,o=e(i);if(o.is(N)?(r=n.target||z(i,E),r&&(r=W(r)?r.call(this,i):this.$el.find(r),r.length&&(r.is(N)?i=r.get(0):r.hasClass(w)?s=r:l=r)),s||(a=c(i)&&i.name||!i.id?i.name:i.id,s=this.$el.find(n.wrapper+"."+w+'[for="'+a+'"]'))):s=o,!n.hide&&!s.length)if(o=this.$el.find(r||i),s=e("<"+n.wrapper+">").attr({class:w+(n.cls?" "+n.cls:""),style:n.style||t,for:a}),c(i)){var u=o.parent();s.appendTo(u.is("label")?u.parent():u)}else l?s.appendTo(l):s[n.pos&&"right"!==n.pos?"insertBefore":"insertAfter"](o);return s},showMsg:function(t,i,n){if(t){var s,a,r,l,o=this,u=o.options;if(Q(t)&&!t.jquery&&!i)return void e.each(t,function(e,t){var i=o.elements[e]||o.$el.find(m(e))[0];o.showMsg(i,t)});e(t).is(N)&&(n=n||o.getField(t)),(a=(n||u).msgMaker)&&(i=o._getMsgOpt(i,n),t=e(t).get(0),i.msg||"error"===i.type||(r=z(t,"data-"+i.type),null!==r&&(i.msg=r)),J(i.msg)&&(l=o._getMsgDOM(t,i),!I.test(l[0].className)&&l.addClass(i.cls),6===X&&"bottom"===i.pos&&(l[0].style.marginTop=e(t).outerHeight()+"px"),l.html(a.call(o,i))[0].style.display="",W(s=n&&n.msgShow||u.msgShow)&&s.call(o,l,i.type)))}},hideMsg:function(t,i,n){var s,a,r=this,l=r.options;t=e(t).get(0),e(t).is(N)&&(n=n||r.getField(t),n&&(n.isValid||r.reseting)&&z(t,V,null)),i=r._getMsgOpt(i,n),i.hide=!0,a=r._getMsgDOM(t,i),a.length&&(W(s=n&&n.msgHide||l.msgHide)?s.call(r,a,i.type):(a[0].style.display="none",a[0].innerHTML=null))},getField:function(e,i){var n,s,a=this;if(J(e))n=e,e=t;else{if(z(e,M))return a._parse(e);n=e.id&&"#"+e.id in a.fields||!e.name?"#"+e.id:e.name}return((s=a.fields[n])||i&&(s=new a.Field(n)))&&(s.element=e),s},setField:function(e,t){var i={};e&&(J(e)?i[e]=t:i=e,this._initFields(i))},isFormValid:function(){var e,t,i=this.fields;for(e in i)if(t=i[e],t._rules&&(t.required||t.must||t.value)&&!t.isValid)return!1;return!0},holdSubmit:function(e){this.submiting=e===t||e},cleanUp:function(){this._reset(1)},destroy:function(){this._reset(1),this.$el.off(v).removeData(h),z(this.$el[0],T,this._NOVALIDATE)}},e(window).on("beforeunload",function(){this.focus()}),e(document).on("click",":submit",function(){var e,t=this;t.form&&(e=t.getAttributeNode("formnovalidate"),(e&&null!==e.nodeValue||null!==z(t,T))&&(p=!0))}).on("focusin submit validate","form,."+k,function(t){if(null===z(this,T)){var i,n=e(this);!n.data(h)&&(i=l(this))&&(e.isEmptyObject(i.fields)?(z(this,T,T),n.off(v).removeData(h)):"focusin"===t.type?i._focusin(t):i._submit(t))}}),new a({fallback:"This field is not valid.",loading:"Validating..."}),new s({required:function(t,i){var n=this,s=U(n.value),a=!0;if(i)if(1===i.length){if(g(i[0])){if(n.rules[i[0]]){if(!s&&!n.test(t,i[0]))return z(t,x,null),null;z(t,x,!0)}}else if(!s&&!e(i[0],n.$el).length)return null}else if("not"===i[0])e.each(i.slice(1),function(){return a=s!==U(this)});else if("from"===i[0]){var r,l=n.$el.find(i[1]),o="_validated_";return a=l.filter(function(){var e=n.getField(this);return e&&!!U(e.getValue())}).length>=(i[2]||1),a?s||(r=null):r=u(l[0],n)||!1,e(t).data(o)||l.data(o,1).each(function(){t!==this&&n._validate(this)}).removeData(o),r}return a&&!!s},integer:function(e,t){var i,n="0|",s="[1-9]\\d*",a=t?t[0]:"*";switch(a){case"+":i=s;break;case"-":i="-"+s;break;case"+0":i=n+s;break;case"-0":i=n+"-"+s;break;default:i=n+"-?"+s}return i="^(?:"+i+")$",new RegExp(i).test(this.value)||this.messages.integer[a]},match:function(t,i){if(i){var n,s,a,r,l,o,u,d,c=this,g="eq";if(1===i.length?a=i[0]:(g=i[0],a=i[1]),o=m(a),u=c.$el.find(o)[0]){if(d=c.getField(u),n=c.value,s=d.getValue(),c._match||(c.$el.on("valid"+y+v,o,function(){e(t).trigger("validate")}),c._match=d._match=1),!c.required&&""===n&&""===s)return null;if(l=i[2],l&&(/^date(time)?$/i.test(l)?(n=f(n),s=f(s)):"time"===l&&(n=+n.replace(/:/g,""),s=+s.replace(/:/g,""))),"eq"!==g&&!isNaN(+n)&&isNaN(+s))return!0;switch(r=c.messages.match[g].replace("{1}",c._getDisplay(t,d.display||a)),g){case"lt":return+n<+s||r;case"lte":return+n<=+s||r;case"gte":return+n>=+s||r;case"gt":return+n>+s||r;case"neq":return n!==s||r;default:return n===s||r}}}},range:function(e,t){return this.getRangeMsg(this.value,t)},checked:function(e,t){if(c(e)){var i,n,s=this;return e.name?n=s.$el.find('input[name="'+e.name+'"]').filter(function(){var e=this;return!i&&c(e)&&(i=e),!e.disabled&&e.checked}).length:(i=e,n=i.checked),t?s.getRangeMsg(n,t):!!n||u(i,s,"")||s.messages.required}},length:function(e,t){var i=this.value,n=("true"===t[1]?i.replace(D,"xx"):i).length;return this.getRangeMsg(n,t,t[1]?"_2":"")},remote:function(t,i){if(i){var n,s=this,a=H.exec(i[0]),r=s._rules[s._i],l={},o="",u=a[3],d=a[2]||"POST",c=(a[1]||"").toLowerCase();return r.must=!0,l[t.name]=s.value,i[1]&&e.map(i.slice(1),function(e){var t,i;~e.indexOf("=")?o+="&"+e:(t=e.split(":"),e=U(t[0]),i=U(t[1])||e,l[e]=s.$el.find(m(i)).val())}),l=e.param(l)+o,!s.must&&r.data&&r.data===l?r.result:("cors"!==c&&/^https?:/.test(u)&&!~u.indexOf(location.host)&&(n="jsonp"),e.ajax({url:u,type:d,data:l,dataType:n}))}},filter:function(e,t){var i=this.value,n=i.replace(t?new RegExp("["+t[0]+"]","gm"):L,"");n!==i&&this.setValue(n)}}),i.config=function(t,i){function n(e,t){"rules"===e?new s(t):"messages"===e?new a(t):e in Y?Y[e]=t:K[e]=t}Q(t)?e.each(t,n):J(t)&&n(t,i)},i.setTheme=function(t,i){Q(t)?e.extend(!0,Z,t):J(t)&&Q(i)&&(Z[t]=e.extend(Z[t],i))},i.load=function(t){if(t){var n,s,a,r=document,l={},o=r.scripts[0];t.replace(/([^?=&]+)=([^&#]*)/g,function(e,t,i){l[t]=i}),n=l.dir||i.dir,i.css||""===l.css||(s=r.createElement("link"),s.rel="stylesheet",s.href=i.css=n+"jquery.validator.css",o.parentNode.insertBefore(s,o)),i.local||""===l.local||(i.local=(l.local||r.documentElement.lang||"en").replace("_","-"),i.pending=1,s=r.createElement("script"),s.src=n+"local/"+i.local+".js",a="onload"in s?"onload":"onreadystatechange",s[a]=function(){s.readyState&&!/loaded|complete/.test(s.readyState)||(s=s[a]=null,delete i.pending,e(window).triggerHandler("validatorready"))},o.parentNode.insertBefore(s,o))}},function(){for(var e,t,n=document.scripts,s=n.length,a=/(.*validator(?:\.min)?.js)(\?.*(?:local|css|dir)(?:=[\w\-]*)?)?/;s--&&!t;)e=n[s],t=(e.hasAttribute?e.src:e.getAttribute("src",4)||"").match(a);t&&(i.dir=t[1].split("/").slice(0,-1).join("/")+"/",i.load(t[2]))}(),e[h]=i});

(function(factory){typeof module==="object"&&module.exports?module.exports=factory(require("jquery")):typeof define==="function"&&define.amd?require(["jquery"],factory):factory(jQuery)}(function($){$.validator.config({rules:{digits:[/^\d+$/,WST.lang('common_tips1')],letters:[/^[a-z]+$/i,WST.lang('common_tips2')],date:[/^\d{4}-\d{2}-\d{2}$/,WST.lang('common_tips3')],time:[/^([01]\d|2[0-3])(:[0-5]\d){1,2}$/,WST.lang('common_tips4')],email:[/^[\w\+\-]+(\.[\w\+\-]+)*@[a-z\d\-]+(\.[a-z\d\-]+)*\.([a-z]{2,4})$/i,WST.lang('common_tips5')],url:[/^(https?|s?ftp):\/\/\S+$/i,WST.lang('common_tips6')],qq:[/^[1-9]\d{4,}$/,WST.lang('common_tips7')],IDcard:[/^\d{6}(19|2\d)?\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)?$/,WST.lang('common_tips8')],tel:[/^(?:(?:0\d{2,3}[\- ]?[1-9]\d{6,7})|(?:[48]00[\- ]?[1-9]\d{6}))$/,WST.lang('common_tips9')],mobile:[/^1[3-9]\d{9}$/,WST.lang('common_tips10')],zipcode:[/^\d{6}$/,WST.lang('common_tips11')],chinese:[/^[\u0391-\uFFE5]+$/,WST.lang('common_tips12')],username:[/^\w{3,12}$/,WST.lang('common_tips12')],password:[/^[\S]{6,16}$/,WST.lang('common_tips14')],accept:function(element,params){if(!params){return true}var ext=params[0],value=$(element).val();return(ext==="*")||(new RegExp(".(?:"+ext+")$","i")).test(value)||this.renderMsg(WST.lang('common_tips15'),ext.replace(/\|/g,","))}},messages:{0:WST.lang('common_tips16'),fallback:WST.lang('common_tips17'),loading:WST.lang('loading'),error:WST.lang('common_tips18'),timeout:WST.lang('common_tips19'),required:WST.lang('common_tips20'),remote:WST.lang('common_tips21'),integer:{"*":WST.lang('common_tips22'),"+":WST.lang('common_tips23'),"+0":WST.lang('common_tips24'),"-":WST.lang('common_tips25'),"-0":WST.lang('common_tips26')},match:{eq:WST.lang('common_tips27'),neq:WST.lang('common_tips28'),lt:WST.lang('common_tips29'),gt:WST.lang('common_tips30'),lte:WST.lang('common_tips31'),gte:WST.lang('common_tips32')},range:{rg:WST.lang('common_tips33'),gte:WST.lang('common_tips34'),lte:WST.lang('common_tips35'),gtlt:WST.lang('common_tips36'),gt:WST.lang('common_tips37'),lt:WST.lang('common_tips38')},checked:{eq:WST.lang('common_tips39'),rg:WST.lang('common_tips40'),gte:WST.lang('common_tips41'),lte:WST.lang('common_tips42')},length:{eq:WST.lang('common_tips43'),rg:WST.lang('common_tips44'),gte:WST.lang('common_tips45'),lte:WST.lang('common_tips46'),eq_2:"",rg_2:"",gte_2:"",lte_2:""}}});var TPL_ARROW='<span class="n-arrow"><b>◆</b><i>◆</i></span>';$.validator.setTheme({"simple_right":{formClass:"n-simple",msgClass:"n-right"},"simple_bottom":{formClass:"n-simple",msgClass:"n-bottom"},"yellow_top":{formClass:"n-yellow",msgClass:"n-top",msgArrow:TPL_ARROW},"yellow_right":{formClass:"n-yellow",msgClass:"n-right",msgArrow:TPL_ARROW},"yellow_right_effect":{formClass:"n-yellow",msgClass:"n-right",msgArrow:TPL_ARROW,msgShow:function($msgbox,type){var $el=$msgbox.children();if($el.is(":animated")){return}if(type==="error"){$el.css({left:"20px",opacity:0}).delay(100).show().stop().animate({left:"-4px",opacity:1},150).animate({left:"3px"},80).animate({left:0},80)}else{$el.css({left:0,opacity:1}).fadeIn(200)}},msgHide:function($msgbox,type){var $el=$msgbox.children();$el.stop().delay(100).show().animate({left:"20px",opacity:0},300,function(){$msgbox.hide()})}}})}));
/**tip插件**/
(function($){var tips=[],reBgImage=/^url\(["']?([^"'\)]*)["']?\);?$/i,rePNG=/\.png$/i,ie6=!!window.createPopup&&document.documentElement.currentStyle.minWidth=="undefined";function handleWindowResize(){$.each(tips,function(){this.refresh(true)})}$(window).resize(handleWindowResize);$.Poshytip=function(elm,options){this.$elm=$(elm);this.opts=$.extend({},$.fn.poshytip.defaults,options);this.$tip=$(['<div class="',this.opts.className,'">','<div class="tip-inner tip-bg-image"></div>','<div class="tip-arrow tip-arrow-top tip-arrow-right tip-arrow-bottom tip-arrow-left"></div>',"</div>"].join("")).appendTo(document.body);this.$arrow=this.$tip.find("div.tip-arrow");this.$inner=this.$tip.find("div.tip-inner");this.disabled=false;this.content=null;this.init()};$.Poshytip.prototype={init:function(){tips.push(this);var title=this.$elm.attr("title");this.$elm.data("title.poshytip",title!==undefined?title:null).data("poshytip",this);if(this.opts.showOn!="none"){this.$elm.bind({"mouseenter.poshytip":$.proxy(this.mouseenter,this),"mouseleave.poshytip":$.proxy(this.mouseleave,this)});switch(this.opts.showOn){case"hover":if(this.opts.alignTo=="cursor"){this.$elm.bind("mousemove.poshytip",$.proxy(this.mousemove,this))}if(this.opts.allowTipHover){this.$tip.hover($.proxy(this.clearTimeouts,this),$.proxy(this.mouseleave,this))}break;case"focus":this.$elm.bind({"focus.poshytip":$.proxy(this.showDelayed,this),"blur.poshytip":$.proxy(this.hideDelayed,this)});break}}},mouseenter:function(e){if(this.disabled){return true}this.$elm.attr("title","");if(this.opts.showOn=="focus"){return true}this.showDelayed()},mouseleave:function(e){if(this.disabled||this.asyncAnimating&&(this.$tip[0]===e.relatedTarget||jQuery.contains(this.$tip[0],e.relatedTarget))){return true}if(!this.$tip.data("active")){var title=this.$elm.data("title.poshytip");if(title!==null){this.$elm.attr("title",title)}}if(this.opts.showOn=="focus"){return true}this.hideDelayed()},mousemove:function(e){if(this.disabled){return true}this.eventX=e.pageX;this.eventY=e.pageY;if(this.opts.followCursor&&this.$tip.data("active")){this.calcPos();this.$tip.css({left:this.pos.l,top:this.pos.t});if(this.pos.arrow){this.$arrow[0].className="tip-arrow tip-arrow-"+this.pos.arrow}}},show:function(){if(this.disabled||this.$tip.data("active")){return}this.reset();this.update();if(!this.content){return}this.display();if(this.opts.timeOnScreen){this.hideDelayed(this.opts.timeOnScreen)}},showDelayed:function(timeout){this.clearTimeouts();this.showTimeout=setTimeout($.proxy(this.show,this),typeof timeout=="number"?timeout:this.opts.showTimeout)},hide:function(){if(this.disabled||!this.$tip.data("active")){return}this.display(true)},hideDelayed:function(timeout){this.clearTimeouts();this.hideTimeout=setTimeout($.proxy(this.hide,this),typeof timeout=="number"?timeout:this.opts.hideTimeout)},reset:function(){this.$tip.queue([]).detach().css("visibility","hidden").data("active",false);this.$inner.find("*").poshytip("hide");if(this.opts.fade){this.$tip.css("opacity",this.opacity)}this.$arrow[0].className="tip-arrow tip-arrow-top tip-arrow-right tip-arrow-bottom tip-arrow-left";this.asyncAnimating=false},update:function(content,dontOverwriteOption){if(this.disabled){return}var async=content!==undefined;if(async){if(!dontOverwriteOption){this.opts.content=content}if(!this.$tip.data("active")){return}}else{content=this.opts.content}var self=this,newContent=typeof content=="function"?content.call(this.$elm[0],function(newContent){self.update(newContent)}):content=="[title]"?this.$elm.data("title.poshytip"):content;if(this.content!==newContent){this.$inner.empty().append(newContent);this.content=newContent}this.refresh(async)},refresh:function(async){if(this.disabled){return}if(async){if(!this.$tip.data("active")){return}var currPos={left:this.$tip.css("left"),top:this.$tip.css("top")}}this.$tip.css({left:0,top:0}).appendTo(document.body);if(this.opacity===undefined){this.opacity=this.$tip.css("opacity")}var bgImage=this.$tip.css("background-image").match(reBgImage),arrow=this.$arrow.css("background-image").match(reBgImage);if(bgImage){var bgImagePNG=rePNG.test(bgImage[1]);if(ie6&&bgImagePNG){this.$tip.css("background-image","none");this.$inner.css({margin:0,border:0,padding:0});bgImage=bgImagePNG=false}else{this.$tip.prepend('<table class="tip-table" border="0" cellpadding="0" cellspacing="0"><tr><td class="tip-top tip-bg-image" colspan="2"><span></span></td><td class="tip-right tip-bg-image" rowspan="2"><span></span></td></tr><tr><td class="tip-left tip-bg-image" rowspan="2"><span></span></td><td></td></tr><tr><td class="tip-bottom tip-bg-image" colspan="2"><span></span></td></tr></table>').css({border:0,padding:0,"background-image":"none","background-color":"transparent"}).find(".tip-bg-image").css("background-image",'url("'+bgImage[1]+'")').end().find("td").eq(3).append(this.$inner)}if(bgImagePNG&&!$.support.opacity){this.opts.fade=false}}if(arrow&&!$.support.opacity){if(ie6&&rePNG.test(arrow[1])){arrow=false;
this.$arrow.css("background-image","none")}this.opts.fade=false}var $table=this.$tip.find("> table.tip-table");if(ie6){this.$tip[0].style.width="";$table.width("auto").find("td").eq(3).width("auto");var tipW=this.$tip.width(),minW=parseInt(this.$tip.css("min-width")),maxW=parseInt(this.$tip.css("max-width"));if(!isNaN(minW)&&tipW<minW){tipW=minW}else{if(!isNaN(maxW)&&tipW>maxW){tipW=maxW}}this.$tip.add($table).width(tipW).eq(0).find("td").eq(3).width("100%")}else{if($table[0]){$table.width("auto").find("td").eq(3).width("auto").end().end().width(document.defaultView&&document.defaultView.getComputedStyle&&parseFloat(document.defaultView.getComputedStyle(this.$tip[0],null).width)||this.$tip.width()).find("td").eq(3).width("100%")}}this.tipOuterW=this.$tip.outerWidth();this.tipOuterH=this.$tip.outerHeight();this.calcPos();if(arrow&&this.pos.arrow){this.$arrow[0].className="tip-arrow tip-arrow-"+this.pos.arrow;this.$arrow.css("visibility","inherit")}if(async&&this.opts.refreshAniDuration){this.asyncAnimating=true;var self=this;this.$tip.css(currPos).animate({left:this.pos.l,top:this.pos.t},this.opts.refreshAniDuration,function(){self.asyncAnimating=false})}else{this.$tip.css({left:this.pos.l,top:this.pos.t})}},display:function(hide){var active=this.$tip.data("active");if(active&&!hide||!active&&hide){return}this.$tip.stop();if((this.opts.slide&&this.pos.arrow||this.opts.fade)&&(hide&&this.opts.hideAniDuration||!hide&&this.opts.showAniDuration)){var from={},to={};if(this.opts.slide&&this.pos.arrow){var prop,arr;if(this.pos.arrow=="bottom"||this.pos.arrow=="top"){prop="top";arr="bottom"}else{prop="left";arr="right"}var val=parseInt(this.$tip.css(prop));from[prop]=val+(hide?0:(this.pos.arrow==arr?-this.opts.slideOffset:this.opts.slideOffset));to[prop]=val+(hide?(this.pos.arrow==arr?this.opts.slideOffset:-this.opts.slideOffset):0)+"px"}if(this.opts.fade){from.opacity=hide?this.$tip.css("opacity"):0;to.opacity=hide?0:this.opacity}this.$tip.css(from).animate(to,this.opts[hide?"hideAniDuration":"showAniDuration"])}hide?this.$tip.queue($.proxy(this.reset,this)):this.$tip.css("visibility","inherit");if(active){var title=this.$elm.data("title.poshytip");if(title!==null){this.$elm.attr("title",title)}}this.$tip.data("active",!active)},disable:function(){this.reset();this.disabled=true},enable:function(){this.disabled=false},destroy:function(){this.reset();this.$tip.remove();delete this.$tip;this.content=null;this.$elm.unbind(".poshytip").removeData("title.poshytip").removeData("poshytip");tips.splice($.inArray(this,tips),1)},clearTimeouts:function(){if(this.showTimeout){clearTimeout(this.showTimeout);this.showTimeout=0}if(this.hideTimeout){clearTimeout(this.hideTimeout);this.hideTimeout=0}},calcPos:function(){var pos={l:0,t:0,arrow:""},$win=$(window),win={l:$win.scrollLeft(),t:$win.scrollTop(),w:$win.width(),h:$win.height()},xL,xC,xR,yT,yC,yB;if(this.opts.alignTo=="cursor"){xL=xC=xR=this.eventX;yT=yC=yB=this.eventY}else{var elmOffset=this.$elm.offset(),elm={l:elmOffset.left,t:elmOffset.top,w:this.$elm.outerWidth(),h:this.$elm.outerHeight()};xL=elm.l+(this.opts.alignX!="inner-right"?0:elm.w);xC=xL+Math.floor(elm.w/2);xR=xL+(this.opts.alignX!="inner-left"?elm.w:0);yT=elm.t+(this.opts.alignY!="inner-bottom"?0:elm.h);yC=yT+Math.floor(elm.h/2);yB=yT+(this.opts.alignY!="inner-top"?elm.h:0)}switch(this.opts.alignX){case"right":case"inner-left":pos.l=xR+this.opts.offsetX;if(this.opts.keepInViewport&&pos.l+this.tipOuterW>win.l+win.w){pos.l=win.l+win.w-this.tipOuterW}if(this.opts.alignX=="right"||this.opts.alignY=="center"){pos.arrow="left"}break;case"center":pos.l=xC-Math.floor(this.tipOuterW/2);if(this.opts.keepInViewport){if(pos.l+this.tipOuterW>win.l+win.w){pos.l=win.l+win.w-this.tipOuterW}else{if(pos.l<win.l){pos.l=win.l}}}break;default:pos.l=xL-this.tipOuterW-this.opts.offsetX;if(this.opts.keepInViewport&&pos.l<win.l){pos.l=win.l}if(this.opts.alignX=="left"||this.opts.alignY=="center"){pos.arrow="right"}}switch(this.opts.alignY){case"bottom":case"inner-top":pos.t=yB+this.opts.offsetY;if(!pos.arrow||this.opts.alignTo=="cursor"){pos.arrow="top"}if(this.opts.keepInViewport&&pos.t+this.tipOuterH>win.t+win.h){pos.t=yT-this.tipOuterH-this.opts.offsetY;if(pos.arrow=="top"){pos.arrow="bottom"}}break;case"center":pos.t=yC-Math.floor(this.tipOuterH/2);if(this.opts.keepInViewport){if(pos.t+this.tipOuterH>win.t+win.h){pos.t=win.t+win.h-this.tipOuterH}else{if(pos.t<win.t){pos.t=win.t}}}break;default:pos.t=yT-this.tipOuterH-this.opts.offsetY;if(!pos.arrow||this.opts.alignTo=="cursor"){pos.arrow="bottom"}if(this.opts.keepInViewport&&pos.t<win.t){pos.t=yB+this.opts.offsetY;if(pos.arrow=="bottom"){pos.arrow="top"}}}this.pos=pos}};$.fn.poshytip=function(options){if(typeof options=="string"){var args=arguments,method=options;Array.prototype.shift.call(args);if(method=="destroy"){this.die?this.die("mouseenter.poshytip").die("focus.poshytip"):$(document).undelegate(this.selector,"mouseenter.poshytip").undelegate(this.selector,"focus.poshytip")}return this.each(function(){var poshytip=$(this).data("poshytip");
if(poshytip&&poshytip[method]){poshytip[method].apply(poshytip,args)}})}var opts=$.extend({},$.fn.poshytip.defaults,options);if(!$("#poshytip-css-"+opts.className)[0]){$(['<style id="poshytip-css-',opts.className,'" type="text/css">',"div.",opts.className,"{visibility:hidden;position:absolute;top:0;left:0;}","div.",opts.className," table.tip-table, div.",opts.className," table.tip-table td{margin:0;font-family:inherit;font-size:inherit;font-weight:inherit;font-style:inherit;font-variant:inherit;vertical-align:middle;}","div.",opts.className," td.tip-bg-image span{display:block;font:1px/1px sans-serif;height:",opts.bgImageFrameSize,"px;width:",opts.bgImageFrameSize,"px;overflow:hidden;}","div.",opts.className," td.tip-right{background-position:100% 0;}","div.",opts.className," td.tip-bottom{background-position:100% 100%;}","div.",opts.className," td.tip-left{background-position:0 100%;}","div.",opts.className," div.tip-inner{background-position:-",opts.bgImageFrameSize,"px -",opts.bgImageFrameSize,"px;}","div.",opts.className," div.tip-arrow{visibility:hidden;position:absolute;overflow:hidden;font:1px/1px sans-serif;}","</style>"].join("")).appendTo("head")}if(opts.liveEvents&&opts.showOn!="none"){var handler,deadOpts=$.extend({},opts,{liveEvents:false});switch(opts.showOn){case"hover":handler=function(){var $this=$(this);if(!$this.data("poshytip")){$this.poshytip(deadOpts).poshytip("mouseenter")}};this.live?this.live("mouseenter.poshytip",handler):$(document).delegate(this.selector,"mouseenter.poshytip",handler);break;case"focus":handler=function(){var $this=$(this);if(!$this.data("poshytip")){$this.poshytip(deadOpts).poshytip("showDelayed")}};this.live?this.live("focus.poshytip",handler):$(document).delegate(this.selector,"focus.poshytip",handler);break}return this}return this.each(function(){new $.Poshytip(this,opts)})};$.fn.poshytip.defaults={content:"[title]",className:"tip-yellow",bgImageFrameSize:10,showTimeout:500,hideTimeout:100,timeOnScreen:0,showOn:"hover",liveEvents:false,alignTo:"cursor",alignX:"right",alignY:"top",offsetX:-22,offsetY:18,keepInViewport:true,allowTipHover:true,followCursor:false,fade:true,slide:true,slideOffset:8,showAniDuration:300,hideAniDuration:300,refreshAniDuration:200}})(jQuery);