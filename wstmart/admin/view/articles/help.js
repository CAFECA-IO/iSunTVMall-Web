var mmg,combo;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_article_id'), name:'articleId' ,width:20,sortable:true},
            {title:WST.lang('label_article_name'), name:'articleTitle' ,width:200,sortable:true},
            {title:WST.lang('label_article_cat'), name:'catName' ,width:100,sortable:true},
            {title:WST.lang('is_show'), name:'isShow' ,width:50,sortable:true, renderer: function(val,item,rowIndex){
                return '<form autocomplete="off" class="layui-form" lay-filter="gridForm"><input type="checkbox" id="isShow" name="isShow" '+((item['isShow']==1)?"checked":"")+' lay-skin="switch" value="1" lay-filter="isShow" lay-text="'+WST.lang('is_show_val')+'" data="'+item['articleId']+'"></form>';
            }},
            {title:WST.lang('label_article_last_editor'), name:'staffName' ,width:50,sortable:true},
            {title:WST.lang('label_article_create_time'), name:'createTime' ,width:120,sortable:true},
            {title:WST.lang('sort'), name:'catSort' ,width:15, renderer: function(val,item,rowIndex){
                return '<span style="color:blue;cursor:pointer;" ondblclick="changeSort(this,'+item["articleId"]+');">'+val+'</span>';
            }},
            {title:WST.lang('op'), name:'' ,width:100, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                if(WST.GRANT.DPBZ_02)h += "<a  class='btn btn-blue' onclick='javascript:toEdit("+item['articleId']+")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                if(WST.GRANT.DPBZ_03)h += "<a  class='btn btn-red' onclick='javascript:toDel(" + item['articleId'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-89,indexCol: true, cols: cols,method:'POST',checkCol:true,multiSelect:true,
        url: WST.U('admin/articles/pageQueryByHelp'), fullWidthRows: true, autoLoad: false,remoteSort: true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });   
    mmg.on('loadSuccess',function(){
    	layui.form.render('','gridForm');
        layui.form.on('switch(isShow)', function(data){
            var id = $(this).attr("data");
            if(this.checked){
                toggleIsShow(1,id);
            }else{
                toggleIsShow(0,id);
            }
        });
    })
    loadGrid(p);
}


function loadGrid(p){
	p=(p<=1)?1:p;
	mmg.load({key:$('#key').val(),catId:$('#catId').val(),page:p});
}

function toggleIsShow(t,v){
	if(!WST.GRANT.WZGL_02)return;
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    	$.post(WST.U('admin/articles/editiIsShow'),{id:v,isShow:t},function(data,textStatus){
			  layer.close(loading);
			  var json = WST.toAdminJson(data);
			  if(json.status=='1'){
			    	WST.msg(json.msg,{icon:1});
		            mmg.load();
			  }else{
			    	WST.msg(json.msg,{icon:2});
			  }
		});
}

function toggleIsShow(t,v){
	if(!WST.GRANT.WZGL_02)return;
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    	$.post(WST.U('admin/articles/editiIsShow'),{id:v,isShow:t},function(data,textStatus){
			  layer.close(loading);
			  var json = WST.toAdminJson(data);
			  if(json.status=='1'){
			    	WST.msg(json.msg,{icon:1});
		            loadGrid(WST_CURR_PAGE);
			  }else{
			    	WST.msg(json.msg,{icon:2});
			  }
		});
}

function toEdit(id){
	location.href=WST.U('admin/articles/toEditHelp','id='+id+'&p='+WST_CURR_PAGE);
}

function toEdits(id,p){
    var params = WST.getParams('.ipt');
    var n = 0;
    params['langParams'] = {};
    for(var i in WST.conf.sysLangs){
        n = WST.conf.sysLangs[i]['id'];
        params['langParams'][n] = {};
    	params['langParams'][n]['articleTitle'] = params['langParams'+n+'articleTitle'];
    	params['langParams'][n]['articleKey'] = params['langParams'+n+'articleKey'];
    	params['langParams'][n]['articleDesc'] = params['langParams'+n+'articleDesc'];
    	params['langParams'][n]['articleContent'] = params['langParams'+n+'articleContent'];
    }
    params.id = id;
    if(params.TypeStatus == 4){
    	params.coverImg = '';
    }
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	$.post(WST.U('admin/articles/'+((id>0)?"editHelp":"addHelp")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = WST.toAdminJson(data);
		  if(json.status=='1'){
		    	WST.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=WST.U('admin/articles/help','p='+p);
		        },1000);
		  }else{
		        WST.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/articles/delHelp'),{id:id},function(data,textStatus){
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

function toBatchDel(){
	var rows = mmg.selectedRows();
	if(rows.length==0){
		WST.msg(WST.lang('require_del'),{icon:2});
		return;
	}
	var ids = [];
	for(var i=0;i<rows.length;i++){
       ids.push(rows[i]['articleId']); 
	}
	var box = WST.confirm({content:WST.lang('del_batch_tips'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/articles/delByBatchHelp'),{ids:ids.join(',')},function(data,textStatus){
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
var oldSort;
function changeSort(t,id){
   $(t).attr('ondblclick'," ");
var html = "<input type='text' id='sort-"+id+"' style='width:30px;padding:2px;' onblur='doneChange(this,"+id+")' value='"+$(t).html()+"' />";
 $(t).html(html);
 $('#sort-'+id).focus();
 $('#sort-'+id).select();
 oldSort = $(t).html();
}
function doneChange(t,id){
  var sort = ($(t).val()=='')?0:$(t).val();
  if(sort==oldSort){
    $(t).parent().attr('ondblclick','changeSort(this,'+id+')');
    $(t).parent().html(parseInt(sort));
    return;
  }
  $.post(WST.U('admin/articles/changeSort'),{id:id,catSort:sort},function(data){
    var json = WST.toAdminJson(data);
    if(json.status==1){
        $(t).parent().attr('ondblclick','changeSort(this,'+id+')');
        $(t).parent().html(parseInt(sort));
    }
  });
}