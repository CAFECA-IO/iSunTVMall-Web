var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('group_name'), name:'groupName' ,width:300,sortable:true},
        {title:WST.lang('sort'), name:'groupOrder' ,width:200,sortable:true},
        {title:WST.lang('minimum_consumption'), name:'minMoney' ,width:300,sortable:true},
        {title:WST.lang('maximum_consumption'), name:'maxMoney' ,width:300,sortable:true},
        {title:WST.lang('op'), name:'' ,width:100, align:'center', renderer: function(val,item,rowIndex){
            var h = "";
            h += "<a  class='btn btn-blue' onclick='javascript:getForEdit("+item['id']+")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
            h += "<a  class='btn btn-red' onclick='javascript:del(" + item['id'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
            return h;
        }}
    ];

    mmg = $('.mmg').mmGrid({height: h-85,indehxCol: true, cols: cols,method:'POST',
        url: WST.U('shop/shopmembergroups/pageQuery'), fullWidthRows: true, autoLoad: false,remoteSort: true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}

function loadGrid(p){
	p=(p<=1)?1:p;
    mmg.load({groupName:$('#groupName2').val(),page:p});
}

function getForEdit(id){
	 var loading = WST.msg(WST.lang("loading"), {icon: 16,time:60000});
     $.post(WST.U('shop/shopmembergroups/get'),{id:id},function(data,textStatus){
           layer.close(loading);
           var json = WST.toJson(data);
           if(json.id){
           	  WST.setValues(json);
              toEdit(json.id);
           }else{
           		WST.msg(json.msg,{icon:2});
           }
    });
}
function toEdit(id){
	if(id==0){
      title = WST.lang('add');

  }else{
      title = WST.lang('edit');
  }
var box = WST.open({title:title,type:1,content:$('#editBox'),area: ['500px', '270px'],
		btn:[WST.lang('ok'),WST.lang('cancel')],end:function(){$('#editBox').hide();},yes:function(){
		$('#editForm').submit();
	},cancel:function () {
            $('#editForm')[0].reset();
        },btn2: function() {
            $('#editForm')[0].reset();
        },});
	$('#editForm').validator({
        fields: {
            groupName: {
            	rule:"required;",
            	msg:{required:WST.lang('msg_shopMemBergroups1')},
            	tip:WST.lang('tip_shopMemBergroups1'),
            	ok:""
            },
            minMoney: {
                rule:"required;",
                msg:{required:WST.lang('msg_shopMemBergroups2')},
                tip:WST.lang('tip_shopMemBergroups2'),
                ok:""
            },
            maxMoney: {
                rule:"required;",
                msg:{required:WST.lang('msg_shopMemBergroups3')},
                tip:WST.lang('tip_shopMemBergroups3'),
                ok:""
            }
        },
       valid: function(form){
		        var params = WST.getParams('.ipt');
	                params.id = id;
	                var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           		$.post(WST.U('shop/shopmembergroups/'+((id==0)?"add":"edit")),params,function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(WST.lang('op_ok'),{icon:1});
	           			    	$('#editBox').hide();
	           			    	$('#editForm')[0].reset();
	           			    	layer.close(box);
                              loadGrid(WST_CURR_PAGE);
	           			  }else{
	           			        WST.msg(json.msg,{icon:2});
	           			  }
	           		});

    	}

  });

}
//删除
function del(id){
	var c = WST.confirm({content:WST.lang('are_you_sure_to_delete'),yes:function(){
		layer.close(c);
		var load = WST.load({msg:WST.lang('loading')});
		$.post(WST.U('shop/shopmembergroups/del'),{id:id},function(data,textStatus){
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

//设置分组
function setGroup(){

}
