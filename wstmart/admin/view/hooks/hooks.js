var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_hook_name'), name:'name', width: 60},
            {title:WST.lang('label_hook_desc'), name:'hookRemarks', width: 300},
            {title:WST.lang('label_hook_addon'), name:'addons' ,width:70, align:'center'},
            {title:WST.lang('op'), name:'op' ,width:20, align:'center',renderer: function(val,item,rowIndex){
                if(item['addons']!=''){
                    return '<a class="btn btn-blue btn-mright" href="javascript:hookBox('+item['hookId']+',\''+item['addons']+'\')"><i class="fa fa-search"></i>'+WST.lang('label_hook_sort')+'</a>';
                }
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-89,indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/hooks/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    hooksQuery(p);
}

function hookBox(id,addons){
    addons = addons.replace(',,',',');
    var str = addons.split(',');
    var h = (str.length<=2)?220:(str.length-2)*15+220;
    h = (h>500)?500:h;
    var html = ['<table class="hook">'];
    for(var i=0;i<str.length;i++){
        html.push('<tr><td width="60%" class="hookval" val="'+str[i]+'">'+str[i]+'</td><td><button type="button" class="btn btn-primary btn-mright" onclick="javascript:moveUp(this)">'+WST.lang('label_hook_up')+'</button><button type="button" class="btn btn-primary btn-mright" onclick="javascript:moveDown(this)">'+WST.lang('label_hook_down')+'</button></td></tr>')
    }
    html.push('</table>');
    var w=WST.open({
        type: 1,
        title:WST.lang('label_hook_sort_title'),
        content:html.join(''),shade: [0.6, '#000'],
        area: ['400px', h+'px'],
        btn: [WST.lang('submit')],
        yes: function(index, layero){
            var hook = [];
            $('.hookval').each(function(){
                hook.push($(this).attr('val'));
            });
            if(hook.length<=1){
                WST.msg(WST.lang('label_hook_no_need_sort'), {icon: 2});   
                return; 
            }
            var ll = WST.msg(WST.lang('loading'));
            $.post(WST.U('admin/hooks/changgeHookOrder'),{id:id,hook:hook.join(',')},function(data){
                layer.close(w);
                layer.close(ll);
                var json = WST.toAdminJson(data);
                if(json.status>0){
                    WST.msg(json.msg, {icon: 1});
                    hooksQuery();
                }else{
                    WST.msg(json.msg, {icon: 2});
                }
            });
        }
    });
}
function moveUp(obj){
    var tr = $(obj).parents("tr");
    if (tr.index() != 0)tr.prev().before(tr);
}
function moveDown(obj){
    var down = $(obj).parents("tr").parent();
    var len = down.children().size();
    var tr = $(obj).parents("tr");
    if (tr.index() != len - 1)tr.next().after(tr);
}
//查询
function hooksQuery(p){
    p=(p<=1)?1:p;
	var query = WST.getParams('.query');
    query.page = p;
	mmg.load(query);
}

