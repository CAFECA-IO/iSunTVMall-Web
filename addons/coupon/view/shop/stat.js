function loadStat(){
    var loading = WST.load({msg:WST.lang('coupon_searching')});
    $.post(WST.AU('coupon://shops/stat'),WST.getParams('.j-ipt'),function(data,textStatus){
        layer.close(loading);
        var json = WST.toJson(data);
        var myChart = echarts.init(document.getElementById('main'));
        myChart.clear();
        $('#mainTable').addClass('hide');
        if(json.status=='1' && json.data){
            var option = {
                tooltip : {
                    trigger: 'axis'
                },
                toolbox: {
                    show : true,
                    y: 'top',
                    feature : {
                        mark : {show: true},
                        dataView : {show: false, readOnly: false},
                        magicType : {show: true, type: ['line', 'bar', 'tiled']},
                        restore : {show: true},
                        saveAsImage : {show: true}
                    }
                },
                calculable : true,
                legend: {
                    data:[WST.lang('coupon_received_num'),WST.lang('coupon_use_num')]
                },
                xAxis : [
                    {
                        type : 'category',
                        splitLine : {show : false},
                        data : json.data.days
                    }
                ],
                yAxis : [
                    {
                        type : 'value',
                        position: 'right'
                    }
                ],
                series : [
                    {
                        name:WST.lang('coupon_received_num'),
                        type:'line',
                        data:json.data['coupon']
                    },
                    {
                        name:WST.lang('coupon_use_num'),
                        type:'line',
                        data:json.data['use']
                    }
                ]
            };  
            myChart.setOption(option);
        }else{
            WST.msg('没有查询到记录',{icon:5});
        }
    }); 
}

function toBack(p){
    location.href = WST.AU('coupon://shops/index','p='+p);
}


