var ws,client_id,group,workerName,offline = false,userPhoto,shopImg,_service_eval={};


function _entryShop(){
	var _shopId = $('#shopId').val();
	entryShop(_shopId);
}

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
    $('#shopId').val(_baseData.shopId);
    $('#userId').val(_baseData.userId);
    $('#shopImg').attr('src', _baseData.shopImg);
    $('#userPhoto').attr('src', _baseData.userPhoto);
    
    $('#receiveId').val(_baseData.receiveId);
    $('#shopName').val(_baseData.shopName);
    $('#loginName').val(_baseData.loginName);
    $('#logoTitle').html(_baseData.shopName);

    userId = _baseData.userId;
    loginName = _baseData.loginName;

    imInit();

  })
}


function imInit(){
	$('#J_ImChatList').css({height:WST.pageHeight()-$('#J_Toolbar').height()-$('.J_WaiterBody').height()-30,overflow:'auto',backgroundColor: '#F5F5F5'});
    // 聊天记录
    getChatRecord();
    // 上传图片
    uploadImg();
    // 绑定点击事件
    $('#J_Order').click(function(){
    	$('.im-order').html(' ');
    	$('#currOrderPage').val(0);
    	$('#totalOrderPage').val(1);
    	dialogShow('order');
    });
	$('#J_Recent').click(function(){
		$('.im-order').html(' ');
		$('#currPage').val(1);
    	$('#totalPage').val(1);
		dialogShow('recent');
	});
	$('#J_Eval').click(function(){
		$('.im-order').html($('.evaluate-box').parent().html());
		dialogShow('eval');
		// 默认为非常满意
		$('.eval-star').each(function(k,item){$(item).addClass('chked')});
		_star_num = 5;
		$('#star-text').html(_star_text[_star_num]);
		bindStarClick();
	});
	$('.im-pop-close').click(function(){
		dialogShow('close');
	});
	$('#J_ImChatList').scroll(function(event){  
	    var wScrollY = $('#J_ImChatList').scrollTop(); // 当前滚动条位置    
	    if(wScrollY<=20)loadMoreHistory();
	});
	userPhoto = $('#userPhoto').attr('src');
	shopImg = $('#shopImg').attr('src');
}

// 设置当前浏览商品
var showCurrVisit = function(goodsInfo){
	var url = entryGoods(goodsInfo.goodsId);
	var img = IMAGE_DOMAIN+"/"+goodsInfo.goodsImg;
	var code = [];
	code.push('<li id="visit_goods" class="J_ChatItem chat-txt chat-info" data-type="skuCard"><div class="">');
    code.push('<span class="dialog-box" style="margin-left:0"><span class="J_MsgCont cont">');
    code.push('<ul class="im-list01 im-list04" style="padding-bottom: 0;"><li><div class="sub">');
    code.push('<a target="_blank" href="'+url+'">');
    code.push('<div class="pic">');
    code.push('<img src="'+img+'">');
    code.push('</div></a>');
	code.push('<div class="txt">');
    code.push('<a target="_blank" href="'+url+'">');
    code.push('<p class="name">'+goodsInfo.goodsName+'</p>');
	code.push('<p class="bg-2"><span style="padding-top: 4px;" class="price">'+WST.lang('currency_symbol')+goodsInfo.shopPrice+'</span><a href="javascript:;" class="J_Reelect i-reSel" onclick="sendCurrVisit()">'+WST.lang('wstim_send_link')+'</a></p>');
    code.push('</a></div></div></li></ul>');
    code.push('</span></span></div></li>');
    var _obj = $('#J_ImChatList');
	_obj.append(code.join(''));
	_obj.scrollTop(_obj[0].scrollHeight);
	document.getElementById("visit_goods").addEventListener("transitionend", hideCurrVisit);
}
// 发送正在浏览
function sendCurrVisit(){
	var content = {type:"goods",goodsId:goodsInfo.goodsId,content:goodsInfo.goodsName};
		content = JSON.stringify(content);
		offline?_sendOfflineMsg(content):_sendMsg(content);
	$('#visit_goods').css({opacity: 0,visibility:'hidden'});
}
// 移除正在浏览
function hideCurrVisit(){
	document.getElementById("visit_goods").removeEventListener("transitionend", hideCurrVisit);
	$('#visit_goods').remove();
}

// 插入会话时间
var insertTime = function(_time){
	if(_time==undefined)return;
	var code = '<div id="connect" class="chat-txt chat-time" ><p class="time">'+_time+'</p></div>';
	$('#J_ImChatList').append(code);
}

// 连接服务器
function connectServer(){
	ws = new WebSocket(APIS['imServer']);
	/**
	* 连接服务器
	*/
	ws.onopen = function(){
		var sendData = {type:'login',
						uid:$('#userId').val(),
						userName:loginName,
						role:'user',
						platform:2,
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
					WSTImNotify(_data.content);
			break;
	        case 'chat':
	        	// 客服接待
	        	var _txt = WST.lang('wstim_tips6', [_data.groupName]);
				var code = '<div id="connect" class="chat-txt chat-time" ><p class="time">'+_txt+'</p></div>';
				workerName = code;
				offline = false
				setStatus(true);
				disableSendMsg();
				var _obj = $('#J_ImChatList');
				_obj.append(code);
				scrollToEnd();
				// 当前正在浏览的商品
				if(goodsInfo!=undefined){
					var content = JSON.stringify(goodsInfo);
					var sendData = {role:'user',content:content,type:'visit',to:group};
					ws.send(JSON.stringify(sendData));
				}
            break;
	    	case 'say':
	    		if(_data.role=='user'){
		    		if(_data.from==userId){
			    		// 发送消息给某人
			    		_doSendMsg(_data);
		    		}else{
		    			// 接收某人消息
		    			receiveMsg(_data);
		    			setRead();
		    		}
	    		}
	    	break;
	    	case 'message':
	    		// 触发留言
	    		offline = true;
	    		// disableSendMsg();
	    		noServiceOnline();
	    	break;
	    	case 'wait':
	    		// 排队状态
	    		disableSendMsg(true);
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
	};
}
// 标记为已读
function setRead(){
	var url = APIS['userSetRead'];
	$.post(url,{shopId:$('#shopId').val()},function(data){
	},'json')
}
// 排队状态
function disableSendMsg(flag){
	if(flag){
		$('#Global_Loading').show();
		$('.wait_btn').click(function(){
			$('#Global_Loading').hide();
			ws.close();
		});
	}else{
		$('#Global_Loading').hide();
	}
}
// 标记客服在线状态
function setStatus(isOnline){
	var _obj = $('.J_WaiterHead');
	isOnline?_obj.removeClass('offline'):_obj.addClass('offline');
}
// 当前无客服在线的提示
function noServiceOnline(){
	var _txt = WST.lang('wstim_tips9');
	var code = [
		'<li class="J_ChatItem chat-txt chat-tips" data-type="system">',
	    '<div class="system"><span class="dialog-box">',
	    '<span class="J_MsgCont cont">'+_txt+'</span>',
	    '</span></div></li>'
	];
	var _obj = $('#J_ImChatList');
		_obj.append(code.join(''));
		scrollToEnd();
}
// 点击发送消息
function sendMsg(){
	var content = $.trim($('#J_TextIn').val());
	if(content=='')return;
	offline?_sendOfflineMsg(content):_sendMsg(content);
}
// 发送消息
function _sendMsg(content){
	var sendData = {role:'user',content:content,type:'say',to:group};
	ws.send(JSON.stringify(sendData));
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
						receiveMsg({content:content});
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
function _doSendMsg(msg){
	dealContent(msg);
	var _obj = $('#J_ImChatList');
	var code = [];
	code.push(
		'<li class="J_ChatItem chat-txt" data-rel="history">',
        '<div class="customer"><span class="dialog-box"><span class="J_MsgCont cont">',
        '<div class="J_TextMsgBox text-msg-wrap" style="height:auto;">'+msg.content+'</div>',
        '</span></span>',
        '<img src="'+userPhoto+'" class="userImg chat_photo">',
        '</div></li>'
		);
		
	$('#J_TextIn').val('');
	insertTime(msg.createTime);
	_obj.append(code.join(''));
	_obj.scrollTop(_obj[0].scrollHeight);
}
// 接收消息
function receiveMsg(msg){
	dealContent(msg);
	var _obj = $('#J_ImChatList');
	var code = [];
	code.push(
			'<li class="J_ChatItem chat-txt" data-rel="history">',
            '<div class="service">',
            '<img src="'+shopImg+'" class="shopImg chat_photo">',
            '<span class="dialog-box"><span class="J_MsgCont cont">',
            '<div class="J_TextMsgBox text-msg-wrap" style="height:auto;">'+msg.content+'</div>',
            '</span></span></div></li>'
			);
	insertTime(msg.createTime);
	_obj.append(code.join(''));
	_obj.scrollTop(_obj[0].scrollHeight);
}
// 聊天记录
function getChatRecord(){
	var receiveId = $('#shopId').val();
	var shopName = $('#shopName').val();
	var loginName = $('#loginName').val();
	var params = {
		receiveId:receiveId
	};
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
				connectServer();
				var _obj = $('#J_ImChatList');
				scrollToEnd();
			}
			if(goodsInfo!=undefined)showCurrVisit(goodsInfo);
		},
		'json');
}
// 令滚动条滑动到底部
function scrollToEnd(){
	var _obj = $('#J_ImChatList');
	_obj[0].scrollTop = _obj[0].scrollHeight;
}
//  显示遮罩层
function dialogShow(type){
	switch(type){
		case 'order':
			// 加载订单列表
			$('#Im_PopUp').show();
			$('.im-box-title').html(WST.lang('wstim_tip19'));
			getOrderList();
		break;
		case 'recent':
			// 加载浏览历史
			$('#Im_PopUp').show();
			$('.im-box-title').html(WST.lang('wstim_tip18'));
			getHistory();
		break;
		case 'eval':
			// 评分
			$('#Im_PopUp').show();
			$('.im-box-title').html(WST.lang('wstim_tips3'));
		break;
		case 'close':
		$('#Im_PopUp').hide();
		break;
	}
}

// 我的订单
function getOrderList(obj){
	var currPage = $('#currOrderPage').val();
	var totalPage = $('#totalOrderPage').val();
	if(parseInt(currPage)+1>totalPage){
		return;
	}
	var params = {
		shopId:$('#shopId').val(),
		page:parseInt(currPage)+1,
	}
	var code = [];
	var url = APIS['userOrderListQuery'];
	$.post(url,params,function(data){
		$('#currOrderPage').val(data.current_page);
		$('#totalOrderPage').val(data.last_page);
		var _rows = data.data;
		if(_rows && _rows.length==0){
			$('.im-order').html('<p class="tc">'+WST.lang('wstim_no_data_available')+'</p>');
			return;
		}
        code.push('<ul class="J_OrderListWrap im-list01 im-list03-pop">');
		for(var i in data.data){
			var _obj = data.data[i];
			code.push(
				'<li class="J_OrderItem" data-oid="'+_obj.orderId+'" data-ono="'+_obj.orderNo+'">',
	                '<p class="bg-1">',
	                    '<strong>'+WST.lang('wstim_order_number')+'：</strong>',
	                    '<span class="s-3">'+_obj.orderNo+'</span><span class="time">'+_obj.createTime+'</span>',
	                '</p>',
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
		                    '<img src="'+_imgSrc+'"  />',
		                '</div>'
					);
				}
			}else{
				var _gObj = _obj.list[0];
				var _url = entryGoods(_gObj.goodsId);
				var _imgSrc = _gObj.goodsImg;
				code.push(
						'<div class="pic">',
		                    '<img src="'+_imgSrc+'" />',
		                '</div>',
		                '<div class="txt">',
		                    '<p class="name">'+_gObj.goodsName+'</p>',
		                    '<p class="bg-2">',
		                        '<span class="price">'+WST.lang('currency_symbol')+_obj.realTotalMoney+'</span>',
		                        '<span class="state s-5 ">'+_obj.orderStatus+'</span>',
		                    '</p>',
		                '</div>'
					);
			}
			code.push('</div></li>');
		}
		code.push('</ul>');
		// 将代码添加到页面上
		$('.im-order').html(code.join(''));
		// 绑定点击事件
		$('.J_OrderItem').click(function(){
			var orderId = $(this).data('oid');
			var orderNo = $(this).data('ono');
			var content = {type:"orders",orderId:orderId,content:WST.lang('wstim_order_number')+"："+orderNo};
			content = JSON.stringify(content);
			offline?_sendOfflineMsg(content):_sendMsg(content);
			$('#Im_PopUp').hide();
		});
	},'json');
}
// 浏览记录
function getHistory(){
	var params = {
		shopId:$('#shopId').val(),
		wap:1,
	}
	var code = [];
	var url = APIS['userHistoryQuery'];
	$.post(url,params,function(data){
		if(data.data.length==0){
			$('.im-order').html('<p class="tc">'+WST.lang('wstim_no_data_available')+'</p>');
			return;
		}
		data = data.data;
		code.push('<ul class="J_ProductListWrap im-list01  im-list01-pop">');
		for(var i in data){
			var _obj = data[i];
			var _url = entryGoods(_obj.goodsId);
			var _imgSrc = _obj.goodsImg;
			code.push(
					'<li class="J_ProductItem" data-gid="'+_obj.goodsId+'" data-gname="'+_obj.goodsName+'">',
		                '<div class="sub"><div class="pic">',
	                    '<img src="'+_imgSrc+'" />',
	                    '</div>',
	                    '<div class="txt"><p class="name">'+_obj.goodsName+'</p>',
	                    '<p class="bg-2"><span class="price">'+WST.lang('currency_symbol')+_obj.shopPrice+'</span></p>',
	                    '</div></div></li>'
			        );
		}
		code.push('</ul>');
		// 将代码添加到页面上
		$('.im-order').html(code.join(''));
		// 绑定点击`发送`
		$('.J_ProductItem').click(function(){
			var goodsId = $(this).data('gid');
			var goodsName = $(this).data('gname');
			var receiveId = $('#receiveId').val();
			var content = {type:"goods",goodsId:goodsId,content:goodsName};
			content = JSON.stringify(content);
			offline?_sendOfflineMsg(content):_sendMsg(content);
			$('#Im_PopUp').hide();
		})
	},'json');
}
// 图片上传
function uploadImg(){
	WST.imUpload({
    pick:'#J_Photo',
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

loading=false;

// 加载更多聊天记录
function loadMoreHistory(){
	if(loading){
		// 加载中
		return;
	}
	loading = true;
	var receiveId = $('#shopId').val();
	var currPage = $('#currPage').val();
	var totalPage = $('#totalPage').val();
	if(parseInt(currPage)+1>totalPage){
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
					'<div id="connect" class="chat-txt chat-time" ><p class="time">'+_obj.createTime+'</p></div>',
					'<li class="J_ChatItem chat-txt" data-rel="history">',
			        '<div class="customer"><span class="dialog-box"><span class="J_MsgCont cont">',
			        '<div class="J_TextMsgBox text-msg-wrap" style="height:auto;">'+_obj.content+'</div>',
			        '</span></span>',
			        '<img src="'+userPhoto+'" class="chat_photo">',
			        '</div></li>'
					);
			}else{
				code.push(
					'<div id="connect" class="chat-txt chat-time" ><p class="time">'+_obj.createTime+'</p></div>',
					'<li class="J_ChatItem chat-txt" data-rel="history">',
		            '<div class="service">',
		            '<img src="'+shopImg+'" class="chat_photo">',
		            '<span class="dialog-box"><span class="J_MsgCont cont">',
		            '<div class="J_TextMsgBox text-msg-wrap" style="height:auto;">'+_obj.content+'</div>',
		            '</span></span></div></li>'
					);
			}
		}
		$('#J_ImChatList').prepend(code.join(''));
		loading = false;
	},'json');
}

// 评分-默认5分
var _star_num = 5;
var _star_text = { 1:WST.lang('wstim_evaluate1'), 2:WST.lang('wstim_evaluate2'), 3:WST.lang('wstim_evaluate3'), 4:WST.lang('wstim_evaluate4'), 5:WST.lang('wstim_very_satisfied') };
function bindStarClick(){
	$('.im-order .eval-star').click(function(e){
		var _objs = $('.im-order .eval-star');
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
}
// 提交评分
function submitEval(){
  var _postData = {
	score:_star_num,
	shopId:$('#shopId').val(),
	serviceId:group
  };
  // 避免重复评价
  var _seKey = _postData.shopId+'_'+_postData.serviceId;
  if(_service_eval[_seKey]){
	  $('#Im_PopUp').hide();
	return layer.msg(WST.lang('wstim_evaluate5'));
  }
  $.post(APIS['userEvalate'],_postData,function(res){
	  var json = WST.imToJson(res);
	  
	  if(json.status==1){
		  // 记录当前已评价
		  _service_eval[_seKey] = true;
		  layer.msg(json.msg,{time:1000},function(){
			$('#Im_PopUp').hide();
		  });
	  }else{
		  layer.msg(json.msg);
	  }
  })
}