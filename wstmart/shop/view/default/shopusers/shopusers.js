var mmg;
function initGrid(p,userId){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('account_name'), name:'loginName' ,width:200,sortable:true},
        {title:WST.lang('role_name'), name:'roleId' ,width:200,sortable:true,renderer:function(val,item,rowIndex){
            return item['roleId']?(item['roleName']?item['roleName']:WST.lang('nothing')):WST.lang('administrators');
        }},
        {title:WST.lang('creation_time'), name:'createTime' ,width:200,sortable:true},
        {title:WST.lang('op'), name:'' ,width:100, align:'center', renderer: function(val,item,rowIndex){
            var h = "";
			h += "<a  class='btn btn-blue' onclick='javascript:toEdit("+item['id']+")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
            h += "<a  class='btn btn-blue' onclick='javascript:toNotify("+item['id']+")'><i class='fa fa-pencil'></i>"+WST.lang('receive_reminder_settings')+"</a> ";
            if(item['roleId'] > 0 && item['userId']!=userId){
                h += "<a  class='btn btn-red' onclick='javascript:del(" + item['id'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
            }
            return h;
        }}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',checkCol:true,multiSelect:true,
        url: WST.U('shop/shopusers/pageQuery'), fullWidthRows: true, autoLoad: false,remoteSort: true,
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
	location.href = WST.U('shop/shopusers/edit','id='+id+'&p='+WST_CURR_PAGE);
}
function toAdd(){
    location.href = WST.U('shop/shopusers/add','p='+WST_CURR_PAGE);
}
function toNotify(id){
    location.href = WST.U('shop/shopusers/notify','id='+id+'&p='+WST_CURR_PAGE);
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
			var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		    $.post(WST.U('shop/shopusers/toAdd'),params,function(data,textStatus){
		    	layer.close(loading);
		    	var json = WST.toJson(data);
		    	if(json.status=='1'){
		    		WST.msg(json.msg,{icon:1},function(){
						location.href=WST.U('shop/shopusers/index',"p="+p);
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
			var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		    $.post(WST.U('shop/shopusers/toEdit'),params,function(data,textStatus){
		    	layer.close(loading);
		    	var json = WST.toJson(data);
		    	if(json.status=='1'){
		    		WST.msg(json.msg,{icon:1},function(){
						location.href=WST.U('shop/shopusers/index','p='+p);
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
	var c = WST.confirm({content:WST.lang('shopUsers_tips17'),yes:function(){
		layer.close(c);
		var load = WST.load({msg:WST.lang('loading')});
		$.post(WST.U('shop/shopusers/del'),{id:id},function(data,textStatus){
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