var _connectInfo = {
  userId:"",
  loginName:"",
  shopId:"",
  isService:""
}
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
        modifyBtn();
      }
    })
    });
    // 
}
function entryChatList(){
  location.href = (_connectInfo.shopId>0 && _connectInfo.isService)?APIS['moShopChatList']:APIS['moUserChatList']
  
}
function modifyBtn(){
    // 替换底部图标
    var HEAD = document.getElementsByTagName("head").item(0) || document.documentElement; 
    var _style = document.createElement('style');

    var style = '.wst-menus .chatList {background: url('+IM_PATH+'/mobile/img/footer-message.png) center center no-repeat; background-size: 100%; }\
    .wst-menus .unReadNum {display: none; position: absolute; width: 17px; height: 17px; background:#de0202; color: #fff; border-radius: 50%; font-size: 0.11rem; text-align: center; line-height: 18px; right: 5px; top: 1px;}\
    .chat-word {float: left; width: 100%; text-align: center; font-size: 0.12rem; color: #9EA0AA; height: 16px; line-height: 16px; }.J_followbox{display: none}#chatListBox{position: relative;}'
    _style.innerHTML = style;
    HEAD.appendChild(_style);


    var msgBtn = '<div class="ui-col ui-col"><a onclick="entryChatList()">'
        msgBtn += '<p id="chatListBox"><span id="botUnRead" class="unReadNum">0</span><span class="icon chatList"></span><span class="chat-word">'+WST.lang('wstim_message')+'</span></p></a></div>'
    $('.J_im_cart').before(msgBtn);
}

function connectServer(){
		if(!APIS['imServer']){
		    return;
		}
	    var _gws = new WebSocket(APIS['imServer']);
        // 连接服务器
        _gws.onopen = function(){
            var sendData = {
                            uid:_connectInfo.userId,// 用户id
                            userName:_connectInfo.loginName,// 用户名
                            role:'lisenter',// 角色
                            shopId:_connectInfo.shopId// 所属店铺id
                            };
            _gws.send(JSON.stringify(sendData));
            // 若为客服身份，则执行客服登录
            if(_connectInfo.shopId>0 && _connectInfo.isService){

                 // 角色
                 sendData.role='worker';
                 sendData.type='login';
                _gws.send(JSON.stringify(sendData));
            }
        }
        _gws.onmessage = function(e){
            let _data = JSON.parse(e.data);
            console.log('_data``',_data);
            switch(_data.type){
                case 'serverPushData':
                    if(window.jsAlert){
                      WSTImNotify(_data.content);
                    }else{
                      // 加载css文件
                      var head = document.getElementsByTagName('head')[0];
                      var link = document.createElement('link');
                      link.href = IM_PATH+"/mobile/static/alertjs/alertjs.css";
                      link.rel = 'stylesheet';
                      link.type = 'text/css';
                      head.appendChild(link);
                      var _arr = [IM_PATH+"/mobile/static/alertjs/alertjs.js"];
                      // 加载脚本文件
                      seriesLoadScripts(_arr,function(){
                        
                        WSTImNotify(_data.content);
                      });
                    }

                    


                break;
                case 'newMsg':
                	var _obj = $('#botUnRead');
                  var _curr = _obj.html();
                    	_obj.show().html(++_curr);
                 console.log(WST.lang('wstim_tip23'))
                break;
                case 'unReadMsgNum':
                    var totalNum = _data.userUnRead+_data.serviceUnRead;
                    var _obj = $('#botUnRead');
                    (totalNum>0)?_obj.show().html(totalNum):_obj.hide();
                    console.log(WST.lang('wstim_unread_messages')+',', totalNum)

                break;
            }
        };
        _gws.onclose = function(evt){}
        _gws.onerror = function(evt){}

}