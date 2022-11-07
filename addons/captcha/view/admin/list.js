var mmg1,isInitUpload = false;

//列表
function initGrid1(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang("captcha_explain"), name:'title', width: 500},
            {title:WST.lang("captcha_picture"), name:'imgPath', width: 30, renderer: function(val,rowdata,rowIndex){
                return '<img src="'+WST.conf.RESOURCE_PATH+'/'+val+'" style="width:50px;height:50px;">'
            }},
            {title:WST.lang("captcha_operation"), name:'' ,width:120, align:'center', renderer: function(val,rowdata,rowIndex){
                var h = "";
	            if(WST.GRANT.ADDON_CAPTCHA_02)h += "<a class='btn btn-red' href='javascript:getForEdit(" + rowdata['id'] + ",0)'><i class='fa fa-pencil'></i>编辑</a> "; 
                if(WST.GRANT.ADDON_CAPTCHA_03)h += "<a class='btn btn-red' href='javascript:del(" + rowdata['id'] + ",0)'><i class='fa fa-trash'></i>删除</a>"; 
	            return h;
	        }}
        ];
 
    mmg1 = $('.mmg1').mmGrid({height: h-90,indexCol: true,indexColWidth:50,  cols: cols,method:'POST',checkCol:true,multiSelect:true,
        url: WST.AU('captcha://admin/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg1').mmPaginator({})
        ]
    });
    loadGrid1(p);
}
function loadGrid1(p){
	var params = {};
	params.name = $('#name').val();
	p=(p<=1)?1:p;
	params.page=p;
	mmg1.load(params);
}


//编辑时段
function getForEdit(id){
    var loading = WST.msg(WST.lang("captcha_loading_tips"), {icon: 16,time:60000});
    $.post(WST.AU('captcha://admin/getById'),{id:id},function(data,textStatus){
        layer.close(loading);
        var json = WST.toAdminJson(data);
        if(json.data && json.data.id){
            WST.setValues(json.data);
            //显示原来的图片
            if(json.friendlinkIco!=''){
                $('#preview').html('<img src="'+WST.conf.RESOURCE_PATH+'/'+json.data.imgPath+'" height="30px" />');
            }else{
                $('#preview').empty();
            }
            toEdit(json.data.id);
        }else{
            WST.msg(json.msg,{icon:2});
        }
    });
}
function initUpload(){
  //文件上传
  WST.upload({
      pick:'#imgPathPicker',
      formData: {dir:'captcha'},
      accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
      callback:function(f){
        var json = WST.toAdminJson(f);
        if(json.status==1){
          $('#uploadMsg').empty().hide();
          $('#imgPath').val(json.savePath+json.thumb);
          $('#preview').html('<img src="'+WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb+'" height="30" />');
        }else{
            WST.msg(json.msg,{icon:2});
        }
    },
    progress:function(rate){
        $('#uploadMsg').show().html('已上传'+rate+"%");
    }
  });
}
function toEdit(id){
  if(!isInitUpload){
     isInitUpload = true;
     initUpload();
  }
    var title = WST.lang("captcha_edit_pic");
    var box = WST.open({title:title,type:1,content:$('#editBox'),area: ['660px', '360px'],btn: [WST.lang("captcha_confirm"),WST.lang("captcha_cancel")],
        yes:function(){
        $('#editForm').isValid(function(v){
            if(v){
                var params = WST.getParams('.ipt');
                params.id = id;
                var loading = WST.msg(WST.lang("captcha_submiting"), {icon: 16,time:60000});
                $.post(WST.AU('captcha://admin/edit'),params,function(data,textStatus){
                    layer.close(loading);
                    var json = WST.toAdminJson(data);
                    if(json.status=='1'){
                        WST.msg(WST.lang("captcha_operation_success"),{icon:1});
                        $('#editBox').hide();
                        $('#editForm')[0].reset();
                        //清空预览图
                        $('#preview').html('');
                        layer.close(box);
                        loadGrid1(WST_CURR_PAGE);
                    }else{
                        WST.msg(json.msg,{icon:2});
                    }
                });
            }
      });  
    },cancel:function(){
        $('#editBox').hide();
        $('#editForm')[0].reset();
        $('#preview').html('');
    },end:function(){
        $('#editBox').hide();
        $('#editForm')[0].reset();
        $('#preview').html('');
    }});
}

//删除
function del(id){
    var box = WST.confirm({content:WST.lang("captcha_confirm_del_inquiry"),yes:function(){
    var loading = WST.msg(WST.lang("captcha_submiting"), {icon: 16,time:60000});
    $.post(WST.AU('captcha://admin/del'),{id:id},function(data,textStatus){
            layer.close(loading);
            var json = WST.toAdminJson(data);
            if(json.status=='1'){
                WST.msg(json.msg,{icon:1});
                layer.close(box);
                loadGrid1(WST_CURR_PAGE);
            }else{
                WST.msg(json.msg,{icon:2});
            }
        });
    }});
}
function toBatchDel(){
    var rows = mmg1.selectedRows();
    if(rows.length==0){
         WST.msg(WST.lang("captcha_select_need_del_pic"),{icon:2});
         return;
    }
    var ids = [];
    for(var i=0;i<rows.length;i++){
       ids.push(rows[i]['id']); 
    }
    var box = WST.confirm({content:WST.lang("captcha_confirm_del_inquiry"),yes:function(){
        var loading = WST.msg(WST.lang("captcha_submiting"), {icon: 16,time:60000});
        $.post(WST.AU('captcha://admin/batchDel'),{ids:ids.join(',')},function(data,textStatus){
                  layer.close(loading);
                  var json = WST.toAdminJson(data);
                  if(json.status=='1'){
                        WST.msg(json.msg,{icon:1});
                        loadGrid1(WST_CURR_PAGE);
                  }else{
                        WST.msg(json.msg,{icon:2});
                  }
            });
    }});
}

var uploading = null,images = [],imgNum = 0;
$(function(){
    var uploader = WST.upload({
        server:WST.U('admin/index/uploadPic'),pick:'#uploadImg',
        formData: {dir:'captcha'},
        callback:function(f,file){
            uploader.removeFile(file);
            var json = WST.toAdminJson(f);
            if(json.status==1){
                uploader.refresh();
                images.push(json);
            }else{
                WST.msg(WST.lang("captcha_upload_fail_num_tips",[imgNum]), {icon: 5});
            }
        }
    });
    uploader.on('uploadStart',function(file) {
        imgNum++;
        uploading = WST.msg(WST.lang("captcha_upload_num_tips",[imgNum]));
    });
    uploader.on('uploadFinished',function() {
        layer.close(uploading);
        var html = [];
        for (var i = 0; i <images.length; i++) {
            html.push('<div class="imgItem"><div><img style="width:100px;height:100px" src="'+WST.conf.RESOURCE_PATH+'/'+images[i]['savePath']+images[i]['name']+'"/></div><div><input type="text" maxlength="4" ni="'+i+'" nv="'+images[i]['savePath']+images[i]['name']+'" class="m-ipt" style="width:100px"></div></div>');
        }
        html.push('<div style="clear:both;"></div>');
        images.length = 0;
        imgNum = 0;
        var imgBox = WST.open({type: 1,title:WST.lang("captcha_picture_setting")+"<font color='red'>"+WST.lang("captcha_input_pic_title_tips")+"</font>",shade: [0.6, '#000'], border: [0],
            content: '<div class="imgBox">'+html.join('')+'</div>',area: ['850px', '550px'],btn: [WST.lang("captcha_confirm"),WST.lang("captcha_cancel")],
            yes:function(index, layero){
                var param = {};
                var no = 0,isBreak = false;
                $('.m-ipt').each(function(){
                    param['title_'+no] = $.trim($(this).val());
                    if(!param['title_'+no] || param['title_'+no] ==''){
                        WST.msg(WST.lang("captcha_input_pic_title"),{icon:2,time:2000});
                        isBreak = true;
                        return;
                    }
                    param['imgPath_'+no] = $(this).attr('nv');
                    no++;
                });
                if(isBreak)return;
                param.no = no;
                //提交
                var loading = WST.msg(WST.lang("captcha_submiting"), {icon: 16,time:60000});
                $.post(WST.AU('captcha://admin/add'),param,function(data,textStatus){
                        var json = WST.toAdminJson(data);
                        if(json.status=='1'){
                            WST.msg(json.msg,{icon:1});
                            layer.close(loading);
                            layer.close(imgBox);
                            loadGrid1(0);
                        }else{
                            WST.msg(json.msg,{icon:2});
                        }
                    });
            }});
    });
});