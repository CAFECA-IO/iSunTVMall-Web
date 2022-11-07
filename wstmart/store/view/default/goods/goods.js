
var mmg;
function toDetail(goodsId,key){
    window.open(WST.U('home/goods/detail','goodsId='+goodsId+"&key="+key));
}

function saleByPage(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang("product_picture"), name:'goodsName', width: 80, renderer: function(val,item,rowIndex){
                return "<span class='weixin'><a style='color:blue' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'><img class='img' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'><img class='imged' style='height:200px;width:200px;border:0px; background:#fff' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></a></span>";
            }},
        {title:WST.lang("goods_name"), name:'goodsName', width: 250, renderer: function(val,item,rowIndex){
        	return "<a style='color:#666' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'>"+val+"</a> ";
                
        }},
        {title:WST.lang("product_no"), name:'goodsSn', width: 100},
        {title:WST.lang("price"), name:'shopPrice', width: 50},
        {title:WST.lang("recommend"), name:'isRecom', width: 30,renderer:function(val,item,rowIndex){
                if(item['isRecom']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i>"+WST.lang("yes")+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang("no")+"</span>";
                }
            }},
        {title:WST.lang("boutique"), name:'isBest', width: 30,renderer:function(val,item,rowIndex){
                if(item['isBest']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i>"+WST.lang("yes")+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang("no")+"</span>";
                }
            }},
        {title:WST.lang("new_products"), name:'isNew', width: 30,renderer:function(val,item,rowIndex){
                if(item['isNew']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i>"+WST.lang("yes")+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang("no")+"</span>";
                }
            }},
        {title:WST.lang("hot_sale"), name:'isHot', width: 30,renderer:function(val,item,rowIndex){
                if(item['isHot']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i>"+WST.lang("yes")+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang("no")+"</span>";
                }
            }},
        {title:WST.lang("sale_num"), name:'saleNum', width: 40},
        {title:WST.lang("stock"), name:'goodsStock', width: 40}
    ];

    mmg = $('.mmg').mmGrid({height: h-150,indexCol: true, cols: cols,method:'POST',
        url: WST.U('store/goods/saleByPage'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}

function loadGrid(p){
    p = (p<=1)?1:p;
    mmg.load({cat1:$('#cat1').val(),cat2:$('#cat2').val(),goodsType:$('#goodsType').val(),goodsName:$('#goodsName').val(),page:p});
}

function getCat(val){
  if(val==''){
  	$('#cat2').html("<option value='' >-"+WST.lang("select")+"-</option>");
  	return;
  }
  $.post(WST.U('store/shopcats/listQuery'),{parentId:val},function(data,textStatus){
       var json = WST.toJson(data);
       var html = [],cat;
       html.push("<option value='' >-"+WST.lang("select")+"-</option>");
       if(json.status==1 && json.list){
         json = json.list;
       for(var i=0;i<json.length;i++){
           cat = json[i];
           html.push("<option value='"+cat.catId+"'>"+cat.catName+"</option>");
        }
       }
       $('#cat2').html(html.join(''));
  });
}