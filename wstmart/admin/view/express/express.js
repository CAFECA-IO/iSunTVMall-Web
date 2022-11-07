var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_express_name'), name:'expressName', width: 260},
            {title:WST.lang('label_express_code'), name:'expressCode' ,width:160},
            {title:WST.lang('label_is_enable'), name:'isShow', width: 30,renderer: function(val,item,rowIndex){
              return '<input type="checkbox" '+((item['isShow']==1)?"checked":"")+' name="isShow2" lay-skin="switch" lay-filter="isShow2" data="'+item['expressId']+'" lay-text="'+WST.lang('is_show_val')+'">';

            }},
            {title:WST.lang('op'), name:'' ,width:180, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
			    if(WST.GRANT.KDGL_02)h += "<a  class='btn btn-blue' onclick='javascript:getForEdit(" + item['expressId'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
			    if(WST.GRANT.KDGL_03)h += "<a  class='btn btn-red' onclick='javascript:toDel(" + item['expressId'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
			    return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: (h-165),indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/express/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });  
    mmg.on('loadSuccess',function(){
      layui.form.render('','gridForm');
        layui.form.on('switch(isShow2)', function(data){
            var id = $(this).attr("data");
            if(this.checked){
            toggleIsShow(0,id);
        }else{
            toggleIsShow(1,id);
        }
        });
    })
    $('#headTip').WSTTips({width:200,height:35,callback:function(v){
         if(v){
             mmg.resize({height:h-165});
         }else{
             mmg.resize({height:h-135});
         }
    }});
    loadQuery(p);
}
function toggleIsShow(t,v){
  if(!WST.GRANT.DQGL_02)return;
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
      $.post(WST.U('admin/express/editiIsShow'),{id:v,isShow:t},function(data,textStatus){
        layer.close(loading);
        var json = WST.toAdminJson(data);
        if(json.status=='1'){
            WST.msg(json.msg,{icon:1});
            loadQuery(WST_CURR_PAGE);
        }else{
            WST.msg(json.msg,{icon:2});
        }
    });
}
function loadQuery(p){
    p=(p<=1)?1:p;
    mmg.load({page:p});
}
function toDel(id){
	var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/express/del'),{id:id},function(data,textStatus){
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

function getForEdit(id){
	 var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
     $.post(WST.U('admin/express/get'),{id:id},function(data,textStatus){
           layer.close(loading);
           var json = WST.toAdminJson(data);
           if(json.expressId){
           		WST.setValues(json);
              if(json.langs){
                for(var key in json.langs){
                   WST.setValue('langParams'+key+'expressName',json.langs[key]['expressName']);
                }
              }
              $('#isShow')[0].checked = (json.isShow==1);
              layui.form.render();
           		toEdit(json.expressId);
           }else{
           		WST.msg(json.msg,{icon:2});
           }
    });
}

function toEdit(id){
	var title = WST.lang('add');
	if(id>0){
		title = WST.lang('edit');
	}else{
		$('#expressForm')[0].reset();
	}

	var box = WST.open({title:title,type:1,content:$('#expressBox'),area: ['100%', '100%'],offset: 't',btn:[WST.lang('submit'),WST.lang('cancel')],btnAlign: 'c',
        end:function(){$('#expressBox').hide();},
		yes:function(){
		$('#expressForm').submit();
	}});
  var fields = {};
  var n = 0;
  for(var i in WST.conf.sysLangs){
      n = WST.conf.sysLangs[i]['id'];
      fields['langParams'+n+'expressName'] = {
              rule:"required;",
              msg:{required:WST.lang('require_express_name')},
              tip:WST.lang('require_express_name'),
              ok:"",
            }
  }
	$('#expressForm').validator({
        fields: fields,
       valid: function(form){
		        var params = WST.getParams('.ipt');
            var n = 0;
            params['langParams'] = {};
            for(var i in WST.conf.sysLangs){
                n = WST.conf.sysLangs[i]['id'];
                params['langParams'][n] = {};
                params['langParams'][n]['expressName'] = params['langParams'+n+'expressName'];
            }
	          params.expressId = id;
	          var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	          $.post(WST.U('admin/express/'+((id==0)?"add":"edit")),params,function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(WST.lang('op_ok'),{icon:1});
	           			    	$('#expressForm')[0].reset();
	           			    	layer.close(box);
                              loadQuery(WST_CURR_PAGE);
	           			  }else{
	           			        WST.msg(json.msg,{icon:2});
	           			  }
	         });

    	}

  });

}