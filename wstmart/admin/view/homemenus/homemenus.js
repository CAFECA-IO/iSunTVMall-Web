var grid;
$(function(){
  $('#headTip').WSTTips({width:200,height:35,callback:function(v){
     var diff = v?113:53;
  }});
  grid = $("#maingrid").WSTGridTree({
    url:WST.U('admin/homemenus/pageQuery'),
    pageSize:10000,
    pageSizeOptions:10000,
    height:'99%',
        width:'100%',
        minColToggle:6,
        delayLoad :true,
        rownumbers:true,
        columns: [
          { display: WST.lang('label_homemenu_name'), name: 'menuName', id:"menuId", width:190,isSort: false},
          { display: WST.lang('label_homemenu_url'), name: 'menuUrl', isSort: false},
          { display: WST.lang('label_homemenu_type'), name: 'menuType',  width:120,isSort: false,render:function(rowdata, rowindex, value){
            if(rowdata['menuType']==0){
              return WST.lang('label_homemenu_type0');
            }else if(rowdata['menuType']==1){
              return WST.lang('label_homemenu_type1');
            }else if(rowdata['menuType']==2){
              return WST.lang('label_homemenu_type2');
            }else if(rowdata['menuType']==3){
              return WST.lang('label_homemenu_type3');
            }
          }},
          { display: WST.lang('label_homemenu_is_show'), name: 'isShow', width:120,isSort: false,render :function(item, rowindex, value){
            return '<input type="checkbox" '+((item.isShow==1)?"checked":"")+' class="ipt" lay-skin="switch" lay-filter="isShow" data="'+item.menuId+'" lay-text="'+WST.lang('homemenu_is_show')+'">';
          }},
          { display: WST.lang('sort'), name: 'menuSort', width:120,isSort: false,render:function(rowdata,rowindex,value){
             return '<span class="classSort" style="cursor:pointer;color:blue;" ondblclick="changeSort(this,'+rowdata["menuId"]+');">'+rowdata['menuSort']+'</span>';
          }},
          { display: WST.lang('op'), name: 'op',width:290,isSort: false,render: function (rowdata, rowindex, value){
              var h = "";
              if(WST.GRANT.QTCD_01)h += "<a  class='btn btn-blue' href='javascript:toEdit(0," + rowdata['menuId'] + ","+rowdata['menuType']+")'><i class='fa fa-plus'></i>"+WST.lang('homemenus_add_child')+"</a> ";
              if(WST.GRANT.QTCD_02)h += "<a  class='btn btn-blue' href='javascript:getForEdit("+rowdata["parentId"]+"," + rowdata['menuId'] + ")' href='"+WST.U('admin/homemenus/toEdit','menuId='+rowdata['menuId'])+"'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
              if(WST.GRANT.QTCD_03)h += "<a  class='btn btn-red' href='javascript:toDel("+rowdata["parentId"]+"," + rowdata['menuId'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
              return h;
          }}
        ],
        callback:function(){
            $('.classSort').poshytip({content:WST.lang('homemenu_dblclick'),showTimeout:0,hideTimeout:1,alignX: 'center',
              offsetY: 10,timeOnScreen:1000,allowTipHover: false});
            layui.form.render();
         }
    });
    layui.form.on('switch(isShow)', function(data){
          var id = $(this).attr("data");
          if(this.checked){
              toggleIsShow(id, 1);
          }else{
              toggleIsShow(id, 0);
          }
    });
    $('body').css('overflow-y','auto');
})


var oldSort;
function changeSort(t,id){
  if(!WST.GRANT.QTCD_02)return;
  $(t).attr('ondblclick'," ");
var html = "<input type='text' id='sort-"+id+"' style='width:30px;padding:2px' onblur='doneChange(this,"+id+")' value='"+$(t).html()+"' />";
 $(t).html(html);
 $('#sort-'+id).focus();
 $('#sort-'+id).select();
}
function doneChange(t,id){
  var sort = ($(t).val()=='')?0:$(t).val();
  if(sort==oldSort){
    $(t).parent().attr('ondblclick','changeSort(this,'+id+')');
    $(t).parent().html(parseInt(sort));
    return;
  }
  $.post(WST.U('admin/homemenus/changeSort'),{id:id,menuSort:sort},function(data){
    var json = WST.toAdminJson(data);
    if(json.status==1){
        $(t).parent().attr('ondblclick','changeSort(this,'+id+')');
        $(t).parent().html(parseInt(sort));
    }
  });
}




function toDel(pid,menuId){
  var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/homemenus/del'),{menuId:menuId},function(data,textStatus){
      layer.close(loading);
      var json = WST.toAdminJson(data);
      if(json.status=='1'){
        WST.msg(json.msg,{icon:1});
        layer.close(box);
        grid.reload(pid);
      }else{
        WST.msg(json.msg,{icon:2});
      }
    });
  }});
}



function edit(pid,menuId){
  //获取所有参数
  var params = WST.getParams('.ipt');
    params.menuId = menuId;
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/homemenus/'+((menuId==0)?"add":"edit")),params,function(data,textStatus){
      layer.close(loading);
      var json = WST.toAdminJson(data);
      if(json.status=='1'){
          WST.msg(json.msg,{icon:1});
          loadGrid();
      }else{
          WST.msg(json.msg,{icon:2});
      }
    });
}
function toggleIsShow(menuId, isShow){
  if(!WST.GRANT.QTCD_02)return;
  $.post(WST.U('admin/homemenus/setToggle'), {'menuId':menuId, 'isShow':isShow}, function(data, textStatus){
    var json = WST.toAdminJson(data);
    if(json.status=='1'){
      WST.msg(json.msg,{icon:1});
      grid.reload(menuId);
    }else{
      WST.msg(json.msg,{icon:2});
    }
  })
}

function getForEdit(pid,menuId){
  $('#menuForm')[0].reset();
  var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/homemenus/get'),{menuId:menuId},function(data,textStatus){
          layer.close(loading);
          var json = WST.toAdminJson(data);
          if(json.menuId){
              WST.setValues(json);
              for(var key in WST.conf.sysLangs){
                 var val = json['langParams']?json['langParams'][WST.conf.sysLangs[key]['id']]['menuName']:'';
                 $('#langParams'+WST.conf.sysLangs[key]['id']+'menuName').val(val);
              }
              toEdit(json.menuId,pid);
          }else{
              WST.msg(json.msg,{icon:2});
          }
   });
}

function toEdit(menuId,parentId,tId){
  var title = WST.lang('edit');
  if(menuId==0){
    $('#menuForm')[0].reset();
    title = WST.lang('add');
    WST.setValue('isShow',1);
  }
  if(parentId>0){
	  $('#menuTypes').hide();
  }else{
	  $('#menuTypes').show();
  }
  if(tId==1){$('#menuType').val(1);}
  layui.form.render();
  var box = WST.open({title:title,type:1,content:$('#menuBox'),area: ['100%', '100%'],btn:[WST.lang('submit'),WST.lang('cancel')],offset: 't',btnAlign: 'c',
    end:function(){$('#menuBox').hide();},yes:function(){
    $('#menuForm').submit();
  }});
  $('#menuForm').validator({
        fields: {
          'menuName': {rule:"required;",msg:{required:WST.lang('require_homemenu_name')}},
          'menuUrl': {rule:"required;",msg:{required:WST.lang('require_homemenu_url')}}
        },
        valid: function(form){
          var params = WST.getParams('.ipt');
          var n = 0;
          params['langParams'] = {};
          for(var key in WST.conf.sysLangs){
              n = WST.conf.sysLangs[key]['id'];
              params['langParams'][n] = {};
              params['langParams'][n]['menuName'] = params['langParams'+n+'menuName'];
          }
          params.menuId = menuId;
          params.parentId = parentId;
          params.isShow = params.isShow?params.isShow:0;
          var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
         $.post(WST.U('admin/homemenus/'+((menuId==0)?"add":"edit")),params,function(data,textStatus){
           layer.close(loading);
           var json = WST.toAdminJson(data);
           if(json.status=='1'){
                WST.msg(json.msg,{icon:1});
                $('#menuBox').hide();
                $('#menuForm')[0].reset();
                layer.close(box);
                grid.reload(params.parentId);
           }else{
             WST.msg(json.msg,{icon:2});
            }
          });

      }

  });
}
function loadGrid(){
    $("#maingrid").WSTGridTree({
    url:WST.U('admin/homemenus/pageQuery',{menuType:$('#s_menuType').val()}),
    pageSize:10000,
    pageSizeOptions:10000,
    height:'99%',
        width:'100%',
        minColToggle:6,
        delayLoad :true,
        rownumbers:true,
        columns: [
          { display: WST.lang('label_homemenu_name'), name: 'menuName', id:"menuId", width:190,isSort: false},
          { display: WST.lang('label_homemenu_url'), name: 'menuUrl', isSort: false},
          { display: WST.lang('label_homemenu_type'), name: 'menuType',  width:80,isSort: false,render:function(rowdata, rowindex, value){
             return (rowdata['menuType']==0)?WST.lang('label_homemenu_type0'):WST.lang('label_homemenu_type1');
          }},
          { display: WST.lang('label_homemenu_is_show'), name: 'isShow', width:80,isSort: false,render :function(item, rowindex, value){
            return '<input type="checkbox" '+((item.isShow==1)?"checked":"")+' class="ipt" lay-skin="switch" lay-filter="isShow" data="'+item.menuId+'" lay-text="'+WST.lang('homemenu_is_show')+'">';
          }},
          { display: WST.lang('sort'), name: 'menuSort', width:60,isSort: false,render:function(rowdata,rowindex,value){
             return '<span style="cursor:pointer;color:blue;" ondblclick="changeSort(this,'+rowdata["menuId"]+');">'+rowdata['menuSort']+'</span>';
          }},
          { display: WST.lang('op'), name: 'op',width:290,isSort: false,render: function (rowdata, rowindex, value){
              var h = "";
              if(WST.GRANT.QTCD_01)h += "<a  class='btn btn-blue' href='javascript:toEdit(0," + rowdata['menuId'] + ","+rowdata['menuType']+")'><i class='fa fa-plus'></i>"+WST.lang('homemenus_add_child')+"</a> ";
              if(WST.GRANT.QTCD_02)h += "<a  class='btn btn-blue' href='javascript:getForEdit("+rowdata["parentId"]+"," + rowdata['menuId'] + ")' href='"+WST.U('admin/homemenus/toEdit','menuId='+rowdata['menuId'])+"'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
              if(WST.GRANT.QTCD_03)h += "<a  class='btn btn-red' href='javascript:toDel("+rowdata["parentId"]+"," + rowdata['menuId'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
              return h;
          }}
        ],
        callback:function(){
          layui.form.render();
        }
    });
    layui.form.on('switch(isShow)', function(data){
          var id = $(this).attr("data");
          if(this.checked){
              toggleIsShow(id, 1);
          }else{
              toggleIsShow(id, 0);
          }
    });
}