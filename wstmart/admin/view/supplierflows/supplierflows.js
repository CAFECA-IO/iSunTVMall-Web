var mmg;
function initGrid(p){
  var h = WST.pageHeight();
  var cols = [
            {title:WST.lang('step_name'), name:'flowName' ,width:150},
            {title:WST.lang('sort'), name:'sort' ,width:30, renderer: function(val,item,rowIndex){
                return '<span style="color:blue;cursor:pointer;" ondblclick="changeSort(this,'+item["flowId"]+');">'+val+'</span>';
            }},
            {title:WST.lang('is_show'), name:'isShow' ,width:50,sortable:true, renderer: function(val,item,rowIndex){
                var disabled="";
                if(item['isDelete'] == 0)disabled='disabled=disabled';
                return '<form autocomplete="off" class="layui-form" lay-filter="gridForm"><input type="checkbox" id="isShow" name="isShow" '+((item['isShow']==1)?"checked":"")+' lay-skin="switch" value="1" lay-filter="isShow" lay-text="'+WST.lang('is_show_val')+'" '+disabled+' data="'+item['flowId']+'"></form>';
            }},
            {title:WST.lang('op'), name:'' ,width:60, align:'center', renderer: function(val,item,rowIndex){
                var h="";
	            if(WST.GRANT.GRZLC_02)h += "<a  class='btn btn-blue' href='javascript:toEditFlow(" + item['flowId'] + ")'><i class='fa fa-search'></i>"+WST.lang("view")+"</a> ";
	            if(WST.GRANT.GRZLC_02)h += "<a  class='btn btn-blue' href='javascript:getForEdit(" + item['flowId'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";

	            if(WST.GRANT.GRZLC_03 && item['isDelete'] == 1)h += "<a  class='btn btn-red' href='javascript:toDel(" + item['flowId'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
	            return h;
            }}
            ];
    
    mmg = $('.mmg').mmGrid({height: h-196,indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/supplierflows/pageQuery'), fullWidthRows: true, autoLoad: false,
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
    });
    $('#headTip').WSTTips({width:200,height:35,callback:function(v){
         var diff = v?196:131;
         mmg.resize({height:h-diff})
    }});
    loadGrid(p);
}
function loadGrid(p){
    p=(p<=1)?1:p;
    mmg.load({page:p});
}

function getForEdit(id){
	 var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
     $.post(WST.U('admin/supplierflows/get'),{id:id},function(data,textStatus){
           layer.close(loading);
           var json = WST.toAdminJson(data);
           if(json.flowId){
           		WST.setValues(json);
           		toEdit(json.flowId);
           }else{
           		WST.msg(json.msg,{icon:2});
           }
    });
}

function toEdit(id){
	var title =(id==0)?WST.lang('add'):WST.lang('edit');
	var box = WST.open({title:title,type:1,content:$('#flowBox'),area: ['550px', '280px'],btn: [WST.lang('confirm'),WST.lang('cancel')],yes:function(){
			$('#flowForm').submit();
	},cancel:function(){
		//重置表单
		$('#flowForm')[0].reset();

	},end:function(){
		$('#flowBox').hide();
		//重置表单
		$('#flowForm')[0].reset();

	}});
	$('#flowForm').validator({
        fields: {
            flowName: {
            	rule:"required;",
            	msg:{required:WST.lang('shopFlows_tips19')},
            	tip:WST.lang('shopFlows_tips19'),
            	ok:"",
            }
        },
       valid: function(form){
		        var params = WST.getParams('.ipt');
		        	params.flowId = id;
		        var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		   		$.post(WST.U('admin/supplierflows/'+((id==0)?"add":"edit")),params,function(data,textStatus){
		   			  layer.close(loading);
		   			  var json = WST.toAdminJson(data);
		   			  if(json.status=='1'){
		   			    	WST.msg(WST.lang('op_ok'),{icon:1});
		   			    	$('#flowForm')[0].reset();
		   			    	layer.close(box);
                          loadGrid(WST_CURR_PAGE)
		   			  }else{
		   			        WST.msg(json.msg,{icon:2});
		   			  }
		   		});

    	}

  });
}

function toDel(id){
	var box = WST.confirm({content:WST.lang('are_you_sure_you_want_to_delete_it'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/supplierflows/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(WST.lang('op_ok'),{icon:1});
	           			    	layer.close(box);
                              loadGrid(WST_CURR_PAGE)
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
    $.post(WST.U('admin/supplierflows/changeSort'),{id:id,sort:sort},function(data){
        var json = WST.toAdminJson(data);
        if(json.status==1){
            $(t).parent().attr('ondblclick','changeSort(this,'+id+')');
            $(t).parent().html(parseInt(sort));
        }
    });
}

function toggleIsShow(t,v){
    if(!WST.GRANT.GRZLC_02)return;
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/supplierflows/editiIsShow'),{id:v,isShow:t},function(data,textStatus){
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

function toEditFlow(id){
    location.href=WST.U('admin/supplierflows/toEditFlow','id='+id);
}





		