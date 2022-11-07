var mmg;
$(function(){
    var laydate = layui.laydate;
    laydate.render({
        elem: '#startDate'
    });
    laydate.render({
        elem: '#endDate'
    });
    layer.photos({
        photos: '.feedback-content-gallery',
        area: ['20%','auto']
    });
})
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
        {title: WST.lang('label_feedback_user'), name:'userName' ,width:30,sortable:true},
        {title: WST.lang('label_feedback_type'), name:'feedbackType' ,width:30,sortable:true},
        {title: WST.lang('label_feedback_content'), name:'feedbackContent' ,width:330,sortable:true},
        {title: WST.lang('label_feedback_tel'), name:'contactInfo' ,width:65,sortable:true},
        {title: WST.lang('create_time'), name:'createTime' ,width:100,sortable:true},
        {title: WST.lang('label_feedback_status'), name:'feedbackStatus' ,width:30,sortable:true,renderer: function(val,item,rowIndex){
            if(item['feedbackStatus']==0){
                return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+item['feedbackStatusName']+"</span>";
            }else{
                return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+item['feedbackStatusName']+"</span>";
            }
        }},
        {title:WST.lang('op'), name:'' ,width:100, align:'center', renderer: function(val,item,rowIndex){
            var h = "";
            if(item['feedbackStatus'] == 0){
                if(WST.GRANT.GNFK_02)h += "<a  class='btn btn-blue' onclick='javascript:toEdit("+item['feedbackId']+")'><i class='fa fa-pencil'></i>"+WST.lang('label_feedback_reply')+"</a> ";
            }else{
                h += "<a  class='btn btn-blue' onclick='javascript:toEdit("+item['feedbackId']+")'><i class='fa fa-search'></i>"+WST.lang('view')+"</a> ";
            }
            if(WST.GRANT.GNFK_03)h += "<a  class='btn btn-red' onclick='javascript:toDel(" + item['feedbackId'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('view')+"</a> ";
            return h;
        }}
    ];

    mmg = $('.mmg').mmGrid({height: (h-85),indexCol: true, cols: cols,method:'POST',nowrap:true,
        url: WST.U('admin/feedbacks/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}

function loadGrid(p){
    p=(p<=1)?1:p;
    mmg.load({page:p,key:$('#key').val(),feedbackContent:$('#feedbackContent').val(),feedbackType:$('#feedbackType').val(),startDate:$('#startDate').val(),endDate:$('#endDate').val()});
}

function toDel(id){
    var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
        var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
        $.post(WST.U('admin/feedbacks/del'),{feedbackId:id},function(data,textStatus){
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

function toEdit(id){
    location.href=WST.U('admin/feedbacks/toEdit','feedbackId='+id+'&p='+WST_CURR_PAGE);
}

function toEdits(id,p){
    var params = WST.getParams('.ipt');
    params.feedbackId = id;
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/feedbacks/edit'),params,function(data,textStatus){
        layer.close(loading);
        var json = WST.toAdminJson(data);
        if(json.status=='1'){
            WST.msg(json.msg,{icon:1});
            setTimeout(function(){
                location.href=WST.U('admin/feedbacks/index','p='+p);
            },1000);
        }else{
            WST.msg(json.msg,{icon:2});
        }
    });
}