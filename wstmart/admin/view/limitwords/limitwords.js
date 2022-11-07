var mmg;
$(function(){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_limitwords_word'), name:'word', width: 300},
            {title:WST.lang('create_time'), name:'createTime', width: 300},
            {title:WST.lang('op') , width: 300,name:'status', renderer:function(val,item,rowIndex){
                    var h = "";
                    if(WST.GRANT.XTJYGJZ_02)h += "<a class='btn btn-blue' onclick='javascript:toEdit("+item.id+")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                    if(WST.GRANT.XTJYGJZ_03)h += "<a class='btn btn-red' onclick='javascript:toDel("+item.id+")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                    return h;
                }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-162,indexCol: true,indexColWidth:50,cols: cols,method:'POST',
        url: WST.U('admin/limitWords/pageQuery'), fullWidthRows: true, autoLoad: true,
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
	mmg.load({page:1,word:$('#limitword').val()});
}

function toEdit(id){
    $('#limitWordForm')[0].reset();
    if(id>0){
        $.post(WST.U('admin/limitwords/get'),{id:id},function(data,textStatus){
            var json = WST.toAdminJson(data);
            if(json){
                WST.setValues(json);
                layui.form.render();
                editsBox(id);
            }
        });
    }else{
        WST.setValues({word:''});
        layui.form.render();
        editsBox(id);
    }
}

function editsBox(id,v){
    var title =(id>0)?WST.lang('edit'):WST.lang('add');
    var box = WST.open({title:title,type:1,content:$('#limitWordBox'),area: ['500px', '200px'],btn:[WST.lang('submit'),WST.lang('cancel')],
        end:function(){$('#limitWordBox').hide();},yes:function(){
            $('#limitWordForm').submit();
        }});
    $('#limitWordForm').validator({
        fields: {
            word: {
                tip: WST.lang('require_limitwords_word'),
                rule: WST.lang('label_limitwords_word')+':required;length[~50];'
            },
        },
        valid: function(form){
            var params = WST.getParams('.ipt');
            params.id = id;
            var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
            $.post(WST.U('admin/limitwords/'+((id>0)?"edit":"add")),params,function(data,textStatus){
                layer.close(loading);
                var json = WST.toAdminJson(data);
                if(json.status=='1'){
                    WST.msg(json.msg,{icon:1});
                    layer.close(box);
                    setTimeout(function(){
                        loadGrid(WST_CURR_PAGE);
                    },1000);
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            });
        }
    });
}

function toDel(id){
    var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
            var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
            $.post(WST.U('admin/limitwords/del'),{id:id},function(data,textStatus){
                layer.close(loading);
                var json = WST.toAdminJson(data);
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