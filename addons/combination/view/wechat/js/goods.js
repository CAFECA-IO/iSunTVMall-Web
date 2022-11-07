function initGoodsDetail(){
    new Swiper('.combine-goods-container', {
        freeMode : true,
        spaceBetween: 10,
        autoplay : false,
        speed:1200,
        loop : false,
        width : window.innerWidth*0.80,
        autoHeight: true, //高度随内容变化
        on: {
            resize: function(){
                this.params.width = window.innerWidth*0.80;
                this.update();
            }
        }
    });
}

function toCombineGoods(combineId){
    location.href = WST.AU('combination://combination/wechat','combineId='+combineId);
}
// 查看大图
function gViewImg(index,obj){
    var pswpElement = document.querySelectorAll('.pswp')[0];
    var gallery = $(obj).parent().data("gallery");
    if(gallery!=''){
        gallery = gallery.split(',').map(function(imgUrl,i){
          imgUrl = WST.conf.RESOURCE_PATH+"/"+imgUrl;
          var _obj = { src:imgUrl, w:0, h:0 };
          return _obj;
        })
      }
    // build items array
    if(!gallery || gallery.length==0)return;
    // define options (if needed)
    var options = {
      index: index
    };
    // Initializes and opens PhotoSwipe
    var gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, gallery, options);
    gallery.init();
    gallery.listen('imageLoadComplete', function(index, item) {
        if (item.h < 1 || item.w < 1) {
          var img = new Image();
          img.onload = function(){
            item.w = img.width;
            item.h = img.height;
            gallery.invalidateCurrItems()
            gallery.updateSize(true);
          }
          img.src = item.src
        }
    });
  }

function inMore(){
    if($("#arrow").css("display")=='none'){
        jQuery('#arrow').show(200);
        $("#layer").show();
    }else{
        jQuery('#arrow').hide(100);
        $("#layer").hide();
    }
}
var specGoods = null,specGoodsNo = 0;;
//弹框窗口并初始化
function dataShow(id){
    var json = goodsInfo['list'];
    for(var i=0;i<json.length;i++){
        if(json[i]['id']==id){
            specGoods = json[i];
            specGoodsNo = i;
            break;
        }
    }
    if(!specGoods)return;
    if(specGoods['isSpec']==1){
        var gettpl = document.getElementById('list').innerHTML;
        var specIds = ":"+specGoods['defaultSpecs']['specIds']+":";
        laytpl(gettpl).render({'specs':specGoods['specs'],'ids':specIds,'goodsUnit':specGoods['goodsUnit'],goodsStock:specGoods['defaultSpecs']['goodsStock']}, function(html){
            $('#specImage').attr('src',$('#j-combineGoodsImg'+specGoods['id']).attr('src'));
            $('#standard').html(html);
        });
        echo.init();
    }
    checkGoodsStock(id);
    if(specGoods['specNames'].length>0){
        var h = window.innerHeight;
        var th = (285+specGoods['specNames'].length*60);
        jQuery('#frame').css('height',(th>h?h:th)+'px');
    }
    $('.spec .j-option').click(function(){
        jQuery(this).addClass('active').siblings().removeClass('active');
        if(jQuery(this).attr('data-image')){
            jQuery("#specImage").attr('src',$(this).attr('data-image'));
        }
        checkGoodsStock(id);
    });
    jQuery('#cover').attr("onclick","javascript:dataHide();").show();
    jQuery('#frame').animate({"bottom": 0}, 500);
}
//选中事件
function checkGoodsStock(id){
    var specIds = [],stock = 0,goodsPrice=0,marketPrice=0;
    if(specGoods.isSpec==1){
        $('.spec .active').each(function(){
            specIds.push(parseInt($(this).attr('data-val'),10));
        });
        specIds.sort(function(a,b){return a-b;});
        if(specGoods.saleSpec[specIds.join(':')]){
            stock = specGoods.saleSpec[specIds.join(':')].specStock;
            goodsPrice = specGoods.saleSpec[specIds.join(':')].specPrice;
            marketPrice = specGoods.saleSpec[specIds.join(':')].marketPrice;
        }
    }else{
        stock = specGoods.goodsStock;
        goodsPrice = specGoods.goodsPrice;
        marketPrice = specGoods.marketPrice;
    }
    $('#j-goods-stock').html(stock);
    $('#j-shop-price').html(WST.lang('currency_symbol')+goodsPrice);
    $('#j-market-price').html(WST.lang('currency_symbol')+marketPrice);
    if(stock<=0){
        $('#j-specBtn').addClass('disabled').attr('onclick','javascript:void(0);');
    }else{
        $('#j-specBtn').removeClass('disabled').attr('onclick','javascript:selectSpec('+specGoods.id+');');
    }
}
//结束选中
function selectSpec(id){
    var specNames = [],specIds = [];
    $('.spec .active').each(function(){
        specNames.push($(this).attr('data-name'));
        specIds.push(parseInt($(this).attr('data-val'),10));
        if(jQuery(this).attr('data-image')){
            jQuery("#j-combineGoodsImg"+id).attr('src',$(this).attr('data-image'));
        }
    });
    specIds.sort(function(a,b){return a-b;});
    if(specGoods.saleSpec[specIds.join(':')]){
        goodsInfo['list'][specGoodsNo]['defaultSpecs'] = specGoods.saleSpec[specIds.join(':')];
        goodsInfo['list'][specGoodsNo]['goodsSpecId'] = goodsInfo['list'][specGoodsNo]['defaultSpecs']['id'];
        goodsInfo['list'][specGoodsNo]['marketPrice'] = goodsInfo['list'][specGoodsNo]['defaultSpecs']['marketPrice'];
        goodsInfo['list'][specGoodsNo]['shopPrice'] = goodsInfo['list'][specGoodsNo]['defaultSpecs']['specPrice'];
        goodsInfo['list'][specGoodsNo]['goodsStock'] = goodsInfo['list'][specGoodsNo]['defaultSpecs']['specStock'];

    }
    $('.j-spec'+id).html('已选：'+specNames.join(' '));
    $('.j-price'+id).html(WST.lang('currency_symbol')+specGoods.saleSpec[specIds.join(':')]['specPrice']);
    $('.j-spec'+id).attr('combineGoodsSpecId',specGoods.saleSpec[specIds.join(':')]['id']);
    checkGoodsMoney();
    dataHide();
}
function dataHide(){
    jQuery('#frame').animate({'bottom': '-100%'}, 500);
    jQuery('#cover').hide();
}
//计算列表商品总价
function checkGoodsMoney(){
    var chkGoods = {};
    $('.ui-icon-success-block').each(function(){
        chkGoods[$(this).attr('dataval')] = true;
    });
    var json = goodsInfo['list'];
    var totalMoney = 0;
    for(var i=0;i<json.length;i++){
        if(chkGoods[json[i]['id']]){
            totalMoney += parseFloat(json[i]['shopPrice'],10);
        }
    }
    $('#j-totalMoney').html(WST.lang('currency_symbol')+totalMoney);
}
function initIndex(){
    $('.ui-icon-choose').click(function(){
        var obj = $(this);
        if(obj.attr('goodstype')==1 || goodsInfo['combineType']==1)return;
        if(obj.hasClass('ui-icon-unchecked-s')){
            obj.addClass('ui-icon-success-block').removeClass('ui-icon-unchecked-s');
        }else{
            obj.addClass('ui-icon-unchecked-s').removeClass('ui-icon-success-block');
        }
        checkGoodsMoney();
    });
    echo.init();
}
function toSettlement(combineId){
    var isCheck = false;
    var params = {};
    params[combineId] = combineId;
    var combineGoodsIds = [];
    var g = [];
    var combineChk = {'1':false,'0':false};
    $('.ui-icon-success-block').each(function(){
        var spec = $('.j-spec'+$(this).attr('dataval'));
        combineGoodsIds.push($(this).attr('dataval'));
        if(spec.attr('combineGoodsSpecId')){
            g.push('g'+spec.attr('combineGoodsId')+'='+spec.attr('combineGoodsSpecId'));
        }
        if($(this).attr('goodsType')==1){
            combineChk['1'] = true;
        }else{
            combineChk['0'] = true;
        }
    });
    if(!(combineChk['1'] && combineChk['0'])){
        WST.msg('请至少选择一个搭配商品','info');
        return;
    }
    var json = goodsInfo['list'];
    for(var i=0;i<json.length;i++){
        if($.inArray(json[i]['id'].toString(),combineGoodsIds)!=-1){
            if(json[i]['goodsStock']<=0){
                WST.msg('商品'+(i+1)+'库存不足，请重新选择','info');
                return;
            }
        }
    }
    location.href = WST.AU('combination://carts/wxSettlement','id='+window.base64.encode('combineId='+combineId+'&combineGoodsIds='+combineGoodsIds.join(',')+'&'+g.join('&')+'&time='+new Date().getTime()));
}
