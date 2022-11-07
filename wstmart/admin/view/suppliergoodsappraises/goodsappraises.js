var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_goodsappraises_goods_img'), name:'goodsImg', width: 30, renderer: function(val,item,rowIndex){
            	var thumb = item['goodsImg'];
	        	thumb = thumb.replace('.','_thumb.');
	        	return "<span class='weixin'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb
            	+"'><span class='imged' style='left:45px;'><img  style='height:150px;width:150px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></span>";
            }},
            {title:WST.lang('label_goodsappraises_order_no'), name:'orderNo',sortable: true, width: 90},
            {title:WST.lang('label_order_goods_name'), name:'goodsName',sortable: true, width: 100,renderer: function(val,item,rowIndex){
                return "<span  ><p class='wst-nowrap'>"+item['goodsName']+"</p></span>";
            }},
            
            {title:WST.lang('product_rating'), name:'goodsScore',sortable: true, width: 80, renderer: function(val,item,rowIndex){
            	var s="<div style='line-height:28px;'>";
	        	for(var i=0;i<val;++i){
	        		s +="<img src='"+WST.conf.ROOT+"/wstmart/admin/view/suppliergoodsappraises/icon_score_yes.png'>";
	        	}
	        	s += "</div>";
	        	return s;
            }},
            {title:WST.lang('label_goodsappraises_score1'), name:'timeScore',sortable: true, width: 80, renderer: function(val,item,rowIndex){
            	var s="<div style='line-height:28px;'>";
	        	for(var i=0;i<val;++i){
	        		s +="<img src='"+WST.conf.ROOT+"/wstmart/admin/view/suppliergoodsappraises/icon_score_yes.png'>";
	        	}
	        	s += "</div>";
	        	return s;
            }},
            {title:WST.lang('label_goodsappraises_score2'), name:'serviceScore',sortable: true, width: 80, renderer: function(val,item,rowIndex){
            	var s="<div style='line-height:28px;'>";
	        	for(var i=0;i<val;++i){
	        		s +="<img src='"+WST.conf.ROOT+"/wstmart/admin/view/suppliergoodsappraises/icon_score_yes.png'>";
	        	}
	        	s += "</div>";
	        	return s;
            }},
            {title:WST.lang('label_goodsappraises_txt'), name:'content', width: 155},
            {title:WST.lang('status'), name:'isShow', width: 20,sortable: true, renderer: function(val,item,rowIndex){
            	return (val==0)?"<span class='statu-no'><i class='fa fa-ban'></i> "+WST.lang('is_show_val0')+"</span>":"<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('is_show_val1')+"</span></h3>";
            }},
            {title:WST.lang('op'), name:'' ,width:95, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
	            if(WST.GRANT.PJGL_02)h += "<a class='btn btn-blue' href='"+WST.U('admin/suppliergoodsappraises/toEdit','id='+item['id'])+'&p='+WST_CURR_PAGE+"'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
	            if(WST.GRANT.PJGL_03)h += "<a class='btn btn-red' href='javascript:toDel(" + item['id'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> "; 
	            return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-89,indexCol: true,indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/suppliergoodsappraises/pageQuery'), fullWidthRows: true, autoLoad: false,
        remoteSort:true ,
        sortName: 'orderNo',
        sortStatus: 'desc',
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });  
    loadGrid(p);
}
function toDel(id){
	var box = WST.confirm({content:WST.lang('are_you_sure_you_want_to_delete_it'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/suppliergoodsappraises/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(WST.lang('op_ok'),{icon:1});
	           			    	layer.close(box);
	           		            loadGrid(WST_CURR_PAGE);
	           			  }else{
	           			    	WST.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}
function loadGrid(p){
    p=(p<=1)?1:p;
	var query = WST.getParams('.query');
    query.page = p;
	mmg.load(query);
}

function editInit(p){
	

/* 表单验证 */
    $('#goodsAppraisesForm').validator({
            fields: {
                content: {
                  rule:"required;length(3~50)",
                  msg:{length:WST.lang('require_goodsappraises_txt'),required:WST.lang('require_goodsappraises_txt')},
                  tip:WST.lang('require_goodsappraises_txt'),
                  ok:"",
                },
                score:  {
                  rule:"required",
                  msg:{required:WST.lang('require_goodsappraises_score')},
                  ok:"",
                  target:"#score_error",
                },
                
            },

          valid: function(form){
            var params = WST.getParams('.ipt');
                //获取修改的评分
                params.goodsScore = $('.goodsScore').find('[name=score]').val();
                params.timeScore = $('.timeScore').find('[name=score]').val();
                params.serviceScore = $('.serviceScore').find('[name=score]').val();
            var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
            $.post(WST.U('admin/suppliergoodsappraises/'+((params.id==0)?"add":"edit")),params,function(data,textStatus){
              layer.close(loading);
              var json = WST.toAdminJson(data);
              if(json.status=='1'){
                  WST.msg(WST.lang('op_ok'),{icon:1});
                  location.href=WST.U('Admin/suppliergoodsappraises/index',"p="+p);
              }else{
                    WST.msg(json.msg,{icon:2});
              }
            });

      }

    });
}
function toolTip(){
    $('body').mousemove(function(e){
    	var windowH = $(window).height();  
        if(e.pageY >= windowH*0.8){
        	var top = windowH*0.233;
        	$('.imged').css('margin-top',-top);
        }else{
        	var top = windowH*0.06;
        	$('.imged').css('margin-top',-top);
        }
    });
}