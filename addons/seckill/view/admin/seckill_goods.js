var mmg1,isInit1 = false;

//已审核的秒杀活动列表
function initGrid1(p){
    if(isInit1){
        loadGrid1(p);
        return;
    }
    isInit1 = true;
    var h = WST.pageHeight();
    var cols = [
            {title:'&nbsp;', name:'goodsImg', width: 50, renderer: function(val,item,rowIndex){
                var thumb = item['goodsImg'];
                thumb = thumb.replace('_thumb.','.');
                return "<span class='weixin'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:60px;width:60px;' src='"+WST.conf.ROOT+"/"+item['goodsImg']
                +"'><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.ROOT+"/"+thumb+"'></span></span>";
            }},
            {title:WST.lang('seckill_goods_name'), name:'goodsName', width: 100},
            {title:WST.lang('seckill_buy_price'), name:'shopPrice', width: 20,renderer: function(val,item,rowIndex){return WST.lang('currency_symbol')+val}},
            {title:WST.lang('seckill_tips6'), name:'secPrice', width: 20,renderer: function(val,item,rowIndex){return WST.lang('currency_symbol')+val}},
            {title:WST.lang('seckill_tips7'), name:'secNum', width: 20},
            {title:WST.lang('seckill_tips8'), name:'secLimit', width: 20},
            {title:WST.lang('seckill_operation'), name:'' ,width:120, align:'center', renderer: function(val,rowdata,rowIndex){
                var h = "";
	            if(WST.GRANT.SECKILL_TGHD_03)h += "<a class='btn btn-red' href='javascript:delGoods(" + rowdata['id'] + ",0)'><i class='fa fa-trash'></i>"+WST.lang('seckill_del')+"</a>"; 
	            return h;
	        }}
        ];
 
    mmg1 = $('.mmg1').mmGrid({height: h-130,indexCol: true,indexColWidth:50,  cols: cols,method:'POST',
        url: WST.AU('seckill://admin/queryGoodsByPage'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg1').mmPaginator({})
        ]
    });
    mmg1.on('loadSuccess',function(){
        layui.form.render('','gridForm');
    });
    loadGrid1(p);
}
function loadGrid1(p){
	var params = {};
	params.goodsName = $('#goodsName').val();
    params.seckillId = $('#seckillId').val();
	params.timeId = $('.layui-this').data("timeid");
	p=(p<=1)?1:p;
	params.page=p;
	mmg1.load(params);
}

//删除秒杀商品
function delGoods(id){
    WST.confirm({content:WST.lang('seckill_confirm_del'), yes:function(tips){
        var loading = WST.msg(WST.lang('seckill_submitting'), {icon: 16,time:60000});
        $.post(WST.AU('seckill://admin/delGoods'),{id:id},function(data,textStatus){
            layer.close(loading);
            var json = WST.toAdminJson(data);
            if(json.status==1){
                WST.msg(json.msg,{icon:1});
                loadGrid1(WST_CURR_PAGE);
            }else{
                WST.msg(json.msg,{icon:2});
            }

        });
    }});
}

$(function(){
    var element = layui.element;
    element.on('tab(msgTab)', function(data){
        initGrid1();
    });
    $(".layui-tab-title li").eq(0).click();
})