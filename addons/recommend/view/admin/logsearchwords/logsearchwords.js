var mmg;
$(function(){
    var laydate = layui.laydate;
    laydate.render({
        elem: '#startDate'
    });
    laydate.render({
        elem: '#endDate'
    });
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('recommend_ipt_key_plo'), name:'searchWord', width: 300,sortable:true},
			{title:WST.lang('recommend_ipt_key_num'), name:'searchCnt', width: 300,sortable:true},
            {title:WST.lang('recommend_ipt_last_time'), name:'lastTime', width: 300,sortable:true},
            {title:WST.lang('recommend_op'), name:'' ,width:100, align:'center', renderer: function(val,rowdata,rowIndex){
                    var h = "";
                    if(WST.GRANT.RECOMMEND_SPSSRZ_02)h += "<a class='btn btn-blue' href='javascript:toEdit(" + rowdata['id'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('recommend_set_goods')+"</a> ";
                    return h;
                }}
            ];
    mmg = $('.mmg').mmGrid({height: h-162,indexCol: true,indexColWidth:50,cols: cols,method:'POST',
        url: WST.AU('recommend://logsearchwords/pageQuery'), fullWidthRows: true, autoLoad: true,remoteSort: true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });  
     $('#headTip').WSTTips({width:200,height:35,callback:function(v){
         var diff = v?162:135;
         mmg.resize({height:h-diff})
    }});   
})
function loadGrid(){
	mmg.load({page:1,startDate:$('#startDate').val(),endDate:$('#endDate').val(),searchWord:$('#searchWord').val()});
}

function toEdit(id){
    location.href = WST.AU('recommend://logsearchwords/toEdit',{id:id,p:WST_CURR_PAGE});
}


