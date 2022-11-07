var mmg;

function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('wstim_username'), name:'loginName', width: 150},
            {title:WST.lang('wstim_business_name'), name:'shopName', width: 100},
            {title:WST.lang('wstim_date'), name:'createTime', width: 30,},
            {title:WST.lang('wstim_op'), name:'' ,width:100, align:'center', renderer: function(val,rowdata,rowIndex){
	            var h = "<a class='btn btn-default' target='_blank' onclick='viewDetail("+rowdata['userId']+","+rowdata['shopId']+",\""+rowdata['loginName']+"\")'> <i class='fa fa-search'></i>"+WST.lang('wstim_cat')+"</a> ";
	            return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-82,indexCol: true, indexColWidth:50, cols: cols,method:'POST',
        url: APIS['adminDialogQuery'], fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function loadGrid(p){
	var params = WST.imGetParams('.j-ipt');
    p=(p<=1)?1:p;
    params.page=p;
	mmg.load(params);
}
var viewDetail = function(userId,shopId,uname){
    var tips = layer.load({msg:WST.lang('wstim_loading')});
    $.post(APIS['adminDialogPage'],{id:userId,shopId:shopId,uname:uname},function(data,textStatus){
        layer.close(tips);
        WST.imOpen({
            type: 1,
            title:[WST.lang('wstim_msg1')+'（'+uname+'）', 'background-color:#2cabe2;color:#fff;'],
            shade: [0.6, '#000'],
            border: [0],
            content: data.replace('#@userId@#',userId).replace('#@shopId@#',shopId),
            area: [(WST.pageWidth()*0.9)+'px', (WST.pageHeight()*0.9)+'0px'],
            offset: '10px',
            fixed:true,
        });
    });
}