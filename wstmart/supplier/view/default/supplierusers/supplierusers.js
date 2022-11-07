var mmg;
function initGrid(p,userId){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('label_supp_users_login_name'), name:'loginName' ,width:200,sortable:true},
        {title:WST.lang('label_supp_users_role_name'), name:'roleId' ,width:200,sortable:true,renderer:function(val,item,rowIndex){
            return item['roleId']?(item['roleName']?item['roleName']:WST.lang('none')):WST.lang('admin');
        }},
        {title:WST.lang('create_time'), name:'createTime' ,width:200,sortable:true},
        {title:WST.lang('op'), name:'' ,width:100, align:'center', renderer: function(val,item,rowIndex){
            var h = "";
            h += "<a  class='btn btn-blue' onclick='javascript:toEdit("+item['id']+")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
            h += "<a  class='btn btn-blue' onclick='javascript:toNotify("+item['id']+")'><i class='fa fa-pencil'></i>"+WST.lang('receive_notify_set')+"</a> ";
            if(item['roleId'] > 0 && userId!=item['userId']){
                h += "<a  class='btn btn-red' onclick='javascript:del(" + item['id'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
            }
            return h;
        }}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',checkCol:true,multiSelect:true,
        url: WST.U('supplier/supplierusers/pageQuery'), fullWidthRows: true, autoLoad: false,remoteSort: true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}

function loadGrid(p){
	p=(p<=1)?1:p;
    mmg.load({userName:$('#userName').val(),page:p});
}

function toEdit(id){
	location.href = WST.U('supplier/supplierusers/edit','id='+id+'&p='+WST_CURR_PAGE);
}
function toAdd(){
    location.href = WST.U('supplier/supplierusers/add','p='+WST_CURR_PAGE);
}
function toNotify(id){
    location.href = WST.U('supplier/supplierusers/notify','id='+id+'&p='+WST_CURR_PAGE);
}

/**保存角色数据**/
function add(p){
	$('#editForm').isValid(function(v){
		if(v){
			var params = WST.getParams('.ipt');
			if(WST.conf.IS_CRYPT=='1'){
	            var public_key=$('#token').val();
	            var exponent="10001";
	       	    var rsa = new RSAKey();
	            rsa.setPublic(public_key, exponent);
	            params.loginPwd = rsa.encrypt(params.loginPwd);
	            params.reUserPwd = rsa.encrypt(params.reUserPwd);
	        }
			var loading = WST.msg(WST.lang('submitting_data'), {icon: 16,time:60000});
		    $.post(WST.U('supplier/supplierusers/toAdd'),params,function(data,textStatus){
		    	layer.close(loading);
		    	var json = WST.toJson(data);
		    	if(json.status=='1'){
		    		WST.msg(json.msg,{icon:1},function(){
						location.href=WST.U('supplier/supplierusers/index',"p="+p);
					});
		    	}else{
		    		WST.msg(json.msg,{icon:2});
		    	}
		    });
		}
	});
}

function edit(p){
	$('#editForm').isValid(function(v){
		if(v){
			var params = WST.getParams('.ipt');
			if(WST.conf.IS_CRYPT=='1' && params.newPass!=""){
	            var public_key=$('#token').val();
	            var exponent="10001";
	       	    var rsa = new RSAKey();
	            rsa.setPublic(public_key, exponent);
	            params.oldPass = rsa.encrypt(params.oldPass);
	            params.newPass = rsa.encrypt(params.newPass);
	            params.reNewPass = rsa.encrypt(params.reNewPass);
	        }
			var loading = WST.msg(WST.lang('submitting_data'), {icon: 16,time:60000});
		    $.post(WST.U('supplier/supplierusers/toEdit'),params,function(data,textStatus){
		    	layer.close(loading);
		    	var json = WST.toJson(data);
		    	if(json.status=='1'){
		    		WST.msg(json.msg,{icon:1},function(){
						location.href=WST.U('supplier/supplierusers/index','p='+p);
					});
		    	}else{
		    		WST.msg(json.msg,{icon:2});
		    	}
		    });
		}
	});
}

//删除角色
function del(id){
	var c = WST.confirm({content:WST.lang('delete_account_tips'),yes:function(){
		layer.close(c);
		var load = WST.load({msg:WST.lang('submitting_data')});
		$.post(WST.U('supplier/supplierusers/del'),{id:id},function(data,textStatus){
			layer.close(load);
		    var json = WST.toJson(data);
		    if(json.status==1){
		    	WST.msg(json.msg,{icon:1});
                loadGrid(WST_CURR_PAGE);
		    }else{
		    	WST.msg(json.msg,{icon:2});
		    }
		});
	}});
}
