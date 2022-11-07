function queryByPage(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('label_goods_img'), name:'goodsName', width: 40, renderer: function(val,item,rowIndex){
                return "<span class='weixin'><img class='img' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'><img class='imged' style='height:200px;width:200px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span>";
            }},
        {title:WST.lang('label_goods'), name:'goodsName', width: 200},

        {title:WST.lang('goodsconsult_show'), name:'', width: 10, align:'center',renderer:function(val,item,rowIndex){
            if(item['isShow']==1){
                return "<span class='statu-yes'><i class='fa fa-check-circle'></i>"+WST.lang('goodsconsult_show')+"</span>";
            }else{
                return "<span class='statu-no'><i class='fa fa-ban'></i>"+WST.lang('goodsconsult_hide')+"</span>";
            }
        }},
        {title:WST.lang('label_goodsconsult_content'), name:'goodsName', width: 300,renderer:function(val,item,rowIndex){
            var html="";
            if(WST.blank(item['loginName'])==''){
                html+=WST.lang('goodsconsult_tourist')+"："+item['consultContent'];
            }else{
                html+=item['loginName']+"："+item['consultContent'];

            }
                html+="<span>("+item['createTime']+")</span>";
                if(item['reply']==null || item['reply']==''){
                   html+="<div><textarea style=\"width:98%;height:80px;margin-bottom:2px;\" id=\"reply-"+item['id']+"\" placeholder='"+WST.lang('label_goodsconsult_reply_plo')+"' maxlength='200'></textarea>\n" +
                       "              <a class=\"btn btn-primary\" style=\"margin-left:3px;\" onclick=\"reply(this,"+item['id']+")\"><i class='fa fa-mail-reply'></i>"+WST.lang('reply')+"</a></div>";
                }else {
                    html+="<p class=\"reply-content\">"+item['reply']+"【"+item['replyTime']+"】</p>";
                }
                return html;
            }},
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',
        url: WST.U('supplier/goodsconsult/pageQuery'), fullWidthRows: true, autoLoad: false,checkCol:true,multiSelect:true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function loadGrid(p){
    p=(p<=1)?1:p;
    mmg.load({consultType:$('#consultType').val(),consultKey:$('#consultKey').val(),page:p});
}
function reply(t,id){
 var params = {};
 if($('#reply-'+id).val()==''){
    WST.msg(WST.lang('require_reply'),{icon:2});
    return false;
 }
 params.reply = $('#reply-'+id).val();
 params.id=id;
 $.post(WST.U('supplier/goodsconsult/reply'),params,function(data){
    var json = WST.toJson(data);
    if(json.status==1){
      var today = new Date();
      var Myd = today.toLocaleDateString();
      var His = today.toLocaleTimeString();
      var html = '<p class="reply-content">'+params.reply+'【'+Myd+'  '+His+'】</p>'
      $(t).parent().html(html);
    }
 });
}

function editConsult(isShow,id){
    var rows = mmg.selectedRows();
    if(rows.length==0){
        WST.msg(WST.lang('select_edit_goods'),{icon:2});
        return;
    }
    var ids = [];
    for(var s=0;s<rows.length;s++){
        ids.push(rows[s]['id']);
    }
	var params = {};
	params.id = ids;
	params.isShow = parseInt(isShow);
	$.post(WST.U('supplier/goodsConsult/edit'),params,function(data){
          var json = WST.toJson(data);
          if(json.status==1){
           WST.msg(WST.lang('op_ok'), {icon: 1});
           loadGrid(WST_CURR_PAGE);
          }
	})
}
