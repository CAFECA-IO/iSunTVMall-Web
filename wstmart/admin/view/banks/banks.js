var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_bank_name'), name:'bankName', width: 300,renderer:function(val,item,rowIndex){
                if(item['bankImg'] && item['bankImg']!=''){
                   return '<img src="'+WST.conf.RESOURCE_PATH+'/'+item['bankImg']+'" height="28px" />&nbsp;'+val;
                }
                return '<span style="margin-left:32px">'+val+'</span>';
            }},
            {title:WST.lang('label_bank_code'), name:'bankCode', width: 100},
            {title:WST.lang('is_show'), name:'isShow', width: 30,renderer: function(val,item,rowIndex){
              return '<input type="checkbox" '+((item['isShow']==1)?"checked":"")+' name="isShow2" lay-skin="switch" lay-filter="isShow2" data="'+item['bankId']+'" lay-text="'+WST.lang('is_show_val')+'">';

            }},
            {title:WST.lang('op'), name:'' ,width:170, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                if(WST.GRANT.YHGL_02)h += "<a  class='btn btn-blue' onclick='javascript:getForEdit("+item['bankId']+")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                if(WST.GRANT.YHGL_03)h += "<a  class='btn btn-red' onclick='javascript:toDel(" + item['bankId'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-89,indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/banks/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    mmg.on('loadSuccess',function(){
      layui.form.render('','gridForm');
        layui.form.on('switch(isShow2)', function(data){
            var id = $(this).attr("data");
            if(this.checked){
            toggleIsShow(0,id);
        }else{
            toggleIsShow(1,id);
        }
        });
    })
    loadGrid(p)
}
function toggleIsShow(t,v){
  if(!WST.GRANT.YHGL_02)return;
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
      $.post(WST.U('admin/banks/editiIsShow'),{id:v,isShow:t},function(data,textStatus){
        layer.close(loading);
        var json = WST.toAdminJson(data);
        if(json.status=='1'){
            WST.msg(json.msg,{icon:1});
            loadGrid(WST_CURR_PAGE);
        }else{
            WST.msg(json.msg,{icon:2});
        }
    });
}
function loadGrid(p){
    p=(p<=1)?1:p;
    mmg.load({page:p});
}
function toDel(id){
	var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/banks/del'),{id:id},function(data,textStatus){
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

function getForEdit(id){
	 var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
     $.post(WST.U('admin/banks/get'),{id:id},function(data,textStatus){
           layer.close(loading);
           var json = WST.toAdminJson(data);
           if(json.bankId){
           		WST.setValues(json);
              for(var key in WST.conf.sysLangs){
                  var val = json['langParams']?json['langParams'][WST.conf.sysLangs[key]['id']]['bankName']:'';
                  $('#langParams'+WST.conf.sysLangs[key]['id']+'bankName').val(val);
              }
              $('#isShow')[0].checked = (json.isShow==1);
              layui.form.render();
              $('#preview').empty();
              if(json.bankImg && json.bankImg!='')$('#preview').html('<img src="'+WST.conf.RESOURCE_PATH+'/'+json.bankImg+'" height="30"  />');
           		toEdit(json.bankId);
           }else{
           		WST.msg(json.msg,{icon:2});
           }
    });
}
function initUpload(){
  //文件上传
  WST.upload({
      pick:'#bankImgPicker',
      formData: {dir:'banks'},
      accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
      callback:function(f){
        var json = WST.toAdminJson(f);
        if(json.status==1){
          $('#uploadMsg').empty().hide();
          $('#bankImg').val(json.savePath+json.thumb);
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
var isInitUpload = false;
function resetBank(){
    $("#bankName").val("");
    $("#bankImg").val("");
    $("#preview").empty();
}
function toEdit(id){
	if(id==0){
      title = WST.lang('add');
      resetBank();
  }else{
      title = WST.lang('edit');
  }
  if(!isInitUpload){
     isInitUpload = true;
     initUpload();
  }
	var box = WST.open({title:title,type:1,content:$('#bankBox'),area: ['100%', '100%'],offset: 't',btnAlign: 'c',
		btn:[WST.lang('submit'),WST.lang('cancel')],end:function(){$('#bankBox').hide();},yes:function(){
		$('#bankForm').submit();
	},cancel:function () {
            resetBank();
        },btn2: function() {
            resetBank();
        },});
  var fields = {};
  var n = 0;
  for(var key in WST.conf.sysLangs){
      n = WST.conf.sysLangs[key]['id'];
      fields['langParams'+n+'bankName'] = {
              rule:"required;",
              msg:{required:WST.lang('require_bank_name')},
              tip:{required:WST.lang('require_bank_name')},
              ok:""
            }           
  }
	$('#bankForm').validator({
        fields: fields,
       valid: function(form){
		            var params = WST.getParams('.ipt');
                var n = 0;
                params['langParams'] = {};
                for(var key in WST.conf.sysLangs){
                   n = WST.conf.sysLangs[key]['id'];
                   params['langParams'][n] = {};
                   params['langParams'][n]['bankName'] = params['langParams'+n+'bankName'];
                }
	              params.bankId = id;
	              var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           		$.post(WST.U('admin/banks/'+((id==0)?"add":"edit")),params,function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(json.msg,{icon:1});
	           			    	$('#bankBox').hide();
	           			    	$('#bankForm')[0].reset();
	           			    	layer.close(box);
                              loadGrid(WST_CURR_PAGE);
	           			  }else{
	           			        WST.msg(json.msg,{icon:2});
	           			  }
	           		});

    	}

  });

}