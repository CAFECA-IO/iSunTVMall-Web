var mmg;
function initGrid(p){
   var h = WST.pageHeight();
   var cols = [
        {title:WST.lang('coupon_face_value2'), name:'couponValue', width: 100,renderer:function(val,item,rowIndex){
        	return WST.lang('currency_symbol')+item['couponValue'];
        }},
        {title:WST.lang('coupon_type'), name:'startPrice', width: 60,renderer:function(val,item,rowIndex){
        	return (item['useCondition']==1)?(WST.lang('coupon_full_reduction',[item['useMoney'],item['couponValue']])):WST.lang('coupon_cash_coupon');
        }},
        {title:WST.lang('coupon_suit_obj'), name:'floorPrice', width: 60,renderer:function(val,item,rowIndex){
        	return (item['useObjects']==0)?WST.lang('coupon_all_shop_can_use'):WST.lang('coupon_appoint_goods');
        }},
        {title:WST.lang('coupon_start_time'), name:'startDate', width: 100},
        {title:WST.lang('coupon_end_time'), name:'endDate', width: 100},
        {title:WST.lang('coupon_coupon_num'), name:'couponNum', width: 30},
        {title:WST.lang('coupon_quantity_received'), name:'receiveNum', width: 30},
	   {title:WST.lang('coupon_provide_type'), name:'couponType' ,width: 60,renderer:function(val,item,rowIndex){
			   var couponType='';
			   if(item['couponType']==1)couponType=WST.lang('coupon_provide_type_1');
			   if(item['couponType']==2)couponType=WST.lang('coupon_provide_type_2');
			   return couponType;
		   }},
        {title:WST.lang('coupon_operation'), name:'' ,width:250,renderer:function(val,item,rowIndex){
            var html = [];
				if(item['couponType']==1 && item['couponStatus']==true)html.push("<a class='btn btn-blue' onclick='javascript:toGiveCoupon("+item["couponId"]+")'><i class='fa fa-user'></i>"+WST.lang('coupon_grant')+"</a> ");
            if(item['receiveNum']>0)html.push("<a class='btn btn-blue' onclick='javascript:toStat("+item["couponId"]+")'><i class='fa fa-line-chart'></i>"+WST.lang('coupon_statistics')+"</a> ");
            html.push("<a class='btn btn-blue' onclick='javascript:toList("+item["couponId"]+")'><i class='fa fa-search'></i>"+WST.lang('coupon_check')+"</a> ");
            html.push("<a class='btn btn-blue' onclick='javascript:toEdit("+item["couponId"]+")'><i class='fa fa-pencil'></i>"+WST.lang('coupon_edit')+"</a>");
            html.push(" <a class='btn btn-red' onclick='javascript:del("+item["couponId"]+")'><i class='fa fa-trash-o'></i>"+WST.lang('coupon_del')+"</a>");
            return html.join('');
        }}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',nowrap:true,
        url: WST.AU('coupon://shops/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function loadGrid(p){
	var params=WST.getParams('#useCondition');
	p=(p<=1)?1:p;
	params.page=p;
    mmg.load(params);
}
function checkUseCondition(v){
    if(v==1){
    	$('#useMoney').attr('disabled',false);
    }else{
    	$('#useMoney').val(0);
    	$('#useMoney').attr('disabled',true);
    }
}
function toStat(id){
	location.href = WST.AU('coupon://shops/toStat','id='+id+'&p='+WST_CURR_PAGE);
}
function toList(id){
	location.href = WST.AU('coupon://shops/coupons','id='+id+'&p='+WST_CURR_PAGE);
}
function toEdit(id){
    location.href = WST.AU('coupon://shops/edit','id='+id+'&p='+WST_CURR_PAGE);
}
function toView(id){
	location.href = WST.AU('coupon://goods/detail','id='+id);
}

function toGiveCoupon(id){
	location.href = WST.AU('coupon://shops/toGiveCoupon','id='+id+'&p='+WST_CURR_PAGE);
}

function save(p){
    $('#couponform').isValid(function(v){
		if(v){
			var params = WST.getParams('.ipt');
			var loading = WST.load({msg:WST.lang('coupon_submitting')});
			$.post(WST.AU("coupon://shops/toEdit"),params,function(data,textStatus){
				layer.close(loading);
			    var json = WST.toJson(data);
			    if(json.status==1){
		            WST.msg(json.msg,{icon:1},function(){
		            	location.href = WST.AU('coupon://shops/index',"p="+p);
		            });
			    }else{
			    	WST.msg(json.msg,{icon:2});
			    }
			});
		}
	});
}
function del(id){
	var box = WST.confirm({content:WST.lang('coupon_confirm_del'),yes:function(){
		layer.close(box);
		var loading = WST.load({msg:WST.lang('coupon_submitting')});
		$.post(WST.AU("coupon://shops/del"),{id:id},function(data,textStatus){
			layer.close(loading);
		    var json = WST.toJson(data);
			if(json.status==1){
			    WST.msg(json.msg,{icon:1},function(){
			        loadGrid(WST_CURR_PAGE);
			    });
		    }else{
				WST.msg(json.msg,{icon:2});
			}
		});
	}});
}

function searchGoods(){
	var params = WST.getParams('.s-ipt');
    var loading = WST.load({msg:WST.lang('coupon_searching')});
	$.post(WST.AU("coupon://shops/searchGoods"),params,function(data,textStatus){
		layer.close(loading);
	    var json = WST.toJson(data);
	    $('#goodsSearchBox').empty();
	    if(json.status==1 && json.data){
	    	var gettpl = document.getElementById('tblist').innerHTML;
	       	laytpl(gettpl).render(json.data, function(html){
	       		$('#goodsSearchBox').html(html);
	       	});
	    }
	});
}

function moveRight(){
	if($('.lchk').size()<=0)return;
	var ids = $('#useObjectIds').val();
	if(ids.length>0){
		ids = ids.split(',');
	}else{
		ids = [];
	}
	$('.lchk').each(function(){
		if($(this)[0].checked){
	        $(this).attr('class','rchk');
	        $('#goodsResultBox').append($(this).parent().parent());
	        ids.push($(this).val());
	    }
	})
	$('#useObjectIds').val(ids.join(','));
}

function moveLeft(){
	if($('.rchk').size()<=0)return;
	var ids = $('#useObjectIds').val().split(',');
	$('.rchk').each(function(){
		if($(this)[0].checked){
	        $(this).attr('class','lchk');
	        $('#goodsSearchBox').append($(this).parent().parent());
	        for(var i=0;i<ids.length;i++){
	        	if(ids[i]==$(this).val())ids.splice(i, 1);
	        }
	    }
	})
    $('#useObjectIds').val(ids.join(','));
}

function changeSearchType(type){
	var userId = parseInt($('#userId').val());
	if(type==1){
		$('.order').show();
		$('.group').hide();
		if(userId>0){
			$('#orderSearchBox').show();
		}else{
			$('#orderSearchBox').hide();
		}
	}else{
		$('.order').hide();
		$('#orderSearchBox').hide();
		$('.group').show();
	}
}

function searchOrder(){
	var params = {};
	params.orderNo = $('#key').val();
	var loading = WST.load({msg:WST.lang('coupon_searching')});
	$.post(WST.AU("coupon://shops/searchOrder"),params,function(data,textStatus){
		layer.close(loading);
		var json = WST.toJson(data);
		if(json.status==1 && json.data){
			$('#orderSearchBox').show();
			$('#userId').val(json.data.userId);
			$('#orderNo').html(json.data.orderNo);
			$('#createTime').html(json.data.createTime);
			$('#userName').html(json.data.userName);
			$('#userPhone').html(json.data.userPhone);
			$('#userAddress').html(json.data.userAddress);
		}else{
			$('#orderSearchBox').hide();
			$('#userId').val('');
			$('#orderNo').html('');
			$('#createTime').html('');
			$('#userName').html('');
			$('#userPhone').html('');
			$('#userAddress').html('');
			WST.msg(json.msg,{icon:2});
		}
	});
}

function give(p){
	var searchType = 0;
	$("input[name^='searchType']").each(function(key,value){
		if($(value).prop('checked')){
			searchType = $(value).val();
		}
	});
	var params = {};
	params.couponId = $('#couponId').val();
	var ids = [];
	if(searchType==1){
		var userId = $('#userId').val();
		ids.push(userId);
	}else{
		var suffix = '_2';
		$('input[name="rchk'+suffix+'"]').each(function(){
			ids.push($(this).val());
		});
	}
	params.ids = ids.join(',');
	if(params.ids==''){
		WST.msg(WST.lang('coupon_select_send_user'), {icon: 2});
		return;
	}
	var box = WST.confirm({content:WST.lang('coupon_confrim_send_tips'),yes:function(){
		var loading = WST.msg(WST.lang('coupon_submitting'), {icon: 16,time:60000});
		$.post(WST.AU('coupon://shops/toGive'),params,function(data,textStatus){
			layer.close(loading);
			var json = WST.toJson(data);
			if(json.status=='1'){
				layer.close(box);
				WST.msg(json.msg,{icon:1},function(){
					location.href = WST.AU('coupon://shops/index',"p="+p);
				});
			}else{
				WST.msg(json.msg,{icon:2});
			}
		});
	}});
}

function changeMemberGroup(obj,suffix){
	var id = $(obj).val();
	var params = {};
	params.id = id;
	var loading = WST.msg(WST.lang('coupon_submitting'), {icon: 16,time:60000});
	$.post(WST.AU('coupon://shops/searchMemberGroupUsers'),params,function(data,textStatus){
		layer.close(loading);
		var json = WST.toJson(data);
		if(json.status=='1'){
			$("#llist"+suffix).empty();
			if(!json.data)return;
			json = json.data;
			var ids = $('#ids'+suffix).val().split(',');
			var data,html=[];
			for(var i=0;i<json.length;i++){
				data = json[i];
				if($.inArray(data.userId.toString(),ids)==-1){
					html.push('<div class="trow"><div class="tck"><input type="checkbox" name="lchk'+suffix+'" class="lchk'+suffix+'" value="'+data.userId+'"></div>');
					html.push('<div class="ttxt">'+data.loginName+' / '+data.userName+'</div></div>');
				}
			}
			$("#llist"+suffix).html(html.join(''));
		}else{
			WST.msg(json.msg,{icon:2});
		}
	});
}

function moveUserRight(suffix){
	$('input[name="lchk'+suffix+'"]:checked').each(function(){
		var html = [];
		html.push('<div class="trow"><div class="tck"><input type="checkbox" name="rchk'+suffix+'" class="rchk'+suffix+'" value="'+$(this).val()+'"></div>');
		html.push('<div class="ttxt">'+$(this).parent().parent().find('.ttxt').html()+'</div>');
		$(this).parent().parent().remove();
		$('#rlist'+suffix).append(html.join(''));
	});
	var ids = [];
	$('input[name="rchk'+suffix+'"]').each(function(){
		ids.push($(this).val());
	});
	$('#ids'+suffix).val(ids.join(','));
}
function moveUserLeft(suffix){
	$('input[name="rchk'+suffix+'"]:checked').each(function(){
		var html = [];
		html.push('<div class="trow"><div class="tck"><input type="checkbox" name="lchk'+suffix+'" class="lchk'+suffix+'" value="'+$(this).val()+'"></div>');
		html.push('<div class="ttxt">'+$(this).parent().parent().find('.ttxt').html()+'</div></div>');
		$(this).parent().parent().remove();
		$('#llist'+suffix).append(html.join(''));
	})
}
