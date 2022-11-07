<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    'currency_symbol' =>'HK$',
    //页面通用
    'combination_title'=>'组合套餐',
    'combination_description' =>'商品关联搭配工具：支持固定及自由搭配，推荐适合的搭配商品，帮助店铺提升客单价和转化率',

   	'no_login'=>'您还没有登录，请先登录',
   	'combination_operation' =>'操作',
   	'combination_operation_success' =>'操作成功',
    'combination_operation_fail' =>'操作失败',
    'combination_query' =>'查询',
    'combination_check'=>'查看',
    'combination_del'=>'删除',
    'combination_edit'=>'编辑',
    'combination_save'=>'保存',
    'combination_add'=>'新增',
    'combination_update'=>'修改',
    'combination_reset'=>'重置',
    'combination_back'=>'返回',
    'combination_confirm'=>'确定',
    'combination_cancel'=>'取消',
    'combination_submit'=>'提交',
    'combination_close'=>'关闭',
    'combination_full_screen'=>'全屏',
    'combination_upload'=>'上传',
    'combination_has_upload'=>'已上传',
    'combination_please_select'=>'请选择',
    'combination_back_page'=>'返回上一页',
    'combination_loading'=>'正在加载中...',
    'combination_loading_data'=>'正在加载数据，请稍后...',
    'combination_submitting' =>'正在提交数据，请稍后...',
    'combination_searching' =>'正在查询数据，请稍后...',
    'combination_confirm_del' =>'您确定删除吗？',
    'combination_no_data'=>'暂无数据',

    'combination_home_page'=>'首页',
    'combination_cat' =>'分类',
    'combination_shopping_cart' =>'购物车',
    'combination_follow'=>'关注',
    'combination_mine'=>'我的',

    'combination_combine_type' =>'套餐类型',
    'combination_combine_type_1' =>'自由搭配',
    'combination_combine_type_2' =>'固定套餐',
    'combination_combine_type_tips' =>'（注意：自由搭配套餐允许用户任意退货，固定套餐退货时必须全部商品一起退）',

    'combination_main_image' =>'套餐主图',
    'combination_goods_image' =>'商品图片',
    'combination_goods_name' =>'商品名称',
    'combination_goods_attr' =>'商品属性',
    'combination_reduction_price' =>'立减价格',
    'combination_original_price' =>'原价',

    'combination_is_free_shipping' =>'是否包邮',
    'combination_free_shipping' =>'包邮',
    'combination_not_free_shipping' =>'买家承担运费',

    'combination_combine_name' =>'套餐名称',
    'combination_combine_name_tips' =>'套餐名称不能超过10个字',
    'combination_combine_desc' =>'套餐介绍',
    'combination_combine_desc_tips' =>'套餐介绍不能超过50个字',
    'combination_combine_img' =>'套餐封面',
    'combination_require_combine_img' =>'套餐封面图片不能为空',
    'combination_activity_time' =>'活动时间',
    'combination_start_date' =>'开始日期',
   	'combination_to_title' =>'至',
   	'combination_end_date' =>'结束日期',
    'combination_activity_advance' =>'活动预热',
    'combination_before' =>'提前',
    'combination_day' =>'天',
    'combination_package_status' =>'套餐状态',
    'combination_package_status_1' =>'未开始',
    'combination_package_status_2' =>'进行中',
    'combination_package_status_3' =>'已结束',
    'combination_package_status_4' =>'暂停',
    'combination_package_status_5' =>'恢复',
    'combination_package_sort_no' =>'套餐排序号',


    'combination_require_goods_cat' =>'请选择商品分类',
    'combination_goods_name_id' =>'商品名称/商品编号',
    'combination_require_select_time' =>'请选择套餐活动时间',
    'combination_require_advance_day' =>'请选择活动预热提前天数',
    'combination_main_goods' =>'主商品',
    'combination_require_main_goods' =>'请选择套餐主商品',
    'combination_require_sale_main_goods' =>'请选择在售的套餐主商品',
    'combination_reduce_money_tips' =>'主商品立减价格不能为负数',
    'combination_reduce_money_tips2' =>'主商品立减价格必须比商品价格小',
    'combination_reduce_money_tips3' =>'主商品立减价格必须比商品规格价格小',
    'combination_combine_goods' =>'搭配商品',
    'combination_require_combine_goods' =>'请选择搭配商品',
    'combination_combine_goods_tips_1' =>'1.组合商品只支持实物商品。',
    'combination_combine_goods_tips_2' =>'2.若商品有多规格的话，则商品组合价格=每个规格的价格-立减价格。',
    'combination_not_select_same_goods' =>'请勿选择相同的商品',
    'combination_require_sale_combine_goods' =>'请选择在售的搭配商品',
    'combination_invalid_goods' =>'无效的商品',
    'combination_combine_reduce_money_tips' =>'搭配商品立减价格不能为负数',
    'combination_combine_reduce_money_tips2' =>'搭配商品立减价格必须比商品价格小',
    'combination_combine_reduce_money_tips3' =>'搭配立减价格必须比商品规格价格小',
    'combination_time_tips' =>'套餐已过期或未开始',
    'combination_invalid_combine' =>'无效的套餐搭配',
    'combination_not_repeat_order' =>'请勿重复提交订单',
    'combination_invalid_address' =>'无效的用户地址',
    'combination_stock_less_tips' =>'商品%s库存不足，请重新选择',
    'combination_order_use_score_tips' =>'交易订单【%s】使用积分%s个',
    'combination_order_success'=>'下单成功',
    'combination_order_success_wait_pay'=>'下单成功，等待用户支付',
    'combination_order_has_pay'=>'订单已支付',
    'combination_order_success_has_pay'=>'订单已支付，下单成功',
    'combination_package_detail_not_exist' =>'商品组合套餐详情不存在',
    'combination_illegal_operation' =>'非法操作',
    'combination_goods_not_exist' =>'商品不存在',
    'combination_require_goods' =>'请选择商品',
    'combination_no_pay_type' =>'没有支付方式',
    'combination_package_not_exist' =>'组合套餐不存在',
    'combination_select_want_combine_goods' =>'请选择要搭配的商品',
    'combination_select_least_one' =>'请至少选择一个搭配商品',
    'combination_invalid' =>'已失效',


    'combination_select_spec' =>'选择规格',
    'combination_activity_start_time' =>'活动开始时间',
    'combination_has_select_num_tips' =>'已选择%s个搭配商品',
    'combination_portfolio_price' =>'组合价',
    'combination_buy_now' =>'立即购买',
    'combination_price' =>'价格',
    'combination_stock' =>'库存',
    'combination_goods_detail' =>'商品详情',
    'combination_check_order_information' =>'填写核对订单信息',
    'combination_order_submit_success' =>'成功提交订单',
    'combination_check_order' =>'填写并核对订单',
    'combination_consignee_information' =>'收货人信息',

    //地址
    'combination_user_address'=>'用户地址',
   	'combination_add_address' =>'新增收货地址',
    'combination_more_address'=>'更多地址',
    'combination_default_address'=>'默认地址',
   	'combination_update_receiving_address'=>'修改收货地址',
   	'combination_consignee'=>'收货人',
    'combination_require_consignee'=>'请填写收货人',
    'combination_detail_address'=>'详细地址',
    'combination_require_detail_address'=>'请输入详细地址',
    'combination_contact_number'=>'联系电话',
    'combination_require_contact_number'=>'请填写联系电话',
    'combination_is_default_address'=>'是否默认地址',
    'combination_save_address_of_consignee'=>'保存收货人地址',
    'combination_receiving_address'=>'收货地址',
    'combination_please_select_area'=>'请选择地区',
    'combination_please_select_address'=>'请选择收货地址',
    'combination_set_default'=>'设为默认',
    'combination_set_default_address'=>'设为默认地址',
    'combination_receiving_address_management'=>'地址管理',
    'combination_my_address'=>'我的地址',
    'combination_yes'=>'是',
    'combination_no'=>'否',
    'combination_stow_address'=>'收起地址',

    'combination_select_complete_address'=>'请选择完整收货地址！',
    'combination_curr_area_no_pick_up_tips'=>'该区域内没有自提点,请选择快递运输',
    'combination_select_pick_up'=>'选择自提点',
    'combination_calculating_price_tips'=>'正在计算订单价格，请稍后...',
    'combination_items_goods_total_tips'=>'共<span>%s</span>件商品',
    'combination_cart_empty_tips'=>'购物车中空空如也，赶紧去选购吧～',
    'combination_cart_empty_add_goods_tips'=>'您的购物车没有商品哦，请先添加商品~',

    //结算
    'combination_confirm_order' =>'确认订单',
   	'combination_available_integral' =>'可获得积分',
   	'combination_pay_type'=>'支付方式',
    'combination_cash_on_delivery'=>'货到付款',
    'combination_online_payment'=>'在线支付',
    'combination_goods_merchbill'=>'商品清单',
    'combination_goods'=>'商品',
    'combination_unit_price'=>'单价',
    'combination_type'=>'类型',
    'combination_package_price'=>'套餐价',
    'combination_require_invoice'=>'请选择发票',
    'combination_is_need_invoice'=>'是否需要发票',
    'combination_need'=>'需要',
    'combination_not_need'=>'不需要',
    'combination_invoice_information'=>'发票信息',
    'combination_no_invoice'=>'不开发票',
    'combination_invoice_detail'=>'明细',
    'combination_invoice_type'=>'发票类型',
    'combination_invoice_special_vat'=>'增值税专用发票',
    'combination_invoice_normal'=>'普通发票',
    'combination_invoice_status'=>'发票状态',
    'combination_has_open'=>'已开',
    'combination_has_not_open'=>'未开',
    'combination_invoice_head'=>'发票抬头',
    'combination_invoice_content'=>'发票内容',
    'combination_add_invoice_head'=>'新增发票抬头',
    'combination_personal'=>'个人',
    'combination_unit'=>'单位',
    'combination_invoice_personal_detail'=>'普通发票（纸质）  个人   明细',
    'combination_invoice_head_detail'=>'普通发票（纸质）%s 明细',
    'combination_invoice_special_personal_detail'=>'专用发票（纸质） 个人  明细',
    'combination_invoice_special_detail'=>'专用发票（纸质）%s 明细',
    'combination_invoice_head_required'=>'发票抬头不能为空',
    'combination_require_unit_name'=>'请填写单位名称',
    'combination_require_taxpayer_code'=>'请填写纳税人识别码',

    'combination_invoice_tips_1'=>'发票金额不包含优惠券和积分支付部分',
    'combination_invoice_tips_2'=>'第三方卖家销售的商品发票由商家开具、寄出、发票内容由商家决定',
    'combination_delivery_method'=>'送货方式',
    'combination_express_delivery'=>'快递运输',
    'combination_delivery_mode'=>'配送方式',
    'combination_self_extraction'=>'自提',
    'combination_order_remark'=>'订单备注',
    'combination_require_order_remark'=>'填写订单备注',
    'combination_leave_message_to_seller'=>'给卖家留言',
    'combination_shop_total'=>'店铺合计',
    'combination_shop_total_includ_freight'=>'店铺合计(含运费)',
    'combination_needpay_total'=>'应付总金额(含运费)',
    'combination_total_freight'=>'运费总计',
    'combination_freight'=>'运费',
    'combination_specs'=>'规格',
    'combination_address'=>'地址',
    'combination_region_title'=>'区域',
    'combination_select_area'=>'选择区域',
    'combination_require_select_area'=>'请选择区域',
    'combination_self_lift_point_desc'=>'系统将根据您的收货地址显示其范围内的自提点，请确保您的收货地址正确填写。',
    'combination_pay_for_order'=>'支付订单费用',
    'combination_pay_for_order_desc'=>'支付购买商品费用 HK$%s',
    'combination_submit_order' =>'提交订单',
    'combination_qq_chat'=>'QQ交谈',
    'combination_contact_me'=>'和我联系',

    'combination_score_tips1' =>'（共有%s个积分，可抵HK$%s）',
    'combination_integral_pay' =>'积分支付',
    'combination_try_this_match' =>'试试这样搭配?',
    'combination_match_price' =>'搭配价',
    'combination_free_shipping_tips' =>'【包邮】',
    'combination_during_event' =>'活动期间',
    'combination_has_select' =>'已选',
    'combination_use_integral' =>'使用积分',

    'combination_activity_info'=>'活动内容',
    'combination_activity_setting'=>'活动设置'
];