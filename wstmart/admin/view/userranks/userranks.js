var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_user_rank_img'), name:'userrankImg', width: 30,renderer:function(val,item,rowIndex){
            return '<img src="'+WST.conf.RESOURCE_PATH+'/'+item['userrankImg']+'" height="28px" />';
          }},
            {title:WST.lang('label_user_rank_name'), name:'rankName' ,width:100},
            {title:WST.lang('label_user_rank_start_score'), name:'startScore' ,width:100},
            {title:WST.lang('label_user_rank_end_score'), name:'endScore' ,width:60},
            {title:WST.lang('op'), name:'' ,width:180, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                if(WST.GRANT.HYDJ_02)h += "<a  class='btn btn-blue' onclick='javascript:toEdit("+item['rankId']+")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                if(WST.GRANT.HYDJ_03)h += "<a  class='btn btn-red' onclick='javascript:toDel(" + item['rankId'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
            ];

    mmg = $('.mmg').mmGrid({height: h-89,indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/userranks/pageQuery'), fullWidthRows: true, autoLoad: false,
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
function toEdit(id){
    location.href = WST.U('admin/userranks/toEdit','id='+id+'&p='+WST_CURR_PAGE);
}
function toDel(id){
	var box = WST.confirm({content:WST.lang("del_tips"),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/userranks/del'),{id:id},function(data,textStatus){
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

function editInit(p){
  var fields = {
              userrankImg: {
                rule:"required",
                msg:{required:WST.lang("require_user_rank_img")},
                tip:WST.lang("require_user_rank_img"),
                ok:""
            }
        }
  var n = 0;
  for(var key in WST.conf.sysLangs){
      n = WST.conf.sysLangs[key]['id'];
      fields['langParams'+n+'rankName'] = {
                                            rule:"required",
                                            msg:{required:WST.lang("require_user_rank_name")},
                                            tip:WST.lang("require_user_rank_name"),
                                            ok:"",
                                          }
  }
  /* 表单验证 */
  $('#userRankForm').validator({
        fields:fields,
        valid: function(form){
            var params = WST.getParams('.ipt');
            var n = 0;
            params['langParams'] = {};
            for(var key in WST.conf.sysLangs){
                n = WST.conf.sysLangs[key]['id'];
                params['langParams'][n] = {};
                params['langParams'][n]['rankName'] = params['langParams'+n+'rankName'];
            }
            var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
            $.post(WST.U('admin/userranks/'+((params.rankId>0)?"edit":"add")),params,function(data,textStatus){
              layer.close(loading);
              var json = WST.toAdminJson(data);
              if(json.status=='1'){
                  WST.msg(WST.lang('op_ok'),{icon:1});
                  location.href=WST.U('Admin/userranks/index',"p="+p);
              }else{
                    WST.msg(json.msg,{icon:2});
              }
        });
      }
  });

//文件上传
WST.upload({
    pick:'#userranksPicker',
    formData: {dir:'userranks'},
    accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
    callback:function(f){
      var json = WST.toAdminJson(f);
      if(json.status==1){
      $('#uploadMsg').empty().hide();
      //保存上传的图片路径
      $('#userrankImg').val(json.savePath+json.thumb);
      $('#preview').html('<img src="'+WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb+'" height="30" />');
      }else{
        WST.msg(json.msg,{icon:2});
      }
  },
  progress:function(rate){
      $('#uploadMsg').show().html(WST.lang('upload_rate')+rate+"%");
  }
});


};





