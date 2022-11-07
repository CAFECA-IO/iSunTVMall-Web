<?php
namespace addons\presale\model;
use think\addons\BaseModel as Base;
use addons\presale\validate\Presales as Validate;
use think\Db;
/**
 * ============================================================================
 * WSTMart多用户商城
 * 版权所有 2016-2066 广州商淘信息科技有限公司，并保留所有权利。
 * 官网地址:http://www.wstmart.net
 * 交流社区:http://bbs.shangtao.net
 * 联系QQ:153289970
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！未经本公司授权您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 商品预售插件
 */
class Presales extends Base{
	/***
     * 安装插件
     */
    public function installMenu(){
    	Db::startTrans();
		try{
			$hooks = ["wechatDocumentUserIndexTools","mobileDocumentUserIndexTools"
		    ];
			$this->bindHoods("Presale", $hooks);
			$now = date("Y-m-d H:i:s");
			//管理员后台
            $datas = [];
            $menuLangParams = [
                1=>['menuName'=>'商品預售'],
                2=>['menuName'=>'商品预售'],
                3=>['menuName'=>'Presale'],
            ];
            $menuId = Db::name('menus')->insertGetId(["parentId"=>93,"menuSort"=>1,"dataFlag"=>1,"isShow"=>1,"menuMark"=>"presale"]);
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['menuId'] = $menuId;
                $data['langId'] = $v['id'];
                $data['menuName'] = $menuLangParams[$v['id']]['menuName'];
                $datas[] = $data;
            }
            Db::name('menus_langs')->insertAll($datas);

			if($menuId!==false){
                $privilegeLangParams = [
                    1=>['privilegeName_00'=>'查看商品預售','privilegeName_04'=>'預售商品操作','privilegeName_03'=>'刪除預售商品'],
                    2=>['privilegeName_00'=>'查看商品预售','privilegeName_04'=>'预售商品操作','privilegeName_03'=>'删除预售商品'],
                    3=>['privilegeName_00'=>'View Presale','privilegeName_04'=>'Presale product operation','privilegeName_03'=>'Delete Presale items'],
                ];
                $privilegeIds = [];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"PRESALE_SPYS_00","isMenuPrivilege"=>1,"privilegeUrl"=>"/addon/presale-admin-index","otherPrivilegeUrl"=>"/addon/presale-admin-pageQuery,/addon/presale-admin-pageAuditQuery","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'00'];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"PRESALE_SPYS_04","isMenuPrivilege"=>0,"privilegeUrl"=>"","otherPrivilegeUrl"=>"/addon/presale-admin-allow,/addon/presale-admin-illegal","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'04'];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"PRESALE_SPYS_03","isMenuPrivilege"=>0,"privilegeUrl"=>"/addon/presale-admin-del","otherPrivilegeUrl"=>"","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'03'];
                $datas = [];
                for($i=0;$i<count($privilegeIds);$i++){
                    foreach (WSTSysLangs() as $key => $v) {
                        $data = [];
                        $data['privilegeId'] = $privilegeIds[$i]['privilegeId'];
                        $data['langId'] = $v['id'];
                        $data['privilegeName'] = $privilegeLangParams[$v['id']]['privilegeName_'.$privilegeIds[$i]['code']];
                        $datas[] = $data;
                    }
                }
                Db::name('privileges_langs')->insertAll($datas);
			}

			//商家中心
            $homeMenuLangParams = [
                1=>['menuName'=>'商品預售'],
                2=>['menuName'=>'商品预售'],
                3=>['menuName'=>'Presale'],
            ];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>77,"menuUrl"=>"/addon/presale-shops-index","menuOtherUrl"=>"/addon/presale-shops-pageQuery,/addon/presale-shops-edit,/addon/presale-shops-toEdit,/addon/presale-shops-changeSale,/addon/presale-shops-del,/addon/presale-shops-searchGoods,/addon/presale-shops-orders,/addon/presale-shops-pageQueryOrders,/addon/presale-shops-toView","menuType"=>1,"isShow"=>1,"menuSort"=>1,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"presale"]);
            $datas = [];
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['menuId'] = $homeMenuId;
                $data['langId'] = $v['id'];
                $data['menuName'] = $homeMenuLangParams[$v['id']]['menuName'];
                $datas[] = $data;
            }
            Db::name('home_menus_langs')->insertAll($datas);

			//用户中心
            $homeMenuLangParams = [
                1=>['menuName'=>'我參與的預售'],
                2=>['menuName'=>'我参与的预售'],
                3=>['menuName'=>'My Presale'],
            ];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>101,"menuUrl"=>"addon/presale-users-plist","menuOtherUrl"=>"addon/presale-users-pageQuery,addon/presale-users-checkPayStatus","menuType"=>0,"isShow"=>1,"menuSort"=>1,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"presale"]);
            $datas = [];
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['menuId'] = $homeMenuId;
                $data['langId'] = $v['id'];
                $data['menuName'] = $homeMenuLangParams[$v['id']]['menuName'];
                $datas[] = $data;
            }
            Db::name('home_menus_langs')->insertAll($datas);

			//新增上传目录
            $dataLangParams = [
                1=>[
                    'dataName_00'=>'商品預售',
                    'dataName_01'=>'預售商品審核通過',
                    'dataName_02'=>'預售商品審核不通過',
                    'dataName_03'=>'預售尾款逾期提醒(用戶)',
                    'dataName_04'=>'預售尾款逾期提醒(商家)',
                    'dataName_05'=>'預售結束通知',
                    'dataName_06'=>'預售訂單未支付定金自動取消(用户)',
                    'dataName_07'=>'預售失敗退款通知',
                ],
                2=>[
                    'dataName_00'=>'商品预售',
                    'dataName_01'=>'预售商品审核通过',
                    'dataName_02'=>'预售商品审核不通过',
                    'dataName_03'=>'预售尾款逾期提醒(用户)',
                    'dataName_04'=>'预售尾款逾期提醒(商家)',
                    'dataName_05'=>'预售结束通知(用户)',
                    'dataName_06'=>'预售订单未支付定金自动取消(用户)',
                    'dataName_07'=>'预购失败退款通知',
                ],
                3=>[
                    'dataName_00'=>'Presale',
                    'dataName_01'=>'Pre-sale goods approved',
                    'dataName_02'=>'Fail to pass the examination of pre-sale goods',
                    'dataName_03'=>'Pre-sale balance overdue reminder(User)',
                    'dataName_04'=>'Pre-sale balance overdue reminder(Shop)',
                    'dataName_05'=>'Pre-sale end notice(User)',
                    'dataName_06'=>'The advance order will be cancelled automatically if the deposit is not paid(User)',
                    'dataName_07'=>'Refund notice for pre order failure',
                ],
            ];
            $dataIds = [];
            $dataId = Db::name('datas')->insertGetId(['catId'=>3,'dataVal'=>'presale']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'00'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>6,'dataVal'=>'PRESALE_GOODS_ALLOW']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'01'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>6,'dataVal'=>'PRESALE_GOODS_REJECT']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'02'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>6,'dataVal'=>'PRESALE_USER_PAY_OVERTIME']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'03'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>6,'dataVal'=>'PRESALE_SHOP_PAY_OVERTIME']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'04'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>6,'dataVal'=>'PRESALE_END']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'05'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>6,'dataVal'=>'PRESALE_ORDER_USER_PAY_TIMEOUT']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'06'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>9,'dataVal'=>'WX_PRESALE_USER_PAY_OVERTIME']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'03'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>9,'dataVal'=>'WX_PRESALE_SHOP_PAY_OVERTIME']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'04'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>9,'dataVal'=>'WX_PRESALE_END']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'05'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>9,'dataVal'=>'WX_PRESALE_REFUND']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'07'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>9,'dataVal'=>'WX_PRESALE_ORDER_USER_PAY_TIMEOUT']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'06'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>9,'dataVal'=>'WX_PRESALE_GOODS_ALLOW']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'01'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>9,'dataVal'=>'WX_PRESALE_GOODS_REJECT']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'02'];
            $datas = [];
            for($i=0;$i<count($dataIds);$i++){
                foreach (WSTSysLangs() as $key => $v) {
                    $data = [];
                    $data['dataId'] = $dataIds[$i]['dataId'];
                    $data['langId'] = $v['id'];
                    $data['dataName'] = $dataLangParams[$v['id']]['dataName_'.$dataIds[$i]['code']];
                    $datas[] = $data;
                }
            }
            Db::name('datas_langs')->insertAll($datas);
			installSql("presale");
			$this->addNavMenu();
			$this->addMobileBtn();
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return false;
	   	}
    }

	/**
	 * 删除菜单
	 */
	public function uninstallMenu(){
		Db::startTrans();
		try{
			$hooks = ["wechatDocumentUserIndexTools","mobileDocumentUserIndexTools"
		    ];
			$this->unbindHoods("Presale", $hooks);
            $menuId = Db::name('menus')->where(["menuMark"=>"presale"])->value('menuId');
            Db::name('menus')->where(["menuMark"=>"presale"])->delete();
            Db::name('menus_langs')->where(['menuId'=>$menuId])->delete();

            $privilegeIds = Db::name('privileges')->where("privilegeCode","like","PRESALE_%")->column('privilegeId');
            Db::name('privileges')->where("privilegeCode","like","PRESALE_%")->delete();
            Db::name('privileges_langs')->where([['privilegeId','in',$privilegeIds]])->delete();

            $homeMenuIds = Db::name('home_menus')->where(["menuMark"=>"presale"])->column('menuId');
            Db::name('home_menus')->where(["menuMark"=>"presale"])->delete();
            Db::name('home_menus_langs')->where([['menuId','in',$homeMenuIds]])->delete();

            $dataId = Db::name('datas')->where(["dataVal"=>"presale"])->value('id');
            Db::name('datas')->where(["dataVal"=>"groupon"])->delete();
            Db::name('datas_langs')->where(["dataId"=>$dataId])->delete();

            $dataIds = Db::name('datas')->where([["dataVal",'like',"%PRESALE%"]])->column('id');
            Db::name('datas')->where([["dataVal",'like',"%PRESALE%"]])->delete();
            Db::name('datas_langs')->where([["dataId","in",$dataIds]])->delete();
			uninstallSql("presale");//传入插件名
			$this->delNavMenu();
			$this->delMobileBtn();
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return false;
	   	}
	}

    /**
     * 菜单显示隐藏
     */
    public function toggleShow($isShow = 1){
        Db::startTrans();
        try{
            Db::name('menus')->where(["menuMark"=>"presale"])->update(["isShow"=>$isShow]);
            Db::name('home_menus')->where(["menuMark"=>"presale"])->update(["isShow"=>$isShow]);
            Db::name('navs')->where(["navUrl"=>"addon/presale-goods-lists.html"])->update(["isShow"=>$isShow]);
            if($isShow==1){
                $this->addMobileBtn();
            }else{
                $this->delMobileBtn();
            }
            Db::commit();
            return true;
        }catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    public function addNavMenu(){
        $navData = [
            'navType'=>0,
            'navUrl'=>'addon/presale-goods-lists.html',
            'isShow'=>1,
            'isOpen'=>0,
            'navSort'=>0,
            'createTime'=>date('Y-m-d H:i:s')
        ];
        $navId = Db::name('navs')->insertGetId($navData);
        $datas = [];
        $langParams = [
            1=>['navTitle'=>'預售'],
            2=>['navTitle'=>'预售'],
            3=>['navTitle'=>'Pre-sale'],
        ];
        foreach (WSTSysLangs() as $key => $v) {
            $data = [];
            $data['navId'] = $navId;
            $data['langId'] = $v['id'];
            $data['navTitle'] = $langParams[$v['id']]['navTitle'];
            $datas[] = $data;
        }
        Db::name('navs_langs')->insertAll($datas);
    }

	public function addMobileBtn(){
        $langParams = [
            1=>['btnName'=>'預售'],
            2=>['btnName'=>'预售'],
            3=>['btnName'=>'Pre-sale'],
        ];
        $datas = [];
		$data = array();
		$data["btnSrc"] = 0;
		$data["btnUrl"] = "addon/presale-goods-molists.html";
		$data["btnImg"] = "addons/presale/view/images/presale.png";
		$data["addonsName"] = "Presale";
		$data["btnSort"] = 6;
        $btnId = Db::name('mobile_btns')->insertGetId($data);
        foreach (WSTSysLangs() as $key => $v) {
            $data = [];
            $data['btnId'] = $btnId;
            $data['langId'] = $v['id'];
            $data['btnName'] = $langParams[$v['id']]['btnName'];
            $datas[] = $data;
        }
        Db::name('mobile_btns_langs')->insertAll($datas);

        $datas = [];
		$data = array();
		$data["btnSrc"] = 1;
		$data["btnUrl"] = "addon/presale-goods-wxlists.html";
		$data["btnImg"] = "addons/presale/view/images/presale.png";
		$data["addonsName"] = "Presale";
		$data["btnSort"] = 6;
        $btnId = Db::name('mobile_btns')->insertGetId($data);
        foreach (WSTSysLangs() as $key => $v) {
            $data = [];
            $data['btnId'] = $btnId;
            $data['langId'] = $v['id'];
            $data['btnName'] = $langParams[$v['id']]['btnName'];
            $datas[] = $data;
        }
        Db::name('mobile_btns_langs')->insertAll($datas);

        // app端
        if(WSTDatas('ADS_TYPE',4)){
            $datas = [];
            $data = array();
            $data["btnSrc"] = 3;
            $data["btnUrl"] = "wst://Presale";
            $data["btnImg"] = "addons/presale/view/images/presale.png";
            $data["addonsName"] = "Presale";
            $data["btnSort"] = 17;
            $btnId = Db::name('mobile_btns')->insertGetId($data);
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['btnId'] = $btnId;
                $data['langId'] = $v['id'];
                $data['btnName'] = $langParams[$v['id']]['btnName'];
                $datas[] = $data;
            }
            Db::name('mobile_btns_langs')->insertAll($datas);
        }
        // 小程序端
        if(WSTDatas('ADS_TYPE',5)){
            $datas = [];
            $data = array();
            $data["btnSrc"] = 2;
            $data["btnUrl"] = "/addons/package/pages/presale/goods/list";
            $data["btnImg"] = "addons/presale/view/images/presale.png";
            $data["addonsName"] = "Presale";
            $data["btnSort"] = 17;
            $btnId = Db::name('mobile_btns')->insertGetId($data);
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['btnId'] = $btnId;
                $data['langId'] = $v['id'];
                $data['btnName'] = $langParams[$v['id']]['btnName'];
                $datas[] = $data;
            }
            Db::name('mobile_btns_langs')->insertAll($datas);
        }

	}

    public function delNavMenu(){
        $navId = Db::name('navs')->where(['navUrl'=>'addon/presale-goods-lists.html'])->value('id');
        Db::name('navs')->where(['navUrl'=>'addon/presale-goods-lists.html'])->delete();
        Db::name('navs_langs')->where(['navId'=>$navId])->delete();
    }


	public function delMobileBtn(){
        $btnIds =  Db::name('mobile_btns')->where(["addonsName"=>"presale"])->column('id');
        Db::name('mobile_btns')->where(["addonsName"=>"presale"])->delete();
        Db::name('mobile_btns_langs')->where([['btnId','in',$btnIds]])->delete();
	}

	/**
	 * 获取商品组合列表-管理员
	 */
	public function pageQueryByAdmin($presaleStatus){
		$goodsName = input('goodsName');
		$shopName = input('shopName');
		$areaIdPath = input('areaIdPath');
		$goodsCatIdPath = input('goodsCatIdPath');
		$where = [];
		$where[]=['p.dataFlag','=',1];
		$where[]=['p.presaleStatus','=',$presaleStatus];
        if($shopName !='')$where[] = ['s.shopName|s.shopSn','like','%'.$shopName.'%'];
		if($areaIdPath !='')$where[] = ['s.areaIdPath','like',$areaIdPath."%"];
		if($goodsCatIdPath !='')$where[] = ['g.goodsCatIdPath','like',$goodsCatIdPath."%"];
		if($goodsName !='')$where[] = ['pl.goodsName', 'like', '%'.$goodsName.'%'];
        $page =  $this->alias('p')
                    ->join('__PRESALES_LANGS__ pl','pl.presaleId=p.id and pl.langId='.WSTCurrLang())
                    ->join('__GOODS__ g','g.goodsId=p.goodsId and g.dataFlag=1','inner')
                    ->join('__SHOPS__ s','s.shopId=p.shopId','left')->where($where)
                    ->order('id desc')
                    ->field('p.*,s.shopName,g.isSpec,g.shopPrice,p.shopId,pl.goodsName')
                    ->paginate(input('limit/d'))->toArray();
        if(count($page['data'])>0){
        	$time = time();
        	foreach($page['data'] as $key =>$v){
        		$page['data'][$key]['verfiycode'] =  WSTShopEncrypt($v['shopId']);
        		$page['data'][$key]['minShopPrice'] = 0;
        		$page['data'][$key]['maxShopPrice'] = 0;
        		$page['data'][$key]['shopPrice'] = round($v['shopPrice']-$v['reduceMoney']);
        		if($v['isSpec']==1){
                    $spec = Db::name('goods_specs')->where(['goodsId'=>$v['goodsId'],'dataFlag'=>1])->field('max(specPrice) maxShopPrice,min(specPrice) minShopPrice')->find();
			        $page['data'][$key]['minShopPrice'] = round($spec['minShopPrice']-$v['reduceMoney']);
			        $page['data'][$key]['maxShopPrice'] = round($spec['maxShopPrice']-$v['reduceMoney']);
        		}
        		$page['data'][$key]['goodsImg'] = WSTImg($v['goodsImg']);
        		if(strtotime($v['startTime'])<=$time && strtotime($v['endTime'])>=$time){
        			$page['data'][$key]['status'] = 1;
        		}else if(strtotime($v['startTime'])>$time){
                    $page['data'][$key]['status'] = 0;
        		}else{
        			$page['data'][$key]['status'] = -1;
        		}
        	}
        }
        $page['status'] = 1;
        return $page;
	}

	/**
	 * 获取商品组合列表-商家
	 */
	public function pageQueryByShop($sId = 0){
		$goodsName = input('goodsName');

		$shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
        $presaleStatus=input('presaleStatus','');
		$where = [];
		$where[]=['shopId','=',$shopId];
		$where[]=['dataFlag','=',1];
        if($presaleStatus !='')$where[]=['presaleStatus','=',$presaleStatus];
		if($goodsName !='')$where[] = ['pl.goodsName', 'like', '%'.$goodsName.'%'];
        $page =  $this
                    ->alias('p')
                    ->join('__PRESALES_LANGS__ pl','pl.presaleId=p.id and pl.langId='.WSTCurrLang())
                    ->where($where)
                    ->order('id desc')
                    ->paginate(input('limit/d'))->toArray();
        if(count($page['data'])>0){
        	$time = time();
        	foreach($page['data'] as $key =>$v){
        		$page['data'][$key]['verfiycode'] =  WSTShopEncrypt($shopId);
        		$page['data'][$key]['goodsImg'] = WSTImg($v['goodsImg']);
        		if(strtotime($v['startTime'])<=$time && strtotime($v['endTime'])>=$time){
        			$page['data'][$key]['status'] = 1;
        		}else if(strtotime($v['startTime'])>$time){
                    $page['data'][$key]['status'] = 0;
        		}else{
        			$page['data'][$key]['status'] = -1;
        		}
        	}
        }
        $page['status'] = 1;
        return $page;
	}

	/**
     * 搜索商品
     */
    public function searchGoods($sId=0){
    	$shopId =($sId==0)?(int)session('WST_USER.shopId'):$sId;
    	$shopCatId1 = (int)input('post.shopCatId1');
    	$shopCatId2 = (int)input('post.shopCatId2');
    	$goodsName = input('post.goodsName');
    	$where = [];
    	$where[] = ['goodsStatus','=',1];
    	$where[] = ['dataFlag','=',1];
    	$where[] = ['shopId','=',$shopId];
    	if($shopCatId1>0)$where[] = ['shopCatId1','=',$shopCatId1];
    	if($shopCatId2>0)$where[] = ['shopCatId2','=',$shopCatId2];
    	if($goodsName!='')$where[] = ['gl.goodsName','like','%'.$goodsName.'%'];
    	$rs = Db::name('goods')
            ->alias('g')
            ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
            ->where($where)->field('gl.goodsName,gl.goodsSeoKeywords,gl.goodsSeoDesc,g.goodsId,shopPrice,goodsTips,goodsImg,isSpec')->select();
    	foreach ($rs as $key => $v) {
    		if($v['isSpec']==1){
	        	$spec = Db::name('goods_specs')->where(['shopId'=>$shopId,'goodsId'=>$v['goodsId'],'dataFlag'=>1])->field('max(specPrice) maxShopPrice,min(specPrice) minShopPrice')->find();
	        	$rs[$key]['minShopPrice'] = $spec['minShopPrice'];
	        	$rs[$key]['maxShopPrice'] = $spec['maxShopPrice'];
            }
    	}
        return WSTReturn('',1,$rs);
    }

	/**
	 * 新增商品
	 */
	public function add($sId=0){
		$data = input('post.');
		WSTUnset($data,'id,dataFlag,auditRemark,presaleStatus');
		$shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
		$data['shopId'] = $shopId;
		if($data['goodsId']==0)return WSTReturn(lang('presale_select_presale_goods'));
		$chk = Db::name('goods')->where(['goodsId'=>$data['goodsId'],'shopId'=>$shopId,'goodsStatus'=>1,'dataFlag'=>1])->find();
		if(empty($chk))return WSTReturn(lang('presale_select_presale_goods'));
        foreach (WSTSysLangs() as $key => $v) {
            $goodsName = input('langParams' . $v['id'] . 'goodsName');
            if ($goodsName == '') {
                return WSTReturn(lang('presale_require_goods_name'));
            }
        }
		//判断是否已经存在同时间的预售商品
		$where = [];
		$where['goodsId'] = $data['goodsId'];
		$where['dataFlag'] = 1;
		$whereOr = ' ( ("'.date('Y-m-d H:i:s',WSTStrToTime($data['startTime'])).'" between startTime and endTime) or ( "'.date('Y-m-d H:i:s',WSTStrToTime($data['endTime'])).'" between startTime and endTime) ) ';
		$rn = Db::name('presales')->where($where)->where($whereOr)->count();
		if($rn>0)return WSTReturn(lang('presale_goods_is_in_same_time_activity'));
        $reduceMoney = (float)input('post.reduceMoney');
        if($reduceMoney<0)return WSTReturn(lang('presale_goods_reduce_money_can_not_minus'));
        if($chk['shopPrice']<$reduceMoney)return WSTReturn(lang('presale_reduce_money_must_less_goods_price'));
        $minPrice = $chk['shopPrice'];
        if($chk['isSpec']){
        	$minPrice = Db::name('goods_specs')->where(['shopId'=>$shopId,'goodsId'=>$data['goodsId'],'dataFlag'=>1])->min('specPrice');
        	if($minPrice<$reduceMoney)return WSTReturn(lang('presale_reduce_money_must_less_goods_price'));
        }
        if($data['saleType']==1 && $data['depositType']==1){
        	$dmoney = ($minPrice-$reduceMoney)*$data['depositRate']/100;
        	if($dmoney<0.01){
        		return WSTReturn(lang('presale_deposit_money_error_tips'));
        	}
        }
        if($data['goodsNum']<$data['limitNum'])return WSTReturn(lang('presale_can_buy_num_tips'));
        $data['deliverDays'] = ($data['saleType']==0)?$data['deliverDays0']:$data['deliverDays1'];
        if(WSTStrToTime($data['startTime']) > WSTStrToTime($data['endTime']))return WSTReturn(lang('presale_time_error_tips'));
		Db::startTrans();
		try{
			$data['createTime'] = date('Y-m-d h:i:s');
			$data['dataFlag'] = 1;
			$result = null;
			$validate = new Validate;
        	if (!$validate->scene(true)->check($data)) {
        		return WSTReturn($validate->getError());
        	}else{
        		$result = $this->allowField(true)->save($data);
        	}
	    	if(false !== $result){
                $presaleId = $this->id;
                $dataLangs = [];
                foreach (WSTSysLangs() as $key => $v) {
                    $dataLang = [];
                    $dataLang['presaleId'] = $presaleId;
                    $dataLang['langId'] = $v['id'];
                    $dataLang['goodsName'] = input('langParams'.$v['id'].'goodsName');
                    $dataLang['goodsSeoKeywords'] = input('langParams'.$v['id'].'goodsSeoKeywords');
                    $dataLang['goodsSeoDesc'] = input('langParams'.$v['id'].'goodsSeoDesc');
                    $dataLang['goodsTips'] = input('langParams'.$v['id'].'goodsTips');
                    $dataLangs[] = $dataLang;
                }
                Db::name('presales_langs')->insertAll($dataLangs);
				//WSTUseResource(0, $this->id, $data['goodsImg']);
		        Db::commit();
		        return WSTReturn(lang('presale_operation_success'),1);
			}
		}catch (\Exception $e) {
            Db::rollback();
        }
		return WSTReturn(lang('presale_operation_fail'));
	}

	/**
	 * 编辑商品
	 */
	public function edit($sId=0){
		$data = input('post.');
		WSTUnset($data,'dataFlag,auditRemark,presaleStatus');
		$shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
		$rs = Db::name('presales')->where(['shopId'=>$shopId,'dataFlag'=>1,'id'=>$data['id']])->find();
		if(empty($rs))return WSTReturn(lang('presale_activity_is_gone'));
        foreach (WSTSysLangs() as $key => $v) {
            $goodsName = input('langParams' . $v['id'] . 'goodsName');
            if ($goodsName == '') {
                return WSTReturn(lang('presale_require_goods_name'));
            }
        }
		if($rs['presaleStatus']==1)return WSTReturn(lang('presale_is_audit_can_not_edit'));
		$data['shopId'] = $shopId;
		if($data['goodsId']==0)return WSTReturn(lang('presale_select_presale_goods'));
		$chk = Db::name('goods')->where(['goodsId'=>$data['goodsId'],'shopId'=>$shopId,'goodsStatus'=>1,'dataFlag'=>1])->find();
		if(empty($chk))return WSTReturn(lang('presale_select_presale_goods'));
		//判断是否已经存在同时间的预售商品
		$id = $data['id'];
		$where = [];
		$where[] = ['goodsId','=',$data['goodsId']];
		$where[] = ['id','<>',$data['id']];
		$where[] = ['dataFlag','=',1];
		$whereOr = ' ( ("'.date('Y-m-d H:i:s',WSTStrToTime($data['startTime'])).'" between startTime and endTime) or ( "'.date('Y-m-d H:i:s',WSTStrToTime($data['endTime'])).'" between startTime and endTime) ) ';
		$rn = Db::name('presales')->where($where)->where($whereOr)->count();
		if($rn>0)return WSTReturn(lang('presale_goods_is_in_same_time_activity'));
        $reduceMoney = (float)input('post.reduceMoney');
        if($reduceMoney<0)return WSTReturn(lang('presale_goods_reduce_money_can_not_minus'));
        if($chk['shopPrice']<$reduceMoney)return WSTReturn(lang('presale_reduce_money_must_less_goods_price'));
        $minPrice = $chk['shopPrice'];
        if($chk['isSpec']){
        	$minPrice = Db::name('goods_specs')->where(['shopId'=>$shopId,'goodsId'=>$data['goodsId'],'dataFlag'=>1])->min('specPrice');
        	if($minPrice<$reduceMoney)return WSTReturn(lang('presale_reduce_money_must_less_goods_price'));
        }
        if($data['saleType']==1 && $data['depositType']==1){
        	$dmoney = ($minPrice-$reduceMoney)*$data['depositRate']/100;
        	if($dmoney<0.01){
        		return WSTReturn(lang('presale_deposit_money_error_tips'));
        	}
        }
        if($data['goodsNum']<$data['limitNum'])return WSTReturn(lang('presale_can_buy_num_tips'));
        $data['deliverDays'] = ($data['saleType']==0)?$data['deliverDays0']:$data['deliverDays1'];
        if(WSTStrToTime($data['startTime']) > WSTStrToTime($data['endTime']))return WSTReturn(lang('presale_time_error_tips'));
		Db::startTrans();
		try{
			$data['createTime'] = date('Y-m-d h:i:s');
			$data['presaleStatus'] = 0;
			$data['dataFlag'] = 1;
			$data['isSale'] = 1;
			$result = null;
			$validate = new Validate;
			if (!$validate->scene(true)->check($data)) {
        		return WSTReturn($validate->getError());
        	}else{
			    $result = $this->allowField(true)->save($data,['id'=>$data['id'],'shopId'=>$shopId]);
			}
	    	if(false !== $result){
                Db::name('presales_langs')->where(['presaleId'=>$id])->delete();
                $dataLangs = [];
                foreach (WSTSysLangs() as $key => $v) {
                    $dataLang = [];
                    $dataLang['presaleId'] = $id;
                    $dataLang['langId'] = $v['id'];
                    $dataLang['goodsName'] = input('langParams'.$v['id'].'goodsName');
                    $dataLang['goodsSeoKeywords'] = input('langParams'.$v['id'].'goodsSeoKeywords');
                    $dataLang['goodsSeoDesc'] = input('langParams'.$v['id'].'goodsSeoDesc');
                    $dataLang['goodsTips'] = input('langParams'.$v['id'].'goodsTips');
                    $dataLangs[] = $dataLang;
                }
                Db::name('presales_langs')->insertAll($dataLangs);
				//WSTUseResource(0, $data['id'], $data['goodsImg']);
		        Db::commit();
		        return WSTReturn(lang('presale_operation_success'),1);
			}
		}catch (\Exception $e) {
			//print_r($e);
            Db::rollback();
        }
		return WSTReturn(lang('presale_operation_fail'));
	}

	/**
	 * 获取商品-用于编辑
	 */
	public function getById($sId=0){
		$id = (int)input('id');
		$shopId = ($sId==0)?(int)session('WST_USER.shopId'):$sId;
		$data = $this
            ->alias('p')
            ->join('__PRESALES_LANGS__ pl','pl.presaleId=p.id and pl.langId='.WSTCurrLang())
            ->field('p.*,pl.goodsName,pl.goodsTips,pl.goodsSeoKeywords,pl.goodsSeoDesc')
            ->where(['id'=>$id,'shopId'=>$shopId])->find();
		$goods = Db::name('goods')->where(['goodsId'=>$data['goodsId']])->field('isSpec,shopPrice')->find();
		$data['isSpec'] = $goods['isSpec'];
		$data['shopPrice'] = $goods['shopPrice'];
		$data['minShopPrice'] = $goods['shopPrice'];
	    $data['maxShopPrice'] = $goods['shopPrice'];
		if($goods['isSpec']){
			$spec = Db::name('goods_specs')->where(['goodsId'=>$data['goodsId'],'dataFlag'=>1])->field('max(specPrice) maxShopPrice,min(specPrice) minShopPrice')->find();
	        $data['minShopPrice'] = $spec['minShopPrice'];
	        $data['maxShopPrice'] = $spec['maxShopPrice'];
		}
        $data['langParams'] = Db::name('presales_langs')->where(['presaleId'=>$id])->column('*','langId');
		return $data;
	}


	/**
	 * 删除
	 */
	public function del($sId=0){
		$id = (int)input('id');
		$shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
		$data = [];
		$data['shopId'] = $shopId;
		$data['id'] = $id;
        Db::startTrans();
		try{
			$v = $this->where($data)->find();
			$time = time();
			if($v->presaleStatus==1 && (strtotime($v->startTime)<=$time && strtotime($v->endTime)>=$time))return WSTReturn(lang('presale_no_allow_delete'));
			$rs = $this->where($data)->update(['dataFlag'=>-1]);
			if($rs!==false){
				//WSTUnuseResource('presales','goodsImg',$id);
				Db::commit();
				return WSTReturn(lang('presale_operation_success'),1);
			}
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('presale_operation_fail'));
	}

	/**
	* 设置商品违规状态
	*/
	public function illegal(){
		$id = (int)input('post.id');
		if($id<=0)return WSTReturn(lang("presale_invalid_goods"));
		$illegalRemarks = input('post.illegalRemarks');
		if($illegalRemarks=='')return WSTReturn(lang("presale_require_illegal_reason"));
		//判断商品状态
		$rs = $this->alias('p')
            ->join('__PRESALES_LANGS__ pl','pl.presaleId=p.id and pl.langId='.WSTCurrLang())
            ->join('__GOODS__ g','g.goodsId=p.goodsId','inner')->where(['id'=>$id])->field('p.*,g.goodsSn,pl.goodsName')->find();
		if((int)$rs['presaleStatus']==-1)return WSTReturn(lang("presale_op_fail_and_status_is_change"));
		Db::startTrans();
		try{
			$res = $this->where('id',$id)->update(['presaleStatus'=>-1,'auditRemark'=>$illegalRemarks]);
			if($res!==false){
				//退款
				$this->unSaleRefund($id);
				//发送一条商家信息
				$tpl = WSTMsgTemplates('PRESALE_GOODS_REJECT');
		        if($tpl['tplContent']!='' && $tpl['status']=='1'){
		            $find = ['${GOODS}','${GOODS_SN}','${TIME}','${REASON}'];
		            $replace = [$rs['goodsName'],$rs['goodsSn'],date('Y-m-d H:i:s'),$illegalRemarks];

		            $msg = array();
		            $msg["shopId"] = $rs['shopId'];
		            $msg["tplCode"] = $tpl["tplCode"];
		            $msg["msgType"] = 1;
		            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
		            $msg["msgJson"] = ['from'=>7,'dataId'=>$id];
		            model("common/MessageQueues")->add($msg);
		        }
		        if((int)WSTConf('CONF.wxenabled')==1){
					$params = [];
					$params['GOODS'] = $rs['goodsName'];
					$params['GOODS_SN'] = $rs['goodsSn'];
					$params['TIME'] = date('Y-m-d H:i:s');
					$params['REASON'] = $illegalRemarks;

					$msg = array();
					$tplCode = "WX_PRESALE_GOODS_REJECT";
					$msg["shopId"] = $rs['shopId'];
		            $msg["tplCode"] = $tplCode;
		            $msg["msgType"] = 4;
		            $msg["paramJson"] = ['CODE'=>$tplCode,'params'=>$params];
		            $msg["msgJson"] = "";
		            model("common/MessageQueues")->add($msg);
				}
				Db::commit();
				return WSTReturn(lang('presale_operation_success'),1);
			}
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('presale_operation_fail'),-1);
	}
   /**
	* 通过商品审核
	*/
	public function allow(){
		$id = (int)input('post.id');
		if($id<=0)return WSTReturn(lang("presale_invalid_goods"));
		//判断商品状态
		$rs = $this->alias('p')
            ->join('__PRESALES_LANGS__ pl','pl.presaleId=p.id and pl.langId='.WSTCurrLang())
            ->join('__GOODS__ g','g.goodsId=p.goodsId','inner')->where(['id'=>$id])->field('p.*,g.goodsSn,pl.goodsName')->find();
		if((int)$rs['presaleStatus']!=0)return WSTReturn(lang("presale_op_fail_and_status_is_change"));
		Db::startTrans();
		try{
			$res = $this->where('id',$id)->update(['presaleStatus'=>1]);
			if($res!==false){
				//发送一条商家信息
				$tpl = WSTMsgTemplates('PRESALE_GOODS_ALLOW');
		        if($tpl['tplContent']!='' && $tpl['status']=='1'){
		            $find = ['${GOODS}','${GOODS_SN}','${TIME}'];
		            $replace = [$rs['goodsName'],$rs['goodsSn'],date('Y-m-d H:i:s')];

		            $msg = array();
		            $msg["shopId"] = $rs['shopId'];
		            $msg["tplCode"] = $tpl["tplCode"];
		            $msg["msgType"] = 1;
		            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
		            $msg["msgJson"] = ['from'=>7,'dataId'=>$id];
		            model("common/MessageQueues")->add($msg);
		        }
		        if((int)WSTConf('CONF.wxenabled')==1){
					$params = [];
					$params['GOODS'] = $rs['goodsName'];
					$params['GOODS_SN'] = $rs['goodsSn'];
					$params['TIME'] = date('Y-m-d H:i:s');

					$msg = array();
					$tplCode = "WX_PRESALE_GOODS_ALLOW";
					$msg["shopId"] = $rs['shopId'];
		            $msg["tplCode"] = $tplCode;
		            $msg["msgType"] = 4;
		            $msg["paramJson"] = ['CODE'=>$tplCode,'params'=>$params];
		            $msg["msgJson"] = "";
		            model("common/MessageQueues")->add($msg);
				}
				Db::commit();
				return WSTReturn(lang('presale_operation_success'),1);
			}
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('presale_operation_fail'),-1);
	}

	/**
     * 设置商品上下架
     */
    public function changeSale($sId = 0){
    	$shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
        $id = (int)input('id');
        $type = (int)input('type');
        $isSale = ($type==1)?1:0;
        $where = [];
        $where['shopId'] = $shopId;
        $where['id'] = $id;
        $rs = Db::name('presales')->where($where)->update(['isSale'=>$isSale]);
        return WSTReturn(lang('presale_operation_success'),1);
    }

    /**
	 * 删除预售商品
	 */
	public function delByAdmin(){
		Db::startTrans();
		try{
			$id = (int)input('id');
			$data = [];
			$data['id'] = $id;
	        $rs = $this->update(['dataFlag'=>-1],$data);
            //WSTUnuseResource('presales','goodsImg',$id);
	        if($rs){
	        	//退款
				$this->unSaleRefund($id);
	        }
	        Db::commit();
			return WSTReturn(lang('presale_operation_success'),1);
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('presale_operation_fail'),-1);
        }
	}

	/**
	 * 下架商品退款
	 */
	public function unSaleRefund($presaleId){
		$where = [];
		$where[] = ["presaleId",'=',$presaleId];
		$where[] = ["orderId",'=',0];
		Db::name("presale_orders")->where($where)->update(['presaleStatus'=>-1,'failType'=>2]);

		$where = [];
		$where[] = ["presaleId",'=',$presaleId];
		$where[] = ["orderId",'=',0];
		$where[] = ["failType",'=',2];
		$porders = Db::name('presale_orders po')
					->join("presale_moneys pm","pm.porderId=po.id","inner")
					->field("pm.*,po.goodsName,po.orderNo,po.realTotalMoney")
					->where($where)
					->select();
		for($i=0,$j=count($porders);$i<$j;$i++){
			$porder = $porders[$i];
			if($porder['payType']=='wallets' || $porder['payType']=='ccgwpays' || $porder['payType']=='others'){
	        	$rs = $this->savePresaleRefund($porder);
	        }else if(in_array($porder['payType'], ['weixinpays','app_weixinpays','alipays'])){
	        	Db::name('presale_moneys')->where(["id"=>$porder["id"],"refundStatus"=>0])->update(["refundStatus"=>1]);
	        }
		}
	}

	public function  savePresaleRefund($porder){
		$moneyType = $porder["moneyType"];
		$presaleMoney = $porder["presaleMoney"];
		$realTotalMoney = $porder["realTotalMoney"];
		$id = $porder["id"];
		$refundTime = date('Y-m-d H:i:s');
		$rs = Db::name('presale_moneys')->where(["id"=>$id,"refundStatus"=>0])->update(["refundStatus"=>2,"refundTime"=>$refundTime]);
		if($rs){
			//创建一条充值流水记录
			if($presaleMoney>0){
				$userId = $porder["userId"];
				//退钱包金额
				if($presaleMoney>0){
					$lm = [];
					$orderNo = $porder["orderNo"];
					$lm['targetType'] = 0;
					$lm['targetId'] = $userId;
					$lm['dataId'] = $orderNo;
					$lm['dataSrc'] = 1;
					$lm['remark'] = ($moneyType==1)?json_encode(['type'=>'lang_params','key'=>'presale_log_money_remark51','params'=>[$orderNo,$presaleMoney]]):json_encode(['type'=>'lang_params','key'=>'presale_log_money_remark52','params'=>[$orderNo,$presaleMoney]]);
					$lm['moneyType'] = 1;
					$lm['money'] = $presaleMoney;
					$lm['createTime'] = date('Y-m-d H:i:s');
					model('common/LogMoneys')->add($lm);
				}

				//发送一条用户信息
				WSTSendMsg($userId,WSTLang('presale_send_msg1',[$orderNo]).($moneyType==1?WSTLang('presale_log_money_remark6'):WSTLang('presale_log_money_remark7')).WSTLang("presale_send_msg2"),['from'=>1,'dataId'=>$id]);

				//微信消息
			    if((int)WSTConf('CONF.wxenabled')==1){
				    $params = [];
				    $params['GOODS'] = $porder['goodsName'];
			        $params['PRESALE_MONEY'] = $realTotalMoney;
				    $params['FREUND_MONEY'] = $presaleMoney;
		            WSTWxMessage(['CODE'=>'WX_PRESALE_REFUND','userId'=>$userId,'URL'=>Url('wechat/users/index','',true,true),'params'=>$params]);
			    }
		    }
		}

	}

    /**
     * 加载每个商品的规格
     */
    public function getGoodsSpecs($goodsIds){
        $specMap = [];
        $tmpSpecItem = [];
        //获取规格值
		$specs = Db::name('spec_cats')->alias('gc')
                ->join('__SPEC_ITEMS__ sit','gc.catId=sit.catId','inner')
                ->join('__SPEC_CATS_LANGS__ scl','gc.catId=scl.catId and scl.langId='.WSTCurrLang())
                ->join('__SPEC_ITEMS_LANGS__ sil','sit.itemId=sil.itemId and sil.langId='.WSTCurrLang())
                ->where([['sit.goodsId','in',$goodsIds],['gc.isShow','=',1],['sit.dataFlag','=',1]])
                ->field('gc.isAllowImg,scl.catName,sit.catId,sit.itemId,sil.itemName,sit.itemImg,sit.goodsId')
                ->order('gc.isAllowImg desc,gc.catSort asc,gc.catId asc')->select();
		foreach ($specs as $key =>$v){
			$specMap[$v['goodsId']]['specs'][$v['catId']]['name'] = $v['catName'];
			$specMap[$v['goodsId']]['specs'][$v['catId']]['list'][] = $v;
			$tmpSpecItem[$v['itemId']] = ['catName'=>$v['catName'],'itemName'=>$v['itemName']];
		}
        //获取销售规格
		$sales = Db::name('goods_specs')->where([['goodsId','in',$goodsIds]])->field('id,isDefault,productNo,specIds,marketPrice,specPrice,specStock,goodsId,specIds,specWeight,specVolume')->select();
		if(!empty($sales)){
			foreach ($sales as $key =>$v){
				//处理销售规格
				$str = explode(':',$v['specIds']);
				sort($str);
				if($v['isDefault']==1){
					$specMap[$v['goodsId']]['defaultSpecs'] = $v;
					$str = explode(':',$v['specIds']);
				    foreach ($str as $vv){
				    	if(isset($tmpSpecItem[$vv]))$specMap[$v['goodsId']]['items'][] = $tmpSpecItem[$vv];
				    }
				}
				$specMap[$v['goodsId']]['saleSpec'][implode(':',$str)] = $v;
			}
		}
		$data = ['-1'=>[]];
		foreach ($goodsIds as $key => $v) {
			if(isset($specMap[$v])){
				$goods = [];
				$goods['specs'] = $specMap[$v]['specs'];
				$goods['saleSpec'] = $specMap[$v]['saleSpec'];
		        $goods['defaultSpecs'] = $specMap[$v]['defaultSpecs'];
		        $goods['goodsSpecId'] = $specMap[$v]['defaultSpecs']['id'];
	            $data[$v] = $goods;
	        }
		}
        return $data;
    }



	/**
	 * 检测预售支付
	 */
	public function checkPayStatus($porderId,$uId=0){
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
        $rs = Db::name('presale_orders po')
                     ->where(['po.id'=>$porderId,'po.userId'=>$userId])
                     ->find();
        if(!empty($rs)){
        	$now = date("Y-m-d H:i:s");
        	$presale = Db::name("presales")->where(['id'=>$rs['presaleId']])->find();
        	if($rs['presaleStatus']==0){
        		if($presale['startTime']<=$now && $presale['endTime']>=$now){
	        		$rs['isCanPay'] = 1;
	        	}else{
	        		$rs['isCanPay'] = 0;
	        	}
        	}else if($rs['presaleStatus']==1){
        		if($rs['startPayTime']<=$now && $rs['endPayTime']>=$now){
	        		$rs['isCanPay'] = 1;
	        	}else{
	        		$rs['isCanPay'] = 0;
	        	}
        	}else{
        		$rs['isCanPay'] = 0;
        	}
        }
        return $rs;
	}

	/**
	 * 获取预售购买
	 */
	public function getPresalePay($porderId,$uId=0){
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
        $rs = Db::name('presale_orders po')
        			->join('__GOODS__ g','po.goodsId=g.goodsId','inner')
                    ->where(['po.id'=>$porderId,'po.userId'=>$userId])
                    ->field("po.*,g.goodsImg")
                    ->find();
        if(!empty($rs)){
        	$now = date("Y-m-d H:i:s");
        	$presale = Db::name("presales")->where(['id'=>$rs['presaleId']])->find();
        	if($rs['presaleStatus']==0){
        		if($presale['startTime']<=$now && $presale['endTime']>=$now){
	        		$rs['isCanPay'] = 1;
	        	}else{
	        		$rs['isCanPay'] = 0;
	        	}
        	}else if($rs['presaleStatus']==1){
        		if($rs['startPayTime']<=$now && $rs['endPayTime']>=$now){
	        		$rs['isCanPay'] = 1;
	        	}else{
	        		$rs['isCanPay'] = 0;
	        	}
        	}else{
        		$rs['isCanPay'] = 0;
        	}
        }
        return $rs;
	}


	/**
	 * 支付定金信息
	 */
	public function getPayInfo($porderId,$payfor,$uId=0){
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		$data = [];
		$porder = Db::name('presale_orders po')
                     ->where(['po.id'=>$porderId,'po.userId'=>$userId])
                     ->find();

       	$presale = Db::name('presales')
                    ->alias('p')
                    ->join('__PRESALES_LANGS__ pl','pl.presaleId=p.id and pl.langId='.WSTCurrLang())
                    ->field('p.*,pl.goodsName')
                    ->where(['id'=>$porder['presaleId']])->find();

		//判断时间是否合适
		$time = time();
		if($payfor !=2 && (strtotime($presale['startTime']) > $time || strtotime($presale['endTime']) < $time)){
			return WSTReturn(lang('presale_illegal_op'));
		}
		$porder['goodsImg'] = $presale['goodsImg'];
		$data["porder"] = $porder;
		$data["presale"] = $presale;

		//获取支付信息
		$data['payments'] = $this->getPayments();
		return WSTReturn('',1,$data);
	}

	public function getPayments(){
		//获取支付信息
		$payments = Db::name('payments')->where(['isOnline'=>1,'enabled'=>1])->order('payOrder asc')->select();
		return $payments;
	}



	/**
	 * 定时任务
	 */
	public function scanTask(){

		//计算逾期未支付预售，没收定金
		$date = date('Y-m-d H:i:s');
		$dbo = Db::name("presale_orders")->where('endPayTime<"'.$date.'" and presaleStatus=1');
		$dbo->limit(100);
		$rs = $dbo->select();

		if(!empty($rs)){
			Db::startTrans();
		    try{
			    foreach($rs as $key =>$v){

				    $shop = Db::name('shops')->where('shopId',$v['shopId'])->field('userId')->find();
				    //将定金划拨给商家
				    $lm = [];
					$lm['targetType'] = 1;
					$lm['targetId'] = $v['shopId'];
					$lm['dataId'] = $v['id'];
					$lm['dataSrc'] = 'presale';
					$lm['remark'] = json_encode(['type'=>'lang_params','key'=>'presale_log_money_remark8','params'=>[$v['orderNo'],$v['goodsName'],$v['depositMoney']]]);
					$lm['moneyType'] = 1;
					$lm['money'] = $v['depositMoney'];
					$lm['payType'] = '0';
					$lm['tradeNo'] = '';
					$lm['createTime'] = date('Y-m-d H:i:s');
					model('common/LogMoneys')->add($lm);
					//改变定金状态
					Db::name('presale_orders')->where('id',$v['id'])->update(['presaleStatus'=>-1,'failType'=>1]);
				    //发送系统消息-商家
		            $tpl = WSTMsgTemplates('PRESALE_SHOP_PAY_OVERTIME');
				    if($tpl['tplContent']!='' && $tpl['status']=='1'){
				        $find = ['${GOODS}','${PRESALENO}','${RESULT}'];
				        $replace = [$v['goodsName'],$v['orderNo'],WSTLang('presale_send_msg3')];

				    	$msg = array();
			            $msg["shopId"] = $v['shopId'];
			            $msg["tplCode"] = $tpl["tplCode"];
			            $msg["msgType"] = 1;
			            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
			            $msg["msgJson"] = ['from'=>'presale','dataId'=>$v['id']];
			            model("common/MessageQueues")->add($msg);
				    }
				    //发送系统消息-用户
				    $tpl = WSTMsgTemplates('PRESALE_USER_PAY_OVERTIME');
					if($tpl['tplContent']!='' && $tpl['status']=='1'){
					    $find = ['${GOODS}','${PRESALENO}','${RESULT}'];
					    $replace = [$v['goodsName'],$v['orderNo'],WSTLang('presale_send_msg3')];
					    WSTSendMsg($v['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>'presale','dataId'=>$v['id']]);
				    }
				    //发送微信消息-商家
				    if((int)WSTConf('CONF.wxenabled')==1){
				        $params = [];
				        $params['GOODS'] = $v['goodsName'];
			            $params['PRESALENO'] = $v['orderNo'];
			            $params['RESULT'] = WSTLang('presale_send_msg3');

					    $msg = array();
						$tplCode = "WX_PRESALE_SHOP_PAY_OVERTIME";
						$msg["shopId"] = $v['shopId'];
			            $msg["tplCode"] = $tplCode;
			            $msg["msgType"] = 4;
			            $msg["paramJson"] = ['CODE'=>$tplCode,'params'=>$params];
			            $msg["msgJson"] = "";
			            model("common/MessageQueues")->add($msg);

					    $params = [];
				        $params['GOODS'] = $v['goodsName'];
				        $params['PRESALENO'] = $v['orderNo'];
			            $params['RESULT'] = WSTLang('presale_send_msg3');
					    WSTWxMessage(['CODE'=>'WX_PRESALE_USER_PAY_OVERTIME','userId'=>$v['userId'],'params'=>$params]);
					}
			    }
			    Db::commit();
			}catch (\Exception $e) {
			    Db::rollback();
			}
		}
		return WSTReturn(lang('presale_operation_success'),1);
	}

	/**
	 * 取消期未支付定金的订单，返回库存
	 */
	public function scanCancelOrder(){
		$autoCancelNoPayDays = (int)WSTConf('CONF.autoCancelNoPayDays');
	 	$autoCancelNoPayDays = ($autoCancelNoPayDays>0)?$autoCancelNoPayDays:6;
	 	$lastDay = date("Y-m-d H:i:s",strtotime("-".$autoCancelNoPayDays." hours"));
	 	$orders = Db::name('presale_orders o')
				 	->where([['o.createTime','<',$lastDay],['o.presaleStatus','=',0]])
				 	->field("o.id,o.orderNo,o.userId,o.presaleId,o.goodsNum")
				 	->select();

	 	if(!empty($orders)){
	 		$prefix = config('database.prefix');
	 		$orderIds = [];
	 		foreach ($orders as $okey => $order){
	 			$orderIds[] = $order['id'];
	 		}
	 		Db::startTrans();
		    try{
		    	//提前锁定订单
		    	Db::name('presale_orders')->where([['id','in',$orderIds]])->update(['presaleStatus'=>-1,'realTotalMoney'=>0]);
		    	echo $this->getLastSql();
                foreach ($orders as $okey => $order){
                	Db::name('presales')->where('id',$order['presaleId'])->setDec('orderNum',$order['goodsNum']);
                    //发送消息
	                $tpl = WSTMsgTemplates('PRESALE_ORDER_USER_PAY_TIMEOUT');
	                if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	                    $find = ['${ORDER_NO}'];
	                    $replace = [$order['orderNo']];
	                    //发送一条用户信息
					    WSTSendMsg($order['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>1,'dataId'=>$order['id']]);
	                }

	                //微信消息
		            if((int)WSTConf('CONF.wxenabled')==1){
		            	$params = [];
		                $params['ORDER_NO'] = $order['orderNo'];
	                    WSTWxMessage(['CODE'=>'WX_PRESALE_ORDER_USER_PAY_TIMEOUT','userId'=>$order['userId'],'URL'=>addon_url('presale://users/wxlist','',true,true),'params'=>$params]);

		            }
                }

		        Db::commit();
				return WSTReturn(lang('presale_operation_success'),1);
	 		}catch (\Exception $e) {
	 			//print_r($e);
	            Db::rollback();
	            return WSTReturn(lang('presale_operation_fail'),-1);
	        }
	 	}
		return WSTReturn(lang('presale_operation_success'),1);
	}

	/**
	 * 提醒支付尾款
	 */
	public function scanPayNotice(){
		$now = date("Y-m-d H:i:s");
		$where = [];
		$where[] = ["dataFlag","=",1];
		$where[] = ["presaleStatus","=",1];
		$where[] = ["endTime","<=",$now];
		$list = Db::name("presales")->where($where)->field("id,endTime")->limit(100)->select();
		foreach ($list as $key => $v) {
			$olist = Db::name("presale_orders")->where(["presaleId"=>$v['id'],"presaleStatus"=>1])->field("id,goodsName,orderNo,userId")->select();
			foreach ($olist as $key2 => $v2) {
				//发送系统消息-用户
			    $tpl = WSTMsgTemplates('PRESALE_END');
				if($tpl['tplContent']!='' && $tpl['status']=='1'){
				    $find = ['${GOODS}','${ORDERNO}'];
				    $replace = [$v2['goodsName'],$v2['orderNo']];
				    WSTSendMsg($v2['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>'presale','dataId'=>$v2['id']]);
			    }
			    //发送微信消息-用户
			    if((int)WSTConf('CONF.wxenabled')==1){
				    $params = [];
			        $params['GOODS'] = $v2['goodsName'];
			        $params['ORDERNO'] = $v2['orderNo'];
		            $params['END_TIME'] = $v['endTime'];
				    WSTWxMessage(['CODE'=>'WX_PRESALE_END','userId'=>$v2['userId'],'params'=>$params]);
				}
			}
		}
		return WSTReturn(lang('presale_operation_success'),1);
	}

	/**
	 * 退款
	 */
	public function batchRefund(){
		$pmoneys = Db::name('presale_moneys')->where(['refundStatus'=>1])->select();
		$am = null;
		$wm = null;
		$pm = null;
		for($i=0,$j=count($pmoneys);$i<$j;$i++){
			$pmoney = $pmoneys[$i];
			if($pmoney['payType']=='weixinpays'){
				if($wm==null) $wm = new Weixinpays();
				$wm->presaleRefund($pmoney);
			}else if($pmoney['payType']=='app_weixinpays'){
				if($pm==null) $pm = new WeixinpaysApp();
				$pm->presaleRefund($pmoney);
			}else if($pmoney['payType']=='alipays'){
				if($am==null) $am = new Alipays();
				$am->presaleRefund($pmoney);
			}
		}
	}
}
