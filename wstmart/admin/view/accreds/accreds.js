var mmg;
function initGrid(p){
  var h = WST.pageHeight();
  var cols = [
            {title:WST.lang('upload_img2'), name:'accredImg' ,width:50, align:'center', renderer: function(val,item,rowIndex){
               return '<img src="'+WST.conf.RESOURCE_PATH+'/'+item['accredImg']+'" height="28px" />';
            }},
            {title:WST.lang('label_accreds_name'), name:'accredName' ,width:125},
            {title:WST.lang('label_accreds_time'), name:'createTime' ,width:75,},
            {title:WST.lang('op'), name:'' ,width:60, align:'center', renderer: function(val,item,rowIndex){
                var h="";
	            if(WST.GRANT.RZGL_02)h += "<a  class='btn btn-blue' href='javascript:getForEdit(" + item['accredId'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
	            if(WST.GRANT.RZGL_03)h += "<a  class='btn btn-red' href='javascript:toDel(" + item['accredId'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
	            return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-89,indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/Accreds/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function loadGrid(p){
    p=(p<=1)?1:p;
    mmg.load({page:p});
}

function getForEdit(id){
	 var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
     $.post(WST.U('admin/accreds/get'),{id:id},function(data,textStatus){
           layer.close(loading);
           var json = WST.toAdminJson(data);
           if(json.accredId){
           		WST.setValues(json);
           		for(var key in WST.conf.sysLangs){
					var val = json['langParams']?json['langParams'][WST.conf.sysLangs[key]['id']]['accredName']:'';
					$('#langParams'+WST.conf.sysLangs[key]['id']+'accredName').val(val);
				}
           		//显示原来的图片
           		$('#preview').html('<img src="'+WST.conf.RESOURCE_PATH+'/'+json.accredImg+'" height="30px" />');
           		$('#isImg').val('ok');
           		toEdit(json.accredId);
           }else{
           		WST.msg(json.msg,{icon:2});
           }
    });
}

function toEdit(id){
	if(!isInitUpload){
		initUpload();
	}
	var title =(id==0)?WST.lang('add'):WST.lang('edit');
	var box = WST.open({title:title,type:1,content:$('#accredBox'),area: ['100%', '100%'],offset: 't',btnAlign: 'c',btn: [WST.lang('submit'),WST.lang('cancel')],yes:function(){
			$('#accredForm').submit();
	},cancel:function(){
		//重置表单
		$('#accredForm')[0].reset();
		//清空预览图
		$('#preview').html('');
		$('#accredImg').val('');

	},end:function(){
		$('#accredBox').hide();
		//重置表单
		$('#accredForm')[0].reset();
		//清空预览图
		$('#preview').html('');
		$('#accredImg').val('');

	}});
	var fields = {
            accredImg:  {
            	rule:"required;",
            	msg:{required:WST.lang('require_upload_img2')},
            	tip:WST.lang('require_upload_img2'),
            	ok:"",
            }
    }
    var n = 0;
    for(var key in WST.conf.sysLangs){
    	n = WST.conf.sysLangs[key]['id'];
        fields['langParams'+n+'accredName'] = {
            	rule:"required;",
            	msg:{required:WST.lang('require_accreds_name')},
            	tip:WST.lang('require_accreds_name'),
            	ok:"",
            }
    }
	$('#accredForm').validator({
        fields: fields,
       valid: function(form){
		        var params = WST.getParams('.ipt');
		        var n = 0;
			    params['langParams'] = {};
			    for(var key in WST.conf.sysLangs){
					n = WST.conf.sysLangs[key]['id'];
					params['langParams'][n] = {};
					params['langParams'][n]['accredName'] = params['langParams'+n+'accredName'];
				}
		        params.accredId = id;
		        var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		   		$.post(WST.U('admin/accreds/'+((id==0)?"add":"edit")),params,function(data,textStatus){
		   			  layer.close(loading);
		   			  var json = WST.toAdminJson(data);
		   			  if(json.status=='1'){
		   			    	WST.msg(json.msg,{icon:1});
		   			    	$('#accredForm')[0].reset();
		   			    	//清空预览图
		   			    	$('#preview').html('');
		   			    	//清空图片隐藏域
		   			    	$('#accredImg').val('');
		   			    	layer.close(box);
                          loadGrid(WST_CURR_PAGE)
		   			  }else{
		   			        WST.msg(json.msg,{icon:2});
		   			  }
		   		});

    	}

  });
}
var isInitUpload = false;
function initUpload(){
	isInitUpload = true;
	//文件上传
	WST.upload({
	    pick:'#adFilePicker',
	    formData: {dir:'accreds'},
	    accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
	    callback:function(f){
	      var json = WST.toAdminJson(f);
	      if(json.status==1){
	        $('#uploadMsg').empty().hide();
	        //将上传的图片路径赋给全局变量
		    $('#accredImg').val(json.savePath+json.thumb);
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
	           	$.post(WST.U('admin/accreds/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(json.msg,{icon:1});
	           			    	layer.close(box);
                              loadGrid(WST_CURR_PAGE)
	           			  }else{
	           			    	WST.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}






		