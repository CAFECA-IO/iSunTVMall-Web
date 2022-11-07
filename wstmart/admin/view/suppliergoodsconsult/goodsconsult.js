var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_goodsappraises_goods_img'), name:'goodsImg', width: 100, renderer: function(val,item,rowIndex){
            	var thumb = item['goodsImg'];
	        	thumb = thumb.replace('.','_thumb.');
	        	return "<span class='weixin'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb
            	+"'><span class='imged' style='left:45px;'><img  style='height:150px;width:150px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></span>";
            }},
            {title:WST.lang('label_order_goods_name'), name:'goodsName', width: 100,renderer: function(val,item,rowIndex){
                return "<span  ><p class='wst-nowrap'>"+item['goodsName']+"</p></span>";
            }},
            
            {title:WST.lang('label_goodsconsult_content'), name:'consultContent', width: 100,renderer: function(val,item,rowIndex){
                return "<span  ><p class='wst-nowrap'>"+item['consultContent']+"</p></span>";
            }},
            {title:WST.lang('label_goodsconsult_reply_txt'), name:'reply', width: 100,renderer: function(val,item,rowIndex){
                return "<span  ><p class='wst-nowrap'>"+item['reply']+"</p></span>";
            }},
            {title:WST.lang('status'), name:'isShow', width: 100, renderer: function(val,item,rowIndex){
            	return (val==0)?"<span class='statu-no'><i class='fa fa-ban'></i> "+WST.lang('is_show_val0')+"</span>":"<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('is_show_val1')+"</span></h3>";
            }},
            {title:WST.lang('op'), name:'' ,width:70, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
	            if(WST.GRANT.SPZX_02)h += "<a class='btn btn-blue' href='"+WST.U('admin/suppliergoodsconsult/toEdit','id='+item['id'])+'&p='+WST_CURR_PAGE+"'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
	            if(WST.GRANT.SPZX_03)h += "<a class='btn btn-red' href='javascript:toDel(" + item['id'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> "; 
	            return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-89,indexCol: true,indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/suppliergoodsconsult/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function toDel(id){
	var box = WST.confirm({content:WST.lang('are_you_sure_you_want_to_delete_it'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/suppliergoodsconsult/del'),{id:id},function(data,textStatus){
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
    $('#goodsconsultForm').validator({
            fields: {
                consultContent: {
                  rule:"required;length(3~200)",
                  msg:{length:WST.lang('require_goodsconsult_content1'),required:WST.lang('require_goodsconsult_content1')},
                  tip:WST.lang('require_goodsconsult_content1'),
                  ok:"",
                },
                reply:  {
                  rule:"required;length(3~200)",
                  msg:{length:WST.lang('require_goodsconsult_reply_txt'),required:WST.lang('require_goodsconsult_reply_txt')},
                  tip:WST.lang('require_goodsconsult_reply_txt'),
                  ok:""
                },
                
            },
          valid: function(form){
            var params = WST.getParams('.ipt');
            var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
            $.post(WST.U('admin/suppliergoodsconsult/edit'),params,function(data,textStatus){
              layer.close(loading);
              var json = WST.toAdminJson(data);
              if(json.status=='1'){
                  WST.msg(WST.lang('op_ok'),{icon:1});
                  location.href=WST.U('Admin/suppliergoodsconsult/index',"p="+p);
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