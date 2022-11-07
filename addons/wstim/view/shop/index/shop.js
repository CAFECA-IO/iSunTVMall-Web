var mmg;
function initGrid(p){
	var laydate = layui.laydate;
    laydate.render({
        elem: '#startDate'
    });
    laydate.render({
        elem: '#endDate'
    });
   var h = WST.pageHeight();
   var cols = [
        {title:WST.lang('wstim_username'), name:'loginName', width: 300},
        {title:WST.lang('wstim_date'), name:'createTime',width: 150},
        {title:WST.lang('wstim_op'), name:'' ,width:110,renderer:function(val,item,rowIndex){
            var html = [];
            html.push("<a class='fa fa-search btn btn-default' onclick='javascript:view("+item["userId"]+",\""+item['loginName']+"\")'><i class='fa fa-search'></i>"+WST.lang('wstim_cat')+"</a>");
            return html.join('');
        }}
    ];

    mmg = $('.mmg').mmGrid({height: h-82,indexCol: true, cols: cols,method:'POST',nowrap:true,
        url: APIS['shopGetDialogs'], fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}

function loadGrid(p){
    var params = {};
    params = WST.imGetParams('.s-query');
    p=(p<=1)?1:p;
    params.page=p;
    mmg.load(params);
}

function view(id,uname){
    var tips = WST.imLoad({msg:WST.lang('wstim_loading')});
	$.post(APIS['shopDialogPage'],{id:id,uname:uname},function(data,textStatus){
		layer.close(tips);
		WST.imOpen({
		    type: 1,
		    title:[WST.lang('wstim_msg1')+'（'+uname+'）', 'background-color:#e23e3d;color:#fff;'],
		    shade: [0.6, '#000'],
		    border: [0],
		    content: data.replace('#@userId@#',id),
		    area: [(WST.pageWidth()*0.9)+'px', (WST.pageHeight()*0.9)+'0px'],
            offset: '10px',
		    fixed:true,
		});
	});
}

