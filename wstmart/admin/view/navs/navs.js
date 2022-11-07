var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_nav_posi'), name:'navType', width: 30, renderer: function(val,item,rowIndex){
            	return (val==0)?WST.lang('nav_posi_0'):WST.lang('nav_posi_1');
            }},
            {title:WST.lang('label_nav_name'), name:'navTitle',width:30, renderer: function(val,item,rowIndex){
                return WST.TransLang(val);
            }},
            {title:WST.lang('label_nav_url'), name:'navUrl' ,width:120},
            {title:WST.lang('label_nav_is_show'), name:'isShow',width:20, renderer: function(val,item,rowIndex){
            	return '<span class="layui-form"><input type="checkbox" '+ ((item.isShow==1)?"checked":"" )+' class="ipt" id="isShow" name="isShow" lay-skin="switch" lay-filter="isShow" data="'+item['id']+'" lay-text="'+WST.lang('nav_is_show')+'"></span>';
            }},
            {title:WST.lang('label_nav_open'), name:'isOpen',width:30,renderer: function(val,item,rowIndex){
            	return (val==1)?'<span style="cursor:pointer" onclick="isShowtoggle(\'isOpen\','+item['id']+', 0)">'+WST.lang('nav_open_0')+'</span>':'<span style="cursor:pointer" onclick="isShowtoggle(\'isOpen\','+item['id']+', 1)">'+WST.lang('nav_open_1')+'</span>';
            }},
            {title:WST.lang('sort'), name:'navSort',width:10},
            {title:WST.lang('op'), name:'op' ,width:80, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
	            if(WST.GRANT.DHGL_02)h += "<a  class='btn btn-blue' href='"+WST.U('admin/Navs/toEdit','id='+item['id'])+'&p='+WST_CURR_PAGE+"'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
	            if(WST.GRANT.DHGL_03)h += "<a  class='btn btn-red' href='javascript:toDel(" + item['id'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
	            return h;
            }}
            ];

    mmg = $('.mmg').mmGrid({height: (h-165),indexCol: true, cols: cols,method:'POST',nowrap: true,
        url: WST.U('admin/Navs/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    mmg.on('loadSuccess',function(){
    	layui.form.render();
        layui.form.on('switch(isShow)', function(data){
            var id = $(this).attr("data");
            if(this.checked){
                isShowtoggle('isShow',id, 1);
            }else{
                isShowtoggle('isShow',id, 0);
            }
        });
    })
     $('#headTip').WSTTips({width:200,height:35,callback:function(v){
       var diff = v?155:128;
       mmg.resize({height:h-diff})
    }});
    loadGrid(p)
}
function loadGrid(p){
    p = (p<=1)?1:p;
    mmg.load({page:p});
}
function toDel(id){
	var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/navs/del'),{id:id},function(data,textStatus){
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
function edit(id,p){
    var params = WST.getParams('.ipt');
    var n = 0;
    params['langParams'] = {};
    for(var i in WST.conf.sysLangs){
        n = WST.conf.sysLangs[i]['id'];
        params['langParams'][n] = {};
        params['langParams'][n]['navTitle'] = params['langParams'+n+'navTitle'];
    }
    params.id = id;
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/navs/'+((id==0)?"add":"edit")),params,function(data,textStatus){
        layer.close(loading);
        var json = WST.toAdminJson(data);
        if(json.status=='1'){
            WST.msg(json.msg,{icon:1});
            location.href=WST.U('admin/navs/index','p='+p);
        }else{
            WST.msg(json.msg,{icon:2});
        }
    });
}
function isShowtoggle(field, id, val){
	if(!WST.GRANT.DHGL_02)return;
	$.post(WST.U('admin/Navs/editiIsShow'), {'field':field, 'id':id, 'val':val}, function(data, textStatus){
		var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(json.msg,{icon:1});
                              loadGrid(WST_CURR_PAGE);
	           			  }else{
	           			    	WST.msg(json.msg,{icon:2});
	           			  }
	})
}


function changeFlink(obj){
     var flink = $(obj).val();
     if(flink==1)
       $("#articles").hide();
     else
       $("#articles").show();

}
function changeArticles(obj){
     var url = $(obj).val();

     $("#navUrl").val(url);
}
