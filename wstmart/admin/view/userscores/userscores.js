var mmg;
function gridInit(id){
    var laydate = layui.laydate;
    laydate.render({
        elem: '#startDate'
    });
    laydate.render({
        elem: '#endDate'
    });
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_user_score_src'), name:'dataSrc', width: 60},
            {title:WST.lang('label_user_score_data_src'), name:'dataSrc',width: 60,renderer: function (val,item,rowIndex){
                if(item['scoreType']==1){
                    return '<font color="red">+'+item['score']+'</font>';
                }else{
                    return '<font color="green">-'+item['score']+'</font>';
                }
            }},
            {title:WST.lang('label_user_score_remark'), name:'dataRemarks',width: 60},
            {title:WST.lang('label_user_score_date'), name:'createTime',width: 40}
            ];

    mmg = $('.mmg').mmGrid({height: (h-120),indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/userscores/pageQuery','id='+id), fullWidthRows: true, autoLoad: true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
}
function loadGrid(id){
	mmg.load({page:1,id:id,startDate:$('#startDate').val(),endDate:$('#endDate').val()});
}
var w;
function toAdd(id){
	var ll = WST.msg(WST.lang('loading'));
	$.post(WST.U('admin/userscores/toAdd',{id:id}),{},function(data){
		layer.close(ll);
		w =WST.open({type: 1,title:WST.lang("user_score_change_user_score"),shade: [0.6, '#000'],offset:'50px',border: [0],content:data,area: ['550px', '380px'],success:function(){
            layui.form.render();
        }});
	});
}
function editScore(){
	var params = WST.getParams('.ipt');
	var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/userscores/add'),params,function(data,textStatus){
    	layer.close(loading);
    	var json = WST.toAdminJson(data);
    	if(json.status=='1'){
    		WST.msg(WST.lang("op_ok"),{icon:1});
    		closeFrom();
    		loadGrid(params.userId);
    	}else{
    		WST.msg(json.msg,{icon:2});
    	}
    });
}
function closeFrom(){
    layer.close(w);
}
