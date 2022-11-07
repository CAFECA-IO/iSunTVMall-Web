function initSummary(){
	 var loading = WST.msg(WST.lang('loading'),{icon: 16,time:60000});
	 $.post(WST.U('admin/resources/summary'),{rnd:Math.random()},function(data,textStatus){
	       layer.close(loading);
	       var json = WST.toAdminJson(data);
	       if(json.status==1){
	    	   json = json.data;
	    	   var html = [],tmp,i=1,divLen = 0;
	    	   for(var key in json){
	    		   if(key=='_WSTSummary_')continue;
	    		   tmp = json[key];
	    		   var arr = ['appraises','articles','complains','goods','image','feedbacks'];
	    		   var picHandle = false;
	    		   var picHandleHtml = '';
	    		   if($.inArray(key,arr)>-1){
					   picHandle = true;
				   }
	    		   if(picHandle){
					   picHandleHtml += '<a  class="btn btn-blue" onclick="javascript:picInfo(\''+key+'\')"><i class="fa fa-pencil"></i>'+WST.lang('reource_img_handle')+'</a>';
				   }
	    		   html.push('<tr class="mmg-body wst-grid-tree-row" height="28" align="center">'
	    				     ,'<td class="wst-grid-tree-row-cell" style="width:26px;">'+(i++)+'</td>'
	    				     ,'<td class="wst-grid-tree-row-cell">'+WST.blank(tmp.directory,WST.lang('unnofile'))+'('+key+')'+'</td>'
	    				     ,'<td class="wst-grid-tree-row-cell" align="left">'+getCharts(json['_WSTSummary_'],tmp.data['1'],tmp.data['0'])+'</td>'
	    				     ,'<td class="wst-grid-tree-row-cell" nowrap>'+tmp.data['1']+'/'+tmp.data['0']+'</td>'
	    				     ,'<td class="wst-grid-tree-row-cell"><a class="btn btn-blue" href="'+WST.U('admin/resources/lists','keyword='+key)+'"><i class="fa fa-search"></i>'+WST.lang('view')+'</a>'+picHandleHtml+'</td>');
	    	   }
	    	   $('#list').html(html.join(''));
	       }else{
	           WST.msg(json.msg,{icon:2});
	       }
	 });
	 $('#headTip').WSTTips({width:200,height:35,callback:function(v){}});  
}
// 处理图片
var picTotal,num=0;
function picInfo(key){
	var box = WST.open({title:WST.lang('handle_img'),type:1,content:$('#picHandleBox'),area: ['50%', '40%'],btn: [WST.lang('submit'),WST.lang('cancel')],
		yes:function(){
			var loading = WST.msg(WST.lang('handling_img'), {icon: 16,time:60000});
			var type = $("input[name='handleType']:checked").val();
			$.post(WST.U('admin/resources/getPicInfo'),{key:key,type:type},function(data,textStatus){
				layer.close(loading);
				var json = WST.toAdminJson(data);
				if(json.status==1){
					picTotal = json.data;
					WST.msg(json.msg,{icon:1});
					layer.close(box);
					picHandle(type);
				}else{
					WST.msg(json.msg,{icon:2});
				}
			});
		},cancel:function(){
			$('#picHandleBox').hide();
		},end:function(){
			$('#picHandleBox').hide();
		}});
}

function picHandle(type){
	id = picTotal[num]['resId'];
	$.post(WST.U('admin/resources/picHandle'),{id:id,type:type},function(data,textStatus){
		var json = WST.toAdminJson(data);
		if(json.status=='1'){
			if(num < picTotal.length-1){
				num++;
				WST.msg(WST.lang('handle_num',[num,num+"/"+picTotal.length]));
				picHandle(type);
				return;
			}else{
				num=0;
				WST.msg(WST.lang('handle_result'),{icon:1});
			}
		}else{
			WST.msg(json.msg,{icon:2});
		}
	});
}

function getCharts(maxSize,size1,size2){
	var w = WST.pageWidth()-600;
	var tlen = (parseFloat(size1,10)+parseFloat(size2,10))*w/maxSize+1;
	var s1len = parseFloat(size1,10)*w/maxSize;
	var s2len = parseFloat(size2,10)*w/maxSize;
	return ['<div style="width:'+tlen+'px"><div style="height:20px;float:left;width:'+s1len+'px;background:#1890ff;"></div><div style="height:20px;float:left;width:'+s2len+'px;background:#ddd;"></div></div>'];
}
var mmg;
function initGrid(p){
   var h = WST.pageHeight();
   var cols = [
            {title:WST.lang('label_resource_file'), name:'resPath', width: 50, renderer: function(val,item,rowIndex){
				if(item['resType']==0){
					return "<span class='weixin'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['resPath']
					+"'><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['resPath']+"'></span></span>";
				}else if(item['resType']==1){
					return '<video muted src="'+WST.conf.RESOURCE_PATH+'/'+item["resPath"]+'" id="previewVideo" width="50" height="50"></video>';
				}
				
				
            }},
            {title:WST.lang('label_resource_s_type'), name:'resType', width: 50, renderer: function(val,item,rowIndex){
				var text = "";
				switch(val){
					case 0:
						text = WST.lang('label_resource_s_type0');
					break;
					case 1:
						text = WST.lang('label_resource_s_type1');
					break;
				}
            	return text;
            }},
            {title:WST.lang('upload_user'), name:'userName' ,width:200, renderer: function(val,item,rowIndex){
               if(item['fromType']==1){
	        		return WST.lang('resource_staff')+item['loginName'];
	        	}else{
	        		if(WST.blank(item['userType'])==''){
	        			return WST.lang('resource_visitor');
	        		}else{
	        			if(item['userType']==1){
	        				return WST.lang('resource_shop',[item['shopName']])+item['loginName'];
	        			}else{
	        				return item['loginName'];
	        			}
	        		}
	        	}
            }},
            {title:WST.lang('label_resource_size'), name:'resSize' ,width:30},
            {title:WST.lang('label_resource_status'), name:'isUse' ,width:30, renderer: function(val,item,rowIndex){
               return (val==1)?"<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('label_resource_t_all1')+"</span>":"<span class='statu-no'><i class='fa fa-ban'></i> "+WST.lang('label_resource_t_all0')+"</span>";
            }},
            {title:WST.lang('label_resource_time'), name:'createTime' ,width:120},
            {title:WST.lang('op'), name:'' ,width:80, align:'center', renderer: function(val,item,rowIndex){
                var h = '<a class="btn btn-blue btn-mright" href="javascript:toView('+item['resId']+',\''+item['resPath']+'\','+item['resType']+')"><i class="fa fa-search"></i>'+WST.lang('view')+'</a>';
	        	if(WST.GRANT.TPKJ_04)h += "<button  class='btn btn-red' onclick='javascript:toDel(" + item['resId'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</button> ";
                return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-89,indexCol: true,indexColWidth:50, cols: cols,method:'POST',checkCol:true,multiSelect:true,
        url: WST.U('admin/resources/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator()
        ]
    }); 
    loadGrid(p);
}
function loadGrid(p){
	p=(p<=1)?1:p;
	mmg.load({page:p,keyword:$('#key').val(),isUse:$('#isUse').val(),resType:$('#resType').val()});
}
function toView(id,res,resType){
	var content = WST.U('admin/resources/checkImages','resPath='+res);
	if(resType==1){
		content = WST.U('admin/resources/checkVideo','resPath='+res);
	}
    parent.showBox({title:WST.lang('label_resource_detail'),type:2,content:content,area: ['700px', '510px'],btn:[WST.lang('close')]});
}
//批量删除
function toBatchDel(){
    var rows = mmg.selectedRows();
    if(rows.length==0){
        WST.msg(WST.lang('require_resource_del'),{icon:2});
        return;
    }
    var ids = [];
    for(var i=0;i<rows.length;i++){
        ids.push(rows[i]['resId']);
    }
    var content=WST.lang('require_resource_del_tips');
    toDel(ids,content);
    return false;
}
function toDel(id,content){
	if(!content) content=WST.lang('resource_tips01');
	var box = WST.confirm({content:content,yes:function(){
		var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		$.post(WST.U('admin/resources/del'),{id:id},function(data,textStatus){
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
function toolTip(){
    WST.toolTip();
}