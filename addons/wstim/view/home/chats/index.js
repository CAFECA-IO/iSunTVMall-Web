var ws,client_id,group,workerName,offline = false,_first = true,_service_eval = {},sendKeyCode;

function getConfig(){
  var params = parseParams(location.href.split('?')[1] || '');
  if(!params.shopId ||  params.shopId<=0){
    return layer.msg(WST.lang('wstim_tips4'));
  }
  var chatBaseUrl = APIS['userBaseData']+'?shopId='+params.shopId;
  if(params && params.goodsId>0){
	chatBaseUrl += '&goodsId='+params.goodsId;
  }
  $.get(chatBaseUrl, function(res){

    res = WST.imToJson(res);
    if(res.status!=1){
        return layer.msg(res.msg);
    }
    var _baseData = res.data;
    if(!_baseData || !_baseData.shopId || !_baseData.userId){
      return layer.msg(WST.lang('wstim_tips5'));
    }
    goodsInfo = _baseData.goods;
    // 请求最近会话列表
    getChatList()


    $('#shopId').val(_baseData.shopId);
    $('#userId').val(_baseData.userId);
    $('#shopImg').val(_baseData.shopImg);
    $('#shopName').val(_baseData.shopName);
    $('#loginName').val(_baseData.loginName);
    $('#logoTitle').html(_baseData.shopName);

    userId = _baseData.userId;
	loginName = _baseData.loginName;
	
	// 按键发送,默认按Enter发送
	sendKeyCode = $.cookie("sendKeyCode") || 13;
  })
}
function imInit(){
	// 获取聊天记录
	getChatRecord();
	// 获取订单列表
	getOrderList();
	// 获取浏览记录
	getHistory();
	// 右侧tab点击事件
	$('.im-item').click(function(){
		$('.im-item').removeClass('current');
		$(this).addClass('current');
		var _currTab = $(this).data('info');
		$('.im-tab-content').removeClass('im-show');
		$('.im-my-'+_currTab).addClass('im-show');
	});
	bindLeftLiClick();
	// 选中当前店铺并移到第一位
	var _shopId = $('#shopId').val();
	if(_shopId>0){
		var _obj = $('#shopId_'+_shopId);
		_obj.addClass('current');
		_obj.prependTo($('.u-lst'));
		setRead(_shopId,_obj);
	}
	if(goodsInfo!=undefined)showCurrVisit(goodsInfo);
	$.cookie('userChatPage', true,{path: '/'});
}
// 最近会话列表
function getChatList(){
	var url = APIS['userChatList'];
	$.get(url, function(data){
		data = WST.imToJson(data);
		var code = [];
		if(data.status==1 && data.data && data.data.length>0){
			for(var i=0;i<data.data.length;++i){
				var _chatListItem = data.data[i];
				code.push('<li title="'+_chatListItem.shopName+'" class="no-msg" data-sid="'+_chatListItem.shopId+'" id="shopId_'+_chatListItem.shopId+'" data-uname="'+_chatListItem.shopName+'">');
				code.push('<a href="javascript:;"><span class="icon icon_1"><img src="'+_chatListItem.shopImg+'" class="left_img"></span>');
				code.push('<div class="category"><span>'+_chatListItem.shopName+'</span>');
				var _display = 'none';
				if(_chatListItem.unReadNum>0){
					_display = 'block';
				}
				code.push('<em style="display:'+_display+'">'+_chatListItem.unReadNum+'</em>');
				code.push('</div><p class="tips"><span class="s02">'+_chatListItem.createTime+'</span>');
				code.push('<i class="i01"></i><span class="s01">'+_chatListItem.content.content+'</span></p></a></li>');
			}  
			$('.u-lst').html(code.join(''));
		}
		// 连接
		imInit();
	})
}



$(window).on('beforeunload', function(){
	$.cookie('userChatPage', false,{path: '/'});
});

// 设置当前浏览商品
var showCurrVisit = function(goodsInfo){
	$('.cv_l img').attr('src',IM_DOMAIN+goodsInfo.goodsImg);
	$('.cv_gname').html(goodsInfo.goodsName);
	$('.cv_price').html(WST.lang('currency_symbol')+goodsInfo.shopPrice);

	$('.cv_main').attr('href', entryGoods(goodsInfo.goodsId));
	$('.im-my-faq').css({height:563});
	$('.im-my-order').css({height:527});
	$('.curr_visit').css({height:120});
}
// 发送正在浏览
function sendCurrVisit(){
	$('.im-my-faq').css({height:683})
	$('.im-my-order').css({height:647})
	$('.curr_visit').css({height:0});
	var content = {type:"goods",goodsId:goodsInfo.goodsId,content:goodsInfo.goodsName};
		content = JSON.stringify(content);
		offline?_sendOfflineMsg(content):_sendMsg(content);
}

// 左侧点击事件
function bindLeftLiClick(){
	$('.u-lst li').click(function(){
		// 将图片数组置为空
		if(window.imImgArr){
			imImgArr = [];
		}
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
			var _currShopId = $(this).data('sid');
			$('#shopId').val(_currShopId);
			// 设置当前对话者名
			$('#shopName').val($(this).data('uname'));
			// 设置为已读【将未读消息数置为0,设置为已读】
			var unReadCount = $(this).find('em').html();
			if(unReadCount>0)setRead(_currShopId,$(this));
			$(this).find('em').html(0).hide();
			// 设置页数
			$('#currPage').val(1);
			$('#totalPage').val(1);
			$('#currOrderPage').val(0);
			$('#totalOrderPage').val(1);

			// 获取聊天记录渲染
			getChatRecord();
			// if(!onlineList[_currShopId])$('#logoTitle').parent().addClass('off');
			// 清空原有订单列表、浏览记录
			$('#o_list').html('');
			$('#h_list').html('');


			// 获取订单列表
			getOrderList();
			// 获取浏览记录
			getHistory();

			var sendData = {type:'login',
						uid:$('#userId').val(),
						userName:loginName,
						role:'user',
						shopId:$('#shopId').val()};
			ws.send(JSON.stringify(sendData));

		}
	});
}
// 标记为已读
function setRead(shopId,obj){
	var url = APIS['userSetRead'];
	$.post(url,{shopId:shopId},function(data){
		if(data.status==1){
			obj.find('em').html('0').css({display:'none'});
		}
	},'json')
}


// 检测左侧是否存在最近会话
var checkRecentExists = function(){
	var shopId = $('#shopId').val();
	var obj = $('#shopId_'+shopId);
	if(obj.length<=0){
		var code = [];
		var shopName = $('#shopName').val();
		var shopImg = $('#shopImg').val();
		code.push(
			'<li title="'+shopName+'" class="no-msg current" data-sid="'+shopId+'" id="shopId_'+shopId+'" data-uname="'+shopName+'">',
          	'<a href="javascript:;">',
            '<span class="icon icon_1">',
            '<img src="'+shopImg+'" class="left_img">',
            '</span><div class="category">',
            '<span>'+shopName+'</span>',
            '<em style="display:none">0</em>',
			'</div><p class="tips"><span class="s02"></span><i class="i01"></i><span class="s01"></span></p></a></li>'
		);
		$('.u-lst').append(code.join(''));
	}
}


// 连接服务器
function connectServer(){
	_first = false;
	ws = new WebSocket(APIS['imServer']);
	/**
	* 连接服务器
	*/
	ws.onopen = function(){
		var sendData = {type:'login',
						uid:$('#userId').val(),
						userName:loginName,
						platform:1,
						role:'user',
						shopId:$('#shopId').val(),
						group:$.cookie("group")};
		ws.send(JSON.stringify(sendData));
	};
	ws.onmessage = function(e){
	    var _data = JSON.parse(e.data);
	    if(!!_data.group){
	    	group = _data.group;
            $.cookie("group", group);
        }
	    switch(_data.type){
			case 'serverPushData':
				layer.open({
					type: 1
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
	        case 'chat':
	        	// 客服接待
	        	var _txt = WST.lang('wstim_tips6', [_data.groupName]);
				var code = '<div id="connect" class="chat-txt chat-time" ><p class="time">'+_txt+'</p></div>';
				workerName = code;
				offline = false
				setStatus(true);
				disableSendMsg();
				var _obj = $('#scrollDiv');
				_obj.append(code);
				_obj[0].scrollTop = _obj[0].scrollHeight;
				// 当前正在浏览的商品
				if(goodsInfo!=undefined){
					var content = JSON.stringify(goodsInfo);
					var sendData = {role:'user',content:content,type:'visit',to:group};
					ws.send(JSON.stringify(sendData));
				}
				checkRecentExists();

            break;
	    	case 'login':
	    		// 登录
	    	break;
	    	case 'say':
	    		if(_data.role=='user'){
		    		if(_data.from==userId){
			    		// 发送消息给某人
			    		_doSendMsg(_data);
		    		}else{
		    			// 接收某人消息
			    		// 1.判断当前是否与改用户聊天
			    		var _index = '#shopId_'+_data.shopId;
			    		if($(_index).hasClass('current')){
			    			// 设置消息已读
			    			setRead(_data.shopId,$(_index));
			    			// 直接渲染消息
			    			receiveMsg(_data);
			    		}
			    		// 设置未读消息数及内容摘要
			    		setMsgNumAndContent(_data,$(_index));
		    		}
	    		}
	    	break;
	    	case 'message':
	    		// 触发留言
	    		offline = true;
	    		disableSendMsg();
	    		noServiceOnline();
	    	break;
	    	case 'noonline':
	    		// 接收消息者已离线
	    	break;
	    	case 'wait':
	    		// 排队状态
	    		disableSendMsg(true);
	    	break;
	    	default:
	    	break;
	    }
	};

	ws.onclose = function(evt)
	{
	  console.log('WebSocketClosed!');
	  // 触发留言
	  offline = true;
	};
	ws.onerror = function(evt)
	{
	  console.log('WebSocketError!');
	  // 触发留言
	  offline = true;
	  disableSendMsg();
	  noServiceOnline();
	};
}
// 设置未读消息数及内容摘要
function setMsgNumAndContent(data,obj,type){
	// 判断当前是否已经存在该会话
	var _em = obj.find('em');
	// 获取已存在的未读消息数
	var _currUnReadNum = parseInt(_em.html());
		++_currUnReadNum;
	var _content = data.content;
	_em.html(_currUnReadNum);
	if(_currUnReadNum>0)_em.css({display: 'block'});
	obj.find('.s01').html(replaceContent(_content));
	obj.find('.s02').html(data.createTime);
	// 加入队列，修改为在线状态
	obj.removeClass('off');
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




// 标记客服在线状态
function setStatus(isOnline){
	var _obj = $('#logoTitle').parent();
	isOnline?_obj.removeClass('off'):_obj.addClass('off');
}
// 当前无客服在线的提示
function noServiceOnline(){
	checkRecentExists();
	var _txt = WST.lang('wstim_tips9');
	var code = '<div class="chat-txt chat-time" ><p class="time txt-red">'+_txt+'</p></div>';
	var _obj = $('#scrollDiv');
		_obj.append(code);
		_obj[0].scrollTop = _obj[0].scrollHeight;
}
// 是否关闭发送消息
function disableSendMsg(flag){
	if(flag){
		var code = [];
		code.push(
			'<div class="disable_edit"><div class="wait_txt">',
	            '<span>'+WST.lang('wstim_tips10')+'</span>',
	            '<a class="wait_btn" href="#">'+WST.lang('wstim_tips11')+'</a>',
	       	'</div></div>'
		);
		$('.im-edit-area').html(code.join(''));
		$('.wait_btn').click(function(){
			disableSendMsg();
			ws.close();
		});
	}else{
		var code = [];
		code.push(
			'<div class="im-edit-toolbar">',
	         '<p class="im-icon-out">',
	           '<a href="javascript:;" id="uploadBtn" class="pic" title="'+WST.lang('wstim_mapping')+'" style="position: relative; z-index: 1;overflow:hidden;"> ',
	            '<i class="im-pic"></i> ',
			   '</a> ',
			   '<a href="javascript:;" onclick="showEvalBox()" id="evalBtn" class="pic" title="'+WST.lang('wstim_score')+'" style="position: relative; z-index: 1;overflow:hidden;"> ',
	            '<i class="im-eval"></i></a> ',
	         '</p> ',
	        '</div>',
	        '<div class="im-edit-ipt-area"> ',
	         '<div id="text_in" class="im-edit-ipt" style="overflow-y: auto; font-weight: normal; font-size: 12px; overflow-x: hidden; word-break: break-all; font-style: normal; outline: none;" contenteditable="true" hidefocus="true" tabindex="0"></div> ',
	        '</div> ',
	        '<div class="im-edit-btn-area"> ',
	         '<div class="im-link-area"></div> ',
	         '<div class="im-btn-send-area"> ',

				'<a href="javascript:;" ',
					'onclick="sendMsg()"',
					'class="im-btn im-btn-send" ',
					'id="sendMsg"> ',
					'<span class="im-txt">'+WST.lang('wstim_send_out')+'</span> <span class="im-btn-l"></span> ',
				'</a> ',
				'<i id="switch_send" class="switch-send"></i>',

				`
				<ul id="enter_set" class="enter-set">
					<li id="enter_set_13" keycode="13" class="curr-set">`+WST.lang('wstim_send1')+`</li>
					<li id="enter_set_10" keycode="10" >`+WST.lang('wstim_send2')+`</li>
				</ul>
				`,

	         '</div> ',
	         '<div class="im-edit-tip" id="chat_count">',
	          WST.lang('wstim_tips12'),
	          '<span class="im-word-num">360</span>'+WST.lang('wstim_word')+'',
	         '</div> ',
	        '</div> '
		);
		$('.im-edit-area').html(code.join(''));
		// 启用上传图片
		uploadImg();
		// 截图发送
		pasteImg();
		// enter键监听
		pressEnter();
	}
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
				var uploadPhotoSrc = json.savePath+json.thumb;
				var content = {type:"image",content:uploadPhotoSrc};
					content = JSON.stringify(content);
				offline?_sendOfflineMsg(content):_sendMsg(content);
			}else{
				layer.msg(json.msg);
			}
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
	var receiveId = $('#shopId').val();
	var currPage = $('#currPage').val();
	var totalPage = $('#totalPage').val();
	if(parseInt(currPage)+1>totalPage){
		$(obj).find('.txt').html(lang('wstim_there_is_no_chat_record'));
		p.removeClass('loading');
		return;
	}
	var params = {
		receiveId:receiveId,
		page:parseInt(currPage)+1,
	};
	var url = APIS['userChatQuery'];
	$.post(url,params,function(data){
		var shopName = $('#shopName').val();
		var loginName = $('#loginName').val();
		$('#currPage').val(data.current_page);
		$('#totalPage').val(data.last_page);
		var rows = data.data;
		var code = [];
		for(var i in rows){
			var _obj = rows[i];
			dealContent(_obj);
			if(_obj.from!=userId){
				code.push(
					'<div class="chat-txt"><div class="chat-area service">',
		            '<p class="name">'+shopName+'</p>',
		            '<div class="dialog"><i class="i_arr"></i>',
		            '<table border="0" cellpadding="0" cellspacing="0"><tbody><tr>',
		            '<td class="lt"></td><td class="tt"></td><td class="rt"></td>',
		            '</tr><tr><td class="lm"></td><td class="mm"><div class="cont">',
		            '<div>'+_obj.content+'</div></div>',
		            '</td><td class="rm"></td></tr><tr><td class="lb"></td><td class="bm"></td><td class="rb"></td></tr></tbody></table></div></div></div>'
		                  );
			}else{
				code.push(
					'<div class="chat-txt"><div class="chat-area customer">',
					'<p class="name">'+loginName+'</p>',
					'<div class="dialog"><i class="i_arr"></i><span class="e_tips"></span><table border="0" cellpadding="0" cellspacing="0">',
					'<tbody><tr><td class="lt"></td><td class="tt"></td><td class="rt"></td></tr><tr><td class="lm"></td><td class="mm"><div class="cont">',
					'<div>'+ _obj.content+'</div>',
					'</div></td><td class="rm"></td></tr><tr><td class="lb"></td><td class="bm"></td><td class="rb"></td></tr></tbody></table></div></div></div>'
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


// 聊天记录
function getChatRecord(){
	var receiveId = $('#shopId').val();
	var shopName = $('#shopName').val();
	var loginName = $('#loginName').val();
	var params = {
		receiveId:receiveId
	};
	if(receiveId==0)return;
	var url = APIS['userChatQuery'];
	$.post(
		url,
		params,
		function(data){
			$('#currPage').val(data.current_page);
			$('#totalPage').val(data.last_page);
			var rows = data.data;
			var last = rows.length-1;
			for(var i in rows){
				var _obj = rows[i];
				if(_obj.from!=userId){
					receiveMsg(_obj);
				}else{
					_doSendMsg(_obj);
				}
			}
			if(i==last || rows.length==0){
				// 加载完最后一条聊天记录后
				if(_first)connectServer();
				var _obj = $('#scrollDiv');
				_obj[0].scrollTop = _obj[0].scrollHeight;
			}
		},
		'json');
}

// 点击发送消息
function sendMsg(){
	var content = $.trim($('#text_in').html());
	if(content=='')return;
	offline?_sendOfflineMsg(content):_sendMsg(content);
}
// 发送留言
function _sendOfflineMsg(content){
	var params = {content:content,type:'message',to:$('#shopId').val()};
	var url = APIS['userSendMsg'];
	$.post(url,params,function(data){
		if(data.status==1){
			_doSendMsg(params);
			// 触发自动回复
			if(data.data && data.data.length>0){
				data.data.map(function(content){
					setTimeout(function(){
						receiveMsg({content:content, userName: $('#shopName').val()});
					},200)
				})
			}
		}else{
			layer.msg(WST.lang('wstim_tips13'));
		}
	},'json');
	// 若存在推送
	if(APIS['notificationUrl']){
		$.post(APIS['notificationUrl'],params,function(data){});
	}
}
// 发送消息
function _sendMsg(content){
	var sendData = {role:'user',content:content,type:'say',to:group};
	ws.send(JSON.stringify(sendData));
}

function _doSendMsg(msg){
	dealContent(msg);
	var _obj = $('#scrollDiv');
	var loginName = $('#loginName').val();
	var code = [];
	code.push(
		'<div class="chat-txt">',
	          '<div class="chat-area customer">',
	            '<p class="name">'+loginName+'</p>',
	            '<div class="dialog"><i class="i_arr"></i><span class="e_tips"></span>',
	            '<table border="0" cellpadding="0" cellspacing="0"><tbody><tr><td class="lt">',
	            '</td><td class="tt"></td><td class="rt"></td></tr><tr><td class="lm"></td><td class="mm"><div class="cont">',
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
	var _obj = $('#scrollDiv');
	var code = [];
	code.push(
			'<div class="chat-txt"><div class="chat-area service">',
            '<p class="name">'+msg.userName+'</p>',
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
var olistFirst = true;
// 我的订单
function getOrderList(obj){
	var shopId = $('#shopId').val();
	if(shopId==0)return;


	var currPage = $('#currOrderPage').val();
	var totalPage = $('#totalOrderPage').val();
	if(parseInt(currPage)+1>totalPage){
		$(obj).find('.txt').html(WST.lang('wstim_tips14'));
		return;
	}
	var params = {
		shopId:shopId,
		page:parseInt(currPage)+1,
	}
	var code = [];
	var url = APIS['userOrderListQuery'];
	$.post(url,params,function(data){
		$('#currOrderPage').val(data.current_page);
		$('#totalOrderPage').val(data.last_page);
		var _rows = data.data;
		if(olistFirst && _rows.length==0){
			$('#o_list').html('<p class="tc"><i></i>'+WST.lang('wstim_no_data_available')+'</p>');
			$('.comonMore').remove();
			olistFirst = false;
			return;
		}
		for(var i in data.data){
			var _obj = data.data[i];
			code.push(
				'<li><p class="bg-1">',
			        '<strong>'+WST.lang('wstim_order_number')+'：</strong>',
			        '<span class="s-3">'+_obj.orderNo+'</span>',
			        '<span class="order-status">'+_obj.orderStatus+'</span>',
			        '<span class="btn" style="display: inline;">',
			          '<a href="javascript:;" class="btn-1 J_oreder_send" data-oid="'+_obj.orderId+'" data-ono="'+_obj.orderNo+'" >发送</a></span></p>',
			              '<div class="other">',
			                '<div class="sub">'
			        );
			// 判断订单下的商品数量，若只有一件则显示商品名称，否则仅显示商品图片
			if(_obj.list.length>1){
				var goodsCount = _obj.list.length;
				for(var j=0;j<goodsCount;++j){
					var _gObj = _obj.list[j];
					var _url = entryGoods(_gObj.goodsId);
					var _imgSrc = _gObj.goodsImg;
					code.push(
						'<div class="pic">',
			                '<a href="'+_url+'" target="_blank">',
			                  '<img data-original="'+_imgSrc+'" class="goodsImg" />',
			                '</a>',
			            '</div>'
					);
				}
			}else{
				var _gObj = _obj.list[0];
				var _url = entryGoods(_gObj.goodsId);
				var _imgSrc = _gObj.goodsImg;
				code.push(
						'<div class="pic">',
			                '<a href="'+_url+'" target="_blank">',
			                  '<img data-original="'+_imgSrc+'" class="goodsImg" />',
			                '</a></div>',
			            '<div class="txt"><p class="name">',
	                    '<a href="'+_url+'" target="_blank">'+_gObj.goodsName+'</a>',
	                    '</p></div>'
					);
			}
			code.push(
		          '</div><p>',
		            '<strong>'+WST.lang('wstim_order_amount')+'：</strong>',
		            '<span class="s-3">'+_obj.realTotalMoney+'</span>',
		            '<span class="time">'+_obj.createTime+'</span>',
		          '</p></div></li>'
			);
		}
		// 将代码添加到页面上
		$('#o_list').append(code.join(''));
		lazyLoadImg();
		// 绑定点击订单`发送`
		$('.J_oreder_send').click(function(){
			var orderId = $(this).data('oid');
			var orderNo = $(this).data('ono');
			var content = {type:"orders",orderId:orderId,content:WST.lang('wstim_order_number')+"："+orderNo};
			content = JSON.stringify(content);
			offline?_sendOfflineMsg(content):_sendMsg(content);
		})
	},'json');
}
// 浏览记录
function getHistory(){
	var params = {
		shopId:$('#shopId').val()
	}
	var code = [];
	var url = APIS['userHistoryQuery'];
	$.post(url,params,function(data){
		data = WST.imToJson(data);
		if(data.status==1 && data.data && data.data.length>0){
			data = data.data;
			for(var i in data){
				var _obj = data[i];
				var _url = entryGoods(_obj.goodsId);
				var _imgSrc = _obj.goodsImg;
				code.push(
					'<li><div class="other">',
				                '<div class="sub">'
				        );
				code.push(
						'<div class="pic">',
			                '<a href="'+_url+'" target="_blank">',
			                  '<img data-original="'+_imgSrc+'" class="goodsImg" />',
			                '</a></div><div class="txt"><p class="name">',
	                    '<a href="'+_url+'" target="_blank">'+_obj.goodsName+'</a></p></div>'
					);
				code.push(
			          '</div><p style="position:relative;">',
			          '<a href="javascript:;" class="btn-1 J_goods_send" data-gid="'+_obj.goodsId+'" data-gname="'+_obj.goodsName+'">发送</a>',
			          '<strong>'+WST.lang('wstim_commodity_amount')+'：</strong>',
			          '<span class="s-3">'+_obj.shopPrice+'</span>',
			          '<span class="time"></span></p></div></li>'
				);
			}
			// 将代码添加到页面上
			$('#h_list').html(code.join(''));
			// 绑定点击`发 送`
			$('.J_goods_send').click(function(){
				var goodsId = $(this).data('gid');
				var goodsName = $(this).data('gname');
				var content = {type:"goods",goodsId:goodsId,content:goodsName};
				content = JSON.stringify(content);
				offline?_sendOfflineMsg(content):_sendMsg(content);
			})
			lazyLoadImg();
		}
	},'json');
}
function lazyLoadImg(){
	$('.goodsImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 100});//商品默认图片
	$('.usersImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 100});//会员默认头像
}
// 图片上传
function uploadImg(){
	WST.imUpload({
    pick:'#uploadBtn',
    formData: {dir:'users'},
    accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
    callback:function(f){
      var json = WST.imToJson(f);
      if(json.status==1){
        var uploadPhotoSrc = json.savePath+json.thumb;
		var content = {type:"image",content:uploadPhotoSrc};
			content = JSON.stringify(content);
		offline?_sendOfflineMsg(content):_sendMsg(content);
      }else{
        layer.msg(json.msg);
      }
      },
      progress:function(rate){
          $('#uploadMsg').show().html(WST.lang('wstim_uploaded')+rate+"%");
      }
      });
}


// 打开评价页
function showEvalBox(){
    //自定页
    layer.open({
      title:false,
      type: 1,
      skin: 'layui-layer-demo', //样式类名
      closeBtn: 0, //不显示关闭按钮
      anim: 2,
      shadeClose: false, //开启遮罩关闭
      content: $('.evaluate-box')
    });
  }
  // 默认5分
  var _star_num = 5;
  var _star_text = { 1:WST.lang('wstim_evaluate1'), 2:WST.lang('wstim_evaluate2'), 3:WST.lang('wstim_evaluate3'), 4:WST.lang('wstim_evaluate4'), 5:WST.lang('wstim_very_satisfied') };
  $(function(){
    $('.eval-star').click(function(e){
      var _objs = $('.eval-star');
      var _obj = $(this);
      var index = _obj.index();
      _star_num = index+1;
      if(_obj.hasClass('chked')){
        _objs.map(function(_i,x){
          if(_i>index)$(x).removeClass('chked')
        })
      }else{
        // 选中
        _objs.map(function(_i,x){
          if(_i<=index)$(x).addClass('chked')
        })
      }
      $('#star-text').html(_star_text[_star_num]);
    })
    // 关闭遮罩层
    $('.eval-close').click(function(){
      layer.closeAll();
    });
  })
  // 提交评分
  function submitEval(){
    var _postData = {
      score:_star_num,
	  shopId:$('#shopId').val(),
	  serviceId:group
	};
	// 避免重复评价
	var _seKey = _postData.shopId+'_'+_postData.serviceId;
	if(_service_eval[_seKey])return layer.msg(WST.lang('wstim_evaluate5'));
	$.post(APIS['userEvalate'],_postData,function(res){
		var json = WST.imToJson(res);
		
		if(json.status==1){
			// 记录当前已评价
			_service_eval[_seKey] = true;
			layer.msg(json.msg,{time:1000},function(){
				// 重置表单
				resetEval();
				layer.closeAll();
			});
		}else{
			layer.msg(json.msg);
		}
	})
  }
  function resetEval(){
	_star_num = 5;
	$('#star-text').html(_star_text[_star_num]);
  }