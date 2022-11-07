var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('cron_task_name'), name:'cronName', width: 80},
            {title:WST.lang('cron_task_desc'), name:'cronDesc', width: 150},
            {title:WST.lang('cron_last_run_time'), name:'runTime', width: 70, renderer: function(val,item,rowIndex){
            	return (item['runTime']==0)?'-':item['runTime'];
            }},
            {title:WST.lang('cron_execution_status'), name:'isEnable', width: 20, renderer: function(val,item,rowIndex){
            	return (item['isRunSuccess']==1)?'<span class="statu-yes"><i class="fa fa-check-circle"></i> '+WST.lang('cron_success')+'</span>':'<span class="statu-no"><i class="fa fa-times-circle"></i> '+WST.lang('cron_fail')+'</span>';
            }},
            {title:WST.lang('cron_next_run_time'), name:'nextTime', width: 70, renderer: function(val,item,rowIndex){
            	return (item['nextTime']==0)?'-':item['nextTime'];
            }},
            {title:WST.lang('cron_auchor'), name:'auchor', width: 20, renderer: function(val,item,rowIndex){
            	return !item['author']?'':'<a href="'+item['authorUrl']+'" target="_blank">'+item['author']+'</a>';
            }},
            {title:WST.lang('cron_plan_status'), name:'isEnable', width: 20, renderer: function(val,item,rowIndex){
            	return (item['isEnable']==1)?'<span class="statu-yes"><i class="fa fa-check-circle"></i> '+WST.lang('cron_enable')+'</span>':'<span class="statu-wait"><i class="fa fa-ban"></i> '+WST.lang('cron_disable')+'</span>';
            }},
            {title:WST.lang('cron_operation'), name:'' ,width:180, align:'center', renderer: function(val,item,rowIndex){
                var h="";
	            if(WST.GRANT.CRON_JHRW_04){
	            	h += "<a class='btn btn-blue' href='javascript:toEdit(" + item['id'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('cron_update')+"</a> ";
	            	if(item['isEnable']==0){
	            	    h += "<a class='btn btn-green' href='javascript:changgeEnableStatus(" + item['id'] + ",1)'><i class='fa fa-check'></i>"+WST.lang('cron_enable')+"</a> "; 
		            }else{
		            	h += "<a class='btn btn-red' href='javascript:changgeEnableStatus(" + item['id'] + ",0)'><i class='fa fa-ban'></i>"+WST.lang('cron_disable')+"</a> "; 
		                h += '<a class="btn btn-blue" href="javascript:run(\'' + item['id'] + '\')"><i class="fa fa-refresh"></i>'+WST.lang('cron_run')+'</a> ';
		            }
		            if(!item['cronCode'])h += '<a class="btn btn-blue" href="javascript:del(\'' + item['id'] + '\')"><i class="fa fa-refresh"></i>'+WST.lang('cron_del')+'</a>';
	            } 
	            return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-162,indexCol: true, cols: cols,method:'POST',
        url: WST.AU('cron://cron/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    $('#headTip').WSTTips({width:200,height:35,callback:function(v){
		 var diff = v?115:88;
	     mmg.resize({height:h-diff})
	}});
    loadGrid(p);
}
function loadGrid(p){
    p=(p<=1)?1:p;
	mmg.load({page:p});
}

function toEdit(id){
	location.href=WST.AU('cron://cron/toEdit','id='+id+'&p='+WST_CURR_PAGE);
}
function checkType(v){
   $('.cycle').hide();
   $('.cycle'+v).show();
}
function run(id){
	var box = WST.confirm({content:WST.lang('cron_confirm_run'),yes:function(){
		var loading = WST.msg(WST.lang('cron_running'),{icon: 16,time:6000000000});
		$.post(WST.AU('cron://cron/runCron'),{id:id},function(data,textStatus){
			layer.close(loading);
	        var json = WST.toAdminJson(data);
	        if(json.status=='1'){
	           	WST.msg(json.msg,{icon:1});
	           	layer.close(box);
                loadGrid(WST_CURR_PAGE);
	        }else{
	           	WST.msg(json.msg,{icon:2});
	        }
		})
	}});
}
function edit(id,p){
    var params = WST.getParams('.ipt');
	params.id = id;
	var loading = WST.msg(WST.lang('cron_submitting'), {icon: 16,time:60000});
	$.post(WST.AU('cron://cron/edit'),params,function(data,textStatus){
		layer.close(loading);
		var json = WST.toAdminJson(data);
		if(json.status=='1'){
		   	WST.msg(WST.lang('cron_operation_success'),{icon:1},function(){
		   		location.href=WST.AU('cron://cron/index','p='+p);
		   	});
		}else{
		   	WST.msg(json.msg,{icon:2});
		}
	});
}
function changgeEnableStatus(id,type){
	var msg = (type==1)?WST.lang('cron_confirm_enable'):WST.lang('cron_confirm_disable')
	var box = WST.confirm({content:msg,yes:function(){
	           var loading = WST.msg(WST.lang('cron_submitting'), {icon: 16,time:60000});
	           	$.post(WST.AU('cron://cron/changeEnableStatus'),{id:id,status:type},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(json.msg,{icon:1});
	           			    	layer.close(box);
                              loadGrid(WST_CURR_PAGE);
	           			  }else{
	           			    	WST.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}

function del(id){
	var box = WST.confirm({content:WST.lang('cron_confirm_del'),yes:function(){
	           var loading = WST.msg(WST.lang('cron_submitting'), {icon: 16,time:60000});
	           	$.post(WST.AU('cron://cron/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(WST.lang('cron_operation_success'),{icon:1});
	           			    	layer.close(box);
                                loadGrid(WST_CURR_PAGE)
	           			  }else{
	           			    	WST.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}






		