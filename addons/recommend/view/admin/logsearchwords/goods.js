function toolTip(){
	WST.toolTip();
}

function moveRight(suffix){
	$('input[name="lchk'+suffix+'"]:checked').each(function(){
		var html = [];
		html.push('<div class="trow"><div class="tck"><input type="checkbox" name="rchk'+suffix+'" class="rchk'+suffix+'" value="'+$(this).val()+'"></div>');
		html.push('<div class="ttxt">'+$(this).parent().parent().find('.ttxt').html()+'</div>');
		html.push('<div class="top"><input type="text" class="s-sort s-ipt'+suffix+'" value="0" v="'+$(this).val()+'"></div></div>');
		$(this).parent().parent().remove();
		$('#rlist'+suffix).append(html.join(''));
	});
	var ids = [];
	$('input[name="rchk'+suffix+'"]').each(function(){
		ids.push($(this).val());
	});
	$('#ids'+suffix).val(ids.join(','));
}
function moveLeft(suffix){
	$('input[name="rchk'+suffix+'"]:checked').each(function(){
		var html = [];
		html.push('<div class="trow"><div class="tck"><input type="checkbox" name="lchk'+suffix+'" class="lchk'+suffix+'" value="'+$(this).val()+'"></div>');
		html.push('<div class="ttxt">'+$(this).parent().parent().find('.ttxt').html()+'</div></div>');
		$(this).parent().parent().remove();
		$('#llist'+suffix).append(html.join(''));
	})
}
/**商品**/
function loadGoods(suffix){
	var params = WST.getParams('.ipt'+suffix);
	params.key = params['key'+suffix];
	params.goodsCatId = WST.ITGetGoodsCatVal('pgoodsCats1'+suffix);
	if(params.goodsCatId==''){
		WST.msg(WST.lang('require_recommend_goods_cat'),{icon:2});
		return;
	}
	var loading = WST.msg(WST.lang('recommend_loading'), {icon: 16,time:60000});
	$.post(WST.AU('recommend://logsearchwords/searchGoods'),params,function(data,textStatus){
		layer.close(loading);
		var json = WST.toAdminJson(data);
		if(json.status=='1'){
			if(!json.data)return;
			json = json.data;
			$("#llist"+suffix).empty();
			var ids = $('#ids'+suffix).val().split(',');
			var data,html=[];
			for(var i=0;i<json.length;i++){
				data = json[i];
				if($.inArray(data.goodsId.toString(),ids)==-1){
					html.push('<div class="trow"><div class="tck"><input type="checkbox" name="lchk'+suffix+'" class="lchk'+suffix+'" value="'+data.goodsId+'"></div>');
					html.push('<div class="ttxt">【'+data.shopName+'】'+data.goodsName+'</div></div>');
				}
			}
			$("#llist"+suffix).html(html.join(''));
		}else{
			WST.msg(json.msg,{icon:2});
		}
	});
}
function listQueryByGoods(suffix){
	suffix = (typeof(suffix)=='object')?'_2':suffix;
	$('#rlist'+suffix).empty();
	$('#ids'+suffix).val('');
	var params = {};
	params.logId = $("#logId").val();
	var loading = WST.msg(WST.lang('recommend_loading'), {icon: 16,time:60000});
	$.post(WST.AU('recommend://logsearchwords/listQueryByGoods'),params,function(data,textStatus){
		layer.close(loading);
		var json = WST.toAdminJson(data);
		if(json.status=='1'){
			if(json.data && json.data.length){
				json = json.data;
				var data,html=[],ids = [];
				for(var i=0;i<json.length;i++){
					data = json[i];
					ids.push(data.goodsId);
					html.push('<div class="trow"><div class="tck"><input type="checkbox" name="rchk'+suffix+'" class="rchk'+suffix+'" value="'+data.goodsId+'"></div>');
					html.push('<div class="ttxt">【'+data.shopName+'】'+data.goodsName+'</div>');
					html.push('<div class="top"><input type="text" class="s-sort s-ipt'+suffix+'" value="'+data.sort+'" v="'+data.goodsId+'"></div></div>');
				}
				$('#ids'+suffix).val(ids.join(','));
				$("#rlist"+suffix).html(html.join(''));
			}
			if(WST.ITGetGoodsCatVal('pgoodsCats1'+suffix)>0)loadGoods(suffix);
		}
	});
}
function editGoods(suffix){
	var params = {},ids = [];
	$('input[name="rchk'+suffix+'"]').each(function(){
		ids.push($(this).val());
	});
	$('.s-ipt'+suffix).each(function(){
		params['ipt'+$(this).attr('v')] = $(this).val();
	});
	params.ids = ids.join(',');
	params.logId = $("#logId").val();
	var loading = WST.msg(WST.lang('recommend_loading'), {icon: 16,time:60000});
	$.post(WST.AU('recommend://logsearchwords/edit'),params,function(data,textStatus){
		layer.close(loading);
		var json = WST.toAdminJson(data);
		if(json.status==1){
			WST.msg(json.msg,{icon:1});
		}else{
			WST.msg(json.msg,{icon:2});
		}
	});
}