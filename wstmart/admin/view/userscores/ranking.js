var mmg;
function gridInit(id){
    var laydate = layui.laydate;
    laydate.render({
        elem: '#month',
        type: 'month'
    });
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_user_score_user'), name:'userName', width: 160,renderer: function (val,item,rowIndex){
                return '<img src="'+item['userPhoto']+'" height="50"/>'+"【"+item['loginName']+"】"+WST.blank(item['userName']);
            }},
            {title:WST.lang('label_user_score_sign_time'), name:'createTime',width: 160},
            {title:WST.lang('label_user_score_continue_sign'), name:'dataId',width: 60},
            {title:WST.lang('label_user_score_sign_num'), name:'signCount',width: 40}
            ];

    mmg = $('.mmg').mmGrid({height: (h-87),indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/userscores/pageQueryByRanking'), fullWidthRows: true, autoLoad: true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
}
function loadGrid(id){
	mmg.load({page:1,month:$('#month').val()});
}
