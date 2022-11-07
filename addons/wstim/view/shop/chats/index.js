var timer={},onlineList={},_reconnectTimer;
var userInfoObj = {}, sendKeyCode;


// 进入订单详情
var entryOrder = function (orderId) {
	return APIS['pcShopOrderDetail'] + '?id=' + orderId;
}


function loadUserInfo(list){
	var userIds = Object.keys(list);
	for(var i in userIds){
		var _userId = userIds[i];
		if(!userInfoObj[_userId]){
			// 先设置为空对象
			userInfoObj[_userId] = {};
			getUserInfo(_userId);
		}
	}
}
var getUserInfo = function(userId){
	var url = APIS['shopGetUserInfo'];
	$.post(url,{userId:userId},function(data){
			userInfoObj[userId] = data;
			// 将值设置到左侧列表中
			var _target = $('#userId_'+userId);
			_target.attr('loginName', data.loginName);
			_target.find('img').attr('src', data.userPhoto);
			_target.find('.category').find('span').html(data.loginName);
	});
}
function showUserInfo(userId){
	var _uInfo = userInfoObj[userId];
	$('#imr_upic').attr('src',_uInfo.userPhoto);
	$('#imr_loginName').html(_uInfo.loginName);
	$('#imr_qq').html(_uInfo.userQQ);
	$('#imr_rankName').html(_uInfo.rankName);
	$('#imr_phone').html(_uInfo.userPhone);
}
function chatList(){
    $.post(APIS['shopChatList'],{},function(data,textStatus){
		var _data = WST.imToJson(data);
        if (_data.status == 1) {
            if(!_data.data)return;
            // c_{userId},s_{shopId}
			let _obj = {};
            _data.data.map(x=>{
                _obj[`${x.userId}`] = x;
            })
			loadUnReadList(_obj);
        } else {
        }

	});
}

function reConnect(){
	clearTimeout(_reconnectTimer);
	if(ws.readyState!=1){
		// 执行重连
		_reconnectTimer = setTimeout(function(){
			connectServer();
		}, 3000);
	}else{
		clearTimeout(_reconnectTimer);
	}
}


function connectServer(){
	ws = new WebSocket(APIS['imServer']);
	/**
	* 连接服务器
	*/
	ws.onopen = function(){
		var sendData = {type:'login',
						serviceId:sendId,
						userName:workerName,
						role:'worker',
						shopId:shopId};
		ws.send(JSON.stringify(sendData));
	};
	ws.onmessage = function(e){
	    var _data = JSON.parse(e.data);
	    switch(_data.type){
	    	case 'visit':
	    		var visit = $.cookie('visit');
	    		visit = (visit==undefined)?{}:JSON.parse(visit);
	    		var _goodsInfo = JSON.parse(_data.content);
	    		visit[_data.from] = _goodsInfo;
	    		$.cookie('visit',JSON.stringify(visit));
	    		var _index = '#userId_'+_data.from;
	    		if($(_index).hasClass('current')){
	    			showCurrVisit(_data.from);
	    		}
	    	break;
	    	case 'load':
	    		// 加入聊天列表
	    		loadList(_data.list);
	    		loadUserInfo(_data.list);
	    	break;
	    	case 'unReadMsg':
	    		// 加载未读留言
	    		loadUnReadList(_data.list);
	    		loadUserInfo(_data.list);
	    	break;
	    	case 'removeList':
	    		// 移出聊天列表
	    	break;
	    	case 'serviceStatus':
	    		if(_data.data.waitCount!=undefined)$('#waitCount').html(_data.data.waitCount);
	    		if(_data.data.chatCount!=undefined)$('#chatCount').html(_data.data.chatCount);
	    	break;
	    	case 'say':
	    		if(_data.role=='worker'){
		    		if(_data.from==sendId){
			    		// 发送消息给某人
			    		_doSendMsg(_data);
		    		}else{
		    			onlineList[_data.from] = true;
		    			// 接收某人消息
			    		// 1.判断当前是否与改用户聊天
			    		var _index = '#userId_'+_data.from;
			    		if($(_index).hasClass('current')){
			    			// 设置消息已读
			    			setRead(_data.from,$(_index))
			    			// 直接渲染消息
			    			receiveMsg(_data);
			    		}
			    		// 设置未读消息数及内容摘要
			    		setMsgNumAndContent(_data,$(_index));
		    		}
	    		}
	    	break;
	    	case 'conversation':
	    		loadUserInfo({[_data.from]:_data.from});
	    		// 用户在pc端刷新操作
	    		clearInterval(timer[_data.from]);
	    		// 标识用户为在线
	    		setUserOnline(_data.from);
	    		// 接收某人消息
	    		// 1.判断当前是否与改用户聊天
	    		var _index = '#userId_'+_data.from;
	    		if($(_index).hasClass('current')){
	    			// 设置消息已读
	    			setRead(_data.from,$(_index))
	    			// 直接渲染消息
	    			// receiveMsg(_data);
	    		}else{
	    			// 设置未读消息数及内容摘要
	    			setMsgNumAndContent(_data,$(_index),'join');
	    		}
	    	break;
	    	case 'userExit':
	    		// 接收消息者已离线
	    		setUserOffLine(_data.clientUid);
	    	break;
	    	default:
	    	break;
	    }
	};

	ws.onclose = function(evt)
	{
	  reConnect();
	  console.log('WebSocketClosed!');
	};
	ws.onerror = function(evt)
	{
	  reConnect();
	  console.log('WebSocketError!');
	};
}

function imInit(){
	// 获取对话列表
	chatList();
	// 截图发送
	pasteImg();
	// enter发送
	pressEnter();
	// 按键发送,默认按Enter发送
	sendKeyCode = $.cookie("sendKeyCode") || 13;
	$.cookie('shopChatPage', true,{path: '/'});
	// 建立服务器连接
	connectServer();
	// 右侧点击事件
	bindLeftLiClick();
	// 延迟加载
	$('.usersImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 100,placeholder:''});//会员默认头像
	$('.shopsImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 100,placeholder:''});//店铺默认头像

	// 图片上传
	WST.imUpload({
	    pick:'#uploadBtn',
	    formData: {dir:'users', isThumb:1},
	    accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
	    callback:function(f){
	      var json = WST.imToJson(f);
	      if(json.status==1){
	        var to = $('#userId').val();
	        if(to<=0)return;// 无接收对象
	        var uploadPhotoSrc = json.savePath+json.thumb;
			var content = JSON.stringify({type:"image",content:uploadPhotoSrc});
			var sendData = {role:'worker',content:content,type:'say',to:to};
			ws.send(JSON.stringify(sendData));
	      }else{
	        alert(json.msg);
	      }
	      },
	      progress:function(rate){
	         $('#uploadMsg').show().html(WST.lang('wstim_uploaded')+rate+"%");
	      }
	});
	// 获取快捷回复内容
	// getQuickReplys();


}
$(window).on('beforeunload', function(){
	$.cookie('shopChatPage', false,{path: '/'});
	var unReadNum = 0;
	$('li a div em').each(function(k,v){
		var _num = Number($(v).html());
		 	_num = !isNaN(_num)?_num:0;
		unReadNum += _num;
	})
	$.cookie('shopUnReadNum',unReadNum,{path: '/'});
});


// 设置当前浏览商品
var showCurrVisit = function(userId){
	var visit = $.cookie('visit');
	if(visit==undefined)return;
	visit = JSON.parse(visit);
	var goodsInfo = visit[userId];
	if(goodsInfo==undefined)return;
	$('.cv_l img').attr('src',IM_DOMAIN+goodsInfo.goodsImg);
	$('.cv_gname').html(goodsInfo.goodsName);
	$('.cv_price').html(WST.lang('currency_symbol')+goodsInfo.shopPrice);

	$('.cv_main').attr('href', entryGoods(goodsInfo.goodsId, goodsInfo.type));
	$('.curr_visit').css({height:120});
}
// 关闭正在浏览
function closeCurrVisit(){
	$('.curr_visit').css({height:0});
}
// 将用户设置为在线
function setUserOnline(uid){
	onlineList[uid] = true;
	$('#userId_'+uid).removeClass('off');
	if($('#userId').val()==uid){// 当前聊天用户在线
		$('#logoTitle').parent().removeClass('off')
	}
}
// 将用户设置为离线
function setUserOffLine(uid){
	timer[uid] = setInterval(function(){
		$('#userId_'+uid).addClass('off');
		onlineList[uid] = undefined;
		if($('#userId').val()==uid){// 当前聊天用户离线
			$('#logoTitle').parent().addClass('off')
		}
		clearInterval(timer[uid]);
	},3000)
}
// 右侧点击事件
function bindLeftLiClick(){
	$('.u-lst li').click(function(){
		if(!$(this).hasClass('current')){
			// 当前已选中则不进行任何操作
			$(this).siblings().removeClass('current');
			$(this).addClass('current');
			var _currShopName = $(this).find('.category span').html();
			$('#logoTitle').html(_currShopName);
			var _obj = $('#scrollDiv');
			// 清空当前聊天窗口
			_obj.html('<div class="chat-more" id="clickMore" onclick="clickMore(this)"><p class=""><span class="icon"></span><span class="txt">'+WST.lang('wstim_click_to_load_more')+'</span></p></div>');
			// 设置userId
			var _currUserId = $(this).data('uid');
			$('#userId').val(_currUserId);
			// 设置当前对话者名
			$('#loginName').val($(this).attr('loginName'));
			// 设置为已读
			var unReadCount = $(this).find('em').html();
			if(unReadCount>0)setRead(_currUserId,$(this));
			// 获取聊天记录渲染
			getChatRecord();
			if(!onlineList[_currUserId])$('#logoTitle').parent().addClass('off');
			// 获取用户信息在右侧显示
			showUserInfo(_currUserId);
			// 判断是否需要显示正在浏览
			showCurrVisit(_currUserId);
		}
	});
}
// 点击加载更多聊天记录
function clickMore(obj){
	var p = $(obj).find('p');
	if(!p.hasClass('loading')){
		$(obj).find('.txt').html(lang('wstim_click_to_load_more_chats'));
		p.addClass('loading');
	}
	var userId = $('#userId').val();
	var currPage = $('#currPage').val();
	var totalPage = $('#totalPage').val();
	if(parseInt(currPage)+1>totalPage){
		$(obj).find('.txt').html(lang('wstim_there_is_no_chat_record'));
		p.removeClass('loading');
		return;
	}
	var params = {
		userId:userId,
		page:parseInt(currPage)+1,
	};
	var url = APIS['shopChatQuery'];
	$.post(url,params,function(data){
		var loginName = $('#loginName').val();
		$('#currPage').val(data.current_page);
		$('#totalPage').val(data.last_page);
		var rows = data.data;
		var code = [];
		for(var i in rows){
			var _obj = rows[i];
			dealContent(_obj);
			if(_obj.from==sendId){
				code.push(
					'<div class="chat-txt"><div class="chat-area customer">',
					'<p class="name">'+_obj.userName+'</p>',
					'<div class="dialog"><i class="i_arr"></i><span class="e_tips"></span><table border="0" cellpadding="0" cellspacing="0">',
					'<tbody><tr><td class="lt"></td><td class="tt"></td><td class="rt"></td></tr><tr><td class="lm"></td><td class="mm"><div class="cont">',
					'<div>'+ _obj.content+'</div>',
					'</div></td><td class="rm"></td></tr><tr><td class="lb"></td><td class="bm"></td><td class="rb"></td></tr></tbody></table></div></div></div>'
	                  );
			}else{
				code.push(
					'<div class="chat-txt"><div class="chat-area service">',
		            '<p class="name">'+loginName+'</p>',
		            '<div class="dialog"><i class="i_arr"></i>',
		            '<table border="0" cellpadding="0" cellspacing="0"><tbody><tr>',
		            '<td class="lt"></td><td class="tt"></td><td class="rt"></td>',
		            '</tr><tr><td class="lm"></td><td class="mm"><div class="cont">',
		            '<div>'+_obj.content+'</div></div>',
		            '</td><td class="rm"></td></tr><tr><td class="lb"></td><td class="bm"></td><td class="rb"></td></tr></tbody></table></div></div></div>'
		                  );
			}
		}
		if(rows && rows.length>0){
			code.unshift('<div class="chat-more" id="clickMore" onclick="clickMore(this)"><p class=""><span class="icon"></span><span class="txt">'+WST.lang('wstim_click_to_load_more')+'</span></p></div>');
			$('#scrollDiv').prepend(code.join(''));
			$('#scrollDiv').scrollTop(0); // 滚动到顶部
			$(obj).remove();
		}else{
			$(obj).find('.txt').html(lang('wstim_there_is_no_chat_record'));
			p.removeClass('loading');
		}
	},'json');
}
// 标记为已读
function setRead(userId,obj){
	var url = APIS['shopSetRead'];
	$.post(url,{from:userId,to:sendId},function(data){
		if(data.status==1){
			obj.find('em').html('0').css({display:'none'});
		}
	},'json')
}
// 聊天记录
function getChatRecord(){
	var userId = $('#userId').val();
	var loginName = $('#loginName').val();
	var params = {
		userId:userId
	};
	var url = APIS['shopChatQuery'];
	$.post(
		url,
		params,
		function(data){
			$('#currPage').val(data.current_page);
			$('#totalPage').val(data.last_page);
			var rows = data.data;
			for(var i in rows){
				var _obj = rows[i];
				if(_obj.from!=userId){
					_doSendMsg(_obj);
				}else{
					receiveMsg(_obj);
				}
			}
		},
		'json');
}
// 发送消息
function sendMsg(){
	var content = $.trim($('#text_in').html());
	var to = $('#userId').val();
	if(content=='')return;
	if(parseInt(to)==0)return;
	//解析url
    content = content.replace(/(http|https):\/\/[\S]+/gi, function(url){
        return "<a target='_blank' href="+url+">"+url+"</a>";
    });
    var sendData = {role:'worker',content:content,type:'say',to:to};
    ws.send(JSON.stringify(sendData));
}
function _doSendMsg(msg){
	dealContent(msg);
	var _obj = $('#scrollDiv');
	var _userName = msg.userName || workerName;
	var code = [];
	code.push(
			'<div class="chat-txt"><div class="chat-area customer">',
			'<p class="name">'+_userName+'</p>',
			'<div class="dialog"><i class="i_arr"></i><span class="e_tips"></span><table border="0" cellpadding="0" cellspacing="0">',
			'<tbody><tr><td class="lt"></td><td class="tt"></td><td class="rt"></td></tr><tr><td class="lm"></td><td class="mm"><div class="cont">',
			'<div>'+ msg.content+'</div>',
			'</div></td><td class="rm"></td></tr><tr><td class="lb"></td><td class="bm"></td><td class="rb"></td></tr></tbody></table></div></div></div>'
	                  );
	$('#text_in').html('');
	_obj.append(code.join(''));
	_obj.scrollTop(_obj[0].scrollHeight);
}
// 接收消息
function receiveMsg(msg){
	dealContent(msg);
	var userName = $('#loginName').val();
	var _obj = $('#scrollDiv');
	var code = [];
	code.push(
			'<div class="chat-txt"><div class="chat-area service">',
            '<p class="name">'+userName+'</p>',
            '<div class="dialog"><i class="i_arr"></i>',
            '<table border="0" cellpadding="0" cellspacing="0"><tbody><tr>',
            '<td class="lt"></td><td class="tt"></td><td class="rt"></td>',
            '</tr><tr><td class="lm"></td><td class="mm"><div class="cont">',
            '<div>'+msg.content+'</div></div>',
            '</td><td class="rm"></td></tr><tr><td class="lb"></td><td class="bm"></td><td class="rb"></td></tr></tbody></table></div></div></div>'
                  );
	_obj.append(code.join(''));
	_obj.scrollTop(_obj[0].scrollHeight);
}
// 设置未读消息数及内容摘要
function setMsgNumAndContent(data,obj,type){
	// 判断当前是否已经存在该会话
	if(obj.length<=0){
		var	_content = ''
		if(data.content && data.content.content){
			_content = replaceContent(data.content.content);
		}
		var code = [];
		var _unread = '<em style="display: none">0</em>';
		if(data.unReadNum>0){
			_unread = '<em style="display: block">'+data.unReadNum+'</em>';
		}
		var imgSrc = data.userPhoto;
		if(data.userPhoto && data.userPhoto.indexOf('http')==-1){
			imgSrc = data.userPhoto;
		}
		code.push(
			'<li title="'+data.loginName+'" class="no-msg" data-uid="'+data.from+'" id="userId_'+data.from+'" data-uname="'+data.loginName+'">',
			'<a href="javascript:;"><span class="icon icon_1">',
			'<img src="'+imgSrc+'" class="left_img" />',
			'</span><div class="category">',
			'<span>'+data.loginName+'</span>',
			_unread,
			'</div><p class="tips">',
			'<span class="s02">'+(data.createTime?data.createTime:'')+'</span>',
			'<i class="i01"></i>',
			'<span class="s01">'+_content+'</span>',
			'</p></a></li>'
		);
		$('.u-lst').prepend(code.join(''));
		// 绑定点击事件
		bindLeftLiClick();
	}else{
		var _em = obj.find('em');
		// 获取已存在的未读消息数
		var _currUnReadNum = parseInt(_em.html());
			++_currUnReadNum;
		var _content = data.content;
		if(type=='join'){
			// 表示加入会话，场景：一开始为留言状态，之后用户上线
			_currUnReadNum = data.unReadNum;
			_content = data.content.content;
		}
		_em.html(_currUnReadNum);
		if(_currUnReadNum>0)_em.css({display: 'block'});
		obj.find('.s01').html(replaceContent(_content));
		obj.find('.s02').html(data.createTime);

		// 加入队列，修改为在线状态
		obj.removeClass('off');
	}
}
// 加载当前接待的用户列表
function loadList(rows){

	var code = [];
	for(var i in rows){
		var _data = rows[i];
			onlineList[i] = true;// 记录在线用户

		var	_content = ''
		if(_data.content && _data.content.content){
			_content = replaceContent(_data.content.content);
		}
		var _unread = '<em style="display: none">0</em>';
		if(_data.unReadNum>0){
			_unread = '<em style="display: block">'+_data.unReadNum+'</em>';
		}
		var imgSrc = _data.userPhoto;
		if(_data.userPhoto && _data.userPhoto.indexOf('http')==-1){
			imgSrc = _data.userPhoto;
		}
		if($("#userId_"+i).length>0){
			$("#userId_"+i).remove();
		}
		code.push(
			'<li title="'+_data.loginName+'" class="no-msg" data-uid="'+i+'" id="userId_'+i+'" data-uname="'+_data.loginName+'">',
			'<a href="javascript:;"><span class="icon icon_1">',
			'<img src="'+imgSrc+'" class="left_img" />',
			'</span><div class="category">',
			'<span>'+_data.loginName+'</span>',
			_unread,
			'</div><p class="tips">',
			'<span class="s02">'+(_data.createTime?_data.createTime:'')+'</span>',
			'<i class="i01"></i>',
			'<span class="s01">'+_content+'</span>',
			'</p></a></li>'
		);
	}
	// $('.u-lst').html(code.join(''));
	$('.u-lst').prepend(code.join(''));
	// 绑定点击事件
	bindLeftLiClick();
}
// 加载未读用户列表
function loadUnReadList(rows){
	var code = [];
	for(var i in rows){
		var _data = rows[i];
		var _className = '';
		if($('#userId_'+i).length<=0){
			_className = 'off';
			var	_content = ''
			if(_data.content && _data.content.content){
				_content = replaceContent(_data.content.content);
			}
			var _unread = '<em style="display: none">0</em>';
			if(_data.unReadNum>0){
				_unread = '<em style="display: block">'+_data.unReadNum+'</em>';
			}
			var imgSrc = _data.userPhoto;
			if(_data.userPhoto){
				if(_data.userPhoto && _data.userPhoto.indexOf('http')==-1){
					imgSrc = _data.userPhoto;
				}
			}
			code.push(
				'<li title="'+_data.loginName+'" class="no-msg '+_className+'" data-uid="'+i+'" id="userId_'+i+'" data-uname="'+_data.loginName+'">',
				'<a href="javascript:;"><span class="icon icon_1">',
				'<img src="'+imgSrc+'" class="left_img" />',
				'</span><div class="category">',
				'<span>'+_data.loginName+'</span>',
				_unread,
				'</div><p class="tips">',
				'<span class="s02">'+(_data.createTime?_data.createTime:'')+'</span>',
				'<i class="i01"></i>',
				'<span class="s01">'+_content+'</span>',
				'</p></a></li>'
			);
		}
	}
	$('.u-lst').append(code.join(''));
	// 绑定点击事件
	bindLeftLiClick();
}




// 替换消息中的图片、链接
function replaceContent(content){
	if(isJSON(content)){
		var _json = JSON.parse(content);
		switch(_json.type){
			case 'goods':
				content = WST.lang('wstim_tips7')
			break;
			case 'orders':
				content = WST.lang('wstim_tips7')
			break;
			case 'image':
				content = WST.lang('wstim_tips8');
			break;
		}
	}
	return content.replace(/<a\b[^>]+[^>]*>([\s\S]*?)<\/a>/g,WST.lang('wstim_tips7')).replace(/<img\b.*\/>/g,WST.lang('wstim_tips8'));
}

function pressEnter(){
	$("#switch_send").click(function(e){
		// 冒泡事件
		e.stopPropagation();
		// 设置当前选中
		$("#enter_set_"+sendKeyCode).addClass("curr-set").siblings().removeClass("curr-set");
		// 显示选项
		$("#enter_set").show();
	})
	$("#enter_set li").click(function(e){
		var _obj = $(this);
		_obj.addClass("curr-set").siblings().removeClass("curr-set");
		sendKeyCode = _obj.attr("keycode");
		$.cookie("sendKeyCode", sendKeyCode);
		$("#enter_set").hide();
	})
	$("#text_in").focus(function(e){
		$("#enter_set").hide();
	})
	$("#text_in").keypress(function (e) {
		if (e.which == sendKeyCode) {
			return sendMsg();
		}
	})
}

// 输入框发送截图
function pasteImg(){
	$('#text_in').off('paste').on('paste',function(e){


		e.preventDefault();
		var text = null;
		if (window.clipboardData && clipboardData.setData) {
			// IE
			text = window.clipboardData.getData('text');
		} else {
			text = (e.originalEvent || e).clipboardData.getData('text/plain');
		}
		if (document.body.createTextRange) {
			if (document.selection) {
				textRange = document.selection.createRange();
			} else if (window.getSelection) {
				sel = window.getSelection();
				var range = sel.getRangeAt(0);

				// 创建临时元素，使得TextRange可以移动到正确的位置
				var tempEl = document.createElement("span");
				tempEl.innerHTML = "&#FEFF;";
				range.deleteContents();
				range.insertNode(tempEl);
				textRange = document.body.createTextRange();
				textRange.moveToElementText(tempEl);
				tempEl.parentNode.removeChild(tempEl);
			}
			textRange.text = text;
			textRange.collapse(false);
			textRange.select();
		} else {
			// Chrome之类浏览器
			document.execCommand("insertText", false, text);
		}

		//获取剪切板的内容
		var clipboardData = event.clipboardData || window.clipboardData || event.originalEvent.clipboardData;
		var _ref = clipboardData.items, len;
		if (_ref) {
			for (var k = 0, len1 = _ref.length; k < len1; ++k) {
				var item = _ref[k];
				// 检测剪切板中是否有图片
				if (item.type.match(/^image\//)) {
					reader = new FileReader();
					reader.onload = function (event) {
						var to = $('#userId').val();
						if(to<=0)return;// 无接收对象
						sendScreenShot(event.target.result)
					};
					try {
						reader.readAsDataURL(item.getAsFile());
					} catch (error) {
						console.log('error``', error);
					}
					break;
				}
			}
		}
	});
}

// 发送截图
function sendScreenShot(base64Codes){
	var load = WST.imLoad({msg:WST.lang('wstim_loading')});
	var formData = new FormData();
	formData.append("imageName", base64UrlToBlob(base64Codes), "image.png");
	//ajax 提交form
	$.ajax({
		// 你后台的接收地址
		url: APIS.pcUploadServer+'?dir=users',
		type: "POST",
		data: formData,
		dataType: "json",
		processData: false,
		contentType: false,       
		success: function (data) {
			layer.close(load);
			var json = WST.imToJson(data);
			if(json.status==1){
				var to = $('#userId').val();
				if(to<=0)return;// 无接收对象
				var uploadPhotoSrc = json.savePath+json.thumb;
				var content = JSON.stringify({type:"image",content:uploadPhotoSrc});
				var sendData = {role:'worker',content:content,type:'say',to:to};
				ws.send(JSON.stringify(sendData));
			  }else{
				alert(json.msg);
			  }
		}
	});
}