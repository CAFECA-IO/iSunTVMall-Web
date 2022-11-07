var grid,oldData = {},oldorderData = {};
function initGrid(){
	grid = $('#maingrid').WSTGridTree({
		url:WST.U('admin/trades/pageQuery'),
		pageSize:10000,
		pageSizeOptions:[10000],
		height:'99%',
        width:'100%',
        minColToggle:6,
        delayLoad :true,
        rownumbers:true,
        columns: [
	        { display: WST.lang('label_trade_name'), width: 230,name: 'tradeName', id:'tradeId', align: 'left',isSort: false},
	        { display: WST.lang('label_trade_simple_name'), width: 150,name: 'simpleName', id:'tradeId', align: 'left',isSort: false},
            { display: WST.lang('is_show'), width: 70, name: 'isShow',isSort: false,
                render: function (item)
                {
                    return '<input type="checkbox" '+((item.isShow==1)?"checked":"")+' class="ipt" lay-skin="switch" lay-filter="isShow" data="'+item.tradeId+'" lay-text="'+WST.lang('is_show_val1')+'|'+WST.lang('is_show_val0')+'">';
                }
            },
            { display: WST.lang('sort'), name: 'tradeSort',width: 50,isSort: false,render: function (item)
                {
                	oldorderData[item.tradeId] = item.tradeSort;
                    return '<input type="text" style="width:50px" value="'+item.tradeSort+'" onblur="javascript:editOrder('+item.tradeId+',this)"/>';
            }},
            { display: WST.lang('label_trade_fee'), width: 50, name: 'tradeFee',isSort: false},
	        { display: WST.lang('op'), name: 'op',width: 170,isSort: false,
	        	render: function (rowdata){
		            var h = "";
			        if(WST.GRANT.SHYGL_01)h += "<a class='btn btn-blue' href='javascript:toEdit("+rowdata["tradeId"]+",0)'><i class='fa fa-plus'></i>"+WST.lang('trade_add_sub')+"</a> ";
		            if(WST.GRANT.SHYGL_02)h += "<a class='btn btn-blue' href='javascript:toEdit("+rowdata["parentId"]+","+rowdata["tradeId"]+")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
		            if(WST.GRANT.SHYGL_03)h += "<a class='btn btn-red' href='javascript:toDel("+rowdata["parentId"]+","+rowdata["tradeId"]+")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
		            return h;
	        	}}
        ],
        callback:function(){
		    layui.form.render();
	    }
    });
    layui.form.on('switch(isShow)', function(data){
        var id = $(this).attr("data");
        if(this.checked){
            toggleIsShow(id, 1);
        }else{
            toggleIsShow(id, 0);
        }
   });
}

function toggleIsShow(id,isShow){
	if(!WST.GRANT.SPFL_02)return;
	if(isShow==0){
		var box = WST.confirm({content:WST.lang("trade_hide_tips"),yes:function(){
			  layer.close(box);
              var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
			  $.post(WST.U('admin/trades/editiIsShow'),{id:id,isShow:isShow},function(data,textStatus){
					layer.close(loading);
					var json = WST.toAdminJson(data);
					if(json.status=='1'){
						 WST.msg(json.msg,{icon:1});
						 grid.reload(id);
					}else{
						 WST.msg(json.msg,{icon:2});
					}
			  });
		}});
	}else{
		var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	    $.post(WST.U('admin/trades/editiIsShow'),{id:id,isShow:isShow},function(data,textStatus){
			layer.close(loading);
			var json = WST.toAdminJson(data);
			if(json.status=='1'){
				 WST.msg(json.msg,{icon:1});
				 grid.reload(id);
			}else{
				 WST.msg(json.msg,{icon:2});
			}
		});
	}
}
var editBox;
function toEdit(pid,id){
	var w = WST.pageWidth();
	var h = WST.pageHeight();
    editBox = WST.open({type:2,title:false,content:WST.U('admin/trades/toEdit','id='+id+'&pid='+pid),closeBtn:0,area: [w+'px', h+'px'],offset:['0px','0px']})
}
function closeEditBox(){
    layer.close(editBox);
}
function loadGrid(id){
	grid.reload(id);
}
function toEdits(){
    var id = $('#tradeId').val();
    var params = WST.getParams('.ipt');
    var n = 0;
    params['langParams'] = {};
    for(var key in WST.conf.sysLangs){
        n = WST.conf.sysLangs[key]['id'];
        params['langParams'][n] = {};
    	params['langParams'][n]['tradeName'] = params['langParams'+n+'tradeName'];
    	params['langParams'][n]['simpleName'] = params['langParams'+n+'simpleName'];
    	params['langParams'][n]['subTitle'] = params['langParams'+n+'subTitle'];
    	params['langParams'][n]['seoTitle'] = params['langParams'+n+'seoTitle'];
    	params['langParams'][n]['seoKeywords'] = params['langParams'+n+'seoKeywords'];
    	params['langParams'][n]['seoDes'] = params['langParams'+n+'seoDes'];
    }
    params.id = id;
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/trades/'+((id>0)?"edit":"add")),params,function(data,textStatus){
        layer.close(loading);
        parent.loadGrid((params.parentId>0)?params.parentId:id);
        var json = WST.toAdminJson(data);
        if(json.status=='1'){
            WST.msg(json.msg,{icon:1},function(){
                parent.closeEditBox();
            });
        }else{
            WST.msg(json.msg,{icon:2});
        }
    });
}
var isInitUpload = false;

function toDel(pid,id){
	var box = WST.confirm({content:WST.lang("trade_delete_tips"),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/trades/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			       WST.msg(json.msg,{icon:1});
	           			       layer.close(box);
	           		           grid.reload(pid);
	           			  }else{
	           			       WST.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}

function initUpload(){
	isInitUpload = true;
	//文件上传
	WST.upload({
	    pick:'#tradeFilePicker',
	    formData: {dir:'trades'},
	    accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
	    callback:function(f){
	      var json = WST.toAdminJson(f);
	      if(json.status==1){
	        $('#uploadMsg').empty().hide();
	        //将上传的图片路径赋给全局变量
		    $('#tradeImg').val(json.savePath+json.thumb);
		    $('#preview').html('<img src="'+WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb+'" height="30" />');
	      }else{
	      	WST.msg(json.msg,{icon:2});
	      }
	  },
	  progress:function(rate){
	      $('#uploadMsg').show().html(WST.lang('upload_rate')+rate+"%");
	  }
	});

}

function editOrder(id,obj){
	if($.trim(obj.value)=='' || $.trim(obj.value)==editOrder[id]){
		obj.value = editOrder[id];
		return;
	}
	$.post(WST.U('admin/trades/editOrder'),{id:id,tradeSort:obj.value},function(data,textStatus){
	    var json = WST.toAdminJson(data);
	    if(json.status=='1'){
	    	editOrder[id] = $.trim(obj.value);
	        WST.msg(json.msg,{icon:1});
	    }else{
	        WST.msg(json.msg,{icon:2});
	    }
	});
}
