var grid = {};
function initGrid(){
	grid = $('#maingrid').WSTGridTree({
		url:WST.U('supplier/suppliercats/pageQuery'),
		rownumbers:true,
		columns: [
			{ display: WST.lang('label_supp_cats_cat_name'), name: 'catName', id:'catId', align: 'left',isSort: false},
			{ display: WST.lang('is_show'), width: 160, name: 'isShow',isSort: false,
				render: function (item)
				{
					return '<input type="checkbox" '+((item.isShow==1)?"checked":"")+' class="ipt" lay-skin="switch" lay-filter="isShow" data="'+item.catId+'" lay-text="'+WST.lang('is_show_val')+'">';
				}
			},
			{ display: WST.lang('sort')+WST.lang('double_click_edit'), name: 'catSort',width: 160,isSort: false,render:function(rowdata){
					return '<span style="color:blue;cursor:pointer;" ondblclick="changeSort(this,'+rowdata["catId"]+');">'+rowdata['catSort']+'</span>';
				}},
			{ display: WST.lang('op'), name: 'op',width: 250,isSort: false,
				render: function (rowdata,e){
					var h = "";
					if(rowdata["parentId"]==0)h += "<a class='btn btn-blue' href='javascript:toEdit("+rowdata["catId"]+",0)'><i class='fa fa-plus'></i>"+WST.lang('add')+"</a> ";
					h += "<a class='btn btn-blue' href='javascript:toEdit("+rowdata["parentId"]+","+rowdata["catId"]+")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
					h += "<a class='btn btn-red' href='javascript:delCat("+rowdata["catId"]+")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
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
	var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	$.post(WST.U('supplier/suppliercats/changeCatStatus'),{id:id,isShow:isShow},function(data,textStatus){
		layer.close(loading);
		var json = WST.toJson(data);
		if(json.status=='1'){
			WST.msg(json.msg,{icon:1});
			grid.reload(id);
		}else{
			WST.msg(json.msg,{icon:2});
		}
	});
}

function delCat(id){
	var box = WST.confirm({content:WST.lang('delete_goods_cat'),yes:function(){
		var loading = WST.msg(WST.lang('submitting_data'), {icon: 16,time:60000});
		$.post(WST.U('supplier/suppliercats/del'),{id:id},function(data,textStatus){
			layer.close(loading);
			var json = WST.toJson(data);
			if(json.status=='1'){
				WST.msg(WST.lang('op_ok'),{icon:1});
				layer.close(box);
				location.reload();
			}else{
				WST.msg(json.msg,{icon:2});
			}
		});
	}});
}

function changeCatStatus(isShow,id,pid){
	var params = {};
		params.id = id;
		params.isShow = isShow;
		params.pid = pid;
	$.post(WST.U('supplier/suppliercats/changeCatStatus'),params,function(data,textStatus){
		location.reload();
	});

}

function toEdit(pid,id){
	$('#catForm')[0].reset();
	if(id>0){
		$.post(WST.U('supplier/suppliercats/get'),{id:id},function(data,textStatus){
			var json = WST.toJson(data);
			if(json){
				WST.setValues(json);
				if(json.langs){
					for(var key in json.langs){
						WST.setValue('langParams'+key+'catName',json.langs[key]['catName']);
					}
				}
				layui.form.render();
				editsBox(id);
			}
		});
	}else{
		WST.setValues({parentId:pid,isShow:1,catSort:0});
		layui.form.render();
		editsBox(id);
	}
}

function editsBox(id){
	var title =(id>0)?WST.lang('edit'):WST.lang('add');
	var box = WST.open({title:title,type:1,content:$('#catBox'),area: ['100%', '100%'],offset: 't',btn:[WST.lang('submit'),WST.lang('cancel')],btnAlign: 'c',
		end:function(){$('#catBox').hide();},yes:function(){
			$('#catForm').submit();
		}});
	var fields = {};
	var n = 0;
	for(var i in WST.conf.sysLangs){
		n = WST.conf.sysLangs[i]['id'];
		fields['langParams'+n+'catName'] = {
			tip: WST.lang('require_supp_cats_cat_name'),
			rule: WST.lang('label_supp_cats_cat_name')+':required;length[~50];'
		}
	}
	fields['catSort'] = {
		tip: WST.lang('require_sort'),
		rule: WST.lang('sort')+':required;length[~8];'
	};
	$('#catForm').validator({
		fields: fields,
		valid: function(form){
			var params = WST.getParams('.ipt');
			var n = 0;
			params['langParams'] = {};
			for(var i in WST.conf.sysLangs){
				n = WST.conf.sysLangs[i]['id'];
				params['langParams'][n] = {};
				params['langParams'][n]['catName'] = params['langParams'+n+'catName'];
			}
			params.id = id;
			var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
			$.post(WST.U('supplier/suppliercats/'+((id>0)?"edit":"add")),params,function(data,textStatus){
				layer.close(loading);
				var json = WST.toJson(data);
				if(json.status=='1'){
					WST.msg(json.msg,{icon:1});
					$('#catBox').hide();
					layer.close(box);
					grid.reload(params.parentId);
				}else{
					WST.msg(json.msg,{icon:2});
				}
			});
		}
	});
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
	$.post(WST.U('supplier/suppliercats/editSort'),{id:id,catSort:sort},function(data){
		var json = WST.toJson(data);
		if(json.status==1){
			WST.msg(json.msg,{icon:1});
			$(t).parent().attr('ondblclick','changeSort(this,'+id+')');
			$(t).parent().html(parseInt(sort));
		}
	});
}
