var mmg,isInitUpload = false;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('upload_img'), name:'btnImg', width: 50,renderer: function(val,item,rowIndex){
                return '<img src="'+WST.conf.RESOURCE_PATH+'/'+item['btnImg']+'" height="60px" style="margin-top:5px;" />';
            }},
            {title:WST.lang('label_btn_name'), name:'btnName' ,width:60},
            {title:WST.lang('label_btn_url'), name:'btnUrl' ,width:300},
            {title:WST.lang('label_btn_type'), name:'btnSrc' ,width:20,renderer: function(val,item,rowIndex){
                var rs = WST.lang('product_mobile');
                switch(val){
                    case 1:
                      rs=WST.lang('product_wechat');
                    break;
                    case 2:
                      rs=WST.lang('product_weapp');
                    break;
                    case 3:
                      rs=WST.lang('product_app');
                    break;
                }
                return rs;
            }},
            {title:WST.lang('label_btn_addon'), name:'addonsName' ,width:20},
            {title:WST.lang('sort'), name:'btnSort' ,width:10},
            {title:WST.lang('op'), name:'' ,width:100, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
    			      if(WST.GRANT.ANGL_02)h += "<a  class='btn btn-blue' onclick='javascript:getForEdit(" + item['id'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
    			      if(WST.GRANT.ANGL_03)h += "<a  class='btn btn-red' onclick='javascript:toDel(" + item['id'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
            ];

    mmg = $('.mmg').mmGrid({height: (h-170),indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/mobilebtns/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    $('#headTip').WSTTips({width:200,height:35,callback:function(v){
       var diff = v?155:128;
       mmg.resize({height:h-diff})
    }});
    $('#btnSrc').change(function(v){
        if($(this).val()==3){// App端
           if($('#appBtns').length==0){
               var options = ['<option value="0">'+WST.lang('reuqire_btm_msg')+'</option>'];
               for(var i in appScreens){
                  var _obj = appScreens[i];
                  options.push('<option value="'+_obj.explain+'">'+_obj.screenName+'</option>');
               }
               var select = '<select id="appBtns" >'+options.join('')+'</select>'
               var html= '<tr><th>'+WST.lang('reuqire_btm_app')+'：</th><td>'+select+'</td></tr><tr id="screenExplain"><th></th><td></td></tr>';
               $('#mbBtnType').after(html);
               $('#appBtns').change(function(v){
                  var _explain = $(this).val()==0?'':'<span style="color:red">'+WST.lang('reuqire_btm_app_eg')+'：</span>'+$(this).val();
                  $('#screenExplain td').html(_explain);
               })
           }
        }else{
          $('#appBtns').parent().parent().remove();
          $('#screenExplain').remove();
        }
    })
    loadGrid(p);
}
// app按钮
var appScreens = []
$(function(){
  $.post(WST.U('admin/appscreens/pagequery'),{},function(responData){
    appScreens = (responData instanceof Object)?responData:[];
  });
});

function loadGrid(p){
    p=(p<=1)?1:p;
	var query = WST.getParams('.query');
    query.page = p;
	mmg.load(query);
}
function getForEdit(id){
	 var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
     $.post(WST.U('admin/mobileBtns/get'),{id:id},function(data,textStatus){
           layer.close(loading);
           var json = WST.toAdminJson(data);
           if(json.id){
           		WST.setValues(json);
               if(json.langs){
                   for(var key in json.langs){
                       WST.setValue('langParams'+key+'btnName',json.langs[key]['btnName']);
                   }
               }
           		//显示原来的图片
           		$('#preview').html('<img src="'+WST.conf.RESOURCE_PATH+'/'+json.btnImg+'" height="30px;"/>');
           		$('#isImg').val('ok');
           		toEdit(json.id);
           }else{
           		WST.msg(json.msg,{icon:2});
           }
    });
}

function toEdit(id){
  if(!isInitUpload){
    initUpload();
    isInitUpload = true;
  }
	var title =(id==0)?WST.lang('add'):WST.lang('edit');
	var box = WST.open({title:title,type:1,content:$('#mbtnBox'),area: ['100%', '100%'],offset: 't',btn: [WST.lang('confirm'),WST.lang('cancel')],btnAlign: 'c',yes:function(){
			$('#mbtnForm').submit();
	},cancel:function(){
		//重置表单
		$('#mbtnForm')[0].reset();
		//清空预览图
		$('#preview').html('');
		$('#btnImg').val('');

	},end:function(){
		//重置表单
		$('#mbtnForm')[0].reset();
		//清空预览图
		$('#preview').html('');
		$('#btnImg').val('');
    $('#mbtnBox').hide();
    // 隐藏app端说明
    $('#appBtns').parent().parent().remove();
     $('#screenExplain').remove();

	}});
    var fields = {};
    var n = 0;
    for(var i in WST.conf.sysLangs){
        n = WST.conf.sysLangs[i]['id'];
        fields['langParams'+n+'btnName'] = {
            rule:"required;",
            msg:{required:WST.lang('require_btn_name')},
            tip:WST.lang('require_btn_name'),
            ok:"",
        }
    }
    fields['btnUrl'] = {
        rule:"required;",
        msg:{required:WST.lang('require_btn_url')},
        tip:WST.lang('require_btn_url'),
        ok:"",
    };
    fields['btnImg'] = {
        rule:"required;",
        msg:{required:WST.lang('require_upload_img')},
        tip:WST.lang('require_upload_img'),
        ok:"",
    };
	$('#mbtnForm').validator({
        fields: fields,
       valid: function(form){
		       var params = WST.getParams('.ipt');
               var n = 0;
               params['langParams'] = {};
               for(var i in WST.conf.sysLangs){
                   n = WST.conf.sysLangs[i]['id'];
                   params['langParams'][n] = {};
                   params['langParams'][n]['btnName'] = params['langParams'+n+'btnName'];
               }
		       params.id = id;
		        var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		   		$.post(WST.U('admin/mobileBtns/'+((id==0)?"add":"edit")),params,function(data,textStatus){
		   			  layer.close(loading);
		   			  var json = WST.toAdminJson(data);
		   			  if(json.status=='1'){
		   			    	WST.msg(json.msg,{icon:1});
		   			    	$('#mbtnForm')[0].reset();
		   			    	//清空预览图
		   			    	$('#preview').html('');
		   			    	//清空图片隐藏域
		   			    	$('#btnImg').val('');
		   			    	layer.close(box);
		   		            loadGrid(WST_CURR_PAGE);
		   			  }else{
		   			        WST.msg(json.msg,{icon:2});
		   			  }
		   		});

    	}

  });
}
function initUpload(){
  WST.upload({
    pick:'#adFilePicker',
    formData: {dir:'sysconfigs'},
    accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
    callback:function(f){
      var json = WST.toAdminJson(f);
      if(json.status==1){
        $('#uploadMsg').empty().hide();
        //将上传的图片路径赋给全局变量
      $('#btnImg').val(json.savePath+json.thumb);
      $('#preview').html('<img src="'+WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb+'" height="30" />');
      }else{
        WST.msg(json.msg,{icon:2});
      }
  },
  progress:function(rate){
      $('#uploadMsg').show().html(WST.lang('upload_rate')+rate+'%');
  }
});
}
function toDel(id){
	var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/mobileBtns/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(json.msg,{icon:1});
	           			    	layer.close(box);
	           		            loadGrid(WST_CURR_PAGE);
	           			  }else{
	           			    	WST.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}





