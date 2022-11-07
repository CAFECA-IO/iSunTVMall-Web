jQuery.noConflict();
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
                flag = true
                $('#consultType').val($(this).val());
                chk = $(this).parent().siblings().html();
            }
        });
        if(!flag){
            WST.msg(WST.lang('select_consult_type'));
            return;
        }
        $('#consultText').html(chk);
    }
    jQuery('#frame').animate({'bottom': '-100%'}, 500);
    jQuery('#cover').hide();
}
// 获取商品咨询
function getgoodsConsultList(){
  $('#Load').show();
    loading = true;
    var param = {};
    param.goodsId = $('#goodsId').val();
    param.pagesize = 10;
    param.page = Number( $('#currPage').val() ) + 1;
    $.post(WST.U('mobile/goodsconsult/listQuery'), param, function(data){
        var json = WST.toJson(data);
        json = json.data;
        var html = '';
        if(json && json.data && json.data.length>0){
           var gettpl = document.getElementById('gcList').innerHTML;
          laytpl(gettpl).render(json.data, function(html){
            $('#_gcList').append(html);
          });
          $('#currPage').val(data.data.current_page);
          $('#totalPage').val(data.data.last_page);
          $('.bottom-btn-box').show();
        }else{
            html += '<div class="wst-prompt-icon"><img src="'+ window.conf.MOBILE +'/img/nothing-message.png"></div>';
            html += '<div class="wst-prompt-info">';
            html += '<p>'.WST.lang('no_data').'</p>';
            html += '<button class="ui-btn-s consult-btn" onclick="javascript:consult();">'+WST.lang('iwant_to_consult')+'</button>';
            html += '</div>';
            $('#_gcList').html(html);
            $('.bottom-btn-box').hide();
        }
        loading = false;
        $('#Load').hide();
        echo.init();//图片懒加载
    });
}
function consultListInit(){
  var currPage = totalPage = 0;
  var loading = false;
  $(document).ready(function(){
      getgoodsConsultList();
      $("#frame").css('top',0);
      $("#frame").css('right','-100%');
      $(window).scroll(function(){  
          if (loading) return;
          if ((5 + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
              currPage = Number( $('#currPage').val() );
              totalPage = Number( $('#totalPage').val() );
              if( totalPage > 0 && currPage < totalPage ){
                getgoodsConsultList();
              }
          }
      });
  });
};
$(function(){WST.initFooter()});
/* 发布咨询 */
function consult(){
  var goodsId = $('#goodsId').val();
  location.href=WST.U('mobile/goodsconsult/consult',{goodsId:goodsId});
}
// 提交商品咨询
function consultCommit(){
  var params={};
  params.goodsId = $('#goodsId').val();
  params.consultType = $('#consultType').val();
  if(params.consultType<=0){
    WST.msg(WST.lang('select_consult_type'),'info');
    return;
  }
  params.consultContent = $('#consultContent').val();
  if(params.consultContent == ''){
    WST.msg(WST.lang('input_consult_content'),'info');
    return;
  }
  if(params.consultContent.length<3 || params.consultContent.length>200){
    WST.msg(WST.lang('consult_content_msg'),'info');
    return;
  }
  WST.load(WST.lang('submiting_tips'));
  $.post(WST.U('mobile/goodsconsult/add'),params,function(responData){
    WST.noload();
    var json = WST.toJson(responData);
    if(json.status==1){
       // 发布成功
       WST.msg(json.msg,'success');
       setTimeout(function(){
    	   history.back();
        },1000);
    }else{
      WST.msg(json.msg,'warn');
    }
  })
}