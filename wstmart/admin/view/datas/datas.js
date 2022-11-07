var zTree,mmg,rMenu,diff = 0;
function layout(){
	var h = WST.pageHeight();
	var w = WST.pageWidth();
	$('.j-layout').width(w-10);
	$('.j-layout-left').width(200).height(h-110);
	$('.j-layout-center').width(w-220).height(h-110);
	$('#headTip').WSTTips({width:200,height:35,callback:function(v){
		 diff = v?110:53;
		 $('.j-layout-left').height(h-diff);
		 $('#menuTree').height(h-diff-40);
		 $('.j-layout-center').height(h-diff);
		 if(mmg)mmg.resize({height:h-diff-125});
    }});
}
function initGrid(id){
	var h = WST.pageHeight();
	var cols = [
            {title:WST.lang('label_data_name'), name:'dataName', width: 60},
            {title:WST.lang('label_data_code'), name:'dataVal'  ,width:60},
            {title:WST.lang('sort'), name:'dataSort' ,width:10},
            {title:WST.lang('op'), name:'' ,width:60, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
			    if(WST.GRANT.SJGL_02)h += "<a  class='btn btn-blue' onclick='javascript:getForEdit(" + item['id'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
			    if(WST.GRANT.SJGL_03)h += "<a  class='btn btn-red' onclick='javascript:toDel(" + item['id'] + ","+item['catId']+")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
            ];
    mmg = $('.mmg').mmGrid({height: (diff==0)?(h-220):(h-diff-125),cols: cols,method:'POST',indexCol: true,
        url: WST.U("admin/datas/childQuery"), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    mmg.load({id:id});  
    
	isInit = true;
}


var isInit = false;
$(window).resize(function(){layout();});
$(function(){
	layout();
	$('#menuTree').height(WST.pageHeight()-140);
	var setting = {
	      view: {
	           selectedMulti: false,
	           dblClickExpand:false
	      },
	      async: {
	           enable: true,
	           url:WST.U('admin/datacats/listQuery'),
	           autoParam:["id", "name=n", "level=lv"]
	      },
	      callback:{
	           onRightClick: onRightClick,
	           onClick: onClick,
	           onAsyncSuccess: onAsyncSuccess
	      }
	};
	$.fn.zTree.init($("#menuTree"), setting);
	zTree = $.fn.zTree.getZTreeObj("menuTree");
	rMenu = $("#rMenu");



	$('#m_add').click(function(){
		treeNode = zTree.getSelectedNodes()[0];
	    editMenu({menuId:0,menuName:'',parentId:treeNode.id,pnode:treeNode,menuSort:0});
	});
	$('#m_edit').click(function(){
		treeNode = zTree.getSelectedNodes()[0];
	    getForEditMenu(treeNode.id);
	    return false;
	});
	$('#m_del').click(function(){
		treeNode = zTree.getSelectedNodes()[0];
	              	layer.confirm(WST.lang('del_tips'), {btn: [WST.lang('submit'),WST.lang('cancel')]}, function(){
	              	    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	              	    $.post(WST.U('admin/datacats/del'),{id:treeNode.id},function(data,textStatus){
	              		     layer.close(loading);
	              		     var json = WST.toAdminJson(data);
	              		     if(json.status=='1'){
	              		          WST.msg(json.msg,{icon:1});
	              		          zTree.reAsyncChildNodes(treeNode.getParentNode(), "refresh",true);
	              		     }else{
	              		          WST.msg(json.msg,{icon:2});
	              		     }
	              		 });
	                  });
	    return false;
	});
});
function loadGrid(id){
    mmg.load({id:id,page:1});       
}
function onAsyncSuccess(event, treeId, treeNode, msg){
	var json = WST.toAdminJson(msg);
	if(json && json.id==0){
		var treeNode = zTree.getNodeByTId('menuTree_1');
		zTree.reAsyncChildNodes(treeNode, "refresh",true);
		zTree.expandAll(treeNode,true);
	}
}
function onClick(e,treeId, treeNode){
	if(treeNode.id>0){
	      $('.wst-toolbar').show();
	      $('#maingrid').show();
	}else{
	    $('.wst-toolbar').hide();
	    $('#maingrid').hide();
	}
	if(!isInit){
		initGrid(treeNode.id);
	}else{
        loadGrid(treeNode.id);
	}
}
function onRightClick(event, treeId, treeNode) {
	if(!treeNode)return;
	if(!WST.GRANT.CDGL_01 && !WST.GRANT.CDGL_02 && !WST.GRANT.CDGL_03)return;
	if(!treeNode && event.target.tagName.toLowerCase() != "button" && $(event.target).parents("a").length == 0) {
		zTree.cancelSelectedNode();
		showRMenu("root", event.clientX, event.clientY);
	}else if(treeNode && !treeNode.noR) {
		zTree.selectNode(treeNode);
		var level = treeNode.level;
		if(level==0){
			$("#m_edit").hide();
			$("#m_del").hide();
		}else{
			$("#m_edit").show();
			$("#m_del").show();
		}
		showRMenu("node", event.clientX, event.clientY);
	}
}
function showRMenu(type, x, y) {
	$("#rMenu ul").show();
    y += document.body.scrollTop;
    x += document.body.scrollLeft;
    rMenu.css({"top":y+"px", "left":x+"px", "visibility":"visible"});
	$("body").bind("mousedown", onBodyMouseDown);
}
function hideRMenu() {
	if (rMenu) rMenu.css({"visibility": "hidden"});
	$("body").unbind("mousedown", onBodyMouseDown);
}
function onBodyMouseDown(event){
	if (!(event.target.id == "rMenu" || $(event.target).parents("#rMenu").length>0)) {
		rMenu.css({"visibility" : "hidden"});
	}
}
function getForEditMenu(id){
	 var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/datacats/get'),{id:id},function(data,textStatus){
          layer.close(loading);
          var json = WST.toAdminJson(data);
          if(json.catId){
          	  editMenu(json);
          }else{
          	  WST.msg(json.msg,{icon:2});
          }
   });
}	                    		
function editMenu(obj){
	WST.setValues(obj);
	for(var key in WST.conf.sysLangs){
		var val = obj['langParams']?obj['langParams'][WST.conf.sysLangs[key]['id']]['catName']:'';
		$('#langParams'+WST.conf.sysLangs[key]['id']+'catName').val(val);
	}
	var box = WST.open({ title:(obj.catId==0)?WST.lang('add_data_cat'):WST.lang('edit_data_cat'),type: 1,area: ['100%', '100%'],offset: 't',btnAlign: 'c',
	                content:$('#menuBox'),
	                btn:[WST.lang('submit'),WST.lang('cancel')],
	                end:function(){$('#menuBox').hide();$("#catName").val('');$("#catCode").val('');},
	                yes: function(index, layero){
	                	for(var key in WST.conf.sysLangs){
		                	if(!$('#langParams'+WST.conf.sysLangs[key]['id']+'catName').isValid())return;
		                }
	                	if(!$('#catCode').isValid())return;
		                var params = WST.getParams('.ipt2');
		                var n = 0;
					    params['langParams'] = {};
					    for(var key in WST.conf.sysLangs){
					        n = WST.conf.sysLangs[key]['id'];
					        params['langParams'][n] = {};
					    	params['langParams'][n]['catName'] = params['langParams'+n+'catName'];
					    }
		                params.catId = obj.catId;
		                var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		                $.post(WST.U('admin/datacats/'+((params.catId)?"edit":"add")),params,function(data,textStatus){
		                	layer.close(loading);
		                	var json = WST.toAdminJson(data);
		                	if(json.status=='1'){
		                	    WST.msg(json.msg,{icon:1});
		                		layer.close(box);
		                	    $('#menuForm')[0].reset();
		                		treeNode = zTree.getNodeByTId('menuTree_1');
		                		zTree.reAsyncChildNodes(treeNode, "refresh",true);
		                	}else{
		                		WST.msg(json.msg,{icon:2});
		                	}
		                });
	            }});
}

function getForEdit(id){
	 var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
     $.post(WST.U('admin/datas/get'),{id:id},function(data,textStatus){
           layer.close(loading);
           var json = WST.toAdminJson(data);
           if(json.id){
           		WST.setValues(json);
           		for(var key in WST.conf.sysLangs){
					var val = json['langParams']?json['langParams'][WST.conf.sysLangs[key]['id']]['dataName']:'';
					$('#langParams'+WST.conf.sysLangs[key]['id']+'dataName').val(val);
				}
           		toEdit(json.id);
           }else{
           		WST.msg(json.msg,{icon:2});
           }
    });
}

function toEdit(id){
	var title = WST.lang('add');
	if(id>0){
		title = WST.lang('edit');
	}else{
		$('#dataForm')[0].reset();
	}
	var box = WST.open({title:title,type:1,content:$('#dataBox'),area: ['100%', '100%'],offset: 't',btnAlign: 'c',btn:[WST.lang('submit'),WST.lang('cancel')],
               end:function(){$('#dataBox').hide();},
		       yes:function(){
		            for(var key in WST.conf.sysLangs){
		                if(!$('#langParams'+WST.conf.sysLangs[key]['id']+'dataName').isValid())return;
		            }
		            if(!$('#dataVal').isValid())return;
	                var params = WST.getParams('.ipt');
	                var n = 0;
					params['langParams'] = {};
				    for(var key in WST.conf.sysLangs){
					    n = WST.conf.sysLangs[key]['id'];
					    params['langParams'][n] = {};
					    params['langParams'][n]['dataName'] = params['langParams'+n+'dataName'];
					}
	                params.catId = zTree.getSelectedNodes()[0].id;
	                params.id = id;
	                var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           		$.post(WST.U('admin/datas/'+((id==0)?"add":"edit")),params,function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(json.msg,{icon:1});
	           			    	$('#dataForm')[0].reset();
	           			    	layer.close(box);
	           		            loadGrid(params.catId);
	           			  }else{
	           			        WST.msg(json.msg,{icon:2});
	           			  }
	           		});
	          }});
}

function toDel(id,pid){
	var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/datas/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(json.msg,{icon:1});
	           			    	layer.close(box);
	           		            loadGrid(pid);
	           			  }else{
	           			    	WST.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}