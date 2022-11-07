var mmg;
function initGrid(p){
	var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('role_name'), name:'roleName', width: 30},
            {title:WST.lang('role_notes'), name:'roleDesc' },
            {title:WST.lang('op'), name:'' ,width:160, lockWidth:true,align:'center', renderer: function(val,item,rowIndex){
                var h = "";
	            if(WST.GRANT.JSGL_02)h += "<a  class='btn btn-blue' onclick='javascript:toEdit(" + item['roleId'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
	            if(WST.GRANT.JSGL_03)h += "<a  class='btn btn-red' onclick='javascript:toDel(" + item['roleId'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
	            return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: (h-166),indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/roles/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });     
    $('#headTip').WSTTips({width:200,height:35,callback:function(v){
		 var diff = v?166:137;
	     mmg.resize({height:h-diff})
	}});
    loadQuery(p);
}
function loadQuery(p){
    p=(p<=1)?1:p;
    mmg.load({page:p});
}
function toEdit(id){
	location.href=WST.U('admin/roles/toEdit','id='+id+'&p='+WST_CURR_PAGE);
}
function toDel(id){
	var box = WST.confirm({content:WST.lang('are_you_sure_you_want_to_delete_it'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           $.post(WST.U('admin/roles/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(WST.lang('op_ok'),{icon:1});
	           			    	layer.close(box);
                              loadQuery(WST_CURR_PAGE);
	           			  }else{
	           			    	WST.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}
function getNodes(event, treeId, treeNode){
	zTree.expandNode(treeNode,true, true, true);
	if($.inArray(treeNode.privilegeCode,rolePrivileges)>-1){
		zTree.checkNode(treeNode,true,true);
	}
}
function save(p){
	if(!$('#roleName').isValid())return;
	var nodes = zTree.getChangeCheckedNodes();
	var privileges = [];
	for(var i=0;i<nodes.length;i++){
		if(nodes[i].isParent==0)privileges.push(nodes[i].privilegeCode);
	}
	var params = WST.getParams('.ipt');
	params.privileges = privileges.join(',');
	var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/roles/'+((params.roleId==0)?"add":"edit")),params,function(data,textStatus){
    	layer.close(loading);
    	var json = WST.toAdminJson(data);
    	if(json.status=='1'){
    		WST.msg(WST.lang('op_ok'),{icon:1});
    		location.href=WST.U('admin/roles/index',"p="+p);
    	}else{
    		WST.msg(json.msg,{icon:2});
    	}
    });
}
