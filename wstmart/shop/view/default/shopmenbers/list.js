var mmg;
var mmg2;
var mmg3;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('customer_information'), name:'loginName', width: 30},
            {title:WST.lang('customer_grouping'), name:'groupName', width: 60, renderer: function(val,item,rowIndex){
            	if(WST.blank(item['groupName'])=='')return '-';
            }},
            {title:WST.lang('transaction_amount'), name:'totalOrderMoney', width: 100},
			{title:WST.lang('number_of_transactions'), name:'totalOrderNum', width: 100},
			{title:WST.lang('average_transaction_amount'), name:'avgMoney', width: 100, renderer: function(val,item,rowIndex){
				return Math.round(item['totalOrderMoney']/item['totalOrderNum']*100) / 100;
			}},
			{title:WST.lang('latest_transaction_time'), name:'lastOrderTime', width: 350},
            {title:WST.lang('op'), name:'' ,width:70, align:'center', renderer: function(val,item,rowIndex){
				return "<a class='btn btn-blue' href='javascript:toView("+item["userId"]+")'><i class='fa fa-search'></i>"+WST.lang('transaction_records')+"</a> ";
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-89,indexCol: true,indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('shop/shopmembers/pageQuery',{isOrder:1}), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}

function loadGrid(p){
    p=(p<=1)?1:p;
	mmg.load({page:p,isNew:1,key:$('#key').val()});
}

function initGrid2(p){
	var h = WST.pageHeight();
	var cols = [
		{title:WST.lang('customer_information'), name:'loginName', width: 30},
        {title:WST.lang('customer_grouping'), name:'groupName', width: 60, renderer: function(val,item,rowIndex){
            	if(WST.blank(item['groupName'])=='')return '-';
        }},
        {title:WST.lang('focus_on_time'), name:'createTime', width: 60}
	];

	mmg2 = $('.mmg2').mmGrid({height: h-89,indexCol: true,indexColWidth:50, cols: cols,method:'POST',
		url: WST.U('shop/shopmembers/pageQuery',{isOrder:0}), fullWidthRows: true, autoLoad: false,
		plugins: [
			$('#pg2').mmPaginator({})
		]
	});
	loadGrid2(p);
}

function loadGrid2(p){
	p=(p<=1)?1:p;
	mmg2.load({page:p,key:$('#key2').val()});
}

function toView(id){
	location.href=WST.U('shop/brandapplys/toView','id='+id+'&p='+WST_CURR_PAGE);
}

function toEdits(id,p){
    var params = WST.getParams('.ipt');
    params.id = id;
    var type = $('#type').val();
    params.isNew = 0;
    if(type=='new'){
        params.isNew = 1;
    }
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	$.post(WST.U('shop/brandapplys/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = WST.toJson(data);
		  if(json.status=='1'){
		    	WST.msg(json.msg,{icon:1});
			    location.href=WST.U('shop/brandapplys/index',"p="+p+'&type='+type);
		  }else{
		        WST.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
    var isNew = $('#isNew').val();
	var box = WST.confirm({content:WST.lang('are_you_sure_to_delete'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('shop/brandapplys/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(json.msg,{icon:1});
	           			    	layer.close(box);
                                  if(isNew==1){
                                      loadGrid(WST_CURR_PAGE);
                                  }else{
                                      loadGrid2(WST_CURR_PAGE);
                                  }
	           			  }else{
	           			    	WST.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}