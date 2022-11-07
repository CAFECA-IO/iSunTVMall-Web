var mmg,isInitUpload = false;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_friendlink_name'), name:'friendlinkName', width: 80},
            {title:WST.lang('label_friendlink_url'), name:'friendlinkUrl' ,width:100},
            {title:WST.lang('upload_img2'), name:'friendlinkIco' ,width:30,renderer:function(val,item,rowIndex){
              if(item['friendlinkIco']){
                return '<img src="'+WST.conf.RESOURCE_PATH+'/'+item['friendlinkIco']+'" height="28px" />';
              }else{
                return "";
              }
            }},
            {title:WST.lang('op'), name:'' ,width:80, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                if(WST.GRANT.YQGL_02)h += "<a  class='btn btn-blue' onclick='javascript:getForEdit("+item['friendlinkId']+")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                if(WST.GRANT.YQGL_03)h += "<a  class='btn btn-red' onclick='javascript:toDel(" + item['friendlinkId'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-89,indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/Friendlinks/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadQuery(p);
}
function loadQuery(p){
    p=(p<=1)?1:p;
    mmg.load({page:p});
}
function initUpload(){
  //文件上传
  WST.upload({
      pick:'#adFilePicker',
      formData: {dir:'friendlinks'},
      accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
      callback:function(f){
        var json = WST.toAdminJson(f);
        if(json.status==1){
          $('#uploadMsg').empty().hide();
          $('#friendlinkIco').val(json.savePath+json.thumb);
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
function toDel(id){
	var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/friendlinks/del'),{id:id},function(data,textStatus){
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

function getForEdit(id){
	 var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
     $.post(WST.U('admin/friendlinks/get'),{id:id},function(data,textStatus){
           layer.close(loading);
           var json = WST.toAdminJson(data);
           if(json.friendlinkId){
           		WST.setValues(json);
           		//显示原来的图片
           		if(json.friendlinkIco!=''){
           			$('#preview').html('<img src="'+WST.conf.RESOURCE_PATH+'/'+json.friendlinkIco+'" height="30px" />');
           		}else{
           			$('#preview').empty();
           		}
           		toEdit(json.friendlinkId);
           }else{
           		WST.msg(json.msg,{icon:2});
           }
    });
}

function toEdit(id){
  if(!isInitUpload){
     isInitUpload = true;
     initUpload();
  }
	var title =(id==0)?WST.lang('add'):WST.lang('edit');
	var box = WST.open({title:title,type:1,content:$('#friendlinkBox'),area: ['700px', '360px'],btn: [WST.lang('confirm'),WST.lang('cancel')],
		yes:function(){
			$('#friendlinkForm').submit();
	},cancel:function(){
    $('#friendlinkBox').hide();
		//重置表单
		$('#friendlinkForm')[0].reset();
		//清空预览图
		$('#preview').html('');
		//清空图片隐藏域
		$('#friendlinkIco').val('');
	},end:function(){
    $('#friendlinkBox').hide();
		//重置表单
		$('#friendlinkForm')[0].reset();
		//清空预览图
		$('#preview').html('');
		//清空图片隐藏域
		$('#friendlinkIco').val('');

	}});
	$('#friendlinkForm').validator({
        fields: {
            friendlinkName: {
            	rule:"required;",
            	msg:{required:WST.lang('require_friendlink_name')},
            	tip:WST.lang('require_friendlink_name'),
            	ok:"",
            },
            friendlinkUrl: {
	            rule: "required;",
	            msg: {required: WST.lang('require_friendlink_url')},
	            tip: WST.lang('require_friendlink_url'),
	            ok: "",
        	}
        },
       valid: function(form){
		        var params = WST.getParams('.ipt');
		        params.friendlinkId = id;
		        var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		   		$.post(WST.U('admin/friendlinks/'+((id==0)?"add":"edit")),params,function(data,textStatus){
		   			  layer.close(loading);
		   			  var json = WST.toAdminJson(data);
		   			  if(json.status=='1'){
		   			    	WST.msg(WST.lang('op_ok'),{icon:1});
                  $('#friendlinkBox').hide();
		   			    	$('#friendlinkForm')[0].reset();
		   			    	//清空预览图
		   			    	$('#preview').html('');
		   			    	//清空图片隐藏域
		   			    	$('#friendlinkIco').val('');
		   			    	layer.close(box);
                          loadQuery(WST_CURR_PAGE);
		   			  }else{
		   			        WST.msg(json.msg,{icon:2});
		   			  }
		   		});

    	}

  });
}
