var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_attr_name'), name:'attrName', width: 10},
            {title:WST.lang('label_attr_cat'), name:'goodsCatNames', width: 160},
            {title:WST.lang('label_attr_type'), name:'attrType', width: 5,renderer: function(val,item,rowIndex){
            	return (val==1)?WST.lang('label_attr_type1'):(val==2?WST.lang('label_attr_type2'):WST.lang('label_attr_type3'));
            }},
            {title:WST.lang('label_attr_opts'), name:'attrVal', width: 260},
            {title:WST.lang('is_show'), name:'attrVal', width: 20,renderer: function(val,item,rowIndex){
            	return '<input type="checkbox" '+((item['isShow']==1)?"checked":"")+' id="isShow1" name="isShow1" value="1" class="ipt" lay-skin="switch" lay-filter="isShow1" data="'+item['attrId']+'" lay-text="'+WST.lang('is_show_val')+'">'
            }},
            {title:WST.lang('sort'), name:'attrSort', width: 5},
            {title:WST.lang('op'), name:'' ,width:80, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
	        	if(WST.GRANT.SPSX_02)h += "<a class='btn btn-blue' href='javascript:toEdit("+ item['attrId']+")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
	        	if(WST.GRANT.SPSX_03)h += "<a class='btn btn-red' href='javascript:toDel(" + item['attrId'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
	            return h;
            }}
            ];

    mmg = $('.mmg').mmGrid({height: h-89,indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/attributes/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    mmg.on('loadSuccess',function(data){
    	layui.form.render();
    	layui.form.on('switch(isShow1)', function(data){
           var id = $(this).attr("data");
		   if(this.checked){
			   toggleIsShow(id, 1);
		   }else{
			   toggleIsShow(id, 0);
		   }
		});
    });
    loadGrid(p);
}

//------------------属性类型---------------//
function toEdit(attrId){
	$("select[id^='bcat_0_']").remove();
	$('#attrForm').get(0).reset();
	$.post(WST.U('admin/attributes/get'),{attrId:attrId},function(data,textStatus){
        var json = WST.toAdminJson(data);
        WST.setValues(json);
		if(json.langs){
			for(var key in json.langs){
				WST.setValue('langParams'+key+'attrName',json.langs[key]['attrName']);
			}
		}
        changeArrType(json.attrType);
        layui.form.render();
        if(json.goodsCatId>0){
        	var goodsCatPath = json.goodsCatPath.split("_");
        	$('#bcat_0').val(goodsCatPath[0]);
        	var opts = {id:'bcat_0',val:goodsCatPath[0],childIds:goodsCatPath,className:'goodsCats'}
        	WST.ITSetGoodsCats(opts);
        }
		var title =(attrId==0)?WST.lang('add'):WST.lang('add');
		var box = WST.open({title:title,type:1,content:$('#attrBox'),area: ['100%', '100%'],offset: 't',btn:[WST.lang('submit'),WST.lang('cancel')],btnAlign: 'c',end:function(){$('#attrBox').hide();},yes:function(){
			$('#attrForm').submit();
		}});
		var fields = {};
		var n = 0;
		for(var i in WST.conf.sysLangs){
			n = WST.conf.sysLangs[i]['id'];
			fields['langParams'+n+'attrName'] = {
				tip: WST.lang('require_attr_name'),
				rule: WST.lang('label_attr_name')+':required;'
			}
		}
		fields['attrVal'] = {
			rule: 'required(attrType)'
		};
		$('#attrForm').validator({
			rules: {
				attrType: function() {
		            return ($('#attrType').val()!='0');
		        }
		    },
			fields: fields,
			valid: function(form){
			    var params = WST.getParams('.ipt');
				var n = 0;
				params['langParams'] = {};
				for(var i in WST.conf.sysLangs){
					n = WST.conf.sysLangs[i]['id'];
					params['langParams'][n] = {};
					params['langParams'][n]['attrName'] = params['langParams'+n+'attrName'];
				}
			    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
			    params.goodsCatId = WST.ITGetGoodsCatVal('goodsCats');
			 	$.post(WST.U('admin/attributes/'+((params.attrId==0)?"add":"edit")),params,function(data,textStatus){
			 		layer.close(loading);
			    	var json = WST.toAdminJson(data);
					if(json.status=='1'){
						WST.msg(json.msg,{icon:1});
						layer.close(box);
						$('#attrBox').hide();
						loadGrid(WST_CURR_PAGE);
						layer.close(box);
				  	}else{
				    	WST.msg(json.msg,{icon:2});
					}
			 	});
			}
		});

	});
}
function loadGrid(p){
	p=(p<=1)?1:p;
	var keyName = $("#keyName").val();
	var goodsCatPath = WST.ITGetAllGoodsCatVals('cat_0','pgoodsCats');
	mmg.load({"page":p,"keyName":keyName,"goodsCatPath":goodsCatPath.join('_')});
}

function toDel(attrId){
	var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
		var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		$.post(WST.U('admin/attributes/del'),{attrId:attrId},function(data,textStatus){
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

function toggleIsShow( attrId, isShow){
	$.post(WST.U('admin/attributes/setToggle'), {'attrId':attrId, 'isShow':isShow}, function(data, textStatus){
		var json = WST.toAdminJson(data);
		if(json.status=='1'){
			WST.msg(json.msg,{icon:1});
			loadGrid(WST_CURR_PAGE);
		}else{
			WST.msg(json.msg,{icon:2});
		}
	})
}

function changeArrType(v){
	if(v>0){
		$('#attrValTr').show();
	}else{
		$('#attrValTr').hide();
	}
}
