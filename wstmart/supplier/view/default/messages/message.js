var laytpl = layui.laytpl;
var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('label_message_msg'), name:'msgContent' ,width:700,sortable:true,renderer: function(val,item,rowIndex){
            if(item['msgStatus'] == 0){
                return "<i class='fa fa-envelope fa-lg'></i-o'></i> "+item['msgContent'];
            }else{
                return "<i class='fa fa-envelope-open-o fa-lg'></i> "+item['msgContent'];
            }
        }},
        {title:WST.lang('create_time'), name:'createTime' ,width:90,sortable:true},
        {title:WST.lang('op'), name:'' ,width:100, align:'center', renderer: function(val,item,rowIndex){
            var h = "";
            h += "<a  class='btn btn-blue' onclick='javascript:showMsg("+item['id']+")'><i class='fa fa-search'></i>"+WST.lang('view')+"</a> ";
            h += "<a  class='btn btn-red' onclick='javascript:delMsg(" + item['id'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
            return h;
        }}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',checkCol:true,multiSelect:true,
        url: WST.U('supplier/Messages/pageQuery'), fullWidthRows: true, autoLoad: false,remoteSort: true,
        plugins: [
            $('#pg').mmPaginator()
        ]
    });
    loadGrid(p);
}

function loadGrid(p){
    p = (p<=1)?1:p;
    mmg.load({page:p});
}

function showMsg(id){
  location.href=WST.U('supplier/messages/showSupplierMsg','msgId='+id+'&p='+WST_CURR_PAGE);
}

function delMsg(id){
WST.confirm({content:WST.lang("message_confirm_del_msg"), yes:function(tips){
  var ll = WST.load({msg:WST.lang('submitting_data')});
  $.post(WST.U('supplier/messages/del'),{id:id},function(data,textStatus){
    layer.close(ll);
      layer.close(tips);
    var json = WST.toJson(data);
    if(json.status=='1'){
      WST.msg(WST.lang('op_ok'), {icon: 1}, function(){
         loadGrid(WST_CURR_PAGE);
      });
    }else{
      WST.msg(WST.lang('op_err'), {icon: 5});
    }
  });
}});
}
function batchDel(){
    var rows = mmg.selectedRows();
    if(rows.length==0){
        WST.msg(WST.lang('message_select_del_msg'),{icon:2});
        return;
    }
    var ids = [];
    for(var i=0;i<rows.length;i++){
        ids.push(rows[i]['id']);
    }
    WST.confirm({content:WST.lang("message_confirm_del_msg"), yes:function(tips){
        var params = {};
        params.ids = ids;
        var load = WST.load({msg:WST.lang('submitting_data')});
        $.post(WST.U('supplier/messages/batchDel'),params,function(data,textStatus){
          layer.close(load);
          var json = WST.toJson(data);
          if(json.status=='1'){
            WST.msg(WST.lang('op_ok'),{icon:1},function(){
                loadGrid(WST_CURR_PAGE);
            });
          }else{
            WST.msg(WST.lang('op_err'),{icon:5});
          }
        });
    }});
}
function batchRead(){
    var rows = mmg.selectedRows();
    if(rows.length==0){
        WST.msg(WST.lang('message_select_handle_msg'),{icon:2});
        return;
    }
    var ids = [];
    for(var i=0;i<rows.length;i++){
        ids.push(rows[i]['id']);
    }
    WST.confirm({content:WST.lang("message_confirm_set_read"), yes:function(tips){
        var params = {};
        params.ids = ids;
        var load = WST.load({msg:WST.lang('submitting_data')});
        $.post(WST.U('supplier/messages/batchRead'),params,function(data,textStatus){
          layer.close(load);
          var json = WST.toJson(data);
          if(json.status=='1'){
            WST.msg(WST.lang('op_ok'),{icon:1},function(){
                loadGrid(1);
            });
          }else{
            WST.msg(WST.lang('op_err'),{icon:5});
          }
        });
    }});
}
