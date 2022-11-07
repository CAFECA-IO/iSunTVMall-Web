var mmg;
function initGrid(p){
   var h = WST.pageHeight();
   var cols = [
        {title:WST.lang('wstim_account_number'), name:'loginName', width: 300},
        {title:WST.lang('wstim_nickname'), name:'userName',width: 150},
        {title:WST.lang('wstim_role_name'), name:'roleName', width: 70},
        {title:WST.lang('wstim_set_as_customer_service'), name:'' ,width:110,renderer:function(val,item,rowIndex){
            return '<form complete="off" class="layui-form" lay-filter="gridForm"><input type="checkbox" '+((item['isService']==1)?'checked':'')+'  value="'+item['isService']+'" id="isService" name="isService" data-id="'+item['userId']+'" lay-skin="switch" lay-filter="isService" lay-text="'+WST.lang('wstim_tip32')+'" /></form>';
        }}
    ];

    mmg = $('.mmg').mmGrid({height: h-45,indexCol: true, cols: cols,method:'POST',nowrap:true,
        url: APIS['shopServiceQuery'], fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    mmg.on('loadSuccess',function(){
        layui.form.render('','gridForm');
        var form = layui.form;
        form.render();
        form.on('switch(isService)', function(data){
           var id = $(this).data("id");
           if(this.checked){
              setService(id,1);
           }else{
              setService(id,0);
           }
        });
    });
    loadGrid(p);
}

function loadGrid(p){
    var params = {};
    params = WST.imGetParams('.s-ipt');
    p=(p<=1)?1:p;
    params.page=p;
    mmg.load(params);
}

// 设置为客服
function setService(userId,isSet){
    var load = WST.imLoad({msg:WST.lang('wstim_loading')});
    var url = APIS['shopServiceSet'];
      $.post(url,
            {id:userId,isSet:(isSet==1)?1:0},
            function(data,textStatus){
                layer.close(load);
                var json = WST.imToJson(data);
                $('#isService').val((isSet==0)?1:0);

            });
}