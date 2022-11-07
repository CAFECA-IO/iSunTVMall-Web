var submitStatus = false;
var base_url = '';
var mmg;
var mmg_coupon;
var mmg_new;
var global_good_select_ids=[];
var global_coupon_select_ids=[];
var global_new_select_ids=[];
function initSaleGrid(p,ids){
    if(ids!='')global_good_select_ids = ids.split(',');
    var h = WST.pageHeight();
    var cols = [
        {title:'', name:'goodsName', width: 50,sortable:true,renderer: function(val,item,rowIndex){
                var checked = '';
                if(global_good_select_ids && in_array(item['goodsId'], global_good_select_ids)){
                    checked = 'checked';
                }
                return "<span class='select-goods-id'><input type='checkbox' "+checked+" goods-id="+item['goodsId']+" onclick='selectGoodIds(this)'></span>";
            }},
        {title:WST.lang('custompage_goods_img'), name:'goodsImg', width: 100, renderer: function(val,item,rowIndex){
                var thumb = item['goodsImg'];
                thumb = thumb.replace('.','_thumb.');
                return "<span class='weixin'><img id='img' onmouseout='toolTip()' onmouseover='toolTip()' style='height:50px;width:50px;' src='"+WST.conf.RESOURCE_PATH+"/"+thumb
                    +"'><span class='imged' ><img  style='height:180px;width:180px;' src='"+WST.conf.RESOURCE_PATH+"/"+item['goodsImg']+"'></span></span>";
            }},
        {title:WST.lang('custompage_goods_name'), name:'goodsName', width: 300,sortable:true,renderer: function(val,item,rowIndex){
                return "<span  ><p class='wst-nowrap'>"+item['goodsName']+"</p></span>";
            }},
        {title:WST.lang('custompage_price'), name:'shopPrice' ,width:100,sortable:true, renderer: function(val,item,rowIndex){
                return WST.lang('currency_symbol')+item['shopPrice'];
            }},
        {title:WST.lang('custompage_belong_cat'), name:'goodsCatName' ,width:300,renderer: function(val,item,rowIndex){
                return "<span  ><p class='wst-nowrap'>"+item['goodsCatName']+"</p></span>";
            }},
        {title:WST.lang('custompage_goods_stock'), name:'goodsStock' ,width:50,sortable:true,align:'center'}
    ];

    mmg = $('.mmg').mmGrid({height: h,indexCol: true, indexColWidth:50, cols: cols,method:'POST',
        url: WST.AU('custompage://custompagedecoration/goodPageQuery'), fullWidthRows: true, autoLoad: false,remoteSort: true,sortName:'goodsSn',sortStatus:'desc',
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function loadGrid(p){
    p=(p<=1)?1:p;
    var params = WST.getParams('.j-ipt');
    params.page = p;
    mmg.load(params);
}
function toolTip(){
    WST.toolTip();
}
function initGridCoupon(p,ids){
    if(ids!='')global_coupon_select_ids = ids.split(',');
    var h = WST.pageHeight();
    var cols = [
        {title:'', name:'couponValue', width: 50,sortable:true,renderer: function(val,item,rowIndex){
                var checked = '';
                if(global_coupon_select_ids && in_array(item['couponId'], global_coupon_select_ids)){
                    checked = 'checked';
                }
                return "<span class='select-coupon-id'><input type='checkbox' "+checked+" coupon-id="+item['couponId']+" onclick='selectCouponIds(this)'></span>";
            }},
        {title:WST.lang('custompage_coupon_value'), name:'couponValue', width: 50,renderer:function(val,item,rowIndex){
                return WST.lang('currency_symbol')+item['couponValue'];
            }},
        {title:WST.lang('custompage_type'), name:'startPrice', width: 60,renderer:function(val,item,rowIndex){
                return (item['useCondition']==1)?(WST.lang('custompage_coupon_condition5',[item['useMoney'],item['couponValue']])):WST.lang("custompage_cash_coupon");
            }},
        {title:WST.lang('custompage_use_objects'), name:'floorPrice', width: 60,renderer:function(val,item,rowIndex){
                return (item['useObjects']==0)?WST.lang("custompage_use_objects_type1"):WST.lang("custompage_use_objects_type2");
            }},
        {title:WST.lang('custompage_start_date'), name:'startDate', width: 120},
        {title:WST.lang('custompage_end_date'), name:'endDate', width: 120},
        {title:WST.lang('custompage_coupon_num'), name:'couponNum', width: 30},
    ];

    mmg_coupon = $('.mmg-coupon').mmGrid({height: h,indexCol: true, cols: cols,method:'POST',nowrap:true,
        url: WST.AU('custompage://custompagedecoration/couponPageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg-coupon').mmPaginator({})
        ]
    });
    loadGridCoupon(p);
}
function loadGridCoupon(p){
    var params=WST.getParams('#useCondition');
    p=(p<=1)?1:p;
    params.page=p;
    mmg_coupon.load(params);
}
function initGridNew(p,ids){
    if(ids!='')global_new_select_ids = ids.split(',');
    var h = WST.pageHeight();
    var cols = [
        {title:'', name:'', width: 50,sortable:true,renderer: function(val,item,rowIndex){
                var checked = '';
                if(global_new_select_ids && in_array(item['articleId'], global_new_select_ids) ){
                    checked = 'checked';
                }
                return "<span class='select-new-id'><input type='checkbox' "+checked+" new-id="+item['articleId']+" onclick='selectNewIds(this)'></span>";
            }},
        {title:WST.lang('custompage_article_id'), name:'articleId' ,width:20,sortable:true},
        {title:WST.lang('custompage_title'), name:'articleTitle' ,width:200,sortable:true},
        {title:WST.lang('custompage_cat'), name:'catName' ,width:100,sortable:true},
        {title:WST.lang('custompage_create_time'), name:'createTime' ,width:120,sortable:true},
    ];

    mmg_new = $('.mmg-new').mmGrid({height:h,indexCol: true, cols: cols,method:'POST',nowrap:true,
        url: WST.AU('custompage://custompagedecoration/newPageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg-new').mmPaginator({})
        ]
    });
    loadGridNew(p);
}
function loadGridNew(p){
    var params = {};
    p=(p<=1)?1:p;
    params.page=p;
    mmg_new.load(params);
}
function in_array(search,array){
    for(var i in array){
        if(array[i]==search){
            return true;
        }
    }
    return false;
}
$(function(){
    base_url = WST.conf.RESOURCE_PATH;
    // 楼层商品组件标题列表名称长度计算
    $('.attr-content-floor-goods').each(function(idx,item){
        var floor_goods_title = $(item).find(".attr-content-floor-goods-title").val();
        if(floor_goods_title != undefined){
            $(item).find('.floor-goods-title-length').html(floor_goods_title.length);
        }
        $(item).find('.floor-goods-columns-name').each(function(index,obj){
            var columns_title = $(obj).val();
            if(columns_title != undefined){
                $(obj).parent().find('.floor-goods-columns-name-length').html(columns_title.length);
            }
        });
    });
    // 商品组件标题名称长度计算
    $('.attr-content-goods-group').each(function(idx,item){
        $(item).find('.attr-content-goods-group-title').each(function(index,obj){
            var goods_group_title = $(obj).val();
            if(goods_group_title != undefined){
                $(obj).parent().find('.goods-group-title-length').html(goods_group_title.length);
            }
        });
    });
    // 搜索组件提示文字与热搜关键字长度计算
    $('.attr-content-search').each(function(idx,item){
        $(item).find('.attr-content-search-type').each(function(index,obj){
            var search_type = $(obj).val();
            if(search_type != undefined){
                $(obj).parent().find('.search-type-length').html(search_type.length);
            }
        });
        $(item).find('.attr-content-search-text').each(function(index,obj){
            var search_text = $(obj).val();
            if(search_text != undefined){
                $(obj).parent().find('.search-text-length').html(search_text.length);
            }
        });
        $(item).find('.search-hots-text').each(function(index,obj){
            var hots_text = $(obj).val();
            if(hots_text != undefined){
                $(obj).parent().find('.search-hots-text-length').html(hots_text.length);
            }
        });
    });
    // 优惠券组件标题与描述长度计算
    $('.attr-content-coupon').each(function(idx,item){
        var coupon_title = $(item).find(".attr-content-coupon-title").val();
        if(coupon_title != undefined){
            $(item).find('.coupon-title-length').html(coupon_title.length);
        }
        var coupon_desc = $(item).find(".attr-content-coupon-desc").val();
        if(coupon_desc != undefined){
            $(item).find('.coupon-desc-length').html(coupon_desc.length);
        }
    });
    // 导航组件文字内容长度计算
    $('.attr-content-nav').each(function(idx,item){
        $(item).find('.attr-content-nav-text').each(function(index,obj){
            var nav_text = $(obj).val();
            if(nav_text != undefined){
                $(obj).parent().find('.nav-text-length').html(nav_text.length);
            }
        });
    });
    // 单文本组件提示文字长度计算
    $('.attr-content-txt').each(function(idx,item){
        var txt_text = $(item).find(".attr-content-txt-text").val();
        if(txt_text != undefined){
            $(item).find('.txt-text-length').html(txt_text.length);
        }
    });
    // 图文列表组件标题长度计算
    $('.attr-content-image-text').each(function(idx,item){
        var image_text_num = $(item).attr('image_text_num');
        var image_text_item = $(item).find('.image-text-style').val();
        var image_text_title = $(item).find(".attr-content-image-text-title").val();
        if(image_text_title != undefined){
            $(item).find('.image-text-title-length').html(image_text_title.length);
        }
        $(".effect-image-text").each(function(index, obj) {
            if($(obj).hasClass("image_text"+image_text_num)) {
                if (image_text_item == 1 || image_text_item == 2) {
                    if (image_text_title.length > 12) {
                        image_text_title = image_text_title.substr(0, 12) + '...';
                    }
                } else {
                    if (image_text_title.length > 20) {
                        image_text_title = image_text_title.substr(0, 20);
                    }
                }
                $(obj).find('.image-text-style-title').html(image_text_title);
            }
        });
    });
    // 图文列表组件简介长度计算
    $('.attr-content-image-text').each(function(idx,item){
        var image_text_num = $(item).attr('image_text_num');
        var image_text_item = $(item).find('.image-text-style').val();
        var image_text_desc = $(item).find(".attr-content-image-text-desc").val();
        if(image_text_desc != undefined){
            $(item).find('.image-text-desc-length').html(image_text_desc.length);
        }
        $(".effect-image-text").each(function(index, obj) {
            if($(obj).hasClass("image_text"+image_text_num)) {
                if (image_text_desc.length > 50) {
                    image_text_desc = image_text_desc.substr(0, 50)+ '...';
                }
                $(obj).find('.image-text-style-desc').html(image_text_desc);
            }
        });
    });
    // 多店铺组件顶部文字长度计算
    var shop_title = $(".attr-content-shop-title").val();
    if(shop_title != undefined){
        $('.shop-title-length').html(shop_title.length);
    }
    // 新闻组件新闻主题名称长度计算
    $('.attr-content-new').each(function(idx,item){
        var new_title = $(item).find(".attr-content-new-title").val();
        if(new_title != undefined){
            $(item).find('.new-title-length').html(new_title.length);
        }
    });
    // 营销活动组件新闻主题名称长度计算
    $('.attr-content-marketing').each(function(idx,item){
        var marketing_title = $(item).find(".attr-content-marketing-title").val();
        if(marketing_title != undefined){
            $(item).find('.marketing-title-length').html(marketing_title.length);
        }
    });
    // 页面组件封面图片上传初始化
    WST.upload({
        pick:'#page-picker',
        formData: {dir:'custompagedecoration'},
        accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
        callback:function(f){
            var json = WST.toAdminJson(f);
            if(json.status==1){
                $('#uploadMsg').empty().hide();
                var img_url = json.savePath+json.thumb; //保存到数据库的路径
                $("#page-picker").parent().attr("img_url",img_url);
                $('#page-picker').find('.page-poster').attr('src',WST.conf.RESOURCE_PATH+'/'+img_url);
                $('#page-picker').find('.page-poster').show();
                $('#page-picker').find('.page-poster-default').hide();
            }else{
                WST.msg(json.msg,{icon:2});
            }
        },
        progress:function(rate){
            $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
        }
    });

    // swiper组件图片上传初始化
    $(".banner-picker").each(function(idx,item){
        var swiper_id_selector = "#"+$(item).attr('id');
        var swiper_num = $(item).parent().parent().parent().attr("swiper_num");
        WST.upload({
            pick:swiper_id_selector,
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    var img_src = WST.conf.RESOURCE_PATH+'/'+$(item).parent().attr("img_url");
                    $(swiper_id_selector).parent().attr('img_url',img_url);
                    $(swiper_id_selector).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+img_url);
                    $(".effect-media-swiper").each(function(idx, item) {
                        if(parseInt($(item).attr("swiper_num")) == swiper_num){
                            if($(item).find("img").attr("src") == img_src){
                                $(item).find("img").attr("src",WST.conf.RESOURCE_PATH+'/'+img_url);
                            }
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
    });

    // 楼层商品组件图片上传初始化
    $(".floor-goods-picker").each(function(idx,item){
        var floor_goods_id_selector = "#"+$(item).attr('id');
        var floor_goods_num = $(item).parent().parent().parent().attr("floor_goods_num");
        WST.upload({
            pick:floor_goods_id_selector,
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $(floor_goods_id_selector).parent().attr('img_url',img_url);
                    $(floor_goods_id_selector).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+img_url);
                    $(".effect-floor-goods").each(function(idx,item){
                        if(parseInt($(item).attr("floor_goods_num")) == floor_goods_num){
                            $(item).find('.floor-img').attr('src',WST.conf.RESOURCE_PATH+'/'+img_url);
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
    });

    // 搜索组件图片上传初始化
    $(".search-picker").each(function(idx,item){
        var search_id_selector = "#"+$(item).attr('id');
        var search_num = $(item).parent().parent().parent().attr("search_num");
        WST.upload({
            pick:search_id_selector,
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $(search_id_selector).parent().attr('img_url',img_url);
                    $(search_id_selector).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+img_url);
                    $(".effect-search").each(function(idx,item){
                        if(parseInt($(item).attr("search_num")) == search_num){
                            $(item).find('.search-left img').attr('src',WST.conf.RESOURCE_PATH+'/'+img_url);
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
    });

    // 优惠券组件图片上传初始化
    $(".coupon-picker").each(function(idx,item){
        var coupon_id_selector = "#"+$(item).attr('id');
        var coupon_num = $(item).parent().parent().parent().attr("coupon_num");
        WST.upload({
            pick:coupon_id_selector,
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $(coupon_id_selector).parent().attr('img_url',img_url);
                    $(coupon_id_selector).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+img_url);
                    $(".effect-coupon").each(function(idx,item){
                        if(parseInt($(item).attr("coupon_num")) == coupon_num){
                            $(item).css('background',"url("+WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb+") no-repeat");
                            $(item).css('background-size',"100%");
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
    });

    // 单图组组件图片上传初始化
    $(".image-picker").each(function(idx,item){
        var image_id_selector = "#"+$(item).attr('id');
        var image_num = $(item).parent().parent().parent().attr("image_num");
        var image_item = $(item).parent().attr("image_item");
        WST.upload({
            pick:image_id_selector,
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $(image_id_selector).parent().attr('img_url',img_url);
                    $(image_id_selector).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+img_url);
                    $(".effect-image").each(function(idx,item){
                        if(parseInt($(item).attr("image_num")) == image_num){
                            $(item).find('img').each(function(key,value){
                                if(parseInt($(value).attr('image_item')) == image_item){
                                    $(value).attr('src',WST.conf.RESOURCE_PATH+'/'+img_url);
                                }
                            });
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
    });

    // 图片橱窗组件图片上传初始化
    $(".shopwindow-picker").each(function(idx,item){
        var shopwindow_id_selector = "#"+$(item).attr('id');
        var shopwindow_num = $(item).parent().parent().parent().attr("shopwindow_num");
        var shopwindow_item = $(item).parent().attr("shopwindow_item");
        WST.upload({
            pick:shopwindow_id_selector,
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $(shopwindow_id_selector).parent().attr('img_url',img_url);
                    $(shopwindow_id_selector).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+img_url);
                    $(".effect-shopwindow").each(function(idx,item){
                        if(parseInt($(item).attr("shopwindow_num")) == shopwindow_num){
                            $(item).find('img').each(function(key,value){
                                if(parseInt($(value).attr('shopwindow_item')) == shopwindow_item){
                                    $(value).attr('src',WST.conf.RESOURCE_PATH+'/'+img_url);
                                }
                            });
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
    });

    // 公告组件图片上传初始化
    $(".notice-picker").each(function(idx,item) {
        var notice_id_selector = "#" + $(item).attr('id');
        var notice_num = $(item).parent().parent().attr("notice_num");
        WST.upload({
            pick:notice_id_selector,
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $("#notice-picker-"+notice_num).parent().parent().find('.notice-img').val(img_url);
                    $("#notice-picker-"+notice_num).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                    $(".effect-notice").each(function(idx,item){
                        if(parseInt($(item).attr("notice_num")) == notice_num){
                            $(item).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
    });

    // 视频组件图片上传初始化
    $(".video-picker").each(function(idx,item){
        var video_id_selector = "#"+$(item).attr('id');
        var video_num = $(item).parent().parent().parent().attr("video_num");
        WST.upload({
            pick:video_id_selector,
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $(video_id_selector).parent().attr('img_url',img_url);
                    $(video_id_selector).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+img_url);
                    $(".effect-video").each(function(idx,item){
                        if(parseInt($(item).attr("video_num")) == video_num){
                            $(item).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+img_url);
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
    });

    // 图文列表组件图片上传初始化
    $(".image-text-picker").each(function(idx,item) {
        var image_text_id_selector = "#" + $(item).attr('id');
        var image_text_num = $(item).parent().parent().parent().parent().parent().attr("image_text_num");
        var image_text_class_name = 'image_text'+image_text_num;
        var image_text_img_num = parseInt(image_text_id_selector.substr(parseInt(image_text_id_selector.lastIndexOf("\-"))+1,image_text_id_selector.length));
        WST.upload({
            pick:image_text_id_selector,
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $(image_text_id_selector).parent().parent().attr('img_url',img_url);
                    $(image_text_id_selector).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                    $(".effect-image-text").each(function(idx,item){
                        if ($(item).hasClass(image_text_class_name)) {
                            $(item).find('.image-text-imgs').each(function(key,value){
                                $(value).find('img').each(function(key2,value2){
                                    if(image_text_img_num==1 && key2==0){
                                        $(value2).attr('src', WST.conf.RESOURCE_PATH + '/' + json.savePath + json.thumb);
                                    }
                                    if(image_text_img_num==2 && key2==1){
                                        $(value2).attr('src', WST.conf.RESOURCE_PATH + '/' + json.savePath + json.thumb);
                                    }
                                    if(image_text_img_num==3 && key2==2){
                                        $(value2).attr('src', WST.conf.RESOURCE_PATH + '/' + json.savePath + json.thumb);
                                    }
                                });
                            });
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
    });

    // 颜色拾取器初始化开始
    $('.goods-group-select-bg-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var goods_group_num = $(el).parent().parent().attr("goods_group_num");
                $(".effect-goods-group").each(function(idx, item) {
                    if($(item).hasClass("goods_group"+goods_group_num)){
                        $(item).css('background','#'+hex);
                        $(el).parent().parent().find(".goods-group-bg-color").val('#'+hex);
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    $('.swiper-indicator-select-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var swiper_num = $(el).parent().parent().attr("swiper_num");
                $(".effect-media-swiper").each(function(idx, item) {
                    if($(item).hasClass("swiper"+swiper_num)){
                        $(item).find("span").css('background','#'+hex);
                        $(el).parent().parent().find(".indicator-color").val('#'+hex);
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    $('.nav-select-bg-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var nav_num = $(el).parent().parent().attr("nav_num");
                $(".effect-nav").each(function(idx, item) {
                    if($(item).hasClass("nav"+nav_num)){
                        $(item).find(".navs-main").css('background','#'+hex);
                        $(el).parent().parent().find(".nav-bg-color").val('#'+hex);
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    $('.nav-select-text-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var nav_num = $(el).parent().parent().parent().attr("nav_num");
                var nav_item = $(el).parent().parent().attr("nav_item");
                $(".effect-nav").each(function(idx, item) {
                    if($(item).hasClass("nav"+nav_num)){
                        $(item).find(".navs").each(function(key, value) {
                            if($(value).attr("nav_item") == nav_item){
                                $(value).find(".navs-info p").css('color','#'+hex);
                                $(el).parent().find(".attr-content-nav-text-color").val('#'+hex);
                            }
                        });
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    $('.notice-select-bg-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var notice_num = $(el).parent().parent().attr("notice_num");
                $(".effect-notice").each(function(idx, item) {
                    if($(item).hasClass("notice"+notice_num)){
                        $(item).css('background','#'+hex);
                        $(el).parent().parent().find(".notice-bg-color").val('#'+hex);
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    $('.notice-select-text-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var notice_num = $(el).parent().parent().attr("notice_num");
                $(".effect-notice").each(function(idx, item) {
                    if($(item).hasClass("notice"+notice_num)){
                        $(item).find(".notice-info p").css('color','#'+hex);
                        $(el).parent().parent().find(".notice-text-color").val('#'+hex);
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    $('.coupon-title-select-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var coupon_num = $(el).parent().parent().attr("coupon_num");
                $(".effect-coupon").each(function(idx, item) {
                    if($(item).hasClass("coupon"+coupon_num)){
                        $(item).find('.coupon-title').css('color','#'+hex);
                        $(el).parent().parent().find(".coupon-title-color").val('#'+hex);
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    $('.coupon-desc-select-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var coupon_num = $(el).parent().parent().attr("coupon_num");
                $(".effect-coupon").each(function(idx, item) {
                    if($(item).hasClass("coupon"+coupon_num)){
                        $(item).find('.coupon-desc').css('color','#'+hex);
                        $(el).parent().parent().find(".coupon-desc-color").val('#'+hex);
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    $('.coupon-text-select-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var coupon_num = $(el).parent().parent().attr("coupon_num");
                $(".effect-coupon").each(function(idx, item) {
                    if($(item).hasClass("coupon"+coupon_num)){
                        $(item).find('.coupon-value-content').css('color','#'+hex);
                        $(item).find('.coupon-text').css('color','#'+hex);
                        $(item).find('.coupon-condition').css('color','#'+hex);
                        $(el).parent().parent().find(".coupon-text-color").val('#'+hex);
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    $('.coupon-btn-select-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var coupon_num = $(el).parent().parent().attr("coupon_num");
                $(".effect-coupon").each(function(idx, item) {
                    if($(item).hasClass("coupon"+coupon_num)){
                        $(item).find('.coupon-get').css('background','#'+hex);
                        $(el).parent().parent().find(".coupon-btn-color").val('#'+hex);
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    $('.coupon-btn-text-select-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var coupon_num = $(el).parent().parent().attr("coupon_num");
                $(".effect-coupon").each(function(idx, item) {
                    if($(item).hasClass("coupon"+coupon_num)){
                        $(item).find('.coupon-get').css('color','#'+hex);
                        $(el).parent().parent().find(".coupon-btn-text-color").val('#'+hex);
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    $('.image-select-bg-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var image_num = $(el).parent().parent().attr("image_num");
                $(".effect-image").each(function(idx, item) {
                    if($(item).hasClass("image"+image_num)){
                        $(item).css('background','#'+hex);
                        $(el).parent().parent().find(".image-bg-color").val('#'+hex);
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    $('.shopwindow-select-bg-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var shopwindow_num = $(el).parent().parent().attr("shopwindow_num");
                $(".effect-shopwindow").each(function(idx, item) {
                    if($(item).hasClass("shopwindow"+shopwindow_num)){
                        $(item).css('background','#'+hex);
                        $(el).parent().parent().find(".shopwindow-bg-color").val('#'+hex);
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    $('.blank-select-bg-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var blank_num = $(el).parent().parent().parent().attr("blank_num");
                $(".effect-blank").each(function(idx, item) {
                    if($(item).hasClass("blank"+blank_num)){
                        $(item).css('background','#'+hex);
                        $(el).parent().parent().parent().find(".blank-bg-color").val('#'+hex);
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    $('.line-select-bg-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var line_num = $(el).parent().parent().parent().attr("line_num");
                $(".effect-line").each(function(idx, item) {
                    if($(item).hasClass("line"+line_num)){
                        $(item).css('background','#'+hex);
                        $(el).parent().parent().parent().find(".line-bg-color").val('#'+hex);
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    $('.line-select-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var line_num = $(el).parent().parent().parent().attr("line_num");
                $(".effect-line").each(function(idx, item) {
                    if($(item).hasClass("line"+line_num)){
                        $(item).find(".line").css('border-color','#'+hex);
                        $(el).parent().parent().parent().find(".line-border-color").val('#'+hex);
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    $('.text-select-bg-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var text_num = $(el).parent().parent().attr("text_num");
                $(".effect-text").each(function(idx, item) {
                    if($(item).hasClass("text"+text_num)){
                        $(item).find(".text").css('background','#'+hex);
                        $(el).parent().parent().parent().find(".text-bg-color").val('#'+hex);
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    $('.txt-select-bg-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var txt_num = $(el).parent().parent().attr("txt_num");
                $(".effect-txt").each(function(idx, item) {
                    if($(item).hasClass("txt"+txt_num)){
                        $(item).find(".txt-info").css('background','#'+hex);
                        $(el).parent().parent().find(".txt-bg-color").val('#'+hex);
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    $('.txt-select-text-color').colpick({
        layout:'hex',
        submit:1,
        colorScheme:'dark',
        onSubmit:function(hsb,hex,rgb,el,bySetColor){
            if(!bySetColor){
                $(el).css('background','#'+hex);
                var txt_num = $(el).parent().parent().attr("txt_num");
                $(".effect-txt").each(function(idx, item) {
                    if($(item).hasClass("txt"+txt_num)){
                        $(item).find(".txt-info p").css('color','#'+hex);
                        $(el).parent().parent().find(".txt-text-color").val('#'+hex);
                    }
                });
            }
            $(el).colpickHide();
        }
    }).keyup(function(){
        $(this).colpickSetColor(this.value);
        $(this).colpickHide();
    });
    // 颜色拾取器初始化结束

    // 滑块初始化开始
    $('.swiper-range-slider').jRange({
        from: 0,
        to: 50,
        step: 1,
        format: '%s',
        width: 100,
        showLabels: false,
        isRange : true,
        showScale: false
    });
    $('.notice-range-slider').jRange({
        from: 0,
        to: 50,
        step: 1,
        format: '%s',
        width: 120,
        showLabels: false,
        isRange : true,
        showScale: false
    });
    $('.coupon-padding-range-slider').jRange({
        from: 0,
        to: 50,
        step: 1,
        format: '%s',
        width: 120,
        showLabels: false,
        isRange : true,
        showScale: false
    });
    $('.coupon-counts-range-slider').jRange({
        from: 0,
        to: 6,
        step: 1,
        format: '%s',
        width: 120,
        showLabels: false,
        isRange : true,
        showScale: false
    });
    $('.image-range-slider').jRange({
        from: 0,
        to: 50,
        step: 1,
        format: '%s',
        width: 100,
        showLabels: false,
        isRange : true,
        showScale: false
    });
    $('.video-range-slider').jRange({
        from: 0,
        to: 50,
        step: 1,
        format: '%s',
        width: 120,
        showLabels: false,
        isRange : true,
        showScale: false
    });
    $('.blank-range-slider').jRange({
        from: 0,
        to: 200,
        step: 1,
        format: '%s',
        width: 120,
        showLabels: false,
        isRange : true,
        showScale: false
    });
    $('.line-height-range-slider').jRange({
        from: 1,
        to: 20,
        step: 1,
        format: '%s',
        width: 120,
        showLabels: false,
        isRange : true,
        showScale: false
    });
    $('.line-margin-range-slider').jRange({
        from: 0,
        to: 50,
        step: 1,
        format: '%s',
        width: 120,
        showLabels: false,
        isRange : true,
        showScale: false
    });
    $('.text-range-slider').jRange({
        from: 0,
        to: 50,
        step: 1,
        format: '%s',
        width: 120,
        showLabels: false,
        isRange : true,
        showScale: false
    });
    $('.txt-range-slider').jRange({
        from: 0,
        to: 50,
        step: 1,
        format: '%s',
        width: 120,
        showLabels: false,
        isRange : true,
        showScale: false
    });
    $('.image-text-range-slider').jRange({
        from: 0,
        to: 50,
        step: 1,
        format: '%s',
        width: 120,
        showLabels: false,
        isRange : true,
        showScale: false
    });
    $('.marketing-range-slider').jRange({
        from: 0,
        to: 50,
        step: 1,
        format: '%s',
        width: 120,
        showLabels: false,
        isRange : true,
        showScale: false
    });
    // 滑块初始化结束

    // 富文本初始化开始
    var kindeditor_flag = false;
    $(".effect-text").each(function(idx, item) {
        kindeditor_flag = true;
        var text_num = $(item).attr("text_num");
        KindEditor.ready(function(K) {
            editor1 = K.create('.text-desc'+text_num, {
                height:'400px',
                width:'280px',
                uploadJson : WST.conf.ROOT+'/admin/index/editorUpload',
                allowFileManager : false,
                allowImageUpload : true,
                themeType : "default",
                items:[     'source', 'undo', 'redo',  'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
                            'plainpaste', 'wordpaste', 'justifyleft', 'justifycenter', 'justifyright',
                            'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
                            'superscript', 'clearhtml', 'quickformat', 'selectall',  'fullscreen',
                            'formatblock', 'fontname', 'fontsize',  'forecolor', 'hilitecolor', 'bold',
                            'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', 'image','multiimage','media','table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
                            'anchor', 'link', 'unlink'
                ],
                afterBlur: function(){ this.sync(); },
                afterChange : function() {
                    var content = $(document.getElementsByTagName('iframe')[idx].contentWindow.document.body).html();
                    $(".effect-text").each(function(idx, item) {
                        if($(item).hasClass("text"+text_num)){
                            $(item).find(".text").html(content);
                        }
                    });
                }
            });
        });
    });
    if(kindeditor_flag == false){
        KindEditor.ready(function(K) {
            editor1 = K.create('.text-desc', {
                height:'400px',
                width:'280px',
                uploadJson : WST.conf.ROOT+'/admin/index/editorUpload',
                allowFileManager : false,
                allowImageUpload : true,
                themeType : "default",
                items:[     'source', 'undo', 'redo',  'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
                        'plainpaste', 'wordpaste', 'justifyleft', 'justifycenter', 'justifyright',
                        'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
                        'superscript', 'clearhtml', 'quickformat', 'selectall',  'fullscreen',
                        'formatblock', 'fontname', 'fontsize',  'forecolor', 'hilitecolor', 'bold',
                        'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', 'image','multiimage','media','table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
                        'anchor', 'link', 'unlink'
                ],
                afterBlur: function(){ this.sync(); }
            });
        });
    }
    // 富文本初始化结束

    // 页面设置组件
    $(".effect-title").click(function(event){
        componentRemoveClasses(1);
        hideComponentAttrContent(1);
        $(".effect-title").addClass("effect-active");
        $(".effect-title").addClass("select");
        $(".attr-title").html(WST.lang("custompage_page_set"));
        $(".attr-content-page").show();
    });

    // 轮播组件
    $(".component-media-swiper").click(function(event){
        componentRemoveClasses(1);
        var swiper_num = 0;
        var swiper_num_arr = [];
        $(".effect-media-swiper").each(function(idx, item) {
            swiper_num = parseInt($(item).attr('swiper_num'));
            swiper_num_arr.push(swiper_num);
        });
        if(swiper_num_arr.length>0){
            swiper_num = Math.max.apply(null,swiper_num_arr)+1;
        }else{
            swiper_num = 1;
        }
        var swiper_class_name = "swiper"+swiper_num;
        var swiper_attr_class_name = "swiper_attr"+swiper_num;
        $(".effect-content").append("<div class='max-el-relative'><div class='effect-media-swiper "+swiper_class_name+"' swiper_num="+swiper_num+" ><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort-name' value='swiper'/><img src='"+base_url+"/upload/custompagedecoration/base/pc_banner_01.jpg' alt=''/><div class='dots round'><span class='roundness'></span><span class='roundness'></span></div><div class='btn-del' onclick='delSwiperBtn(this)'>"+WST.lang('custompage_del')+"</div></div><div class='border-absolute effect-active select' onclick='addSwiperClassActive(this)' onmouseover='addClassActive(this)' onmouseout='removeClassActive(this)'></div></div>");
        $(".attr-title").html(WST.lang("custompage_component_swiper"));
        hideComponentAttrContent(1);
        $(".attr-content").append("<div class='attr-content-swiper "+swiper_attr_class_name+"' swiper_num="+swiper_num+"><div class='attr-content-swiper-item' ><label class='d-label'>"+WST.lang('custompage_indicator_type')+"</label><input type='radio' value='1' name='swiper-indicator"+swiper_num+"' id='i-rectangle"+swiper_num+"' onclick='changeSwiperIndicator(this)'  /><label for='i-rectangle"+swiper_num+"' class='d-a-label'>"+WST.lang('custompage_rectangle')+"</label><input type='radio' value='2' name='swiper-indicator"+swiper_num+"' id='i-square"+swiper_num+"' onclick='changeSwiperIndicator(this)'  /><label for='i-square"+swiper_num+"' class='d-a-label'>"+WST.lang('custompage_square')+"</label><input type='radio' value='3' name='swiper-indicator"+swiper_num+"' id='i-roundness"+swiper_num+"' checked onclick='changeSwiperIndicator(this)'  /><label for='i-roundness"+swiper_num+"' class='d-a-label'>"+WST.lang('custompage_roundness')+"</label></div><div class='attr-content-swiper-item' ><label class='d-label'>"+WST.lang('custompage_swiper_interval')+"</label><select name='swiper-inteval"+swiper_num+"' onchange='changeSwiperInterval(this)'><option value='3'>3s</option><option value='4'>4s</option><option value='5'>5s</option><option value='6'>6s</option></select></div><div class='attr-content-swiper-item'><label class='d-label'>"+WST.lang('custompage_padding_top')+"</label><div class='attr-content-swiper-item-content'><input type='hidden' class='swiper-range-slider'  value='0' onchange='changeSwiperPadding(this,1)'/><span class='swiper-padding-top-value'>0</span><span class='pixel'>px ("+WST.lang('custompage_px')+")</span></div></div><div class='attr-content-swiper-item'><label class='d-label'>"+WST.lang('custompage_padding_bottom')+"</label><div class='attr-content-swiper-item-content'><input type='hidden' class='swiper-range-slider'  value='0' onchange='changeSwiperPadding(this,2)'/><span class='swiper-padding-bottom-value'>0</span><span class='pixel'>px ("+WST.lang('custompage_px')+")</span></div></div><div class='attr-content-swiper-item'><label class='d-label'>"+WST.lang('custompage_padding_left')+"</label><div class='attr-content-swiper-item-content'><input type='hidden' class='swiper-range-slider'  value='0' onchange='changeSwiperPadding(this,3)'/><span class='swiper-padding-left-value'>0</span><span class='pixel'>px ("+WST.lang('custompage_px')+")</span></div></div><div class='attr-content-swiper-item'><label class='d-label'>"+WST.lang('custompage_padding_right')+"</label><div class='attr-content-swiper-item-content'><input type='hidden' class='swiper-range-slider'  value='0' onchange='changeSwiperPadding(this,4)'/><span class='swiper-padding-right-value'>0</span><span class='pixel'>px ("+WST.lang('custompage_px')+")</span></div></div><div class='attr-content-swiper-item' ><label class='d-label'>"+WST.lang('custompage_indicator_color')+"</label><div class='select-component-item-color swiper-indicator-select-color' ></div></div><div class='attr-content-swiper-item-img' ><div class='attr-content-swiper-img' img_url='upload/custompagedecoration/base/pc_banner_01.jpg'><label class='d-label'>"+WST.lang('custompage_image')+"</label><div class='banner-picker' id='banner-picker-"+swiper_num+"-1'><img src='"+base_url+"/upload/custompagedecoration/base/pc_banner_01.jpg'/></div><div class='image-tips'>"+WST.lang('custompage_swiper_image_tips2')+"</div></div><label class='d-label'>"+WST.lang('custompage_link')+"</label><input type='text' value='' class='attr-content-swiper-link' /><div class='attr-btn-del' onclick='delSwiperItemBtn(this)'>X</div></div><div class='attr-content-swiper-item-add' onclick='addSwiperItem(this)'>"+WST.lang('custompage_add_one')+"</div><input type='hidden' class='indicator-type' value=3 /><input type='hidden' class='indicator-color' value='' /><input type='hidden' class='interval' value='3' /><input type='hidden' class='swiper-padding-top' value='0'><input type='hidden' class='swiper-padding-bottom' value='0'><input type='hidden' class='swiper-padding-left' value='0'><input type='hidden' class='swiper-padding-right' value='0'></div>");
        WST.upload({
            pick:'#banner-picker-'+swiper_num+'-1',
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $("#banner-picker-"+swiper_num+"-1").parent().attr('img_url',img_url);
                    $("#banner-picker-"+swiper_num+"-1").find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                    $(".effect-media-swiper").each(function(idx,item){
                       if($(item).hasClass(swiper_class_name)){
                           $(item).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                       }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
        $('.swiper-indicator-select-color').colpick({
            layout:'hex',
            submit:1,
            colorScheme:'dark',
            onSubmit:function(hsb,hex,rgb,el,bySetColor){
                if(!bySetColor){
                    $(el).css('background','#'+hex);
                    var swiper_num = $(el).parent().parent().attr("swiper_num");
                    $(".effect-media-swiper").each(function(idx, item) {
                        if($(item).hasClass("swiper"+swiper_num)){
                            $(item).find("span").css('background','#'+hex);
                            $(el).parent().parent().find(".indicator-color").val('#'+hex);
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
        $('.swiper-range-slider').jRange({
            from: 0,
            to: 50,
            step: 1,
            format: '%s',
            width: 100,
            showLabels: false,
            isRange : true,
            showScale: false
        });
        $(".attr-content-swiper").each(function(idx, item) {
            if($(item).hasClass("swiper_attr"+swiper_num)){
                $(item).show();
            }else{
                $(item).hide();
            }
        });
    });

    // 楼层商品组件
    $(".component-floor-goods").click(function(event){
        var hidden_cats_id = $('.hidden-child-cats-id').val();
        componentRemoveClasses(1);
        var floor_goods_num = 0;
        var floor_goods_num_arr = [];
        $(".effect-floor-goods").each(function(idx, item) {
            floor_goods_num = parseInt($(item).attr('floor_goods_num'));
            floor_goods_num_arr.push(floor_goods_num);
        });
        if(floor_goods_num_arr.length>0){
            floor_goods_num = Math.max.apply(null,floor_goods_num_arr)+1;
        }else{
            floor_goods_num = 1;
        }
        var floor_goods_class_name = "floor_goods"+floor_goods_num;
        var floor_goods_attr_class_name = "floor_goods_attr"+floor_goods_num;
        $(".effect-content").append("<div class='el-relative'><div class='interval'></div><div class='effect-floor-goods "+floor_goods_class_name+"' floor_goods_num="+floor_goods_num+" ><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort-name' value='floor_goods'/><div class='wst-flex-row wst-jsb wst-ac floors'><div class='floor-left'><p class='floor-goods-title-text'>"+WST.lang('custompage_floor_goods_caption')+"</p></div><div class='wst-flex-row wst-jfe wst-ac columns-title floor-right' ><div columns_item='1'>"+WST.lang('custompage_column_title')+"1</div><div columns_item='2'>"+WST.lang('custompage_column_title')+"2</div><div columns_item='3'>"+WST.lang('custompage_column_title')+"3</div></div></div><div class='wst-flex-row floors'><div class='floor-left'><img class='floor-img' src='"+base_url+"/upload/custompagedecoration/base/floor_goods_poster.png'/></div><div class='wst-flex-row wst-jsb wst-fw floor-right'><div class='goods' ><img src='"+base_url+"/upload/custompagedecoration/base/good_01.jpg'/><div class='goods-info' ><div class='wst-flex-column wst-jsb' ><p class='goods-name '>"+WST.lang('custompage_goods_name')+"</p><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p></div></div></div><div class='goods' ><img src='"+base_url+"/upload/custompagedecoration/base/good_02.jpg'/><div class='goods-info' ><div class='wst-flex-column wst-jsb' ><p class='goods-name '>"+WST.lang('custompage_goods_name')+"</p><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p></div></div></div><div class='goods' ><img src='"+base_url+"/upload/custompagedecoration/base/good_03.jpg'/><div class='goods-info' ><div class='wst-flex-column wst-jsb' ><p class='goods-name '>"+WST.lang('custompage_goods_name')+"</p><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p></div></div></div><div class='goods' ><img src='"+base_url+"/upload/custompagedecoration/base/good_04.jpg'/><div class='goods-info' ><div class='wst-flex-column wst-jsb' ><p class='goods-name '>"+WST.lang('custompage_goods_name')+"</p><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p></div></div></div><div class='goods' ><img src='"+base_url+"/upload/custompagedecoration/base/good_01.jpg'/><div class='goods-info' ><div class='wst-flex-column wst-jsb' ><p class='goods-name '>"+WST.lang('custompage_goods_name')+"</p><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p></div></div></div><div class='goods' ><img src='"+base_url+"/upload/custompagedecoration/base/good_02.jpg'/><div class='goods-info' ><div class='wst-flex-column wst-jsb' ><p class='goods-name '>"+WST.lang('custompage_goods_name')+"</p><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p></div></div></div><div class='goods' ><img src='"+base_url+"/upload/custompagedecoration/base/good_03.jpg'/><div class='goods-info' ><div class='wst-flex-column wst-jsb' ><p class='goods-name '>"+WST.lang('custompage_goods_name')+"</p><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p></div></div></div><div class='goods' ><img src='"+base_url+"/upload/custompagedecoration/base/good_04.jpg'/><div class='goods-info' ><div class='wst-flex-column wst-jsb' ><p class='goods-name '>"+WST.lang('custompage_goods_name')+"</p><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p></div></div></div></div></div><div class='btn-del' onclick='delFloorGoodsBtn(this)'>"+WST.lang('custompage_del')+"</div></div><div class='border-absolute' onclick='addFloorGoodsClassActive(this)' onmouseover='addClassActive(this)' onmouseout='removeClassActive(this)'></div></div>");
        $(".attr-title").html(WST.lang("custompage_component_floor_goods"));
        hideComponentAttrContent(1);
        $(".attr-content").append("<div class='attr-content-floor-goods "+floor_goods_attr_class_name+"' floor_goods_num="+floor_goods_num+"><div class='attr-content-floor-goods-item' ><label class='d-label'>"+WST.lang('custompage_floor_goods_title')+"</label><input type='text' value='"+WST.lang('custompage_floor_goods_caption')+"' class='attr-content-floor-goods-title' onkeyup='setFloorGoodsTitle(this)'/><span class='floor-goods-title-tip'><span class='floor-goods-title-length'>4</span>/10</span></div><div class='attr-content-floor-goods-item' ><div class='attr-content-floor-goods-img' img_url='/upload/custompagedecoration/base/floor_goods_poster.png' ><label class='d-label'>"+WST.lang('custompage_image')+"</label><div class='floor-goods-picker' id='floor-goods-picker-"+floor_goods_num+"' onclick='addFloorGoodsItemImg(this)'><img src='"+base_url+"/upload/custompagedecoration/base/floor_goods_poster.png' /></div><div class='image-tips'>"+WST.lang('custompage_floor_goods_image_tips')+"</div></div></div><div class='attr-content-floor-goods-item'><label class='d-label'>"+WST.lang('custompage_link')+"</label><input type='text' value='' class='attr-content-floor-goods-link' /></div><div class='attr-content-floor-goods-item' ><label class='d-label'"+WST.lang('custompage_goods_sort')+"/label><select name='floor-goods-order"+floor_goods_num+"' onchange='changeFloorGoodsOrder(this)'><option value='1' >"+WST.lang('custompage_goods_group_order1')+"</option><option value='2' >"+WST.lang('custompage_goods_group_order2')+"</option><option value='3' >"+WST.lang('custompage_goods_group_order3')+"</option><option value='4' >"+WST.lang('custompage_goods_group_order4')+"</option><option value='5' >"+WST.lang('custompage_goods_group_order5')+"</option><option value='6' >"+WST.lang('custompage_goods_group_order6')+"</option><option value='7' >"+WST.lang('custompage_goods_group_order7')+"</option><option value='8' >"+WST.lang('custompage_goods_group_order8')+"</option></select></div><div class='floor-goods-tips'>"+WST.lang('custompage_floor_goods_tips')+"</div><div class='attr-content-floor-goods-item-column' ><div class='wst-flex-row wst-ac'><label class='d-label'>"+WST.lang('custompage_column_title')+"</label><input type='text' value='"+WST.lang('custompage_column_title')+"1' class='floor-goods-columns-name' columns_item='1' onkeyup='setFloorGoodsColumnsTitle(this)' style='width:150px !important;' /><span class='floor-goods-columns-name-tip'><span class='floor-goods-columns-name-length'>5</span>/6</span></div><label class='d-label'>"+WST.lang('custompage_select_goods')+"</label><input type='radio' value='1' name='floor-goods-select"+floor_goods_num+"-1' id='floor-goods-select-condition"+floor_goods_num+"-1' onclick='changeFloorGoodsSelect(this)' columns_item='1' checked /><label for='floor-goods-select-condition1-1'>"+WST.lang('custompage_condition_select')+"</label><input type='radio' value='2' name='floor-goods-select"+floor_goods_num+"-1' id='floor-goods-select-manual"+floor_goods_num+"-1' onclick='changeFloorGoodsSelect(this)' columns_item='1' /><label for='floor-goods-select-manual1-1'>"+WST.lang('custompage_manual_add')+"</label><input type='hidden' class='floor-goods-select-ids-value' value='' columns_item='1'><input type='hidden' class='floor-goods-select-cats-id-value' value="+hidden_cats_id+" columns_item='1'><input type='hidden' class='floor-goods-tag-value' value='' columns_item='1'/><input type='hidden' class='floor-goods-min-price-value' value='' columns_item='1'/><input type='hidden' class='floor-goods-max-price-value' value='' columns_item='1'/><div class='attr-btn-del' onclick='delFloorGoodsColumnItemBtn(this)'>X</div></div><div class='attr-content-floor-goods-item-column' ><div class='wst-flex-row wst-ac'><label class='d-label'>"+WST.lang('custompage_column_title')+"</label><input type='text' value='"+WST.lang('custompage_column_title')+"2' class='floor-goods-columns-name' columns_item='2' onkeyup='setFloorGoodsColumnsTitle(this)' style='width:150px !important;' /><span class='floor-goods-columns-name-tip'><span class='floor-goods-columns-name-length'>5</span>/6</span></div><label class='d-label'>"+WST.lang('custompage_select_goods')+"</label><input type='radio' value='1' name='floor-goods-select"+floor_goods_num+"-2' id='floor-goods-select-condition"+floor_goods_num+"-2' onclick='changeFloorGoodsSelect(this)' columns_item='2' checked /><label for='floor-goods-select-condition"+floor_goods_num+"-2'>"+WST.lang('custompage_condition_select')+"</label><input type='radio' value='2' name='floor-goods-select"+floor_goods_num+"-2' id='floor-goods-select-manual"+floor_goods_num+"-2' onclick='changeFloorGoodsSelect(this)' columns_item='2' /><label for='floor-goods-select-manual"+floor_goods_num+"-2'>"+WST.lang('custompage_manual_add')+"</label><input type='hidden' class='floor-goods-select-ids-value' value='' columns_item='2'><input type='hidden' class='floor-goods-select-cats-id-value' value="+hidden_cats_id+" columns_item='2'><input type='hidden' class='floor-goods-tag-value' value='' columns_item='2'/><input type='hidden' class='floor-goods-min-price-value' value='' columns_item='2'/><input type='hidden' class='floor-goods-max-price-value' value='' columns_item='2'/><div class='attr-btn-del' onclick='delFloorGoodsColumnItemBtn(this)'>X</div></div><div class='attr-content-floor-goods-item-column' ><div class='wst-flex-row wst-ac'><label class='d-label'>"+WST.lang('custompage_column_title')+"</label><input type='text' value='"+WST.lang('custompage_column_title')+"3' class='floor-goods-columns-name' columns_item='3' onkeyup='setFloorGoodsColumnsTitle(this)' style='width:150px !important;' /><span class='floor-goods-columns-name-tip'><span class='floor-goods-columns-name-length'>5</span>/6</span></div><label class='d-label'>"+WST.lang('custompage_select_goods')+"</label><input type='radio' value='1' name='floor-goods-select"+floor_goods_num+"-3' id='floor-goods-select-condition"+floor_goods_num+"-3' onclick='changeFloorGoodsSelect(this)' columns_item='3' checked /><label for='floor-goods-select-condition"+floor_goods_num+"-3'>"+WST.lang('custompage_condition_select')+"</label><input type='radio' value='2' name='floor-goods-select"+floor_goods_num+"-3' id='floor-goods-select-manual1-3' onclick='changeFloorGoodsSelect(this)' columns_item='3' /><label for='floor-goods-select-manual"+floor_goods_num+"-3'>"+WST.lang('custompage_manual_add')+"</label><input type='hidden' class='floor-goods-select-ids-value' value='' columns_item='3'><input type='hidden' class='floor-goods-select-cats-id-value' value="+hidden_cats_id+" columns_item='3'><input type='hidden' class='floor-goods-tag-value' value='' columns_item='3'/><input type='hidden' class='floor-goods-min-price-value' value='' columns_item='3'/><input type='hidden' class='floor-goods-max-price-value' value='' columns_item='3'/><div class='attr-btn-del' onclick='delFloorGoodsColumnItemBtn(this)'>X</div></div><div class='attr-content-floor-goods-item-column-add' onclick='addFloorGoodsColumnItem(this)'>"+WST.lang('custompage_add_one')+"</div><input type='hidden' class='floor-goods-order' value='1' /></div>");
        WST.upload({
            pick:'#floor-goods-picker-'+floor_goods_num,
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $("#floor-goods-picker-"+floor_goods_num).parent().attr('img_url',img_url);
                    $("#floor-goods-picker-"+floor_goods_num).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                    $(".effect-floor-goods").each(function(idx,item){
                        if($(item).hasClass(floor_goods_class_name)){
                            $(item).find('.floor-img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
        $(".attr-content-floor-goods").each(function(idx, item) {
            if($(item).hasClass("floor_goods_attr"+floor_goods_num)){
                $(item).show();
            }else{
                $(item).hide();
            }
        });
    });

    // 商品组组件
    $(".component-goods-group").click(function(event){
        var hidden_cats_id = $('.hidden-cats-id').val();
        componentRemoveClasses(1);
        var goods_group_num = 0;
        var goods_group_num_arr = [];
        $(".effect-goods-group").each(function(idx, item) {
            goods_group_num = parseInt($(item).attr('goods_group_num'));
            goods_group_num_arr.push(goods_group_num);
        });
        if(goods_group_num_arr.length>0){
            goods_group_num = Math.max.apply(null,goods_group_num_arr)+1;
        }else{
            goods_group_num = 1;
        }
        var goods_group_class_name = "goods_group"+goods_group_num;
        var goods_group_attr_class_name = "goods_group_attr"+goods_group_num;
        $(".effect-content").append("<div class='el-relative'><div class='interval'></div><div class='effect-goods-group "+goods_group_class_name+"' goods_group_num="+goods_group_num+"><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort-name' value='goods_group'/><div class='wst-flex-row wst-jc'><div class='goods-head wst-flex-row wst-ac'><div class='goods-line'></div><p class='goods-group-title-text'>"+WST.lang('custompage_recommend_goods')+"</p><div class='goods-line'></div></div></div><div class='wst-flex-row goods-main wst-jsb wst-fw'><div class='goods' goods_item='1'><img src='"+base_url+"/upload/custompagedecoration/base/good_01.jpg'/><div class='goods-info' ><div class='wst-flex-column wst-jsb' ><p class='goods-name '>"+WST.lang('custompage_goods_name')+"</p><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p></div></div></div><div class='goods' goods_item='2'><img src='"+base_url+"/upload/custompagedecoration/base/good_02.jpg'/><div class='goods-info' ><div class='wst-flex-column wst-jsb' ><p class='goods-name '>"+WST.lang('custompage_goods_name')+"</p><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p></div></div></div><div class='goods' goods_item='3'><img src='"+base_url+"/upload/custompagedecoration/base/good_03.jpg'/><div class='goods-info' ><div class='wst-flex-column wst-jsb' ><p class='goods-name '>"+WST.lang('custompage_goods_name')+"</p><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p></div></div></div><div class='goods' goods_item='4'><img src='"+base_url+"/upload/custompagedecoration/base/good_04.jpg'/><div class='goods-info' ><div class='wst-flex-column wst-jsb' ><p class='goods-name '>"+WST.lang('custompage_goods_name')+"</p><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p></div></div></div><div class='goods' goods_item='5'><img src='"+base_url+"/upload/custompagedecoration/base/good_05.jpg'/><div class='goods-info' ><div class='wst-flex-column wst-jsb' ><p class='goods-name '>"+WST.lang('custompage_goods_name')+"</p><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p></div></div></div><div class='goods' goods_item='6'><img src='"+base_url+"/upload/custompagedecoration/base/good_01.jpg'/><div class='goods-info' ><div class='wst-flex-column wst-jsb' ><p class='goods-name '>"+WST.lang('custompage_goods_name')+"</p><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p></div></div></div><div class='goods' goods_item='7'><img src='"+base_url+"/upload/custompagedecoration/base/good_02.jpg'/><div class='goods-info' ><div class='wst-flex-column wst-jsb' ><p class='goods-name '>"+WST.lang('custompage_goods_name')+"</p><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p></div></div></div><div class='goods' goods_item='8'><img src='"+base_url+"/upload/custompagedecoration/base/good_03.jpg'/><div class='goods-info' ><div class='wst-flex-column wst-jsb' ><p class='goods-name '>"+WST.lang('custompage_goods_name')+"</p><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p></div></div></div><div class='goods' goods_item='9'><img src='"+base_url+"/upload/custompagedecoration/base/good_04.jpg'/><div class='goods-info' ><div class='wst-flex-column wst-jsb' ><p class='goods-name '>"+WST.lang('custompage_goods_name')+"</p><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p></div></div></div><div class='goods' goods_item='10'><img src='"+base_url+"/upload/custompagedecoration/base/good_05.jpg'/><div class='goods-info' ><div class='wst-flex-column wst-jsb' ><p class='goods-name '>"+WST.lang('custompage_goods_name')+"</p><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p></div></div></div></div><div class='btn-del' onclick='delGoodsGroupBtn(this)'>"+WST.lang('custompage_del')+"</div></div><div class='border-absolute' onclick='addGoodsGroupClassActive(this)' onmouseover='addClassActive(this)' onmouseout='removeClassActive(this)'></div></div>");
        $(".attr-title").html(WST.lang("custompage_component_goods_group"));
        hideComponentAttrContent(1);
        var goodsCatsHtml = $('.goodsCatsHtml').html();
        $(".attr-content").append("<div class='attr-content-goods-group "+goods_group_attr_class_name+"' goods_group_num="+goods_group_num+"><div class='attr-content-goods-group-item' ><label class='d-label'>"+WST.lang('custompage_goods_group_title')+"</label><input type='text' value='"+WST.lang('custompage_recommend_goods')+"' class='attr-content-goods-group-title' onkeyup='setGoodsGroupTitle(this)'/><span class='goods-group-title-tip'><span class='goods-group-title-length'>4</span>/10</span></div><div class='attr-content-goods-group-item' ><label class='d-label'>"+WST.lang('custompage_goods_group_title')+"</label><input type='radio' value='1' name='goods_group_title_is_show"+goods_group_num+"' id='goods-group-title-show"+goods_group_num+"' onclick='changeGoodsGroupTitleDisplay(this)' checked /><label for='goods-group-title-show"+goods_group_num+"'>"+WST.lang('custompage_show')+"</label><input type='radio' value='0' name='goods_group_title_is_show"+goods_group_num+"' id='goods-group-title-hide"+goods_group_num+"' onclick='changeGoodsGroupTitleDisplay(this)'  /><label for='goods-group-title-hide"+goods_group_num+"'>"+WST.lang('custompage_hide')+"</label></div><div class='attr-content-goods-group-item' ><label class='d-label'>"+WST.lang('custompage_goods_num')+"</label><input type='radio' value='5' name='goods_num"+goods_group_num+"' id='goods-num-five"+goods_group_num+"' onclick='changeGoodsNum(this)'  /><label for='goods-num-five"+goods_group_num+"'>5"+WST.lang('custompage_goods_unit')+"</label><input type='radio' value='10' name='goods_num"+goods_group_num+"' id='goods-num-ten"+goods_group_num+"' onclick='changeGoodsNum(this)' checked /><label for='goods-num-ten"+goods_group_num+"'>10"+WST.lang('custompage_goods_unit')+"</label></div><div class='goods-num-tips'>"+WST.lang('custompage_goods_num_tips')+"</div><div class='attr-content-goods-group-item' ><label class='d-label'"+WST.lang('custompage_goods_sort')+"/label><select name='goods-group-order"+goods_group_num+"' onchange='changeGoodsGroupOrder(this)'><option value='1' >"+WST.lang('custompage_goods_group_order1')+"</option><option value='2' >"+WST.lang('custompage_goods_group_order2')+"</option><option value='3' >"+WST.lang('custompage_goods_group_order3')+"</option><option value='4' >"+WST.lang('custompage_goods_group_order4')+"</option><option value='5' >"+WST.lang('custompage_goods_group_order5')+"</option><option value='6' >"+WST.lang('custompage_goods_group_order6')+"</option><option value='7' >"+WST.lang('custompage_goods_group_order7')+"</option><option value='8' >"+WST.lang('custompage_goods_group_order8')+"</option></select></div><div class='attr-content-goods-group-item' ><label class='d-label'>"+WST.lang('custompage_select_goods')+"</label><input type='radio' value='1' name='goods-select"+goods_group_num+"' id='goods-select-condition"+goods_group_num+"' onclick='changeGoodsSelect(this)' checked/><label for='goods-select-condition"+goods_group_num+"'>"+WST.lang('custompage_condition_select')+"</label><input type='radio' value='2' name='goods-select"+goods_group_num+"' id='goods-select-manual"+goods_group_num+"' onclick='changeGoodsSelect(this)' /><label for='goods-select-manual"+goods_group_num+"'>"+WST.lang('custompage_manual_add')+"</label><input type='hidden' class='goods-select-ids-value' value='' /><input type='hidden' class='goods-select-cats-id-value' value="+hidden_cats_id+" /><input type='hidden' class='goods-tag-value' value='' /><input type='hidden' class='goods-min-price-value' value='' /><input type='hidden' class='goods-max-price-value' value='' /></div><input type='hidden' class='show-goods-group-title' value='1' /><input type='hidden' class='goods-group-order' value='1' /><input type='hidden' class='goods-group-goods-nums' value='10' /></div>");
        $(".attr-content-goods-group").each(function(idx, item) {
            if($(item).hasClass("goods_group_attr"+goods_group_num)){
                $(item).show();
            }else{
                $(item).hide();
            }
        });
    });

    // 导航组件
    $(".component-nav").click(function(event){
        componentRemoveClasses(1);
        var nav_num = 0;
        $(".effect-nav").each(function(idx, item) {
            nav_num = parseInt($(item).attr('nav_num'));
        });
        nav_num += 1;
        if(nav_num > 1){
            WST.msg(WST.lang('custompage_nav_only_add_one'),{time:1000,anim: 6});
            return;
        }
        var nav_class_name = "nav"+nav_num;
        var nav_attr_class_name = "nav_attr"+nav_num;
        $(".effect-content").append("<div class='max-el-relative'><div class='interval'></div><div class='effect-nav wst-flex-row wst-jc "+nav_class_name+"' nav_num="+nav_num+" ><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort-name' value='nav'/><div class='navs-main wst-flex-row wst-ac'><div class='nav-left'>"+WST.lang('custompage_all_goods_cat')+"</div><div class='nav-right wst-flex-row wst-ac'><div class='nav-item' nav_item='1'><div class='nav-text'>"+WST.lang('custompage_bottom_text')+"</div></div><div class='nav-item' nav_item='2'><div class='nav-text'>"+WST.lang('custompage_bottom_text')+"</div></div><div class='nav-item' nav_item='3'><div class='nav-text'>"+WST.lang('custompage_bottom_text')+"</div></div><div class='nav-item' nav_item='4'><div class='nav-text'>"+WST.lang('custompage_bottom_text')+"</div></div></div></div><div class='btn-del' onclick='delNavBtn(this)'>"+WST.lang('custompage_del')+"</div></div><div class='border-absolute' onclick='addNavClassActive(this)' onmouseover='addClassActive(this)' onmouseout='removeClassActive(this)'></div></div>");
        $(".attr-title").html(WST.lang("custompage_component_nav"));
        hideComponentAttrContent(1);
        $(".attr-content").append("<div class='attr-content-nav "+nav_attr_class_name+"' nav_num="+nav_num+"><div class='attr-content-nav-item' ><label class='d-label'>"+WST.lang('custompage_background_color')+"</label><div class='select-component-item-color nav-select-bg-color' ></div></div><div class='attr-content-nav-item' ><label class='d-label'>"+WST.lang('custompage_show_cat')+"</label><input type='radio' value='1' name='nav-category"+nav_num+"'  id='n-category-show"+nav_num+"' onclick='changeNavCategoryDisplay(this)' checked /><label for='n-category-show"+nav_num+"'>"+WST.lang('custompage_show')+"</label><input type='radio' value='0' name='nav-category"+nav_num+"' id='n-category-hide"+nav_num+"' onclick='changeNavCategoryDisplay(this)' /><label for='n-category-hide"+nav_num+"'>"+WST.lang('custompage_hide')+"</label></div><div class='attr-content-nav-item-group' nav_item='1'><div class='attr-content-nav-info'><label class='nav-label'>"+WST.lang('custompage_text_content')+"</label><input type='text' value='"+WST.lang('custompage_bottom_text')+"' class='attr-content-nav-text' onkeyup='setNavItemTitle(this)' /><span class='nav-text-tip'><span class='nav-text-length'>4</span>/4</span></div><div class='attr-content-nav-info'><label class='nav-label'>"+WST.lang('custompage_text_color')+"</label><div class='select-component-item-color nav-select-text-color' style='background:#333333;' ></div><input type='hidden' class='attr-content-nav-text-color' value='#333333' /></div><div class='attr-content-nav-info'><label class='nav-label'>"+WST.lang('custompage_link_address')+"</label><input type='text' value='' class='attr-content-nav-link' /></div><div class='attr-btn-del' onclick='delNavItemBtn(this)'>X</div></div><div class='attr-content-nav-item-group' nav_item='2'><div class='attr-content-nav-info'><label class='nav-label'>"+WST.lang('custompage_text_content')+"</label><input type='text' value='"+WST.lang('custompage_bottom_text')+"' class='attr-content-nav-text' onkeyup='setNavItemTitle(this)' /><span class='nav-text-tip'><span class='nav-text-length'>4</span>/4</span></div><div class='attr-content-nav-info'><label class='nav-label'>"+WST.lang('custompage_text_color')+"</label><div class='select-component-item-color nav-select-text-color' style='background:#333333;' ></div><input type='hidden' class='attr-content-nav-text-color' value='#333333' /></div><div class='attr-content-nav-info'><label class='nav-label'>"+WST.lang('custompage_link_address')+"</label><input type='text' value='' class='attr-content-nav-link' /></div><div class='attr-btn-del' onclick='delNavItemBtn(this)'>X</div></div><div class='attr-content-nav-item-group' nav_item='3'><div class='attr-content-nav-info'><label class='nav-label'>"+WST.lang('custompage_text_content')+"</label><input type='text' value='"+WST.lang('custompage_bottom_text')+"' class='attr-content-nav-text' onkeyup='setNavItemTitle(this)' /><span class='nav-text-tip'><span class='nav-text-length'>4</span>/4</span></div><div class='attr-content-nav-info'><label class='nav-label'>"+WST.lang('custompage_text_color')+"</label><div class='select-component-item-color nav-select-text-color' style='background:#333333;' ></div><input type='hidden' class='attr-content-nav-text-color' value='#333333' /></div><div class='attr-content-nav-info'><label class='nav-label'>"+WST.lang('custompage_link_address')+"</label><input type='text' value='' class='attr-content-nav-link' /></div><div class='attr-btn-del' onclick='delNavItemBtn(this)'>X</div></div><div class='attr-content-nav-item-group' nav_item='4'><div class='attr-content-nav-info'><label class='nav-label'>"+WST.lang('custompage_text_content')+"</label><input type='text' value='"+WST.lang('custompage_bottom_text')+"' class='attr-content-nav-text' onkeyup='setNavItemTitle(this)' /><span class='nav-text-tip'><span class='nav-text-length'>4</span>/4</span></div><div class='attr-content-nav-info'><label class='nav-label'>"+WST.lang('custompage_text_color')+"</label><div class='select-component-item-color nav-select-text-color' style='background:#333333;' ></div><input type='hidden' class='attr-content-nav-text-color' value='#333333' /></div><div class='attr-content-nav-info'><label class='nav-label'>"+WST.lang('custompage_link_address')+"</label><input type='text' value='' class='attr-content-nav-link' /></div><div class='attr-btn-del' onclick='delNavItemBtn(this)'>X</div></div><div class='attr-content-nav-item-add' onclick='addNavItem(this)'>"+WST.lang('custompage_add_one')+"</div><input type='hidden' class='nav-bg-color' value='#ffffff' /><input type='hidden' class='show-nav-category' value='1' /></div>");
        $('.nav-select-bg-color').colpick({
            layout:'hex',
            submit:1,
            colorScheme:'dark',
            onSubmit:function(hsb,hex,rgb,el,bySetColor){
                if(!bySetColor){
                    $(el).css('background','#'+hex);
                    var nav_num = $(el).parent().parent().attr("nav_num");
                    $(".effect-nav").each(function(idx, item) {
                        if($(item).hasClass("nav"+nav_num)){
                            $(item).find(".navs-main").css('background','#'+hex);
                            $(el).parent().parent().find(".nav-bg-color").val('#'+hex);
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
        $('.nav-select-text-color').colpick({
            layout:'hex',
            submit:1,
            colorScheme:'dark',
            onSubmit:function(hsb,hex,rgb,el,bySetColor){
                if(!bySetColor){
                    $(el).css('background','#'+hex);
                    var nav_num = $(el).parent().parent().parent().attr("nav_num");
                    var nav_item = $(el).parent().parent().attr("nav_item");
                    $(".effect-nav").each(function(idx, item) {
                        if($(item).hasClass("nav"+nav_num)){
                            $(item).find(".nav-right .nav-item").each(function(key, value) {
                                if($(value).attr("nav_item") == nav_item){
                                    $(value).css('color','#'+hex);
                                    $(el).parent().find(".attr-content-nav-text-color").val('#'+hex);
                                }
                            });
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
        $(".attr-content-nav").each(function(idx, item) {
            if($(item).hasClass("nav_attr"+nav_num)){
                $(item).show();
            }else{
                $(item).hide();
            }
        });
    });

    // 公告组件
    $(".component-notice").click(function(event){
        componentRemoveClasses(1);
        var notice_num = 0;
        $(".effect-notice").each(function(idx, item) {
            notice_num = parseInt($(item).attr('notice_num'));
        });
        notice_num += 1;
        if(notice_num > 1){
            WST.msg(WST.lang('custompage_notice_add_tips'),{time:1000,anim: 6});
            return;
        }
        var notice_class_name = "notice"+notice_num;
        var notice_attr_class_name = "notice_attr"+notice_num;
        $(".effect-content").append("<div class='el-relative'><div class='interval'></div><div class='effect-notice "+notice_class_name+"' notice_num="+notice_num+" ><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort-name' value='notice'/><div class='notice-info'><div class='notice-left'><img src='"+base_url+"/upload/custompagedecoration/base/notice.png' alt=''/></div><div class='notice-right'><p>"+WST.lang('custompage_here_input_notice_content')+"</p></div></div><div class='btn-del' onclick='delNoticeBtn(this)'>"+WST.lang('custompage_del')+"</div></div><div class='border-absolute effect-active select' onclick='addNoticeClassActive(this)' onmouseover='addClassActive(this)' onmouseout='removeClassActive(this)'></div></div>");
        $(".attr-title").html(WST.lang("custompage_component_notice"));
        hideComponentAttrContent(1);
        $(".attr-content").append("<div class='attr-content-notice "+notice_attr_class_name+"' notice_num="+notice_num+"><div class='attr-content-notice-item'><label class='d-label'>"+WST.lang('custompage_vertical_padding')+"</label><div class='attr-content-notice-item-content'><input type='hidden' class='notice-range-slider' value='0' onchange='changeNoticePadding(this)'/><span class='notice-vertical-padding-value'>0</span><span class='pixel'>px ("+WST.lang('custompage_px')+")</span></div></div><div class='attr-content-notice-item' ><label class='d-label'>"+WST.lang('custompage_background_color')+"</label><div class='select-component-item-color notice-select-bg-color'></div></div><div class='attr-content-notice-item' ><label class='d-label'>"+WST.lang('custompage_text_color')+"</label><div class='select-component-item-color notice-select-text-color' style='background:#000000;'></div></div><div class='attr-content-notice-item'><label class='d-label'>"+WST.lang('custompage_notice_icon')+"</label><div class='notice-picker' id='notice-picker-"+notice_num+"' onclick='addNoticeImg(this)'><img src='"+base_url+"/upload/custompagedecoration/base/notice.png'/></div></div><div class='attr-content-notice-item' ><label class='d-label'>"+WST.lang('custompage_scroll_direction')+"</label><input type='radio' value='1' name='notice-direction' id='notice-direction-vertical' onclick='changeNoticeDirection(this)' checked /><label for='notice-direction-vertical' class='d-a-label'>"+WST.lang('custompage_vertical')+"</label><input type='radio' value='2' name='notice-direction' id='notice-direction-horizontal' onclick='changeNoticeDirection(this)'   /><label for='notice-direction-horizontal' class='d-a-label'>"+WST.lang('custompage_horizontal')+"</label></div><div class='attr-content-notice-item-group' ><div><label class='d-label'>"+WST.lang('custompage_notice')+"</label><input type='text' value='"+WST.lang('custompage_here_input_notice_content')+"' class='attr-content-notice-text'  onkeyup='setNoticeText(this)'/></div><div><label class='d-label'>"+WST.lang('custompage_link')+"</label><input type='text' value='' class='attr-content-notice-link' /></div><div class='attr-btn-del' onclick='delNoticeItemBtn(this)'>X</div></div><div class='attr-content-notice-item-group' ><div><label class='d-label'>"+WST.lang('custompage_notice')+"</label><input type='text' value='"+WST.lang('custompage_here_input_notice_content')+"' class='attr-content-notice-text' onkeyup='setNoticeText(this)' /></div><div><label class='d-label'>"+WST.lang('custompage_link')+"</label><input type='text' value='' class='attr-content-notice-link' /></div><div class='attr-btn-del' onclick='delNoticeItemBtn(this)'>X</div></div><div class='attr-content-notice-item-group-add' onclick='addNoticeItem(this)'>"+WST.lang('custompage_add_one')+"</div><input type='hidden' class='notice-bg-color' value='#ffffff' /><input type='hidden' class='notice-text-color' value='#000000' /><input type='hidden' class='notice-img' value='/upload/custompagedecoration/base/notice.png' /><input type='hidden' class='notice-vertical-padding' value='0'><input type='hidden' class='notice-direction' value='1' /></div>");
        WST.upload({
            pick:'#notice-picker-'+notice_num,
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $("#notice-picker-"+notice_num).parent().parent().find('.notice-img').val(img_url);
                    $("#notice-picker-"+notice_num).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                    $(".effect-notice").each(function(idx,item){
                        if($(item).hasClass(notice_class_name)){
                            $(item).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
        $('.notice-select-bg-color').colpick({
            layout:'hex',
            submit:1,
            colorScheme:'dark',
            onSubmit:function(hsb,hex,rgb,el,bySetColor){
                if(!bySetColor){
                    $(el).css('background','#'+hex);
                    var notice_num = $(el).parent().parent().attr("notice_num");
                    $(".effect-notice").each(function(idx, item) {
                        if($(item).hasClass("notice"+notice_num)){
                            $(item).css('background','#'+hex);
                            $(el).parent().parent().find(".notice-bg-color").val('#'+hex);
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
        $('.notice-select-text-color').colpick({
            layout:'hex',
            submit:1,
            colorScheme:'dark',
            onSubmit:function(hsb,hex,rgb,el,bySetColor){
                if(!bySetColor){
                    $(el).css('background','#'+hex);
                    var notice_num = $(el).parent().parent().attr("notice_num");
                    $(".effect-notice").each(function(idx, item) {
                        if($(item).hasClass("notice"+notice_num)){
                            $(item).find(".notice-info p").css('color','#'+hex);
                            $(el).parent().parent().find(".notice-text-color").val('#'+hex);
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
        $('.notice-range-slider').jRange({
            from: 0,
            to: 50,
            step: 1,
            format: '%s',
            width: 120,
            showLabels: false,
            isRange : true,
            showScale: false
        });
        $(".attr-content-notice").each(function(idx, item) {
            if($(item).hasClass("notice_attr"+notice_num)){
                $(item).show();
            }else{
                $(item).hide();
            }
        });
    });

    // 搜索框组件
    $(".component-search").click(function(){
        componentRemoveClasses(1);
        var search_num = 0;
        $(".effect-search").each(function(idx, item) {
            search_num = parseInt($(item).attr('search_num'));
        });
        search_num += 1;
        if(search_num > 1){
            WST.msg(WST.lang('custompage_search_add_tips'),{time:1000,anim: 6});
            return;
        }
        var search_class_name = "search"+search_num;
        var search_attr_class_name = "search_attr"+search_num;
        $(".effect-content").append("<div class='el-relative'><div class='interval'></div><div class='effect-search "+search_class_name+"' search_num="+search_num+" ><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort-name' value='search'/><div class='search-info '><div class='search-left'><img src='"+base_url+"/upload/custompagedecoration/base/banner_01.jpg'/></div><div class='search-mid '><div class='search-mid-content wst-flex-row wst-jsb'><div class='wst-flex-row'><p class='search-type'>"+WST.lang('custompage_search_goods')+"</p><p class='search-text'>"+WST.lang('custompage_search_goods_demo')+"</p></div><div class='search-btn wst-flex-row wst-center'>"+WST.lang('custompage_search')+"</div></div><div class='wst-flex-row search-hot-container'><div class='search-hot-item wst-flex-row wst-ac' hots_item='1'><p>"+WST.lang('custompage_fruit')+"</p></div><div class='search-hot-item wst-flex-row wst-ac' hots_item='2'><div class='search-hot-line'></div><p>"+WST.lang('custompage_phone')+"</p></div><div class='search-hot-item wst-flex-row wst-ac' hots_item='3'><div class='search-hot-line'></div><p>"+WST.lang('custompage_clothes')+"</p></div></div></div><div class='search-right wst-flex-row wst-center'><div class='search-icon'></div><p>"+WST.lang('custompage_my_cart')+"</p></div></div><div class='btn-del' onclick='delSearchBtn(this)'>"+WST.lang('custompage_del')+"</div></div><div class='border-absolute' onclick='addSearchClassActive(this)' onmouseover='addClassActive(this)' onmouseout='removeClassActive(this)'></div></div>");
        $(".attr-title").html(WST.lang("custompage_component_search"));
        hideComponentAttrContent(1);
        $(".attr-content").append("<div class='attr-content-search "+search_attr_class_name+"' search_num="+search_num+"><div class='attr-content-search-item' ><div class='attr-content-search-img' img_url='/upload/custompagedecoration/base/banner_01.jpg' ><label class='d-label'>"+WST.lang('custompage_image')+"</label><div class='search-picker' id='search-picker-"+search_num+"' onclick='addSearchItemImg(this)'><img src='"+base_url+"/upload/custompagedecoration/base/banner_01.jpg' /></div><div class='image-tips'>"+WST.lang('custompage_search_image_tips')+"</div></div></div><div class='attr-content-search-item'><label class='d-label'>"+WST.lang('custompage_link')+"</label><input type='text' value='' class='attr-content-search-link' /></div><div class='attr-content-search-item'><div class='attr-content-search-item-title'><label class='d-label'>"+WST.lang('custompage_search_goods_prompt_text')+"</label></div><div class='attr-content-search-group'><div class='attr-content-search-item-content'><input type='text' value='"+WST.lang('custompage_search_goods')+"' class='attr-content-search-type' item='1' onkeyup='setSearchTextType(this)' style='width:150px !important;'/><span class='search-type-tip'><span class='search-type-length'item='1'>3</span>/3</span></div><div class='attr-content-search-item-content'><input type='text' value='"+WST.lang('custompage_search_goods_demo')+"' class='attr-content-search-text' item='1' onkeyup='setSearchText(this)' style='width:150px !important;'/><span class='search-text-tip'><span class='search-text-length' item='1'>9</span>/10</span></div></div></div><div class='search-tips'>"+WST.lang('custompage_search_tips')+"</div><div class='attr-content-search-item'><div class='attr-content-search-item-title'><label class='d-label'>"+WST.lang('custompage_search_shop_prompt_text')+"</label></div><div class='attr-content-search-group'><div class='attr-content-search-item-content'><input type='text' value='"+WST.lang('custompage_search_shop')+"' class='attr-content-search-type' item='2' onkeyup='setSearchTextType(this)' style='width:150px !important;'/><span class='search-type-tip'><span class='search-type-length' item='2' >3</span>/3</span></div><div class='attr-content-search-item-content'><input type='text' value='"+WST.lang('custompage_search_shop_demo')+"' class='attr-content-search-text' item='2' onkeyup='setSearchText(this)' style='width:150px !important;'/><span class='search-text-tip'><span class='search-text-length' item='2'>9</span>/10</span></div></div></div><div class='attr-content-search-hot-item' ><div class='wst-flex-row wst-ac'><label class='d-label search-label'>"+WST.lang('custompage_hot_keyword')+"</label><input type='text' value='"+WST.lang('custompage_fruit')+"' class='search-hots-text' hots_item='1' onkeyup='setSearchHotsText(this)' style='width:120px !important;'/><span class='search-hots-text-tip'><span class='search-hots-text-length'>2</span>/5</span></div><div class='attr-btn-del' onclick='delSearchHotItemBtn(this)'>X</div></div><div class='attr-content-search-hot-item' ><div class='wst-flex-row wst-ac'><label class='d-label search-label'>"+WST.lang('custompage_hot_keyword')+"</label><input type='text' value='"+WST.lang('custompage_phone')+"' class='search-hots-text' hots_item='2' onkeyup='setSearchHotsText(this)' style='width:120px !important;'/><span class='search-hots-text-tip'><span class='search-hots-text-length'>2</span>/5</span></div><div class='attr-btn-del' onclick='delSearchHotItemBtn(this)'>X</div></div><div class='attr-content-search-hot-item' ><div class='wst-flex-row wst-ac'><label class='d-label search-label'>"+WST.lang('custompage_hot_keyword')+"</label><input type='text' value='"+WST.lang('custompage_clothes')+"' class='search-hots-text' hots_item='3' onkeyup='setSearchHotsText(this)' style='width:120px !important;'/><span class='search-hots-text-tip'><span class='search-hots-text-length'>2</span>/5</span></div><div class='attr-btn-del' onclick='delSearchHotItemBtn(this)'>X</div></div><div class='attr-content-search-hot-item-add' onclick='addSearchHotItem(this)'>"+WST.lang('custompage_add_one')+"</div></div>");
        WST.upload({
            pick:'#search-picker-'+search_num,
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $("#search-picker-"+search_num).parent().attr('img_url',img_url);
                    $("#search-picker-"+search_num).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                    $(".effect-search").each(function(idx,item){
                        if($(item).hasClass(search_class_name)){
                            $(item).find('.search-left img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
        $(".attr-content-search").each(function(idx, item) {
            if($(item).hasClass("search_attr"+search_num)){
                $(item).show();
            }else{
                $(item).hide();
            }
        });
    });

    // 优惠券组件
    $(".component-coupon").click(function(event){
        componentRemoveClasses(1);
        var coupon_num = 0;
        var coupon_num_arr = [];
        $(".effect-coupon").each(function(idx, item) {
            coupon_num = parseInt($(item).attr('coupon_num'));
            coupon_num_arr.push(coupon_num);
        });
        if(coupon_num_arr.length>0){
            coupon_num = Math.max.apply(null,coupon_num_arr)+1;
        }else{
            coupon_num = 1;
        }
        var coupon_class_name = "coupon"+coupon_num;
        var coupon_attr_class_name = "coupon_attr"+coupon_num;
        $(".effect-content").append("<div class='el-relative'><div class='interval'></div><div class='effect-coupon "+coupon_class_name+"' coupon_num="+coupon_num+" ><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort-name' value='coupon'/><div class='coupons'><div class='coupon-left wst-flex-column wst-jc wst-afs'><div class='coupon-title'>"+WST.lang('custompage_coupon_center')+"</div><div class='coupon-desc'>"+WST.lang('custompage_coupon_sub_title')+"</div></div><div class='coupon-right wst-flex-row'><div class='coupon-item wst-flex-column wst-center'><div class='wst-flex-row wst-afe'><div class='coupon-value-content'>"+WST.lang('currency_symbol')+"<span class='coupon-value'>5</span></div><div><div class='coupon-text'>"+WST.lang('custompage_coupon')+"</div><div class='coupon-condition'>"+WST.lang('custompage_coupon_condition_demo2')+"</div></div></div><div class='coupon-get'>"+WST.lang('custompage_get_now')+"</div></div><div class='coupon-item wst-flex-column wst-center'><div class='wst-flex-row wst-afe'><div class='coupon-value-content'>"+WST.lang('currency_symbol')+"<span class='coupon-value'>5</span></div><div><div class='coupon-text'>"+WST.lang('custompage_coupon')+"</div><div class='coupon-condition'>"+WST.lang('custompage_coupon_condition_demo2')+"</div></div></div><div class='coupon-get'>"+WST.lang('custompage_get_now')+"</div></div><div class='coupon-item wst-flex-column wst-center'><div class='wst-flex-row wst-afe'><div class='coupon-value-content'>"+WST.lang('currency_symbol')+"<span class='coupon-value'>5</span></div><div><div class='coupon-text'>"+WST.lang('custompage_coupon')+"</div><div class='coupon-condition'>"+WST.lang('custompage_coupon_condition_demo2')+"</div></div></div><div class='coupon-get'>"+WST.lang('custompage_get_now')+"</div></div><div class='coupon-item wst-flex-column wst-center'><div class='wst-flex-row wst-afe'><div class='coupon-value-content'>"+WST.lang('currency_symbol')+"<span class='coupon-value'>5</span></div><div><div class='coupon-text'>"+WST.lang('custompage_coupon')+"</div><div class='coupon-condition'>"+WST.lang('custompage_coupon_condition_demo2')+"</div></div></div><div class='coupon-get'>"+WST.lang('custompage_get_now')+"</div></div></div></div><div class='btn-del' onclick='delCouponBtn(this)'>"+WST.lang('custompage_del')+"</div></div><div class='border-absolute' onclick='addCouponClassActive(this)' onmouseover='addClassActive(this)' onmouseout='removeClassActive(this)'></div></div>");
        $(".attr-title").html(WST.lang("custompage_component_coupon"));
        hideComponentAttrContent(1);
        $(".attr-content").append("<div class='attr-content-coupon "+coupon_attr_class_name+"' coupon_num="+coupon_num+"><div class='attr-content-coupon-item' ><div class='attr-content-coupon-img' img_url='/upload/custompagedecoration/base/coupon_bg.png' ><label class='d-label'>"+WST.lang('custompage_background_image')+"</label><div class='coupon-picker' id='coupon-picker-"+coupon_num+"' onclick='addCouponBgImg(this)'><img src='"+base_url+"/upload/custompagedecoration/base/coupon_bg.png' /></div><div class='image-tips coupon-image-tips'>"+WST.lang('custompage_coupon_image_tips')+"</div></div></div><div class='attr-content-coupon-item' ><label class='d-label'>"+WST.lang('custompage_coupon_title')+"</label><input type='text' value='"+WST.lang('custompage_coupon_center')+"' class='attr-content-coupon-title' onkeyup='setCouponTitle(this)'/><span class='coupon-title-tip'><span class='coupon-title-length'>4</span>/4</span></div><div class='attr-content-coupon-item' ><label class='d-label'>"+WST.lang('custompage_coupon_desc')+"</label><input type='text' value='"+WST.lang('custompage_coupon_sub_title')+"' class='attr-content-coupon-desc' onkeyup='setCouponDesc(this)'/><span class='coupon-desc-tip'><span class='coupon-desc-length'>9</span>/9</span></div><div class='attr-content-coupon-item' ><label class='d-label'>"+WST.lang('custompage_coupon_title_select_color')+"</label><div class='select-component-item-color coupon-title-select-color' style='background:#ffffff;' ></div></div><div class='attr-content-coupon-item' ><label class='d-label'>"+WST.lang('custompage_coupon_desc_select_color')+"</label><div class='select-component-item-color coupon-desc-select-color'  style='background:#ffffff;'></div></div><div class='attr-content-coupon-item' ><label class='d-label'>"+WST.lang('custompage_coupon_text_select_color')+"</label><div class='select-component-item-color coupon-text-select-color' style='background:#F9EE9E;' ></div></div><div class='attr-content-coupon-item' ><label class='d-label'>"+WST.lang('custompage_coupon_btn_select_color')+"</label><div class='select-component-item-color coupon-btn-select-color'  style='background:#F9EE9E;'></div></div><div class='attr-content-coupon-item' ><label class='d-label'>"+WST.lang('custompage_coupon_btn_text_select_color')+"</label><div class='select-component-item-color coupon-btn-text-select-color' style='background:#000000;' ></div></div><div class='attr-content-coupon-item'><label class='d-label'>"+WST.lang('custompage_add_coupon')+"</label><div class='add-coupon' onclick='addCoupons(this)'>+ "+WST.lang('custompage_add_coupon')+"</div></div><div class='coupon-tips'>"+WST.lang('custompage_coupon_tips5')+"</div><div class='coupon-tips'>"+WST.lang('custompage_coupon_tips6')+"</div><div class='coupon-tips red'>"+WST.lang('custompage_coupon_tips3')+"</div><div class='coupon-tips red'>"+WST.lang('custompage_coupon_tips4')+"</div><input type='hidden' class='coupon-select-ids-value' value='' ><input type='hidden' class='coupon-title-color' value='#ffffff' /><input type='hidden' class='coupon-desc-color' value='#ffffff' /><input type='hidden' class='coupon-text-color' value='#F9EE9E' /><input type='hidden' class='coupon-btn-color' value='#F9EE9E' /><input type='hidden' class='coupon-btn-text-color' value='#000000' /></div>");
        WST.upload({
            pick:'#coupon-picker-'+coupon_num,
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $("#coupon-picker-"+coupon_num).parent().attr('img_url',img_url);
                    $("#coupon-picker-"+coupon_num).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                    $(".effect-coupon").each(function(idx,item){
                        if($(item).hasClass(coupon_class_name)){
                            $(item).css('background',"url("+WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb+") no-repeat");
                            $(item).css('background-size',"100%");
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
        $('.coupon-title-select-color').colpick({
            layout:'hex',
            submit:1,
            colorScheme:'dark',
            onSubmit:function(hsb,hex,rgb,el,bySetColor){
                if(!bySetColor){
                    $(el).css('background','#'+hex);
                    var coupon_num = $(el).parent().parent().attr("coupon_num");
                    $(".effect-coupon").each(function(idx, item) {
                        if($(item).hasClass("coupon"+coupon_num)){
                            $(item).find('.coupon-title').css('color','#'+hex);
                            $(el).parent().parent().find(".coupon-title-color").val('#'+hex);
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
        $('.coupon-desc-select-color').colpick({
            layout:'hex',
            submit:1,
            colorScheme:'dark',
            onSubmit:function(hsb,hex,rgb,el,bySetColor){
                if(!bySetColor){
                    $(el).css('background','#'+hex);
                    var coupon_num = $(el).parent().parent().attr("coupon_num");
                    $(".effect-coupon").each(function(idx, item) {
                        if($(item).hasClass("coupon"+coupon_num)){
                            $(item).find('.coupon-desc').css('color','#'+hex);
                            $(el).parent().parent().find(".coupon-desc-color").val('#'+hex);
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
        $('.coupon-text-select-color').colpick({
            layout:'hex',
            submit:1,
            colorScheme:'dark',
            onSubmit:function(hsb,hex,rgb,el,bySetColor){
                if(!bySetColor){
                    $(el).css('background','#'+hex);
                    var coupon_num = $(el).parent().parent().attr("coupon_num");
                    $(".effect-coupon").each(function(idx, item) {
                        if($(item).hasClass("coupon"+coupon_num)){
                            $(item).find('.coupon-value-content').css('color','#'+hex);
                            $(item).find('.coupon-text').css('color','#'+hex);
                            $(item).find('.coupon-condition').css('color','#'+hex);
                            $(el).parent().parent().find(".coupon-text-color").val('#'+hex);
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
        $('.coupon-btn-select-color').colpick({
            layout:'hex',
            submit:1,
            colorScheme:'dark',
            onSubmit:function(hsb,hex,rgb,el,bySetColor){
                if(!bySetColor){
                    $(el).css('background','#'+hex);
                    var coupon_num = $(el).parent().parent().attr("coupon_num");
                    $(".effect-coupon").each(function(idx, item) {
                        if($(item).hasClass("coupon"+coupon_num)){
                            $(item).find('.coupon-get').css('background','#'+hex);
                            $(el).parent().parent().find(".coupon-btn-color").val('#'+hex);
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
        $('.coupon-btn-text-select-color').colpick({
            layout:'hex',
            submit:1,
            colorScheme:'dark',
            onSubmit:function(hsb,hex,rgb,el,bySetColor){
                if(!bySetColor){
                    $(el).css('background','#'+hex);
                    var coupon_num = $(el).parent().parent().attr("coupon_num");
                    $(".effect-coupon").each(function(idx, item) {
                        if($(item).hasClass("coupon"+coupon_num)){
                            $(item).find('.coupon-get').css('color','#'+hex);
                            $(el).parent().parent().find(".coupon-btn-text-color").val('#'+hex);
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
        $(".attr-content-coupon").each(function(idx, item) {
            if($(item).hasClass("coupon_attr"+coupon_num)){
                $(item).show();
            }else{
                $(item).hide();
            }
        });
    });

    // 单图组组件
    $(".component-image").click(function(event){
        componentRemoveClasses(1);
        var image_num = 0;
        var image_num_arr = [];
        $(".effect-image").each(function(idx, item) {
            image_num = parseInt($(item).attr('image_num'));
            image_num_arr.push(image_num);
        });
        if(image_num_arr.length>0){
            image_num = Math.max.apply(null,image_num_arr)+1;
        }else{
            image_num = 1;
        }
        var image_class_name = "image"+image_num;
        var image_attr_class_name = "image_attr"+image_num;
        $(".effect-content").append("<div class='el-relative'><div class='effect-image "+image_class_name+"' image_num="+image_num+" ><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort-name' value='image'/><div class='images'><div class='images-item'><img src='"+base_url+"/upload/custompagedecoration/base/pc_image_01.jpg' alt='' image_item='1'/></div></div><div class='btn-del' onclick='delImageBtn(this)'>"+WST.lang('custompage_del')+"</div></div><div class='border-absolute effect-active select' onclick='addImageClassActive(this)' onmouseover='addClassActive(this)' onmouseout='removeClassActive(this)'></div></div>");
        $(".attr-title").html(WST.lang("custompage_image"));
        hideComponentAttrContent(1);
        $(".attr-content").append("<div class='attr-content-image "+image_attr_class_name+"' image_num="+image_num+"><div class='attr-content-image-item'><label class='d-label'>"+WST.lang('custompage_padding_top')+"</label><div class='attr-content-image-item-content'><input type='hidden' class='image-range-slider'  value='0' onchange='changeImagePadding(this,1)'/><span class='image-padding-top-value'>0</span><span class='pixel'>px ("+WST.lang('custompage_px')+")</span></div></div><div class='attr-content-image-item'><label class='d-label'>"+WST.lang('custompage_padding_bottom')+"</label><div class='attr-content-image-item-content'><input type='hidden' class='image-range-slider'  value='0' onchange='changeImagePadding(this,2)'/><span class='image-padding-bottom-value'>0</span><span class='pixel'>px ("+WST.lang('custompage_px')+")</span></div></div><div class='attr-content-image-item'><label class='d-label'>"+WST.lang('custompage_padding_left')+"</label><div class='attr-content-image-item-content'><input type='hidden' class='image-range-slider'  value='0' onchange='changeImagePadding(this,3)'/><span class='image-padding-left-value'>0</span><span class='pixel'>px ("+WST.lang('custompage_px')+")</span></div></div><div class='attr-content-image-item'><label class='d-label'>"+WST.lang('custompage_padding_right')+"</label><div class='attr-content-image-item-content'><input type='hidden' class='image-range-slider'  value='0' onchange='changeImagePadding(this,4)'/><span class='image-padding-right-value'>0</span><span class='pixel'>px ("+WST.lang('custompage_px')+")</span></div></div><div class='attr-content-image-item' ><label class='d-label'>"+WST.lang('custompage_background_color')+"</label><div class='select-component-item-color image-select-bg-color'></div></div><div class='attr-content-image-item-img' ><div class='attr-content-image-img' img_url='/upload/custompagedecoration/base/pc_image_01.jpg' image_item='1'><label class='d-label'>"+WST.lang('custompage_image')+"</label><div class='image-picker' id='image-picker-"+image_num+"-1' onclick='addImageItemImg(this)'><img src='"+base_url+"/upload/custompagedecoration/base/pc_image_01.jpg'/></div><div class='image-tips'>"+WST.lang('custompage_image_img_tips2')+"</div></div><label class='d-label'>"+WST.lang('custompage_link')+"</label><input type='text' value='' class='attr-content-image-link' /><div class='attr-btn-del' onclick='delImageItemBtn(this)'>X</div></div><div class='attr-content-image-item-add' onclick='addImageItem(this)'>"+WST.lang('custompage_add_one')+"</div><input type='hidden' class='image-padding-top' value='0'><input type='hidden' class='image-padding-bottom' value='0'><input type='hidden' class='image-padding-left' value='0'><input type='hidden' class='image-padding-right' value='0'><input type='hidden' class='image-bg-color' value='#ffffff' /></div>");
        WST.upload({
            pick:'#image-picker-'+image_num+'-1',
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $("#image-picker-"+image_num+"-1").parent().attr('img_url',img_url);
                    $("#image-picker-"+image_num+"-1").find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                    $(".effect-image").each(function(idx,item){
                        if($(item).hasClass(image_class_name)){
                            $(item).find('img').each(function(idx,obj){
                                if(parseInt($(obj).attr('image_item')) == 1){
                                    $(obj).attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                                }
                            });
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
        $('.image-select-bg-color').colpick({
            layout:'hex',
            submit:1,
            colorScheme:'dark',
            onSubmit:function(hsb,hex,rgb,el,bySetColor){
                if(!bySetColor){
                    $(el).css('background','#'+hex);
                    var image_num = $(el).parent().parent().attr("image_num");
                    $(".effect-image").each(function(idx, item) {
                        if($(item).hasClass("image"+image_num)){
                            $(item).css('background','#'+hex);
                            $(el).parent().parent().find(".image-bg-color").val('#'+hex);
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
        $('.image-range-slider').jRange({
            from: 0,
            to: 50,
            step: 1,
            format: '%s',
            width: 100,
            showLabels: false,
            isRange : true,
            showScale: false
        });
        $(".attr-content-image").each(function(idx, item) {
            if($(item).hasClass("image_attr"+image_num)){
                $(item).show();
            }else{
                $(item).hide();
            }
        });
    });

    // 图片橱窗组件
    $(".component-shopwindow").click(function(event){
        componentRemoveClasses(1);
        var shopwindow_num = 0;
        var shopwindow_num_arr = [];
        $(".effect-shopwindow").each(function(idx, item) {
            shopwindow_num = parseInt($(item).attr('shopwindow_num'));
            shopwindow_num_arr.push(shopwindow_num);
        });
        if(shopwindow_num_arr.length>0){
            shopwindow_num = Math.max.apply(null,shopwindow_num_arr)+1;
        }else{
            shopwindow_num = 1;
        }
        var shopwindow_class_name = "shopwindow"+shopwindow_num;
        var shopwindow_attr_class_name = "shopwindow_attr"+shopwindow_num;
        $(".effect-content").append("<div class='el-relative'><div class='effect-shopwindow "+shopwindow_class_name+"' shopwindow_num="+shopwindow_num+" ><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort-name' value='shopwindow'/><div class='shopwindows'><div class='shopwindows-item' ><img src='"+base_url+"/upload/custompagedecoration/base/shopwindow_01.jpg' alt='' shopwindow_item='1'/></div><div class='shopwindows-item' ><img src='"+base_url+"/upload/custompagedecoration/base/shopwindow_02.jpg' alt='' shopwindow_item='2'/></div><div class='shopwindows-item' ><img src='"+base_url+"/upload/custompagedecoration/base/shopwindow_03.jpg' alt='' shopwindow_item='3'/></div><div class='shopwindows-item' ><img src='"+base_url+"/upload/custompagedecoration/base/shopwindow_04.jpg' alt='' shopwindow_item='4'/></div></div><div class='btn-del' onclick='delShopwindowBtn(this)'>"+WST.lang('custompage_del')+"</div><div class='shopwindows-layout none'><div class='s-layout'><div class='s-layout-left'><div class='s-layout-left-item' ></div></div><div class='s-layout-right'><div class='s-layout-top'><div class='s-layout-top-item' ></div></div><div class='s-layout-bottom'><div class='s-layout-bottom-item' ></div><div class='s-layout-bottom-item' ></div></div></div></div></div><div class='shopwindows-s-hide'></div></div><div class='border-absolute effect-active select' onclick='addShopwindowClassActive(this)' onmouseover='addClassActive(this)' onmouseout='removeClassActive(this)'></div></div>");
        $(".attr-title").html(WST.lang("custompage_component_shopwindow"));
        hideComponentAttrContent(1);
        $(".attr-content").append("<div class='attr-content-shopwindow "+shopwindow_attr_class_name+"' shopwindow_num="+shopwindow_num+"><div class='attr-content-shopwindow-item' ><label class='d-label'>"+WST.lang('custompage_background_color')+"</label><div class='select-component-item-color shopwindow-select-bg-color'></div></div><div class='attr-content-shopwindow-item'><label class='d-label'>"+WST.lang('custompage_layout_type')+"</label><div class='shopwindow-layout' style='width:150px;'><input type='radio' value='1' name='shopwindow-layout"+shopwindow_num+"' id='s-two"+shopwindow_num+"' onclick='changeShopwindowLayout(this)' checked /><label for='s-two"+shopwindow_num+"'>"+WST.lang('custompage_layout_type1')+"</label><input type='radio' value='2' name='shopwindow-layout"+shopwindow_num+"' id='s-three"+shopwindow_num+"' onclick='changeShopwindowLayout(this)' /><label for='s-three"+shopwindow_num+"'>"+WST.lang('custompage_layout_type2')+"</label><input type='radio' value='3' name='shopwindow-layout"+shopwindow_num+"' id='s-four"+shopwindow_num+"' onclick='changeShopwindowLayout(this)' /><label for='s-four"+shopwindow_num+"'>"+WST.lang('custompage_layout_type3')+"</label><input type='radio' value='4' name='shopwindow-layout"+shopwindow_num+"' id='s-s"+shopwindow_num+"' onclick='changeShopwindowLayout(this)' /><label for='s-s"+shopwindow_num+"'>"+WST.lang('custompage_layout_type4')+"</label></div></div><div class='attr-content-shopwindow-item'><label class='d-label'>"+WST.lang('custompage_layout_desc')+"</label><div class='shopwindow-layout-info'>"+WST.lang('custompage_shopwindow_image_tips1')+"</div></div><div class='attr-content-shopwindow-item-img' ><div class='attr-content-shopwindow-img' img_url='/upload/custompagedecoration/base/shopwindow_01.jpg' shopwindow_item='1'><label class='d-label'>"+WST.lang('custompage_image')+"</label><div class='shopwindow-picker' id='shopwindow-picker-"+shopwindow_num+"-1' onclick='addShopwindowItemImg(this)'><img src='"+base_url+"/upload/custompagedecoration/base/shopwindow_01.jpg'/></div></div><label class='d-label'>"+WST.lang('custompage_link')+"</label><input type='text' value='' class='attr-content-shopwindow-link' /><div class='attr-btn-del' onclick='delShopwindowItemBtn(this)'>X</div></div><div class='attr-content-shopwindow-item-img' ><div class='attr-content-shopwindow-img' img_url='/upload/custompagedecoration/base/shopwindow_02.jpg' shopwindow_item='2'><label class='d-label'>"+WST.lang('custompage_image')+"</label><div class='shopwindow-picker' id='shopwindow-picker-"+shopwindow_num+"-2' onclick='addShopwindowItemImg(this)'><img src='"+base_url+"/upload/custompagedecoration/base/shopwindow_02.jpg'/></div></div><label class='d-label'>"+WST.lang('custompage_link')+"</label><input type='text' value='' class='attr-content-shopwindow-link' /><div class='attr-btn-del' onclick='delShopwindowItemBtn(this)'>X</div></div><div class='attr-content-shopwindow-item-img' ><div class='attr-content-shopwindow-img' img_url='/upload/custompagedecoration/base/shopwindow_03.jpg' shopwindow_item='3'><label class='d-label'>"+WST.lang('custompage_image')+"</label><div class='shopwindow-picker' id='shopwindow-picker-"+shopwindow_num+"-3' onclick='addShopwindowItemImg(this)'><img src='"+base_url+"/upload/custompagedecoration/base/shopwindow_03.jpg'/></div></div><label class='d-label'>"+WST.lang('custompage_link')+"</label><input type='text' value='' class='attr-content-shopwindow-link' /><div class='attr-btn-del' onclick='delShopwindowItemBtn(this)'>X</div></div><div class='attr-content-shopwindow-item-img' ><div class='attr-content-shopwindow-img' img_url='/upload/custompagedecoration/base/shopwindow_04.jpg' shopwindow_item='4'><label class='d-label'>"+WST.lang('custompage_image')+"</label><div class='shopwindow-picker' id='shopwindow-picker-"+shopwindow_num+"-4' onclick='addShopwindowItemImg(this)'><img src='"+base_url+"/upload/custompagedecoration/base/shopwindow_04.jpg'/></div></div><label class='d-label'>"+WST.lang('custompage_link')+"</label><input type='text' value='' class='attr-content-shopwindow-link' /><div class='attr-btn-del' onclick='delShopwindowItemBtn(this)'>X</div></div><div class='attr-content-shopwindow-item-add' onclick='addShopwindowItem(this)'>"+WST.lang('custompage_add_one')+"</div><input type='hidden' class='shopwindow-bg-color' value='#ffffff' /><input type='hidden' class='shopwindow-layout-value' value='1' /></div>");
        WST.upload({
            pick:'#shopwindow-picker-'+shopwindow_num+'-1',
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $("#shopwindow-picker-"+shopwindow_num+"-1").parent().attr('img_url',img_url);
                    $("#shopwindow-picker-"+shopwindow_num+"-1").find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                    $(".effect-shopwindow").each(function(idx,item){
                        if($(item).hasClass(shopwindow_class_name)){
                            $(item).find('img').each(function(idx,obj){
                                if(parseInt($(obj).attr('shopwindow_item')) == 1){
                                    $(obj).attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                                }
                            });
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
        WST.upload({
            pick:'#shopwindow-picker-'+shopwindow_num+'-2',
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $("#shopwindow-picker-"+shopwindow_num+"-2").parent().attr('img_url',img_url);
                    $("#shopwindow-picker-"+shopwindow_num+"-2").find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                    $(".effect-shopwindow").each(function(idx,item){
                        if($(item).hasClass(shopwindow_class_name)){
                            $(item).find('img').each(function(idx,obj){
                                if(parseInt($(obj).attr('shopwindow_item')) == 2){
                                    $(obj).attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                                }
                            });
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
        WST.upload({
            pick:'#shopwindow-picker-'+shopwindow_num+'-3',
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $("#shopwindow-picker-"+shopwindow_num+"-3").parent().attr('img_url',img_url);
                    $("#shopwindow-picker-"+shopwindow_num+"-3").find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                    $(".effect-shopwindow").each(function(idx,item){
                        if($(item).hasClass(shopwindow_class_name)){
                            $(item).find('img').each(function(idx,obj){
                                if(parseInt($(obj).attr('shopwindow_item')) == 3){
                                    $(obj).attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                                }
                            });
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
        WST.upload({
            pick:'#shopwindow-picker-'+shopwindow_num+'-4',
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $("#shopwindow-picker-"+shopwindow_num+"-4").parent().attr('img_url',img_url);
                    $("#shopwindow-picker-"+shopwindow_num+"-4").find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                    $(".effect-shopwindow").each(function(idx,item){
                        if($(item).hasClass(shopwindow_class_name)){
                            $(item).find('img').each(function(idx,obj){
                                if(parseInt($(obj).attr('shopwindow_item')) == 4){
                                    $(obj).attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                                }
                            });
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });

        $('.shopwindow-select-bg-color').colpick({
            layout:'hex',
            submit:1,
            colorScheme:'dark',
            onSubmit:function(hsb,hex,rgb,el,bySetColor){
                if(!bySetColor){
                    $(el).css('background','#'+hex);
                    var shopwindow_num = $(el).parent().parent().attr("shopwindow_num");
                    $(".effect-shopwindow").each(function(idx, item) {
                        if($(item).hasClass("shopwindow"+shopwindow_num)){
                            $(item).css('background','#'+hex);
                            $(el).parent().parent().find(".shopwindow-bg-color").val('#'+hex);
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
        $(".attr-content-shopwindow").each(function(idx, item) {
            if($(item).hasClass("shopwindow_attr"+shopwindow_num)){
                $(item).show();
            }else{
                $(item).hide();
            }
        });
    });

    // 视频组件
    $(".component-video").click(function(event){
        componentRemoveClasses(1);
        var video_num = 0;
        var video_num_arr = [];
        $(".effect-video").each(function(idx, item) {
            video_num = parseInt($(item).attr('video_num'));
            video_num_arr.push(video_num);
        });
        if(video_num_arr.length>0){
            video_num = Math.max.apply(null,video_num_arr)+1;
        }else{
            video_num = 1;
        }
        var video_class_name = "video"+video_num;
        var video_attr_class_name = "video_attr"+video_num;
        $(".effect-content").append("<div class='el-relative'><div class='effect-video "+video_class_name+"' video_num="+video_num+" ><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort-name' value='video'/><img src='"+base_url+"/upload/custompagedecoration/base/pc_video.png' alt=''/><div class='btn-del' onclick='delVideoBtn(this)'>"+WST.lang('custompage_del')+"</div></div><div class='border-absolute effect-active select' onclick='addVideoClassActive(this)' onmouseover='addClassActive(this)' onmouseout='removeClassActive(this)'></div></div>");
        $(".attr-title").html(WST.lang("custompage_component_video"));
        hideComponentAttrContent(1);
        $(".attr-content").append("<div class='attr-content-video "+video_attr_class_name+"' video_num="+video_num+" ><div class='attr-content-video-item'><label class='d-label'>"+WST.lang('custompage_vertical_padding')+"</label><div class='attr-content-video-item-content'><input type='hidden' class='video-range-slider'  value='0' onchange='changeVideoMargin(this)'/><span class='video-vertical-padding-value'>0</span><span class='pixel'>px ("+WST.lang('custompage_px')+")</span></div></div><div class='attr-content-video-item'><div class='attr-content-video-img' img_url='/upload/custompagedecoration/base/pc_video.png' ></div></div><div class='attr-content-video-item'><label class='d-label'>"+WST.lang('custompage_video_link')+"</label><input type='text' value='' class='attr-content-video-link' /></div><input type='hidden' class='video-vertical-padding' value='0' /></div>");
        WST.upload({
            pick:'#video-picker-'+video_num,
            formData: {dir:'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback:function(f){
                var json = WST.toAdminJson(f);
                if(json.status==1){
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath+json.thumb; //保存到数据库的路径
                    $("#video-picker-"+video_num).parent().attr('img_url',img_url);
                    $("#video-picker-"+video_num).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                    $(".effect-video").each(function(idx,item){
                        if($(item).hasClass(video_class_name)){
                            $(item).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                        }
                    });
                }else{
                    WST.msg(json.msg,{icon:2});
                }
            },
            progress:function(rate){
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
            }
        });
        $('.video-range-slider').jRange({
            from: 0,
            to: 50,
            step: 1,
            format: '%s',
            width: 120,
            showLabels: false,
            isRange : true,
            showScale: false
        });
        $(".attr-content-video").each(function(idx, item) {
            if($(item).hasClass("video_attr"+video_num)){
                $(item).show();
            }else{
                $(item).hide();
            }
        });
    });

    // 辅助空白组件
    $(".component-blank").click(function(event){
        componentRemoveClasses(1);
        var blank_num = 0;
        var blank_num_arr = [];
        $(".effect-blank").each(function(idx, item) {
            blank_num = parseInt($(item).attr('blank_num'));
            blank_num_arr.push(blank_num);
        });
        if(blank_num_arr.length>0){
            blank_num = Math.max.apply(null,blank_num_arr)+1;
        }else{
            blank_num = 1;
        }
        var blank_class_name = "blank"+blank_num;
        var blank_attr_class_name = "blank_attr"+blank_num;
        $(".effect-content").append("<div class='el-relative'><div class='effect-blank "+blank_class_name+"' blank_num="+blank_num+" ><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort-name' value='blank'/><div class='btn-del' onclick='delBlankBtn(this)'>"+WST.lang('custompage_del')+"</div></div><div class='border-absolute effect-active select' onclick='addBlankClassActive(this)' onmouseover='addClassActive(this)' onmouseout='removeClassActive(this)'></div></div>");
        $(".attr-title").html(WST.lang("custompage_component_blank"));
        hideComponentAttrContent(1);
        $(".attr-content").append("<div class='attr-content-blank "+blank_attr_class_name+"' blank_num="+blank_num+" ><div class='attr-content-blank-item'><label class='d-label'>"+WST.lang('custompage_component_height')+"</label><div class='attr-content-blank-item-content'><input type='hidden' class='blank-range-slider'  value='20' onchange='changeBlankHeight(this)'/><span class='blank-height-value'>20</span><span>px </span></div></div><div class='attr-content-blank-item' ><label class='d-label'>"+WST.lang('custompage_background_color')+"</label><div class='attr-content-blank-item-content'><div class='select-component-item-color blank-select-bg-color'></div></div></div><input type='hidden' class='blank-bg-color' value='#ffffff' /><input type='hidden' class='blank-height' value='20'></div>");
        $('.blank-select-bg-color').colpick({
            layout:'hex',
            submit:1,
            colorScheme:'dark',
            onSubmit:function(hsb,hex,rgb,el,bySetColor){
                if(!bySetColor){
                    $(el).css('background','#'+hex);
                    var blank_num = $(el).parent().parent().parent().attr("blank_num");
                    $(".effect-blank").each(function(idx, item) {
                        if($(item).hasClass("blank"+blank_num)){
                            $(item).css('background','#'+hex);
                            $(el).parent().parent().parent().find(".blank-bg-color").val('#'+hex);
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
        $('.blank-range-slider').jRange({
            from: 0,
            to: 200,
            step: 1,
            format: '%s',
            width: 120,
            showLabels: false,
            isRange : true,
            showScale: false
        });
        $(".attr-content-blank").each(function(idx, item) {
            if($(item).hasClass("blank_attr"+blank_num)){
                $(item).show();
            }else{
                $(item).hide();
            }
        });
    });

    // 辅助线组件
    $(".component-line").click(function(event){
        componentRemoveClasses(1);
        var line_num = 0;
        var line_num_arr = [];
        $(".effect-line").each(function(idx, item) {
            line_num = parseInt($(item).attr('line_num'));
            line_num_arr.push(line_num);
        });
        if(line_num_arr.length>0){
            line_num = Math.max.apply(null,line_num_arr)+1;
        }else{
            line_num = 1;
        }
        var line_class_name = "line"+line_num;
        var line_attr_class_name = "line_attr"+line_num;
        $(".effect-content").append("<div class='el-relative'><div class='effect-line "+line_class_name+"' line_num="+line_num+" ><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort-name' value='line'/><div class='line'></div><div class='btn-del' onclick='delLineBtn(this)'>"+WST.lang('custompage_del')+"</div></div><div class='border-absolute effect-active select' onclick='addLineClassActive(this)' onmouseover='addClassActive(this)' onmouseout='removeClassActive(this)'></div></div>");
        $(".attr-title").html(WST.lang('custompage_component_line'));
        hideComponentAttrContent(1);
        $(".attr-content").append("<div class='attr-content-line "+line_attr_class_name+"' line_num="+line_num+" ><div class='attr-content-line-item' ><label class='d-label'>"+WST.lang('custompage_background_color')+"</label><div class='attr-content-line-item-content'><div class='select-component-item-color line-select-bg-color'></div></div></div><div class='attr-content-line-item'><label class='d-label'>"+WST.lang('custompage_line_style')+"</label><div class='attr-content-line-item-content'><input type='radio' value='1' name='line-class"+line_num+"' id='l-solid' onclick='changeLineClass(this)' checked /><label for='l-solid'>"+WST.lang('custompage_line_class1')+"</label><input type='radio' value='2' name='line-class"+line_num+"' id='l-dashed' onclick='changeLineClass(this)' /><label for='l-dashed'>"+WST.lang('custompage_line_class2')+"</label><input type='radio' value='3' name='line-class"+line_num+"' id='l-dotted' onclick='changeLineClass(this)' /><label for='l-dotted'>"+WST.lang('custompage_line_class3')+"</label></div></div><div class='attr-content-line-item' ><label class='d-label'>"+WST.lang('custompage_line_color')+"</label><div class='attr-content-line-item-content'><div class='select-component-item-color line-select-color'></div></div></div><div class='attr-content-line-item'><label class='d-label'>"+WST.lang('custompage_line_height')+"</label><div class='attr-content-line-item-content'><input type='hidden' class='line-height-range-slider'  value='1' onchange='changeLineHeight(this)'/><span class='line-height-value'>1</span><span class='pixel'>px ("+WST.lang('custompage_px')+")</span></div></div><div class='attr-content-line-item'><label class='d-label'>"+WST.lang('custompage_vertical_padding')+"</label><div class='attr-content-line-item-content'><input type='hidden' class='line-margin-range-slider'  value='10' onchange='changeLineMargin(this)'/><span class='line-vertical-margin-value'>10</span><span>px ("+WST.lang('custompage_px')+")</span></div></div><input type='hidden' class='line-bg-color' value='#ffffff' /><input type='hidden' class='line-class' value='1' /><input type='hidden' class='line-border-color' value='#000000' /><input type='hidden' class='line-height' value='1' /><input type='hidden' class='line-vertical-margin' value='10' /></div>");
        $('.line-select-bg-color').colpick({
            layout:'hex',
            submit:1,
            colorScheme:'dark',
            onSubmit:function(hsb,hex,rgb,el,bySetColor){
                if(!bySetColor){
                    $(el).css('background','#'+hex);
                    var line_num = $(el).parent().parent().parent().attr("line_num");
                    $(".effect-line").each(function(idx, item) {
                        if($(item).hasClass("line"+line_num)){
                            $(item).css('background','#'+hex);
                            $(el).parent().parent().parent().find(".line-bg-color").val('#'+hex);
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
        $('.line-select-color').colpick({
            layout:'hex',
            submit:1,
            colorScheme:'dark',
            onSubmit:function(hsb,hex,rgb,el,bySetColor){
                if(!bySetColor){
                    $(el).css('background','#'+hex);
                    var line_num = $(el).parent().parent().parent().attr("line_num");
                    $(".effect-line").each(function(idx, item) {
                        if($(item).hasClass("line"+line_num)){
                            $(item).find(".line").css('border-color','#'+hex);
                            $(el).parent().parent().parent().find(".line-border-color").val('#'+hex);
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
        $('.line-height-range-slider').jRange({
            from: 1,
            to: 20,
            step: 1,
            format: '%s',
            width: 120,
            showLabels: false,
            isRange : true,
            showScale: false
        });
        $('.line-margin-range-slider').jRange({
            from: 0,
            to: 50,
            step: 1,
            format: '%s',
            width: 120,
            showLabels: false,
            isRange : true,
            showScale: false
        });
        $(".attr-content-line").each(function(idx, item) {
            if($(item).hasClass("line_attr"+line_num)){
                $(item).show();
            }else{
                $(item).hide();
            }
        });
    });

    // 富文本组件
    $(".component-text").click(function(event){
        componentRemoveClasses(1);
        var text_num = 0;
        var text_num_arr = [];
        $(".effect-text").each(function(idx, item) {
            text_num = parseInt($(item).attr('text_num'));
            text_num_arr.push(text_num);
        });
        if(text_num_arr.length>0){
            text_num = Math.max.apply(null,text_num_arr)+1;
        }else{
            text_num = 1;
        }
        var text_class_name = "text"+text_num;
        var text_attr_class_name = "text_attr"+text_num;
        $(".effect-content").append("<div class='el-relative'><div class='effect-text "+text_class_name+"' text_num="+text_num+" ><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort-name' value='text'/><div class='text'>"+WST.lang('custompage_here_is_text_content')+"</div><div class='btn-del' onclick='delTextBtn(this)'>"+WST.lang('custompage_del')+"</div></div><div class='border-absolute effect-active select' onclick='addTextClassActive(this)' onmouseover='addClassActive(this)' onmouseout='removeClassActive(this)'></div></div>");
        $(".attr-title").html(WST.lang("custompage_component_text"));
        hideComponentAttrContent(1);
        $(".attr-content").append("<div class='attr-content-text "+text_attr_class_name+"' text_num="+text_num+" ><div class='attr-content-text-item'><label class='d-label'>"+WST.lang('custompage_vertical_padding')+"</label><div class='attr-content-text-item-content'><input type='hidden' class='text-range-slider'  value='0' onchange='changeTextPadding(this,1)'/><span class='text-vertical-padding-value'>0</span><span class='pixel'>px ("+WST.lang('custompage_px')+")</span></div></div><div class='attr-content-text-item'><label class='d-label'>"+WST.lang('custompage_horizontal_padding')+"</label><div class='attr-content-text-item-content'><input type='hidden' class='text-range-slider'  value='0' onchange='changeTextPadding(this,2)'/><span class='text-horizontal-padding-value'>0</span><span class='pixel'>px ("+WST.lang('custompage_px')+")</span></div></div><div class='attr-content-text-item' ><label class='d-label'>"+WST.lang('custompage_background_color')+"</label><div class='select-component-item-color text-select-bg-color'></div></div><div class='attr-content-text-item'><textarea rows='2' cols='60' class='text-desc"+text_num+"'>"+WST.lang('custompage_here_is_text_content')+"</textarea></div><input type='hidden' class='text-vertical-padding' value='0' /><input type='hidden' class='text-horizontal-padding' value='0' /><input type='hidden' class='text-bg-color' value='#ffffff' /></div>");
        $('.text-select-bg-color').colpick({
            layout:'hex',
            submit:1,
            colorScheme:'dark',
            onSubmit:function(hsb,hex,rgb,el,bySetColor){
                if(!bySetColor){
                    $(el).css('background','#'+hex);
                    var text_num = $(el).parent().parent().attr("text_num");
                    $(".effect-text").each(function(idx, item) {
                        if($(item).hasClass("text"+text_num)){
                            $(item).find(".text").css('background','#'+hex);
                            $(el).parent().parent().parent().find(".text-bg-color").val('#'+hex);
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
        KindEditor.ready(function(K) {
            editor1 = K.create('.text-desc'+text_num, {
                height:'400px',
                width:'280px',
                uploadJson : WST.conf.ROOT+'/admin/index/editorUpload',
                allowFileManager : false,
                allowImageUpload : true,
                themeType : "default",
                items:[     'source', 'undo', 'redo',  'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
                            'plainpaste', 'wordpaste', 'justifyleft', 'justifycenter', 'justifyright',
                            'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
                            'superscript', 'clearhtml', 'quickformat', 'selectall',  'fullscreen',
                            'formatblock', 'fontname', 'fontsize',  'forecolor', 'hilitecolor', 'bold',
                            'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', 'image','multiimage','media','table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
                            'anchor', 'link', 'unlink'
                ],
                afterBlur: function(){ this.sync(); },
                afterChange : function() {
                    var content = "";
                    $(".attr-content-text").each(function(idx, item) {
                        if($(item).hasClass("text_attr"+text_num)){
                            content = $(document.getElementsByTagName('iframe')[idx].contentWindow.document.body).html();
                        }
                    });

                    $(".effect-text").each(function(idx, item) {
                        if($(item).hasClass("text"+text_num)){
                            $(item).find(".text").html(content);
                        }
                    });
                }
            });
        });
        $('.text-range-slider').jRange({
            from: 0,
            to: 50,
            step: 1,
            format: '%s',
            width: 120,
            showLabels: false,
            isRange : true,
            showScale: false
        });
        $(".attr-content-text").each(function(idx, item) {
            if($(item).hasClass("text_attr"+text_num)){
                $(item).show();
            }else{
                $(item).hide();
            }
        });
    });

    // 单文本组件
    $(".component-txt").click(function(event){
        componentRemoveClasses(1);
        var txt_num = 0;
        var txt_num_arr = [];
        $(".effect-txt").each(function(idx, item) {
            txt_num = parseInt($(item).attr('txt_num'));
            txt_num_arr.push(txt_num);
        });
        if(txt_num_arr.length>0){
            txt_num = Math.max.apply(null,txt_num_arr)+1;
        }else{
            txt_num = 1;
        }
        var txt_class_name = "txt"+txt_num;
        var txt_attr_class_name = "txt_attr"+txt_num;
        $(".effect-content").append("<div class='el-relative'><div class='interval'></div><div class='effect-txt "+txt_class_name+"' txt_num="+txt_num+" ><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort-name' value='txt'/><div class='txt-info'><p>"+WST.lang('custompage_add_txt_content')+"</p></div><div class='btn-del' onclick='delTxtBtn(this)'>"+WST.lang('custompage_del')+"</div></div><div class='border-absolute effect-active select' onclick='addTxtClassActive(this)' onmouseover='addClassActive(this)' onmouseout='removeClassActive(this)'></div></div>");
        $(".attr-title").html(WST.lang("custompage_component_txt"));
        hideComponentAttrContent(1);
        $(".attr-content").append("<div class='attr-content-txt "+txt_attr_class_name+"' txt_num="+txt_num+" ><div class='attr-content-txt-item'><label class='d-label'>"+WST.lang('custompage_prompt_text')+"</label><input type='text' value='"+WST.lang('custompage_add_txt_content')+"' class='attr-content-txt-text' onkeyup='setTxtText(this)' /><span class='txt-text-tip'><span class='txt-text-length'>8</span>/50</span></div><div class='attr-content-txt-item'><label class='d-label'>"+WST.lang('custompage_link_address')+"</label><input type='text' value='' class='attr-content-txt-link' /></div><div class='attr-content-txt-item'><label class='d-label'>"+WST.lang('custompage_font_position')+"</label><input type='radio' value='1' checked name='txt-text-alignment"+txt_num+"'  id='t-left"+txt_num+"' onclick='changeTxtTextAlignment(this)' /><label for='t-left"+txt_num+"'>"+WST.lang('custompage_alignment_left')+"</label><input type='radio' value='2' name='txt-text-alignment"+txt_num+"' id='s-center"+txt_num+"' onclick='changeTxtTextAlignment(this)' /><label for='s-center"+txt_num+"'>"+WST.lang('custompage_alignment_center')+"</label><input type='radio' value='3' name='txt-text-alignment"+txt_num+"' id='s-right"+txt_num+"' onclick='changeTxtTextAlignment(this)' /><label for='s-right"+txt_num+"'>"+WST.lang('custompage_alignment_right')+"</label></div><div class='attr-content-txt-item' ><label class='d-label'>"+WST.lang('custompage_background_color')+"</label><div class='select-component-item-color txt-select-bg-color'></div></div><div class='attr-content-txt-item' ><label class='d-label'>"+WST.lang('custompage_text_color')+"</label><div class='select-component-item-color txt-select-text-color' style='background:#000000;'></div></div><div class='attr-content-txt-item'><label class='d-label'>"+WST.lang('custompage_vertical_padding')+"</label><div class='attr-content-txt-item-content'><input type='hidden' class='txt-range-slider'  value='0' onchange='changeTxtPadding(this,1)'/><span class='txt-vertical-padding-value'>0</span><span class='pixel'>px ("+WST.lang('custompage_px')+")</span></div></div><div class='attr-content-txt-item'><label class='d-label'>"+WST.lang('custompage_horizontal_padding')+"</label><div class='attr-content-txt-item-content'><input type='hidden' class='txt-range-slider'  value='0' onchange='changeTxtPadding(this,2)'/><span class='txt-horizontal-padding-value'>0</span><span class='pixel'>px ("+WST.lang('custompage_px')+")</span></div></div><input type='hidden' class='txt-bg-color' value='#ffffff' /><input type='hidden' class='txt-text-color' value='#000000' /><input type='hidden' class='attr-content-txt-text-alignment' value='1' /><input type='hidden' class='txt-vertical-padding' value='0' /><input type='hidden' class='txt-horizontal-padding' value='0' /></div>");
        $('.txt-select-bg-color').colpick({
            layout:'hex',
            submit:1,
            colorScheme:'dark',
            onSubmit:function(hsb,hex,rgb,el,bySetColor){
                if(!bySetColor){
                    $(el).css('background','#'+hex);
                    var txt_num = $(el).parent().parent().attr("txt_num");
                    $(".effect-txt").each(function(idx, item) {
                        if($(item).hasClass("txt"+txt_num)){
                            $(item).find(".txt-info").css('background','#'+hex);
                            $(el).parent().parent().find(".txt-bg-color").val('#'+hex);
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
        $('.txt-select-text-color').colpick({
            layout:'hex',
            submit:1,
            colorScheme:'dark',
            onSubmit:function(hsb,hex,rgb,el,bySetColor){
                if(!bySetColor){
                    $(el).css('background','#'+hex);
                    var txt_num = $(el).parent().parent().attr("txt_num");
                    $(".effect-txt").each(function(idx, item) {
                        if($(item).hasClass("txt"+txt_num)){
                            $(item).find(".txt-info p").css('color','#'+hex);
                            $(el).parent().parent().find(".txt-text-color").val('#'+hex);
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
        $('.txt-range-slider').jRange({
            from: 0,
            to: 50,
            step: 1,
            format: '%s',
            width: 120,
            showLabels: false,
            isRange : true,
            showScale: false
        });
        $(".attr-content-txt").each(function(idx, item) {
            if($(item).hasClass("txt_attr"+txt_num)){
                $(item).show();
            }else{
                $(item).hide();
            }
        });
    });

    // 图文列表组件
    $(".component-image-text").click(function(event){
        componentRemoveClasses(1);
        var image_text_num = 0;
        var image_text_num_arr = [];
        $(".effect-image-text").each(function(idx, item) {
            image_text_num = parseInt($(item).attr('image_text_num'));
            image_text_num_arr.push(image_text_num);
        });
        if(image_text_num_arr.length>0){
            image_text_num = Math.max.apply(null,image_text_num_arr)+1;
        }else{
            image_text_num = 1;
        }
        var image_text_class_name = "image_text"+image_text_num;
        var image_text_attr_class_name = "image_text_attr"+image_text_num;
        $(".effect-content").append("<div class='el-relative'><div class='effect-image-text image_text"+image_text_num+"' image_text_num="+image_text_num+"><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort-name' value='image_text'/><div class='image-text image-text-style-1' image_text_item='1'><div class='image-text-imgs image-text-style-1-left'><img src='"+base_url+"/upload/custompagedecoration/base/banner_01.jpg' alt=''/></div><div class='image-text-style-1-right'><p class='image-text-style-title'>"+WST.lang('custompage_image_text_title_demo')+"</p><p class='image-text-style-desc'>"+WST.lang('custompage_image_text_desc_demo')+"</p></div></div><div class='image-text image-text-style-2 none2' image_text_item='2'><div class='image-text-style-2-left'><p class='image-text-style-title'>"+WST.lang('custompage_image_text_title_demo')+"</p><p class='image-text-style-desc'>"+WST.lang('custompage_image_text_desc_demo')+"</p></div><div class='image-text-imgs image-text-style-2-right'><img src='"+base_url+"/upload/custompagedecoration/base/banner_01.jpg' alt=''/></div></div><div class='image-text image-text-style-3 none' image_text_item='3'><div class='image-text-style-3-top'><p class='image-text-style-title'>"+WST.lang('custompage_image_text_title_demo')+"</p><p class='image-text-style-desc'>"+WST.lang('custompage_image_text_desc_demo')+"</p></div><div class='image-text-imgs image-text-style-3-bottom'><img src='"+base_url+"/upload/custompagedecoration/base/banner_01.jpg' alt=''/></div></div><div class='image-text image-text-style-4 none' image_text_item='4'><div class='image-text-style-4-top'><p class='image-text-style-title'>"+WST.lang('custompage_image_text_title_demo')+"</p><p class='image-text-style-desc'>"+WST.lang('custompage_image_text_desc_demo')+"</p></div><div class='image-text-imgs wst-flex-row wst-jsa image-text-style-4-bottom'><img src='"+base_url+"/upload/custompagedecoration/base/banner_01.jpg' alt=''/><img src='"+base_url+"/upload/custompagedecoration/base/banner_01.jpg' alt=''/></div></div><div class='image-text image-text-style-5 none' image_text_item='5'><div class='image-text-style-5-top'><p class='image-text-style-title'>"+WST.lang('custompage_image_text_title_demo')+"</p><p class='image-text-style-desc'>"+WST.lang('custompage_image_text_desc_demo')+"</p></div><div class='image-text-imgs wst-flex-row wst-jsa image-text-style-5-bottom'><img src='"+base_url+"/upload/custompagedecoration/base/banner_01.jpg' alt=''/><img src='"+base_url+"/upload/custompagedecoration/base/banner_01.jpg' alt=''/><img src='"+base_url+"/upload/custompagedecoration/base/banner_01.jpg' alt=''/></div></div><div class='btn-del' onclick='delImageTextBtn(this)'>"+WST.lang('custompage_del')+"</div></div><div class='border-absolute effect-active select' onclick='addImageTextClassActive(this)' onmouseover='addClassActive(this)' onmouseout='removeClassActive(this)'></div></div>");
        $(".attr-title").html(WST.lang("custompage_component_image_text"));
        hideComponentAttrContent(1);
        $(".attr-content").append("<div class='attr-content-image-text image_text_attr"+image_text_num+"' image_text_num="+image_text_num+" ><div class='attr-content-image-text-item'><label class='d-label'>"+WST.lang('custompage_image_text_style')+"</label></div><div class='attr-content-image-text-item'><div class='image-text-style-item active' onclick='changeImageTextStyle(this)' image_text_item='1'>"+WST.lang('custompage_image_text_style1')+"</div><div class='image-text-style-item' onclick='changeImageTextStyle(this)' image_text_item='2'>"+WST.lang('custompage_image_text_style2')+"</div><div class='image-text-style-item' onclick='changeImageTextStyle(this)' image_text_item='3'>"+WST.lang('custompage_image_text_style3')+"</div><div class='image-text-style-item' onclick='changeImageTextStyle(this)' image_text_item='4'>"+WST.lang('custompage_image_text_style4')+"</div><div class='image-text-style-item' onclick='changeImageTextStyle(this)' image_text_item='5'>"+WST.lang('custompage_image_text_style5')+"</div></div><div class='attr-content-image-text-item-img' ><div class='image-text-img'><div class='attr-content-image-text-img ' img_url='upload/custompagedecoration/base/banner_01.jpg'><div class='wst-flex-row'><label class='d-label'>"+WST.lang('custompage_image')+"</label><div class='image-text-picker' id='image-text-picker-"+image_text_num+"-1'><img src='"+base_url+"/upload/custompagedecoration/base/banner_01.jpg'/></div></div><div class='image-tips'>"+WST.lang('custompage_image_text_img_tips1')+"</div></div></div><div class='image-text-img none'><div class='attr-content-image-text-img ' img_url='upload/custompagedecoration/base/banner_01.jpg'><div class='wst-flex-row'><label class='d-label'>"+WST.lang('custompage_image')+"</label><div class='image-text-picker' id='image-text-picker-"+image_text_num+"-2'><img src='"+base_url+"/upload/custompagedecoration/base/banner_01.jpg'/></div></div><div class='image-tips'>"+WST.lang('custompage_image_text_img_tips1')+"</div></div></div><div class='image-text-img none'><div class='attr-content-image-text-img ' img_url='upload/custompagedecoration/base/banner_01.jpg'><div class='wst-flex-row'><label class='d-label'>"+WST.lang('custompage_image')+"</label><div class='image-text-picker' id='image-text-picker-"+image_text_num+"-3'><img src='"+base_url+"/upload/custompagedecoration/base/banner_01.jpg'/></div></div><div class='image-tips'>"+WST.lang('custompage_image_text_img_tips1')+"</div></div></div><div><label class='d-label'>"+WST.lang('custompage_title')+"</label><input type='text' value='"+WST.lang('custompage_here_input_title')+"' class='attr-content-image-text-title' onkeyup='setImageTextTitle(this)'style='width:145px !important;' /><span class='image-text-title-tip'><span class='image-text-title-length'>6</span>/20</span></div><div><label class='d-label'>"+WST.lang('custompage_desc')+"</label><input type='text' value='"+WST.lang('custompage_here_input_desc')+"' class='attr-content-image-text-desc' onkeyup='setImageTextDesc(this)' style='width:145px !important;' /><span class='image-text-desc-tip'><span class='image-text-desc-length'>6</span>/200</span></div><div><label class='d-label'>"+WST.lang('custompage_link')+"</label><input type='text' value='' class='attr-content-image-text-link' /></div></div><div class='attr-content-image-text-item'><label class='d-label'>"+WST.lang('custompage_vertical_padding')+"</label><div class='attr-content-image-text-item-content'><input type='hidden' class='image-text-range-slider'  value='0' onchange='changeImageTextPadding(this)'/><span class='image-text-vertical-padding-value'>10</span><span class='pixel'>px ("+WST.lang('custompage_px')+")</span></div></div><input type='hidden' class='image-text-style' value='1' /><input type='hidden' class='image-text-vertical-padding' value='0'></div>");
        WST.upload({
            pick: '#image-text-picker-' + image_text_num + '-1',
            formData: {dir: 'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png', mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback: function (f) {
                var json = WST.toAdminJson(f);
                if (json.status == 1) {
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath + json.thumb; //保存到数据库的路径
                    $('#image-text-picker-' + image_text_num+ '-1').parent().parent().attr('img_url', img_url);
                    $('#image-text-picker-' + image_text_num+ '-1').find('img').attr('src', WST.conf.RESOURCE_PATH + '/' + json.savePath + json.thumb);
                    $('.effect-image-text').each(function (idx, item) {
                        if ($(item).hasClass(image_text_class_name)) {
                            $(item).find('.image-text-imgs').each(function(key,value){
                                $(value).find('img').each(function(key2,value2){
                                    if(key2==0){
                                        $(value2).attr('src', WST.conf.RESOURCE_PATH + '/' + json.savePath + json.thumb);
                                    }
                                });
                            });
                        }
                    });
                } else {
                    WST.msg(json.msg, {icon: 2});
                }
            },
            progress: function (rate) {
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload') + rate + '%');
            }
        });
        WST.upload({
            pick: '#image-text-picker-' + image_text_num + '-2',
            formData: {dir: 'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png', mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback: function (f) {
                var json = WST.toAdminJson(f);
                if (json.status == 1) {
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath + json.thumb; //保存到数据库的路径
                    $('#image-text-picker-' + image_text_num+ '-2').parent().parent().attr('img_url', img_url);
                    $('#image-text-picker-' + image_text_num+ '-2').find('img').attr('src', WST.conf.RESOURCE_PATH + '/' + json.savePath + json.thumb);
                    $('.effect-image-text').each(function (idx, item) {
                        if ($(item).hasClass(image_text_class_name)) {
                            $(item).find('.image-text-imgs').each(function(key,value){
                                $(value).find('img').each(function(key2,value2){
                                    if(key2==1){
                                        $(value2).attr('src', WST.conf.RESOURCE_PATH + '/' + json.savePath + json.thumb);
                                    }
                                });
                            });
                        }
                    });
                } else {
                    WST.msg(json.msg, {icon: 2});
                }
            },
            progress: function (rate) {
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload') + rate + '%');
            }
        });
        WST.upload({
            pick: '#image-text-picker-' + image_text_num + '-3',
            formData: {dir: 'custompagedecoration'},
            accept: {extensions: 'gif,jpg,jpeg,png', mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
            callback: function (f) {
                var json = WST.toAdminJson(f);
                if (json.status == 1) {
                    $('#uploadMsg').empty().hide();
                    var img_url = json.savePath + json.thumb; //保存到数据库的路径
                    $('#image-text-picker-' + image_text_num+ '-3').parent().parent().attr('img_url', img_url);
                    $('#image-text-picker-' + image_text_num+ '-3').find('img').attr('src', WST.conf.RESOURCE_PATH + '/' + json.savePath + json.thumb);
                    $('.effect-image-text').each(function (idx, item) {
                        if ($(item).hasClass(image_text_class_name)) {
                            $(item).find('.image-text-imgs').each(function(key,value){
                                $(value).find('img').each(function(key2,value2){
                                    if(key2==2){
                                        $(value2).attr('src', WST.conf.RESOURCE_PATH + '/' + json.savePath + json.thumb);
                                    }
                                });
                            });
                        }
                    });
                } else {
                    WST.msg(json.msg, {icon: 2});
                }
            },
            progress: function (rate) {
                $('#uploadMsg').show().html(WST.lang('custompage_has_upload') + rate + '%');
            }
        });
        $('.image-text-range-slider').jRange({
            from: 0,
            to: 50,
            step: 1,
            format: '%s',
            width: 120,
            showLabels: false,
            isRange : true,
            showScale: false
        });
        $(".attr-content-image-text").each(function(idx, item) {
            if($(item).hasClass("image_text_attr"+image_text_num)){
                $(item).show();
            }else{
                $(item).hide();
            }
        });
    });

    // 多店铺组件
    $(".component-shop").click(function(event){
        componentRemoveClasses(1);
        var shop_num = 0;
        $(".effect-shop").each(function(idx, item) {
            shop_num = parseInt($(item).attr('shop_num'));
        });
        shop_num += 1;
        if(shop_num > 1){
            WST.msg(WST.lang('custompage_shop_add_tips'),{time:1000,anim: 6});
            return;
        }
        var shop_class_name = "shop"+shop_num;
        var shop_attr_class_name = "shop_attr"+shop_num;
        $(".effect-content").append("<div class='el-relative'><div class='effect-shop "+shop_class_name+"' shop_num="+shop_num+" ><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort-name' value='shop'/><div class='shop-title'>—— <span class='shop-title-text'>"+WST.lang('custompage_nearby_shop')+"</span> ——</div><div class='current-location'><div class='location-icon'></div><div>"+WST.lang('custompage_user_address_demo')+"</div></div><div class='shop-item'><div class='shop-left wst-flex-row wst-jc'><img src='"+base_url+"/upload/custompagedecoration/base/shop.png' alt=''></div><div class='shop-right'><div><p class='shop-name'>"+WST.lang('custompage_shop_name_demo')+"</p><p class='shop-desc'>"+WST.lang('custompage_shop_desc_demo')+"</p><div class='shop-score'><p>"+WST.lang('custompage_shop_score')+"：</p><div class='star-icon'></div><div class='star-icon'></div><div class='star-icon'></div><div class='star-icon'></div><div class='star-icon'></div></div></div><div class='wst-flex-row wst-jsb'><div class='shop-location'><div class='location-icon'></div><p>"+WST.lang('custompage_shop_address_demo')+"</p></div><p>2km</p></div></div></div><div class='shop-item'><div class='shop-left wst-flex-row wst-jc'><img src='"+base_url+"/upload/custompagedecoration/base/shop.png' alt=''></div><div class='shop-right'><div><p class='shop-name'>"+WST.lang('custompage_shop_name_demo')+"</p><p class='shop-desc'>"+WST.lang('custompage_shop_desc_demo')+"</p><div class='shop-score'><p>"+WST.lang('custompage_shop_score')+"：</p><div class='star-icon'></div><div class='star-icon'></div><div class='star-icon'></div><div class='star-icon'></div><div class='star-icon'></div></div></div><div class='wst-flex-row wst-jsb'><div class='shop-location'><div class='location-icon'></div><p>"+WST.lang('custompage_shop_address_demo')+"</p></div><p>2km</p></div></div></div><div class='btn-del' onclick='delShopBtn(this)'>"+WST.lang('custompage_del')+"</div></div><div class='border-absolute' onclick='addShopClassActive(this)' onmouseover='addClassActive(this)' onmouseout='removeClassActive(this)'></div></div>");
        $(".attr-title").html(WST.lang("custompage_component_shop"));
        hideComponentAttrContent(1);
        $(".attr-content").append("<div class='attr-content-shop "+shop_attr_class_name+"' shop_num="+shop_num+" ><div class='attr-content-shop-item'><label class='d-label'>"+WST.lang('custompage_search_radius')+"</label><input type='text' value='5' class='attr-content-shop-search-radius' onkeyup='javascript:WST.isChinese(this,1)' onkeypress='return WST.isNumberdoteKey(event)' /><span class='shop-radius-tip'>Km"+WST.lang('custompage_within')+"</span></div><div class='attr-content-shop-item'><label class='d-label'>"+WST.lang('custompage_top_text')+"</label><input type='text' value='"+WST.lang('custompage_nearby_shop')+"' class='attr-content-shop-title' onkeyup='setShopTitle(this)'/><span class='shop-title-tip'><span class='shop-title-length'>4</span>/10</span></div><div class='shop-tips red'>"+WST.lang('custompage_shop_tips')+"</div></div>");
        $(".attr-content-shop").each(function(idx, item) {
            if($(item).hasClass("shop_attr"+shop_num)){
                $(item).show();
            }else{
                $(item).hide();
            }
        });
    });

    // 新闻组件
    $(".component-new").click(function(event){
        componentRemoveClasses(1);
        var new_num = 0;
        var new_num_arr = [];
        $(".effect-new").each(function(idx, item) {
            new_num = parseInt($(item).attr('new_num'));
            new_num_arr.push(new_num);
        });
        if(new_num_arr.length>0){
            new_num = Math.max.apply(null,new_num_arr)+1;
        }else{
            new_num = 1;
        }
        var new_class_name = "new"+new_num;
        var new_attr_class_name = "new_attr"+new_num;
        $(".effect-content").append("<div class='el-relative'><div class='effect-new "+new_class_name+"' new_num="+new_num+" ><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort-name' value='new'/><div class='new-title'><div class='new-title-text'>"+WST.lang('custompage_new_info')+"</div><div>"+WST.lang('custompage_more')+"</div></div><div class='btn-del' onclick='delNewBtn(this)'>"+WST.lang('custompage_del')+"</div></div><div class='border-absolute' onclick='addNewClassActive(this)' onmouseover='addClassActive(this)' onmouseout='removeClassActive(this)'></div></div>");
        $(".attr-title").html(WST.lang("custompage_component_new"));
        hideComponentAttrContent(1);
        $(".attr-content").append("<div class='attr-content-new "+new_attr_class_name+"' new_num="+new_num+"><div class='attr-content-new-item' ><label class='d-label'>"+WST.lang('custompage_new_num')+"</label><input type='radio' value='2' name='new-count"+new_num+"'  id='n-count-2-"+new_num+"' checked onclick='changeNewCount(this)' /><label for='n-count-2-"+new_num+"' class='d-a-label'>2"+WST.lang('custompage_goods_unit')+"</label><input type='radio' value='4' name='new-count"+new_num+"' id='n-count-4-"+new_num+"' onclick='changeNewCount(this)' /><label for='n-count-4-"+new_num+"' class='d-a-label'>4"+WST.lang('custompage_goods_unit')+"</label><input type='radio' value='6' name='new-count"+new_num+"' id='n-count-6-"+new_num+"' onclick='changeNewCount(this)' /><label for='n-count-6-"+new_num+"' class='d-a-label'>6"+WST.lang('custompage_goods_unit')+"</label><input type='radio' value='8' name='new-count"+new_num+"' id='n-count-8-"+new_num+"' onclick='changeNewCount(this)' /><label for='n-count-8-"+new_num+"' class='d-a-label'>8"+WST.lang('custompage_goods_unit')+"</label></div><div class='attr-content-new-item'><label class='d-label'>"+WST.lang('custompage_new_title')+"</label><input type='text' value='"+WST.lang('custompage_new_info')+"' class='attr-content-new-title' onkeyup='setNewTitle(this)'/><span class='new-title-tip'><span class='new-title-length'>4</span>/8</span></div><div class='attr-content-new-item'><label class='d-label'>"+WST.lang('custompage_new_column_add')+"</label><input type='radio' value='1' name='news-select"+new_num+"' onclick='addNews(this)' /></div><input type='hidden' class='new-select-ids-value' value='' ><input type='hidden' class='new-count' value='2' /></div>");
        $(".attr-content-new").each(function(idx, item) {
            if($(item).hasClass("new_attr"+new_num)){
                $(item).show();
            }else{
                $(item).hide();
            }
        });
    });

    // 营销活动组件
    $(".component-marketing").click(function(event){
        componentRemoveClasses(1);
        var marketing_num = 0;
        var marketing_num_arr = [];
        $(".effect-marketing").each(function(idx, item) {
            marketing_num = parseInt($(item).attr('marketing_num'));
            marketing_num_arr.push(marketing_num);
        });
        if(marketing_num_arr.length>0){
            marketing_num = Math.max.apply(null,marketing_num_arr)+1;
        }else{
            marketing_num = 1;
        }
        var marketing_class_name = "marketing"+marketing_num;
        var marketing_attr_class_name = "marketing_attr"+marketing_num;
        $(".effect-content").append("<div class='el-relative'><div class='effect-marketing "+marketing_class_name+"' marketing_num="+marketing_num+" ><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort' value='0'/><input type='hidden' class='sort-name' value='marketing'/><div class='marketing-title'><div class='wst-flex-row wst-ac'><div class='marketing-title-text'>"+WST.lang('custompage_marketing_title')+"</div><div class='seckill-title none2'><div class='seckill-title-text'>12:00"+WST.lang('custompage_session')+"</div><div class='seckill-time'>01:15:16</div></div></div><div class='seckill-more'>"+WST.lang('custompage_more')+"</div></div><div class='wst-flex-row marketing-main'><div class='goods wst-flex-row' ><div class='goods-image'><img src='"+base_url+"/upload/custompagedecoration/base/good_01.jpg' alt=''  /></div><div class='goods-info wst-flex-column wst-jsb' ><p class='goods-name'>"+WST.lang('custompage_here_show_goods_name')+"</p><div class='pintuan-detail none'><div class='wst-flex-row wst-jsb'><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p><p class='pintuan-num' >2"+WST.lang('custompage_pintuan_num')+"</p></div></div><div class='seckill-detail none'><div class='wst-flex-row wst-jsb'><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p><p class='goods-price goods-price-decoration' >"+WST.lang('currency_symbol')+"150.00</p></div></div><div class='auction-detail none'><p class='goods-price auction-num'>1"+WST.lang('custompage_auction_num')+"</p><p class='goods-price'>"+WST.lang('custompage_current_price')+"：<span class='red'>"+WST.lang('currency_symbol')+"10.00</span></p></div><div class='bargain-detail none'><div class='wst-flex-row wst-jsb'><p class='market-price goods-price-decoration '>"+WST.lang('custompage_old_price')+":"+WST.lang('currency_symbol')+"20.00</p><p class='bargain-num'>2"+WST.lang('custompage_join_num')+"</p></div><p class='goods-price red '>"+WST.lang('custompage_bottom_price')+"："+WST.lang('currency_symbol')+"5.00</p></div></div></div><div class='goods wst-flex-row' ><div class='goods-image'><img src='"+base_url+"/upload/custompagedecoration/base/good_02.jpg' alt=''  /></div><div class='goods-info wst-flex-column wst-jsb' ><p class='goods-name'>"+WST.lang('custompage_here_show_goods_name')+"</p><div class='pintuan-detail none'><div class='wst-flex-row wst-jsb'><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p><p class='pintuan-num' >2"+WST.lang('custompage_pintuan_num')+"</p></div></div><div class='seckill-detail none'><div class='wst-flex-row wst-jsb'><p class='goods-price red '>"+WST.lang('currency_symbol')+"99.00</p><p class='goods-price goods-price-decoration' >"+WST.lang('currency_symbol')+"150.00</p></div></div><div class='auction-detail none'><p class='goods-price auction-num'>1"+WST.lang('custompage_auction_num')+"</p><p class='goods-price'>"+WST.lang('custompage_current_price')+"：<span class='red'>"+WST.lang('currency_symbol')+"10.00</span></p></div><div class='bargain-detail none'><div class='wst-flex-row wst-jsb'><p class='market-price goods-price-decoration '>"+WST.lang('custompage_old_price')+":"+WST.lang('currency_symbol')+"20.00</p><p class='bargain-num'>2"+WST.lang('custompage_join_num')+"</p></div><p class='goods-price red '>"+WST.lang('custompage_bottom_price')+"："+WST.lang('currency_symbol')+"5.00</p></div></div></div></div><div class='btn-del' onclick='delMarketingBtn(this)'>"+WST.lang('custompage_del')+"</div></div><div class='border-absolute' onclick='addMarketingClassActive(this)' onmouseover='addClassActive(this)' onmouseout='removeClassActive(this)'></div></div>");
        $(".attr-title").html(WST.lang("custompage_component_marketing"));
        hideComponentAttrContent(1);
        var marketingTypeHtml = $('.marketingType').html();
        $(".attr-content").append("<div class='attr-content-marketing "+marketing_attr_class_name+"' marketing_num="+marketing_num+" ><div class='attr-content-marketing-item'><label class='d-label'>"+WST.lang('custompage_component_marketing')+"</label>"+marketingTypeHtml+"</div><div class='attr-content-marketing-item'><label class='d-label'>"+WST.lang('custompage_column_title')+"</label><input type='text' value='"+WST.lang('custompage_marketing_title')+"' class='attr-content-marketing-title' onkeyup='setMarketingTitle(this)'/><span class='marketing-title-tip'><span class='marketing-title-length'>0</span>/10</span></div><div class='attr-content-marketing-item'><label class='d-label'>"+WST.lang('custompage_vertical_padding')+"</label><div class='attr-content-marketing-item-content'><input type='hidden' class='marketing-range-slider'  value='0' onchange='changeMarketingPadding(this)'/><span class='marketing-vertical-padding-value'>0</span><span class='pixel'>px ("+WST.lang('custompage_px')+")</span></div></div><div class='marketing-tips red'>"+WST.lang('custompage_marking_tips3')+"</div><input type='hidden' class='marketing-type' value=''><input type='hidden' class='marketing-vertical-padding' value='0'></div>");
        $('.marketing-range-slider').jRange({
            from: 0,
            to: 50,
            step: 1,
            format: '%s',
            width: 120,
            showLabels: false,
            isRange : true,
            showScale: false
        });
        $(".attr-content-marketing").each(function(idx, item) {
            if($(item).hasClass("marketing_attr"+marketing_num)){
                $(item).show();
            }else{
                $(item).hide();
            }
        });
    });
});

// 公共方法开始
function addClassActive(obj){
    $(obj).parent().find(".border-absolute").addClass("effect-active");
}

function removeClassActive(obj){
    if($(obj).hasClass("select")){

    }else{
        $(obj).parent().find(".border-absolute").removeClass("effect-active");
    }
}

function delBtn(obj){
    var that = $(obj);
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        that.parent().remove();
        layer.close(index);
    });
}

function componentRemoveClasses(type){
    if(type==1){
        $(".effect-title").removeClass("effect-active");
        $(".effect-title").removeClass("select");
    }
    $(".effect-media-swiper").each(function(idx, item) {
        $(item).parent().find(".border-absolute").removeClass('effect-active');
        $(item).parent().find(".border-absolute").removeClass('select');
    });
    $(".effect-floor-goods").each(function(idx, item) {
        $(item).parent().find(".border-absolute").removeClass('effect-active');
        $(item).parent().find(".border-absolute").removeClass('select');
    });
    $(".effect-goods-group").each(function(idx, item) {
        $(item).parent().find(".border-absolute").removeClass('effect-active');
        $(item).parent().find(".border-absolute").removeClass('select');
    });
    $(".effect-nav").each(function(idx, item) {
        $(item).parent().find(".border-absolute").removeClass('effect-active');
        $(item).parent().find(".border-absolute").removeClass('select');
    });
    $(".effect-notice").each(function(idx, item) {
        $(item).parent().find(".border-absolute").removeClass('effect-active');
        $(item).parent().find(".border-absolute").removeClass('select');
    });
    $(".effect-search").each(function(idx, item) {
        $(item).parent().find(".border-absolute").removeClass('effect-active');
        $(item).parent().find(".border-absolute").removeClass('select');
    });
    $(".effect-coupon").each(function(idx, item) {
        $(item).parent().find(".border-absolute").removeClass('effect-active');
        $(item).parent().find(".border-absolute").removeClass('select');
    });
    $(".effect-image").each(function(idx, item) {
        $(item).parent().find(".border-absolute").removeClass('effect-active');
        $(item).parent().find(".border-absolute").removeClass('select');
    });
    $(".effect-shopwindow").each(function(idx, item) {
        $(item).parent().find(".border-absolute").removeClass('effect-active');
        $(item).parent().find(".border-absolute").removeClass('select');
    });
    $(".effect-video").each(function(idx, item) {
        $(item).parent().find(".border-absolute").removeClass('effect-active');
        $(item).parent().find(".border-absolute").removeClass('select');
    });
    $(".effect-blank").each(function(idx, item) {
        $(item).parent().find(".border-absolute").removeClass('effect-active');
        $(item).parent().find(".border-absolute").removeClass('select');
    });
    $(".effect-line").each(function(idx, item) {
        $(item).parent().find(".border-absolute").removeClass('effect-active');
        $(item).parent().find(".border-absolute").removeClass('select');
    });
    $(".effect-text").each(function(idx, item) {
        $(item).parent().find(".border-absolute").removeClass('effect-active');
        $(item).parent().find(".border-absolute").removeClass('select');
    });
    $(".effect-txt").each(function(idx, item) {
        $(item).parent().find(".border-absolute").removeClass('effect-active');
        $(item).parent().find(".border-absolute").removeClass('select');
    });
    $(".effect-image-text").each(function(idx, item) {
        $(item).parent().find(".border-absolute").removeClass('effect-active');
        $(item).parent().find(".border-absolute").removeClass('select');
    });
    $(".effect-shop").each(function(idx, item) {
        $(item).parent().find(".border-absolute").removeClass('effect-active');
        $(item).parent().find(".border-absolute").removeClass('select');
    });
    $(".effect-new").each(function(idx, item) {
        $(item).parent().find(".border-absolute").removeClass('effect-active');
        $(item).parent().find(".border-absolute").removeClass('select');
    });
    $(".effect-marketing").each(function(idx, item) {
        $(item).parent().find(".border-absolute").removeClass('effect-active');
        $(item).parent().find(".border-absolute").removeClass('select');
    });
}

function hideComponentAttrContent(type){
    if(type==1){
        $(".attr-content-page").hide();
    }
    $(".attr-content-swiper").hide();
    $(".attr-content-floor-goods").hide();
    $(".attr-content-goods-group").hide();
    $(".attr-content-nav").hide();
    $(".attr-content-notice").hide();
    $(".attr-content-search").hide();
    $(".attr-content-coupon").hide();
    $(".attr-content-image").hide();
    $(".attr-content-shopwindow").hide();
    $(".attr-content-video").hide();
    $(".attr-content-blank").hide();
    $(".attr-content-line").hide();
    $(".attr-content-text").hide();
    $(".attr-content-txt").hide();
    $(".attr-content-image-text").hide();
    $(".attr-content-shop").hide();
    $(".attr-content-new").hide();
    $(".attr-content-marketing").hide();
}
// 公共方法结束

// 页面设置组件方法开始
function setPageTitle(obj){
    var page_title = $(obj).val();
    $(".effect-title-text").html(page_title);
}
// 页面设置组件方法结束

// 轮播组件方法开始
function delSwiperBtn(obj){
    var swiper_num = $(obj).parent().attr("swiper_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".effect-media-swiper").each(function(idx, item) {
            if($(item).hasClass("swiper"+swiper_num)){
                $(item).parent().remove();
            }
        });
        $(".attr-content-swiper").each(function(idx, item) {
            if($(item).hasClass("swiper_attr"+swiper_num)){
                $(item).remove();
            }
        });
        layer.close(index);
    });
}

function delSwiperItemBtn(obj){
    var that = $(obj);
    var swiper_item_counts = 0;
    var swiper_num = $(obj).parent().parent().attr("swiper_num");
    var swiper_class_name = "swiper"+swiper_num;
    var swiper_attr_class_name = "swiper_attr"+swiper_num;
    var img_url = $(obj).parent().find(".webuploader-pick img").attr("src") ? $(obj).parent().find(".webuploader-pick img").attr("src") : $(obj).parent().find('.banner-picker img').attr("src");
    var img_src = "";
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".attr-content-swiper").each(function(idx, item) {
            if($(item).attr("swiper_num") == swiper_num){
                $(item).find(".attr-content-swiper-item-img").each(function(idx, item) {
                    swiper_item_counts += 1;
                });
            }
        });
        if(swiper_item_counts == 1){
            WST.msg(WST.lang('custompage_at_least_keep_one'),{time:1000,anim: 6});
        }else{
            that.parent().remove();
            $(".attr-content-swiper").each(function(idx, item) {
                if($(item).hasClass(swiper_attr_class_name)){
                    $(item).find(".attr-content-swiper-item-img").each(function(index, obj) {
                        if(index == 0){
                            // 保存swiper组件第一张图片的路径，用来显示在中间的效果图
                            img_src = $(obj).find(".webuploader-pick img").attr("src");
                        }
                    });
                }
            });
            $(".effect-media-swiper").each(function(idx, item) {
                if($(item).hasClass(swiper_class_name)){
                    if($(item).find("img").attr("src") == img_url){
                        $(item).find("img").attr("src",img_src);
                    }
                }
            });
            layer.close(index);
        }
    });
}

function addSwiperClassActive(obj){
    var swiper_num = $(obj).parent().find(".effect-media-swiper").attr("swiper_num");
    componentRemoveClasses(1);
    $(obj).addClass("effect-active");
    $(obj).addClass("select");

    $(".attr-title").html(WST.lang("custompage_component_swiper"));
    hideComponentAttrContent(1);
    $(".attr-content-swiper").each(function(idx, item) {
        if($(item).hasClass("swiper_attr"+swiper_num)){
            $(item).show();
        }else{
            $(item).hide();
        }
    });
}

function addSwiperItem(obj){
    var swiper_num = $(obj).parent().attr("swiper_num");
    var swiper_img_num = '';
    var swiper_class_name = "swiper"+swiper_num;
    var swiper_attr_class_name = "swiper_attr"+swiper_num;
    var img_src = "";
    $(".attr-content-swiper").each(function(idx, item) {
        if($(item).hasClass(swiper_attr_class_name)){
            $(item).find(".attr-content-swiper-item-img").each(function(index, obj) {
                if($(obj).find(".banner-picker")){
                    swiper_img_num = $(obj).find(".banner-picker").attr('id');
                }
            });
        }
    });
    // 截取最后一个id属性值里的数字，例如（banner-picker-2-1）
    swiper_img_num = parseInt(swiper_img_num.substr(parseInt(swiper_img_num.lastIndexOf("\-"))+1,swiper_img_num.length))+1;
    $(obj).before("<div class='attr-content-swiper-item-img'><div class='attr-content-swiper-img' img_url='upload/custompagedecoration/base/pc_banner_01.jpg'><label class='d-label'>"+WST.lang('custompage_image')+"</label><div class='banner-picker' id='banner-picker-"+swiper_num+"-"+swiper_img_num+"'><img src='"+base_url+"/upload/custompagedecoration/base/pc_banner_01.jpg'/></div><div class='image-tips'>"+WST.lang('custompage_swiper_image_tips2')+"</div></div><label class='d-label'>"+WST.lang('custompage_link')+"</label><input type='text' value='' class='attr-content-swiper-link' /><div class='attr-btn-del' onclick='delSwiperItemBtn(this)'>X</div></div>");
    WST.upload({
        pick:'#banner-picker-'+swiper_num+'-'+swiper_img_num,
        formData: {dir:'custompagedecoration'},
        accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
        callback:function(f){
            var json = WST.toAdminJson(f);
            if(json.status==1){
                $('#uploadMsg').empty().hide();
                var img_url = json.savePath+json.thumb; //保存到数据库的路径
                $("#banner-picker-"+swiper_num+'-'+swiper_img_num).parent().attr('img_url',img_url);
                $("#banner-picker-"+swiper_num+'-'+swiper_img_num).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                $(".attr-content-swiper").each(function(idx, item) {
                    if($(item).hasClass(swiper_attr_class_name)){
                        $(item).find(".attr-content-swiper-item-img").each(function(index, obj) {
                            if(index == 0){
                                // 保存swiper组件第一张图片的路径，用来显示在中间的效果图
                                img_src = $(obj).find(".attr-content-swiper-img").attr("img_url");
                            }
                        });
                    }
                });
                $(".effect-media-swiper").each(function(idx,item){
                    if($(item).hasClass(swiper_class_name)){
                        // 当改变的是第一张轮播图，需要更新中间的效果图
                        if(swiper_img_num==1){
                            if(img_src.indexOf('banner')!=-1){
                                $(item).find('img').attr('src',img_src);
                            }else{
                                $(item).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+img_src);
                            }
                        }
                    }
                });
            }else{
                WST.msg(json.msg,{icon:2});
            }
        },
        progress:function(rate){
            $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
        }
    });
}

function changeSwiperIndicator(obj){
    var swiper_num = $(obj).parent().parent().attr("swiper_num");
    var indicator_type = $(obj).val();
    $(obj).parent().parent().find(".indicator-type").val(indicator_type);
    $(".effect-media-swiper").each(function(idx, item) {
        if($(item).hasClass("swiper"+swiper_num)){
            if(indicator_type == 1){
                // 长方形
                $(item).find("span").removeClass("roundness");
                $(item).find("span").removeClass("square");
                $(item).find("span").addClass("rectangle");
            }else if(indicator_type == 2){
                // 正方形
                $(item).find("span").removeClass("roundness");
                $(item).find("span").removeClass("rectangle");
                $(item).find("span").addClass("square");
            }else{
                // 圆形
                $(item).find("span").removeClass("square");
                $(item).find("span").removeClass("rectangle");
                $(item).find("span").addClass("roundness");
            }
        }
    });
}

function changeSwiperInterval(obj){
    var swiper_num = $(obj).parent().parent().attr("swiper_num");
    var interval = $(obj).val();
    $(obj).parent().parent().find(".interval").val(interval);
}

function changeSwiperPadding(obj,type){
    var swiper_num = $(obj).parent().parent().parent().attr("swiper_num");
    var swiper_margin = $(obj).val().split(',')[1];
    switch (type) {
        case 1:
            $(obj).parent().parent().parent().find('.swiper-padding-top').val(swiper_margin);
            $(obj).parent().find(".swiper-padding-top-value").text(swiper_margin);
            break;
        case 2:
            $(obj).parent().parent().parent().find('.swiper-padding-bottom').val(swiper_margin);
            $(obj).parent().find(".swiper-padding-bottom-value").text(swiper_margin);
            break;
        case 3:
            $(obj).parent().parent().parent().find('.swiper-padding-left').val(swiper_margin);
            $(obj).parent().find(".swiper-padding-left-value").text(swiper_margin);
            break;
        case 4:
            $(obj).parent().parent().parent().find('.swiper-padding-right').val(swiper_margin);
            $(obj).parent().find(".swiper-padding-right-value").text(swiper_margin);
            break;
    }
    $(".effect-media-swiper").each(function(idx, item) {
        if($(item).hasClass("swiper"+swiper_num)){
            $(item).find("img").each(function(key, value) {
                switch (type) {
                    case 1:
                        $(value).css("padding-top",swiper_margin+"px");
                        break;
                    case 2:
                        $(value).css("padding-bottom",swiper_margin+"px");
                        break;
                    case 3:
                        $(value).css("padding-left",swiper_margin+"px");
                        break;
                    case 4:
                        $(value).css("padding-right",swiper_margin+"px");
                        break;
                }
            });
        }
    });
}
// 轮播组件方法结束

// 楼层商品组件方法开始
function delFloorGoodsBtn(obj){
    var floor_goods_num = $(obj).parent().attr("floor_goods_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".effect-floor-goods").each(function(idx, item) {
            if($(item).hasClass("floor_goods"+floor_goods_num)){
                $(item).parent().remove();
            }
        });
        $(".attr-content-floor-goods").each(function(idx, item) {
            if($(item).hasClass("floor_goods_attr"+floor_goods_num)){
                $(item).remove();
            }
        });
        layer.close(index);
    });
}

function addFloorGoodsClassActive(obj){
    var floor_goods_num = $(obj).parent().find(".effect-floor-goods").attr("floor_goods_num");
    componentRemoveClasses(1);
    $(obj).addClass("effect-active");
    $(obj).addClass("select");

    $(".attr-title").html(WST.lang("custompage_component_floor_goods"));
    hideComponentAttrContent(1);
    $(".attr-content-floor-goods").each(function(idx, item) {
        if($(item).hasClass("floor_goods_attr"+floor_goods_num)){
            $(item).show();
        }else{
            $(item).hide();
        }
    });
}

function setFloorGoodsTitle(obj){
    var floor_goods_num = $(obj).parent().parent().attr("floor_goods_num");
    var floor_goods_title = $(obj).val();
    var floor_goods_title_length = floor_goods_title.length;
    $(".effect-floor-goods").each(function(idx, item) {
        if($(item).hasClass("floor_goods"+floor_goods_num)){
            if(floor_goods_title.length>10){
                floor_goods_title = floor_goods_title.substr(0,10);
            }
            $(item).find(".floor-goods-title-text").html(floor_goods_title);
        }
    });
    $(".attr-content-floor-goods").each(function(idx, item) {
        if($(item).hasClass("floor_goods_attr"+floor_goods_num)){
            if(floor_goods_title_length > 10){
                $(item).find('.floor-goods-title-length').html(10);
            }else{
                $(item).find('.floor-goods-title-length').html(floor_goods_title_length);
            }
        }
    });
}

function setFloorGoodsColumnsTitle(obj){
    var floor_goods_num = $(obj).parent().parent().parent().attr("floor_goods_num");
    var columns_title = $(obj).val();
    var columns_title_length = columns_title.length;
    var columns_item = $(obj).attr('columns_item');
    $(".effect-floor-goods").each(function(idx, item) {
        if ($(item).hasClass("floor_goods" + floor_goods_num)) {
            $(item).find('.columns-title div').each(function(key, value) {
                if($(value).attr('columns_item') == columns_item){
                    if(columns_title.length>6){
                        columns_title = columns_title.substr(0,6);
                    }
                    $(value).text(columns_title);
                }
            });
        }
    });
    $(".attr-content-floor-goods").each(function(idx, item) {
        if($(item).hasClass("floor_goods_attr"+floor_goods_num)){
            $(item).find('.attr-content-floor-goods-item-column').each(function(index,obj){
                if($(obj).find('.floor-goods-columns-name').attr('columns_item') == columns_item){
                    if(columns_title_length > 6){
                        $(obj).find('.floor-goods-columns-name-length').html(6);
                    }else{
                        $(obj).find('.floor-goods-columns-name-length').html(columns_title_length);
                    }
                }
            });
        }
    });
}

function addFloorGoodsItemImg(obj){

}

function addFloorGoodsColumnItem(obj){
    var hidden_cats_id = $('.hidden-child-cats-id').val();
    var columns_item_counts = 0;
    var columns_item = 0;
    var floor_goods_num = $(obj).parent().attr("floor_goods_num");
    var floor_goods_class_name = "floor_goods"+floor_goods_num;
    $(".attr-content-floor-goods").each(function(idx, item) {
        if($(item).attr("floor_goods_num") == floor_goods_num){
            $(item).find(".attr-content-floor-goods-item-column").each(function(idx, item) {
                columns_item_counts += 1;
                columns_item = $(item).find('.floor-goods-columns-name').attr('columns_item');
            });
        }
    });
    if(columns_item_counts == 5){
        layer.msg(WST.lang("custompage_max_five_column"),{time:1000,anim: 6});
    }else{
        columns_item += 1;
        $('.attr-content-floor-goods-item-column-add').before("<div class='attr-content-floor-goods-item-column' ><div class='wst-flex-row wst-ac'><label class='d-label'>"+WST.lang('custompage_column_title')+"</label><input type='text' value='"+WST.lang('custompage_column_title')+"1' class='floor-goods-columns-name' columns_item="+columns_item+" onkeyup='setFloorGoodsColumnsTitle(this)' style='width:150px !important;' /><span class='floor-goods-columns-name-tip'><span class='floor-goods-columns-name-length'>5</span>/6</span></div><label class='d-label'>"+WST.lang('custompage_select_goods')+"</label><input type='radio' value='1' name='floor-goods-select"+floor_goods_num+"-"+columns_item+"' id='floor-goods-select-condition"+floor_goods_num+"-"+columns_item+"' onclick='changeFloorGoodsSelect(this)' columns_item='"+columns_item+"' checked /><label for='floor-goods-select-condition"+floor_goods_num+"-"+columns_item+"'>"+WST.lang('custompage_condition_select')+"</label><input type='radio' value='2' name='floor-goods-select"+floor_goods_num+"-"+columns_item+"' id='floor-goods-select-manual"+floor_goods_num+"-"+columns_item+"' onclick='changeFloorGoodsSelect(this)' columns_item='"+columns_item+"' /><label for='floor-goods-select-manual"+floor_goods_num+"-"+columns_item+"'>"+WST.lang('custompage_manual_add')+"</label><input type='hidden' class='floor-goods-select-ids-value' value='' columns_item='"+columns_item+"'><input type='hidden' class='floor-goods-select-cats-id-value' value="+hidden_cats_id+" columns_item='"+columns_item+"'><input type='hidden' class='floor-goods-tag-value' value='' columns_item='"+columns_item+"'/><input type='hidden' class='floor-goods-min-price-value' value='' columns_item='"+columns_item+"'/><input type='hidden' class='floor-goods-max-price-value' value='' columns_item='"+columns_item+"'/><div class='attr-btn-del' onclick='delFloorGoodsColumnItemBtn(this)'>X</div></div>");
        $(".effect-floor-goods").each(function(idx, item) {
            if ($(item).hasClass(floor_goods_class_name)) {
                $(item).find('.columns-title').append("<div columns_item="+columns_item+">"+WST.lang('custompage_column_title')+"1</div>");
            }
        });
    }
}

function delFloorGoodsColumnItemBtn(obj){
    var that = $(obj);
    var columns_item_counts = 0;
    var columns_item = $(obj).parent().find('.floor-goods-columns-name').attr('columns_item');
    var floor_goods_num = $(obj).parent().parent().attr("floor_goods_num");
    var floor_goods_class_name = "floor_goods"+floor_goods_num;

    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".attr-content-floor-goods").each(function(idx, item) {
            if($(item).attr("floor_goods_num") == floor_goods_num){
                $(item).find(".attr-content-floor-goods-item-column").each(function(idx, item) {
                    columns_item_counts += 1;
                });
                if(columns_item_counts == 1){
                    WST.msg(WST.lang('custompage_at_least_keep_one'),{time:1000,anim: 6});
                }else {
                    that.parent().remove();
                    $(".effect-floor-goods").each(function(idx, item) {
                        if($(item).hasClass(floor_goods_class_name)){
                            $(item).find(".columns-title div").each(function(key, value) {
                                if($(value).attr('columns_item') == columns_item){
                                    $(value).remove();
                                }
                            });
                        }
                    });
                    layer.close(index);
                }
            }
        });
    });
}

function changeFloorGoodsOrder(obj){
    var order = $(obj).val();
    $(obj).parent().parent().find(".floor-goods-order").val(order);
}

function changeFloorGoodsSelect(obj){
    var goods_select = $(obj).val();
    var floor_goods_num = $(obj).parent().parent().attr("floor_goods_num");
    var columns_item = $(obj).attr('columns_item');
    if(goods_select == 1){
        // 条件选取
        var floor_goods_select_cats_id_value = $(obj).parent().find('.floor-goods-select-cats-id-value').val();
        var floor_goods_tag_value = $(obj).parent().find('.floor-goods-tag-value').val();
        var floor_goods_min_price_value = $(obj).parent().find('.floor-goods-min-price-value').val();
        var floor_goods_max_price_value = $(obj).parent().find('.floor-goods-max-price-value').val();
        if(floor_goods_select_cats_id_value!=''){
            $('.floor-goods-select-child-cat-name').find('input[type=radio]').each(function(idx, item) {
                if($(item).attr('cats-id')==floor_goods_select_cats_id_value){
                    $(item).prop("checked",true);
                }
            });
        }else{
            $('.floor-goods-select-child-cat-name').find('input[type=radio]').each(function(idx, item) {
                // 默认选中第一个分类
                if(idx==0){
                    $(item).attr("checked",true);
                }
            });
        }
        if(floor_goods_tag_value!=''){
            $("input[name='floor-goods-tag']").each(function(idx, item) {
                if($(item).val()==floor_goods_tag_value){
                    $(item).prop("checked",true);
                }
            });
        }else{
            $("input[name='floor-goods-tag']").each(function(idx, item) {
                // 默认选中不设置
                if(idx==0){
                    $(item).prop("checked",true);
                }
            });
        }
        if(floor_goods_min_price_value!=''){
            $(".floor-goods-min-price").val(floor_goods_min_price_value);
        }else{
            $(".floor-goods-min-price").val('');
        }
        if(floor_goods_max_price_value!=''){
            $(".floor-goods-max-price").val(floor_goods_max_price_value);
        }else{
            $(".floor-goods-max-price").val('');
        }

        var box = WST.open({title:WST.lang('custompage_select_goods_by_condition'),type:1,content:$('#goodBox3'),area: ['80%', '80%'],offset:['10%','10%'],btn: [WST.lang('custompage_confirm'),WST.lang('custompage_cancel')],
            yes:function(){
                var floor_select_cats_ids = [];
                var floor_goods_tag = $("input[name='floor-goods-tag']:checked").val();
                var min_price = '';
                var max_price = '';
                var is_child = 0;
                if($(".floor-goods-min-price").val()!=''){
                    min_price = parseInt($(".floor-goods-min-price").val());
                }
                if($(".floor-goods-max-price").val()!=''){
                    max_price = parseInt($(".floor-goods-max-price").val());
                }
                var floor_goods_min_price = min_price;
                var floor_goods_max_price = max_price;
                if(floor_goods_min_price>0 && floor_goods_max_price==''){
                    WST.msg(WST.lang('custompage_require_goods_max_price'),{time:1000,anim: 6});
                    return;
                }
                if(floor_goods_max_price>0 && floor_goods_min_price==''){
                    WST.msg(WST.lang('custompage_require_goods_min_price'),{time:1000,anim: 6});
                    return;
                }
                if(floor_goods_min_price > floor_goods_max_price){
                    WST.msg(WST.lang('custompage_goods_price_set_error'),{time:1000,anim: 6});
                    return;
                }
                $('.floor-goods-select-child-cat-name').find('input[type=radio]').each(function(idx, item) {
                    if($(item).prop('checked')){
                        is_child = $(item).attr('is-child');
                        floor_select_cats_ids.push($(item).attr('cats-id'));
                    }
                });
                if(is_child==0){
                    WST.msg(WST.lang('custompage_floor_goods_select_child'),{time:1000,anim: 6});
                    return;
                }
                $(".attr-content-floor-goods").each(function(idx, item) {
                    if($(item).attr('floor_goods_num')==floor_goods_num){
                        $(item).find('.floor-goods-select-ids-value').each(function(key, value) {
                            if($(value).attr('columns_item') == columns_item){
                                $(value).val('');
                            }
                        });
                        $(item).find('.floor-goods-select-cats-id-value').each(function(key, value) {
                            if($(value).attr('columns_item') == columns_item){
                                $(value).val(floor_select_cats_ids);
                            }
                        });
                        $(item).find('.floor-goods-tag-value').each(function(key, value) {
                            if($(value).attr('columns_item') == columns_item){
                                $(value).val(floor_goods_tag);
                            }
                        });
                        $(item).find('.floor-goods-min-price-value').each(function(key, value) {
                            if($(value).attr('columns_item') == columns_item){
                                $(value).val(floor_goods_min_price);
                            }
                        });
                        $(item).find('.floor-goods-max-price-value').each(function(key, value) {
                            if($(value).attr('columns_item') == columns_item){
                                $(value).val(floor_goods_max_price);
                            }
                        });
                    }
                });
                $('#goodBox3').hide();
                layer.close(box);
            },cancel:function(){
                $('#goodBox3').hide();
            },end:function(){
                $('#goodBox3').hide();
            }});
    }else{
        // 手动添加
        $('#goodsName').val('');
        var floor_goods_select_ids_value = $(obj).parent().find('.floor-goods-select-ids-value').val();
        initSaleGrid(1,floor_goods_select_ids_value);
        var box = WST.open({title:WST.lang('custompage_select_goods'),type:1,content:$('#goodBox'),area: ['80%', '80%'],offset:['10%','10%'],btn: [WST.lang('custompage_confirm'),WST.lang('custompage_cancel')],
            yes:function(){
                $(".attr-content-floor-goods").each(function(idx, item) {
                    if($(item).attr('floor_goods_num')==floor_goods_num){
                        $(item).find('.floor-goods-select-ids-value').each(function(key, value) {
                            if($(value).attr('columns_item') == columns_item){
                                $(value).val(global_good_select_ids);
                            }
                        });
                        $(item).find('.floor-goods-select-cats-id-value').each(function(key, value) {
                            if($(value).attr('columns_item') == columns_item){
                                $(value).val('');
                            }
                        });
                    }
                });
                layer.close(box);
            },cancel:function(){
                $('#goodBox').hide();
            },end:function(){
                $('#goodBox').hide();
            }});
    }
}
// 楼层商品组件方法结束

// 商品组组件方法开始
function delGoodsGroupBtn(obj){
    var goods_group_num = $(obj).parent().attr("goods_group_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".effect-goods-group").each(function(idx, item) {
            if($(item).hasClass("goods_group"+goods_group_num)){
                $(item).parent().remove();
            }
        });
        $(".attr-content-goods-group").each(function(idx, item) {
            if($(item).hasClass("goods_group_attr"+goods_group_num)){
                $(item).remove();
            }
        });
        layer.close(index);
    });
}

function addGoodsGroupClassActive(obj){
    var goods_group_num = $(obj).parent().find(".effect-goods-group").attr("goods_group_num");
    componentRemoveClasses(1);
    $(obj).addClass("effect-active");
    $(obj).addClass("select");

    $(".attr-title").html(WST.lang("custompage_component_goods_group"));
    hideComponentAttrContent(1);
    $(".attr-content-goods-group").each(function(idx, item) {
        if($(item).hasClass("goods_group_attr"+goods_group_num)){
            $(item).show();
        }else{
            $(item).hide();
        }
    });
}

function changeGoodsNum(obj){
    var goods_group_num = $(obj).parent().parent().attr("goods_group_num");
    var goods_num_value = $(obj).val();
    $(".effect-goods-group").each(function (idx, item) {
        if ($(item).hasClass("goods_group" + goods_group_num)) {
            $(item).find('.goods').each(function (index, value) {
                if(goods_num_value==5) {
                    if(parseInt($(value).attr('goods_item'))>5){
                        $(value).hide();
                    }
                }else{
                    if(parseInt($(value).attr('goods_item'))>5){
                        $(value).show();
                    }
                }
            });
        }
    });

    $(obj).parent().parent().find(".goods-group-goods-nums").val(goods_num_value);
}

function setColumnsTitle(obj){
    var goods_group_num = $(obj).parent().parent().parent().attr("goods_group_num");
    var columns_title = $(obj).val();
    var columns_title_length = columns_title.length;
    var columns_item = $(obj).attr('columns_item');
    $(".effect-goods-group").each(function(idx, item) {
        if ($(item).hasClass("goods_group" + goods_group_num)) {
            $(item).find('.columns-title div').each(function(key, value) {
                if($(value).attr('columns_item') == columns_item){
                    if(columns_title.length>6){
                        columns_title = columns_title.substr(0,6);
                    }
                    $(value).text(columns_title);
                }
            });
        }
    });
    $(".attr-content-goods-group").each(function(idx, item) {
        if($(item).hasClass("goods_group_attr"+goods_group_num)){
            $(item).find('.attr-content-goods-group-item-column').each(function(index,obj){
               if($(obj).find('.goods-group-columns-name').attr('columns_item') == columns_item){
                   if(columns_title_length > 6){
                       $(obj).find('.goods-group-columns-name-length').html(6);
                   }else{
                       $(obj).find('.goods-group-columns-name-length').html(columns_title_length);
                   }
               }
            });
        }
    });
}

function changeGoodsSelect(obj){
    var goods_select = $(obj).val();
    var goods_group_num = $(obj).parent().parent().attr("goods_group_num");
    if(goods_select == 1){
        // 条件选取
        var goods_select_cats_id_value = $(obj).parent().find('.goods-select-cats-id-value').val();
        var goods_tag_value = $(obj).parent().find('.goods-tag-value').val();
        var goods_min_price_value = $(obj).parent().find('.goods-min-price-value').val();
        var goods_max_price_value = $(obj).parent().find('.goods-max-price-value').val();
        if(goods_select_cats_id_value!=''){
            $('.goods-select-cat-name').find('input[type=radio]').each(function(idx, item) {
                if($(item).attr('cats-id')==goods_select_cats_id_value){
                    $(item).prop("checked",true);
                }
            });
        }else{
            $('.goods-select-cat-name').find('input[type=radio]').each(function(idx, item) {
                // 默认选中第一个分类
                if(idx==0){
                    $(item).attr("checked",true);
                }
            });
        }
        if(goods_tag_value!=''){
            $("input[name='goods-tag']").each(function(idx, item) {
                if($(item).val()==goods_tag_value){
                    $(item).prop("checked",true);
                }
            });
        }else{
            $("input[name='goods-tag']").each(function(idx, item) {
                // 默认选中不设置
                if(idx==0){
                    $(item).prop("checked",true);
                }
            });
        }
        if(goods_min_price_value!=''){
            $(".goods-min-price").val(goods_min_price_value);
        }else{
            $(".goods-min-price").val('');
        }
        if(goods_max_price_value!=''){
            $(".goods-max-price").val(goods_max_price_value);
        }else{
            $(".goods-max-price").val('');
        }

        var box = WST.open({title:WST.lang('custompage_select_goods_by_condition'),type:1,content:$('#goodBox2'),area: ['80%', '80%'],offset:['10%','10%'],btn: [WST.lang('custompage_confirm'),WST.lang('custompage_cancel')],
            yes:function(){
                var select_cats_ids = [];
                var goods_tag = $("input[name='goods-tag']:checked").val();
                var min_price = '';
                var max_price = '';
                if($(".goods-min-price").val()!=''){
                    min_price = parseInt($(".goods-min-price").val());
                }
                if($(".goods-max-price").val()!=''){
                    max_price = parseInt($(".goods-max-price").val());
                }
                var goods_min_price = min_price;
                var goods_max_price = max_price;
                if(goods_min_price>0 && goods_max_price==''){
                    WST.msg(WST.lang('custompage_require_goods_max_price'),{time:1000,anim: 6});
                    return;
                }
                if(goods_max_price>0 && goods_min_price==''){
                    WST.msg(WST.lang('custompage_require_goods_min_price'),{time:1000,anim: 6});
                    return;
                }
                if(goods_min_price > goods_max_price){
                    WST.msg(WST.lang('custompage_goods_price_set_error'),{time:1000,anim: 6});
                    return;
                }
                $('.goods-select-cat-name').find('input[type=radio]').each(function(idx, item) {
                    if($(item).prop('checked')){
                        select_cats_ids.push($(item).attr('cats-id'));
                    }
                });
                $(".attr-content-goods-group").each(function(idx, item) {
                    if($(item).attr('goods_group_num')==goods_group_num){
                        $(item).find('.goods-select-ids-value').val('');
                        $(item).find('.goods-select-cats-id-value').val(select_cats_ids);
                        $(item).find('.goods-tag-value').val(goods_tag);
                        $(item).find('.goods-min-price-value').val(goods_min_price);
                        $(item).find('.goods-max-price-value').val(goods_max_price);
                    }
                });
                $('#goodBox2').hide();
                layer.close(box);
            },cancel:function(){
                $('#goodBox2').hide();
            },end:function(){
                $('#goodBox2').hide();
            }});
    }else{
        // 手动添加
        $('#goodsName').val('');
        var goods_select_ids_value = $(obj).parent().find('.goods-select-ids-value').val();
        initSaleGrid(1,goods_select_ids_value);
        var box = WST.open({title:WST.lang('custompage_select_goods'),type:1,content:$('#goodBox'),area: ['80%', '80%'],offset:['10%','10%'],btn: [WST.lang('custompage_confirm'),WST.lang('custompage_cancel')],
            yes:function(){
                $(".attr-content-goods-group").each(function(idx, item) {
                    if($(item).attr('goods_group_num')==goods_group_num){
                        $(item).find('.goods-select-ids-value').val(global_good_select_ids);
                        $(item).find('.goods-select-cats-id-value').val('');
                    }
                });
                layer.close(box);
            },cancel:function(){
                $('#goodBox').hide();
            },end:function(){
                $('#goodBox').hide();
            }});
    }
}

function changeGoodsGroupOrder(obj){
    var order = $(obj).val();
    $(obj).parent().parent().find(".goods-group-order").val(order);
}

function selectGoodIds(obj){
    var checked = $(obj).prop('checked');
    var good_id = $(obj).attr("goods-id");
    if(checked){
        global_good_select_ids.push(good_id);
    }else{
        var index = global_good_select_ids.indexOf(good_id);
        if (index > -1) { // 将之前插入数组的good_id删除
            global_good_select_ids.splice(index, 1);
        }
    }
}

function setGoodsGroupTitle(obj){
    var goods_group_num = $(obj).parent().parent().attr("goods_group_num");
    var goods_group_title = $(obj).val();
    var goods_group_title_length = goods_group_title.length;
    $(".effect-goods-group").each(function(idx, item) {
        if($(item).hasClass("goods_group"+goods_group_num)){
            if(goods_group_title.length>10){
                goods_group_title = goods_group_title.substr(0,10);
            }
            $(item).find(".goods-group-title-text").html(goods_group_title);
        }
    });
    $(".attr-content-goods-group").each(function(idx, item) {
        if($(item).hasClass("goods_group_attr"+goods_group_num)){
            if(goods_group_title_length > 10){
                $(item).find('.goods-group-title-length').html(10);
            }else{
                $(item).find('.goods-group-title-length').html(goods_group_title_length);
            }
        }
    });
}

function changeGoodsGroupTitleDisplay(obj){
    var show_goods_group_title = $(obj).val();
    var goods_group_num = $(obj).parent().parent().attr("goods_group_num");
    $(obj).parent().parent().find('.show-goods-group-title').val(show_goods_group_title);
    $(".effect-goods-group").each(function (idx, item) {
        if ($(item).hasClass("goods_group" + goods_group_num)) {
            if (show_goods_group_title == 1) {
                $(item).find('.goods-head').show();
            } else {
                $(item).find('.goods-head').hide();
            }
        }
    });
}
// 商品组组件方法结束

// 导航组件方法开始
function delNavBtn(obj){
    var nav_num = $(obj).parent().attr("nav_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".effect-nav").each(function(idx, item) {
            if($(item).hasClass("nav"+nav_num)){
                $(item).parent().remove();
            }
        });
        $(".attr-content-nav").each(function(idx, item) {
            if($(item).hasClass("nav_attr"+nav_num)){
                $(item).remove();
            }
        });
        layer.close(index);
    });
}

function addNavClassActive(obj){
    var nav_num = $(obj).parent().find(".effect-nav").attr("nav_num");
    componentRemoveClasses(1);
    $(obj).addClass("effect-active");
    $(obj).addClass("select");

    $(".attr-title").html(WST.lang("custompage_component_nav"));
    hideComponentAttrContent(1);
    $(".attr-content-nav").each(function(idx, item) {
        if($(item).hasClass("nav_attr"+nav_num)){
            $(item).show();
        }else{
            $(item).hide();
        }
    });
}

function delNavItemBtn(obj){
    var nav_num = $(obj).parent().parent().attr("nav_num");
    var nav_item = $(obj).parent().attr("nav_item");
    var nav_item_counts = 0;
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".attr-content-nav-item-group").each(function(idx, item) {
            nav_item_counts += 1;
        });
        if(nav_item_counts == 3){
            WST.msg(WST.lang('custompage_at_least_keep_three'),{time:1000,anim: 6});
        }else{
            $(obj).parent().remove();
            $(".effect-nav").each(function(idx, item) {
                if($(item).hasClass("nav"+nav_num)){
                    $(item).find(".nav-right .nav-item").each(function(key, value) {
                        if($(value).attr("nav_item") == nav_item){
                            $(value).remove();
                        }
                    });
                }
            });
            layer.close(index);
        }
    });
}

function addNavItem(obj){
    var nav_num = $(obj).parent().attr("nav_num");
    var nav_item = 0;
    var nav_item_counts = 0;
    $(".attr-content-nav").each(function(idx, item) {
        if($(item).attr("nav_num") == nav_num){
            $(item).find(".attr-content-nav-item-group").each(function(idx, item) {
                nav_item_counts += 1;
                nav_item = parseInt($(item).attr('nav_item'));
            });
        }
    });
    if(nav_item_counts == 10){
        layer.msg(WST.lang("custompage_max_ten_text_nav"),{time:1000,anim: 6});
    }else {
        nav_item += 1;
        $(".effect-nav").each(function(idx, item) {
            if($(item).hasClass("nav"+nav_num)){
                $(item).find('.nav-right').append("<div class='nav-item' nav_item=" + nav_item + "><div class='nav-text'>"+WST.lang('custompage_bottom_text')+"</div></div>")
            }
        });
        $(obj).before("<div class='attr-content-nav-item-group' nav_item=" + nav_item + "><div class='attr-content-nav-info'><label class='nav-label'>"+WST.lang('custompage_text_content')+"</label><input type='text' value='"+WST.lang('custompage_bottom_text')+"' class='attr-content-nav-text' onkeyup='setNavItemTitle(this)' /><span class='nav-text-tip'><span class='nav-text-length'>4</span>/4</span></div><div class='attr-content-nav-info'><label class='nav-label'>"+WST.lang('custompage_text_color')+"</label><div class='select-component-item-color nav-select-text-color' style='background:#333333;'></div><input type='hidden' class='attr-content-nav-text-color' value='#333333' /></div><div class='attr-content-nav-info'><label class='nav-label'>"+WST.lang('custompage_link_address')+"</label><input type='text' value='' class='attr-content-nav-link' /></div><div class='attr-btn-del' onclick='delNavItemBtn(this)'>X</div></div>");
        $('.nav-select-text-color').colpick({
            layout: 'hex',
            submit: 1,
            colorScheme: 'dark',
            onSubmit: function (hsb, hex, rgb, el, bySetColor) {
                if (!bySetColor) {
                    $(el).css('background', '#' + hex);
                    var nav_num = $(el).parent().parent().parent().attr("nav_num");
                    var nav_item = $(el).parent().parent().attr("nav_item");
                    $(".effect-nav").each(function (idx, item) {
                        if ($(item).hasClass("nav" + nav_num)) {
                            $(item).find(".navs").each(function (key, value) {
                                if ($(value).attr("nav_item") == nav_item) {
                                    $(value).find(".navs-info p").css('color', '#' + hex);
                                    $(el).parent().find(".attr-content-nav-text-color").val('#' + hex);
                                }
                            });
                        }
                    });
                }
                $(el).colpickHide();
            }
        }).keyup(function () {
            $(this).colpickSetColor(this.value);
            $(this).colpickHide();
        });
    }
}
function changeNavCategoryDisplay(obj){
    var nav_num = $(obj).parent().parent().attr("nav_num");
    var show_nav_category = $(obj).val();
    $(".effect-nav").each(function(idx, item) {
        if($(item).hasClass("nav"+nav_num)){
            if(show_nav_category==1){
                $(item).find(".nav-left").show();
                $(item).css('border-bottom','2px solid #df2003');
            }else{
                $(item).find(".nav-left").hide();
                $(item).find(".nav-right").css('width','100%');
                $(item).css('border-bottom','none');
            }
        }
    });
    $(obj).parent().parent().find('.show-nav-category').val(show_nav_category);
}

function setNavItemTitle(obj){
    var nav_item = $(obj).parent().parent().attr("nav_item");
    var nav_text = $(obj).val();
    var nav_text_length = nav_text.length;
    var nav_num = $(obj).parent().parent().parent().attr("nav_num");
    $(".effect-nav").each(function(idx, item) {
        if($(item).hasClass("nav"+nav_num)){
            $(item).find(".nav-item").each(function(key, value) {
                if($(value).attr("nav_item") == nav_item){
                    if(nav_text.length>4){
                        nav_text = nav_text.substr(0,4);
                    }
                    $(value).find(".nav-text").html(nav_text);
                }
            });
        }
    });
    $(".attr-content-nav .attr-content-nav-item-group").each(function(idx, item) {
        if($(item).attr('nav_item') == nav_item){
            if(nav_text_length > 4){
                $(item).find('.nav-text-length').html(4);
            }else{
                $(item).find('.nav-text-length').html(nav_text_length);
            }
        }
    });
}
// 导航组件方法结束

// 公告组件方法开始
function delNoticeBtn(obj){
    var notice_num = $(obj).parent().attr("notice_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".effect-notice").each(function(idx, item) {
            if($(item).hasClass("notice"+notice_num)){
                $(item).parent().remove();
            }
        });
        $(".attr-content-notice").each(function(idx, item) {
            if($(item).hasClass("notice_attr"+notice_num)){
                $(item).remove();
            }
        });
        layer.close(index);
    });
}

function addNoticeClassActive(obj){
    var notice_num = $(obj).parent().find(".effect-notice").attr("notice_num");
    componentRemoveClasses(1);
    $(obj).addClass("effect-active");
    $(obj).addClass("select");

    $(".attr-title").html(WST.lang("custompage_component_notice"));
    hideComponentAttrContent(1);
    $(".attr-content-notice").each(function(idx, item) {
        if($(item).hasClass("notice_attr"+notice_num)){
            $(item).show();
        }else{
            $(item).hide();
        }
    });
}

function addNoticeImg(){

}

function setNoticeText(obj){
    var notice_num = $(obj).parent().parent().parent().attr("notice_num");
    $(".attr-content-notice").each(function(idx, item) {
        if($(item).hasClass("notice_attr"+notice_num)){
            $(item).find('.attr-content-notice-item-group').each(function(key, value) {
                if(key==0){
                    // 只显示第一个公告的内容
                    var notice_text = $(value).find('.attr-content-notice-text').val();
                    $(".effect-notice").each(function(idx2, item2) {
                        if($(item2).hasClass("notice"+notice_num)){
                            $(item2).find(".notice-info p").html(notice_text);
                        }
                    });
                }
            });
        }
    });
}

function changeNoticePadding(obj){
    var notice_num = $(obj).parent().parent().parent().attr("notice_num");
    var notice_vertical_padding = $(obj).val().split(',')[1];
    $(obj).parent().parent().parent().find('.notice-vertical-padding').val(notice_vertical_padding);
    $(obj).parent().find(".notice-vertical-padding-value").text(notice_vertical_padding);
    $(".effect-notice").each(function(idx, item) {
        if($(item).hasClass("notice"+notice_num)){
            $(item).find(".notice-info").css("padding-top",notice_vertical_padding+"px");
            $(item).find(".notice-info").css("padding-bottom",notice_vertical_padding+"px");
        }
    });
}

function addNoticeItem(obj){
    var notice_item_counts = 0;
    var notice_item = 0;
    var notice_num = $(obj).parent().attr("notice_num");
    $(".attr-content-notice").each(function(idx, item) {
        if($(item).attr("notice_num") == notice_num){
            $(item).find(".attr-content-notice-item-group").each(function(idx, item) {
                notice_item_counts += 1;
                notice_item = $(item).find('.attr-content-notice-text').attr('notice_item');
            });
        }
    });
    if(notice_item_counts == 10){
        layer.msg(WST.lang("custompage_max_notice_tips"),{time:1000,anim: 6});
    }else{
        notice_item += 1;
        $('.attr-content-notice-item-group-add').before("<div class='attr-content-notice-item-group' ><div><label class='d-label'>"+WST.lang('custompage_notice')+"</label><input type='text' value='"+WST.lang('custompage_here_input_notice_content')+"' class='attr-content-notice-text' onkeyup='setNoticeText(this)' /></div><div><label class='d-label'>"+WST.lang('custompage_link')+"</label><input type='text' value='' class='attr-content-notice-link' /></div><div class='attr-btn-del' onclick='delNoticeItemBtn(this)'>X</div></div>");
    }
}

function delNoticeItemBtn(obj){
    var that = $(obj);
    var notice_item_counts = 0;
    var notice_num = $(obj).parent().parent().attr("notice_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".attr-content-notice").each(function(idx, item) {
            if($(item).attr("notice_num") == notice_num){
                $(item).find(".attr-content-notice-item-group").each(function(idx, item) {
                    notice_item_counts += 1;
                });
                if(notice_item_counts == 2){
                    WST.msg(WST.lang('custompage_at_least_keep_two_notice'),{time:1000,anim: 6});
                }else {
                    that.parent().remove();
                    layer.close(index);
                }
            }
        });
    });
}

function changeNoticeDirection(obj){
    $(obj).parent().parent().find('.notice-direction').val($(obj).val());
}
// 公告组件方法结束

// 搜索框组件方法开始
function delSearchBtn(obj){
    var search_num = $(obj).parent().attr("search_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".effect-search").each(function(idx, item) {
            if($(item).hasClass("search"+search_num)){
                $(item).parent().remove();
            }
        });
        $(".attr-content-search").each(function(idx, item) {
            if($(item).hasClass("search_attr"+search_num)){
                $(item).remove();
            }
        });
        layer.close(index);
    });
}

function addSearchClassActive(obj){
    var search_num = $(obj).parent().find(".effect-search").attr("search_num");
    componentRemoveClasses(1);
    $(obj).addClass("effect-active");
    $(obj).addClass("select");

    $(".attr-title").html(WST.lang("custompage_component_search"));
    hideComponentAttrContent(1);
    $(".attr-content-search").each(function(idx, item) {
        if($(item).hasClass("search_attr"+search_num)){
            $(item).show();
        }else{
            $(item).hide();
        }
    });
}

function addSearchItemImg(){

}

function setSearchText(obj){
    var search_num = $(obj).parent().parent().parent().parent().attr("search_num");
    var search_item = $(obj).attr('item');
    var search_text = $(obj).val();
    var search_text_length = search_text.length;
    $(".effect-search").each(function(idx, item) {
        if($(item).hasClass("search"+search_num)){
            if(search_item==1){
                if(search_text.length>10){
                    search_text = search_text.substr(0,10);
                }
                $(item).find(".search-text").html(search_text);
            }
        }
    });
    $(".attr-content-search").each(function(idx, item) {
        if($(item).hasClass("search_attr"+search_num)){
            if(search_text_length > 10) {
                search_text_length = 10;
            }
            $(".search-text-length").each(function(idx, item) {
                if($(item).attr("item")==search_item){
                    $(item).html(search_text_length);
                }
            });
        }
    });
}

function setSearchTextType(obj){
    var search_num = $(obj).parent().parent().parent().parent().attr("search_num");
    var search_item = $(obj).attr('item');
    var search_type = $(obj).val();
    var search_type_length = search_type.length;
    $(".effect-search").each(function(idx, item) {
        if($(item).hasClass("search"+search_num)){
            if(search_item==1){
                if(search_type.length>3){
                    search_type = search_type.substr(0,3);
                }
                $(item).find(".search-type").html(search_type);
            }
        }
    });
    $(".attr-content-search").each(function(idx, item) {
        if($(item).hasClass("search_attr"+search_num)){
            if(search_type_length > 3) {
                search_type_length = 3;
            }
            $(".search-type-length").each(function(idx, item) {
                if($(item).attr("item")==search_item){
                    $(item).html(search_type_length);
                }
            });
        }
    });
}

function setSearchHotsText(obj){
    var search_num = $(obj).parent().parent().parent().attr("search_num");
    var hots_text = $(obj).val();
    var hots_text_length = hots_text.length;
    var hots_item = $(obj).attr('hots_item');
    $(".effect-search").each(function(idx, item) {
        if ($(item).hasClass("search" + search_num)) {
            $(item).find('.search-hot-item').each(function(key, value) {
                if($(value).attr('hots_item') == hots_item){
                    if(hots_text.length>5){
                        hots_text = hots_text.substr(0,5);
                    }
                    $(value).find('p').html(hots_text);
                }
            });
        }
    });
    $(".attr-content-search").each(function(idx, item) {
        if($(item).hasClass("search_attr"+search_num)){
            $(item).find('.attr-content-search-hot-item').each(function(index,obj){
                if($(obj).find('.search-hots-text').attr('hots_item') == hots_item){
                    if(hots_text_length > 5){
                        $(obj).find('.search-hots-text-length').html(5);
                    }else{
                        $(obj).find('.search-hots-text-length').html(hots_text_length);
                    }
                }
            });
        }
    });
}

function addSearchHotItem(obj){
    var hots_item_counts = 0;
    var hots_item = 0;
    var search_num = $(obj).parent().attr("search_num");
    var search_class_name = "search"+search_num;
    $(".attr-content-search").each(function(idx, item) {
        if($(item).attr("search_num") == search_num){
            $(item).find(".attr-content-search-hot-item").each(function(idx, item) {
                hots_item_counts += 1;
                hots_item = $(item).find('.search-hots-text').attr('hots_item');
            });
        }
    });
    if(hots_item_counts == 5){
        layer.msg(WST.lang("custompage_max_hot_keyword"),{time:1000,anim: 6});
    }else{
        hots_item += 1;
        $('.attr-content-search-hot-item-add').before("<div class='attr-content-search-hot-item' ><div class='wst-flex-row wst-ac'><label class='d-label search-label'>"+WST.lang('custompage_hot_keyword')+"</label><input type='text' value='"+WST.lang('custompage_goods')+"' class='search-hots-text' hots_item='"+hots_item+"' onkeyup='setSearchHotsText(this)' style='width:120px !important;'/><span class='search-hots-text-tip'><span class='search-hots-text-length'>2</span>/5</span></div><div class='attr-btn-del' onclick='delSearchHotItemBtn(this)'>X</div></div>");
        $(".effect-search").each(function(idx, item) {
            if ($(item).hasClass(search_class_name)) {
                $(item).find('.search-hot-container').append("<div class='search-hot-item wst-flex-row wst-ac' hots_item="+hots_item+"><div class='search-hot-line'></div><p>"+WST.lang('custompage_goods')+"</p></div>");
            }
        });
    }
}

function delSearchHotItemBtn(obj){
    var that = $(obj);
    var hots_item_counts = 0;
    var hots_item = $(obj).parent().find('.search-hots-text').attr('hots_item');
    var search_num = $(obj).parent().parent().attr("search_num");
    var search_class_name = "search"+search_num;

    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".attr-content-search").each(function(idx, item) {
            if($(item).attr("search_num") == search_num){
                $(item).find(".attr-content-search-hot-item").each(function(idx, item) {
                    hots_item_counts += 1;
                });
                if(hots_item_counts == 1){
                    WST.msg(WST.lang('custompage_at_least_keep_one'),{time:1000,anim: 6});
                }else {
                    that.parent().remove();
                    $(".effect-search").each(function(idx, item) {
                        if($(item).hasClass(search_class_name)){
                            $(item).find(".search-hot-item").each(function(key, value) {
                                if($(value).attr('hots_item') == hots_item){
                                    $(value).remove();
                                }
                            });
                        }
                    });
                    layer.close(index);
                }
            }
        });
    });
}
// 搜索框组件方法结束

// 优惠券组件方法开始
function delCouponBtn(obj){
    var coupon_num = $(obj).parent().attr("coupon_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".effect-coupon").each(function(idx, item) {
            if($(item).hasClass("coupon"+coupon_num)){
                $(item).parent().remove();
            }
        });
        $(".attr-content-coupon").each(function(idx, item) {
            if($(item).hasClass("coupon_attr"+coupon_num)){
                $(item).remove();
            }
        });
        layer.close(index);
    });
}

function addCouponClassActive(obj){
    var coupon_num = $(obj).parent().find(".effect-coupon").attr("coupon_num");
    componentRemoveClasses(1);
    $(obj).addClass("effect-active");
    $(obj).addClass("select");

    $(".attr-title").html(WST.lang("custompage_component_coupon"));
    hideComponentAttrContent(1);
    $(".attr-content-coupon").each(function(idx, item) {
        if($(item).hasClass("coupon_attr"+coupon_num)){
            $(item).show();
        }else{
            $(item).hide();
        }
    });
}

function addCouponBgImg(){

}

function setCouponTitle(obj){
    var coupon_num = $(obj).parent().parent().attr("coupon_num");
    var coupon_title = $(obj).val();
    var coupon_title_length = coupon_title.length;
    $(".effect-coupon").each(function(idx, item) {
        if($(item).hasClass("coupon"+coupon_num)){
            if(coupon_title.length>4){
                coupon_title = coupon_title.substr(0,4);
            }
            $(item).find(".coupon-title").html(coupon_title);
        }
    });
    $(".attr-content-coupon").each(function(idx, item) {
        if($(item).hasClass("coupon_attr"+coupon_num)){
            if(coupon_title_length > 4){
                $(item).find('.coupon-title-length').html(4);
            }else{
                $(item).find('.coupon-title-length').html(coupon_title_length);
            }
        }
    });
}

function setCouponDesc(obj){
    var coupon_num = $(obj).parent().parent().attr("coupon_num");
    var coupon_desc = $(obj).val();
    var coupon_desc_length = coupon_desc.length;
    $(".effect-coupon").each(function(idx, item) {
        if($(item).hasClass("coupon"+coupon_num)){
            if(coupon_desc.length>9){
                coupon_desc = coupon_desc.substr(0,9);
            }
            $(item).find(".coupon-desc").html(coupon_desc);
        }
    });
    $(".attr-content-coupon").each(function(idx, item) {
        if($(item).hasClass("coupon_attr"+coupon_num)){
            if(coupon_desc_length > 9){
                $(item).find('.coupon-desc-length').html(9);
            }else{
                $(item).find('.coupon-desc-length').html(coupon_desc_length);
            }
        }
    });
}

function changeCouponPadding(obj){
    var coupon_num = $(obj).parent().parent().parent().attr("coupon_num");
    var coupon_vertical_padding = $(obj).val().split(',')[1];
    $(obj).parent().parent().parent().find('.coupon-vertical-padding').val(coupon_vertical_padding);
    $(obj).parent().find(".coupon-vertical-padding-value").text(coupon_vertical_padding);
    $(".effect-coupon").each(function(idx, item) {
        if($(item).hasClass("coupon"+coupon_num)){
            $(item).find(".coupons").css("padding-top",coupon_vertical_padding+"px");
            $(item).find(".coupons").css("padding-bottom",coupon_vertical_padding+"px");
        }
    });
}

function changeCouponCounts(obj){
    var coupon_num = $(obj).parent().parent().parent().attr("coupon_num");
    var coupon_counts = $(obj).val().split(',')[1];
    if(coupon_counts == 0){
        coupon_counts = 1;
    }
    $(obj).parent().parent().parent().find('.coupon-counts').val(coupon_counts);
    $(obj).parent().find(".coupon-counts-value").text(coupon_counts);
}

function changeCouponStyle(obj){
    var coupon_num = $(obj).parent().parent().attr("coupon_num");
    var couponHtml1 = $('.couponHtml1').html();
    var couponHtml2 = $('.couponHtml2').html();
    var coupon_style = $(obj).val();
    $(obj).parent().parent().find('.coupon-style').val(coupon_style);

    $(".effect-coupon").each(function(idx, item) {
        if($(item).hasClass("coupon"+coupon_num)){
            if(coupon_style == 1){
                $(item).find('.coupons').html(couponHtml1);
            }else{
                $(item).find('.coupons').html(couponHtml2);
            }
        }
    });
    $(".attr-content-coupon").each(function(idx, item) {
        if($(item).hasClass("coupon_attr"+coupon_num)){
            if(coupon_style == 1){
                $(item).find('.coupon-nums').html('3');
            }else{
                $(item).find('.coupon-nums').html('2');
            }
            // 清空已选优惠券
            //$(item).find('.coupon-select-ids-value').val('');
        }
    });
}

function addCoupons(obj){
    var coupon_style = $(obj).parent().parent().find('.coupon-style').val();
    var coupon_num = $(obj).parent().parent().attr('coupon_num');
    var coupon_select_ids_value = $(obj).parent().parent().find('.coupon-select-ids-value').val();
    initGridCoupon(1,coupon_select_ids_value);
    var box = WST.open({title:WST.lang('custompage_coupon_list')+"&nbsp;<span style='color:#ff0000;'>("+WST.lang('custompage_index_coupon_tips')+")</span>",type:1,content:$('#couponBox'),area: ['80%', '55%'],btn: [WST.lang('custompage_confirm'),WST.lang('custompage_cancel')],
        yes:function(){
            $(".attr-content-coupon").each(function(idx, item) {
                if($(item).attr('coupon_num')==coupon_num){
                    $(item).find('.coupon-select-ids-value').val(global_coupon_select_ids);
                }
            });
            layer.close(box);
        },cancel:function(){
            $('#couponBox').hide();
        },end:function(){
            $('#couponBox').hide();
        }});
}

function selectCouponIds(obj){
    var checked = $(obj).prop('checked');
    var coupon_id = $(obj).attr("coupon-id");
    if(checked){
        global_coupon_select_ids.push(coupon_id);
    }else{
        var index = global_coupon_select_ids.indexOf(coupon_id);
        if (index > -1) { // 将之前插入数组的coupon_id删除
            global_coupon_select_ids.splice(index, 1);
        }
    }
}
// 优惠券组件方法结束

// 单图组组件方法开始
function delImageBtn(obj){
    var image_num = $(obj).parent().attr("image_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".effect-image").each(function(idx, item) {
            if($(item).hasClass("image"+image_num)){
                $(item).parent().remove();
            }
        });
        $(".attr-content-image").each(function(idx, item) {
            if($(item).hasClass("image_attr"+image_num)){
                $(item).remove();
            }
        });
        layer.close(index);
    });
}

function addImageClassActive(obj){
    var image_num = $(obj).parent().find(".effect-image").attr("image_num");
    componentRemoveClasses(1);
    $(obj).addClass("effect-active");
    $(obj).addClass("select");

    $(".attr-title").html(WST.lang("custompage_component_image"));
    hideComponentAttrContent(1);
    $(".attr-content-image").each(function(idx, item) {
        if($(item).hasClass("image_attr"+image_num)){
            $(item).show();
        }else{
            $(item).hide();
        }
    });
}

function delImageItemBtn(obj){
    var that = $(obj);
    var image_item_counts = 0;
    var image_num = $(obj).parent().parent().attr("image_num");
    var image_item = $(obj).parent().find(".attr-content-image-img").attr("image_item");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".attr-content-image-item-img").each(function(idx, item) {
            image_item_counts += 1;
        });
        if(image_item_counts == 1){
            WST.msg(WST.lang('custompage_at_least_keep_one'),{time:1000,anim: 6});
        }else{
            $(".effect-image").each(function(idx, item) {
                if($(item).hasClass("image"+image_num)){
                    $(item).find(".images img").each(function(key, value) {
                        if($(value).attr("image_item") == image_item){
                            $(value).parent().remove();
                        }
                    });
                }
            });
            that.parent().remove();
            layer.close(index);
        }
    });
}

function addImageItemImg(){

}

function addImageItem(obj){
    var image_num = $(obj).parent().attr("image_num");
    var image_img_num = '';
    var image_class_name = "image"+image_num;
    var image_attr_class_name = "image_attr"+image_num;
    var image_padding_top =  parseInt($(obj).parent().find(".image-padding-top").val());
    var image_padding_bottom =  parseInt($(obj).parent().find(".image-padding-bottom").val());
    var image_padding_left =  parseInt($(obj).parent().find(".image-padding-left").val());
    var image_padding_right =  parseInt($(obj).parent().find(".image-padding-right").val());
    var image_item = 1;
    var style = "";
    var img_src = "";
    $(".effect-image").each(function(idx, item) {
        if($(item).hasClass("image"+image_num)){
            $(item).find(".images img").each(function(key, value) {
                image_item = parseInt($(value).attr('image_item'));
            });
            image_item += 1;
            if(image_padding_top > 0){
                style += "padding-top:"+image_padding_top+"px;";
            }
            if(image_padding_bottom > 0){
                style += "padding-bottom:"+image_padding_bottom+"px;";
            }
            if(image_padding_left > 0){
                style += "padding-left:"+image_padding_left+"px;";
            }
            if(image_padding_right > 0){
                style += "padding-right:"+image_padding_right+"px;";
            }
            $(item).find(".images").append("<div class='images-item' style='"+style+"' ><img src='"+base_url+"/upload/custompagedecoration/base/pc_image_01.jpg' alt='' image_item="+image_item+" /></div>");
        }
    });
    $(".attr-content-image").each(function(idx, item) {
        if($(item).hasClass(image_attr_class_name)){
            $(item).find(".attr-content-image-item-img").each(function(index, obj) {
                if($(obj).find(".image-picker")){
                    image_img_num = $(obj).find(".image-picker").attr('id');
                }
            });
        }
    });
    // 截取最后一个id属性值里的数字，例如（image-picker-2-1）
    image_img_num = parseInt(image_img_num.substr(parseInt(image_img_num.lastIndexOf("\-"))+1,image_img_num.length))+1;
    $(obj).before("<div class='attr-content-image-item-img' ><div class='attr-content-image-img' img_url='/upload/custompagedecoration/base/pc_image_01.jpg' image_item="+image_item+"><label class='d-label'>"+WST.lang('custompage_image')+"</label><div class='image-picker' id='image-picker-"+image_num+"-"+image_img_num+"' onclick='addImageItemImg(this)'><img src='"+base_url+"/upload/custompagedecoration/base/pc_image_01.jpg'/></div><div class='image-tips'>"+WST.lang('custompage_image_img_tips2')+"</div></div><label class='d-label'>"+WST.lang('custompage_link')+"</label><input type='text' value='' class='attr-content-image-link' /><div class='attr-btn-del' onclick='delImageItemBtn(this)'>X</div></div>");
    WST.upload({
        pick:'#image-picker-'+image_num+'-'+image_img_num,
        formData: {dir:'custompagedecoration'},
        accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
        callback:function(f){
            var json = WST.toAdminJson(f);
            if(json.status==1){
                $('#uploadMsg').empty().hide();
                var img_url = json.savePath+json.thumb; //保存到数据库的路径
                $("#image-picker-"+image_num+'-'+image_img_num).parent().attr('img_url',img_url);
                $("#image-picker-"+image_num+'-'+image_img_num).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                $(".attr-content-image").each(function(idx, item) {
                    if($(item).hasClass(image_attr_class_name)){
                        $(item).find(".attr-content-image-item-img").each(function(index, obj) {
                            if(parseInt($(obj).find('.attr-content-image-img').attr('image_item')) == image_item) {
                                // 保存image组件图片的路径，用来显示在中间的效果图
                                img_src = $(obj).find(".attr-content-image-img").attr("img_url");
                            }
                        });
                    }
                });
                $(".effect-image").each(function(idx,item){
                    if($(item).hasClass(image_class_name)){
                        $(item).find('img').each(function(idx,obj){
                            if(parseInt($(obj).attr('image_item')) == image_item){
                                $(obj).attr('src',WST.conf.RESOURCE_PATH+'/'+img_src);
                            }
                        });
                    }
                });
            }else{
                WST.msg(json.msg,{icon:2});
            }
        },
        progress:function(rate){
            $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
        }
    });
}

function changeImagePadding(obj,type){
    var image_num = $(obj).parent().parent().parent().attr("image_num");
    var image_margin = $(obj).val().split(',')[1];
    switch (type) {
        case 1:
            $(obj).parent().parent().parent().find('.image-padding-top').val(image_margin);
            $(obj).parent().find(".image-padding-top-value").text(image_margin);
            break;
        case 2:
            $(obj).parent().parent().parent().find('.image-padding-bottom').val(image_margin);
            $(obj).parent().find(".image-padding-bottom-value").text(image_margin);
            break;
        case 3:
            $(obj).parent().parent().parent().find('.image-padding-left').val(image_margin);
            $(obj).parent().find(".image-padding-left-value").text(image_margin);
            break;
        case 4:
            $(obj).parent().parent().parent().find('.image-padding-right').val(image_margin);
            $(obj).parent().find(".image-padding-right-value").text(image_margin);
            break;
    }
    $(".effect-image").each(function(idx, item) {
        if($(item).hasClass("image"+image_num)){
            $(item).find(".images .images-item").each(function(key, value) {
                switch (type) {
                    case 1:
                        $(value).css("padding-top",image_margin+"px");
                        break;
                    case 2:
                        $(value).css("padding-bottom",image_margin+"px");
                        break;
                    case 3:
                        $(value).css("padding-left",image_margin+"px");
                        break;
                    case 4:
                        $(value).css("padding-right",image_margin+"px");
                        break;
                }
            });
        }
    });
}
// 单图组组件方法结束

// 图片橱窗组件方法开始
function delShopwindowBtn(obj){
    var shopwindow_num = $(obj).parent().attr("shopwindow_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".effect-shopwindow").each(function(idx, item) {
            if($(item).hasClass("shopwindow"+shopwindow_num)){
                $(item).parent().remove();
            }
        });
        $(".attr-content-shopwindow").each(function(idx, item) {
            if($(item).hasClass("shopwindow_attr"+shopwindow_num)){
                $(item).remove();
            }
        });
        layer.close(index);
    });
}

function addShopwindowClassActive(obj){
    var shopwindow_num = $(obj).parent().find(".effect-shopwindow").attr("shopwindow_num");
    componentRemoveClasses(1);
    $(obj).addClass("effect-active");
    $(obj).addClass("select");

    $(".attr-title").html(WST.lang("custompage_component_shopwindow"));
    hideComponentAttrContent(1);
    $(".attr-content-shopwindow").each(function(idx, item) {
        if($(item).hasClass("shopwindow_attr"+shopwindow_num)){
            $(item).show();
        }else{
            $(item).hide();
        }
    });
}

function delShopwindowItemBtn(obj){
    var that = $(obj);
    var shopwindow_item_counts = 0;
    var shopwindow_num = $(obj).parent().parent().attr("shopwindow_num");
    var shopwindow_item = $(obj).parent().find(".attr-content-shopwindow-img").attr("shopwindow_item");
    var shopwindow_layout = parseInt($(obj).parent().parent().find(".shopwindow-layout-value").val());
    var shopwindow_layout_count = 0;
    var shopwindow_layout_array = [];
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".attr-content-shopwindow-item-img").each(function(idx, item) {
            shopwindow_item_counts += 1;
        });
        if(shopwindow_item_counts == 1){
            WST.msg(WST.lang('custompage_at_least_keep_one'),{time:1000,anim: 6});
        }else{
            $(".effect-shopwindow").each(function(idx, item) {
                if($(item).hasClass("shopwindow"+shopwindow_num)){
                    $(item).find(".shopwindows img").each(function(key, value) {
                        if($(value).attr("shopwindow_item") == shopwindow_item){
                            $(value).parent().remove();
                        }
                    });
                    if(shopwindow_layout == 4){
                        $(item).find(".shopwindows-layout img").each(function(key, value) {
                            $(value).remove();
                        });
                        $(item).find(".shopwindows .shopwindows-item img").each(function(key, value) {
                            $(item).find(".shopwindows-s-hide").append("<div class='shopwindows-item'><img src='" + $(value).attr('src') + "' alt='' shopwindow_item=" + $(value).attr('shopwindow_item') + " /></div>");
                        });
                        $(item).find(".shopwindows-s-hide .shopwindows-item img").each(function(key, value) {
                            shopwindow_layout_array.push($(value));
                            shopwindow_layout_count += 1;
                            $(value).parent().remove();
                        });
                        if(shopwindow_layout_count == 2){
                            $(item).find(".s-layout-top").css("height","100%");
                        }else{
                            $(item).find(".s-layout-top").css("height","50%");
                        }
                        for(var i=0;i<shopwindow_layout_array.length;i++){
                            if(i==0){
                                $(item).find(".s-layout-left-item").append(shopwindow_layout_array[i]);
                            }else if(i == 1){
                                $(item).find(".s-layout-top-item").append(shopwindow_layout_array[i]);
                            }else if(i == 2){
                                $(item).find(".s-layout-bottom-item:nth-child(1)").append(shopwindow_layout_array[i]);
                            }else if(i == 3){
                                $(item).find(".s-layout-bottom-item:nth-child(2)").append(shopwindow_layout_array[i]);
                            }
                        }
                    }
                }
            });
            that.parent().remove();
            layer.close(index);
        }
    });
}

function addShopwindowItemImg(){

}

function addShopwindowItem(obj){
    var shopwindow_num = $(obj).parent().attr("shopwindow_num");
    var shopwindow_img_num = '';
    var shopwindow_class_name = "shopwindow"+shopwindow_num;
    var shopwindow_attr_class_name = "shopwindow_attr"+shopwindow_num;
    var shopwindow_layout = parseInt($(obj).parent().find(".shopwindow-layout-value").val());
    var shopwindow_item = 1;
    var shopwindow_layout_count = 0;
    var shopwindow_layout_array = [];
    var style = "";
    $(".attr-content-shopwindow").each(function(idx, item) {
        if($(item).hasClass(shopwindow_attr_class_name)){
            $(item).find(".attr-content-shopwindow-item-img").each(function(index, obj) {
                if($(obj).find(".shopwindow-picker")){
                    shopwindow_img_num = $(obj).find(".shopwindow-picker").attr('id');
                }
            });
        }
    });
    // 截取最后一个id属性值里的数字，例如（image-picker-2-1）
    shopwindow_img_num = parseInt(shopwindow_img_num.substr(parseInt(shopwindow_img_num.lastIndexOf("\-"))+1,shopwindow_img_num.length))+1;
    $(".effect-shopwindow").each(function(idx, item) {
        if($(item).hasClass("shopwindow"+shopwindow_num)){
            if(shopwindow_layout == 1){
                style += "width:50%;";
            }else if(shopwindow_layout == 2){
                style += "width:33%;";
            }else if(shopwindow_layout == 3){
                style += "width:25%;";
            }
            $(item).find(".shopwindows img").each(function(key, value) {
                shopwindow_item += 1;
            });
            $(item).find(".shopwindows").append("<div class='shopwindows-item' style='"+style+"' ><img src='"+base_url+"/upload/custompagedecoration/base/shopwindow_01.jpg' alt='' shopwindow_item="+shopwindow_item+" /></div>");
            if(shopwindow_layout == 4){
                $(item).find(".shopwindows-layout img").each(function(key, value) {
                    $(value).remove();
                });
                $(item).find(".shopwindows .shopwindows-item img").each(function(key, value) {
                    $(item).find(".shopwindows-s-hide").append("<div class='shopwindows-item'><img src='"+$(value).attr('src')+"' alt='' shopwindow_item="+$(value).attr('shopwindow_item')+" /></div>");
                });
                $(item).find(".shopwindows-s-hide .shopwindows-item img").each(function(key, value) {
                    shopwindow_layout_array.push($(value));
                    shopwindow_layout_count += 1;
                    $(value).parent().remove();
                });
                if(shopwindow_layout_count == 2){
                    $(item).find(".s-layout-top").css("height","100%");
                }else{
                    $(item).find(".s-layout-top").css("height","50%");
                }
                for(var i=0;i<shopwindow_layout_array.length;i++){
                    if(i==0){
                        $(item).find(".s-layout-left-item").append(shopwindow_layout_array[i]);
                    }else if(i == 1){
                        $(item).find(".s-layout-top-item").append(shopwindow_layout_array[i]);
                    }else if(i == 2){
                        $(item).find(".s-layout-bottom-item:nth-child(1)").append(shopwindow_layout_array[i]);
                    }else if(i == 3){
                        $(item).find(".s-layout-bottom-item:nth-child(2)").append(shopwindow_layout_array[i]);
                    }
                }
            }
        }
    });
    $(obj).before("<div class='attr-content-shopwindow-item-img' ><div class='attr-content-shopwindow-img' img_url='"+base_url+"/upload/custompagedecoration/base/shopwindow_01.jpg' shopwindow_item="+shopwindow_item+"><label class='d-label'>"+WST.lang('custompage_image')+"</label><div class='shopwindow-picker' id='shopwindow-picker-"+shopwindow_num+"-"+shopwindow_img_num+"' onclick='addShopwindowItemImg(this)'><img src='"+base_url+"/upload/custompagedecoration/base/shopwindow_01.jpg'/></div></div><label class='d-label'>"+WST.lang('custompage_link')+"</label><input type='text' value='' class='attr-content-shopwindow-link' /><div class='attr-btn-del' onclick='delShopwindowItemBtn(this)'>X</div></div>");
    WST.upload({
        pick:'#shopwindow-picker-'+shopwindow_num+'-'+shopwindow_img_num,
        formData: {dir:'custompagedecoration'},
        accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
        callback:function(f){
            var json = WST.toAdminJson(f);
            if(json.status==1){
                $('#uploadMsg').empty().hide();
                var img_url = json.savePath+json.thumb; //保存到数据库的路径
                $("#shopwindow-picker-"+shopwindow_num+'-'+shopwindow_img_num).parent().attr('img_url',img_url);
                $("#shopwindow-picker-"+shopwindow_num+'-'+shopwindow_img_num).find('img').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb);
                $(".attr-content-shopwindow").each(function(idx, item) {
                    if($(item).hasClass(shopwindow_attr_class_name)){
                        $(item).find(".attr-content-shopwindow-item-img").each(function(index, obj) {
                            if(parseInt($(obj).find('.attr-content-shopwindow-img').attr('shopwindow_item')) == shopwindow_item) {
                                // 保存shopwindow组件图片的路径，用来显示在中间的效果图
                                img_src = $(obj).find(".attr-content-shopwindow-img").attr("img_url");
                            }
                        });
                    }
                });
                $(".effect-shopwindow").each(function(idx,item){
                    if($(item).hasClass(shopwindow_class_name)){
                        $(item).find('img').each(function(idx,obj){
                            if(parseInt($(obj).attr('shopwindow_item')) == shopwindow_item){
                                $(obj).attr('src',WST.conf.RESOURCE_PATH+'/'+img_src);
                            }
                        });
                    }
                });
            }else{
                WST.msg(json.msg,{icon:2});
            }
        },
        progress:function(rate){
            $('#uploadMsg').show().html(WST.lang('custompage_has_upload')+rate+"%");
        }
    });
}

function changeShopwindowLayout(obj){
    var shopwindow_num = $(obj).parent().parent().parent().attr("shopwindow_num");
    var shopwindow_layout = $(obj).val();
    var shopwindow_layout_info1 = WST.lang("custompage_shopwindow_image_tips1");
    var shopwindow_layout_info2 = WST.lang("custompage_shopwindow_image_tips2");
    var shopwindow_layout_count = 0;
    var shopwindow_layout_array = [];
    var flag = false;
    $(obj).parent().parent().parent().find(".shopwindow-layout-value").val(shopwindow_layout);
    $(".effect-shopwindow").each(function(idx, item) {
        if($(item).hasClass("shopwindow"+shopwindow_num)){
            if(shopwindow_layout == 1){
                // 堆积两列
                $(item).find(".shopwindows").show();
                $(item).find(".shopwindows-layout").hide();
                $(item).find(".shopwindows-item").css("width","50%");
                $(obj).parent().parent().parent().find(".shopwindow-layout-info").text(shopwindow_layout_info1);
            }else if(shopwindow_layout == 2){
                // 堆积三列
                $(item).find(".shopwindows").show();
                $(item).find(".shopwindows-layout").hide();
                $(item).find(".shopwindows-item").css("width","33%");
                $(obj).parent().parent().parent().find(".shopwindow-layout-info").text(shopwindow_layout_info1);
            }else if(shopwindow_layout == 3){
                // 堆积四列
                $(item).find(".shopwindows").show();
                $(item).find(".shopwindows-layout").hide();
                $(item).find(".shopwindows-item").css("width","25%");
                $(obj).parent().parent().parent().find(".shopwindow-layout-info").text(shopwindow_layout_info1);
            }else{
                // 橱窗样式
                $(item).find(".shopwindows-layout img").each(function(key, value) {
                    $(value).remove();
                });
                $(item).find(".shopwindows .shopwindows-item img").each(function(key, value) {
                    $(item).find(".shopwindows-s-hide").append("<div class='shopwindows-item'><img src='"+$(value).attr('src')+"' alt='' shopwindow_item="+$(value).attr('shopwindow_item')+" /></div>");
                });
                $(item).find(".shopwindows-s-hide .shopwindows-item img").each(function(key, value) {
                    shopwindow_layout_array.push($(value));
                    shopwindow_layout_count += 1;
                    $(value).parent().remove();
                });
                for(var i=0;i<shopwindow_layout_array.length;i++){
                    if(i==0){
                        $(item).find(".s-layout-left-item").append(shopwindow_layout_array[i]);
                    }else if(i == 1){
                        $(item).find(".s-layout-top-item").append(shopwindow_layout_array[i]);
                    }else if(i == 2){
                        $(item).find(".s-layout-bottom-item:nth-child(1)").append(shopwindow_layout_array[i]);
                    }else if(i == 3){
                        $(item).find(".s-layout-bottom-item:nth-child(2)").append(shopwindow_layout_array[i]);
                    }
                }
                $(item).find(".shopwindows").hide();
                $(item).find(".shopwindows-layout").show();
                $(obj).parent().parent().parent().find(".shopwindow-layout-info").text(shopwindow_layout_info2);
            }
        }
    });
}
// 图片橱窗组件方法结束

// 视频组件方法开始
function delVideoBtn(obj){
    var video_num = $(obj).parent().attr("video_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".effect-video").each(function(idx, item) {
            if($(item).hasClass("video"+video_num)){
                $(item).parent().remove();
            }
        });
        $(".attr-content-video").each(function(idx, item) {
            if($(item).hasClass("video_attr"+video_num)){
                $(item).remove();
            }
        });
        layer.close(index);
    });
}

function addVideoClassActive(obj){
    var video_num = $(obj).parent().find(".effect-video").attr("video_num");
    componentRemoveClasses(1);
    $(obj).addClass("effect-active");
    $(obj).addClass("select");

    $(".attr-title").html(WST.lang("custompage_component_video"));
    hideComponentAttrContent(1);
    $(".attr-content-video").each(function(idx, item) {
        if($(item).hasClass("video_attr"+video_num)){
            $(item).show();
        }else{
            $(item).hide();
        }
    });
}

function addVideoItemImg(obj){

}

function changeVideoMargin(obj){
    var video_num = $(obj).parent().parent().parent().attr("video_num");
    var video_margin = $(obj).val().split(',')[1];
    $(obj).parent().parent().parent().find('.video-vertical-padding').val(video_margin);
    $(obj).parent().find(".video-vertical-padding-value").text(video_margin);
    $(".effect-video").each(function(idx, item) {
        if($(item).hasClass("video"+video_num)){
            $(item).css("margin-top",video_margin+"px");
            $(item).css("margin-bottom",video_margin+"px");
        }
    });
}
// 视频组件方法结束

// 辅助空白组件方法开始
function delBlankBtn(obj){
    var blank_num = $(obj).parent().attr("blank_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".effect-blank").each(function(idx, item) {
            if($(item).hasClass("blank"+blank_num)){
                $(item).parent().remove();
            }
        });
        $(".attr-content-blank").each(function(idx, item) {
            if($(item).hasClass("blank_attr"+blank_num)){
                $(item).remove();
            }
        });
        layer.close(index);
    });
}

function addBlankClassActive(obj){
    var blank_num = $(obj).parent().find(".effect-blank").attr("blank_num");
    componentRemoveClasses(1);
    $(obj).addClass("effect-active");
    $(obj).addClass("select");

    $(".attr-title").html(WST.lang("custompage_component_blank"));
    hideComponentAttrContent(1);
    $(".attr-content-blank").each(function(idx, item) {
        if($(item).hasClass("blank_attr"+blank_num)){
            $(item).show();
        }else{
            $(item).hide();
        }
    });
}

function changeBlankHeight(obj){
    var blank_num = $(obj).parent().parent().parent().attr("blank_num");
    var blank_height = $(obj).val().split(',')[1];
    if(blank_height == 0){
        $(obj).parent().parent().parent().find('.blank-height').val(1);
        $(obj).parent().find(".blank-height-value").text(1);
    }else{
        $(obj).parent().parent().parent().find('.blank-height').val(blank_height);
        $(obj).parent().find(".blank-height-value").text(blank_height);
    }
    $(".effect-blank").each(function(idx, item) {
        if($(item).hasClass("blank"+blank_num)){
            if(blank_height == 0){
                $(item).css("height","1px");
            }else{
                $(item).css("height",blank_height+"px");
            }
            if(blank_height <= 16){
                $(item).find(".btn-del").css("height",blank_height+"px");
            }else{
                $(item).find(".btn-del").css("height","16px");
            }
        }
    });
}
// 辅助空白组件方法结束

// 辅助线组件方法开始
function delLineBtn(obj){
    var line_num = $(obj).parent().attr("line_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".effect-line").each(function(idx, item) {
            if($(item).hasClass("line"+line_num)){
                $(item).parent().remove();
            }
        });
        $(".attr-content-line").each(function(idx, item) {
            if($(item).hasClass("line_attr"+line_num)){
                $(item).remove();
            }
        });
        layer.close(index);
    });
}

function addLineClassActive(obj){
    var line_num = $(obj).parent().find(".effect-line").attr("line_num");
    componentRemoveClasses(1);
    $(obj).addClass("effect-active");
    $(obj).addClass("select");

    $(".attr-title").html(WST.lang("custompage_component_line"));
    hideComponentAttrContent(1);
    $(".attr-content-line").each(function(idx, item) {
        if($(item).hasClass("line_attr"+line_num)){
            $(item).show();
        }else{
            $(item).hide();
        }
    });
}

function changeLineHeight(obj){
    var line_num = $(obj).parent().parent().parent().attr("line_num");
    var line_height = $(obj).val().split(',')[1];
    if(line_height == 0){
        $(obj).parent().parent().parent().find('.line-height').val(1);
        $(obj).parent().find(".line-height-value").text(1);
    }else{
        $(obj).parent().parent().parent().find('.line-height').val(line_height);
        $(obj).parent().find(".line-height-value").text(line_height);
    }
    $(".effect-line").each(function(idx, item) {
        if($(item).hasClass("line"+line_num)){
            if(line_height == 0){
                $(item).find(".line").css("border-width","1px");
            }else{
                $(item).find(".line").css("border-width",line_height+"px");
            }
        }
    });
}

function changeLineMargin(obj){
    var line_num = $(obj).parent().parent().parent().attr("line_num");
    var line_margin = $(obj).val().split(',')[1];
    $(obj).parent().parent().parent().find('.line-vertical-margin').val(line_margin);
    $(obj).parent().find(".line-vertical-margin-value").text(line_margin);
    $(".effect-line").each(function(idx, item) {
        if($(item).hasClass("line"+line_num)){
            $(item).find(".line").css("margin-top",line_margin+"px");
            $(item).find(".line").css("margin-bottom",line_margin+"px");
            var line_component_height = parseInt($(item).find(".line").css("margin-top")) + parseInt($(item).find(".line").css("margin-bottom"));
            if(line_component_height <= 16){
                $(item).find(".btn-del").css("height",line_margin+"px");
            }else{
                $(item).find(".btn-del").css("height","16px");
            }
        }
    });
}

function changeLineClass(obj){
    var line_num = $(obj).parent().parent().parent().attr("line_num");
    var line_class = $(obj).val();
    $(obj).parent().parent().parent().find(".line-class").val(line_class);
    $(".effect-line").each(function(idx, item) {
        if($(item).hasClass("line"+line_num)){
            if(line_class == 1){
                // 实线
                $(item).find(".line").css("border-top-style","solid");
            }else if(line_class == 2){
                // 虚线
                $(item).find(".line").css("border-top-style","dashed");
            }else{
                // 点状
                $(item).find(".line").css("border-top-style","dotted");
            }
        }
    });
}
// 辅助线组件方法结束

// 富文本组件方法开始
function delTextBtn(obj){
    var text_num = $(obj).parent().attr("text_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".effect-text").each(function(idx, item) {
            if($(item).hasClass("text"+text_num)){
                $(item).parent().remove();
            }
        });
        $(".attr-content-text").each(function(idx, item) {
            if($(item).hasClass("text_attr"+text_num)){
                $(item).remove();
            }
        });
        layer.close(index);
    });
}

function addTextClassActive(obj){
    var text_num = $(obj).parent().find(".effect-text").attr("text_num");
    componentRemoveClasses(1);
    $(obj).addClass("effect-active");
    $(obj).addClass("select");

    $(".attr-title").html(WST.lang("custompage_component_text"));
    hideComponentAttrContent(1);
    $(".attr-content-text").each(function(idx, item) {
        if($(item).hasClass("text_attr"+text_num)){
            $(item).show();
        }else{
            $(item).hide();
        }
    });
}

function changeTextPadding(obj,type){
    var text_num = $(obj).parent().parent().parent().attr("text_num");
    var text_padding = $(obj).val().split(',')[1];
    if(type == 1){
        $(obj).parent().parent().parent().find('.text-vertical-padding').val(text_padding);
        $(obj).parent().find(".text-vertical-padding-value").text(text_padding);
    }else{
        $(obj).parent().parent().parent().find('.text-horizontal-padding').val(text_padding);
        $(obj).parent().find(".text-horizontal-padding-value").text(text_padding);
    }
    $(".effect-text").each(function(idx, item) {
        if($(item).hasClass("text"+text_num)){
            if(type == 1){
                $(item).find(".text").css("padding-top",text_padding+"px");
                $(item).find(".text").css("padding-bottom",text_padding+"px");
            }else{
                $(item).find(".text").css("padding-left",text_padding+"px");
                $(item).find(".text").css("padding-right",text_padding+"px");
            }
        }
    });
}
// 富文本组件方法结束

// 单文本组件方法开始
function addTxtClassActive(obj){
    var txt_num = $(obj).parent().find(".effect-txt").attr("txt_num");
    componentRemoveClasses(1);
    $(obj).addClass("effect-active");
    $(obj).addClass("select");

    $(".attr-title").html(WST.lang("custompage_component_txt"));
    hideComponentAttrContent(1);
    $(".attr-content-txt").each(function(idx, item) {
        if($(item).hasClass("txt_attr"+txt_num)){
            $(item).show();
        }else{
            $(item).hide();
        }
    });
}

function delTxtBtn(obj){
    var txt_num = $(obj).parent().attr("txt_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".effect-txt").each(function(idx, item) {
            if($(item).hasClass("txt"+txt_num)){
                $(item).parent().remove();
            }
        });
        $(".attr-content-txt").each(function(idx, item) {
            if($(item).hasClass("txt_attr"+txt_num)){
                $(item).remove();
            }
        });
        layer.close(index);
    });
}
function setTxtText(obj){
    var txt_num = $(obj).parent().parent().attr("txt_num");
    var txt_text = $(obj).val();
    var txt_text_length = txt_text.length;
    $(".effect-txt").each(function(idx, item) {
        if($(item).hasClass("txt"+txt_num)){
            if(txt_text.length>15){
                txt_text = txt_text.substr(0,15)+'...';
            }
            $(item).find(".txt-info p").html(txt_text);
        }
    });
    $(".attr-content-txt").each(function(idx, item) {
        if($(item).hasClass("txt_attr"+txt_num)){
            if(txt_text_length > 50){
                $(item).find('.txt-text-length').html(50);
            }else{
                $(item).find('.txt-text-length').html(txt_text_length);
            }
        }
    });
}

function changeTxtTextAlignment(obj){
    var txt_num = $(obj).parent().parent().attr("txt_num");
    var txt_text_alignment = $(obj).val();
    $(obj).parent().parent().parent().find(".attr-content-txt-text-alignment").val(txt_text_alignment);
    $(".effect-txt").each(function(idx, item) {
        if($(item).hasClass("txt"+txt_num)){
            if(txt_text_alignment == 1){
                // 居左
                $(item).find(".txt-info").css("justify-content","flex-start");
            }else if(txt_text_alignment == 2){
                // 居中
                $(item).find(".txt-info").css("justify-content","center");
            }else{
                // 居右
                $(item).find(".txt-info").css("justify-content","flex-end");
            }
        }
    });
}

function changeTxtPadding(obj,type){
    var txt_num = $(obj).parent().parent().parent().attr("txt_num");
    var txt_padding = $(obj).val().split(',')[1];
    if(type == 1){
        $(obj).parent().parent().parent().find('.txt-vertical-padding').val(txt_padding);
        $(obj).parent().find(".txt-vertical-padding-value").text(txt_padding);
    }else{
        $(obj).parent().parent().parent().find('.txt-horizontal-padding').val(txt_padding);
        $(obj).parent().find(".txt-horizontal-padding-value").text(txt_padding);
    }
    $(".effect-txt").each(function(idx, item) {
        if($(item).hasClass("txt"+txt_num)){
            if(type == 1){
                $(item).find(".txt-info").css("padding-top",txt_padding+"px");
                $(item).find(".txt-info").css("padding-bottom",txt_padding+"px");
            }else{
                $(item).find(".txt-info").css("padding-left",txt_padding+"px");
                $(item).find(".txt-info").css("padding-right",txt_padding+"px");
            }
        }
    });
}
// 单文本组件方法结束

// 图文列表组件方法开始
function addImageTextClassActive(obj){
    var image_text_num = $(obj).parent().find(".effect-image-text").attr("image_text_num");
    componentRemoveClasses(1);
    $(obj).addClass("effect-active");
    $(obj).addClass("select");

    $(".attr-title").html(WST.lang("custompage_component_image_text"));
    hideComponentAttrContent(1);
    $(".attr-content-image-text").each(function(idx, item) {
        if($(item).hasClass("image_text_attr"+image_text_num)){
            $(item).show();
        }else{
            $(item).hide();
        }
    });
}

function delImageTextBtn(obj){
    var image_text_num = $(obj).parent().attr("image_text_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".effect-image-text").each(function(idx, item) {
            if($(item).hasClass("image_text"+image_text_num)){
                $(item).parent().remove();
            }
        });
        $(".attr-content-image-text").each(function(idx, item) {
            if($(item).hasClass("image_text_attr"+image_text_num)){
                $(item).remove();
            }
        });
        layer.close(index);
    });
}

function changeImageTextStyle(obj){
    var image_text_num = $(obj).parent().parent().attr("image_text_num");
    var image_text_item = $(obj).attr("image_text_item");
    var image_text_img_count = 0;
    var image_text_class_name = "image_text"+image_text_num;
    var image_text_attr_class_name = "image_text_attr"+image_text_num;
    var image_text_title = $(obj).parent().parent().find('.attr-content-image-text-title').val();
    $(obj).parent().parent().find('.image-text-style').val(image_text_item);
    $(".attr-content-image-text").each(function(idx, item) {
        if($(item).hasClass(image_text_attr_class_name)){
            $(item).find('.image-text-style-item ').each(function(key, value) {
                $(value).removeClass('active');
            });
            $(item).find(".attr-content-image-text-img").each(function(index, obj) {
                if($(obj).find(".image-text-picker")){
                    image_text_img_count += 1;
                }
                switch(image_text_item){
                    case '1':
                        $(obj).find('.image-tips').html(WST.lang('custompage_image_text_img_tips1'));
                        break;
                    case '2':
                        $(obj).find('.image-tips').html(WST.lang('custompage_image_text_img_tips1'));
                        break;
                    case '3':
                        $(obj).find('.image-tips').html(WST.lang('custompage_image_text_img_tips2'));
                        break;
                    case '4':
                        $(obj).find('.image-tips').html(WST.lang('custompage_image_text_img_tips3'));
                        break;
                    case '5':
                        $(obj).find('.image-tips').html(WST.lang('custompage_image_text_img_tips1'));
                        break;
                }
            });

        }
        $(obj).addClass('active');
    });
    $(".attr-content-image-text").each(function(idx, item) {
        if($(item).hasClass(image_text_attr_class_name)){
            switch(image_text_item){
                case '1':
                case '2':
                case '3':
                    $(item).find('.image-text-img').each(function(key,value){
                       if(key==0){
                           $(value).removeClass('none');
                       }else{
                           $(value).addClass('none');
                       }
                    });
                    break;
                case '4':
                    $(item).find('.image-text-img').each(function(key,value){
                        if(key==2){
                            $(value).addClass('none');
                        }else{
                            $(value).removeClass('none');
                        }
                    });
                    break;
                case '5':
                    $(item).find('.image-text-img').each(function(key,value){
                        $(value).removeClass('none');
                    });
                    break;
            }
        }
    });
    $(".effect-image-text").each(function(idx, item) {
        if($(item).hasClass("image_text"+image_text_num)){
            if(image_text_item == 1 || image_text_item == 2){
                if(image_text_title.length>12){
                    image_text_title = image_text_title.substr(0,12)+'...';
                }
            }else{
                if(image_text_title.length>20){
                    image_text_title = image_text_title.substr(0,20);
                }
            }
            $(item).find(".image-text-style-title").html(image_text_title);
            $(item).find('.image-text').each(function(key, value){
                if($(value).attr("image_text_item") == image_text_item){
                    if(key == 0 || key == 1){
                        $(value).removeClass('none2');
                    }else{
                        $(value).removeClass('none');
                    }
                }else{
                    if(key == 0 || key == 1){
                        $(value).addClass('none2');
                    }else{
                        $(value).addClass('none');
                    }
                }
            });
        }
    });
}

function setImageTextTitle(obj){
    var image_text_num = $(obj).parent().parent().parent().attr("image_text_num");
    var image_text_title = $(obj).val();
    var image_text_title_length = image_text_title.length;
    var image_text_style = $(obj).parent().parent().parent().find('.image-text-style').val();
    $(".effect-image-text").each(function(idx, item) {
        if($(item).hasClass("image_text"+image_text_num)){
            if(image_text_style==1||image_text_style==2){
                if(image_text_title.length>12){
                    image_text_title = image_text_title.substr(0,12)+'...';
                }
            }else{
                if(image_text_title.length>20){
                    image_text_title = image_text_title.substr(0,20);
                }
            }
            $(item).find(".image-text-style-title").html(image_text_title);
        }
    });
    $(".attr-content-image-text").each(function(idx, item) {
        if($(item).hasClass("image_text_attr"+image_text_num)){
            if(image_text_title_length > 20){
                $(item).find('.image-text-title-length').html(20);
            }else{
                $(item).find('.image-text-title-length').html(image_text_title_length);
            }
        }
    });
}

function setImageTextDesc(obj){
    var image_text_num = $(obj).parent().parent().parent().attr("image_text_num");
    var image_text_desc = $(obj).val();
    var image_text_desc_length = image_text_desc.length;
    $(".effect-image-text").each(function(idx, item) {
        if($(item).hasClass("image_text"+image_text_num)){
            if(image_text_desc.length>50){
                image_text_desc = image_text_desc.substr(0,50)+'...';
            }
            $(item).find(".image-text-style-desc").html(image_text_desc);
        }
    });
    $(".attr-content-image-text").each(function(idx, item) {
        if($(item).hasClass("image_text_attr"+image_text_num)){
            if(image_text_desc_length > 200){
                $(item).find('.image-text-desc-length').html(200);
            }else{
                $(item).find('.image-text-desc-length').html(image_text_desc_length);
            }
        }
    });
}

function changeImageTextPadding(obj){
    var image_text_num = $(obj).parent().parent().parent().attr("image_text_num");
    var image_text_vertical_padding = $(obj).val().split(',')[1];
    $(obj).parent().parent().parent().find('.image-text-vertical-padding').val(image_text_vertical_padding);
    $(obj).parent().find(".image-text-vertical-padding-value").text(image_text_vertical_padding);
    $(".effect-image-text").each(function(idx, item) {
        if($(item).hasClass("image_text"+image_text_num)){
            $(item).find(".image-text").css("padding-top",image_text_vertical_padding+"px");
            $(item).find(".image-text").css("padding-bottom",image_text_vertical_padding+"px");
        }
    });
}
// 图文列表组件方法结束

// 多店铺组件方法开始
function addShopClassActive(obj){
    var shop_num = $(obj).parent().find(".effect-shop").attr("shop_num");
    componentRemoveClasses(1);
    $(obj).addClass("effect-active");
    $(obj).addClass("select");

    $(".attr-title").html(WST.lang("custompage_component_shop"));
    hideComponentAttrContent(1);
    $(".attr-content-shop").each(function(idx, item) {
        if($(item).hasClass("shop_attr"+shop_num)){
            $(item).show();
        }else{
            $(item).hide();
        }
    });
}

function delShopBtn(obj){
    var shop_num = $(obj).parent().attr("shop_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".effect-shop").each(function(idx, item) {
            if($(item).hasClass("shop"+shop_num)){
                $(item).parent().remove();
            }
        });
        $(".attr-content-shop").each(function(idx, item) {
            if($(item).hasClass("shop_attr"+shop_num)){
                $(item).remove();
            }
        });
        layer.close(index);
    });
}

function setShopTitle(obj){
    var shop_num = $(obj).parent().parent().attr("shop_num");
    var shop_title = $(obj).val();
    var shop_title_length = shop_title.length;
    $(".effect-shop").each(function(idx, item) {
        if($(item).hasClass("shop"+shop_num)){
            if(shop_title.length>10){
                shop_title = shop_title.substr(0,10);
            }
            $(item).find('.shop-title-length').html(shop_title.length);
            $(item).find(".shop-title-text").html(shop_title);
        }
    });
    $(".attr-content-shop").each(function(idx, item) {
        if($(item).hasClass("shop_attr"+shop_num)){
            if(shop_title_length > 10){
                $(item).find('.shop-title-length').html(10);
            }else{
                $(item).find('.shop-title-length').html(shop_title_length);
            }
        }
    });
}
// 多店铺组件方法结束

// 新闻组件方法开始
function addNewClassActive(obj){
    var new_num = $(obj).parent().find(".effect-new").attr("new_num");
    componentRemoveClasses(1);
    $(obj).addClass("effect-active");
    $(obj).addClass("select");

    $(".attr-title").html(WST.lang("custompage_component_new"));
    hideComponentAttrContent(1);
    $(".attr-content-new").each(function(idx, item) {
        if($(item).hasClass("new_attr"+new_num)){
            $(item).show();
        }else{
            $(item).hide();
        }
    });
}

function delNewBtn(obj){
    var new_num = $(obj).parent().attr("new_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".effect-new").each(function(idx, item) {
            if($(item).hasClass("new"+new_num)){
                $(item).parent().remove();
            }
        });
        $(".attr-content-new").each(function(idx, item) {
            if($(item).hasClass("new_attr"+new_num)){
                $(item).remove();
            }
        });
        layer.close(index);
    });
}

function setNewTitle(obj){
    var new_num = $(obj).parent().parent().attr("new_num");
    var new_title = $(obj).val();
    var new_title_length = new_title.length;
    $(".effect-new").each(function(idx, item) {
        if($(item).hasClass("new"+new_num)){
            if(new_title.length>8){
                new_title = new_title.substr(0,8);
            }
            $(item).find('.new-title-length').html(new_title.length);
            $(item).find(".new-title-text").html(new_title);
        }
    });
    $(".attr-content-new").each(function(idx, item) {
        if($(item).hasClass("new_attr"+new_num)){
            if(new_title_length > 8){
                $(item).find('.new-title-length').html(8);
            }else{
                $(item).find('.new-title-length').html(new_title_length);
            }
        }
    });
}

function changeNewCount(obj){
    var count = $(obj).val();
    $(obj).parent().parent().find(".new-count").val(count);
}

function addNews(obj){
    var new_num = $(obj).parent().parent().attr('new_num');
    var new_select_ids_value = $(obj).parent().parent().find('.new-select-ids-value').val();
    initGridNew(1,new_select_ids_value);
    var box = WST.open({title:WST.lang("custompage_select_new"),type:1,content:$('#newBox'),area: ['80%', '55%'],btn: [WST.lang('custompage_confirm'),WST.lang('custompage_cancel')],
        yes:function(){
            $(".attr-content-new").each(function(idx, item) {
                if($(item).attr('new_num')==new_num){
                    $(item).find('.new-select-ids-value').val(global_new_select_ids);
                }
            });
            layer.close(box);
        },cancel:function(){
            $('#newBox').hide();
        },end:function(){
            $('#newBox').hide();
        }});
}

function selectNewIds(obj){
    var checked = $(obj).prop('checked');
    var new_id = $(obj).attr("new-id");
    if(checked){
        global_new_select_ids.push(new_id);
    }else{
        var index = global_new_select_ids.indexOf(new_id);
        if (index > -1) { // 将之前插入数组的new_id删除
            global_new_select_ids.splice(index, 1);
        }
    }
}
// 新闻组件方法结束

// 营销活动组件方法开始
function addMarketingClassActive(obj){
    var marketing_num = $(obj).parent().find(".effect-marketing").attr("marketing_num");
    componentRemoveClasses(1);
    $(obj).addClass("effect-active");
    $(obj).addClass("select");

    $(".attr-title").html(WST.lang("custompage_component_marketing"));
    hideComponentAttrContent(1);
    $(".attr-content-marketing").each(function(idx, item) {
        if($(item).hasClass("marketing_attr"+marketing_num)){
            $(item).show();
        }else{
            $(item).hide();
        }
    });
}

function delMarketingBtn(obj){
    var marketing_num = $(obj).parent().attr("marketing_num");
    layer.confirm(WST.lang('custompage_confirm_del'), {icon: 3, title:WST.lang('custompage_info')}, function(index){
        $(".effect-marketing").each(function(idx, item) {
            if($(item).hasClass("marketing"+marketing_num)){
                $(item).parent().remove();
            }
        });
        $(".attr-content-marketing").each(function(idx, item) {
            if($(item).hasClass("marketing_attr"+marketing_num)){
                $(item).remove();
            }
        });
        layer.close(index);
    });
}

function setMarketingTitle(obj){
    var marketing_num = $(obj).parent().parent().attr("marketing_num");
    var marketing_title = $(obj).val();
    var marketing_title_length = marketing_title.length;
    $(".effect-marketing").each(function(idx, item) {
        if($(item).hasClass("marketing"+marketing_num)){
            if(marketing_title.length>10){
                marketing_title = marketing_title.substr(0,10);
            }
            $(item).find(".marketing-title-text").html(marketing_title);
        }
    });
    $(".attr-content-marketing").each(function(idx, item) {
        if($(item).hasClass("marketing_attr"+marketing_num)){
            if(marketing_title_length > 10){
                $(item).find('.marketing-title-length').html(10);
            }else{
                $(item).find('.marketing-title-length').html(marketing_title_length);
            }
        }
    });
}

function changeMarketingType(obj){
    var marketing_type = $(obj).val();
    var marketing_num = $(obj).parent().parent().attr("marketing_num");
    $(obj).parent().parent().find('.marketing-type').val(marketing_type);
    $(".effect-marketing").each(function(idx, item) {
        if($(item).hasClass("marketing"+marketing_num)){
            switch(marketing_type){
                case 'Pintuan':
                    $(item).find('.goods').each(function(index,obj){
                        $(obj).find('.pintuan-detail').show();
                        $(obj).find('.seckill-detail').hide();
                        $(obj).find('.auction-detail').hide();
                        $(obj).find('.bargain-detail').hide();
                    });
                    $(item).find('.seckill-title').addClass('none2');
                    $(item).find('.seckill-more').hide();
                    break;
                case 'Seckill':
                    $(item).find('.goods').each(function(index,obj){
                        $(obj).find('.pintuan-detail').hide();
                        $(obj).find('.seckill-detail').show();
                        $(obj).find('.auction-detail').hide();
                        $(obj).find('.bargain-detail').hide();
                    });
                    $(item).find('.seckill-title').removeClass('none2');
                    $(item).find('.seckill-more').show();
                    break;
                case 'Auction':
                    $(item).find('.goods').each(function(index,obj){
                        $(obj).find('.pintuan-detail').hide();
                        $(obj).find('.seckill-detail').hide();
                        $(obj).find('.auction-detail').show();
                        $(obj).find('.bargain-detail').hide();
                    });
                    $(item).find('.seckill-title').addClass('none2');
                    $(item).find('.seckill-more').hide();
                    break;
                case 'Bargain':
                    $(item).find('.goods').each(function(index,obj){
                        $(obj).find('.pintuan-detail').hide();
                        $(obj).find('.seckill-detail').hide();
                        $(obj).find('.auction-detail').hide();
                        $(obj).find('.bargain-detail').show();
                    });
                    $(item).find('.seckill-title').addClass('none2');
                    $(item).find('.seckill-more').hide();
                    break;
                case '0':
                    $(item).find('.goods').each(function(index,obj){
                        $(obj).find('.pintuan-detail').hide();
                        $(obj).find('.seckill-detail').hide();
                        $(obj).find('.auction-detail').hide();
                        $(obj).find('.bargain-detail').hide();
                    });
                    $(item).find('.seckill-title').addClass('none2');
                    $(item).find('.seckill-more').hide();
                    break;
            }
        }
    });
    $(".attr-content-marketing").each(function(idx, item) {
        if($(item).hasClass("marketing_attr"+marketing_num)){
           $(item).find('.marketing-type').val(marketing_type);
        }
    });
}

function changeMarketingPadding(obj){
    var marketing_num = $(obj).parent().parent().parent().attr("marketing_num");
    var marketing_vertical_padding = $(obj).val().split(',')[1];
    $(obj).parent().parent().parent().find('.marketing-vertical-padding').val(marketing_vertical_padding);
    $(obj).parent().find(".marketing-vertical-padding-value").text(marketing_vertical_padding);
    $(".effect-marketing").each(function(idx, item) {
        if($(item).hasClass("marketing"+marketing_num)){
            $(item).css("padding-top",marketing_vertical_padding+"px");
            $(item).css("padding-bottom",marketing_vertical_padding+"px");
        }
    });
}
// 营销活动组件方法结束

function submit(){
    var params = {};
    var sort = [];
    var sort_name = [];
    $(".sort").each(function(idx, item) {
        sort.push($(item).val());
    });
    $(".sort-name").each(function(idx, item) {
        sort_name.push($(item).val());
    });
    params.sort = sort;
    params.sort_name = sort_name;
    // 页面名称与id
    params.page_id = $('#pageId').val();
    params.page_type = $('#pageType').val();
    params.page_name = $(".attr-content-page-name").val();
    if(params.page_name==''){
        WST.msg(WST.lang('custompage_require_page_name'),{time:1000,anim: 6});
        return;
    }
    // 页面设置组件开始
    var page_component = [];
    var page_item_id = "";
    page_component.push($(".attr-content-page-title").val());
    page_component.push($(".attr-content-page-share-title").val());
    page_component.push($(".attr-content-page-img").attr('img_url'));
    params.page_component = page_component;
    params.page_item_id = $(".page-item-id").val();
    //console.log(page_component);
    // 页面设置组件结束

    // 轮播组件开始
    var swiper_component = [];
    var swiper_item_id = [];
    var swiper_img = [];
    var swiper_link = [];
    var swiper_indicator_type = [];
    var swiper_indicator_color = [];
    var swiper_interval = [];
    var img_temp = [];
    var link_temp = [];
    var swiper_padding_top = [];
    var swiper_padding_bottom = [];
    var swiper_padding_left = [];
    var swiper_padding_right = [];
    var swiper_nums = [];
    $(".effect-media-swiper").each(function(idx, item) {
        var swiperName = "swiper"+$(item).attr("swiper_num");
        swiper_component.push(swiperName);
        var swiper_num = $(item).attr("swiper_num");
        swiper_nums.push(swiper_num);
    });
    for(var i=0;i<swiper_nums.length;i++){
        $(".attr-content-swiper-item-img").each(function(index, obj) {
            if($(obj).parent().attr("swiper_num") == swiper_nums[i]){
                var swiperLink = $(obj).find(".attr-content-swiper-link").val();
                var swiperImg = $(obj).find(".attr-content-swiper-img").attr('img_url');
                link_temp.push(swiperLink);
                img_temp.push(swiperImg);
            }
        });
        swiper_link[i] = link_temp;
        swiper_img[i] = img_temp;
        link_temp = [];
        img_temp = [];
        $(".attr-content-swiper").each(function(idx, item) {
            if($(item).attr("swiper_num") == swiper_nums[i]){
                swiper_item_id.push($(item).find(".swiper-item-id").val());
                swiper_indicator_type.push($(item).find(".indicator-type").val());
                swiper_indicator_color.push($(item).find(".indicator-color").val()?$(item).find(".indicator-color").val():"#e13335");
                swiper_interval.push($(item).find(".interval").val());
                swiper_padding_top.push($(item).find(".swiper-padding-top").val());
                swiper_padding_bottom.push($(item).find(".swiper-padding-bottom").val());
                swiper_padding_left.push($(item).find(".swiper-padding-left").val());
                swiper_padding_right.push($(item).find(".swiper-padding-right").val());
            }
        });
    }
    params.swiper_component = swiper_component;
    params.swiper_link = swiper_link;
    params.swiper_img = swiper_img;
    params.swiper_indicator_type = swiper_indicator_type;
    params.swiper_indicator_color = swiper_indicator_color;
    params.swiper_interval = swiper_interval;
    params.swiper_padding_top = swiper_padding_top;
    params.swiper_padding_bottom = swiper_padding_bottom;
    params.swiper_padding_left = swiper_padding_left;
    params.swiper_padding_right = swiper_padding_right;
    params.swiper_item_id = swiper_item_id;
    // 轮播组件结束

    // 楼层商品组件开始
    var floor_goods_component = [];
    var floor_goods_item_id = [];
    var floor_goods_background_color = [];
    var floor_goods_img = [];
    var floor_goods_link = [];
    var floor_goods_columns = [];
    var floor_goods_goods_cats = [];
    var floor_goods_title = [];
    var floor_goods_columns_title = [];
    var floor_goods_columns_title_temp = [];
    var floor_goods_columns_goods_select = [];
    var floor_goods_columns_goods_select_ids = [];
    var floor_goods_columns_goods_select_cats_id = [];
    var floor_goods_select_temp = [];
    var floor_goods_select_ids_temp = [];
    var floor_goods_select_cats_id_temp = [];
    var floor_goods_order = [];
    var floor_goods_columns_goods_tag = [];
    var floor_goods_tag_temp = [];
    var floor_goods_columns_goods_min_price = [];
    var floor_goods_min_price_temp = [];
    var floor_goods_columns_goods_max_price = [];
    var floor_goods_max_price_temp = [];
    var floor_goods_nums = [];
    $(".effect-floor-goods").each(function(idx, item) {
        var floor_goods_num = $(item).attr("floor_goods_num");
        floor_goods_nums.push(floor_goods_num);
    });
    for(var i=0;i<floor_goods_nums.length;i++){
        $(".attr-content-floor-goods").each(function(idx, item) {
            if($(item).attr("floor_goods_num") == floor_goods_nums[i]){
                var floorGoodsName = "floor_goods"+$(item).attr("floor_goods_num");
                floor_goods_component.push(floorGoodsName);
                var floorGoodsTitle = $(item).find(".attr-content-floor-goods-title").val();
                if(floorGoodsTitle.length>10){
                    floorGoodsTitle = floorGoodsTitle.substr(0,10);
                }
                floor_goods_title.push(floorGoodsTitle);
                floor_goods_img.push($(item).find(".attr-content-floor-goods-img").attr("img_url"));
                floor_goods_link.push($(item).find(".attr-content-floor-goods-link").val());
                floor_goods_order.push($(item).find(".floor-goods-order").val());
                var floor_goods_num = $(item).attr("floor_goods_num");
                $(".attr-content-floor-goods-item-column").each(function(index, obj) {
                    if($(obj).parent().attr("floor_goods_num") == floor_goods_num){
                        var columnsTitle = $(obj).find(".floor-goods-columns-name").val();
                        if(columnsTitle.length>6){
                            columnsTitle = columnsTitle.substr(0,6);
                        }
                        $(obj).find("input[name^='floor-goods-select']").each(function(key,value){
                            if($(value).prop('checked')){
                                floor_goods_select_temp.push($(value).val());
                            }
                        });
                        floor_goods_columns_title_temp.push(columnsTitle);
                        var selectIds = $(obj).find('.floor-goods-select-ids-value').val();
                        floor_goods_select_ids_temp.push(selectIds);
                        var selectCatsId = $(obj).find('.floor-goods-select-cats-id-value').val();
                        floor_goods_select_cats_id_temp.push(selectCatsId);
                        var tag = $(obj).find('.floor-goods-tag-value').val();
                        floor_goods_tag_temp.push(tag);
                        var minPrice = $(obj).find('.floor-goods-min-price-value').val();
                        floor_goods_min_price_temp.push(minPrice);
                        var maxPrice = $(obj).find('.floor-goods-max-price-value').val();
                        floor_goods_max_price_temp.push(maxPrice);
                    }
                });
                floor_goods_columns_title[i] = floor_goods_columns_title_temp;
                floor_goods_columns_goods_select[i] = floor_goods_select_temp;
                floor_goods_columns_goods_select_ids[i] = floor_goods_select_ids_temp;
                floor_goods_columns_goods_select_cats_id[i] = floor_goods_select_cats_id_temp;
                floor_goods_columns_goods_tag[i] = floor_goods_tag_temp;
                floor_goods_columns_goods_min_price[i] = floor_goods_min_price_temp;
                floor_goods_columns_goods_max_price[i] = floor_goods_max_price_temp;
                floor_goods_columns_title_temp = [];
                floor_goods_select_temp = [];
                floor_goods_select_ids_temp = [];
                floor_goods_select_cats_id_temp = [];
                floor_goods_tag_temp = [];
                floor_goods_min_price_temp = [];
                floor_goods_max_price_temp = [];
            }
        });
        $(".attr-content-floor-goods").each(function(idx, item) {
            if($(item).attr("floor_goods_num") == floor_goods_nums[i]){
                floor_goods_item_id.push($(item).find(".floor-goods-item-id").val());
            }
        });
    }
    params.floor_goods_component = floor_goods_component;
    params.floor_goods_title = floor_goods_title;
    params.floor_goods_img = floor_goods_img;
    params.floor_goods_link = floor_goods_link;
    params.floor_goods_columns_title = floor_goods_columns_title;
    params.floor_goods_columns_goods_select = floor_goods_columns_goods_select;
    params.floor_goods_columns_goods_select_ids = floor_goods_columns_goods_select_ids;
    params.floor_goods_columns_goods_select_cats_id = floor_goods_columns_goods_select_cats_id;
    params.floor_goods_columns_goods_tag = floor_goods_columns_goods_tag;
    params.floor_goods_columns_goods_max_price = floor_goods_columns_goods_max_price;
    params.floor_goods_columns_goods_min_price = floor_goods_columns_goods_min_price;
    params.floor_goods_order = floor_goods_order;
    params.floor_goods_item_id = floor_goods_item_id;
    //console.log("params:",params);return;
    // 楼层商品组件开始

    // 商品组件开始
    var goods_group_component = [];
    var goods_group_item_id = [];
    var goods_group_title = [];
    var show_goods_group_title = [];
    var goods_group_columns_goods_select = [];
    var goods_group_columns_goods_select_ids = [];
    var goods_group_columns_goods_select_cats_id = [];
    var goods_group_order = [];
    var goods_group_columns_goods_tag = [];
    var goods_group_columns_goods_min_price = [];
    var goods_group_columns_goods_max_price = [];
    var goods_group_goods_nums = [];
    var goods_group_nums = [];
    $(".effect-goods-group").each(function(idx, item) {
        var goods_group_num = $(item).attr("goods_group_num");
        goods_group_nums.push(goods_group_num);
    });
    for(var i=0;i<goods_group_nums.length;i++) {
        $(".attr-content-goods-group").each(function (idx, item) {
            if ($(item).attr("goods_group_num") == goods_group_nums[i]) {
                var goodsGroupName = "goods_group"+$(item).attr("goods_group_num");
                goods_group_component.push(goodsGroupName);
                goods_group_item_id.push($(item).find(".goods-group-item-id").val());
                var goodsGroupTitle = $(item).find(".attr-content-goods-group-title").val();
                if (goodsGroupTitle.length > 10) {
                    goodsGroupTitle = goodsGroupTitle.substr(0, 10);
                }
                goods_group_title.push(goodsGroupTitle);
                show_goods_group_title.push($(item).find(".show-goods-group-title").val());
                goods_group_order.push($(item).find(".goods-group-order").val());
                var goods_nums = $(item).find(".goods-group-goods-nums").val();
                goods_group_goods_nums.push(goods_nums);
                $(item).find("input[name^='goods-select']").each(function (key, value) {
                    if ($(value).prop('checked')) {
                        goods_group_columns_goods_select.push($(value).val());
                    }
                });
                var selectIds = $(item).find('.goods-select-ids-value').val();
                goods_group_columns_goods_select_ids.push(selectIds);
                var selectCatsId = $(item).find('.goods-select-cats-id-value').val();
                goods_group_columns_goods_select_cats_id.push(selectCatsId);
                var tag = $(item).find('.goods-tag-value').val();
                goods_group_columns_goods_tag.push(tag);
                var minPrice = $(item).find('.goods-min-price-value').val();
                goods_group_columns_goods_max_price.push(minPrice);
                var maxPrice = $(item).find('.goods-max-price-value').val();
                goods_group_columns_goods_min_price.push(maxPrice);
            }
        });
    }

    params.goods_group_component = goods_group_component;
    params.goods_group_title = goods_group_title;
    params.show_goods_group_title = show_goods_group_title;
    params.goods_group_goods_nums = goods_group_goods_nums;
    params.goods_group_columns_goods_select = goods_group_columns_goods_select;
    params.goods_group_columns_goods_select_ids = goods_group_columns_goods_select_ids;
    params.goods_group_columns_goods_select_cats_id = goods_group_columns_goods_select_cats_id;
    params.goods_group_columns_goods_tag = goods_group_columns_goods_tag;
    params.goods_group_columns_goods_max_price = goods_group_columns_goods_max_price;
    params.goods_group_columns_goods_min_price = goods_group_columns_goods_min_price;
    params.goods_group_order = goods_group_order;
    params.goods_group_item_id = goods_group_item_id;
    //console.log("params:",params);return;
    // 商品组件结束

    // 导航组件开始
    var nav_component = [];
    var nav_item_id = [];
    var nav_background_color = [];
    var show_nav_category = [];
    var nav_item_text = [];
    var nav_item_link = [];
    var nav_item_color = [];
    var n_text_temp = [];
    var n_link_temp = [];
    var n_color_temp = [];
    var nav_nums = [];
    $(".effect-nav").each(function(idx, item) {
        var navName = "nav"+$(item).attr("nav_num");
        nav_component.push(navName);
        var nav_num = $(item).attr("nav_num");
        nav_nums.push(nav_num);
    });
    for(var i=0;i<nav_nums.length;i++){
        $(".attr-content-nav-item-group").each(function(index, obj) {
            if($(obj).parent().attr("nav_num") == nav_nums[i]){
                var navText = $(obj).find(".attr-content-nav-text").val();
                if(navText.length>4){
                    navText = navText.substr(0,4);
                }

                var navLink = $(obj).find(".attr-content-nav-link").val();
                var navTextColor = $(obj).find(".attr-content-nav-text-color").val();
                n_text_temp.push(navText);
                n_link_temp.push(navLink);
                n_color_temp.push(navTextColor);
            }
        });
        nav_item_text[i] = n_text_temp;
        nav_item_link[i] = n_link_temp;
        nav_item_color[i] = n_color_temp;
        n_text_temp = [];
        n_link_temp = [];
        n_color_temp = [];
        $(".attr-content-nav").each(function(idx, item) {
            if($(item).attr("nav_num") == nav_nums[i]){
                nav_item_id.push($(item).find(".nav-item-id").val());
                nav_background_color.push($(item).find(".nav-bg-color").val());
                show_nav_category .push($(item).find(".show-nav-category").val());
            }
        });
    }

    params.nav_component = nav_component;
    params.show_nav_category = show_nav_category;
    params.nav_background_color = nav_background_color;
    params.nav_item_text = nav_item_text;
    params.nav_item_link = nav_item_link;
    params.nav_item_color = nav_item_color;
    params.nav_item_id = nav_item_id;
    //console.log("param:",params);return;
    // 导航组件结束

    // 公告组件开始
    var notice_component = [];
    var notice_item_id = [];
    var notice_background_color = [];
    var notice_text_color = [];
    var notice_img = [];
    var notice_text = [];
    var notice_link = [];
    var notice_text_temp = [];
    var notice_link_temp = [];
    var notice_vertical_padding = [];
    var notice_direction = [];
    $(".attr-content-notice").each(function(idx, item) {
        var noticeName = "notice"+$(item).attr("notice_num");
        var notice_num = $(item).attr("notice_num");
        $(".attr-content-notice-item-group").each(function(index, obj) {
            if($(obj).parent().attr("notice_num") == notice_num){
                var noticeText = $(obj).find(".attr-content-notice-text").val();
                var noticeLink = $(obj).find(".attr-content-notice-link").val();
                notice_text_temp.push(noticeText);
                notice_link_temp.push(noticeLink);
            }
        });
        notice_text[idx] = notice_text_temp;
        notice_link[idx] = notice_link_temp;
        notice_text_temp = [];
        notice_link_temp = [];
        notice_component.push(noticeName);
        notice_item_id.push($(item).find(".notice-item-id").val());
        notice_background_color.push($(item).find(".notice-bg-color").val());
        notice_text_color.push($(item).find(".notice-text-color").val());
        notice_img.push($(item).find(".notice-img").val());
        notice_vertical_padding.push($(item).find(".notice-vertical-padding").val());
        notice_direction.push($(item).find(".notice-direction").val());
    });
    params.notice_component = notice_component;
    params.notice_background_color = notice_background_color;
    params.notice_text_color = notice_text_color;
    params.notice_img = notice_img;
    params.notice_text = notice_text;
    params.notice_link = notice_link;
    params.notice_vertical_padding = notice_vertical_padding;
    params.notice_direction = notice_direction;
    params.notice_item_id = notice_item_id;
    // 公告组件结束

    // 搜索框组件开始
    var search_component = [];
    var search_item_id = [];
    var search_img = [];
    var search_link = [];
    var search_text = [];
    var search_type = [];
    var search_hots = [];
    $(".attr-content-search").each(function(idx, item) {
        var searchName = "search"+$(item).attr("search_num");
        search_component.push(searchName);
        search_item_id.push($(item).find(".search-item-id").val());
        search_img.push($(item).find(".attr-content-search-img").attr('img_url'));
        search_link.push($(item).find(".attr-content-search-link").val());
        $(".attr-content-search-type").each(function(index, obj) {
            var searchType = $(obj).val();
            if (searchType.length > 3) {
                searchType = searchType.substr(0, 3);
            }
            search_type.push(searchType);
        });
        $(".attr-content-search-text").each(function(index, obj) {
            var searchText = $(obj).val();
            if (searchText.length > 10) {
                searchText = searchText.substr(0, 10);
            }
            search_text.push(searchText);
        });
        $(".attr-content-search-hot-item").each(function(index, obj) {
            var hotsText = $(obj).find(".search-hots-text").val();
            if (hotsText.length > 5) {
                hotsText = hotsText.substr(0, 5);
            }
            search_hots.push(hotsText);
        });
    });
    params.search_component = search_component;
    params.search_img = search_img;
    params.search_link = search_link;
    params.search_pc_text = search_text;
    params.search_type = search_type;
    params.search_hots = search_hots;
    params.search_item_id = search_item_id;
    //console.log('params:',params);return;
    // 搜索框组件结束

    // 优惠券组件开始
    var coupon_component = [];
    var coupon_item_id = [];
    var coupon_img = [];
    var coupon_title = [];
    var coupon_desc = [];
    var coupon_title_color = [];
    var coupon_desc_color = [];
    var coupon_text_color = [];
    var coupon_btn_color = [];
    var coupon_btn_text_color = [];
    var coupon_select_ids = [];
    var coupons = [];
    $(".effect-coupon").each(function(idx, item) {
        var coupon_num = $(item).attr("coupon_num");
        coupons.push(coupon_num);
    });
    for(var i=0;i<coupons.length;i++){
        $(".attr-content-coupon").each(function(idx, item) {
            if($(item).attr("coupon_num") == coupons[i]){
                var couponName = "coupon"+$(item).attr("coupon_num");
                coupon_component.push(couponName);
                coupon_item_id.push($(item).find(".coupon-item-id").val());
                var couponTitle = $(item).find(".attr-content-coupon-title").val();
                if(couponTitle.length>4){
                    couponTitle = couponTitle.substr(0,4);
                }
                coupon_title.push(couponTitle);
                var couponDesc = $(item).find(".attr-content-coupon-desc").val();
                if(couponDesc.length>9){
                    couponDesc = couponDesc.substr(0,9);
                }
                coupon_desc.push(couponDesc);
                coupon_img.push($(item).find(".attr-content-coupon-img").attr("img_url"));
                coupon_title_color.push($(item).find(".coupon-title-color").val());
                coupon_desc_color.push($(item).find(".coupon-desc-color").val());
                coupon_text_color.push($(item).find(".coupon-text-color").val());
                coupon_btn_color.push($(item).find(".coupon-btn-color").val());
                coupon_btn_text_color.push($(item).find(".coupon-btn-text-color").val());
                coupon_select_ids.push($(item).find(".coupon-select-ids-value").val());
            }
        });
    }
    params.coupon_component = coupon_component;
    params.coupon_title = coupon_title;
    params.coupon_desc = coupon_desc;
    params.coupon_img = coupon_img;
    params.coupon_title_color = coupon_title_color;
    params.coupon_desc_color = coupon_desc_color;
    params.coupon_text_color = coupon_text_color;
    params.coupon_btn_color = coupon_btn_color;
    params.coupon_btn_text_color = coupon_btn_text_color;
    params.coupon_select_ids = coupon_select_ids;
    params.coupon_item_id = coupon_item_id;
    //console.log("params:",params);return;
    // 优惠券组件结束

    // 单图组组件开始
    var image_component = [];
    var image_item_id = [];
    var image_img = [];
    var image_link = [];
    var image_img_temp = [];
    var image_link_temp = [];
    var image_padding_top = [];
    var image_padding_bottom = [];
    var image_padding_left = [];
    var image_padding_right = [];
    var image_background_color = [];
    var image_nums = [];
    $(".effect-image").each(function(idx, item) {
        var imageName = "image"+$(item).attr("image_num");
        image_component.push(imageName);
        var image_num = $(item).attr("image_num");
        image_nums.push(image_num);
    });
    for(var i=0;i<image_nums.length;i++){
        $(".attr-content-image-item-img").each(function(index, obj) {
            if($(obj).parent().attr("image_num") == image_nums[i]){
                var imageLink = $(obj).find(".attr-content-image-link").val();
                var imageImg = $(obj).find(".attr-content-image-img").attr('img_url');
                image_link_temp.push(imageLink);
                image_img_temp.push(imageImg);
            }
        });
        image_link[i] = image_link_temp;
        image_img[i] = image_img_temp;
        image_link_temp = [];
        image_img_temp = [];
        $(".attr-content-image").each(function(idx, item) {
            if($(item).attr("image_num") == image_nums[i]){
                image_item_id.push($(item).find(".image-item-id").val());
                image_padding_top.push($(item).find(".image-padding-top").val());
                image_padding_bottom.push($(item).find(".image-padding-bottom").val());
                image_padding_left.push($(item).find(".image-padding-left").val());
                image_padding_right.push($(item).find(".image-padding-right").val());
                image_background_color.push($(item).find(".image-bg-color").val());
            }
        });
    }

    params.image_component = image_component;
    params.image_img = image_img;
    params.image_link = image_link;
    params.image_padding_top = image_padding_top;
    params.image_padding_bottom = image_padding_bottom;
    params.image_padding_left = image_padding_left;
    params.image_padding_right = image_padding_right;
    params.image_background_color = image_background_color;
    params.image_item_id = image_item_id;
    // 单图组组件结束

    // 橱窗组件开始
    var shopwindow_component = [];
    var shopwindow_item_id = [];
    var shopwindow_img = [];
    var shopwindow_link = [];
    var shopwindow_img_temp = [];
    var shopwindow_link_temp = [];
    var shopwindow_layout = [];
    var shopwindow_background_color = [];
    var shopwindow_nums = [];
    $(".effect-shopwindow").each(function(idx, item) {
        var shopwindowName = "shopwindow"+$(item).attr("shopwindow_num");
        shopwindow_component.push(shopwindowName);
        var shopwindow_num = $(item).attr("shopwindow_num");
        shopwindow_nums.push(shopwindow_num);
    });
    for(var i=0;i<shopwindow_nums.length;i++){
        $(".attr-content-shopwindow-item-img").each(function(index, obj) {
            if($(obj).parent().attr("shopwindow_num") == shopwindow_nums[i]){
                var shopwindowLink = $(obj).find(".attr-content-shopwindow-link").val();
                var shopwindowImg = $(obj).find(".attr-content-shopwindow-img").attr('img_url');
                shopwindow_link_temp.push(shopwindowLink);
                shopwindow_img_temp.push(shopwindowImg);
            }
        });
        shopwindow_link[i] = shopwindow_link_temp;
        shopwindow_img[i] = shopwindow_img_temp;
        shopwindow_link_temp = [];
        shopwindow_img_temp = [];
        $(".attr-content-shopwindow").each(function(idx, item) {
            if($(item).attr("shopwindow_num") == shopwindow_nums[i]){
                shopwindow_item_id.push($(item).find(".shopwindow-item-id").val());
                shopwindow_background_color.push($(item).find(".shopwindow-bg-color").val());
                shopwindow_layout.push($(item).find(".shopwindow-layout-value").val());
            }
        });
    }

    params.shopwindow_component = shopwindow_component;
    params.shopwindow_img = shopwindow_img;
    params.shopwindow_link = shopwindow_link;
    params.shopwindow_background_color = shopwindow_background_color;
    params.shopwindow_layout = shopwindow_layout;
    params.shopwindow_item_id = shopwindow_item_id;
    //console.log("params:",params);return;
    // 橱窗组件结束

    // 视频组件开始
    var video_component = [];
    var video_item_id = [];
    var video_img = [];
    var video_link = [];
    var video_vertical_padding = [];
    var video_nums = [];
    $(".effect-video").each(function(idx, item) {
        var videoName = "video"+$(item).attr("video_num");
        var videoNum = $(item).attr("video_num");
        video_component.push(videoName);
        video_nums.push(videoNum);
    });
    for(var i=0;i<video_nums.length;i++) {
        $(".attr-content-video").each(function (idx, item) {
            if($(item).attr("video_num") == video_nums[i]){
                video_item_id.push($(item).find(".video-item-id").val());
                video_img.push($(item).find(".attr-content-video-img").attr("img_url"));
                video_link.push($(item).find(".attr-content-video-link").val());
                video_vertical_padding.push($(item).find(".video-vertical-padding").val());
            }
        });
    }
    params.video_component = video_component;
    params.video_link = video_link;
    params.video_img = video_img;
    params.video_vertical_padding = video_vertical_padding;
    params.video_item_id = video_item_id;
    // 视频组件结束

    // 辅助空白组件开始
    var blank_component = [];
    var blank_item_id = [];
    var blank_height = [];
    var blank_background_color = [];
    var blank_nums = [];
    $(".effect-blank").each(function(idx, item) {
        var blankName = "blank"+$(item).attr("blank_num");
        var blankNum = $(item).attr("blank_num");
        blank_component.push(blankName);
        blank_nums.push(blankNum);
    });
    for(var i=0;i<blank_nums.length;i++){
        $(".attr-content-blank").each(function(idx, item) {
            if($(item).attr("blank_num") == blank_nums[i]){
                blank_item_id.push($(item).find(".blank-item-id").val());
                blank_height.push($(item).find(".blank-height").val());
                blank_background_color.push($(item).find(".blank-bg-color").val());
            }
        });
    }
    params.blank_component = blank_component;
    params.blank_height = blank_height;
    params.blank_background_color = blank_background_color;
    params.blank_item_id = blank_item_id;
    // 辅助空白组件结束

    // 辅助线组件开始
    var line_component = [];
    var line_item_id = [];
    var line_background_color = [];
    var line_class = [];
    var line_border_color = [];
    var line_height = [];
    var line_vertical_margin = [];
    var line_nums = [];
    $(".effect-line").each(function(idx, item) {
        var lineName = "line"+$(item).attr("line_num");
        var lineNum = $(item).attr("line_num");
        line_component.push(lineName);
        line_nums.push(lineNum);
    });
    for(var i=0;i<line_nums.length;i++){
        $(".attr-content-line").each(function(idx, item) {
            if($(item).attr("line_num") == line_nums[i]){
                line_item_id.push($(item).find(".line-item-id").val());
                line_background_color.push($(item).find(".line-bg-color").val());
                line_class.push($(item).find(".line-class").val());
                line_border_color.push($(item).find(".line-border-color").val());
                line_height.push($(item).find(".line-height").val());
                line_vertical_margin.push($(item).find(".line-vertical-margin").val());
            }
        });
    }
    params.line_component = line_component;
    params.line_background_color = line_background_color;
    params.line_class = line_class;
    params.line_border_color = line_border_color;
    params.line_height = line_height;
    params.line_vertical_margin = line_vertical_margin;
    params.line_item_id = line_item_id;
    // 辅助线组件结束

    // 富文本组件开始
    var text_component = [];
    var text_item_id = [];
    var text_background_color = [];
    var text_vertical_padding = [];
    var text_horizontal_padding = [];
    var text_text = [];
    var text_nums = [];
    $(".effect-text").each(function(idx, item) {
        var textName = "text"+$(item).attr("text_num");
        var textNum = $(item).attr("text_num");
        text_component.push(textName);
        text_text.push($(item).find(".text").html());
        text_nums.push(textNum);
    });
    for(var i=0;i<text_nums.length;i++){
        $(".attr-content-text").each(function(idx, item) {
            if($(item).attr("text_num") == text_nums[i]){
                text_item_id.push($(item).find(".text-item-id").val());
                text_background_color.push($(item).find(".text-bg-color").val());
                text_vertical_padding.push($(item).find(".text-vertical-padding").val());
                text_horizontal_padding.push($(item).find(".text-horizontal-padding").val());
            }
        });
    }

    params.text_component = text_component;
    params.text_background_color = text_background_color;
    params.text_vertical_padding = text_vertical_padding;
    params.text_horizontal_padding = text_horizontal_padding;
    params.text_text = text_text;
    params.text_item_id = text_item_id;
    // 富文本组件结束

    // 单文本组件开始
    var txt_component = [];
    var txt_item_id = [];
    var txt_background_color = [];
    var txt_text_color = [];
    var txt_text_alignment = [];
    var txt_text = [];
    var txt_link = [];
    var txt_vertical_padding = [];
    var txt_horizontal_padding = [];
    var txt_nums = [];
    $(".effect-txt").each(function(idx, item) {
        var txtName = "txt"+$(item).attr("txt_num");
        var txtNum = $(item).attr("txt_num");
        txt_component.push(txtName);
        txt_nums.push(txtNum);
    });
    for(var i=0;i<txt_nums.length;i++){
        $(".attr-content-txt").each(function(idx, item) {
            if($(item).attr("txt_num") == txt_nums[i]){
                txt_item_id.push($(item).find(".txt-item-id").val());
                txt_background_color.push($(item).find(".txt-bg-color").val());
                txt_text_color.push($(item).find(".txt-text-color").val());
                var txt_text_content = $(item).find(".attr-content-txt-text").val();
                if(txt_text_content.length>50){
                    txt_text_content = txt_text_content.substr(0,50);
                }
                txt_text.push(txt_text_content);
                txt_link.push($(item).find(".attr-content-txt-link").val());
                txt_text_alignment.push($(item).find(".attr-content-txt-text-alignment").val());
                txt_vertical_padding.push($(item).find(".txt-vertical-padding").val());
                txt_horizontal_padding.push($(item).find(".txt-horizontal-padding").val());
            }
        });
    }
    params.txt_component = txt_component;
    params.txt_background_color = txt_background_color;
    params.txt_text_color = txt_text_color;
    params.txt_text_alignment = txt_text_alignment;
    params.txt_text = txt_text;
    params.txt_link = txt_link;
    params.txt_vertical_padding = txt_vertical_padding;
    params.txt_horizontal_padding = txt_horizontal_padding;
    params.txt_item_id = txt_item_id;
    // 单文本组件结束

    // 图文列表组件开始
    var image_text_component = [];
    var image_text_item_id = [];
    var image_text_vertical_padding = [];
    var image_text_style = [];
    var image_text_title = [];
    var image_text_desc = [];
    var image_text_link = [];
    var image_text_img = [];
    var image_text_img_temp = [];
    var image_text_nums = [];
    $(".effect-image-text").each(function(idx, item) {
        var imageTextName = "image_text"+$(item).attr("image_text_num");
        image_text_component.push(imageTextName);
        var image_text_num = $(item).attr("image_text_num");
        image_text_nums.push(image_text_num);
    });
    for(var i=0;i<image_text_nums.length;i++){
        $(".attr-content-image-text-item-img").each(function(index, obj) {
            if($(obj).parent().attr("image_text_num") == image_text_nums[i]){
                $(obj).find('.image-text-img').each(function(key,value){
                    if($(value).hasClass('none')){
                    }else{
                        var imageTextImg = $(value).find(".attr-content-image-text-img").attr('img_url');
                        image_text_img_temp.push(imageTextImg);
                    }
                });
            }
        });
        image_text_img[i] = image_text_img_temp;
        image_text_img_temp = [];
        $(".attr-content-image-text").each(function(idx, item) {
            if($(item).attr("image_text_num") == image_text_nums[i]){
                image_text_item_id.push($(item).find(".image-text-item-id").val());
                image_text_style.push($(item).find(".image-text-style").val());
                var image_text_title_text = $(item).find(".attr-content-image-text-title").val();
                if(image_text_title_text.length>20){
                    image_text_title_text = image_text_title_text.substr(0,20);
                }
                image_text_title.push(image_text_title_text);
                var image_text_desc_text = $(item).find(".attr-content-image-text-desc").val();
                if(image_text_desc_text.length>20){
                    image_text_desc_text = image_text_desc_text.substr(0,200);
                }
                image_text_desc.push(image_text_desc_text);
                image_text_link.push($(item).find(".attr-content-image-text-link").val());
                image_text_vertical_padding.push($(item).find(".image-text-vertical-padding").val());
            }
        });
    }

    params.image_text_component = image_text_component;
    params.image_text_img = image_text_img;
    params.image_text_style = image_text_style;
    params.image_text_title = image_text_title;
    params.image_text_desc = image_text_desc;
    params.image_text_link = image_text_link;
    params.image_text_vertical_padding = image_text_vertical_padding;
    params.image_text_item_id = image_text_item_id;
    // 图文列表组件结束

    // 多店铺组件开始
    var shop_component = [];
    var shop_item_id = [];
    var shop_title = [];
    var shop_search_radius = [];
    $(".effect-shop").each(function(idx, item) {
        var shopName = "shop"+$(item).attr("shop_num");
        shop_component.push(shopName);
    });
    $(".attr-content-shop").each(function(idx, item) {
        shop_item_id.push($(item).find(".shop-item-id").val());
        var shop_title_text = $(item).find(".attr-content-shop-title").val();
        if(shop_title_text.length>10){
            shop_title_text = shop_title_text.substr(0,10);
        }
        shop_title.push(shop_title_text);
        shop_search_radius.push($(item).find(".attr-content-shop-search-radius").val());
    });
    params.shop_component = shop_component;
    params.shop_title = shop_title;
    params.shop_search_radius = shop_search_radius;
    params.shop_item_id = shop_item_id;
    // 多店铺组件结束

    // 新闻组件开始
    var new_component = [];
    var new_item_id = [];
    var new_title = [];
    var new_count = [];
    var new_select_ids = [];
    var new_nums = [];
    $(".effect-new").each(function(idx, item) {
        var newName = "shop"+$(item).attr("new_num");
        var newNum = $(item).attr("new_num");
        new_component.push(newName);
        new_nums.push(newNum);
    });
    for(var i=0;i<new_nums.length;i++){
        $(".attr-content-new").each(function(idx, item) {
            if($(item).attr("new_num") == new_nums[i]){
                new_item_id.push($(item).find(".new-item-id").val());
                new_count.push($(item).find(".new-count").val());
                var new_title_text = $(item).find(".attr-content-new-title").val();
                if(new_title_text.length>8){
                    new_title_text = new_title_text.substr(0,8);
                }
                new_title.push(new_title_text);
                new_select_ids.push($(item).find(".new-select-ids-value").val());
            }
        });
    }
    params.new_component = new_component;
    params.new_title = new_title;
    params.new_count = new_count;
    params.new_select_ids = new_select_ids;
    params.new_item_id = new_item_id;
    // 新闻组件结束

    // 营销活动组件开始
    var marketing_component = [];
    var marketing_item_id = [];
    var marketing_type = [];
    var marketing_title = [];
    var marketing_vertical_padding = [];
    var marketing_flag = false;
    var marketing_nums = [];
    $(".effect-marketing").each(function(idx, item) {
        var marketingName = "marketing"+$(item).attr("marketing_num");
        var marketingNum = $(item).attr("marketing_num");
        marketing_component.push(marketingName);
        marketing_nums.push(marketingNum);
    });
    for(var i=0;i<marketing_nums.length;i++){
        $(".attr-content-marketing").each(function(idx, item) {
            if($(item).attr("marketing_num") == marketing_nums[i]){
                marketing_item_id.push($(item).find(".marketing-item-id").val());
                var marketing_type_value = $(item).find(".marketing-type").val();
                if(marketing_type_value == 0){
                    WST.msg(WST.lang('custompage_require_bottom_nav_text'),{time:1000,anim: 6});
                    marketing_flag = true;
                }else{
                    marketing_type.push(marketing_type_value);
                }
                var marketing_title_text = $(item).find(".attr-content-marketing-title").val();
                if(marketing_title_text.length>10){
                    marketing_title_text = marketing_title_text.substr(0,10);
                }
                marketing_title.push(marketing_title_text);
                marketing_vertical_padding.push($(item).find(".marketing-vertical-padding").val());
            }
        });
    }

    params.marketing_component = marketing_component;
    params.marketing_type = marketing_type;
    params.marketing_title = marketing_title;
    params.marketing_vertical_padding = marketing_vertical_padding;
    params.marketing_item_id = marketing_item_id;
    //console.log("params:",params);return;
    if(marketing_flag)return;
    // 营销活动组件结束
    //console.log(params);
    //return;
    //console.log(sort);
    //console.log(sort_name);
    if(!submitStatus) {
        submitStatus = true;
        $.post(WST.AU('custompage://custompagedecoration/edit'), params, function (res) {
            var json = WST.toAdminJson(res);
            if (json.status == 1) {
                WST.msg(WST.lang("custompage_operation_success"), {icon: 1});
                setTimeout(function () {
                    location.href = WST.AU('custompage://admin/index', 'type=' + params.page_type);
                }, 1000);
            } else {
                WST.msg(json.msg, {icon: 2});
            }
        });
    }
}
