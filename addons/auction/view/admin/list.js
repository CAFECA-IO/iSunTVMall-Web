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
            	+"'><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb+"'></span></span>";
            }},
            {title:WST.lang('auction_goods_name'), name:'goodsName', width: 100,renderer:function(val,item,rowIndex){
                return "<div style='line-height:20px;'><a style='color:blue;' target='_blank' href='"+WST.AU("auction://goods/detail","id="+item['auctionId']+"&key="+item['verfiycode'])+"'>"+val+"</a></div>";
            }},
            {title:WST.lang('auction_belong_shop'), name:'shopName', width: 100},
            {title:WST.lang('auction_participant_num'), name:'auctionNum', width: 80,renderer: function(val,item,rowIndex){
                return "<a style='color:blue;text-decoration:underline' href=\"javascript:logs(" + item['auctionId'] + ")\">"+item['auctionNum']+"</a>";
            }},
            {title:WST.lang('auction_time'), name:'startTime', width: 120, align:'center',renderer: function(val,item,rowIndex){
            	return "<div style='line-height:20px;'>"+item['startTime']+"<br/>"+WST.lang('auction_to_title')+"<br/>"+item['endTime']+"</div>";
            }},
            {title:WST.lang('auction_caution_money'), name:'cautionMoney', width: 30,renderer: function(val,item,rowIndex){return WST.lang('currency_symbol')+val}},
            {title:WST.lang('auction_price'), name:'auctionPrice', width: 100,renderer: function(val,item,rowIndex){
                var h = '';
                h +='<div style="line-height:20px;">';
                h += WST.lang('auction_auction_price')+'：'+WST.lang('currency_symbol')+item['auctionPrice']+'<br/>'+WST.lang('auction_fareinc_price')+'：'+WST.lang('currency_symbol')+item['fareInc']+'<br/>'+WST.lang('auction_curr_price2')+'：'+WST.lang('currency_symbol')+item['currPrice'];
                h +='</div>';
                return h;
            }},
            {title:WST.lang('auction_status2'), name:'saleNum', width: 30,renderer: function(val,item,rowIndex){
            	if(item['status']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('auction_label_status_1')+"</span>";
	        	}else if(item['status']==0){
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('auction_label_status_0')+"</span>";
	        	}else{
                    return "<span class='statu-no'><i class='fa fa-ban'></i> "+WST.lang('auction_label_status_5')+"</span>";
	        	}
            }},
            {title:WST.lang('auction_order_status'), name:'saleNum', width: 30,renderer: function(val,item,rowIndex){
            	if(item['auctionNum']>0){
                    if(item['orderId']>0){
                        return "<span class='statu-yes' style='margin-top:15px;'><i class='fa fa-check-circle'></i> "+WST.lang('auction_has_order')+"</span>";
                    }else{
                        return "<span class='statu-wait' style='margin-top:15px;'><i class='fa fa-clock-o'></i> "+WST.lang('auction_not_order')+"</span>";
                    }
                }
            }},
            {title:WST.lang('auction_operation'), name:'' ,width:100, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
	            if(item['status']==1 && item['auctionNum']==0){
	                if(WST.GRANT.AUCTION_PMHD_04)h += "<a class='btn btn-red' href='javascript:illegal(" + item['auctionId'] + ",0)'><i class='fa fa-ban'></i>"+WST.lang('auction_unsale')+"</a> ";
	            }
	            if(WST.GRANT.AUCTION_PMHD_03){
	            	if(item['auctionNum']>0){
	            		if(item['orderId']>0)h += "<a class='btn btn-red' href='javascript:del(" + item['auctionId'] + ",0)'><i class='fa fa-trash'></i>"+WST.lang('auction_del')+"</a></div> "; 
	            	}else{
                        h += "<a class='btn btn-red' href='javascript:del(" + item['auctionId'] + ",0)'><i class='fa fa-trash'></i>"+WST.lang('auction_del')+"</a> "; 
	            	}
	            }
	            return h;
	        }}
            ];
 
    mmg1 = $('.mmg1').mmGrid({height: h-125,indexCol: true, indexColWidth:50, cols: cols,method:'POST',
        url: WST.AU('auction://goods/pageQueryByAdmin'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg1').mmPaginator({})
        ]
    });
    loadGrid1(p)
}
function loadGrid1(p){
	p=(p<=1)?1:p;
	var params = {};
	params.page=p;
	params.shopName = $('#shopName1').val();
	params.goodsName = $('#goodsName1').val();
	params.areaIdPath = WST.ITGetAllAreaVals('areaId1','j-areas').join('_');
	params.goodsCatIdPath = WST.ITGetAllGoodsCatVals('cat1_0','pgoodsCats').join('_');
	mmg1.load(params);
}
function initGrid2(p){
    if(isInit2){
        loadGrid2(p);
        return;
    }
    isInit2 = true;
    var h = WST.pageHeight();
    var cols = [
        {title:'&nbsp;', name:'bankName', width: 50, renderer: function(val,item,rowIndex){
                var thumb = item['goodsImg'];
                thumb = thumb.replace('_thumb.','.');
                return "<span class='weixin'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']
                    +"'><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb+"'></span></span>";
            }},
        {title:WST.lang('auction_goods_name'), name:'goodsName', width: 100,renderer:function(val,item,rowIndex){
                return "<div style='line-height:20px;'><a style='color:blue;' target='_blank' href='"+WST.AU("auction://goods/detail","id="+item['auctionId']+"&key="+item['verfiycode'])+"'>"+val+"</a></div>";
        }},
        {title:WST.lang('auction_belong_shop'), name:'shopName', width: 100},
        {title:WST.lang('auction_participant_num'), name:'auctionNum', width: 80},
        {title:WST.lang('auction_time'), name:'startTime', width: 190, align:'center',renderer: function(val,item,rowIndex){
                return "<div style='line-height:20px;'>"+item['startTime']+"<br/>"+WST.lang('auction_to_title')+"<br/>"+item['endTime']+"</div>";
            }},
        {title:WST.lang('auction_caution_money'), name:'cautionMoney', width: 30,renderer: function(val,item,rowIndex){return WST.lang('currency_symbol')+val}},
        {title:WST.lang('auction_price'), name:'auctionPrice', width: 100,renderer: function(val,item,rowIndex){
                var h = '';
                h +='<div style="line-height:20px;">';
                h += WST.lang('auction_auction_price')+'：'+WST.lang('currency_symbol')+item['auctionPrice']+'<br/>'+WST.lang('auction_fareinc_price')+'：'+WST.lang('currency_symbol')+item['fareInc']+'<br/>'+WST.lang('auction_curr_price2')+'：'+WST.lang('currency_symbol')+item['currPrice'];
                h +='</div>';
                return h;
        }},
        {title:WST.lang('auction_status2'), name:'saleNum', width: 30,renderer: function(val,item,rowIndex){
                if(item['auctionStatus']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('auction_audit_pass')+"</span>";
                }else if(item['auctionStatus']==0){
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('auction_wait_audit')+"</span>";
                }else{
                    return "<span class='statu-no'><i class='fa fa-ban'></i> "+WST.lang('auction_audit_not_pass')+"</span>";
                }
            }},
        {title:WST.lang('auction_operation'), name:'' ,width:100, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                h +='<div class="btn-group">';
                h +='  <button type="button" class="btn btn-blue dropdown-toggle wst-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                h +='  <i class="fa fa-pencil"></i>'+WST.lang('auction_operation')+' <span class="caret"></span>';
                h +='  </button>';
                h +='  <ul class="dropdown-menu wst-dropdown-menu" style="min-width:60px">';
                if(WST.GRANT.AUCTION_PMHD_04){
                    h +='  <li><a href="javascript:allow('+item["auctionId"]+')"><i class="fa fa-check"></i> '+WST.lang('auction_audit_pass')+'</a></li>';
                    h +='  <li><a href="javascript:illegal('+item["auctionId"]+')"><i class="fa fa-ban"></i> '+WST.lang('auction_audit_not_pass')+'</a></li>';
                    h +='  <li role="separator" class="divider"></li>';
                }
                if(WST.GRANT.AUCTION_PMHD_03)h +='  <li><a href="javascript:del('+item['auctionId']+',1)"><i class="fa fa-trash-o"></i> '+WST.lang('auction_del')+'</a></li>';
                h +='  </ul>';
                h +='</div>';
                return h;
            }}
    ];

    mmg2 = $('.mmg2').mmGrid({height: h-125,indexCol: true, indexColWidth:50, cols: cols,method:'POST',
        url: WST.AU('auction://goods/pageAuditQueryByAdmin'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg2').mmPaginator({})
        ]
    });
    loadGrid2(p);
}
function loadGrid2(p){
    p=(p<=1)?1:p;
    var params = {};
    params.page=p;
	params.shopName = $('#shopName2').val();
	params.goodsName = $('#goodsName2').val();
	params.areaIdPath = WST.ITGetAllAreaVals('areaId2','j-areas').join('_');
	params.goodsCatIdPath = WST.ITGetAllGoodsCatVals('cat2_0','pgoodsCats').join('_');
	mmg2.load(params);
}

function del(id,type){
	var box = WST.confirm({content:WST.lang('auction_confirm_del_tips'),yes:function(){
	           var loading = WST.msg(WST.lang('auction_submitting'), {icon: 16,time:60000});
	           $.post(WST.AU('auction://goods/delByAdmin'),{id:id},function(data,textStatus){
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
	var w = WST.open({type: 1,title:((type==1)?WST.lang('auction_unsale_reasons'):WST.lang('auction_failure_reasons')),shade: [0.6, '#000'],border: [0],
	    content: '<textarea id="illegalRemarks" rows="7" style="width:100%" maxLength="200"></textarea>',
	    area: ['500px', '260px'],btn: [WST.lang('auction_confirm'), WST.lang('auction_close')],
        yes: function(index, layero){
        	var illegalRemarks = $.trim($('#illegalRemarks').val());
        	if(illegalRemarks==''){
        		WST.msg(WST.lang('auction_require_illegal_reason'), {icon: 5});
        		return;
        	}
        	var ll = WST.msg(WST.lang('auction_submitting'),{time:6000000});
		    $.post(WST.AU('auction://goods/illegal'),{id:id,illegalRemarks:illegalRemarks},function(data){
		    	layer.close(w);
		    	layer.close(ll);
		    	var json = WST.toAdminJson(data);
				if(json.status>0){
					WST.msg(json.msg, {icon: 1});
					if(type==0){
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



function allow(id,type){
	var box = WST.confirm({content:WST.lang('auction_confirm_audit_pass_tips'),yes:function(){
        var loading = WST.msg(WST.lang('auction_submitting'), {icon: 16,time:60000});
        $.post(WST.AU('auction://goods/allow'),{id:id},function(data,textStatus){
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

function logs(id){
	parent.showBox({type:2,title:WST.lang('auction_record'),area: ['800px', '450px'],content:WST.AU('auction://goods/auctionLogByAdmin','id='+id+"&rd="+Math.random())});
}
function toolTip(){
    WST.toolTip();
}
