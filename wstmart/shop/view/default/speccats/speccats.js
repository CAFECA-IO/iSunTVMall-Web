var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('specification_type'), name:'catName', width: 10},
            {title:WST.lang('commodity_classification'), name:'goodsCatNames', width: 300,renderer: function(val,item,rowIndex){
                return "<span  ><p class='wst-nowrap'>"+item['goodsCatNames']+"</p></span>";
            }},
            {title:WST.lang('speccats_tips4'), name:'isAllowImg', width: 10,renderer: function(val,item,rowIndex){
            	return (val==1)?"<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('allow')+"</span>":'';
            }},
            {title:WST.lang('display'), name:'attrVal', width: 10,renderer: function(val,item,rowIndex){
            	return '<input '+(item['shopId']==0?'disabled':'')+' type="checkbox" '+((item['isShow']==1)?"checked":"")+' id="isShow1" name="isShow1" value="1" class="ipt" lay-skin="switch" lay-filter="isShow1" data="'+item['catId']+'" lay-text="'+WST.lang("show")+'|'+WST.lang("hide")+'">'
            }},
            {title:WST.lang('source'), name:'attrVal', width: 20,renderer: function(val,item,rowIndex){
            	if(item["shopId"]>0){
            		return WST.lang('merchant_specifications');
            	}else{
            		return "<span style='color:red'>"+WST.lang('platform_specifications')+"</span>";
            	}
            }},
            {title:WST.lang('op'), name:'op' ,width:50, align:'center', renderer: function(val,item,rowIndex){
            	if(item.shopId>0){
            		var h = "";
			        if(item.specId>0){
			        	h += "<a class='btn btn-blue' href='javascript:toEdit("+ item['catId']+"," + item['id'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
			        	h += "<a class='btn btn-red' href='javascript:toDel(" + item['id'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
			            return h;
			        }else{
			        	h += "<a class='btn btn-blue' href='javascript:toEditCat(" + item['id'] + ")' ><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
			        	h += "<a class='btn btn-red' href='javascript:toDelCat(" + item['id'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
			        }
			        return h;
            	}

            }}
            ];

    mmg = $('.mmg').mmGrid({height: h-89,indexCol: true, cols: cols,method:'POST',
        url: WST.U('shop/speccats/pageQuery'), fullWidthRows: true, autoLoad: false,
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
    })
	loadGrid(p);
}
//------------------规格类型---------------//
function toEditCat(catId){
	$("select[id^='bcat_0_']").remove();
	$('#specCatsForm').get(0).reset();
	$.post(WST.U('shop/speccats/get'),{catId:catId},function(data,textStatus){
        var json = WST.toJson(data);
        WST.setValues(json);
		if(json.langs){
			for(var key in json.langs){
				WST.setValue('langParams'+key+'catName',json.langs[key]['catName']);
			}
		}
        layui.form.render();
        if(json.goodsCatId>0){
        	var goodsCatPath = json.goodsCatPath.split("_");
        	$('#bcat_0').val(goodsCatPath[0]);
        	var opts = {id:'bcat_0',val:goodsCatPath[0],childIds:goodsCatPath,className:'goodsCats'}
        	WST.ITSetGoodsCats(opts);
        }
		var title =(catId==0)?WST.lang('add'):WST.lang('edit');
		var box = WST.open({title:title,type:1,content:$('#specCatsBox'),area: ['100%', '100%'],offset: 't',btn:[WST.lang('ok'),WST.lang('cancel')],btnAlign: 'c',end:function(){$('#specCatsBox').hide();},yes:function(){
			$('#specCatsForm').submit();
		}});
		var fields = {};
		var n = 0;
		for(var i in WST.conf.sysLangs){
			n = WST.conf.sysLangs[i]['id'];
			fields['langParams'+n+'catName'] = {
				tip: WST.lang('speccats_tips1'),
				rule: WST.lang('speccats_tips2')+':required;'
			}
		}
		$('#specCatsForm').validator({
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
			    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
			    params.goodsCatId = WST.ITGetGoodsCatVal('goodsCats');
			 	$.post(WST.U('shop/speccats/'+((params.catId==0)?"add":"edit")),params,function(data,textStatus){
			 		layer.close(loading);
			    	var json = WST.toJson(data);
					if(json.status=='1'){
						WST.msg(WST.lang('op_ok'),{icon:1});
						layer.close(box);
						$('#specCatsBox').hide();
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
	var specSrc = $("#specSrc").val();
	var goodsCatPath = WST.ITGetAllGoodsCatVals('cat_0','pgoodsCats');
	mmg.load({"page":p,"specSrc":specSrc,"keyName":keyName,"goodsCatPath":goodsCatPath.join('_')});
}

function toDelCat(catId){
	var box = WST.confirm({content:WST.lang('are_you_sure_to_delete'),yes:function(){
		var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		$.post(WST.U('shop/speccats/del'),{catId:catId},function(data,textStatus){
			layer.close(loading);
			var json = WST.toJson(data);
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

function toggleIsShow( catId, isShow){
	$.post(WST.U('shop/speccats/setToggle'), {'catId':catId, 'isShow':isShow}, function(data, textStatus){
		var json = WST.toJson(data);
		if(json.status=='1'){
			WST.msg(WST.lang('op_ok'),{icon:1});
			loadGrid(WST_CURR_PAGE);
		}else{
			WST.msg(json.msg,{icon:2});
		}
	})
}

//------------------规格---------------//
function toDel(specId){
	var box = WST.confirm({content:WST.lang('are_you_sure_to_delete'),yes:function(){
		var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		$.post(WST.U('shop/specs/del'),{specId:specId},function(data,textStatus){
			layer.close(loading);
			var json = WST.toJson(data);
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


function toEdit(catId,specId){
	    $.post(WST.U('shop/specs/get'),{specId:specId},function(data,textStatus){
	    	var json = WST.toJson(data);
	    	$('#specForm').get(0).reset();
	      	WST.setValues(json);

			var title =(specId==0)?WST.lang('add'):WST.lang('edit');
			var box = WST.open({title:title,type:1,content:$('#specBox'),area: ['450px', '160px'],btn:[WST.lang('ok'),WST.lang('cancel')],yes:function(){
				$('#specForm').submit();
			}});
			$('#specForm').validator({
				rules: {
			        remote: function(el){
			        	return $.post(WST.U('shop/specs/checkSpecName'),{"specName":el.value,"catId":catId},function(data,textStatus){});
			        }
			    },
		        fields: {
		        	'specName': {rule:"required; remote;",msg:{required:WST.lang('speccats_tips1')}},
		        },
		        valid: function(form){
		    	   var params = WST.getParams('.ipt');
		    	   params.catId = catId;
		    	   var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		    	   $.post(WST.U('shop/specs/'+((specId==0)?"add":"edit")),params,function(data,textStatus){
		    		   layer.close(loading);
		    		   var json = WST.toJson(data);
		    		   if(json.status=='1'){
		    	          WST.msg(WST.lang('op_ok'),{icon:1});
		    	          layer.close(box);
		    	          loadGrid(WST_CURR_PAGE);
		    	          $('#specForm')[0].reset();
		    		   }else{
		    			   WST.msg(json.msg,{icon:2});
		    	      }
		    	    });

		    	}

			});
	});
}
