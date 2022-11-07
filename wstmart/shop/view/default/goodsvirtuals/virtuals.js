function stockByPage(id,p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('card_number'), name:'cardNo', width: 150},
        {title:WST.lang('password'), name:'cardPwd', width: 150},
        {title:WST.lang('status'), name:'isUse', width: 30, renderer: function(val,item,rowIndex){
                var isUse="";
                if(item['isUse']==1)isUse="<span class='statu-yes'><i class='fa fa-check-circle'></i>"+WST.lang('order_placed')+"</span>";
                else isUse="<span class='statu-wait'><i class='fa fa-clock-o'></i>"+WST.lang('not_used')+"</span>";
                return isUse;
            }},
        {title:WST.lang('consumer_orders'), name:'orderNo', width: 150},
        {title:WST.lang('op'), name:'' ,width:80, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                if(item['isUse']==0)h += "<a class='btn btn-blue' href='javascript:editCard("+item['id']+")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                else h += "<a class='btn btn-red' href='javascript:delCard("+item['id']+",0)'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',
        url: WST.U('shop/goodsvirtuals/stockByPage',"id="+id), fullWidthRows: true, autoLoad: false,checkCol:true,multiSelect:true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p)
}
function loadGrid(p){
	p=(p<=1)?1:p;
    mmg.load({isUse:$('#isUse').val(),cardNo:$('#cardNo').val(),page:p});
}
function getUseStatus(v){
   switch(v){
   	  case 0:return WST.lang('not_used');
   	  case 1:return WST.lang('order_placed');
   }
}
function closeWin(){
	layer.close(w);
}
function addCardFunc(id,isContinue){
	var params =WST.getParams('.j-ipt');
	params.id = id;
	params.goodsId = $('#vid').val();
	if(params.cardNo=='' || params.cardPwd=='' || params.lastDate==''){
		WST.msg(WST.lang('please_input_the_complete_information_of_card_and_coupon'),{icon:2});
		return;
	}
	ll = WST.load({msg:WST.lang('loading')});
	$.post(WST.U('shop/goodsvirtuals/'+((params.id==0)?"add":"edit")),params,function(data){
		layer.close(ll);
		var json = WST.toJson(data);
		if(json.status==1){
			// stockByPage(WSTCurrPage);
			loadGrid(WST_CURR_PAGE);
			if(isContinue){
                $('#cardForm')[0].reset();
			}else{
				closeWin();
			}
			WST.msg(json.msg, {icon: 1});
		}else{
			WST.msg(json.msg, {icon: 2});
		}
	});
}
var ll,w;
function editCard(id,goodsId){
	ll = WST.load({msg:WST.lang('loading')});
	$.post(WST.U('shop/goodsvirtuals/'+((id==0)?'toAdd':'toEdit')),{id:id},function(data){
		layer.close(ll);
		w = WST.open({
			    type: 1,
			    title:WST.lang('new_card_and_coupon'),
			    shade: [0.6, '#000'],
			    border: [0],
			    content: data,
			    area: ['400px', '190px']
			});
	});
}

function delCard(id,v){
    var rows = mmg.selectedRows();
    if(rows.length==0){
        WST.msg(WST.lang('please_select_the_card_number_to_delete'),{icon:2});
        return;
    }
    var ids = [];
    for(var s=0;s<rows.length;s++){
        ids.push(rows[s]['id']);
    }
	var c = WST.confirm({content:WST.lang('are_you_sure_to_delete'),yes:function(){
		layer.close(c);
		var load = WST.load({msg:WST.lang('loading')});
		$.post(WST.U('shop/goodsvirtuals/del'),{ids:ids,id:$('#vid').val()},function(data,textStatus){
			layer.close(load);
		    var json = WST.toJson(data);
		    if(json.status==1){
		    	$('#all').prop('checked',false);
		    	// stockByPage(WSTCurrPage);
				loadGrid(WST_CURR_PAGE);
		    }else{
		    	WST.msg(json.msg,{icon:2});
		    }
		});
	}});
}

var uploading = null;
$(function(){
	var uploader = WST.upload({
        server:WST.U('shop/goodsvirtuals/importCards'),pick:'#importBtn',
    	formData: {dir:'temp',goodsId:$('#vid').val()},
    	callback:function(f,file){
    		layer.close(uploading);
    		uploader.removeFile(file);
    		var json = WST.toJson(f);
    		if(json.status==1){
    			uploader.refresh();
    		    WST.msg(WST.lang('import_ok', [json.importNum]), {icon: 1});
    		    loadGrid(WST_CURR_PAGE);
    		}else{
    			WST.msg(WST.lang('import_err', [json.msg]), {icon: 5});
    		}
	    },
	    progress:function(rate){
	    	uploading = WST.msg(WST.lang('loading'));
	    }
    });
});
