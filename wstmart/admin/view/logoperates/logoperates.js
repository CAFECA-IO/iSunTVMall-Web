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
            {title:WST.lang('label_logoperates_staff_name_plo'), name:'staffName', width: 50},
            {title:WST.lang('label_logoperates_module'), name:'operateDesc' ,width:80,renderer: function (val,item,rowIndex){
	        	return WST.TransLang(item['menuName'])+"-"+WST.TransLang(item['operateDesc']);
	        }},
            {title:WST.lang('label_logoperates_visit_plo'), name:'operateUrl' ,width:200},
            {title:WST.lang('label_logoperates_ip'), name:'operateIP' ,width:70},
            {title:WST.lang('label_logoperates_time'), name:'operateTime' ,width:70},
            {title:WST.lang('label_logoperates_content'), name:'op' ,width:30,renderer: function (val,item,rowIndex){
	        	return "<a  class='btn btn-blue' onclick='javascript:toView("+item['operateId']+")'><i class='fa fa-search'></i>"+WST.lang('view')+"</a>";
	        }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-162,indexCol: true,indexColWidth:50,cols: cols,method:'POST',
        url: WST.U('admin/logoperates/pageQuery'), fullWidthRows: true, autoLoad: true,
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
	mmg.load({page:1,startDate:$('#startDate').val(),endDate:$('#endDate').val(),staffName:$('#staffName').val(),operateUrl:$('#operateUrl').val()});
}
function toView(id){
	 var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	 $.post(WST.U('admin/logoperates/get'),{id:id},function(data,textStatus){
	       layer.close(loading);
	       var json = WST.toAdminJson(data);
	       if(json.status==1){
               var content="<xmp style='white-space:normal'>"+json.data.content+"</xmp>";
	    	   $('#content').html(content);
	    	   var box = WST.open({ title:WST.lang('label_logoperates_content'),type: 1,area: ['500px', '350px'],
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