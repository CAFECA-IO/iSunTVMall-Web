var mmg;
function initGrid(staffId,p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('employee_account_number'), name:'loginName', width: 50},
            {title:WST.lang('label_logoperates_staff_name_plo'), name:'staffName' ,width:60},
            {title:WST.lang('staff_role'), name:'roleName' ,width:50},
            {title:WST.lang('employee_number'), name:'staffNo' ,width:30},
            {title:WST.lang('working_condition'), name:'workStatus' ,width:20,renderer: function(val,item,rowIndex){
                        return (val==1)?"<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('on_the_job')+"</span>":"<span class='statu-no'><i class='fa fa-ban'></i>"+WST.lang('quit')+"</span>";
            }},
            {title:WST.lang('label_logshoplogins_login_time'), name:'lastTime' ,width:100},
            {title:WST.lang('label_logshoplogins_ip'), name:'lastIP' ,width:60},
            {title:WST.lang('op'), name:'' ,width:150, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
			    if(WST.GRANT.ZYGL_02)h += "<a  class='btn btn-blue' onclick='javascript:toEditPass(" + item['staffId'] + ")'><i class='fa fa-key'></i>"+WST.lang('edit_pass')+"</a> ";
			    if(WST.GRANT.ZYGL_02)h += "<a  class='btn btn-blue' onclick='javascript:toEdit(" + item['staffId'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
			    if(item['staffId']!=1 && item['staffId']!=staffId && WST.GRANT.ZYGL_03)h += "<a  class='btn btn-red' onclick='javascript:toDel(" + item['staffId'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: (h-166),indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/staffs/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });    
    $('#headTip').WSTTips({width:200,height:35,callback:function(v){
		 var diff = v?166:135;
	     mmg.resize({height:h-diff})
	}});
    loadGrid(p);
}
function loadGrid(p){
	p=(p<=1)?1:p;
	mmg.load({page:p,key:$('#key').val()});
}
function toEdit(id){
	location.href=WST.U('admin/staffs/'+((id==0)?'toAdd':'toEdit'),'id='+id+'&p='+WST_CURR_PAGE);
}
function toDel(id){
	var box = WST.confirm({content:WST.lang('are_you_sure_you_want_to_delete_it'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           $.post(WST.U('admin/staffs/del'),{id:id},function(data,textStatus){
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
function checkLoginKey(obj){
	if($.trim(obj.value)=='')return;
	var params = {key:obj.value,userId:0};
	var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/staffs/checkLoginKey'),params,function(data,textStatus){
    	layer.close(loading);
    	var json = WST.toAdminJson(data);
    	if(json.status!='1'){
    		WST.msg(json.msg,{icon:2});
    		obj.value = '';
    	}
    });
}
function save(p){
	var params = WST.getParams('.ipt');
	if(params.staffId==0){
		if(!$('#loginName').isValid())return;
		if(!$('#loginPwd').isValid())return;
	}
	if(!$('#staffName').isValid())return;
	var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/staffs/'+((params.staffId==0)?"add":"edit")),params,function(data,textStatus){
    	layer.close(loading);
    	var json = WST.toAdminJson(data);
    	if(json.status=='1'){
    		WST.msg(WST.lang('op_ok'),{icon:1});
    		location.href=WST.U('admin/staffs/index',"p="+p);
    	}else{
    		WST.msg(json.msg,{icon:2});
    	}
    });
}
function toEditPass(id){
	var w = WST.open({type: 1,title:WST.lang('edit_pass'),shade: [0.6, '#000'],border: [0],content:$('#editPassBox'),area: ['490px', '300px'],
	    btn: [WST.lang('confirm'), WST.lang('cancel')],end:function(){$('#editPassBox').hide();},yes: function(index, layero){
	    	$('#editPassFrom').isValid(function(v){
	    		if(v){
		        	var params = WST.getParams('.ipt');
		        	params.staffId = id;
		        	var ll = WST.msg(WST.lang('loading'));
				    $.post(WST.U('admin/Staffs/editPass'),params,function(data){
				    	layer.close(ll);
				    	var json = WST.toAdminJson(data);
						if(json.status==1){
							WST.msg(json.msg, {icon: 1});
							layer.close(w);
						}else{
							WST.msg(json.msg, {icon: 2});
						}
				   });
	    		}})
        }
	});
}
