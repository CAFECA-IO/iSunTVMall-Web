var mmg;
function initGrid(p){
   var h = WST.pageHeight();
   var cols = [
       {title:WST.lang('txlive_live_room'), name:'roomName', width: 150},
       {title:WST.lang('txlive_anchor_name'), name:'anchorName', width: 150},
       {title:WST.lang('txlive_cover_img'), name:'img', width: 50, renderer: function(val,item,rowIndex){
               return "<span class='weixin'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['coverImg']
                   +"'><span class='imged' style='left:45px;' ><img  style='height:auto; width:200px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['coverImg']+"'></span></span>";
           }},
       {title:WST.lang('txlive_expire_time'), name:'expireTime', width: 150},
       {title:WST.lang('txlive_live_status'), name:'liveStatusText', width: 150},
       {title:WST.lang('txlive_operation'), name:'' ,width:100, align:'center', renderer: function(val,item,rowIndex){
               var h = "";
               h += "<a class='btn btn-red' href='javascript:del(" + item['id'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('txlive_del')+"</a> ";
               return h;
           }}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',
        url: WST.AU('txlive://shops/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}

function loadGrid(p){
    var params = {};
    params = WST.getParams('.s-ipt');
    params.key = $.trim($('#roomName').val());
    p=(p<=1)?1:p;
    params.page=p;
    mmg.load(params);
}

function del(id){
	var box = WST.confirm({content:WST.lang("txlive_confirm_del_live_room"),yes:function(){
		layer.close(box);
		var loading = WST.load({msg:WST.lang('txlive_submitting')});
		$.post(WST.AU("txlive://shops/del"),{id:id},function(data,textStatus){
			layer.close(loading);
		    var json = WST.toJson(data);
			if(json.status==1){
			    WST.msg(json.msg,{icon:1},function(){
			        loadGrid(WST_CURR_PAGE);
			    });
		    }else{
				WST.msg(json.msg,{icon:2});
			}
		});
	}});
}

function initForm(){
	var laydate = layui.laydate;
    laydate.render({
        elem: '#startTime',
        type: 'datetime'
    });
    laydate.render({
        elem: '#endTime',
        type: 'datetime'
    });
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


