var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:'&nbsp;', name:'goodsImg', width: 30, renderer: function(val,item,rowIndex){
            	var thumb = item['goodsImg'];
	        	thumb = thumb.replace('.','_thumb.');
            	return "<span class='weixin'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb
            	+"'><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></span>";
            }},
            {title:WST.lang('label_informs_goods'),sortable: true, name:'goodsName',renderer: function(val,item,rowIndex){
                return "<a style='color:blue' target='_blank' href='"+WST.U("home/goods/detail","goodsId="+item['goodsId'])+"'><span><p class='wst-nowrap'>"+item['goodsName']+"</p></span></a>";
            }},
            {title:WST.lang('label_informs_shop'),sortable: true, name:'shopName'},
            {title:WST.lang('label_informs_user_name'), name:'userName', width: 30,sortable: true, renderer: function(val,item,rowIndex){
            	return WST.blank(item['userName'],item['loginName']);
            }},
            {title:WST.lang('label_informs_type'),sortable: true, name:'informType'},
            {title:WST.lang('label_informs_time'),sortable: true, name:'informTime'},
            {title:WST.lang('label_informs_status'), name:'informStatus', renderer: function(val,item,rowIndex){
	        	if(val==0)
	        		return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('label_informs_result1')+"</span>";
	        	else if(val==1)
	        		return "<span class='statu-no'><i class='fa fa-ban'></i> "+WST.lang('label_informs_result2')+"</span>";
	        	else if(val==2)
	        		return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('label_informs_result3')+"</span>";
	        	else if(val==3)
	        		return "<span class='statu-no'><i class='fa fa-exclamation-triangle'></i> "+WST.lang('label_informs_result4')+"</span>";
            }},
            {title:WST.lang('op'), name:'op' ,width:80, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
		            h += "<a class='btn btn-blue' href='javascript:toView(" + item['informId'] + ")'><i class='fa fa-search'></i>"+WST.lang('view')+"</a> ";
		            if(item['informStatus']==0)
		            h += "<a class='btn btn-blue' href='javascript:toHandle(" + item['informId'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('handle')+"</a> ";
		            return h;
	            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: (h-90),indexCol: true, indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/Informs/pageQuery'), fullWidthRows: true, autoLoad: false,
        remoteSort:true ,
        sortName: 'informTime',
        sortStatus: 'desc',
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function toView(id){
	location.href=WST.U('admin/Informs/view','cid='+id+'&p='+WST_CURR_PAGE);
}
function toHandle(id){
	location.href=WST.U('admin/Informs/toHandle','cid='+id+'&p='+WST_CURR_PAGE);
}
function loadGrid(page){
	var p = WST.getParams('.j-ipt');
	page=(page<=1)?1:page;
    p.page = page;
	mmg.load(p);
}



function finalHandle(id,p){
   var params = {};
   params.cid = id;
   params.finalResult = $.trim($('#finalResult').val());
   params.informStatus = $('input:radio:checked').val();
   if(params.finalResult==''){
     WST.msg(WST.lang('require_informs_handle'),{icon:2});
     return;
   }
   if(typeof(params.informStatus)=='undefined'){
		WST.msg(WST.lang('require_informs_result'),{icon:2});
		return;
	}
   var c = WST.confirm({title:WST.lang('label_informs_box_title'),content:WST.lang('require_informs_tips'),yes:function(){
     layer.close(c);
     $.post(WST.U('Admin/Informs/finalHandle'),params,function(data,textStatus){
        var json = WST.toAdminJson(data);
        if(json.status=='1'){
          WST.msg(json.msg,{icon:1});
          location.reload();
        }else if(json.status == '2'){
          location.href=WST.U('admin/informs/index',"p="+p);
        }else{
          WST.msg(json.msg,{icon:2});
        }
      });
   }});
}

  
