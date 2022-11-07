
function toStock(id,src){
    location.href=WST.U('supplier/goodsvirtuals/stock','id='+id+"&src="+src);
}
function toolTip(){
    WST.toolTip();
}
function stockByPage(p){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('label_goods_img'), name:'goodsName', width: 40, renderer: function(val,item,rowIndex){
                var thumb = item['goodsImg'];
                thumb = thumb.replace('.','_thumb.');
                return "<span class='weixin'><a style='color:blue' href='javascript:toDetail("+ item['goodsId']+",\""+item['verfiycode']+"\")'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb
                +"'></a><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></span>";
        }},
        {title:WST.lang('label_goods_name'), name:'goodsName', width: 200},

        {title:WST.lang('label_product_no'), name:'productNo', width: 100},
        {title:WST.lang('label_goods_spec'), name:'', width: 150,renderer:function(val,item,rowIndex){
                if(item['isSpec']==1){
                	var spec="";
                    for(var s = 0; s < item.spec.length; s++){
                        spec +=item.spec[s]['catName']+"："+item.spec[s]['itemName'];
                        }
                    return spec;
                }else{
                    return "<span class='statu-wait'>"+WST.lang('none')+"</span>";
                }
            }},
        {title:WST.lang('label_goods_stock'), name:'goodsStock', width: 30,renderer:function(val,item,rowIndex){
                var goodsStock="";
                if(item['isSpec']==1){
                    goodsStock+="<span ondblclick='javascript:toEditGoodsStock(" + item['id'] + ",1)'>" +
                        "<input style='width: 60%;display: none;' id='ipt_1_"+item['id']+"' onkeyup='javascript:WST.isChinese(this,1)' onkeypress='return WST.isNumberKey(event)' onblur='javascript:editGoodsStock("+item['id']+",1,"+item['goodsId']+")' class='stockin' maxlength='6'/>\n" +
                        "        <span id='span_1_"+item['id']+"' style='display: inline;cursor:pointer;color:#f30505;'>"+item['goodsStock']+"</span>" +
                        "</span>";
                }else{
                    if(item['goodsType']==0){
                        goodsStock+="<span ondblclick='javascript:toEditGoodsStock(" + item['goodsId'] + ",3)'>" +
                            "<input style='width: 60%;display: none;' id='ipt_3_"+item['goodsId']+"' onkeyup='javascript:WST.isChinese(this,1)' onkeypress='return WST.isNumberKey(event)' onblur='javascript:editGoodsStock("+item['goodsId']+",3)' class='stockin' maxlength='6'/>\n" +
                            "        <span id='span_3_"+item['goodsId']+"' style='display: inline;cursor:pointer;color:#f30505;'>"+item['goodsStock']+"</span>" +
                            "</span>";
                    }else{
                        goodsStock=item['goodsStock'];
                    }
                }

                return goodsStock;
            }},
        {title:WST.lang('label_warn_stock'), name:'warnStock', width: 30,renderer:function(val,item,rowIndex){
                var goodsStock="";
                if(item['isSpec']==1){
                    goodsStock+="<span ondblclick='javascript:toEditGoodsStock(" + item['id'] + ",2)'>" +
                        "<input style='width: 60%;display: none;' id='ipt_2_"+item['id']+"' onkeyup='javascript:WST.isChinese(this,1)' onkeypress='return WST.isNumberKey(event)' onblur='javascript:editGoodsStock("+item['id']+",2,"+item['goodsId']+")' class='stockin' maxlength='6'/>\n" +
                        "        <span id='span_2_"+item['id']+"' style='display: inline;cursor:pointer;color:#f30505;'>"+item['warnStock']+"</span>" +
                        "</span>";
                }else{
                    if(item['goodsType']==0){
                        goodsStock+="<span ondblclick='javascript:toEditGoodsStock(" + item['goodsId'] + ",4)'>" +
                            "<input style='width: 60%;display: none;' id='ipt_4_"+item['goodsId']+"' onkeyup='javascript:WST.isChinese(this,1)' onkeypress='return WST.isNumberKey(event)' onblur='javascript:editGoodsStock("+item['goodsId']+",4)' class='stockin' maxlength='6'/>\n" +
                            "        <span id='span_4_"+item['goodsId']+"' style='display: inline;cursor:pointer;color:#f30505;'>"+item['warnStock']+"</span>" +
                            "</span>";
                    }else{
                        goodsStock=item['warnStock'];
                    }
                }

                return goodsStock;
            }},
        {title:WST.lang('op'), name:'' ,width:200, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                h += "<a class='btn btn-blue' href='javascript:toStock(" + item['goodsId'] + ",\"stockWarnByPage\")'><i class='fa fa-pencil'></i>"+WST.lang('card_edit')+"</a>";
                h += " <a class='btn btn-blue' href='javascript:toEdit(" + item['goodsId'] + ",\"stockwarnbypage\")'><i class='fa fa-pencil'></i>"+WST.lang('goods_edit')+"</a> ";
                return h;
            }}
    ];

    mmg = $('.mmg').mmGrid({height: h-100,indexCol: true, cols: cols,method:'POST',
        url: WST.U('supplier/goods/stockByPage'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function loadGrid(p){
    p = (p<=1)?1:p;
    mmg.load({cat1:$('#cat1').val(),cat2:$('#cat2').val(),page:p});
}
function checkCopyShops(goodsId,src){
    location.href = WST.U('supplier/Suppliergoodscopyrelates/shopIndex','goodsId='+goodsId+'&src='+src+'&p='+WST_CURR_PAGE);
}
function toEdit(id,src){
	location.href = WST.U('supplier/goods/edit','id='+id+'&src='+src+'&p='+WST_CURR_PAGE);
}
//双击修改
function toEditGoodsStock(id,type){
	$("#ipt_"+type+"_"+id).show();
	$("#span_"+type+"_"+id).hide();
	$("#ipt_"+type+"_"+id).focus();
	$("#ipt_"+type+"_"+id).val($("#span_"+type+"_"+id).html());
}
function endEditGoodsStock(type,id){
	$('#span_'+type+'_'+id).html($('#ipt_'+type+'_'+id).val());
	$('#span_'+type+'_'+id).show();
    $('#ipt_'+type+'_'+id).hide();
}
function editGoodsStock(id,type,goodsId){
	var number = $('#ipt_'+type+'_'+id).val();
	if($.trim(number)==''){
		WST.msg(WST.lang('require_stock_warn_goods_stock'), {icon: 5});
        return;
	}
	var params = {};
	params.id = id;
	params.type = type;
	params.goodsId = goodsId;
	params.number = number;
	$.post(WST.U('supplier/Goods/editwarnStock'),params,function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status>0){
			$('#img_'+type+'_'+id).fadeTo("fast",100);
			endEditGoodsStock(type,id);
			$('#img_'+type+'_'+id).fadeTo("slow",0);
		}else{
			WST.msg(json.msg, {icon: 5});
		}
	});
}

function getCat(val){
  if(val==''){
  	$('#cat2').html("<option value='' >-"+WST.lang('select')+"-</option>");
  	return;
  }
  $.post(WST.U('supplier/suppliercats/listQuery'),{parentId:val},function(data,textStatus){
       var json = WST.toJson(data);
       var html = [],cat;
       html.push("<option value='' >-"+WST.lang('select')+"-</option>");
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
