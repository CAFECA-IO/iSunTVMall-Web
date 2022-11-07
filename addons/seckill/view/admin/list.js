var mmg1,mmg2,mmg3,isInit1 = false,isInit2 = false,isInit3 = false;

//已审核的秒杀活动列表
function initGrid1(p){
    if(isInit1){
        loadGrid1(p);
        return;
    }
    isInit1 = true;
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('seckill_tips9'), name:'shopName', width: 130},
            {title:WST.lang('seckill_activity_title'), name:'title', width: 130},
            {title:WST.lang('seckill_activity_status'), name:'status', width: 30, renderer: function(val,rowdata,rowIndex){
                if(rowdata['status']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('seckill_curr_status_1')+"</span>";
                }else if(rowdata['status']==0){
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('seckill_curr_status_2')+"</span>";
                }else{
                    return "<span class='statu-no'><i class='fa fa-ban'></i> "+WST.lang('seckill_curr_status_3')+"</span>";
                }
            }},
            {title:WST.lang('seckill_start_time'), name:'startDate', width: 20},
            {title:WST.lang('seckill_end_time'), name:'endDate', width: 20},
            {title:WST.lang('seckill_tips10'), name:'isSale', width: 30,renderer: function (val,item,rowIndex){
                return '<form class="layui-form" lay-filter="gridForm"><input type="checkbox" name="isSale" '+((item['isSale']==1)?"checked":"")+' lay-skin="switch" value="1" lay-filter="isSale" lay-text="'+WST.lang('seckill_onsale_or_not')+'" data="'+item['id']+'"></form>';
            }},
            {title:WST.lang('seckill_audit_status'), name:'seckillStatus', width: 30, renderer: function(val,rowdata,rowIndex){
            	if(rowdata['seckillStatus']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('seckill_audit_pass')+"</span>";
	        	}else if(rowdata['seckillStatus']==0){
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('seckill_wait_audit')+"</span>";
	        	}else{
                    return "<span class='statu-no'><i class='fa fa-ban'></i> "+WST.lang('seckill_audit_not_pass')+"</span>";
	        	}
            }},
            {title:WST.lang('seckill_operation'), name:'' ,width:120, align:'center', renderer: function(val,rowdata,rowIndex){
                var h = "";
	            h += "<a class='btn btn-red' href="+WST.AU('seckill://admin/seckillGoods',"seckillId=" + rowdata['id'] )+"><i class='fa fa-cog'></i>"+WST.lang('seckill_tips3')+"</a> ";
	            if(WST.GRANT.SECKILL_TGHD_03)h += "<a class='btn btn-red' href='javascript:del(" + rowdata['id'] + ",0)'><i class='fa fa-trash'></i>"+WST.lang('seckill_del')+"</a>";
	            return h;
	        }}
        ];

    mmg1 = $('.mmg1').mmGrid({height: h-230,indexCol: true,indexColWidth:50,  cols: cols,method:'POST',
        url: WST.AU('seckill://admin/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg1').mmPaginator({})
        ]
    });
    mmg1.on('loadSuccess',function(){
        layui.form.render('','gridForm');
        layui.form.on('switch(isSale)', function(data){
            var id = $(this).attr("data");
            if(this.checked){
                toggleSet(id,1);
            }else{
                toggleSet(id,0);
            }
        });
    });
    $('#headTip').WSTTips({width:200,height:35,callback:function(v){
         if(v){
             mmg1.resize({height:h-230});
             if(mmg2)mmg2.resize({height:h-230});
             if(mmg3)mmg3.resize({height:h-230});
         }else{
             mmg1.resize({height:h-165});
             if(mmg2)mmg2.resize({height:h-165});
             if(mmg3)mmg3.resize({height:h-165});
         }
    }});
    loadGrid1(p);
}
function loadGrid1(p){
	var params = {};
	params.shopName = $('#shopName1').val();
	params.title = $('#title1').val();
	p=(p<=1)?1:p;
	params.page=p;
	mmg1.load(params);
}

//待审核的秒杀活动列表
function initGrid2(p){
    if(isInit2){
        loadGrid2(p);
        return;
    }
    isInit2 = true;
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('seckill_tips9'), name:'shopName', width: 130},
            {title:WST.lang('seckill_activity_title'), name:'title', width: 130},
            {title:WST.lang('seckill_activity_status'), name:'status', width: 30, renderer: function(val,rowdata,rowIndex){
                if(rowdata['status']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('seckill_curr_status_1')+"</span>";
                }else if(rowdata['status']==0){
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('seckill_curr_status_2')+"</span>";
                }else{
                    return "<span class='statu-no'><i class='fa fa-ban'></i> "+WST.lang('seckill_curr_status_3')+"</span>";
                }
            }},
            {title:WST.lang('seckill_start_time'), name:'startDate', width: 20},
            {title:WST.lang('seckill_end_time'), name:'endDate', width: 20},
            {title:WST.lang('seckill_tips10'), name:'isSale', width: 30,renderer: function (val,item,rowIndex){
                return '<form class="layui-form" lay-filter="gridForm"><input type="checkbox" disabled name="isSale" '+((item['isSale']==1)?"checked":"")+' lay-skin="switch" value="1" lay-filter="isSale" lay-text="'+WST.lang('seckill_tips1')+'"></form>';
            }},
            {title:WST.lang('seckill_audit_status'), name:'seckillStatus', width: 30, renderer: function(val,rowdata,rowIndex){
                if(rowdata['seckillStatus']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('seckill_audit_pass')+"</span>";
                }else if(rowdata['seckillStatus']==0){
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('seckill_wait_audit')+"</span>";
                }else{
                    return "<span class='statu-no'><i class='fa fa-ban'></i> "+WST.lang('seckill_audit_not_pass')+"</span>";
                }
            }},
            {title:WST.lang('seckill_operation'), name:'' ,width:180, align:'center', renderer: function(val,rowdata,rowIndex){
                var h = "";
                h += "<a class='btn btn-red' href="+WST.AU('seckill://admin/seckillGoods',"seckillId=" + rowdata['id'] )+"><i class='fa fa-cog'></i>"+WST.lang('seckill_tips3')+"</a> ";
                h +='<div class="btn-group">';
                h +='<button type="button" class="btn btn-blue dropdown-toggle wst-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                h +='<i class="fa fa-pencil"></i>'+WST.lang('seckill_operation')+' <span class="caret"></span>';
                h +='</button>';
                h +='<ul class="dropdown-menu wst-dropdown-menu" style="min-width:60px">';
                if(WST.GRANT.SECKILL_TGHD_04){
                    h +='  <li><a href="javascript:allow('+rowdata["id"]+')"><i class="fa fa-check"></i> '+WST.lang('seckill_audit_pass')+'</a></li>';
                    h +='  <li><a href="javascript:illegal('+rowdata["id"]+')"><i class="fa fa-ban"></i> '+WST.lang('seckill_audit_not_pass')+'</a></li>';
                    h +='  <li role="separator" class="divider"></li>';
                }
                if(WST.GRANT.SECKILL_TGHD_03)h +='  <li><a href="javascript:del('+rowdata['id']+',1)"><i class="fa fa-trash-o"></i> '+WST.lang('seckill_del')+'</a></li>';
                h +='</ul>';
                h +='</div>';
                return h;
	        }}
        ];

    mmg2 = $('.mmg2').mmGrid({height: h-230,indexCol: true,indexColWidth:50,  cols: cols,method:'POST',
        url: WST.AU('seckill://admin/pageAuditQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg2').mmPaginator({})
        ]
    });
    mmg2.on('loadSuccess',function(){
        layui.form.render('','gridForm');
    });
    loadGrid2(p);
}
function loadGrid2(p){
    var params = {};
    params.shopName = $('#shopName2').val();
    params.title = $('#title2').val();
    p=(p<=1)?1:p;
    params.page=p;
    mmg2.load(params);
}

//秒杀时段列表
function initGrid3(p){
    if(isInit3){
        loadGrid3(p);
        return;
    }
    isInit3 = true;
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('seckill_time_name'), name:'title'},
            {title:WST.lang('seckill_time_start'), name:'startTime'},
            {title:WST.lang('seckill_time_end'), name:'endTime'},
            {title:WST.lang('seckill_operation'), name:'' ,width:120, align:'center', renderer: function(val,rowdata,rowIndex){
                var h = "";
                h += "<a class='btn btn-red' href='javascript:getForEdit(" + rowdata['id'] + ",1)'><i class='fa fa-pencil'></i>"+WST.lang('seckill_edit')+"</a> ";
                h += "<a class='btn btn-red' href='javascript:timesDel(" + rowdata['id'] + ",1)'><i class='fa fa-trash'></i>"+WST.lang('seckill_del')+"</a>";
                return h;
            }}
        ];

    mmg3 = $('.mmg3').mmGrid({height: h-230,indexCol: true,indexColWidth:50,  cols: cols,method:'POST',
        url: WST.AU('seckill://admin/timesPageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg3').mmPaginator({})
        ]
    });
    loadGrid3(p);
}

function loadGrid3(p){
    var params = {};
    params.title = $('#timeTitle').val();
    p=(p<=1)?1:p;
    params.page=p;
    mmg3.load(params);
}

//秒杀上下架设置
function toggleSet(id,isSale){
    var loading = WST.msg(WST.lang('seckill_submitting'), {icon: 16,time:60000});
    $.post(WST.AU('seckill://admin/toggleSet'),{id:id,isSale:isSale},function(data,textStatus){
        layer.close(loading);
        var json = WST.toAdminJson(data);
        if(json.status=='1'){
            WST.msg(json.msg,{icon:1});
        }else{
            WST.msg(json.msg,{icon:2});
        }
    });
}

//编辑时段
function getForEdit(id){
    var loading = WST.msg(WST.lang('seckill_loading_data'), {icon: 16,time:60000});
    $.post(WST.AU('seckill://admin/getTimes'),{timeId:id},function(data,textStatus){
        layer.close(loading);
        var json = WST.toAdminJson(data);
        if(json.id){
            WST.setValues(json);
            if(json.langs){
                for(var key in json.langs){
                    WST.setValue('langParams'+key+'title',json.langs[key]['title']);
                }
            }
            timesEdit(json.id);
        }else{
            WST.msg(json.msg,{icon:2});
        }
    });
}

function timesEdit(id){

    var title =(id==0)?WST.lang('seckill_add'):WST.lang('seckill_edit');
    var box = WST.open({title:title,type:1,content:$('#timesBox'),area: ['100%', '100%'],offset: 't',btnAlign: 'c',btn: [WST.lang('seckill_confirm'),WST.lang('seckill_cancel')],
        yes:function(){
            $('#timesForm').submit();
        },end:function(){
            $('#timesBox').hide();
            //重置表单
            $('#timesForm')[0].reset();
        }});
    $('#timesForm').validator({
       valid: function(form){
                var params = WST.getParams('.t-ipt');
                params.timeId = id;
                var loading = WST.msg(WST.lang('seckill_submitting'), {icon: 16,time:60000});
                $.post(WST.AU('seckill://admin/'+((id==0)?"addTimes":"editTimes")),params,function(data,textStatus){
                    layer.close(loading);
                    var json = WST.toAdminJson(data);
                    if(json.status=='1'){
                        WST.msg(WST.lang('seckill_operation_success'),{icon:1});
                        $('#timesBox').hide();
                        $('#timesForm')[0].reset();
                        layer.close(box);
                        mmg3.load();
                    }else{
                        WST.msg(json.msg,{icon:2});
                    }
                });

        }

  });
}

//删除时段
function timesDel(id){
    var box = WST.confirm({content:WST.lang('seckill_tips11'),yes:function(){
        var loading = WST.msg(WST.lang('seckill_submitting'), {icon: 16,time:60000});
        $.post(WST.AU('seckill://admin/timesDel'),{timeId:id},function(data,textStatus){
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


//删除秒杀活动
function del(id,type){
    var box = WST.confirm({content:WST.lang('seckill_tips12'),yes:function(){
    var loading = WST.msg(WST.lang('seckill_loading_data'), {icon: 16,time:60000});
    $.post(WST.AU('seckill://admin/del'),{id:id},function(data,textStatus){
            layer.close(loading);
            var json = WST.toAdminJson(data);
            if(json.status=='1'){
                WST.msg(json.msg,{icon:1});
                layer.close(box);
                if(type==0){
                    loadGrid1(WST_CURR_PAGE);
                }else{
                    loadGrid2(WST_CURR_PAGE);
                }
            }else{
                WST.msg(json.msg,{icon:2});
            }
        });
    }});
}

//秒杀活动审核不通过
function illegal(id,type){
    var w = WST.open({type: 1,title:((type==1)?WST.lang('seckill_tips13'):WST.lang('seckill_not_pass_reason')),shade: [0.6, '#000'],border: [0],
        content: '<textarea id="illegalRemarks" rows="7" style="width:100%" maxLength="200"></textarea>',
        area: ['500px', '260px'],btn: [WST.lang('seckill_confirm'), WST.lang('seckill_cancel')],
        yes: function(index, layero){
            var illegalRemarks = $.trim($('#illegalRemarks').val());
            if(illegalRemarks==''){
                WST.msg(WST.lang('seckill_tips14'), {icon: 5});
                return;
            }
            var ll = WST.msg(WST.lang('seckill_loading_data'),{time:6000000});
            $.post(WST.AU('seckill://admin/illegal'),{id:id,illegalRemarks:illegalRemarks},function(data){
                layer.close(w);
                layer.close(ll);
                var json = WST.toAdminJson(data);
                if(json.status>0){
                    WST.msg(json.msg, {icon: 1});
                    if(type==1){
                        loadGrid1(WST_CURR_PAGE);
                    }else{
                        loadGrid2(WST_CURR_PAGE);
                    }
                }else{
                    WST.msg(json.msg, {icon: 2});
                }
           });
        }
    });
}

//秒杀活动审核通过
function allow(id,type){
	var box = WST.confirm({content:WST.lang('seckill_tips15'),yes:function(){
        var loading = WST.msg(WST.lang('seckill_loading_data'), {icon: 16,time:60000});
        $.post(WST.AU('seckill://admin/allow'),{id:id},function(data,textStatus){
        			layer.close(loading);
        			var json = WST.toAdminJson(data);
        			if(json.status=='1'){
        			    WST.msg(json.msg,{icon:1});
        			    layer.close(box);
        		        loadGrid1(WST_CURR_PAGE);
        		        loadGrid2(WST_CURR_PAGE);
        		    }else{
        			    WST.msg(json.msg,{icon:2});
        			}
        		});
         }});
}


$(function(){
    layui.laydate.render({
      elem: '#startTime',
      type:'time'
    });
    layui.laydate.render({
      elem: '#endTime',
      type:'time'
    });
    layui.form.render();
});
