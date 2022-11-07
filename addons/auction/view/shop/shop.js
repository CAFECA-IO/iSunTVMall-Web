var mmg;
function initGrid(p){
   var h = WST.pageHeight();
   var cols = [
        {title:WST.lang('auction_goods_image'), name:'goodsName', width: 30,renderer:function(val,item,rowIndex){
        	var html = [];
            html.push('<div class="goods-img"><a href="'+WST.AU("auction://goods/detail","id="+item["auctionId"])+'" target="_blank">');
            html.push("<span class='weixin'><img class='img' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'><img class='imged' style='height:200px;width:200px;max-width: 200px;max-height: 200px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></a></div>");
            return html.join('');
        }},
        {title:WST.lang('auction_goods_name'), name:'goodsName', width: 300,renderer:function(val,item,rowIndex){
            if(item['auctionStatus']==1){
	            return "<a style='color:blue' href='"+WST.AU("auction://goods/detail","id="+item["auctionId"])+"' target='_blank'>"+val+"</a>";
	        }else{
	        	return val;
	        }
        }},
        {title:WST.lang('auction_auction_price'), name:'auctionPrice', width: 60,renderer:function(val,item,rowIndex){
        	return WST.lang('currency_symbol')+item['auctionPrice'];
        }},
        {title:WST.lang('auction_start_time'), name:'startTime', width: 120},
        {title:WST.lang('auction_end_time'), name:'endTime', width: 120},
        {title:WST.lang('auction_curr_price'), name:'currPrice', width: 60,renderer:function(val,item,rowIndex){
        	return WST.lang('currency_symbol')+item['currPrice'];
        }},
        {title:WST.lang('auction_participant_num'), name:'isNew', width: 30,renderer:function(val,item,rowIndex){
            return "<a style='color:blue' href='"+WST.AU("auction://shops/bidding","id="+item["auctionId"])+"'>"+item['auctionNum']+"</a>";
        }},
        {title:WST.lang('auction_status2'), name:'attrSort', width: 70,renderer:function(val,item,rowIndex){
        	if(item['auctionStatus']==0){
		        return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('auction_wait_audit')+"</span>";
		    }else if(item['auctionStatus']==-1){
		        return "<span class='statu-no' title='"+item['illegalRemarks']+"'><i class='fa fa-ban'></i> "+WST.lang('auction_audit_fail')+"</span>";
		    }else{
		        if(item['status']==0){
		           return "<span class='lbel lbel-info'> "+WST.lang('auction_label_status_0')+"</span>";
		        }else if(item['status']==1){
		           return "<span class='lbel lbel-success'> "+WST.lang('auction_label_status_1')+"</span>";
		        }else{
		           return "<span class='lbel lbel-gray'> "+WST.lang('auction_label_status_5')+"</span>";
		        }
		    }
        }},
        {title:WST.lang('auction_operation'), name:'' ,width:200,renderer:function(val,item,rowIndex){
        	var html = [];
	        if(item['editable']==1){
	           html.push(" <a class='btn btn-blue' href='javascript:toEdit("+item["auctionId"]+")'><i class='fa fa-pencil'></i>"+WST.lang('auction_edit')+"</a>");
	        }
			if(item['isSale']>0){
				html.push(" <a class='btn btn-red' href='javascript:changeSale(" + item['auctionId'] + ",0)'><i class='fa fa-ban'></i>"+WST.lang('auction_unsale')+"</a>");
			}else{
				html.push(" <a class='btn btn-blue' href='javascript:changeSale(" + item['auctionId'] + ",1)'><i class='fa fa-check'></i>"+WST.lang('auction_onsale')+"</a>");
			}
	        if(item['auctionNum']>0){
	            if(item['orderId']>0){
	               html.push(" <a class='btn btn-blue' href='javascript:del("+item["auctionId"]+")'><i class='fa fa-trash-o'></i>"+WST.lang('auction_del')+"</a>");
	            }
	        }else{
	            html.push(" <a class='btn btn-blue' href='javascript:del("+item["auctionId"]+")'><i class='fa fa-trash-o'></i>"+WST.lang('auction_del')+"</a>");
	        }
	        return html.join("");
        }}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',
        url: WST.AU('auction://shops/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}

function loadGrid(p){
	p=(p<=1)?1:p;
    var params = {};
    params = WST.getParams('.s-ipt');
    params.key = $.trim($('#key').val());
    params.page=p;
    console.log(params);
    mmg.load(params);
}
var sgoods = [];
function searchGoods(){
	var params = {};
	params.shopCatId1 = $('#shopCatId1').val();
	params.shopCatId2 = $('#shopCatId2').val();
    params.goodsName = $('#sgoodsName').val();
    if(params.shopCatId1=='' && params.goodsName==''){
		 WST.msg(WST.lang('auction_require_goods_cat'),{icon:2});
		 return;
	}
	$('#goodsId').empty();
    var loading = WST.load({msg:WST.lang('auction_searching')});
	$.post(WST.AU("auction://shops/searchGoods"),params,function(data,textStatus){
		layer.close(loading);
	    var json = WST.toJson(data);
	    if(json.status==1 && json.data){
	    	var html = [];
	    	var option1 = null;
	    	sgoods = json.data;
	    	for(var i=0;i<json.data.length;i++){
	    		if(i==0)option1 = json.data[i];
                html.push('<option value="'+json.data[i].goodsId+'">'+json.data[i].goodsName+'</option>');
	    	}
	    	$('#goodsId').html(html.join(''));
			var n = 0;
			for(var i in WST.conf.sysLangs){
				n = WST.conf.sysLangs[i]['id'];
				$('#langParams'+n+'goodsName').val(option1.goodsName);
				$('#langParams'+n+'goodsSeoDesc').val(option1.goodsSeoDesc);
				$('#langParams'+n+'goodsSeoKeywords').val(option1.goodsSeoKeywords);
			}
	    	$('#marketPrice').html(WST.lang('currency_symbol')+option1.marketPrice);
			$('#goodsImg').val(option1.goodsImg);
			$('#preview').attr('src',WST.conf.RESOURCE_PATH+"/"+option1.goodsImg);
	    }
	});
}
function changeGoods(obj){
	var option1 = null
	for(var i=0;i<sgoods.length;i++){
		if(obj.value==sgoods[i].goodsId)option1 = sgoods[i];
	}
	var n = 0;
	for(var i in WST.conf.sysLangs){
		n = WST.conf.sysLangs[i]['id'];
		$('#langParams'+n+'goodsName').val(option1.goodsName);
		$('#langParams'+n+'goodsSeoDesc').val(option1.goodsSeoDesc);
		$('#langParams'+n+'goodsSeoKeywords').val(option1.goodsSeoKeywords);
	}
	$('#marketPrice').html(WST.lang('currency_symbol')+option1.marketPrice);
	$('#goodsImg').val(option1.goodsImg);
	$('#preview').attr('src',WST.conf.RESOURCE_PATH+"/"+option1.goodsImg);
}
function toEdit(id){
    location.href = WST.AU('auction://shops/edit','id='+id+'&p='+WST_CURR_PAGE);
}
function toView(id){
	location.href = WST.AU('auction://goods/detail','id='+id);
}

function save(p){
    $('#editform').isValid(function(v){
		if(v){
			var params = WST.getParams('.ipt');
			if(params.goodsId==''){
				WST.msg(WST.lang('auction_require_goods'),{icon:2});
				return;
			}
			var loading = WST.load({msg:WST.lang('auction_submitting')});
			$.post(WST.AU("auction://shops/toEdit"),params,function(data,textStatus){
				layer.close(loading);
			    var json = WST.toJson(data);
			    if(json.status==1){
		            WST.msg(json.msg,{icon:1},function(){
		            	location.href = WST.AU('auction://shops/auction','p='+p);
		            });
			    }else{
			    	WST.msg(json.msg,{icon:2});
			    }
			});
		}
	});
}
function del(id){
	var box = WST.confirm({content:WST.lang('auction_confirm_del'),yes:function(){
		layer.close(box);
		var loading = WST.load({msg:WST.lang('auction_submitting')});
		$.post(WST.AU("auction://shops/del"),{id:id},function(data,textStatus){
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

function initJoinGrid(id){
   var h = WST.pageHeight();
   var cols = [
        {title:WST.lang('auction_label_bidders'), name:'loginName', width: 300},
        {title:WST.lang('auction_bidding_price'), name:'payPrice', width: 100},
        {title:WST.lang('auction_bidding_time'), name:'createTime', width: 50},
        {title:WST.lang('auction_order_no'), name:'createTime', width: 50,renderer:function(val,item,rowIndex){
        	return "<a href='#none' style='color:blue' onclick='view("+item['orderId']+")'>"+item['orderNo']+"</a>";
        }},
        {title:'&nbsp;', name:'createTime', width: 50 ,renderer:function(val,item,rowIndex){
        	if(item['isTop']==1)return '<span class="lbel lbel-success">'+WST.lang('auction_highest_price')+'</span>';
        }}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',nowrap:true,
        url: WST.AU('auction://shops/pageAuctionLogQueryByShops','id='+id), fullWidthRows: true, autoLoad: true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
}

function view(id){
    location.href=WST.U('home/orders/view','id='+id);
}
var editor1;
function initForm(){
	var laydate = layui.laydate;
    laydate.render({
        elem: '#startTime',
        type: 'datetime'
    });
    laydate.render({
        elem: '#endTime',
        type: 'datetime'
    });
	KindEditor.ready(function(K) {
		for(var key in WST.conf.sysLangs){
			K.create('#langParams'+WST.conf.sysLangs[key].id+'auctionDesc', {
				height:'550px',
				width:'99.5%',
				uploadJson : WST.conf.ROOT+'/shop/goods/editorUpload',
				allowFileManager : false,
				allowImageUpload : true,
				themeType : "default",
				items:[     'source', 'undo', 'redo',  'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
					'plainpaste', 'wordpaste', 'justifyleft', 'justifycenter', 'justifyright',
					'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
					'superscript', 'clearhtml', 'quickformat', 'selectall',  'fullscreen',
					'formatblock', 'fontname', 'fontsize',  'forecolor', 'hilitecolor', 'bold',
					'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', 'image','multiimage','media','table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
					'anchor', 'link', 'unlink'
				],
				afterBlur: function(){ this.sync(); }
			});
		}
	});

	// KindEditor.ready(function(K) {
	// 	editor1 = K.create('textarea[name="auctionDesc"]', {
	// 		height:'550px',
	// 		width:'99.5%',
	// 		uploadJson : WST.conf.ROOT+'/home/goods/editorUpload',
	// 		allowFileManager : false,
	// 		allowImageUpload : true,
	// 		themeType : "default",
	//         items:[     'source', 'undo', 'redo',  'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
	//                 'plainpaste', 'wordpaste', 'justifyleft', 'justifycenter', 'justifyright',
	//                 'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
	//                 'superscript', 'clearhtml', 'quickformat', 'selectall',  'fullscreen',
	//                 'formatblock', 'fontname', 'fontsize',  'forecolor', 'hilitecolor', 'bold',
	//                 'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', 'image','multiimage','media','table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
	//                 'anchor', 'link', 'unlink'
	//         ],
	// 		afterBlur: function(){ this.sync(); }
	// 	});
	// });
	WST.upload({
		pick:'#goodsImgPicker',
		formData: {dir:'auction',isWatermark:1,isThumb:1},
		accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
		callback:function(f){
			var json = WST.toJson(f);
			if(json.status==1){
				$('#uploadMsg').empty().hide();
				$('#preview').attr('src',WST.conf.RESOURCE_PATH+"/"+json.savePath+json.thumb);
				$('#goodsImg').val(json.savePath+json.name);
				$('#msg_goodsImg').hide();
			}
		},
		progress:function(rate){
			$('#uploadMsg').show().html(WST.lang('auction_has_upload')+rate+"%");
		}
	});
}

function changeSale(id,type){
	$.post(WST.AU('auction://shops/changeSale'),{id:id,type:type},function(data){
		var json = WST.toJson(data);
		if(json.status>0){
			WST.msg(json.msg, {icon: 1});
			loadGrid(WST_CURR_PAGE);
		}else{
			WST.msg(json.msg, {icon: 2});
		}
	});
}
