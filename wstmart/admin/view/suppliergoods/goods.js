var mmg;
function initSaleGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:'&nbsp;', name:'goodsImg', width: 20, renderer: function(val,item,rowIndex){
            	var thumb = item['goodsImg'];
	        	thumb = thumb.replace('.','_thumb.');
            	return "<span class='weixin'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb
            	+"'><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></span>";
            }},
            {title:WST.lang('label_goods_name'), name:'goodsName', width: 120,sortable:true,renderer: function(val,item,rowIndex){
                return "<span  ><p class='wst-nowrap'><a class='btn btn-blue' target='_blank' href='"+WST.U("admin/suppliergoods/detail","goodsId="+item['goodsId'])+"'>"+item['goodsName']+"</a></p></span>";
            }},
            {title:WST.lang('goods_number'), name:'goodsSn' ,width:60,sortable:true},
            {title:WST.lang('label_goods_price'), name:'supplierPrice' ,width:20,sortable:true, renderer: function(val,item,rowIndex){
            	return WST.lang('currency_symbol')+item['supplierPrice'];
            }},
            {title:WST.lang('suppliergoods_supplier'), name:'supplierName' ,width:60,sortable:true},
            {title:WST.lang('label_goods_cat'), name:'goodsCatName' ,width:100,renderer: function(val,item,rowIndex){
                return "<span  ><p class='wst-nowrap'>"+item['goodsCatName']+"</p></span>";
            }},
            {title:WST.lang('label_goods_sale_num'), name:'saleNum' ,width:20,sortable:true,align:'center'},
            {title:WST.lang('op'), name:'' ,width:150, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
	            if(WST.GRANT.SJSP_04)h += "<a class='btn btn-red' href='javascript:illegal(" + item['goodsId'] + ",1)'><i class='fa fa-ban'></i>"+WST.lang('label_goods_handle_status0')+"</a> ";
	            if(WST.GRANT.SJSP_03)h += "<a class='btn btn-red' href='javascript:del(" + item['goodsId'] + ",1)'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> "; 
	            return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: (h-90),indexCol: true, indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/suppliergoods/saleByPage'), fullWidthRows: true, autoLoad: false,remoteSort: true,sortName:'goodsSn',sortStatus:'desc',
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function loadGrid(p){
	p=(p<=1)?1:p;
	var params = WST.getParams('.j-ipt');
	params.areaIdPath = WST.ITGetAllAreaVals('areaId1','j-areas').join('_');
	params.goodsCatIdPath = WST.ITGetAllGoodsCatVals('cat_0','pgoodsCats').join('_');
    params.page = p;
	mmg.load(params);
}

function del(id,type){
	var box = WST.confirm({content:WST.lang('are_you_sure_you_want_to_delete_it'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           $.post(WST.U('admin/suppliergoods/del'),{id:id},function(data,textStatus){
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
function illegal(id,type){
	var w = WST.open({type: 1,title:((type==1)?WST.lang('reasons_for_violation_of_commodity_regulations'):WST.lang('label_goods_handle_statu1_txt')),shade: [0.6, '#000'],border: [0],
	    content: '<textarea id="illegalRemarks" rows="7" style="width:100%" maxLength="200"></textarea>',
	    area: ['500px', '260px'],btn: [WST.lang('confirm'), WST.lang('close_window')],
        yes: function(index, layero){
        	var illegalRemarks = $.trim($('#illegalRemarks').val());
        	if(illegalRemarks==''){
        		WST.msg(((type==1)?WST.lang('require_goods_handle_statu0_txt'):WST.lang('require_goods_handle_statu1_txt')), {icon: 5});
        		return;
        	}
        	var ll = WST.msg(WST.lang('loading'));
		    $.post(WST.U('admin/suppliergoods/illegal'),{id:id,illegalRemarks:illegalRemarks},function(data){
		    	layer.close(w);
		    	layer.close(ll);
		    	var json = WST.toAdminJson(data);
				if(json.status>0){
					WST.msg(json.msg, {icon: 1});
					loadGrid(WST_CURR_PAGE);
				}else{
					WST.msg(json.msg, {icon: 2});
				}
		   });
        }
	});
}

function initAuditGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:'&nbsp;', name:'goodsName', width: 20, renderer: function(val,item,rowIndex){
            	return "<span class='weixin'><img class='img' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'><img class='imged' style='height:200px;width:200px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span>";
            }},
            {title:WST.lang('label_goods_name'), name:'goodsName', width: 200,sortable:true,renderer: function(val,item,rowIndex){
                return "<span  ><p class='wst-nowrap'><a class='btn btn-blue' target='_blank' href='"+WST.U("admin/suppliergoods/detail","goodsId="+item['goodsId']+"&key="+item['verfiycode'])+"'>"+item['goodsName']+"</a></p></span>";
            }},
            {title:WST.lang('goods_number'), name:'goodsSn' ,width:40,sortable:true},
            {title:WST.lang('label_goods_price'), name:'supplierPrice' ,width:20,sortable:true, renderer: function(val,item,rowIndex){
            	return WST.lang('currency_symbol')+item['supplierPrice'];
            }},
            {title:WST.lang('suppliergoods_supplier'), name:'supplierName' ,width:60,sortable:true},
            {title:WST.lang('label_goods_cat'), name:'goodsCatName' ,width:60,renderer: function(val,item,rowIndex){
                return "<span  ><p class='wst-nowrap'>"+item['goodsCatName']+"</p></span>";
            }},
            {title:WST.lang('label_goods_sale_num'), name:'saleNum' ,width:20,sortable:true,align:'center'},
            {title:WST.lang('op'), name:'' ,width:160, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
	            if(WST.GRANT.DSHSP_04)h += "<a class='btn btn-blue' href='javascript:allow(" + item['goodsId'] + ",0)'><i class='fa fa-check'></i>"+WST.lang('goods_handle_ok')+"</a> ";
	            if(WST.GRANT.DSHSP_04)h += "<a class='btn btn-red' href='javascript:illegal(" + item['goodsId'] + ",0)'><i class='fa fa-ban'></i>"+WST.lang('fail_to_pass_the_audit')+"</a> ";
	            if(WST.GRANT.DSHSP_03)h += "<a class='btn btn-red' href='javascript:del(" + item['goodsId'] + ",0)'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a>"; 
	            return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: (h-90),indexCol: true, indexColWidth:40, cols: cols,method:'POST',checkCol:true,multiSelect:true,
        url: WST.U('admin/suppliergoods/auditByPage'), fullWidthRows: true, autoLoad: false,remoteSort: true,sortName:'goodsSn',sortStatus:'desc',
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
// 批量审核通过
function toBatchAllow(){
	var rows = mmg.selectedRows();
	if(rows.length==0){
		 WST.msg(WST.lang('please_select_product'),{icon:2});
		 return;
	}
	var ids = [];
	for(var i=0;i<rows.length;i++){
       ids.push(rows[i]['goodsId']); 
	}

    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
   	$.post(WST.U('admin/suppliergoods/batchAllow'),{ids:ids.join(',')},function(data,textStatus){
   			  layer.close(loading);
   			  var json = WST.toAdminJson(data);
   			  if(json.status=='1'){
   			    	WST.msg(json.msg,{icon:1});
   		            loadGrid(WST_CURR_PAGE);
   			  }else{
   			    	WST.msg(json.msg,{icon:2});
   			  }
   		});
}
// 批量审核不通过
function toBatchIllegal(){
	var rows = mmg.selectedRows();
	if(rows.length==0){
		 WST.msg(WST.lang('please_select_product'),{icon:2});
		 return;
	}
	var ids = [];
	for(var i=0;i<rows.length;i++){
       ids.push(rows[i]['goodsId']); 
	}
	// 先显示弹出框,让用户输入审核不通原因
	var w = WST.open({type: 1,title:WST.lang('label_goods_handle_statu1_txt'),shade: [0.6, '#000'],border: [0],
	    content: '<textarea id="illegalRemarks" rows="7" style="width:100%" maxLength="200"></textarea>',
	    area: ['500px', '260px'],btn: [WST.lang('confirm'), WST.lang('close_window')],
        yes: function(index, layero){
        	var illegalRemarks = $.trim($('#illegalRemarks').val());
        	if(illegalRemarks==''){
        		WST.msg(WST.lang('require_goods_handle_statu1_txt'), {icon: 5});
        		return;
        	}
        	var ll = WST.msg(WST.lang('loading'));
		    $.post(WST.U('admin/suppliergoods/batchIllegal'),{ids:ids.join(','),illegalRemarks:illegalRemarks},function(data){
		    	layer.close(w);
		    	layer.close(ll);
		    	var json = WST.toAdminJson(data);
				if(json.status>0){
					WST.msg(json.msg, {icon: 1});
					loadGrid(WST_CURR_PAGE);
				}else{
					WST.msg(json.msg, {icon: 2});
				}
		   });
        }
	});
}

function allow(id,type){
	var box = WST.confirm({content:WST.lang('goods_handle_ok_tips'),yes:function(){
        var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
        $.post(WST.U('admin/suppliergoods/allow'),{id:id},function(data,textStatus){
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

function initIllegalGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:'&nbsp;', name:'goodsName', width: 20, renderer: function(val,item,rowIndex){
            	return "<span class='weixin'><img class='img' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'><img class='imged' style='height:200px;width:200px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span>";
            }},
            {title:WST.lang('label_goods_name'), name:'goodsName', width: 100,sortable:true,renderer: function(val,item,rowIndex){
                return "<span><p class='wst-nowrap'><a class='btn btn-blue' target='_blank' href='"+WST.U("admin/suppliergoods/detail","goodsId="+item['goodsId']+"&key="+item['verfiycode'])+"'>"+item['goodsName']+"</a></p></span>";
            }},
            {title:WST.lang('goods_number'), name:'goodsSn' ,width:60,sortable:true},
            {title:WST.lang('suppliergoods_supplier'), name:'supplierName' ,width:60,sortable:true},
            {title:WST.lang('label_goods_cat'), name:'goodsCatName' ,width:60,renderer: function(val,item,rowIndex){
                return "<span  ><p class='wst-nowrap'>"+item['goodsName']+"</p></span>";
            }},
            {title:WST.lang('label_goods_handle_statu0_txt'), name:'illegalRemarks' ,width:170,renderer: function(val,item,rowIndex){
                return '<div style="line-height:20px">'+val+'</div>';
            }},
            {title:WST.lang('op'), name:'' ,width:150, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
	            if(WST.GRANT.WGSP_04)h += "<a class='btn btn-blue' href='javascript:allow(" + item['goodsId'] + ",0)'><i class='fa fa-check'></i>"+WST.lang('goods_handle_ok')+"</a> ";
	            if(WST.GRANT.WGSP_03)h += "<a class='btn btn-red' href='javascript:del(" + item['goodsId'] + ",0)'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a></div> "; 
	            return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: (h-90),indexCol: true, indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/suppliergoods/illegalByPage'), fullWidthRows: true, autoLoad: false,remoteSort: true,sortName:'goodsSn',sortStatus:'desc',
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function toolTip(){
    WST.toolTip();
}
