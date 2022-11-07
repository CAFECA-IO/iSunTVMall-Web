jQuery.noConflict();
$(function(){

})


/* 列表 */

var currPage = totalPage = 0;
var loading = false;

function toDetail(id){
  location.href = WST.U('mobile/informs/detail',{id:id});
}

function refreshList(){
  $('#currPage').val(0);
  $('#totalPage').val(100);
  $('#informsListContainer').html("");
  getList();
}

// 获取举报处理状态
function getInformStatus(){
  $.get(WST.U('mobile/informs/informStatus'), function(data){
    var json = WST.toJson(data.data);
    var html = ["<option value='-1'>"+WST.lang('please_select')+"</option>"];
    if(json && json.length>0){
      json.map(function(x){
        html.push("<option value='"+x.dataVal+"'>"+x.dataName+"</option>")
      });
      $('#informStatus').html(html.join(""));
    }
});
}
// 获取列表
function getList(){
  $('#Load').show();
    loading = true;
    var param = {
      informStatus:$('#informStatus').val(),
    };
    param.pagesize = 10;
    param.page = Number( $('#currPage').val() ) + 1;
    $.post(WST.U('mobile/informs/pageQuery'), param, function(data){
        var json = WST.toJson(data.data);
        var html = '';
        if(json && json.data && json.data.length>0){
          var gettpl = document.getElementById('informsList').innerHTML;
          laytpl(gettpl).render(json.data, function(html){
            $('#informsListContainer').append(html);
          });

          $('#currPage').val(json.current_page);
          $('#totalPage').val(json.last_page);
        }else{
          html += '<div class="wst-prompt-icon"><img src="'+ window.conf.MOBILE +'/img/no_data.png"></div>';
          html += '<div class="wst-prompt-info">';
          html += '<p>'+WST.lang('no_data')+'</p>';
          html += '</div>';
          $('#informsListContainer').html(html);
        }
        loading = false;
        $('#Load').hide();
        echo.init();//图片懒加载
    });
}










// 举报须知
function toTips(){
  location.href = WST.U("mobile/informs/tips");
}

// 查看大图
function gViewImg(index, obj) {
  var pswpElement = document.querySelectorAll('.pswp')[0];
  var gallery = $(obj).parent().data("gallery");
  if (gallery != '') {
    gallery = gallery.split(',').map(function (imgUrl, i) {
      imgUrl = WST.conf.RESOURCE_PATH + "/" + imgUrl;
      var _obj = { src: imgUrl, w: 0, h: 0 };
      return _obj;
    })
  }
  // build items array
  if (!gallery || gallery.length == 0) return;
  // define options (if needed)
  var options = {
    index: index
  };
  // Initializes and opens PhotoSwipe
  var gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, gallery, options);
  gallery.init();
  gallery.listen('imageLoadComplete', function (index, item) {
    if (item.h < 1 || item.w < 1) {
      var img = new Image();
      img.onload = function () {
        item.w = img.width;
        item.h = img.height;
        gallery.invalidateCurrItems()
        gallery.updateSize(true);
      }
      img.src = item.src
    }


  });

}



// 举报页
function getInforms(){
  var params = {
    id:$('#goodsId').val(),
  };
  $.get(WST.U('mobile/informs/inform', params), function(data){
    var json = WST.toJson(data);
    if(data.status==1){
      var _data = json.data;
      $('.goodsName').html(_data.goodsName);
      $('#j-shopName').html(_data.shopName);
      $('.goodsImg').attr("src", WST.conf.RESOURCE_PATH+'/'+_data.goodsImg);
      // 选项
      var code = [];
      var checked = 'checked';
      for(var i in _data.types){
        var _obj = _data.types[i];
        code.push(
          '<View class="row ac">\
              <div class="ui-checkbox">\
                  <input class="active ipt" type="radio" name="informType" '+checked+' value="'+_obj.dataVal+'">\
              </div>\
              <Text class="f14 c333">'+_obj.dataName+'</Text>\
          </View>'
        );
        checked = "";
      }
      $('#renderType').html(code.join(""));
    }else{
      WST.msg(json.msg,'info');
    }
  })
  uploadInit();
}

function commit(){
  var param = WST.getParams('.ipt');
  var imgs = [];
  //  是否有上传附件
  $('.imgSrc').each(function(k,v){
    imgs.push($(this).attr('v'));
  })
  imgs = imgs.join(',');
  if(imgs!='')param.informAnnex = imgs;
  param.goodsId = $('#goodsId').val();
  WST.load('正在提交，请稍后...');
  $.post(WST.U('mobile/informs/saveInform'),param,function(data){
    WST.noload();
    var json = WST.toJson(data);
    if(data.status==1){
      WST.msg(json.msg,'success');
      setTimeout(function(){location.href=WST.U('mobile/informs/list')},1000);
    }else{
      WST.msg(json.msg,'info');
    }
  });

}
/*************** 上传图片 *****************/
function uploadInit(){
   var uploader =WST.upload({
        pick:'#filePicker',
        formData: {dir:'informsImg',isThumb:1},
        fileNumLimit:5,
        accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
        callback:function(f,file){
          var json = WST.toJson(f);
          if(json.status==1){
          var tdiv = $("<div style='position: relative'>"+
                       "<img class='imgSrc' src='"+WST.conf.RESOURCE_PATH+"/"+json.savePath+json.thumb+"' v='"+json.savePath+json.name+"'></div>");
          var btn = $('<div class="del-btn"><span class="upload-icon-delete"></span></div>');
          tdiv.append(btn);
          $('#filePicker').before(tdiv);
          btn.on('click','span',function(){
            uploader.removeFile(file);
            $(this).parent().parent().remove();
            uploader.refresh();
          });
          }else{
            WST.msg(json.msg,{icon:2});
          }
      },
      progress:function(rate){
          $('#uploadMsg').show().html('已上传'+rate+"%");
      }
    });
}
/*********************** 举报类型 ****************************/
//弹框
function dataShow(){
  jQuery('#cover').attr("onclick","javascript:dataHide(0);").show();
  jQuery('#frame').animate({"bottom": 0}, 500);
}
function dataHide(type){
  if(type==1){
    var flag=false,chk;
    $('.active').each(function(k,v){
      if($(this).prop('checked')){
        flag = true;
        $('#informStatus').val($(this).val());
        chk = $(this).parent().siblings().html();
      }
    });
    if(!flag){
      WST.msg(WST.lang('inform_select_type_tip'));
      return;
    }
    $('#informText').html(chk);
    refreshList();
  }
  jQuery('#frame').animate({'bottom': '-100%'}, 500);
  jQuery('#cover').hide();
}
