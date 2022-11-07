var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_chargeitem_money'), name:'chargeMoney', width: 60},
            {title:WST.lang('label_chargeitem_gift_money'), name:'giveMoney' ,width:60},
            {title:WST.lang('sort'), name:'itemSort' ,width:50},
            {title:WST.lang('create_time'), name:'createTime' ,width:30},
            {title:WST.lang('op'), name:'op' ,width:150, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                if(WST.GRANT.CZGL_02)h += "<a  class='btn btn-blue' onclick='javascript:location.href=\""+WST.U('admin/Chargeitems/toEdit','id='+item['id'])+'&p='+WST_CURR_PAGE+"\"'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                if(WST.GRANT.CZGL_03)h += "<a  class='btn btn-red' onclick='javascript:toDel(" + item['id'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: (h-90),indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/Chargeitems/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadQuery(p);
}

function loadQuery(p){
    p=(p<=1)?1:p;
    var query = WST.getParams('.query');
    query.page = p;
    mmg.load(query);
}
function toDel(id){
	var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/Chargeitems/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(WST.lang('op_ok'),{icon:1});
	           			    	layer.close(box);
	           		        loadQuery(WST_CURR_PAGE);
	           			  }else{
	           			    	WST.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}



function editInit(p){
	 /* 表单验证 */
    $('#adPositionsForm').validator({
            fields: {
            	chargeMoney: {
                  rule:"required",
                  msg:{required:WST.lang('require_chargeitem_money')},
                  tip:WST.lang('require_chargeitem_money'),
                  ok:"",
                },
                giveMoney: {
                  rule:"required;",
                  msg:{required:WST.lang('require_chargeitem_gift_money')},
                  tip:WST.lang('require_chargeitem_gift_money'),
                  ok:"",
                }
            },
          valid: function(form){
            var params = WST.getParams('.ipt');
            var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
            $.post(WST.U('admin/Chargeitems/'+((params.id==0)?"add":"edit")),params,function(data,textStatus){
              layer.close(loading);
              var json = WST.toAdminJson(data);
              if(json.status=='1'){
                  WST.msg(WST.lang('op_ok'),{icon:1});
                  location.href=WST.U('Admin/Chargeitems/index','p='+p);
              }else{
                    WST.msg(json.msg,{icon:2});
              }
            });
      }
    });
}