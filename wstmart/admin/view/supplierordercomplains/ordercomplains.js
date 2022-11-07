var mmg;
$(function(){
    var laydate = layui.laydate;
    laydate.render({
        elem: '#startDate'
    });
    laydate.render({
        elem: '#endDate'
    });
})
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_ordercomplains_user'), name:'userName', width: 30,sortable: true, renderer: function(val,item,rowIndex){
            	return WST.blank(item['userName'],item['loginName']);
            }},
            {title:WST.lang('label_ordercomplains_order_no'), name:'orderNo',sortable: true, renderer: function(val,item,rowIndex){
            	var h = "";
	            h += "<img class='order-source2' src='"+WST.conf.ROOT+"/wstmart/admin/view/img/order_source_"+item['orderSrc']+".png'>"; 
              h += "<a style='cursor:pointer' onclick='javascript:showDetail("+ item['complainId'] +");'>"+item['orderNo']+"</a>";
	            return h;
            }},
            {title:WST.lang('label_ordercomplains_src'),width: 30,name:'orderCodeTitle'},
            {title:WST.lang('label_ordercomplains_be_user'),width: 30,sortable: true, name:'shopName'},
            {title:WST.lang('label_ordercomplains_type'),width: 120,sortable: true, name:'complainName'},
            {title:WST.lang('label_ordercomplains_time'),width: 80,sortable: true, name:'complainTime'},
            {title:WST.lang('label_ordercomplains_handle_status'), name:'complainStatus', width: 60,renderer: function(val,item,rowIndex){
              var html='23123213';
              if(val==0)
	        		return WST.lang('label_ordercomplains_handle_status0');
	        	else if(val==1)
	        		return WST.lang('label_ordercomplains_handle_status1');
	        	else if(val==2)
	        		return WST.lang('label_ordercomplains_handle_status2');
	        	else if(val==3)
	        		return WST.lang('label_ordercomplains_handle_status3');
	        	else if(val==4)
	        		return WST.lang('label_ordercomplains_handle_status4');
            }},
            {title:WST.lang('op'), name:'op' ,width:180, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
		            h += "<a class='btn btn-blue' href='javascript:toView(" + item['complainId'] + ")'><i class='fa fa-search'></i>"+WST.lang("view")+"</a> ";
		            if(item['complainStatus']!=4)
		            h += "<a class='btn btn-blue' href='javascript:toHandle(" + item['complainId'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('handle')+"</a> ";
		            return h;
	            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: (h-90),indexCol: true, indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/SupplierOrderComplains/pageQuery'), fullWidthRows: true, autoLoad: false,nowrap:true,
        remoteSort:true ,
        sortName: 'complainTime',
        sortStatus: 'desc',
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function toView(id){
	location.href=WST.U('admin/SupplierOrderComplains/view','cid='+id+'&p='+WST_CURR_PAGE);
}
function toHandle(id){
	location.href=WST.U('admin/SupplierOrderComplains/toHandle','cid='+id+'&p='+WST_CURR_PAGE);
}
function loadGrid(page){
	var p = WST.getParams('.j-ipt');
	page=(page<=1)?1:page;
	p.page = page;
	mmg.load(p);
}


function deliverNext(id){
     WST.confirm({content:WST.lang('label_ordercomplains_move_shop_tips'),yes:function(){
       $.post(WST.U('Admin/SupplierOrderComplains/deliverRespond'),{id:id},function(data,textStatus){
          var json = WST.toAdminJson(data);
          if(json.status=='1'){
        	  WST.msg(WST.lang('label_ordercomplains_move_shop_tips2'),{icon:1},function(){
        		  location.reload();
        	  });
          }else{
            WST.msg(json.msg,{icon:2});
          }
        });
     }});
}

function finalHandle(id){
   var params = {};
   params.cid = id;
   params.finalResult = $.trim($('#finalResult').val());
   if(params.finalResult==''){
     WST.msg(WST.lang('require_ordercomplains_result'),{icon:2});
     return;
   }

   var c = WST.confirm({title:WST.lang('label_ordercomplains_tips'),content:WST.lang('label_ordercomplains_result_tips'),yes:function(){
     layer.close(c);
     $.post(WST.U('Admin/SupplierOrderComplains/finalHandle'),params,function(data,textStatus){
        var json = WST.toAdminJson(data);
        if(json.status=='1'){
          WST.msg(json.msg,{icon:1});
          location.reload();
        }else{
          WST.msg(json.msg,{icon:2});
        }
      });
   }});
}

function showDetail(id){
    parent.showBox({title:WST.lang('label_ordercomplains_order_view'),type:2,content:WST.U('admin/SupplierOrderComplains/view',{cid:id,from:1}),area: ['1020px', '500px'],btn:[WST.lang('close')]});
}
  
