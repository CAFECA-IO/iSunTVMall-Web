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
            {title:WST.lang('label_sms_user_id'), name:'smsUserId', width: 10},
            {title:WST.lang('label_sms_content'), name:'smsContent' ,width:120},
            {title:WST.lang('label_sms_code'), name:'smsCode' ,width:50},
            {title:WST.lang('label_sms_send_type'), name:'smsFunc' ,width:80},
            {title:WST.lang('label_sms_user_phone'), name:'smsPhoneNumber' ,width:50},
            {title:WST.lang('label_sms_send_ip'), name:'smsIP' ,width:60},
            {title:WST.lang('label_sms_send_time'), name:'createTime' ,width:70},
            {title:WST.lang('label_sms_back_code'), name:'smsReturnCode' ,width:130}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-162,indexCol: true,indexColWidth:50,cols: cols,method:'POST',
        url: WST.U('admin/logsms/pageQuery'), fullWidthRows: true, autoLoad: true,
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
	mmg.load({page:1,startDate:$('#startDate').val(),endDate:$('#endDate').val(),phone:$('#phone').val()});
}
