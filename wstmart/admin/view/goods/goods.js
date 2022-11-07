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
                return "<span><p class='wst-nowrap'><a style='color:blue' target='_blank' href='"+WST.U("home/goods/detail","goodsId="+item['goodsId'])+"'>"+item['goodsName']+"</a></p></span>";
            }},
            {title:WST.lang('label_goods_no'), name:'goodsSn' ,width:60,sortable:true},
            {title:WST.lang('label_goods_price'), name:'shopPrice' ,width:20,sortable:true, renderer: function(val,item,rowIndex){
            	return WST.lang('currency_symbol')+item['shopPrice'];
            }},
            {title:WST.lang('label_goods_shop'), name:'shopName' ,width:60,sortable:true},
            {title:WST.lang('label_goods_cat'), name:'goodsCatName' ,width:130,renderer: function(val,item,rowIndex){
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
        url: WST.U('admin/goods/saleByPage'), fullWidthRows: true, autoLoad: false,remoteSort: true,sortName:'goodsSn',sortStatus:'desc',
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
	var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           $.post(WST.U('admin/goods/del'),{id:id},function(data,textStatus){
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
	var w = WST.open({type: 1,title:((type==1)?WST.lang('label_goods_handle_statu0_txt'):WST.lang('label_goods_handle_statu1_txt')),shade: [0.6, '#000'],border: [0],
	    content: '<textarea id="illegalRemarks" rows="7" style="width:100%" maxLength="200"></textarea>',
	    area: ['500px', '260px'],btn: [WST.lang('submit'), WST.lang('close')],
        yes: function(index, layero){
        	var illegalRemarks = $.trim($('#illegalRemarks').val());
        	if(illegalRemarks==''){
        		WST.msg(((type==1)?WST.lang('require_goods_handle_statu0_txt'):WST.lang('require_goods_handle_statu1_txt')), {icon: 5});
        		return;
        	}
        	var ll = WST.msg(WST.lang('loading'));
		    $.post(WST.U('admin/goods/illegal'),{id:id,illegalRemarks:illegalRemarks},function(data){
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
                return "<span><p class='wst-nowrap'><a style='color:blue' target='_blank' href='"+WST.U("home/goods/detail","goodsId="+item['goodsId']+"&key="+item['verfiycode'])+"'>"+item['goodsName']+"</a></p></span>";
            }},
            {title:WST.lang('label_goods_no'), name:'goodsSn' ,width:40,sortable:true},
            {title:WST.lang('label_goods_price'), name:'shopPrice' ,width:20,sortable:true, renderer: function(val,item,rowIndex){
            	return WST.lang('currency_symbol')+item['shopPrice'];
            }},
            {title:WST.lang('label_goods_shop'), name:'shopName' ,width:60,sortable:true},
            {title:WST.lang('label_goods_cat'), name:'goodsCatName' ,width:90,renderer: function(val,item,rowIndex){
                return "<span  ><p class='wst-nowrap'>"+item['goodsCatName']+"</p></span>";
            }},
            {title:WST.lang('label_goods_sale_num'), name:'saleNum' ,width:20,sortable:true,align:'center'},
            {title:WST.lang('op'), name:'' ,width:160, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                h +='<div class="btn-group">';
                h +='<button type="button" class="btn btn-blue dropdown-toggle wst-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                h +='<i class="fa fa-pencil"></i>'+WST.lang('to_examine')+' <span class="caret"></span>';
                h +='</button>';
                h +='<ul class="dropdown-menu wst-dropdown-menu" style="min-width:60px">';
                if(WST.GRANT.DSHSP_04){
                    h +='  <li><a href="javascript:allow('+item["goodsId"]+')"><i class="fa fa-check"></i> '+WST.lang('result_1')+'</a></li>';
                    h +='  <li><a href="javascript:illegal('+item["goodsId"]+')"><i class="fa fa-ban"></i> '+WST.lang('result_0')+'</a></li>';
                }
                h +='</ul>';
                h +='</div>&nbsp;'; 
                if(WST.GRANT.DSHSP_03)h += "<a class='btn btn-red' href='javascript:del(" + item['goodsId'] + ",0)'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a>"; 
                return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: (h-90),indexCol: true, indexColWidth:40, cols: cols,method:'POST',checkCol:true,multiSelect:true,
        url: WST.U('admin/goods/auditByPage'), fullWidthRows: true, autoLoad: false,remoteSort: true,sortName:'goodsSn',sortStatus:'desc',
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
		 WST.msg(WST.lang('require_del'),{icon:2});
		 return;
	}
	var ids = [];
	for(var i=0;i<rows.length;i++){
       ids.push(rows[i]['goodsId']); 
	}

    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
   	$.post(WST.U('admin/goods/batchAllow'),{ids:ids.join(',')},function(data,textStatus){
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
	    area: ['500px', '260px'],btn: [WST.lang('submit'), WST.lang('close')],
        yes: function(index, layero){
        	var illegalRemarks = $.trim($('#illegalRemarks').val());
        	if(illegalRemarks==''){
        		WST.msg(WST.lang('require_goods_handle_statu1_txt'), {icon: 5});
        		return;
        	}
        	var ll = WST.msg(WST.lang('loading'));
		    $.post(WST.U('admin/goods/batchIllegal'),{ids:ids.join(','),illegalRemarks:illegalRemarks},function(data){
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
        $.post(WST.U('admin/goods/allow'),{id:id},function(data,textStatus){
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
                return "<span><p class='wst-nowrap'><a style='color:blue' target='_blank' href='"+WST.U("home/goods/detail","goodsId="+item['goodsId']+"&key="+item['verfiycode'])+"'>"+item['goodsName']+"</a></p></span>";
            }},
            {title:WST.lang('label_goods_no'), name:'goodsSn' ,width:60,sortable:true},
            {title:WST.lang('label_goods_shop'), name:'shopName' ,width:60,sortable:true},
            {title:WST.lang('label_goods_cat'), name:'goodsCatName' ,width:60,renderer: function(val,item,rowIndex){
                return "<span  ><p class='wst-nowrap'>"+item['goodsCatName']+"</p></span>";
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
        url: WST.U('admin/goods/illegalByPage'), fullWidthRows: true, autoLoad: false,remoteSort: true,sortName:'goodsSn',sortStatus:'desc',
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function toolTip(){
    WST.toolTip();
}


var goodsTotal,num=0,vtype=0,creatQrcodeCnt=0;
var goodsList = [];
var goodsDir = "";
var grqmap = [],errRqlist = [];
function toExplode(){

    var box = WST.open({title:WST.lang('goods_export_code'),type:1,content:layui.$('#exportBox'),area: ['400px', '180px'],btn:[WST.lang('goods_export_code_tips'),WST.lang('cancel')],yes:function(){
        vtype = $("#vtype").val();
        var params = WST.getParams('.j-ipt');
        grqmap = []
        errRqlist = [];
        params.areaIdPath = WST.ITGetAllAreaVals('areaId1','j-areas').join('_');
        params.goodsCatIdPath = WST.ITGetAllGoodsCatVals('cat_0','pgoodsCats').join('_');
        params.goodsCatIdPath = WST.ITGetAllGoodsCatVals('cat_0','pgoodsCats').join('_');
        var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
        $.post(WST.U('admin/goods/checkExportGoods'),params,function(data,textStatus){
                  layer.close(loading);
                  var json = WST.toAdminJson(data);
                  if(json.status==1){
                        goodsList = json.data.glist;
                        goodsDir = json.data.gdir;
                        goodsTotal = goodsList.length;
                        for(var i in goodsList){
                            grqmap[goodsList[i]['goodsId']] = goodsList[i];
                        }
                        layer.close(box);
                        if(goodsTotal>0){
                            createGoodsQrcode();
                        }else{
                            WST.msg(WST.lang('goods_export_no_data'),{icon:1});
                        }
                  }else{
                        WST.msg(json.msg,{icon:2});
                  }
            });
        }
    });
}

function createGoodsQrcode(){
    var goodsId = goodsList[num].goodsId;
    WST.msg(WST.lang('goods_export_rate',[(num+1),num,goodsTotal]));
    $.post(WST.U('admin/goods/createGoodsQrcode'),{vtype:vtype,goodsId:goodsId,dir:goodsDir},function(data,textStatus){
        var json = WST.toAdminJson(data);
          
        if(json.status!=1){
            errRqlist.push("<div style='line-height:30px;padding:0 20px;color:red;'>"+WST.lang('goods_export_rate_tips',[grqmap[goodsId]["goodsSn"],json.msg])+"</div>");
        }else{
            creatQrcodeCnt++
        }
        if(num < goodsTotal-1){
            num++
            layer.closeAll();
            createGoodsQrcode();
            return;
        }else{
            num=0;
            if(creatQrcodeCnt>0){
                WST.msg(WST.lang('goods_export_result',[creatQrcodeCnt]),{icon:1});
                packageDownQrcode();
            }else{
                if(errRqlist.length>0){
                    WST.open({title:WST.lang('tips'),
                    type:1,
                    content:errRqlist.join(""),
                    area: ['600px', '400px'],
                    btn:[WST.lang('submit')],
                    yes:function(){layer.closeAll();}})
                }
                WST.msg(WST.lang('goods_export_not_code'),{icon:1});
            }
        }
        
    });
}


function packageDownQrcode(){
    var loading = WST.msg(WST.lang('goods_export_code_loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/goods/packageDownQrcode'),{dir:goodsDir},function(data,textStatus){
        var json = WST.toAdminJson(data);
        if(json.status=='1'){
            layer.close(loading);
            if(errRqlist.length>0){
                WST.open({title:WST.lang('tips'),
                type:1,
                content:errRqlist.join(""),
                area: ['600px', '400px'],
                btn:[WST.lang('submit')],
                yes:function(){layer.closeAll();}})
            }
            
            window.location = window.conf.DOMAIN+"/"+json.data;
        }else{
            WST.msg(json.msg,{icon:2});
        }
    });
}