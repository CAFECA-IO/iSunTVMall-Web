var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_cashdraws_no'), name:'cashNo', width: 90,sortable: true},
            {title:WST.lang('label_cashdraws_type'), name:'accType' ,width:40,renderer:function(val,item,rowIndex){
                if(item['accType']==1)return WST.lang('label_cashdraws_type1');
                if(item['accType']==2)return WST.lang('label_cashdraws_type2');
                if(item['accType']==3)return WST.lang('label_cashdraws_type3');
            }},
            {title:WST.lang('label_cashdraws_user_type'), name:'targetTypeName' ,width:50,sortable: true},
            {title:WST.lang('label_cashdraws_user_name'), name:'loginName' ,width:50, renderer:function(val,item,rowIndex){
                return WST.blank(item['userName'])+"("+item['loginName']+")";
            }},
            {title:WST.lang('label_cashdraws_account'), name:'accTargetName' ,width:280, renderer:function(val,item,rowIndex){
                if(item['accType']==1){
                    return item['accNo'];
                }else{
                    return  item['accTargetName']+' | '+item['accNo']+' | '+item['accAreaName'];
                }
            }},
            {title:WST.lang('label_cashdraws_account_user'), name:'accUser' ,width:40,sortable: true},
            {title:WST.lang('label_cashdraws_money'), name:'money' ,width:40,sortable: true, renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+val;
            }},
            {title:WST.lang('label_cashdraws_apply_time'), name:'createTime',sortable: true ,width:60},
            {title:WST.lang('label_cashdraws_status'), name:'cashSatus' ,width:60,sortable: true, renderer:function(val,item,rowIndex){
                return (val==1)?"<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('label_cashdraws_status1')+"</span>":((val==-1)?"<span class='statu-no'><i class='fa fa-ban'></i> "+WST.lang('label_cashdraws_status0')+"&nbsp;</span>":"<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('label_cashdraws_status2')+"&nbsp;</span>");
            }},
            {title:WST.lang('op'), name:'' ,width:120, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
	            h += "<a class='btn btn-blue' href='javascript:toView(" + item['cashId'] + ")'><i class='fa fa-search'></i>"+WST.lang('view')+"</a> ";
	            if(item['cashSatus']==0 && WST.GRANT.TXSQ_04)h += "<a class='btn btn-green' href='javascript:toEdit(" + item['cashId'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('handle')+"</a> ";
	            return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-182,indexCol: true,indexColWidth:50, cols: cols,method:'POST',nowrap:true,
        url: WST.U('admin/cashdraws/pageQuery'), fullWidthRows: true, autoLoad: false,remoteSort: true,sortName:'createTime',sortStatus:'desc',
        remoteSort:true ,
        sortName: 'cashNo',
        sortStatus: 'desc',
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });  
    $('#headTip').WSTTips({width:200,height:35,callback:function(v){
         var diff = v?182:137;
         mmg.resize({height:h-diff})
    }});
    loadGrid(p)
}

function toEdit(id){
	location.href=WST.U('admin/cashdraws/toHandle','id='+id+'&p='+WST_CURR_PAGE);
}
function toView(id){
	location.href=WST.U('admin/cashdraws/toView','id='+id+'&p='+WST_CURR_PAGE);
}
function loadGrid(p){
    p=(p<=1)?1:p;
	mmg.load({page:p,cashNo:$('#cashNo').val(),cashSatus:$('#cashSatus').val(),targetType:$('#targetType').val()});
}

function save(p){
	var params = WST.getParams('.ipt');
	if(typeof(params.cashSatus)=='undefined'){
		WST.msg(WST.lang('require_cashdraws_result'),{icon:2});
		return;
	}
	if(params.cashSatus==-1 && $.trim(params.cashRemarks)==''){
		WST.msg(WST.lang('require_cashdraws_result_txt'),{icon:2});
		return;
	}
	if(WST.confirm({content:WST.lang('require_cashdraws_tips',[((params.cashSatus==1)?WST.lang('cashdraws_ok'):WST.lang('cashdraws_error'))]),yes:function(){
		var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	    $.post(WST.U('admin/cashdraws/handle'),params,function(data,textStatus){
	    	layer.close(loading);
	    	var json = WST.toAdminJson(data);
	    	if(json.status=='1'){
	    		WST.msg(json.msg,{icon:1});
	    		location.href=WST.U('admin/cashdraws/index','p='+p);
	    	}else{
	    		WST.msg(json.msg,{icon:2});
	    	}
	    });
	}}));
}
function toExport(){
    var params = {};
    params = WST.getParams('.j-ipt');
    var box = WST.confirm({content:WST.lang('cashdraws_export_tips'),yes:function(){
        layer.close(box);
        location.href=WST.U('admin/cashdraws/toExport',params);
    }});
}