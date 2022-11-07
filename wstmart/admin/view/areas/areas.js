var mmg;
function initGrid(p){
    var parentId=$('#h_areaId').val();
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_area_name'), name:'areaName', width: 300},
            {title:WST.lang('is_show'), name:'isShow', width: 30,renderer: function(val,item,rowIndex){
            	return '<input type="checkbox" '+((item['isShow']==1)?"checked":"")+' name="isShow2" lay-skin="switch" lay-filter="isShow2" data="'+item['areaId']+'" lay-text="'+WST.lang('is_show_val')+'">';

            }},
            {title:WST.lang('label_area_sort_zm'), name:'areaKey', width: 30},
            {title:WST.lang('sort'), name:'areaSort', width: 30},
            {title:WST.lang('op'), name:'' ,width:140, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                h += "<a  class='btn btn-blue' onclick='javascript:toView("+item['areaId']+")'><i class='fa fa-search'></i>"+WST.lang('view')+"</a> ";
                if(WST.GRANT.DQGL_02)h += "<a  class='btn btn-blue' onclick='javascript:toEdit("+item['areaId']+","+item["parentId"]+")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                if(WST.GRANT.DQGL_03)h += "<a  class='btn btn-red' onclick='javascript:toDel(" + item['areaId'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a>";
                return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-89,indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/areas/pageQuery','parentId='+parentId), fullWidthRows: true, autoLoad: false,
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
    loadQuery(p);
}
function loadQuery(p){
    p=(p<=1)?1:p;
    mmg.load({page:p});
}

function toggleIsShow(t,v){
	if(!WST.GRANT.DQGL_02)return;
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    	$.post(WST.U('admin/areas/editiIsShow'),{id:v,isShow:t},function(data,textStatus){
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

function toReturn(){
	location.href=WST.U('admin/areas/index','parentId='+$('#h_parentId').val()+'&p='+WST_CURR_PAGE);
}

function letterOnblur(obj){
	if($.trim(obj.value)=='')return;
	var loading = WST.msg(WST.lang('area_zm_loading'), {icon: 16,time:60000});
	$.post(WST.U('admin/areas/letterObtain'),{code:obj.value},function(data,textStatus){
		layer.close(loading);
		var json = WST.toAdminJson(data);
		if(json.status == 1){
			$('#areaKey').val(json.msg);
		}
	});
}

function toEdit(id,pid){
	$('#areaForm')[0].reset();
	if(id>0){
		var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		$.post(WST.U('admin/areas/get'),{id:id},function(data,textStatus){
			layer.close(loading);
			var json = WST.toAdminJson(data);
			if(json){
				WST.setValues(json);
				layui.form.render();
				editsBox(id);
			}
		});
	}else{
		WST.setValues({parentId:pid,areaId:0});
		layui.form.render();
		editsBox(id);
	}
}
function toView(id){
	location.href = WST.U('admin/areas/index','parentId='+id);
}
function editsBox(id){
	var box = WST.open({title:(id>0)?WST.lang('edit'):WST.lang('add'),type:1,content:$('#areasBox'),area: ['460px', '360px'],btn:[WST.lang('submit'),WST.lang('cancel')],
		end:function(){$('#areasBox').hide();},yes:function(){
		$('#areaForm').submit();
	          }});
	$('#areaForm').validator({
	    fields: {
	    	areaName: {
	    		tip: WST.lang('require_area_name'),
	    		rule: WST.lang('label_area_name')+':required;length[~10];'
	    	},
		    areaKey: {
	    		tip: WST.lang('require_area_sort_zm'),
	    		rule: WST.lang('label_area_sort_zm')+':required;length[~1];'
	    	},
	    	areaSort: {
            	tip: WST.lang('require_sort'),
            	rule: WST.lang('sort')+':required;length[~8];'
            }
	    },
	    valid: function(form){
	        var params = WST.getParams('.ipt');
	        var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	    		$.post(WST.U('admin/areas/'+((id>0)?"edit":"add")),params,function(data,textStatus){
	    			  layer.close(loading);
	    			  var json = WST.toAdminJson(data);
	    			  if(json.status=='1'){
	    			    	WST.msg(json.msg,{icon:1});
	    			    	$('#areasBox').hide();
	    			    	layer.close(box);
                          loadQuery(WST_CURR_PAGE);
	    			  }else{
	    			        WST.msg(json.msg,{icon:2});
	    			  }
	    		});
	    }
	});
}

function toDel(id){
	var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/areas/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(json.msg,{icon:1});
	           			    	layer.close(box);
                              loadQuery(WST_CURR_PAGE);
	           			  }else{
	           			    	WST.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}