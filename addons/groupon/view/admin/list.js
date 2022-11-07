var mmg1,mmg2,isInit1 = false,isInit2 = false;


function initGrid1(p){
    if(isInit1){
        loadGrid1(p);
        return;
    }
    isInit1 = true;
    var h = WST.pageHeight();
    var cols = [
            {title:'&nbsp;', name:'goodsImg', width: 50, renderer: function(val,item,rowIndex){
                var thumb = item['goodsImg'];
	        	thumb = thumb.replace('_thumb.','.');
                return "<span class='weixin'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']
            	+"'><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb+"'></span></span></span>";
            }},
            {title:WST.lang('group_goods_name'), name:'goodsName', width: 130,renderer:function(val,item,rowIndex){
            	return "<div style='line-height:20px;'><a style='color:blue' target='_blank' href='"+WST.AU("groupon://goods/detail","id="+item['grouponId']+"&key="+item['verfiycode'])+"'>"+val+"</a></div>";
            }},
            {title:WST.lang('group_price'), name:'grouponPrice', width: 20,renderer: function(val,item,rowIndex){return WST.lang('currency_symbol')+val}},
            {title:WST.lang('group_quantity'), name:'grouponNum', width: 30},
            {title:WST.lang('group_sale_num'), name:'orderNum', width: 20},
            {title:WST.lang('group_time2'), name:'startTime', width: 190, align:'center',renderer: function(val,item,rowIndex){
            	return "<div style='line-height:20px;'>"+item['startTime']+" "+WST.lang('group_to_title')+" "+item['endTime']+"</div>";
            }},
            {title:WST.lang('group_belong_shop'), name:'shopName', width: 100},
            {title:WST.lang('group_status'), name:'saleNum', width: 30, renderer: function(val,rowdata,rowIndex){
            	if(rowdata['status']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('group_status_1')+"</span>";
	        	}else if(rowdata['status']==0){
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('group_status_2')+"</span>";
	        	}else{
                    return "<span class='statu-no'><i class='fa fa-ban'></i> "+WST.lang('group_status_3')+"</span>";
	        	}
            }},
            {title:WST.lang('group_operation'), name:'' ,width:120, align:'center', renderer: function(val,rowdata,rowIndex){
                var h = "";
	            if(WST.GRANT.GROUPON_TGHD_04)h += "<a class='btn btn-red' href='javascript:illegal(" + rowdata['grouponId'] + ",1)'><i class='fa fa-ban'></i>"+WST.lang('group_illegal_unsale')+"</a> ";
	            if(WST.GRANT.GROUPON_TGHD_03)h += "<a class='btn btn-red' href='javascript:del(" + rowdata['grouponId'] + ",0)'><i class='fa fa-trash'></i>"+WST.lang('group_del')+"</a>"; 
	            return h;
	        }}
            ];
 
    mmg1 = $('.mmg1').mmGrid({height: h-126,indexCol: true,indexColWidth:50,  cols: cols,method:'POST',
        url: WST.AU('groupon://goods/pageQueryByAdmin'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg1').mmPaginator({})
        ]
    });
    loadGrid1(p);
}
function loadGrid1(p){
	var params = {};
	params.shopName = $('#shopName1').val();
	params.goodsName = $('#goodsName1').val();
	params.areaIdPath = WST.ITGetAllAreaVals('areaId1','j-areas').join('_');
	params.goodsCatIdPath = WST.ITGetAllGoodsCatVals('cat1_0','pgoodsCats').join('_');
	p=(p<=1)?1:p;
	params.page=p;
	mmg1.load(params);
}
function loadGrid2(p){
	var params = {};
	params.shopName = $('#shopName2').val();
	params.goodsName = $('#goodsName2').val();
	params.areaIdPath = WST.ITGetAllAreaVals('areaId2','j-areas').join('_');
	params.goodsCatIdPath = WST.ITGetAllGoodsCatVals('cat2_0','pgoodsCats').join('_');
    p=(p<=1)?1:p;
    params.page=p;
	mmg2.load(params);
}

function del(id,type){
	var box = WST.confirm({content:WST.lang('group_confirm_del'),yes:function(){
	           var loading = WST.msg(WST.lang('group_submitting'), {icon: 16,time:60000});
	           $.post(WST.AU('groupon://goods/delByAdmin'),{id:id},function(data,textStatus){
	           			layer.close(loading);
	           			var json = WST.toAdminJson(data);
	           			if(json.status=='1'){
	           			    WST.msg(json.msg,{icon:1});
	           			    layer.close(box);
	           			    if(type==0){
	           		            loadGrid1(WST_CURR_PAGE);
	           			    }else{
	           			    	loadGrid2(WST_CURR_PAGE);
	           			    }
	           			}else{
	           			    WST.msg(json.msg,{icon:2});
	           			}
	           		});
	            }});
}
function illegal(id,type){
	var w = WST.open({type: 1,title:((type==1)?WST.lang('group_unsale_reasons'):WST.lang('group_failure_reasons')),shade: [0.6, '#000'],border: [0],
	    content: '<textarea id="illegalRemarks" rows="7" style="width:100%" maxLength="200"></textarea>',
	    area: ['500px', '260px'],btn: [WST.lang('group_confirm'), WST.lang('group_close')],
        yes: function(index, layero){
        	var illegalRemarks = $.trim($('#illegalRemarks').val());
        	if(illegalRemarks==''){
        		WST.msg(WST.lang('group_require_illegal_reason'), {icon: 5});
        		return;
        	}
        	var ll = WST.msg(WST.lang('group_submitting'),{time:6000000});
		    $.post(WST.AU('groupon://goods/illegal'),{id:id,illegalRemarks:illegalRemarks},function(data){
		    	layer.close(w);
		    	layer.close(ll);
		    	var json = WST.toAdminJson(data);
				if(json.status>0){
					WST.msg(json.msg, {icon: 1});
					if(type==1){
                        loadGrid1(WST_CURR_PAGE);
					}else{
                        loadGrid2(WST_CURR_PAGE);
					}
				}else{
					WST.msg(json.msg, {icon: 2});
				}
		   });
        }
	});
}

function initGrid2(p){
    if(isInit2){
        loadGrid2(p);
        return;
    }
    isInit2 = true;
    var h = WST.pageHeight();
    var cols = [
            {title:'&nbsp;', name:'goodsImg', width: 50, renderer: function(val,item,rowIndex){
                var thumb = item['goodsImg'];
	        	thumb = thumb.replace('_thumb.','.');
                return "<span class='weixin'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']
            	+"'><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb+"'></span></span></span>";
            }},
            {title:WST.lang('group_goods_name'), name:'goodsName', width: 100,renderer:function(val,item,rowIndex){
            	return "<div style='line-height:20px;'><a style='color:blue' target='_blank' href='"+WST.AU("groupon://goods/detail","id="+item['grouponId']+"&key="+item['verfiycode'])+"'>"+val+"</a></div>";
            }},
            {title:WST.lang('group_price'), name:'grouponPrice', width: 20,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
            {title:WST.lang('group_quantity'), name:'saleNum', width: 20},
            {title:WST.lang('group_sale_num'), name:'orderNum', width: 20},
            {title:WST.lang('group_time2'), name:'startTime', width: 190, renderer: function(val,item,rowIndex){
            	return "<div style='line-height:20px;'>"+item['startTime']+" "+WST.lang('group_to_title')+" "+item['endTime']+"</div>";
            }},
            {title:WST.lang('group_belong_shop'), name:'shopName', width: 100},
            {title:WST.lang('group_status'), name:'grouponStatus', width: 30, renderer: function(val,rowdata,rowIndex){
            	if(rowdata['grouponStatus']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('group_audit_status_1')+"</span>";
	        	}else if(rowdata['grouponStatus']==0){
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('group_audit_status_2')+"</span>";
	        	}else{
                    return "<span class='statu-no'><i class='fa fa-ban'></i> "+WST.lang('group_audit_status_3')+"</span>";
	        	}
            }},
            {title:WST.lang('group_operation'), name:'' ,width:80, align:'center', renderer: function(val,rowdata,rowIndex){
                var h = "";
                h +='<div class="btn-group">';
                h +='  <button type="button" class="btn btn-blue dropdown-toggle wst-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                h +='  <i class="fa fa-pencil"></i>'+WST.lang('group_operation')+' <span class="caret"></span>';
                h +='  </button>';
                h +='  <ul class="dropdown-menu wst-dropdown-menu" style="min-width:60px">';
                if(WST.GRANT.GROUPON_TGHD_04){
                    h +='  <li><a href="javascript:allow('+rowdata["grouponId"]+')"><i class="fa fa-check"></i> '+WST.lang('group_audit_status_1')+'</a></li>';
                    h +='  <li><a href="javascript:illegal('+rowdata["grouponId"]+')"><i class="fa fa-ban"></i> '+WST.lang('group_audit_status_3')+'</a></li>';
                    h +='  <li role="separator" class="divider"></li>';
                }
                if(WST.GRANT.GROUPON_TGHD_03)h +='  <li><a href="javascript:del('+rowdata['grouponId']+',1)"><i class="fa fa-trash-o"></i> '+WST.lang('group_del')+'</a></li>';
                h +='  </ul>';
                h +='</div>';
                return h;
	        }}
            ];
 
    mmg2 = $('.mmg2').mmGrid({height: h-126,indexCol: true,indexColWidth:50,  cols: cols,method:'POST',
        url: WST.AU('groupon://goods/pageAuditQueryByAdmin'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg2').mmPaginator({})
        ]
    });
    loadGrid2(p);
}

function allow(id,type){
	var box = WST.confirm({content:WST.lang('group_confirm_audit_tips'),yes:function(){
        var loading = WST.msg(WST.lang('group_submitting'), {icon: 16,time:60000});
        $.post(WST.AU('groupon://goods/allow'),{id:id},function(data,textStatus){
        			layer.close(loading);
        			var json = WST.toAdminJson(data);
        			if(json.status=='1'){
        			    WST.msg(json.msg,{icon:1});
        			    layer.close(box);
        		        loadGrid1(WST_CURR_PAGE);
        		        loadGrid2(WST_CURR_PAGE);
        		    }else{
        			    WST.msg(json.msg,{icon:2});
        			}
        		});
         }});
}
function toolTip(){
    WST.toolTip();
}

