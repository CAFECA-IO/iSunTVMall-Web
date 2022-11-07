var WST = WST || {};
var APIS = {};
var imImgArr = [];
var IM_DOMAIN = 'http://localhost/wstmartp/';
var IMAGE_DOMAIN = 'http://localhost/wstmartp/';
var IM_PATH = IM_DOMAIN+'addons/wstim/view'
// api地址【必填项】
var apiUrl = IM_DOMAIN+'addon/wstim-chats-getApis';
// 图片前缀
var CHAT_IMG_PREFIX = ".j_chat_goodsImg_";
var CHAT_ORDER_ITEM_PREFIX = ".j_chat_orderItem_";
function getApis(callback){
  // 获取接口信息
  $.get(apiUrl, function(res){
     if(res.status==1){
        APIS = res.data;
        callback && callback();
     }else{
        console.log(res.msg)
     }
  });
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
WST.checkBrowser = function(){
	return {
		mozilla : /firefox/.test(navigator.userAgent.toLowerCase()),
		webkit : /webkit/.test(navigator.userAgent.toLowerCase()),
	    opera : /opera/.test(navigator.userAgent.toLowerCase()),
	    msie : /msie/.test(navigator.userAgent.toLowerCase())
	}
}
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
// 解析url参数
function parseParams(query){
  var re = /([^&=]+)=?([^&]*)/g,
      decodeRE = /\+/g,
      decode = function (str) { return decodeURIComponent( str.replace(decodeRE, " ") ); };
      return (function(query) {
        var params = {}, e;
        while ( e = re.exec(query) ) params[ decode(e[1]) ] = decode( e[2] );
        return params;
      })(query)
}



WST.imToJson = function(str,noAlert){
  var json = {};
  try{
    if(typeof(str )=="object"){
      json = str;
    }else{
      json = eval("("+str+")");
    }
    if(typeof(noAlert)=='undefined'){
      if(json.status && json.status=='-999'){
        alert(WST.lang('wstim_please_login_again'))
      }else if(json.status && json.status=='-998'){
        alert(WST.lang('wstim_you_do_not_have_permission_to_operate'));
        return;
      }
    }
  }catch(e){
    alert(lang('wstim_system_error')+":"+e.getMessage);
    json = {};
  }
  return json;
}

function isWeiXin(){
  //window.navigator.userAgent属性包含了浏览器类型、版本、操作系统类型、浏览器引擎类型等信息，这个属性可以用来判断浏览器类型
  var ua = window.navigator.userAgent.toLowerCase();
  //通过正则表达式匹配ua中是否含有MicroMessenger字符串
  if(ua.match(/MicroMessenger/i) == 'micromessenger'){
    return true;
  }else{
    return false;
  }
}
// 判断是否为手机访问
function isMobile(){
  return (/(iPhone|iOS|Android|Windows Phone|iPod|BlackBerry|webOS)/i.test(navigator.userAgent))
}

// 进入店铺主页
var entryShop  = function(shopId){
  var url = "";
  if (isWeiXin()) {
    url = APIS['wxShopHome']+'?shopId='+shopId;
  } else if (isMobile()) {
    url = APIS['moShopHome']+shopId;
  } else {
    url = APIS['pcShopHome']+shopId;
  }
  window.open(url);
}
// 进入商品详情
var entryGoods = function (goodsId) {
  if (isWeiXin()) {
    return APIS['wxGoodsDetail'] + '?goodsId=' + goodsId;
  } else if (isMobile()) {
    return APIS['moGoodsDetail'] + goodsId;
  } else {
    return APIS['pcGoodsDetail'] + goodsId;
  }
}
// 进入订单详情
var entryOrder = function (orderId) {
  if (isWeiXin()) {
    return APIS['wxOrderDetail'] + '?id=' + orderId;
  }
  else if (isMobile()) {
    return APIS['moOrderDetail'] + '?id=' + orderId;
  } else {
    return APIS['pcOrderDetail'] + '?id=' + orderId;
  }
}
// 手机端收到商城消息时的提示
var WSTImNotify = function(content){
  jsAlert && jsAlert.Success("["+WST.lang('wstim_mall_news')+"]"+content, {dismiss:false, onContentClick:function(){
    window.open(entryMessage());
  }});
}

// 进入商城消息列表页
var entryMessage = function () {
  if (isWeiXin()) {
    return APIS['wxMessage'];
  }
  else if (isMobile()) {
    return APIS['moMessage'];
  } else {
    return APIS['pcMessage'];
  }
}



function isJSON(str) {
    if (typeof str == 'string') {
        try {
            var obj=JSON.parse(str);
            if(typeof obj == 'object' && obj ){
                return true;
            }else{
                return false;
            }
        } catch(e) {
            return false;
        }
    }
}
// 获取商品信息
function getChatGoods(id){
  var _key = CHAT_IMG_PREFIX+id;
  $.get(APIS['getChatGoods'],{id:id},function(res){
    if(res.status==1){
      $(_key).attr("src", IMAGE_DOMAIN+'/'+res.data.goodsImg);
      $(_key).parent().parent().find(".g-price").html(WST.lang('currency_symbol')+res.data.shopPrice)
    }
  })

}
// 获取订单信息
function getOrderInfo(orderId){
  var _key = CHAT_ORDER_ITEM_PREFIX+orderId;
  $.get(APIS['getOrderInfo'],{orderId:orderId},function(res){
    if(res.status==1){
      var _data = res.data;
      var _goods = _data.list[0];
      var code = '<a class="order-box" href="'+entryOrder(orderId)+'" target="_blank">\
                  <div class="order-main">\
                    <p class="om-head">\
                      <span class="f12c999 c333">'+WST.lang('wstim_order_number')+'：'+ _data.orderNo + '</span>\
                      <span class="f12c999">'+ _data.createTime + '</span>\
                    </p>\
                    <div class="goods-box">\
                        <div class="g-main clearfix">\
                          <div class="g-l">\
                              <img src="'+ IMAGE_DOMAIN + '/' + _goods.goodsImg + '" alt="" />\
                          </div>\
                          <div class="g-r">\
                              <div class="g-name">'+ _goods.goodsName + '</div>\
                              <div class="ob-btm">\
                                <span class="f12c999">\
                                  <span class="g-price">'+WST.lang('currency_symbol')+ _data.realTotalMoney + '</span>\
                                </span>\
                                <span class="os">'+ _data.orderStatus + '</span>\
                              </div>\
                          </div>\
                        </div>\
                    </div>\
                  </div>\
                </a>';
      var parent = $(_key).parent();
      parent.html(code);
    }
  })

}

// 处理链接、图片
function dealContent(msg){

  if(isJSON(msg.content)){
    var _json = JSON.parse(msg.content);
    switch(_json.type){
      case 'goods':
        // 获取商品图片
        var _key = CHAT_IMG_PREFIX+_json.goodsId;
        //if($(_key).length==0){
          getChatGoods(_json.goodsId);
        //}
        // msg.content = '<a href="'+entryGoods(_json.goodsId)+'" target="_blank">'+_json.content+'</a>'
        msg.content = '<div class="goods-box">\
          <a href="'+entryGoods(_json.goodsId)+'" target="_blank" href="" class="g-main clearfix">\
            <div class="g-l">\
              <img class="'+_key.replace('.', "")+'" src="" alt="" />\
            </div>\
            <div class="g-r">\
              <div class="g-name">'+_json.content+'</div>\
              <div class="g-price">'+WST.lang('currency_symbol')+'0.00</div>\
            </div>\
          </a>\
      </div>'
      break;
      case 'orders':
        var _key = CHAT_ORDER_ITEM_PREFIX+_json.orderId;
        msg.content = '<a class="'+_key.replace('.', "")+'" href="'+entryOrder(_json.orderId)+'" target="_blank">'+_json.content+'</a>';
        getOrderInfo(_json.orderId);
      break;
      case 'image':
        var maxWidth = "300px;";
        var src = _json.content.indexOf('http') != -1 ? _json.content : IMAGE_DOMAIN + _json.content;
        imImgArr.push({ src:src, w:WST.pageWidth(), h:100 });
        var i = imImgArr.length-1;
        getImgWH(src, i);
        msg.content = '<div onclick="imViewImg('+i+')" class="j-imchatimg"><img style="width:100%;max-width:'+maxWidth+'" src="'+src+'" /></div>';
      break;
    }
  }
}
// 获取图片宽高
function getImgWH(img_url, index){
  // 创建对象
  var img = new Image();
  // 改变图片的src
  img.src = img_url;
  // 判断是否有缓存
  if (img.complete) {
    // 打印
    imImgArr[index].w = img.width;
    imImgArr[index].h = img.height;
  } else {
    // 加载完成执行
    img.onload = function () {
      //  打印
      imImgArr[index].w = img.width;
      imImgArr[index].h = img.height;
    }
  }
}


// 查看大图
function imViewImg(index){
  var pswpElement = document.querySelectorAll('.pswp')[0];
  // build items array
  if(!imImgArr || imImgArr.length==0)return;
  // define options (if needed)
  var options = {
    // optionName: 'option value'
    // for example:
    index: index // start at first slide
  };
  // Initializes and opens PhotoSwipe
  var gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, imImgArr, options);
  gallery.init();
}


WST.imGetParams = function(obj){
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

WST.imLoad = function(options){
    var opts = {};
    opts = $.extend(opts,{time:0,icon:'wstloading',shade: [0.4, '#000000'],offset: '200px',area: ['280px', '75px']},options);
    return layer.msg(opts.msg, opts);
}
WST.imOpen = function(options){
  var opts = {};
  opts = $.extend(opts, {offset:'100px'}, options);
  return layer.open(opts);
}


/**
* 上传图片
*/
function getUploadServer() {
  // 微信
  if (isWeiXin()) return APIS.wxUploadServer;
  // 手机
  if (isMobile()) return APIS.moUploadServer;
  // pc前台
  return APIS.pcUploadServer;
}

WST.imUpload = function (opts) {
  var _opts = {};
  _opts = $.extend(_opts, {
    duplicate: true, auto: true,
    swf: window.IM_PATH + '/static/webuploader/Uploader.swf',
    server: getUploadServer()
  }, opts);
  var uploader = WebUploader.create(_opts);
  uploader.on('uploadSuccess', function (file, response) {
    var json = WST.imToJson(response._raw);
    if (_opts.callback) _opts.callback(json, file);
  });
  uploader.on('uploadError', function (file) {
    if (_opts.uploadError) _opts.uploadError();
  });
  uploader.on('uploadProgress', function (file, percentage) {
    percentage = percentage.toFixed(2) * 100;
    if (_opts.progress) _opts.progress(percentage);
  });
  return uploader;
}


/**
 * 将以base64的图片url数据转换为Blob
 */
function base64UrlToBlob(dataurl) {
  var arr = dataurl.split(',');
  var mime = arr[0].match(/:(.*?);/)[1];
  var bstr = atob(arr[1]);
  var n = bstr.length;
  var u8arr = new Uint8Array(n);
  while (n--) {
    u8arr[n] = bstr.charCodeAt(n);
  }
  return new Blob([u8arr], { type: mime });
}

/*! Lazy Load 1.9.3 - MIT license - Copyright 2010-2013 Mika Tuupola */
!function (a, b, c, d) { var e = a(b); a.fn.lazyload = function (f) { function g() { var b = 0; i.each(function () { var c = a(this); if (!j.skip_invisible || c.is(":visible")) if (a.abovethetop(this, j) || a.leftofbegin(this, j)); else if (a.belowthefold(this, j) || a.rightoffold(this, j)) { if (++b > j.failure_limit) return !1 } else c.trigger("appear"), b = 0 }) } var h, i = this, j = { threshold: 0, failure_limit: 0, event: "scroll", effect: "show", container: b, data_attribute: "original", skip_invisible: !0, appear: null, load: null, placeholder: "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC" }; return f && (d !== f.failurelimit && (f.failure_limit = f.failurelimit, delete f.failurelimit), d !== f.effectspeed && (f.effect_speed = f.effectspeed, delete f.effectspeed), a.extend(j, f)), h = j.container === d || j.container === b ? e : a(j.container), 0 === j.event.indexOf("scroll") && h.bind(j.event, function () { return g() }), this.each(function () { var b = this, c = a(b); b.loaded = !1, (c.attr("src") === d || c.attr("src") === !1) && c.is("img") && c.attr("src", j.placeholder), c.one("appear", function () { if (!this.loaded) { if (j.appear) { var d = i.length; j.appear.call(b, d, j) } a("<img />").bind("load", function () { var d = c.attr("data-" + j.data_attribute); c.hide(), c.is("img") ? c.attr("src", d) : c.css("background-image", "url('" + d + "')"), c[j.effect](j.effect_speed), b.loaded = !0; var e = a.grep(i, function (a) { return !a.loaded }); if (i = a(e), j.load) { var f = i.length; j.load.call(b, f, j) } }).attr("src", c.attr("data-" + j.data_attribute)) } }), 0 !== j.event.indexOf("scroll") && c.bind(j.event, function () { b.loaded || c.trigger("appear") }) }), e.bind("resize", function () { g() }), /(?:iphone|ipod|ipad).*os 5/gi.test(navigator.appVersion) && e.bind("pageshow", function (b) { b.originalEvent && b.originalEvent.persisted && i.each(function () { a(this).trigger("appear") }) }), a(c).ready(function () { g() }), this }, a.belowthefold = function (c, f) { var g; return g = f.container === d || f.container === b ? (b.innerHeight ? b.innerHeight : e.height()) + e.scrollTop() : a(f.container).offset().top + a(f.container).height(), g <= a(c).offset().top - f.threshold }, a.rightoffold = function (c, f) { var g; return g = f.container === d || f.container === b ? e.width() + e.scrollLeft() : a(f.container).offset().left + a(f.container).width(), g <= a(c).offset().left - f.threshold }, a.abovethetop = function (c, f) { var g; return g = f.container === d || f.container === b ? e.scrollTop() : a(f.container).offset().top, g >= a(c).offset().top + f.threshold + a(c).height() }, a.leftofbegin = function (c, f) { var g; return g = f.container === d || f.container === b ? e.scrollLeft() : a(f.container).offset().left, g >= a(c).offset().left + f.threshold + a(c).width() }, a.inviewport = function (b, c) { return !(a.rightoffold(b, c) || a.leftofbegin(b, c) || a.belowthefold(b, c) || a.abovethetop(b, c)) }, a.extend(a.expr[":"], { "below-the-fold": function (b) { return a.belowthefold(b, { threshold: 0 }) }, "above-the-top": function (b) { return !a.belowthefold(b, { threshold: 0 }) }, "right-of-screen": function (b) { return a.rightoffold(b, { threshold: 0 }) }, "left-of-screen": function (b) { return !a.rightoffold(b, { threshold: 0 }) }, "in-viewport": function (b) { return a.inviewport(b, { threshold: 0 }) }, "above-the-fold": function (b) { return !a.belowthefold(b, { threshold: 0 }) }, "right-of-fold": function (b) { return a.rightoffold(b, { threshold: 0 }) }, "left-of-fold": function (b) { return !a.rightoffold(b, { threshold: 0 }) } }) }(jQuery, window, document);
