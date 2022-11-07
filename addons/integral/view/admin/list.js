var mmg;

function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:'&nbsp;', name:'goodsImg', width: 50, renderer: function(val,item,rowIndex){
                var thumb = item['goodsImg'];
	        	thumb = thumb.replace('_thumb.','.');
                return "<span class='weixin'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']
            	+"'><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb+"'></span></span>";
            }},
            {title:WST.lang('integral_goods_name'), name:'goodsName', width: 150,renderer:function(val,item,rowIndex){
            	return "<div style='line-height:20px;'><a style='color:blue;' target='_blank' href='"+WST.AU("integral://goods/detail","id="+item['id']+"&key="+item['verfiycode'])+"'>"+val+"</a></div>";
            }},
            {title:WST.lang('integral_goods_product'), name:'goodsSn', width: 100},
            {title:WST.lang('integral_buy_price'), name:'goodsPrice', width: 120,renderer: function(val,item,rowIndex){
            	return WST.lang('currency_symbol')+item['goodsPrice']+' + '+item['integralNum']+WST.lang('integral_score');
            }},
            {title:WST.lang('integral_quantity'), name:'totalNum', width: 15},
            {title:WST.lang('integral_sale_num'), name:'orderNum', width: 15},
            {title:WST.lang('integral_start_time'), name:'orderNum', width: 190, align:'center',renderer: function(val,item,rowIndex){
            	return "<div style='line-height:20px;'>"+item['startTime']+ WST.lang('integral_to_title')+item['endTime']+"</div>";
            }},
            {title:WST.lang('integral_tips3'), name:'orderNum', width: 30, renderer: function(val,item,rowIndex){
            	if(item['integralStatus']==1){
		        	if(item['status']==1){
		        		
	                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('integral_tips4')+"</span>";
		        	}else if(item['status']==0){
	                    return "<span class='statu-wait'><i class='fa fa-ban'></i> "+WST.lang('integral_tips5')+"</span>";
		        	}else{
	                    return "<span class='statu-no'><i class='fa fa-ban'></i> "+WST.lang('integral_tips6')+"</span>";
		        	}
		        }else{
		        	 return "<span class='statu-no'><i class='fa fa-ban'></i> "+WST.lang('integral_tips7')+"</span>";
		        }
            }},
            {title:WST.lang('integral_operation'), name:'' ,width:150, align:'center', renderer: function(val,rowdata,rowIndex){
                var h = "";
	            if(WST.GRANT.INTEGRAL_TGHD_04)h += "<a class='btn btn-blue' href='javascript:toEdit(" + rowdata['id'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('integral_update')+"</a> ";
	            if(WST.GRANT.INTEGRAL_TGHD_04){
	            	if(rowdata['integralStatus']>0){
	            		h += "<a class='btn btn-red' href='javascript:changeSale(" + rowdata['id'] + ",0)'><i class='fa fa-ban'></i>"+WST.lang('integral_unsale')+"</a> ";
	            	}else{
	            		h += "<a class='btn btn-blue' class='btn btn-primary' href='javascript:changeSale(" + rowdata['id'] + ",1)'><i class='fa fa-check'></i>"+WST.lang('integral_onsale')+"</a> ";
	            	}
	            }
	            if(WST.GRANT.INTEGRAL_TGHD_03)h += "<a class='btn btn-red' href='javascript:del(" + rowdata['id'] + ",0)'><i class='fa fa-trash'></i>"+WST.lang('integral_del')+"</a>"; 
	            return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-87,indexCol: true, indexColWidth:50, cols: cols,method:'POST',
        url: WST.AU('integral://goods/pageQueryByAdmin'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function loadGrid(p){
	var params = {};
	params.shopName = $('#shopName1').val();
	params.goodsName = $('#goodsName1').val();
	params.areaIdPath = WST.ITGetAllAreaVals('areaId1','j-areas').join('_');
	params.goodsCatIdPath = WST.ITGetAllGoodsCatVals('cat1_0','pgoodsCats').join('_');
	p=(p<=1)?1:p;
	params.page=p;
	mmg.load(params);
}

function del(id,type){
	var box = WST.confirm({content:WST.lang('integral_confirm_del'),yes:function(){
	var loading = WST.msg(WST.lang('integral_submitting'), {icon: 16,time:60000});
		$.post(WST.AU('integral://goods/delByAdmin'),{id:id},function(data,textStatus){
			layer.close(loading);
			var json = WST.toAdminJson(data);
			if(json.status=='1'){
			    WST.msg(json.msg,{icon:1});
			    layer.close(box);
			    loadGrid(WST_CURR_PAGE);
			}else{
			    WST.msg(json.msg,{icon:2});
			}
		});
	}});
}


function changeSale(id,type){
	$.post(WST.AU('integral://goods/changeSale'),{id:id,type:type},function(data){
    	var json = WST.toAdminJson(data);
		if(json.status>0){
			WST.msg(json.msg, {icon: 1});
			loadGrid(WST_CURR_PAGE);
		}else{
			WST.msg(json.msg, {icon: 2});
		}
   });
}


function toEdit(id){
	location.href = WST.AU('integral://goods/toEdit',{id:id,p:WST_CURR_PAGE});
}
function toolTip(){
    WST.toolTip();
}
