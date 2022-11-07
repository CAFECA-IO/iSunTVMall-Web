var grid,oldData = {};
function initGrid(){
	grid = $('#maingrid').WSTGridTree({
		url:WST.U('admin/articlecats/pageQuery'),
		rownumbers:true,
        columns: [
	        { display: WST.lang('label_article_cat_name'), name: 'catName', id:'catId', align: 'left',isSort: false},
            { display: WST.lang('label_article_cat_type'), width: 160, name: 'catType',isSort: false,
                render: function (item)
                {
                    if (parseInt(item.catType) == 1) return '<span>'+WST.lang('article_cat_type1')+'</span>';
                    return '<span>'+WST.lang('article_cat_type2')+'</span>';
                }
            },
            { display: WST.lang('is_show'), width: 160, name: 'isShow',isSort: false,
                render: function (item)
                {
                    return '<input type="checkbox" '+((item.isShow==1)?"checked":"")+' class="ipt" lay-skin="switch" lay-filter="isShow" data="'+item.catId+'" lay-text="'+WST.lang('is_show_val')+'">';
                }
            },
	        { display: WST.lang('sort'), name: 'catSort',width: 160,isSort: false},
	        { display: WST.lang('op'), name: 'op',width: 250,isSort: false,
	        	render: function (rowdata,e){
		            var h = "";
			        if(WST.GRANT.WZFL_01)h += "<a class='btn btn-blue' href='javascript:toEdit("+rowdata["catId"]+",0)'><i class='fa fa-plus'></i>"+WST.lang('article_cat_add_cat')+"</a> ";
		            if(WST.GRANT.WZFL_02)h += "<a class='btn btn-blue' href='javascript:toEdit("+rowdata["parentId"]+","+rowdata["catId"]+")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
		            if(WST.GRANT.WZFL_03 && rowdata["catType"]==0)h += "<a class='btn btn-red' href='javascript:toDel("+rowdata["parentId"]+","+rowdata["catId"]+","+rowdata["catType"]+")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> "; 
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
   $('#headTip').WSTTips({width:200,height:35,callback:function(v){}});
   $('body').css('overflow-y','auto');
}
function toggleIsShow(id,isShow){
	if(!WST.GRANT.WZFL_02)return;
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/articlecats/editiIsShow'),{id:id,isShow:isShow},function(data,textStatus){
		layer.close(loading);
	    var json = WST.toAdminJson(data);
		if(json.status=='1'){
			WST.msg(json.msg,{icon:1});
			grid.reload(id);
		}else{
			WST.msg(json.msg,{icon:2});
		}
	});
}

function toEdit(pid,id){
	$('#articlecatForm')[0].reset();
	if(id>0){
		$.post(WST.U('admin/articlecats/get'),{id:id},function(data,textStatus){
			var json = WST.toAdminJson(data);
			if(json){
				WST.setValues(json);
				if(json.langs){
					for(var key in json.langs){
						WST.setValue('langParams['+key+'][catName]',json.langs[key]['catName']);
					}
				}
				layui.form.render();
				editsBox(id);
			}
		});
	}else{
		WST.setValues({parentId:pid,catName:'',isShow:1,catSort:0});
		layui.form.render();
		editsBox(id);
	}
}

function editsBox(id){
	var title =(id>0)?WST.lang('edit'):WST.lang('add');
	var box = WST.open({title:title,type:1,content:$('#articlecatBox'),area: ['100%', '100%'],offset: 't',btn:[WST.lang('submit'),WST.lang('cancel')],btnAlign: 'c',
		end:function(){$('#articlecatBox').hide();},yes:function(){
		          $('#articlecatForm').submit();
	          }});
	$('#articlecatForm').validator({
	    fields: {
	    	catName: {
	    		tip: WST.lang('require_article_cat_name'),
	    		rule: WST.lang('label_article_cat_name')+':required;length[~10];'
	    	},
	    	catSort: {
            	tip: WST.lang('require_sort'),
            	rule: WST.lang('sort')+':required;length[~8];'
            }
	    },
	    valid: function(form){
	        var params = WST.getParams('.ipt');
	        params.id = id;
	        var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    		$.post(WST.U('admin/articlecats/'+((id>0)?"edit":"add")),params,function(data,textStatus){
    			  layer.close(loading);
    			  var json = WST.toAdminJson(data);
    			  if(json.status=='1'){
    			    	WST.msg(json.msg,{icon:1});
    			    	$('#articlecatBox').hide();
    			    	layer.close(box);
    		            grid.reload(params.parentId);
    			  }else{
    			        WST.msg(json.msg,{icon:2});
    			  }
    		});
	    }
	});
}

function toDel(pid,id,type){
	var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/articlecats/del'),{id:id,type:type},function(data,textStatus){
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