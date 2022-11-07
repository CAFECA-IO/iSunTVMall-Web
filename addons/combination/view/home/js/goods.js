$(function(){WSTHook_checkGoodsStock.push('checkCombineGoodsMoney')})
var combineGoodsNo = 0;
var initBox = {0:true};
$(function(){
	$('#combine').TabPanel({tab:0,callback:function(no){
        combineGoodsNo = no;
        if(!initBox[no]){
            initBox[no] = true;
            $("#js-combineGoods"+no).als({
              visible_items: 6,
              scrolling_items: 1,
              orientation: "horizontal",
              circular: "no",
              autoscroll: "no",
              start_from: 0,
        width:'100%'
            });
        }
	}});
  $("#js-combineGoods0").als({
        visible_items: 6,
        scrolling_items: 1,
        orientation: "horizontal",
        circular: "no",
        autoscroll: "no",
        start_from: 0,
        width:'100%'
  });
})
function checkCombineGoodsMoney(pageGoods){
   var combineId = $('#combieTab_'+combineGoodsNo).attr('dataval');
   var json = combineJosn[combineId];
   //计算金额
   var total = 0;
   var isCheck = false;
   var goodsNum = 0;
   $('.j-combine'+combineId).each(function(){
      //搭配商品是主页面商品
      if(json['isMain']==1){
        if(json['list'][$(this).attr('combineGoodsId')]['goodsType']==0){
       	  if($(this)[0].checked){
            if($(this).attr('goodsType')==0)isCheck = true;
            goodsNum++;
    	   	  total += parseFloat(json['list'][$(this).attr('combineGoodsId')]['shopPrice'],10);
    	    }
        }else{
            total += parseFloat(getGoodsPrice(combineId,$(this).attr('goodsId')).goodsPrice - json['reduceMoney'],10);
        }
    }else{
        if($(this)[0].checked){
            isCheck = true;
            if(goodsInfo.id==$(this).attr('goodsId')){
                 goodsNum++;
                 total += parseFloat(getGoodsPrice(combineId,$(this).attr('goodsId')).goodsPrice - json['reduceMoney'],10);
            }else{
               goodsNum++;
               total += parseFloat(json['list'][$(this).attr('combineGoodsId')]['shopPrice'],10);
             }
        }
    }
   });
   total = (total>0)?total:0
   $('#goodsNum_'+combineId).html(isCheck?goodsNum:0);
   $('#combineTotalMoney_'+combineId).html(isCheck?total.toFixed(2):0);
   return pageGoods;
}

function getGoodsPrice(combineId,goodsId){
  var specIds = [],stock = 0,goodsPrice=0,goodsSpecId = 0;
  if(goodsInfo.isSpec==1){
     $('.j-selected').each(function(){
        specIds.push(parseInt($(this).attr('data-val'),10));
     });
     specIds.sort(function(a,b){return a-b;});
     if(goodsInfo.sku[specIds.join(':')]){
        stock = goodsInfo.sku[specIds.join(':')].specStock;
        goodsPrice = parseFloat(goodsInfo.sku[specIds.join(':')].specPrice,10);
        goodsSpecId = goodsInfo.sku[specIds.join(':')].id;
     }
  }else{
        stock = goodsInfo.goodsStock;
        goodsPrice = goodsInfo.goodsPrice;
  }
  $('.j-combine-goodsId'+combineId+'_'+goodsId).attr('combineGoodsSpecId',goodsSpecId);
  return {stock:stock,goodsPrice:goodsPrice,goodsSpecId:goodsSpecId};
}
var specLayer = null;
function selectGoods(combineId,goodsId){
  var obj = $('.j-combine-goodsId'+combineId+'_'+goodsId);
  if(obj[0]){
      var json = combineJosn[combineId]['list'][obj.attr('combineGoodsId')];
      specLayer = layer.open({
          title:WST.lang('combination_select_spec'),
          type: 1,
          area: ['800px', '600px'],
          content: $('#combineteGoodsSpec'),
          end:function(index, layero){
              $('#combineteGoodsSpec').hide();
          },
          success:function(index, layero){
              var gettpl = document.getElementById('combineBoxlist').innerHTML;
              var spec = goodsSpec[goodsId];
              var ids = (spec['combine_specIds_'+combineId])?spec['combine_specIds_'+combineId]:spec['defaultSpecs']['specIds'];
              var specIds = ":"+ids+":";
              var tplParam = {};
              tplParam['specs'] = spec['specs'];
              tplParam['ids'] = specIds;
              tplParam['goodsUnit'] = json['goodsUnit'];
              tplParam['defaultStock'] = spec['defaultSpecs']['specStock'];
              tplParam['defaultPrice'] = parseFloat(spec['defaultSpecs']['specPrice'],10) - parseFloat(json['reduceMoney'],10);
              tplParam['defaultPrice'] = (tplParam['defaultPrice']>0)?tplParam['defaultPrice']:0;
              tplParam['combineId'] = combineId;
              tplParam['goodsId'] = goodsId;
              tplParam['goodsSpecId'] = obj.attr('combineGoodsSpecId');
              laytpl(gettpl).render(tplParam, function(html){
                  $('#standard').html(html);
              });
              $('.spec .j-option').click(function(){
                  $(this).addClass('active').siblings().removeClass('active');
                  checkCombineGoodsStock(combineId,goodsId);
              });
          },
          yes:function(){ 
              layer.close(layerbox);
          }
      });
  }
}
//选中事件
function checkCombineGoodsStock(combineId,goodsId){
    var obj = $('.j-combine-goodsId'+combineId+'_'+goodsId);
    var json = combineJosn[combineId]['list'][obj.attr('combineGoodsId')];
    var specIds = [],stock = 0,goodsPrice = 0;
    $('.spec .active').each(function(){
        specIds.push(parseInt($(this).attr('data-val'),10));
    });
    specIds.sort(function(a,b){return a-b;});
    var spec = goodsSpec[goodsId];
    var goodsSpecId = 0;
    if(spec['saleSpec'][specIds.join(':')]){
        stock = spec['saleSpec'][specIds.join(':')].specStock;
        goodsPrice = parseFloat(spec['saleSpec'][specIds.join(':')].specPrice,10) - parseFloat(json['reduceMoney'],10);
        goodsSpecId = spec['saleSpec'][specIds.join(':')].id;
    }
    goodsPrice = (goodsPrice>0)?goodsPrice:0;
    $('#j-goods-stock').html(stock);
    $('#j-goods-price').html(goodsPrice);
    if(stock<=0){
        $('#j-specBtn').addClass('disabled').attr('onclick','javascript:void(0);');
    }else{
        $('#j-specBtn').removeClass('disabled').attr('onclick','javascript:selectSpec('+combineId+','+goodsId+','+goodsSpecId+');');
    }
}
//结束选中
function selectSpec(combineId,goodsId,goodsSpecId){
    var obj = $('.j-combine-goodsId'+combineId+'_'+goodsId);
    var json = combineJosn[combineId]['list'][obj.attr('combineGoodsId')];
    var specIds = [];
    $('.spec .active').each(function(){
        specIds.push(parseInt($(this).attr('data-val'),10));
        if($(this).attr('data-image')){
            $("#combineGoodsImg_"+combineId+'_'+goodsId).attr('src',$(this).attr('data-image'));
        }
    });
    specIds.sort(function(a,b){return a-b;});
    var spec = goodsSpec[goodsId];
    var goodsSpecId = 0;
    if(spec['saleSpec'][specIds.join(':')]){
        goodsPrice = parseFloat(spec['saleSpec'][specIds.join(':')].specPrice,10) - parseFloat(json['reduceMoney'],10);
        goodsSpecId = spec['saleSpec'][specIds.join(':')].id;
        combineJosn[combineId]['list'][obj.attr('combineGoodsId')]['shopPrice'] = goodsPrice;
        spec['combine_specIds_'+combineId] = specIds.join(':');
    }
    $('#combineGoodsPrice_'+combineId+'_'+goodsId).html(WST.lang('currency_symbol')+goodsPrice);
    $('.j-combine-goodsId'+combineId+'_'+goodsId).attr('combineGoodsSpecId',goodsSpecId);
    $('.j-combine-goodsId'+combineId+'_'+goodsId)[0].checked = true;
    checkCombineGoodsMoney(combineId,goodsId);
    layer.close(specLayer);
}
function combinecart(combineId){
    var isCheck = false;
    var params = {};
    params[combineId] = combineId;
    var combineGoodsIds = [];
    var json = combineJosn[combineId];
    var g = [];
    $('.j-combine'+combineId).each(function(){
   	  if($(this)[0].checked){
   	  	 ischeck = true;
	   	   combineGoodsIds.push($(this).attr('combineGoodsId'));
         if(json['isMain']==1){
             if(json['list'][$(this).attr('combineGoodsId')]['goodsType']==0){
                g.push('g'+$(this).attr('combineGoodsId')+'='+$(this).attr('combineGoodsSpecId'));
             }else{
                g.push('g'+$(this).attr('combineGoodsId')+'='+getGoodsPrice(combineId,$(this).attr('goodsId')).goodsSpecId);
             }
         }else{
             if(goodsInfo.id==$(this).attr('goodsId')){
                g.push('g'+$(this).attr('combineGoodsId')+'='+getGoodsPrice(combineId,$(this).attr('goodsId')).goodsSpecId);
             }else{
                g.push('g'+$(this).attr('combineGoodsId')+'='+$(this).attr('combineGoodsSpecId'));
             }
         }
	    }
    });
    if(combineGoodsIds.length<=1){
    	 WST.msg(WST.lang('combination_select_want_combine_goods'),{icon:2});
    	 return;
    }
    location.href = WST.AU('combination://carts/index','id='+window.base64.encode('combineId='+combineId+'&combineGoodsIds='+combineGoodsIds.join(',')+'&'+g.join('&')+'&time='+new Date().getTime()));
}
