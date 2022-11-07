var WST = WST?WST:{};
WST.wxv = '2.1.1_0220';
WST.toJson = function(str,notLimit){
	var json = {};
	if(str){
	try{
		if(typeof(str )=="object"){
			json = str;
		}else{
			json = eval("("+str+")");
		}
		if(!notLimit){
			if(json.status && json.status=='-999'){
				WST.inLogin();
			}
		}
	}catch(e){
		alert(WST.lang('sys_err_tip')+":"+e.getMessage);
		json = {};
	}
	return json;
	}else{
		return;
	}
}
//登录
WST.inLogin = function(){
	var urla = window.location.href;
	$.post(WST.U('mobile/index/sessionAddress'),{url:urla},function(data,textStatus){});
	var url = WST.U('mobile/users/login',true);
	window.location.href = url;
}
//底部的tab
WST.initFooter = function(tab){
    var homeImage = (tab=='home') ? 'home-active' : 'home';
    var categoryImage = (tab=='category') ? 'category-active' : 'category';
    var cartImage = (tab=='cart') ? 'cart-active' : 'cart';
    var followImage = (tab=='brand') ? 'follow-active' : 'follow';
    var usersImage = (tab=='user') ? 'user-active' : 'user';
    $('#home').append('<span class="icon '+homeImage+'"></span><span class="'+homeImage+'-word">'+WST.lang('home_page')+'</span>');
    $('#category').append('<span class="icon '+categoryImage+'"></span><span class="'+categoryImage+'-word">'+WST.lang('classify')+'</span>');
    $('#cart').prepend('<span class="icon '+cartImage+'"></span><span class="'+cartImage+'-word">'+WST.lang('shopping_cart')+'</span>');
    $('#follow').append('<span class="icon '+followImage+'"></span><span class="'+followImage+'-word">'+WST.lang('follow')+'</span>');
    $('#user').append('<span class="icon '+usersImage+'"></span><span class="'+usersImage+'-word">'+WST.lang('mine')+'</span>');
}
//变换选中框的状态
WST.changeIconStatus = function (obj, toggle, status){
    if(toggle==1){
        if( obj.attr('class').indexOf('ui-icon-unchecked-s') > -1 ){
            obj.removeClass('ui-icon-unchecked-s').addClass('ui-icon-success-block wst-active');
        }else{
            obj.removeClass('ui-icon-success-block wst-active').addClass('ui-icon-unchecked-s');
        }
    }else if(toggle==2){
        if(status == 'wst-active'){
            obj.removeClass('ui-icon-unchecked-s').addClass('ui-icon-success-block wst-active');
        }else{
            obj.removeClass('ui-icon-success-block wst-active').addClass('ui-icon-unchecked-s');
        }
    }
}
WST.changeIptNum = function(diffNum,iptId,id,func){
	var suffix = (id)?"_"+id:"";
	var iptElem = $(iptId+suffix);
	var minVal = parseInt(iptElem.attr('data-min'),10);
	var maxVal = parseInt(iptElem.attr('data-max'),10);
	var num = parseInt(iptElem.val(),10);
	num = num?num:1;
	num = num + diffNum;
	if(maxVal<=num)num=maxVal;
	if(num<=minVal)num=minVal;
	if(num==0)num=1;
	iptElem.val(num);
	if(suffix!='')WST.changeCartGoods(id,num,-1);
	if(func){
		var fn = window[func];
		fn();
	}
}
WST.changeCartGoods = function(id,buyNum,isCheck){
	$.post(WST.U('mobile/carts/changeCartGoods'),{id:id,isCheck:isCheck,buyNum:buyNum,rnd:Math.random()},function(data,textStatus){
	     var json = WST.toJson(data);
	     if(json.status!=1){
	    	 WST.msg(json.msg,'info');
	     }
	});
}
// 批量修改购物车状态
WST.batchChangeCartGoods = function(ids,isCheck){
	$.post(WST.U('mobile/carts/batchChangeCartGoods'),{ids:ids,isCheck:isCheck},function(data,textStatus){
	     var json = WST.toJson(data);
	     if(json.status!=1){
	    	 WST.msg(json.msg,'info');
	     }
	});
}
//加入购物车
WST.addCart = function(goodsId,refresh){
	$.post(WST.U('mobile/carts/addCart'),{goodsId:goodsId,buyNum:1},function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status==1){
			WST.msg(json.msg,'success');
			if(json.cartNum>0){
				var pageId = parseInt($('#pageId').val());
				if(pageId>0){
					// 开启首页自定义布局插件
					$("#cartNum2").html('<i>'+json.cartNum+'</i>');
				}else{
					$("#cartNum").html('<i>'+json.cartNum+'</i>');
				}
				if(refresh!=undefined && refresh==1){
					location.href=WST.U('mobile/carts/index');
				}
			}
			if(json.data && json.data.forward){
				location.href=WST.U('mobile/carts/'+json.data.forward);
			}
		}else{
			WST.msg(json.msg,'info');
		}
	});
}
//商品主页
WST.intoGoods = function(id){
	location.href = WST.U('mobile/goods/detail','goodsId='+id,true);
};
//店铺主页
WST.intoShops = function(id){
	location.href = WST.U('mobile/shops/index','shopId='+id,true);
};
//首页
WST.intoIndex = function(){
	location.href = WST.U('mobile/index/index',true);
};
//搜索
WST.searchPage = function(type,state){
	if(state==1){
		$("#wst-"+type+"-search").show();
		$("#wst-search").focus();
		$('#goodsTab').hide();
		if (window.history && window.history.pushState) 
		{ $(window).on('popstate', function() { var hashLocation = location.hash; var hashSplit = hashLocation.split("#!/");
		   var hashName = hashSplit[1]; 
		   if (hashName !== '') { var hash = window.location.hash; if (hash === '') {WST.searchPage(type,0); } } });
		   window.history.pushState('forward', null, './#forward'); 
		}
	}else{
		$("#wst-"+type+"-search").hide();
		$('#goodsTab').show();
	}
};
WST.search = function(type){
	var data = $('#wst-search').val();
	if(type==1){
		location.href = WST.U('mobile/shops/shopstreet','keyword='+data,true);//店铺
	}else if(type==0){
		location.href = WST.U('mobile/goods/search','keyword='+data,true);//商品
	}else if(type==2){
		var shopId = $('#shopId').val();
		location.href = WST.U('mobile/shops/goods','goodsName='+data+'&shopId='+shopId,true);//店铺商品
	}
};
//关注
WST.favorites = function(sId,type){
    $.post(WST.U('mobile/favorites/add'),{id:sId,type:type},function(data){
        var json = WST.toJson(data);
        if(json.status==1){
            WST.msg(json.msg,'success');
            if(type==1){
                $('#fStatus').html(WST.lang('has_favorited'));
                $('#fBtn').attr('onclick','WST.cancelFavorite('+json.data.fId+',1)');
                $('.j-shopfollow').addClass('follow');
                var followNum = parseInt($('#followNum').val())+1;
				$('#followNum').val(followNum);
                $('#followText').html(WST.lang('collection')+ followNum);
            }else{
            	$('.imgfollow').removeClass('nofollow').addClass('follow');
            	$('.imgfollow').attr('onclick','WST.cancelFavorite('+json.data.fId+',0)');
            }
        }else{
            WST.msg(json.msg,'info');
        }
    })
}
// 取消关注
WST.cancelFavorite = function(fId,type){
    $.post(WST.U('mobile/favorites/cancel'),{id:fId,type:type},function(data){
    var json = WST.toJson(data);
    if(json.status==1){
      WST.msg(json.msg,'success');
      if(type==1){
          $('#fStatus').html(WST.lang('favorite_shop'));
          $('#fBtn').attr('onclick','WST.favorites('+$('#shopId').val()+',1)');
          $('.j-shopfollow').removeClass('follow');
          $('#followNum').val(parseInt($('#followNum').val())-1);
		  $('#followText').html(WST.lang('collection_shop'));
      }else{
    	  $('.imgfollow').removeClass('follow').addClass('nofollow');
    	  $('.imgfollow').attr('onclick','WST.favorites('+$('#goodsId').val()+',0)');
      }
    }else{
      WST.msg(json.msg,'info');
    }
  });
}
//刷新验证码
WST.getVerify = function(id){
    $(id).attr('src',WST.U('mobile/index/getVerify','rnd='+Math.random()));
}
//返回当前页面高度
WST.pageHeight = function(){
	if(WST.checkBrowser().msie){
		return document.compatMode == "CSS1Compat"? document.documentElement.clientHeight :
		document.body.clientHeight;
	}else{
		return self.innerHeight;
	}
};
//返回当前页面宽度
WST.pageWidth = function(){
	if(WST.checkBrowser().msie){
		return document.compatMode == "CSS1Compat"? document.documentElement.clientWidth :
		document.body.clientWidth;
	}else{
		return self.innerWidth;
	}
};
WST.checkBrowser = function(){
	return {
		mozilla : /firefox/.test(navigator.userAgent.toLowerCase()),
		webkit : /webkit/.test(navigator.userAgent.toLowerCase()),
	    opera : /opera/.test(navigator.userAgent.toLowerCase()),
	    msie : /msie/.test(navigator.userAgent.toLowerCase())
	}
}
//只能輸入數字
WST.isNumberKey = function(evt){
	var charCode = (evt.which) ? evt.which : event.keyCode;
	if (charCode > 31 && (charCode < 48 || charCode > 57)){
		return false;
	}else{
		return true;
	}
}
WST.isChinese = function(obj,isReplace){
 	var pattern = /[\u4E00-\u9FA5]|[\uFE30-\uFFA0]/i
 	if(pattern.test(obj.value)){
 		if(isReplace)obj.value=obj.value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]/ig,"");
 		return true;
 	}
 	return false;
}
//适应图片大小正方形
WST.imgAdapt = function(name){
	var w = $('.'+name).width();
	$('.'+name).css({"width": w+"px","height": w+"px"});
	$('.'+name+' a').css({"width": w+"px","height": w+"px"});
	$('.'+name+' a img').css({"max-width": w+"px","max-height": w+"px"});
}
//显示隐藏
WST.showHide = function(t,str){
	var s = str.split(',');
	if(t){
		for(var i=0;i<s.length;i++){
		   $(s[i]).show();
		}
	}else{
		for(var i=0;i<s.length;i++){
		   $(s[i]).hide();
		}
	}
	s = null;
}
/**
 * 提示信息
 * @param content   	内容
 * @param type          info/普通,success/成功,warn/错误
 * @param stayTime      显示时间
 */
WST.msg = function(content,type,stayTime){
	if(!stayTime){
		stayTime = '1200';
	}
	var el = Zepto.tips({content:content,type:type,stayTime:stayTime});
    return  el;
}
//提示对话框
WST.dialog = function(content,event,title){
	$("#wst-dialog").html(content);
	$("#wst-dialog-title").html(title);
	$("#wst-event2").attr("onclick","javascript:"+event);
	$("#wst-di-prompt").dialog("show");
}
//提示分享对话框
WST.share = function(){
	$("#wst-di-share").dialog("show");
}
/**
 * 隐藏对话框
 * @param event   	prompt/提示对话框
 * @param event   	share/提示对话框
 */
WST.dialogHide = function(event){
	$("#wst-di-"+event).dialog("hide");
}
//加载中
WST.load = function(content){
	$('#Loadl').css('display','-webkit-box');
	$('#j-Loadl').html(content);
}
WST.noload = function(){
	$('#Loadl').css('display','none');
}
//滚动到顶部
WSTrunToTop = function (){
	currentPosition=document.documentElement.scrollTop || document.body.scrollTop;
	currentPosition-=20;
	if(currentPosition>0){
		window.scrollTo(0,currentPosition);
	}
	else{
		window.scrollTo(0,0);
		clearInterval(timer);
	}
}
WST.inArray = function(str,arrs){
	if(typeof(arrs) != "object")return -1;
	for(var i=0;i<arrs.length;i++){
		if(arrs[i]==str)return i;
	}
	return -1;
}

WST.blank = function(str,defaultVal){
	if(str=='0000-00-00')str = '';
	if(str=='0000-00-00 00:00:00')str = '';
	if(!str)str = '';
	if(typeof(str)=='null')str = '';
	if(typeof(str)=='undefined')str = '';
	if(str=='' && defaultVal)str = defaultVal;
	return str;
}

/**
* 上传图片
*/
WST.upload = function(opts){
  var _opts = {};
  _opts = $.extend(_opts,{duplicate:true,auto: true,swf: WST.conf.STATIC +'/plugins/webuploader/Uploader.swf',server:WST.U('mobile/orders/uploadPic')},opts);
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

//返回键
function backPrevPage(url){
	window.location.hash = "ready";
	window.location.hash = "ok";
    setTimeout(function(){
		$(window).on('hashchange', function(e) {
			var hashName = window.location.hash.replace('#', '');
			hashName = hashName.split('&');
			if( hashName[0] == 'ready' ){
			    location.href = url;
			}
		});
    },50);
}

//图片切换
WST.replaceImg = function(v,str){
	var vs = v.split('.');
    return v.replace("."+vs[1],str+"."+vs[1]);
}

/**
 * 截取字符串
 */
WST.cutStr = function (str,len)
{
    if(!str || str=='')return '';
    var strlen = 0;
    var s = "";
    for(var i = 0;i < str.length;i++)
    {
        if(strlen >= len){
            return s + "...";
        }
        if(str.charCodeAt(i) > 128)
            strlen += 2;
        else
            strlen++;
        s += str.charAt(i);
    }
    return s;
}

$(function(){
	echo.init();//图片懒加载
    // 滚动到顶部
    $(window).scroll(function(){
        if( $(window).scrollTop() > 200 ){
            $('#toTop').show();
        }else{
            $('#toTop').hide();
        }
    });
    $('#toTop').on('click', function() {
    	timer=setInterval("WSTrunToTop()",1);
	});
	/**
	 * 获取WSTMart基础配置
	 * @type {object}
	 */
	WST.conf = window.conf;
	/* 基础对象检测 */
	WST.conf || $.error(WST.lang('sys_basic_config_not_load'));
	if(WST.conf.ROUTES)WST.conf.ROUTES = eval("("+WST.conf.ROUTES+")");
	/**
	 * 解析URL
	 * @param  {string} url 被解析的URL
	 * @return {object}     解析后的数据
	 */
	WST.parse_url = function(url){
		var parse = url.match(/^(?:([a-z]+):\/\/)?([\w-]+(?:\.[\w-]+)+)?(?::(\d+))?([\w-\/]+)?(?:\?((?:\w+=[^#&=\/]*)?(?:&\w+=[^#&=\/]*)*))?(?:#([\w-]+))?$/i);
		parse || $.error("url格式不正确！");
		return {
			"scheme"   : parse[1],
			"host"     : parse[2],
			"port"     : parse[3],
			"path"     : parse[4],
			"query"    : parse[5],
			"fragment" : parse[6]
		};
	}

	WST.parse_str = function(str){
		var value = str.split("&"), vars = {}, param;
		for(var i=0;i<value.length;i++){
			param = value[i].split("=");
			vars[param[0]] = param[1];
		}
		return vars;
	}
	WST.initU = function(url,vars){
		if(typeof vars === "string"){
			vars = this.parse_str(vars);
		}
		var newUrl = WST.conf.ROUTES[url];
		if(newUrl.indexOf('>')>-1 && newUrl.indexOf('-<')>-1){
			newUrl = newUrl.replace('-<','-:').replace('>','');
		}
	    var urlparams = newUrl.match(/:(\w+(\??))/g);
	    urlparams = (urlparams==null)?[]:urlparams;
	    var tmpv = null;
		for(var v in vars){
			tmpv = ':'+v;
			if(WST.inArray(tmpv,urlparams)>-1){
				newUrl = newUrl.replace(tmpv,vars[v]);
				delete vars[v];
			}
		}
		tmpv = urlparams = null;
		if(false !== WST.conf.SUFFIX){
			newUrl += "." + WST.conf.SUFFIX;
		}
		if($.isPlainObject(vars)){
			var tmp = $.param(vars);
			if(tmp!='')newUrl += "?"+tmp;
			tmp = null;
		}
		//url = url.replace(new RegExp("%2F","gm"),"+");
		newUrl = WST.conf.APP + "/"+newUrl;
		return newUrl;
	}

	WST.U0 = function(url, vars){
		if(!url || url=='')return '';
		var info = this.parse_url(url), path = [], reg;
		/* 验证info */
		info.path || $.error(WST.lang('url_error'));
		url = info.path;
		/* 解析URL */
		path = url.split("/");
		path = [path.pop(), path.pop(), path.pop()].reverse();
		path[1] || $.error("WST.U(" + url + ")"+WST.lang('no_controller'));

		/* 解析参数 */
		if(typeof vars === "string"){
			vars = this.parse_str(vars);
		}
		/* 解析URL自带的参数 */
		info.query && $.extend(vars, this.parse_str(info.query));
		if(false !== WST.conf.SUFFIX){
			url += "." + WST.conf.SUFFIX;
		}
		if($.isPlainObject(vars)){
			var tmp = $.param(vars);
			if(tmp!='')url += "?"+tmp;
			tmp = null;
		}
		//url = url.replace(new RegExp("%2F","gm"),"+");
		url = WST.conf.APP + "/"+url;
		return url;
	}
	WST.U = function(url,vars){
		if(WST.conf.ROUTES && WST.conf.ROUTES[url]){
		    return WST.initU(url,vars);
		}else{
			return WST.U0(url, vars);
		}
	}
	WST.AU = function(url, vars){
        if(!url || url=='')return '';
        var info = this.parse_url(url);
        url = info.path;
        path = url.split("/");
        url = "addon/";
        path = [path.pop(), path.pop()].reverse();
        path[0] || $.error("WST.AU(" + url + ")"+WST.lang('no_controller'));
        path[1] || $.error("WST.AU(" + url + ")"+WST.lang('no_interface'));
        url  = url + info.scheme + "-" + path.join('-');
        /* 解析参数 */
		if(typeof vars === "string"){
			vars = this.parse_str(vars);
		}
		info.query && $.extend(vars, this.parse_str(info.query));
		if(false !== WST.conf.SUFFIX){
			url += "." + WST.conf.SUFFIX;
		}
		if($.isPlainObject(vars)){
			var tmp = $.param(vars);
			if(tmp!='')url += "?"+tmp;
			tmp = null;
		}
		return WST.conf.APP + "/"+url;
	}
});

WST.location = function(callback){
    var geolocation = new qq.maps.Geolocation(WST.conf.MAP_KEY, "ShangTaoTX");
    var options = {timeout: 8000};
    geolocation.getLocation(showPosition, showErr, options);
    function showPosition(position) {
        if(typeof(callback)=='function')callback({latitude:position.lat,longitude:position.lng});
    };
    function showErr() {
        if(typeof(callback)=='function')callback({latitude:0,longitude:0});
    };
}

WST.setCookie = function(key,val,time){
	if(time>0){
		var date=new Date();
		var expiresDays=time;
		date.setTime(date.getTime()+expiresDays*24*3600*1000);
		document.cookie=key + "=" + val +";expires="+date.toGMTString();
	}else{
		document.cookie=key + "=" + val ;
	}

}

WST.getCookie = function(key){
	/*获取cookie参数*/
	var getCookie = document.cookie.replace(/[ ]/g,"");
	var arrCookie = getCookie.split(";");
	var tips;
	for(var i=0;i<arrCookie.length;i++){
		var arr=arrCookie[i].split("=");
		if(key==arr[0]){
			tips=arr[1];
			break;
		}
	}
	return tips;
}
WST.delCookie = function(key){
	var date = new Date();
	date.setTime(date.getTime()-10000); //将date设置为过去的时间
	document.cookie = key + "=v; expires =" +date.toGMTString();
}

//选中底部的自定义tab
WST.selectCustomMenuPage = function(menu){
	$(".wst-custom-menus a").each(function(idx,item){
		if($(item).attr('menu-flag') == menu){
			$(item).find('.custom-menu-select-icon').removeClass('wst-none');
			$(item).find('.custom-menu-icon').addClass('wst-none');
			$(item).find('.custom-menu-select-text').removeClass('wst-none');
			$(item).find('.custom-menu-text').addClass('wst-none');
		}else{
			$(item).find('.custom-menu-select-icon').addClass('wst-none');
			$(item).find('.custom-menu-icon').removeClass('wst-none');
			$(item).find('.custom-menu-select-text').addClass('wst-none');
			$(item).find('.custom-menu-text').removeClass('wst-none');
		}
	});
}

//底部的自定义tab跳转
WST.toCustomMenuPage = function(obj){
	var link = $(obj).attr('link');
	location.href = WST.U(link);
}

WST.getParams = function(obj){
	var params = {};
	var chk = {},s;
	$(obj).each(function(){
		if($(this)[0].type=='hidden' || $(this)[0].type=='number' || $(this)[0].type=='tel' || $(this)[0].type=='password' || $(this)[0].type=='select-one' || $(this)[0].type=='textarea' || $(this)[0].type=='text'){
			params[$(this).attr('id')] = $.trim($(this).val());
		}else if($(this)[0].type=='radio'){
			if($(this).attr('name')){
				params[$(this).attr('name')] = $('input[name='+$(this).attr('name')+']:checked').val();
			}
		}else if($(this)[0].type=='checkbox'){
			if($(this).attr('name') && !chk[$(this).attr('name')]){
				s = [];
				chk[$(this).attr('name')] = 1;
				$('input[name='+$(this).attr('name')+']:checked').each(function(){
					s.push($(this).val());
				});
				params[$(this).attr('name')] = s.join(',');
			}
		}
	});
	chk=null,s=null;
	return params;
}

//只能輸入數字和小數點
WST.isNumberdoteKey = function(evt){
	var e = evt || window.event;
	var srcElement = e.srcElement || e.target;

	var charCode = (evt.which) ? evt.which : event.keyCode;
	if (charCode > 31 && ((charCode < 48 || charCode > 57) && charCode!=46)){
		return false;
	}else{
		if(charCode==46){
			var s = srcElement.value;
			if(s.length==0 || s.indexOf(".")!=-1){
				return false;
			}
		}
		return true;
	}
}
WST.limitDecimal = function(obj,len){
	var s = obj.value;
	if(s.indexOf(".")>-1){
		if((s.length - s.indexOf(".")-1)>len){
			obj.value = s.substring(0,s.indexOf(".")+len+1);
		}
	}
	s = null;
}
// 倒计时
WST.countDown = function(opts){
	var itvTime = (opts.countDownType==1)?100:1000;
	var f = {
		zero: function(n){
			var n = parseInt(n, 10);
			if(n > 0){
				if(n <= 9){
					n = "0" + n;
				}
				return String(n);
			}else{
				return "0";
			}
		},
		count: function(){
			if(opts.nowTime){
				var d = new Date();
				d.setTime(opts.nowTime.getTime()+itvTime);
				opts.nowTime = d;
				d = null;
			}else{
				opts.nowTime = new Date();
			}
			//现在将来秒差值
			var dur = 0;
			var pms = {
				msec: "0",
				sec: "0",
				mini: "0",
				hour: "0",
				day: "0"
			};
			var dur = Math.round((opts.endTime.getTime() - opts.nowTime.getTime()));
			if(dur >= 0){
				pms.msec = Math.floor(dur / 100 % 10);
				pms.sec = Math.floor((dur /1000 % 60)) > 0? f.zero(dur / 1000 % 60) : "00";
				pms.mini = Math.floor((dur / 60000)) > 0? f.zero(Math.floor((dur / 60000)) % 60) : "00";
				pms.hour = Math.floor((dur / 3600000)) > 0? f.zero(Math.floor((dur / 3600000)) % 24) : "00";
				pms.day = Math.floor((dur / 86400000)) > 0? f.zero(Math.floor(dur / 86400000)) : "00";
			}
			pms.last = dur;
			pms.nowTime = opts.nowTime;
			opts.callback(pms);
			if(pms.last<=0)clearInterval(itv);
		}
	};
	var itv = setInterval(f.count, itvTime);
	return itv;
}
WST.TransLang = function(str){
    var lang = {'zh-tw':0,'en':1,'zh-cn':2};
    str = str.split('{L}');
    for(var i=0;i<3;i++){
        if((str.length-1)<i)str[i] = str[0];
    }
    return str[lang[WST.conf.sysLang]];
}
WST.lang = function(str,arr){
	if(typeof(arr)=='undefined')return WSTLang[str];
	console.log(str);
	console.log(WSTLang);
	str = WSTLang[str];
	console.log(str);
	for(var i=0;i<arr.length;i++){
        str = str.replace('%s',arr[i]);
	}
	return str;
}



WST.showAreacodeBox = function(){
    jQuery('#cover2').attr("onclick","javascript:WST.hideAreacodeBox();").show();
    jQuery('#areacodeBox').animate({"bottom": 0}, 500);
}
WST.hideAreacodeBox = function(){
    jQuery('#areacodeBox').animate({'bottom': '-100%'}, 500);
    jQuery('#cover2').hide();
}
WST.checkAreaCode = function(){
	var areaCode='';
    $('#areacodeBox .active').each(function(k,v){
        if($(this).prop('checked')){
            areaCode = $(this).val();
        }
    });
	$("#area_code").html(areaCode);
	$("#areaCode").val(areaCode);
    WST.hideAreacodeBox();
}