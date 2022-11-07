var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_switch_home_url'), name:'homeURL', width: 100},
            {title:WST.lang('label_switch_mobile_url'), name:'mobileURL', width: 100},
            {title:WST.lang('label_switch_wechat_url'), name:'wechatURL', width: 100},
            {title:WST.lang('op'), name:'' ,width:20, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                if(WST.GRANT.YMQH_02)h += "<a  class='btn btn-red' onclick='javascript:getForEdit(" + item['id'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                if(WST.GRANT.YMQH_03)h += "<a  class='btn btn-red' onclick='javascript:toDel(" + item['id'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
            ];

    mmg = $('.mmg').mmGrid({height: h-89,indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/switchs/pageQuery'), fullWidthRows: true, autoLoad: false,
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
function toDel(id){
	var box = WST.confirm({content:WST.lang("del_tips"),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/switchs/del'),{id:id},function(data,textStatus){
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
function getForEdit(id){
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/switchs/get'),{id:id},function(data,textStatus){
        layer.close(loading);
        var json = WST.toAdminJson(data);
        if(json['id']>0){
            WST.setValues(json);
            toEdit(json['id']);
        }else{
            WST.msg(json.msg,{icon:2});
        }
    });
}
function toEdit(id){
    if(id==0){
        $('#switchForm')[0].reset();
    }
	var box = WST.open({title:WST.lang("add"),type:1,content:$('#switchBox'),area: ['450px', '300px'],
		  btn:[WST.lang('confirm'),WST.lang('cancel')],yes:function(){
          save(0,box);
      },
      btn2:function(){$('#switchBox').hide();},
      cancel:function(){$('#switchBox').hide();}
    });
}

function save(type,box){
   var params = WST.getParams('.ipt');
   var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
   $.post(WST.U('admin/switchs/'+((params.id==0)?'add':'edit')),params,function(data,textStatus){
        layer.close(loading);
        var json = WST.toAdminJson(data);
        if(json.status=='1'){
            WST.msg(WST.lang('op_ok'),{icon:1});
            if(type==0){
                $('#switchForm')[0].reset();
                $('#switchBox').hide();
                layer.close(box);
            }else{
              $('#switchForm')[0].reset();
            }
            loadGrid(WST_CURR_PAGE);
            return;
        }else{
            WST.msg(json.msg,{icon:2});
            return;
        }
   });
}
