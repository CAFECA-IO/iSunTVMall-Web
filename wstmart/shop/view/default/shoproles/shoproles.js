var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('role_name'), name:'roleName' ,width:300,sortable:true},
        {title:WST.lang('creation_time'), name:'createTime' ,width:300,sortable:true},
        {title:WST.lang('op'), name:'' ,width:100, align:'center', renderer: function(val,item,rowIndex){
            var h = "";
            h += "<a  class='btn btn-blue' onclick='javascript:toEdit("+item['id']+")'><i class='fa fa-pencil'>"+WST.lang('edit')+"</a>";
            h += "<a  class='btn btn-red' onclick='javascript:del(" + item['id'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
            return h;
        }}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',checkCol:true,multiSelect:true,
        url: WST.U('shop/shoproles/pageQuery'), fullWidthRows: true, autoLoad: false,remoteSort: true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}

function loadGrid(p){
	p=(p<=1)?1:p;
    mmg.load({roleName:$('#roleName').val(),page:p});
}

function toEdit(id){
	location.href = WST.U('shop/shoproles/edit','id='+id+'&p='+WST_CURR_PAGE);
}
function toAdd(){
    location.href = WST.U('shop/shoproles/add','p='+WST_CURR_PAGE);
}

/**保存角色数据**/
function save(p){
	$('#shoprole').isValid(function(v){
		if(v){
			var params = WST.getParams('.ipt');
			var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		    $.post(WST.U('shop/shoproles/'+((params.id==0)?"toAdd":"toEdit")),params,function(data,textStatus){
		    	layer.close(loading);
		    	var json = WST.toJson(data);
		    	if(json.status=='1'){
		    		WST.msg(json.msg,{icon:1},function(){
						location.href=WST.U('shop/shoproles/index',"p="+p);
					});
		    	}else{
		    		WST.msg(json.msg,{icon:2});
		    	}
		    });
		}
	});
}
//删除角色
function del(id){
	var c = WST.confirm({content:WST.lang('are_you_sure_to_delete'),yes:function(){
		layer.close(c);
		var load = WST.load({msg:WST.lang('loading')});
		$.post(WST.U('shop/shoproles/del'),{id:id},function(data,textStatus){
			layer.close(load);
		    var json = WST.toJson(data);
		    if(json.status==1){
		    	WST.msg(json.msg,{icon:1});
                loadGrid(WST_CURR_PAGE);
		    }else{
		    	WST.msg(json.msg,{icon:2});
		    }
		});
	}});
}