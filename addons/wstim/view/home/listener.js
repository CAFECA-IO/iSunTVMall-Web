// pc端监听文件
var _connectInfo = {
	userId:"",
	loginName:"",
	shopId:"",
	isService:"",
	userPhoto:"",
	shopImg:"",
},g_ws;


/**  
 * 串联加载指定的脚本 
 * 串联加载[异步]逐个加载，每个加载完成后加载下一个 
 * 全部加载完成后执行回调 
 * @param array|string 指定的脚本们 
 * @param function 成功后回调的函数 
 * @return array 所有生成的脚本元素对象数组 
 */
  
function seriesLoadScripts(scripts,callback) { 
  if(typeof(scripts) != "object") var scripts = [scripts]; 
  var HEAD = document.getElementsByTagName("head").item(0) || document.documentElement; 
  var s = new Array(), last = scripts.length - 1, recursiveLoad = function(i) { //递归 
    s[i] = document.createElement("script"); 
    s[i].setAttribute("type","text/javascript"); 
    s[i].onload = s[i].onreadystatechange = function() { //Attach handlers for all browsers 
      if(!/*@cc_on!@*/0 || this.readyState == "loaded" || this.readyState == "complete") { 
        this.onload = this.onreadystatechange = null; this.parentNode.removeChild(this);  
        if(i != last) recursiveLoad(i + 1); else if(typeof(callback) == "function") callback(); 
      } 
    } 
    s[i].setAttribute("src",scripts[i]); 
    HEAD.appendChild(s[i]); 
  }; 
  recursiveLoad(0); 
}



window.onload = function(){
	// // 加载音频文件
	if(!document.getElementById("audio")){
		var audio = document.createElement("audio");
		audio.id = "audio";
		audio.controls = "controls";
		audio.style = "display: none";
		audio.src = IM_PATH+"home/img/voice.mp3";
		document.getElementsByTagName('head')[0].appendChild(audio);
	}
	// 加载脚本文件
	var _arr = [IM_PATH+"static/js/common.js"];
	if(typeof jQuery == 'undefined'){
		_arr.unshift(IM_PATH+"static/js/jquery.min.js");
	}else{
		//jQuery cookie
		jQuery.cookie=function(name,value,options){if(typeof value!=="undefined"){options=options||{};if(value===null){value="";options.expires=-1}var expires="";if(options.expires&&(typeof options.expires==="number"||options.expires.toUTCString)){var date;if(typeof options.expires==="number"){date=new Date();date.setTime(date.getTime()+(options.expires*24*60*60*1000))}else{date=options.expires}expires="; expires="+date.toUTCString()}var path=options.path?"; path="+options.path:"";var domain=options.domain?"; domain="+options.domain:"";var secure=options.secure?"; secure":"";document.cookie=[name,"=",encodeURIComponent(value),expires,path,domain,secure].join("")}else{var cookieValue=null;if(document.cookie&&document.cookie!=""){var cookies=document.cookie.split(";");for(var i=0;i<cookies.length;i++){var cookie=jQuery.trim(cookies[i]);if(cookie.substring(0,name.length+1)==(name+"=")){cookieValue=decodeURIComponent(cookie.substring(name.length+1));break}}}return cookieValue}};
	}
	// 加载脚本文件
	seriesLoadScripts(_arr,listenerInit);

		
}
function listenerInit(){
	getApis(function(){
		$.get(APIS['listenerData'],function(res){
			if(res.status==1){
				_connectInfo =  res.data;
				// 连接服务器
				connectServer();
			}
		})
		
	})
	
}



// 设置未读消息数
function setShopUnReadNum(shopUnReadNum){
	if($.cookie('shopChatPage')!='true'){
		// 当前未打开
		$.cookie('shopUnReadNum',shopUnReadNum,{path:'/'});
	}
}



var Notification = window.Notification || window.mozNotification || window.webkitNotification;


// 连接服务器
function connectServer(){
	/**
	* 连接服务器
	*/
	if(!APIS['imServer']){
	    return;
	}
	var _server = APIS['imServer'];
	// g_ws.isService
	if(!g_ws){
	    g_ws = new WebSocket(_server);
	}else if(g_ws.readyState!=1){
	    g_ws = new WebSocket(_server);
	}
	// 连接服务器
	g_ws.onopen = function(){
		if(Notification.permission!='granted')Notification.requestPermission(function(status){console.log('NotificationPermission',status)});
	    var sendData = {
	                    uid:_connectInfo.userId,// 用户id
	                    userName:_connectInfo.loginName,// 用户名
	                    role:'lisenter',// 角色
	                    shopId:_connectInfo.shopId// 所属店铺id
	                    };
	    // 监听新消息
	    g_ws.send(JSON.stringify(sendData));
	    // 若为客服身份，则执行客服登录
	    if(_connectInfo.shopId>0 && _connectInfo.isService){
			 // 角色
			 sendData.serviceId = _connectInfo.serviceId;
	         sendData.role='worker';
	         sendData.type='login';
			g_ws.send(JSON.stringify(sendData));
			
			var _shopHtmlCode = [];
				_shopHtmlCode.push(
					'<li id="imUnRead">',
					'<a onclick="$(\'#imUnReadMsg\').hide()" ',
					'href="'+APIS['pcShopIndex']+'"',
					'target="blank" title="未读消息数"><i class="fa fa-headphones fa-lg"></i>',
					'<span id="imUnReadMsg" style="position: absolute; top: 21%; right: -8%; color: rgb(255, 255, 255); border-radius: 30%; display: block;font-weight: bold;background: #f16609;padding: 2px 6px;font-size: 11px;line-height: initial;">',
					'0</span></a></li>'
				);

				
				if($('#imUnRead').length==0)$('#toMsg').before(_shopHtmlCode.join(''));


	    }
	}

	g_ws.onmessage = function(e){
	    var _data = JSON.parse(e.data);
	    console.log(WST.lang('wstim_news1'),_data);
	    switch(_data.type){
			case 'serverPushData':
				layer.open({
					type: 1
					,title:WST.lang('wstim_news2')
					,offset: 'rb' 
					,content: '<div style="padding:20px 80px;">'+_data.content+'</div>'
					,btn: [WST.lang('wstim_close_all'),WST.lang('wstim_cat')]
					,btnAlign: 'c' //按钮居中
					,shade: 0 //不显示遮罩
					,yes: function(){
						layer.closeAll();
					},
					btn2:function(){
						// 跳转商城消息页
						window.open(entryMessage());
					}
					});
			break;
	    	// 接收到新消息
            case 'newMsg':
                if(_data._object=='user'){
                	userNotice(_data)
                    ++g_ws.userUnRead;
                }
                if(_data._object=='service'){
                	var currUnReadNum = $.cookie('shopUnReadNum') || 1;
					// 设置未读消息数
					setShopUnReadNum(++currUnReadNum);


                	serviceNotice();
                }
            break;
            // 未读消息数
            case 'unReadMsgNum':
                if(_data.userUnRead>0)userNotice();
                if(_data.serviceUnRead>0){
                	setShopUnReadNum(_data.serviceUnRead);
                	serviceNotice();
                }
            break;
        }
	};

	g_ws.onclose = function(evt){};
	g_ws.onerror = function(evt){};
}

// "用户"身份桌面推送
var userNotice = function(_data){
	var title = WST.lang('wstim_news3');
	var body = WST.lang('wstim_news4');
	// 提醒的消息
	var instance = new Notification(
		title, {
				body: body,
				icon:_connectInfo.userPhoto,// 用户头像
				tag:'123',// 用户唯一标识
			}
		);
		instance.onclick = function () {
			var _flag = $.cookie('userChatPage');
			if(_flag!='true'){
				var url = APIS['pcUserIndex']+"?shopId=1";
				window.open(url);
			}
			instance.close();
		};
		instance.onerror = function () {
			// console.log('出错了~');
			// Something to do
		};
		instance.onshow = function () {
			// Something to do
			voiceNotice();
		};
		instance.onclose = function () {
			// Something to do
		};
}
// "客服"身份桌面推送
var serviceNotice = function(unReadNum){
	var _flag = $.cookie('shopChatPage');
	// 设置未读消息数
	if(_flag!='true'){
		// 当前未打开
		var _unReadNum = $.cookie('shopUnReadNum');
		$('#imUnReadMsg').html(_unReadNum).show();
	}
	// 提醒的消息
	var instance = new Notification(
		WST.lang('wstim_news3'), {
				body: WST.lang('wstim_news4'),
				icon:_connectInfo.shopImg,// 用户头像
				tag:'321',// 用户唯一标识
			}
		);
		instance.onclick = function () {
			if(_flag!='true'){
				var url = APIS['pcShopIndex'];
				window.open(url);
			}
			instance.close();
		};
		instance.onerror = function () {
			console.log('出错了~');
			// Something to do
		};
		instance.onshow = function () {
			voiceNotice();
			// Something to do
		};
		instance.onclose = function () {
			// Something to do
		};
}
// 消息提示音
var voiceNotice = function(){
	var audio_box = document.getElementById("audio");
	audio_box.play().catch(e=>{
		// 消息提示音播放失败
	});
}
