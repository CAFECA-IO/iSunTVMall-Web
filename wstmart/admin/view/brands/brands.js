var mmg;
var mmg2;
var mmg3;
var mmg4;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('upload_img2'), name:'img', width: 30, renderer: function(val,item,rowIndex){
                return "<span class='weixin'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['brandImg']
            	+"'><span class='imged' style='left:45px;' ><img  style='height:200px; width:200px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['brandImg']+"'></span></span>";
            }},
            {title:WST.lang('label_brand_apply_name'), name:'brandName', width: 60},
            {title:WST.lang('label_brand_apply_desc'), name:'brandDesc', width: 350,renderer: function(val,item,rowIndex){
                return "<span  ><p class='wst-nowrap'>"+item['brandDesc']+"</p></span>";
            }},
            { title: WST.lang('sort'), name: 'sortNo',isSort: false,width: 10,renderer: function(val,item,rowIndex){
                return '<span style="color:blue;cursor:pointer;" ondblclick="changeSort(this,'+item["brandId"]+');">'+val+'</span>';
            }},
            {title:WST.lang('op'), name:'' ,width:70, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
		        if(WST.GRANT.PPGL_02)h += "<a class='btn btn-blue' href='javascript:toEdit("+item["brandId"]+")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
		        if(WST.GRANT.PPGL_03)h += "<a class='btn btn-red' href='javascript:toDel("+item["brandId"]+")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> "; 
		        if(WST.GRANT.PPGL_00)h += "<a class='btn btn-blue' href='javascript:toView("+item["brandId"]+")'><i class='fa fa-search'></i>"+WST.lang('brand_apply_join_shop')+"</a> ";
		        return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-140,indexCol: true,indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/brands/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}

function loadGrid(p){
    p=(p<=1)?1:p;
	mmg.load({page:p,key:$('#key').val(),id:$('#catId').val()});
}

function initGrid2(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('upload_img2'), name:'img', width: 30, renderer: function(val,item,rowIndex){
                return "<span class='weixin'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['brandImg']
                    +"'><span class='imged' style='left:45px;' ><img  style='height:200px; width:200px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['brandImg']+"'></span></span>";
            }},
        {title:WST.lang('label_brand_apply_name'), name:'brandName', width: 60},
        {title:WST.lang('brand_apply_shop'), name:'shopName', width: 100},
        {title:WST.lang('label_brand_apply_desc'), name:'brandDesc', width: 350,renderer: function(val,item,rowIndex){
                return "<span  ><p class='wst-nowrap'>"+item['brandDesc']+"</p></span>";
            }},
        {title:WST.lang('brand_apply_status'), name:'brandDesc', width: 100,renderer: function(val,item,rowIndex){
                if(item['applyStatus']==0){
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+item['applyStatusName']+"</span>";
                }else if(item['applyStatus']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+item['applyStatusName']+"</span>";
                }else{
                    return "<span class='statu-no'><i class='fa fa-ban'></i> "+item['applyStatusName']+"</span>";
                }
            }},
        {title:WST.lang('op'), name:'' ,width:70, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                if(item['applyStatus'] == 0){
                    if(WST.GRANT.PPGL_02)h += "<a class='btn btn-blue' href='javascript:toEditApply("+item["applyId"]+")'><i class='fa fa-pencil'></i>"+WST.lang('handle')+"</a> ";
                }else{
                    if(WST.GRANT.PPGL_02)h += "<a class='btn btn-blue' href='javascript:toEditApply("+item["applyId"]+")'><i class='fa fa-search'></i>"+WST.lang('view')+"</a> ";
                }
                if(WST.GRANT.PPGL_03)h += "<a class='btn btn-red' href='javascript:toDelApply("+item["applyId"]+")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
    ];

    mmg2 = $('.mmg2').mmGrid({height: h-140,indexCol: true,indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/brandapplys/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg2').mmPaginator({})
        ]
    });
    loadGrid2(p);
}

function loadGrid2(p){
    p=(p<=1)?1:p;
    mmg2.load({page:p,key:$('#key2').val(),isNew:1});
}
function initGrid3(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('upload_img2'), name:'img', width: 30, renderer: function(val,item,rowIndex){
            return "<span class='weixin'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['brandImg']
                +"'><span class='imged' style='left:45px;' ><img  style='height:200px; width:200px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['brandImg']+"'></span></span>";
        }},
        {title:WST.lang('label_brand_apply_name'), name:'brandName', width: 60},
        {title:WST.lang('brand_apply_shop'), name:'shopName', width: 100},
        {title:WST.lang('label_brand_apply_desc'), name:'brandDesc', width: 350,renderer: function(val,item,rowIndex){
            return "<span  ><p class='wst-nowrap'>"+item['brandDesc']+"</p></span>";
        }},
        {title:WST.lang('brand_apply_status'), name:'brandDesc', width: 100,renderer: function(val,item,rowIndex){
            if(item['applyStatus']==0){
                return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+item['applyStatusName']+"</span>";
            }else if(item['applyStatus']==1){
                return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+item['applyStatusName']+"</span>";
            }else{
                return "<span class='statu-no'><i class='fa fa-ban'></i> "+item['applyStatusName']+"</span>";
            }
        }},
        {title:WST.lang('op'), name:'' ,width:70, align:'center', renderer: function(val,item,rowIndex){
            var h = "";
            if(item['applyStatus'] == 0){
                if(WST.GRANT.PPGL_02)h += "<a class='btn btn-blue' href='javascript:toEditApply("+item["applyId"]+")'><i class='fa fa-pencil'></i>"+WST.lang('handle')+"</a> ";
            }else{
                if(WST.GRANT.PPGL_02)h += "<a class='btn btn-blue' href='javascript:toEditApply("+item["applyId"]+")'><i class='fa fa-search'></i>"+WST.lang('view')+"</a> ";
            }
            if(WST.GRANT.PPGL_03)h += "<a class='btn btn-red' href='javascript:toDelApply("+item["applyId"]+")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
            return h;
        }}
    ];

    mmg3 = $('.mmg3').mmGrid({height: h-140,indexCol: true,indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/brandapplys/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg3').mmPaginator({})
        ]
    });
    loadGrid3(p);
}

function loadGrid3(p){
    p=(p<=1)?1:p;
    mmg3.load({page:p,key:$('#key3').val(),isNew:0});
}

function initGrid4(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('brand_apply_shop_name'), name:'shopName', width: 100},
        {title:WST.lang('brand_apply_shop_time'), name:'createTime', width: 100},
        {title:WST.lang('op'), name:'' ,width:70, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                if(WST.GRANT.PPGL_03)h += "<a class='btn btn-red' href='javascript:toDelShop("+item["applyId"]+")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
    ];

    mmg4 = $('.mmg4').mmGrid({height: h-89,indexCol: true,indexColWidth:50, cols: cols,method:'POST',
        url: WST.U('admin/brands/shopPageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg4').mmPaginator({})
        ]
    });
    loadGrid4(p);
}

function loadGrid4(p){
    p=(p<=1)?1:p;
    var brandId = $("#brandId").val();
    mmg4.load({page:p,brandId:brandId,key:$('#key4').val()});
}

function toEditApply(id){
    location.href=WST.U('admin/brandapplys/toEdit','id='+id+'&isNew='+$('#isNew').val()+'&p='+WST_CURR_PAGE);
}

function toDelApply(id){
    var isNew = $('#isNew').val();
    var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
            var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
            $.post(WST.U('admin/brandapplys/del'),{id:id},function(data,textStatus){
                layer.close(loading);
                var json = WST.toAdminJson(data);
                if(json.status=='1'){
                    WST.msg(json.msg,{icon:1});
                    layer.close(box);
                    if(isNew==1){
                        loadGrid2(WST_CURR_PAGE);
                    }else{
                        loadGrid3(WST_CURR_PAGE);
                    }
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            });
        }});
}

function toEdit(id){
	location.href=WST.U('admin/brands/toEdit','id='+id+'&p='+WST_CURR_PAGE);
}

function toView(id){
    location.href=WST.U('admin/brands/toView','id='+id+'&p='+WST_CURR_PAGE);
}

function toEdits(id,p){
    var params = WST.getParams('.ipt');
    params.id = id;
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	$.post(WST.U('admin/brands/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = WST.toAdminJson(data);
		  if(json.status=='1'){
		    	WST.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=WST.U('admin/brands/index',"p="+p);
		        },1000);
		  }else{
		        WST.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/brands/del'),{id:id},function(data,textStatus){
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
function toolTip(){
    $('body').mousemove(function(e){
    	var windowH = $(window).height();  
        if(e.pageY >= windowH*0.8){
        	var top = windowH*0.233;
        	$('.imged').css('margin-top',-top);
        }else{
        	var top = windowH*0.06;
        	$('.imged').css('margin-top',-top);
        }
    });
}


var oldSort;
function changeSort(t,id){
    $(t).attr('ondblclick'," ");
    var html = "<input type='text' id='sort-"+id+"' style='width:30px;padding:2px;' onblur='doneChange(this,"+id+")' value='"+$(t).html()+"' />";
    $(t).html(html);
    $('#sort-'+id).focus();
    $('#sort-'+id).select();
    oldSort = $(t).html();
}
function doneChange(t,id){
    var sort = ($(t).val()=='')?0:$(t).val();
    if(sort==oldSort){
        $(t).parent().attr('ondblclick','changeSort(this,'+id+')');
        $(t).parent().html(parseInt(sort));
        return;
    }
    $.post(WST.U('admin/brands/changeSort'),{id:id,sortNo:sort},function(data){
        var json = WST.toAdminJson(data);
        if(json.status==1){
            $(t).parent().attr('ondblclick','changeSort(this,'+id+')');
            $(t).parent().html(parseInt(sort));
        }
    });
}

function toDelShop(id){
    var box = WST.confirm({content:WST.lang('brand_apply_del_tips'),yes:function(){
            var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
            $.post(WST.U('admin/brandapplys/delShop'),{id:id},function(data,textStatus){
                layer.close(loading);
                var json = WST.toAdminJson(data);
                if(json.status=='1'){
                    WST.msg(json.msg,{icon:1});
                    layer.close(box);
                    loadGrid3(WST_CURR_PAGE);
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            });
        }});
}