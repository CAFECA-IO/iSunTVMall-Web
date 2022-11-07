var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('lang_label_name'), name:'name', width: 300},
            {title:WST.lang('lang_label_status'), name:'status', width: 30,renderer: function(val,item,rowIndex){
              return (item['status']==1)?WST.lang('addon_set_enable'):WST.lang('addon_set_disable');
            }},
            {title:WST.lang('op'), name:'' ,width:170, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                if(WST.GRANT.XTYY_04){
                    if(item['status']==0){
                       h += "<a  class='btn btn-blue' onclick='javascript:changeStatus("+item['id']+",1)'><i class='fa fa-pencil'></i>"+WST.lang('addon_set_enable')+"</a> ";
                    }else{
                       h += "<a  class='btn btn-red' onclick='javascript:changeStatus(" + item['id'] + ",0)'><i class='fa fa-trash-o'></i>"+WST.lang('addon_set_disable')+"</a> ";
                    }
                }
                return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-89,indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/languages/listQuery'), fullWidthRows: true, autoLoad: true
    });
}
function loadGrid(p){
    p=(p<=1)?1:p;
    mmg.load({page:p});
}
function changeStatus(id,type){
  var msg = (type==1)?WST.lang('lang_is_enable'):WST.lang('lang_is_stop');
	var box = WST.confirm({content:msg,yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/languages/changeStatus'),{id:id,type:type},function(data,textStatus){
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
