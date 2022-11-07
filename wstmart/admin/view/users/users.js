var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_user_login_name'), name:'loginName', width: 110,sortable:true},
            {title:WST.lang('label_user_name'), name:'userName' ,width:120,sortable:true,renderer:function(val,item,rowIndex){
                var html = '';
                if(item['wxOpenId'] && item['wxOpenId']!='')html = html+'<img class="order-source2" src="'+WST.conf.ROOT+'/wstmart/admin/view/img/order_source_1.png" title="'+WST.lang('user_has_bind_wx')+'"> ';
                if(item['weOpenId'] && item['weOpenId']!='')html = html+'<img class="order-source2" src="'+WST.conf.ROOT+'/wstmart/admin/view/img/order_source_5.png" title="'+WST.lang('user_has_bind_we')+'"> ';
                html = html +WST.blank(val)
                return html;
            }},
            {title:WST.lang('label_user_phone'), name:'userPhone' ,width:100,sortable:true, renderer: function(val,item,rowIndex){
              if(val){
                return "+"+item["areaCode"]+" "+val;
              }else{
                return "";
              }
            }},
            {title:WST.lang('label_user_email'), name:'userEmail' ,width:100,sortable:true},
            {title:WST.lang('label_user_score'), name:'userScore' ,width:50,sortable:true},
            {title:WST.lang('label_user_rank'), name:'rank' ,width:60,sortable:true},
            {title:WST.lang('label_user_create_time'), name:'createTime' ,width:100,sortable:true},
            {title:WST.lang('label_user_money'), name:'userMoney' ,width:120,sortable:true, renderer:function(val,item,rowIndex){
                return '<div style="line-height:20px">'+WST.lang('user_can_use_money')+'：'+WST.lang('currency_symbol')+val+'<br/>'+WST.lang('user_lock_money')+'：'+WST.lang('currency_symbol')+item['lockMoney']+'<br/>'+WST.lang('user_recharge_give')+'：'+WST.lang('currency_symbol')+item['rechargeMoney']+'</div>';
            }},
            {title:WST.lang('label_user_status'), name:'userStatus' ,width:50, renderer:function(val,item,rowIndex){
              return '<input type="checkbox" '+((val==1)?"checked":"")+' lay-skin="switch" lay-filter="userStatus" data="'+item['userId']+'" lay-text="'+WST.lang('user_status_type1')+'|'+WST.lang('user_status_type2')+'">';
            }},
            {title:WST.lang('op'), name:'' ,width:150, align:'center', renderer: function(val,rowdata,rowIndex){
                var h = "";
                if(WST.GRANT.HYGL_02)h += "<a  class='btn btn-blue' href='"+WST.U('admin/Users/toEdit','id='+rowdata['userId'])+'&p='+WST_CURR_PAGE+"'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                if(WST.GRANT.HYGL_03)h += "<a  class='btn btn-red' href='javascript:toDel(" + rowdata['userId'] + ","+rowdata['userType']+")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                h += "<br/><a href='"+WST.U('admin/userscores/touserscores','id='+rowdata['userId'])+'&p='+WST_CURR_PAGE+"'>"+WST.lang('label_user_score')+"</a> ";
                h += "<a href='"+WST.U('admin/logmoneys/tologmoneys','id='+rowdata['userId']+'&src=users')+'&p='+WST_CURR_PAGE+"&type=0'>"+WST.lang('user_money')+"</a> ";
                h += "<a href='"+WST.U('admin/orders/index','userId='+rowdata['userId'])+'&p='+WST_CURR_PAGE+"&type=0'>"+WST.lang('user_consume_info')+"</a> ";
                return h;
            }}
            ];

    mmg = $('.mmg').mmGrid({height: h-187,indexCol: true,indexColWidth:50, cols: cols,method:'POST',nowrap:true,
        url: WST.U('admin/Users/pageQuery'), fullWidthRows: true, autoLoad: false,remoteSort: true,sortName:'createTime',sortStatus:'desc',
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    mmg.on('loadSuccess',function(){
      layui.form.render('','gridForm');
        layui.form.on('switch(userStatus)', function(data){
            var id = $(this).attr("data");
            if(this.checked){
                changeUserStatus(id, 1);
            }else{
                changeUserStatus(id, 0);
            }
        });
    })
    $('#headTip').WSTTips({width:200,height:35,callback:function(v){
         if(v){
             mmg.resize({height:h-187});
         }else{
             mmg.resize({height:h-142});
         }
    }});
    loadGrid(p);
}
function loadGrid(p){
    p=(p<=1)?1:p;
    mmg.load({page:p});
}
function toEdit(id){
   location.href=WST.U('admin/users/toEdit','id='+id+'&p='+WST_CURR_PAGE);
}
function toDel(id,userType){
  var msg = (userType==1)?WST.lang("user_delete_tips1"):WST.lang("user_delete_tips2");
	var box = WST.confirm({content:msg,yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/Users/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(WST.lang('op_ok'),{icon:1});
	           			    	layer.close(box);
	           		         userQuery(WST_CURR_PAGE);
	           			  }else{
	           			    	WST.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}

function userQuery(p){
    p=(p<=1)?1:p;
		var query = WST.getParams('.query');
    query.page = p;
    mmg.load(query);
}

function changeUserStatus(id, status){
  $.post(WST.U('admin/Users/changeUserStatus'), {'id':id, 'status':status}, function(data, textStatus){
    var json = WST.toAdminJson(data);
                    if(json.status=='1'){
                        WST.msg(WST.lang('op_ok'),{icon:1});
                            userQuery(WST_CURR_PAGE);
                    }else{
                        WST.msg(json.msg,{icon:2});
                    }
  })
}

function editInit(p){
  var laydate = layui.laydate;
  laydate.render({elem: '#brithday'});
	/* 表单验证 */
  $('#userForm').validator({
            dataFilter: function(data) {
                if (data.ok === WST.lang('user_add_login_name_use_ok') ) return "";
                else return WST.lang("user_add_tips2");
            },
            rules: {
                loginName: function(element) {
                    return /\w{5,}/.test(element.value) || WST.lang('require_user_login_name_tips');
                },
                myRemote: function(element){
                    return $.post(WST.U('admin/users/checkLoginKey'),{'loginName':element.value,'userId':$('#userId').val()},function(data,textStatus){});
                }
            },
            fields: {
                loginName: {
                  rule:"required;loginName;myRemote",
                  msg:{required:WST.lang("require_user_login_name")},
                  tip:WST.lang("require_user_login_name"),
                  ok:"",
                },
                userPhone: {
                  rule:"myRemote",
                  ok:"",
                },
                userEmail: {
                  rule:"email;myRemote",
                  ok:"",
                },
                userScore: {
                  rule:"integer[+0]",
                  msg:{integer:WST.lang("require_user_score")},
                  tip:WST.lang("require_user_score"),
                  ok:"",
                },
                userTotalScore: {
                  rule:"match[gte, userScore];integer[+0];",
                  msg:{integer:WST.lang("require_user_score"),match:WST.lang('require_user_score_tips')},
                  tip:WST.lang("require_user_score"),
                  ok:"",
                },

            },

          valid: function(form){
            var params = WST.getParams('.ipt');
            var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
            $.post(WST.U('admin/Users/'+((params.userId==0)?"add":"edit")),params,function(data,textStatus){
              layer.close(loading);
              var json = WST.toAdminJson(data);
              if(json.status=='1'){
                  WST.msg(WST.lang('op_ok'),{icon:1});
                  location.href=WST.U('Admin/Users/index',"p="+p);
              }else{
                    WST.msg(json.msg,{icon:2});
              }
            });

      }

    });



//上传头像
  WST.upload({
      pick:'#adFilePicker',
      formData: {dir:'users'},
      accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
      callback:function(f){
        var json = WST.toAdminJson(f);
        if(json.status==1){
        $('#uploadMsg').empty().hide();
        //将上传的图片路径赋给全局变量
        $('#userPhoto').val(json.savePath+json.thumb);
        $('#preview').html('<img src="'+WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb+'"  height="30" />');
        }else{
          WST.msg(json.msg,{icon:2});
        }
    },
    progress:function(rate){
        $('#uploadMsg').show().html(WST.lang('upload_rate')+rate+"%");
    }
    });
}

function toExport(){
  var params = {};
  params = WST.getParams('.query');
  var box = WST.confirm({content:WST.lang("user_export_tips"),yes:function(){
      layer.close(box);
      location.href=WST.U('admin/users/toExport',params);
  }});
}
