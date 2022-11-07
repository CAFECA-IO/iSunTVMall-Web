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
            {title:WST.lang('accounts'), name:'loginName', width: 50},
            {title: WST.lang('operation_function'), name:'operateDesc' ,width:80,renderer: function (val,item,rowIndex){
            	return WST.TransLang(val);
            }},
            {title: WST.lang('access_path'), name:'operateUrl' ,width:200},
            {title: WST.lang('operation_IP'), name:'operateIP' ,width:70},
            {title: WST.lang('operation_time'), name:'operateTime' ,width:70},
            {title: WST.lang('transfer_parameters'), name:'op' ,width:30,renderer: function (val,item,rowIndex){
	        	return "<a  class='btn btn-blue' onclick='javascript:toView("+item['operateId']+")'><i class='fa fa-search'></i>"+WST.lang('see')+"</a>";
	        }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-162,indexCol: true,indexColWidth:50,cols: cols,method:'POST',
        url: WST.U('shop/logshopoperates/pageQuery'), fullWidthRows: true, autoLoad: true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });   
    
})
function loadGrid(){
	mmg.load({page:1,startDate:$('#startDate').val(),endDate:$('#endDate').val(),loginName:$('#loginName').val(),operateUrl:$('#operateUrl').val()});
}
function toView(id){
	 var loading = WST.msg(WST.lang("loading"), {icon: 16,time:60000});
	 $.post(WST.U('shop/logshopoperates/get'),{id:id},function(data,textStatus){
	       layer.close(loading);
	       var json = WST.toJson(data);
	       if(json.status==1){
               var content="<xmp style='white-space:normal'>"+json.data.content+"</xmp>";
	    	   $('#content').html(content);
	    	   var box = WST.open({ title:WST.lang('transfer_parameters'),type: 1,area: ['500px', '350px'],
		                content:$('#viewBox'),
		                btn:[WST.lang('close')],
		                end:function(){$('#viewBox').hide();},
		                yes: function(index, layero){
		                	layer.close(box);
		                }
	    	   });
	       }else{
	           WST.msg(json.msg,{icon:2});
	       }
	 });
}