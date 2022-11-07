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
            {title:WST.lang('label_loguserlogins_user_name'), name:'loginName', width: 50},
            {title:WST.lang('label_logshoplogins_login_time'), name:'loginTime' ,width:100},
            {title:WST.lang('label_logshoplogins_login_src'), name:'loginSrc' ,width:100},
            {title:WST.lang('label_logshoplogins_ip'), name:'loginIp' ,width:100}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-162,indexCol: true,indexColWidth:50,cols: cols,method:'POST',
        url: WST.U('admin/loguserlogins/pageQuery'), fullWidthRows: true, autoLoad: true,
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
	mmg.load({page:1,loginSrc:$('#loginSrc').val(),startDate:$('#startDate').val(),endDate:$('#endDate').val(),loginName:$('#loginName').val(),loginIp:$('#loginIp').val()});
}