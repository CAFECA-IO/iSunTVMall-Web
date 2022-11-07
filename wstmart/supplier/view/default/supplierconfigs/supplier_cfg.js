function save(){
/* 表单验证 */
$('#supplierCfg').isValid(function(v){
    if(v){
            var params = WST.getParams('.ipt');
            // 图片路径
            var supplierAds = [];
            $('.j-gallery-img').each(function(){
              supplierAds.push($(this).attr('v'));
            });
            params.supplierAds = supplierAds.join(',');
            // 图片轮播广告路径
            var supplierAdsUrl = [];
            $('.cfg-img-url').each(function(){
              supplierAdsUrl.push($(this).val());
            });
            params.supplierAdsUrl = supplierAdsUrl.join(',');

            var loading = WST.load({msg:WST.lang('submitting_data')});

            $.post(WST.U('supplier/supplierconfigs/editSupplierCfg'),params,function(data,textStatus){
              layer.close(loading);
              var json = WST.toJson(data);
              if(json.status=='1'){
                  WST.msg(WST.lang('op_ok'),{icon:1});
              }else{
                    WST.msg(json.msg,{icon:2});
              }
            });

      }

    });
}





$(function(){
  //供货商顶部广告图上传
  WST.upload({
      pick:'#supplierBannerPicker',
      formData: {dir:'supplierconfigs'},
      accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
      callback:function(f){
        var json = WST.toJson(f);
        if(json.status==1){
          $('#uploadMsg').empty().hide();
          var supplierbanner = json.savePath+json.thumb; //保存到数据库的路径
          $('#supplierBanner').val(supplierbanner);
          $('#supplierBannerPreview').parent().show();
          $('.del-banner').show();
          $('#supplierBannerPreview').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
        }else{
            WST.msg(json.msg,{icon:2});
        }
    },
    progress:function(rate){
        $('#uploadMsg').show().html(WST.lang('upload_rate')+rate+"%");
    }
  });


/********** 轮播广告图片上传 **********/
var uploader = batchUpload({uploadPicker:'#batchUpload',uploadServer:WST.U('supplier/index/uploadPic'),formData:{dir:'supplierconfigs'},uploadSuccess:function(file,response){
        var json = WST.toJson(response);
        if(json.status==1){
          $li = $('#'+file.id);
          $li.append('<input type="hidden" class="j-gallery-img" iv="'+json.savePath + json.thumb+'" v="' +json.savePath + json.name+'"/>');
          var delBtn = $('<span class="btn-del">'+WST.lang('del')+'</span>');
          $li.append(delBtn);
          $li.append('<input class="cfg-img-url" type="text" value="" style="width:170px;" placeholder="'+WST.lang('label_supp_configs_ads_url_plo')+'">' );
          $li.css('height','213px');
          $li.find('.success').remove();
                  delBtn.on('click',function(){
                      delBatchUploadImg($(this),function(){
                        if($('.filelist').find('li').length==0){
                          $("#batchUpload").find('.placeholder').removeClass( 'element-invisible' );
                          $('.filelist').parent().removeClass('filled');
                          $('.filelist').hide();
                          $("#batchUpload").find('.statusBar').addClass( 'element-invisible' );
                        }
                        uploader.removeFile(file);
                        uploader.refresh();
                    });
            });
                  $('.filelist li').css('border','1px solid #eee');
        }else{
          WST.msg(json.msg,{icon:2});
        }
      }});
// 删除广告图片
$('.btn-del').click(function(){
      delBatchUploadImg($(this),function(){
        if($('.filelist').find('li').length==0){
          $("#batchUpload").find('.placeholder').removeClass( 'element-invisible' );
          $('.filelist').parent().removeClass('filled');
          $('.filelist').hide();
          $("#batchUpload").find('.statusBar').addClass( 'element-invisible' );
        }
        $(this).parent().remove();
        uploader.refresh();
      });
    })

function delBatchUploadImg(obj,fn){
  var c = WST.confirm({content:WST.lang('delete_ads'),yes:function(){
    $(obj).parent().remove("li");
    layer.close(c);
    fn();
  }});
}


});
