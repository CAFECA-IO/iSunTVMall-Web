var mmg;
var mmg2;
var mmg3;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('customer_information'), name:'loginName', width: 60},
            {title:WST.lang('customer_grouping'), name:'groupName', width: 60, renderer: function(val,item,rowIndex){
            	if(WST.blank(item['groupName'])=='')return '-';
              return val;
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
 
    mmg = $('.mmg').mmGrid({height: h-89,indexCol: true,indexColWidth:50, cols: cols,method:'POST',checkCol:true,multiSelect:true,
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
		{title:WST.lang('customer_information'), name:'loginName', width: 60},
    {title:WST.lang('customer_grouping'), name:'groupName', width: 60, renderer: function(val,item,rowIndex){
        if(WST.blank(item['groupName'])=='')return '-';
        return val;
    }},
    {title:WST.lang('focus_on_time'), name:'createTime', width: 120}
	];

	mmg2 = $('.mmg2').mmGrid({height: h-89,indexCol: true,indexColWidth:50, cols: cols,method:'POST',checkCol:true,multiSelect:true,
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

function toView(userId){
	location.href=WST.U('shop/orders/finished','userId='+userId);
}
function setGroup(type){

  var rows = (type==1)?mmg.selectedRows():mmg2.selectedRows();
  if(rows.length==0){
      WST.msg(WST.lang('set_up_member_groups'),{icon:2});
      return;
  }
  var box = WST.open({title:WST.lang('set_up_member_groups'),type:1,content:$('#editBox'),area: ['400px', '150px'],
    btn:[WST.lang('ok'),WST.lang('cancel')],end:function(){$('#editBox').hide();},yes:function(){
        $('#editForm').submit();
    },cancel:function () {
        $('#editForm')[0].reset();
    },btn2: function() {
        $('#editForm')[0].reset();
    },});
  $('#editForm').validator({
       valid: function(form){
            var rows = (type==1)?mmg.selectedRows():mmg2.selectedRows();
            if(rows.length==0){
               WST.msg(WST.lang('set_up_member_groups'),{icon:2});
               return;
            }
            var ids = [];
            for(var i=0;i<rows.length;i++){
                 ids.push(rows[i]['userId']); 
            }
            var params = {};
            params.groupId = $('#groupId').val();
            params.ids = ids.join(',');
            var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
            $.post(WST.U('shop/shopmembers/setgroup'),params,function(data,textStatus){
                layer.close(loading);
                var json = WST.toJson(data);
                if(json.status=='1'){
                    WST.msg(WST.lang('op_ok'),{icon:1});
                    $('#editBox').hide();
                    $('#editForm')[0].reset();
                    layer.close(box);
                    if(type==1){
                        loadGrid(WST_CURR_PAGE);
                    }else{
                        loadGrid2(WST_CURR_PAGE);
                    }
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            });
      }
  });
}