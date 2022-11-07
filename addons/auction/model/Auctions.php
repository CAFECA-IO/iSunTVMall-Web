<?php
namespace addons\auction\model;
use think\addons\BaseModel as Base;
use wstmart\common\model\GoodsCats;
use think\Db;
use wstmart\common\model\LogSms;
use addons\auction\model\Weixinpays;
use addons\auction\model\WeixinpaysApp;
use addons\auction\model\Alipays;
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
 * 拍卖活动插件
 */
class Auctions extends Base{
	protected $pk = 'auctionId';
	/***
     * 安装插件
     */
   
    public function installMenu(){
    	Db::startTrans();
		try{
			$hooks = ['beforeCancelOrder','wechatDocumentUserIndexTools','mobileDocumentUserIndexTools','adminDocumentHookSummary','mobileDocumentIndex','wechatDocumentIndex'];
			$this->bindHoods("Auction", $hooks);
			//管理员后台
            $datas = [];
            $menuLangParams = [
                1=>['menuName'=>'拍賣活動'],
                2=>['menuName'=>'拍卖活动'],
                3=>['menuName'=>'Auction'],
            ];
            $menuId = Db::name('menus')->insertGetId(["parentId"=>93,"menuSort"=>1,"dataFlag"=>1,"isShow"=>1,"menuMark"=>"auction"]);
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
                    1=>['privilegeName_00'=>'查看拍賣活動','privilegeName_04'=>'拍賣活動操作','privilegeName_03'=>'删除拍卖商品'],
                    2=>['privilegeName_00'=>'查看拍卖活动','privilegeName_04'=>'拍卖商品操作','privilegeName_03'=>'删除拍卖商品'],
                    3=>['privilegeName_00'=>'View Auction','privilegeName_04'=>'Auction operation','privilegeName_03'=>'Delete Auction items'],
                ];
                $privilegeIds = [];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"AUCTION_PMHD_00","isMenuPrivilege"=>1,"privilegeUrl"=>"/addon/auction-goods-pageByAdmin","otherPrivilegeUrl"=>"/addon/auction-goods-pageQueryByAdmin,/addon/auction-goods-pageAuditQueryByAdmin,/addon/auction-goods-auctionLogByAdmin,,/addon/auction-goods-pageAuctionLogQueryByAdmin","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'00'];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"AUCTION_PMHD_04","isMenuPrivilege"=>0,"privilegeUrl"=>"","otherPrivilegeUrl"=>"/addon/auction-goods-allow,/addon/auction-goods-illegal","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'04'];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"AUCTION_PMHD_03","isMenuPrivilege"=>0,"privilegeUrl"=>"/addon/auction-goods-delByAdmin","otherPrivilegeUrl"=>"","dataFlag"=>1,"isEnable"=>1]);
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

			$now = date("Y-m-d H:i:s");
			//商家中心
            $homeMenuLangParams = [
                1=>['menuName_01'=>'拍賣活動','menuName_02'=>'我參與的拍賣','menuName_03'=>'我的保證金'],
                2=>['menuName_01'=>'拍卖活动','menuName_02'=>'我参与的拍卖','menuName_03'=>'我的保证金'],
                3=>['menuName_01'=>'Auction','menuName_02'=>'My Auction','menuName_03'=>'My bond'],
            ];
            $homeMenuIds = [];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>77,"menuUrl"=>"/addon/auction-shops-auction","menuOtherUrl"=>"/addon/auction-shops-auction,/addon/auction-shops-pageQuery,/addon/auction-shops-searchGoods,/addon/auction-shops-edit,/addon/auction-shops-toEdit,/addon/auction-shops-del","menuType"=>1,"isShow"=>1,"menuSort"=>1,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"auction"]);
            $homeMenuIds[] = ['homeMenuId'=>$homeMenuId,'code'=>'01'];
            //用户中心
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>100,"menuUrl"=>"addon/auction-users-auction","menuOtherUrl"=>"addon/auction-users-pageQuery","menuType"=>0,"isShow"=>1,"menuSort"=>1,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"auction"]);
            $homeMenuIds[] = ['homeMenuId'=>$homeMenuId,'code'=>'01'];
            $parentId = $homeMenuId;
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>$parentId,"menuUrl"=>"addon/auction-users-auction","menuOtherUrl"=>"addon/auction-users-pageQuery,addon/auction-users-checkPayStatus,addon/auction-users-submit","menuType"=>0,"isShow"=>1,"menuSort"=>1,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"auction"]);
            $homeMenuIds[] = ['homeMenuId'=>$homeMenuId,'code'=>'02'];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>$parentId,"menuUrl"=>"addon/auction-users-money","menuOtherUrl"=>"addon/auction-users-pageQueryByMoney","menuType"=>0,"isShow"=>1,"menuSort"=>1,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"auction"]);
            $homeMenuIds[] = ['homeMenuId'=>$homeMenuId,'code'=>'03'];
            $datas = [];
            for($i=0;$i<count($homeMenuIds);$i++){
                foreach (WSTSysLangs() as $key => $v) {
                    $data = [];
                    $data['menuId'] = $homeMenuIds[$i]['homeMenuId'];
                    $data['langId'] = $v['id'];
                    $data['menuName'] = $homeMenuLangParams[$v['id']]['menuName_'.$homeMenuIds[$i]['code']];
                    $datas[] = $data;
                }
            }
            Db::name('home_menus_langs')->insertAll($datas);

            //新增上传目录
            $dataLangParams = [
                1=>[
                    'dataName_00'=>'拍賣活動',
                    'dataName_01'=>'拍賣商品審核通過',
                    'dataName_02'=>'拍賣商品審核不通過',
                    'dataName_03'=>'拍賣結果提醒(用户)',
                    'dataName_04'=>'拍賣結果提醒(商家)',
                ],
                2=>[
                    'dataName_00'=>'拍卖活动',
                    'dataName_01'=>'拍卖商品审核通过',
                    'dataName_02'=>'拍卖商品审核不通过',
                    'dataName_03'=>'拍卖结果提醒(用户)',
                    'dataName_04'=>'拍卖结果提醒(商家)',
                ],
                3=>[
                    'dataName_00'=>'Auction',
                    'dataName_01'=>'Goods approved for auction',
                    'dataName_02'=>'Goods failed to pass the auction',
                    'dataName_03'=>'Auction result reminder(User)',
                    'dataName_04'=>'Auction result reminder(Shop)',
                ],
            ];
            $dataIds = [];
            $dataId = Db::name('datas')->insertGetId(['catId'=>3,'dataVal'=>'auction']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'00'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>6,'dataVal'=>'AUCTION_GOODS_ALLOW']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'01'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>6,'dataVal'=>'AUCTION_GOODS_REJECT']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'02'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>6,'dataVal'=>'AUCTION_USER_RESULT']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'03'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>6,'dataVal'=>'AUCTION_SHOP_RESULT']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'04'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>9,'dataVal'=>'WX_AUCTION_GOODS_ALLOW']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'01'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>9,'dataVal'=>'WX_AUCTION_GOODS_REJECT']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'02'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>9,'dataVal'=>'WX_AUCTION_USER_RESULT']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'03'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>9,'dataVal'=>'WX_AUCTION_SHOP_RESULT']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'04'];
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

			installSql("auction");
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
			$hooks = ['beforeCancelOrder','wechatDocumentUserIndexTools','mobileDocumentUserIndexTools','adminDocumentHookSummary','mobileDocumentIndex','wechatDocumentIndex'];
			$this->unbindHoods("Auction", $hooks);
            $menuId = Db::name('menus')->where(["menuMark"=>"auction"])->value('menuId');
            Db::name('menus')->where(["menuMark"=>"auction"])->delete();
            Db::name('menus_langs')->where(['menuId'=>$menuId])->delete();

            $homeMenuIds = Db::name('home_menus')->where(["menuMark"=>"auction"])->column('menuId');
            Db::name('home_menus')->where(["menuMark"=>"auction"])->delete();
            Db::name('home_menus_langs')->where([['menuId','in',$homeMenuIds]])->delete();

            $privilegeIds = Db::name('privileges')->where("privilegeCode","like","AUCTION_%")->column('privilegeId');
            Db::name('privileges')->where("privilegeCode","like","AUCTION_%")->delete();
            Db::name('privileges_langs')->where([['privilegeId','in',$privilegeIds]])->delete();

            //删除微信参数数据
			$tplMsgIds = Db::name('template_msgs')->where([['tplCode','in',explode(',','AUCTION_GOODS_ALLOW,AUCTION_GOODS_REJECT,WX_AUCTION_GOODS_ALLOW,WX_AUCTION_GOODS_REJECT,AUCTION_USER_RESULT,AUCTION_SHOP_RESULT,WX_AUCTION_USER_RESULT,WX_AUCTION_SHOP_RESULT')]])
			  ->column('id');
			if((int)WSTConf('CONF.wxenabled')==1)Db::name('wx_template_params')->where([['parentId','in',$tplMsgIds]])->delete();
            $dataId = Db::name('datas')->where(["dataVal"=>"auction"])->value('id');
            Db::name('datas')->where(["dataVal"=>"auction"])->delete();
            Db::name('datas_langs')->where(["dataId"=>$dataId])->delete();

            $dataIds = Db::name('datas')->where([["dataVal",'like',"%AUCTION%"]])->column('id');
            Db::name('datas')->where([["dataVal",'like',"%AUCTION%"]])->delete();
            Db::name('datas_langs')->where([["dataId","in",$dataIds]])->delete();
			uninstallSql("auction");//传入插件名
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
			Db::name('menus')->where(["menuMark"=>"auction"])->update(["isShow"=>$isShow]);
			Db::name('home_menus')->where(["menuMark"=>"auction"])->update(["isShow"=>$isShow]);
			Db::name('navs')->where(["navUrl"=>"addon/auction-goods-lists.html"])->update(["isShow"=>$isShow]);
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
            'navUrl'=>'addon/auction-goods-lists.html',
            'isShow'=>1,
            'isOpen'=>0,
            'navSort'=>0,
            'createTime'=>date('Y-m-d H:i:s')
        ];
        $navId = Db::name('navs')->insertGetId($navData);
        $datas = [];
        $langParams = [
            1=>['navTitle'=>'拍賣活動'],
            2=>['navTitle'=>'拍卖活动'],
            3=>['navTitle'=>'Auction'],
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
            1=>['btnName'=>'拍賣活動'],
            2=>['btnName'=>'拍卖活动'],
            3=>['btnName'=>'Auction'],
        ];
        $datas = [];
		$data = array();
		$data["btnSrc"] = 0;
		$data["btnUrl"] = "addon/auction-goods-molists.html";
		$data["btnImg"] = "addons/auction/view/images/auction.png";
		$data["addonsName"] = "Auction";
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
		$data["btnUrl"] = "addon/auction-goods-wxlists.html";
		$data["btnImg"] = "addons/auction/view/images/auction.png";
		$data["addonsName"] = "Auction";
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
			$data["btnUrl"] = "wst://Auction";
			$data["btnImg"] = "addons/auction/view/images/auction.png";
			$data["addonsName"] = "Auction";
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
		}

		// 小程序端
		if(WSTDatas('ADS_TYPE',5)){
            $datas = [];
			$data = array();
			$data["btnSrc"] = 2;
			$data["btnUrl"] = "/addons/package/pages/auction/goods/list";
			$data["btnImg"] = "addons/auction/view/images/auction.png";
			$data["addonsName"] = "Auction";
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
		}
	}

    public function delNavMenu(){
        $navId = Db::name('navs')->where(['navUrl'=>'addon/auction-goods-lists.html'])->value('id');
        Db::name('navs')->where(['navUrl'=>'addon/auction-goods-lists.html'])->delete();
        Db::name('navs_langs')->where(['navId'=>$navId])->delete();
    }

    public function delMobileBtn(){
        $btnIds =  Db::name('mobile_btns')->where(["addonsName"=>"Auction"])->column('id');
        Db::name('mobile_btns')->where(["addonsName"=>"Auction"])->delete();
        Db::name('mobile_btns_langs')->where([['btnId','in',$btnIds]])->delete();
    }

	/**
	 * 取消团购订单
	 */
	public function beforeCancelOrder($params){
		$order = DB::name('orders')->where('orderId',$params['orderId'])->field('orderCode')->find();
		if($order['orderCode']=='auction'){
			$isWeapp = (int)input("isWeapp");
			if($isWeapp==1){
				$data = ["status"=>-1,"msg"=>lang('auction_order_cannot_cancel')];
				die(json_encode($data));
			}else{
				die('{"status":-1,"msg":"'.lang('auction_order_cannot_cancel').'"}');
			}
		}
	}

	/**
     * 商家获取拍卖列表
     */
	public function pageQueryByShop($sId=0){
		$goodsName = input('goodsName');
		$auctionStatus=input('auctionStatus');
		$shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
		$where = [];
		$where[]=['au.shopId','=',$shopId];
		$where[]=['au.dataFlag','=',1];
		$where[]=['g.dataFlag','=',1];
		if($goodsName !='')$where[] = ['aul.goodsName|g.goodsSn', 'like', '%'.$goodsName.'%'];
		if($auctionStatus!='')$where[]=['au.auctionStatus','=',$auctionStatus];
        $page =  $this->alias('au')
                      ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
                      ->field('au.*,aul.goodsName')
                      ->where($where)
                      ->join('__GOODS__ g','g.goodsId=au.goodsId','left')
                      ->order('au.updateTime desc')
                      ->paginate(input('limit/d'))->toArray();
        if(count($page['data'])>0){
        	$time = time();
        	foreach($page['data'] as $key =>$v){
        		$page['data'][$key]['goodsImg'] = WSTImg($v['goodsImg']);
        		if(strtotime($v['startTime'])<=$time && strtotime($v['endTime'])>=$time){
        			$page['data'][$key]['status'] = 1;
        		}else if(strtotime($v['startTime'])>$time){
                    $page['data'][$key]['status'] = 0;
        		}else{
        			$page['data'][$key]['status'] = -1;
        		}
        		$page['data'][$key]['editable'] = ($v['auctionNum']==0 && $v['isClose']==0 )?1:0;
        	}
        }

        $page['status'] = 1;
        return $page;
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
        $where['auctionId'] = $id;
        $where['shopId'] = $shopId;
        $rs = Db::name('auctions')->where($where)->update(['isSale'=>$isSale]);
        if($isSale==1){
            return WSTReturn(lang('auction_onsale_success'),1);
        }else{
            return WSTReturn(lang('auction_unsale_success'),1);
        }
    }

    /**
     * 搜索商品
     */
    public function searchGoods($sId=0){
    	$shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
    	$shopCatId1 = (int)input('post.shopCatId1');
    	$shopCatId2 = (int)input('post.shopCatId2');
    	$goodsName = input('post.goodsName');
    	$where =[];
    	$where[]=['goodsStatus','=',1];
    	$where[]=['dataFlag','=',1];
    	$where[]=['goodsType','=',0];
    	$where[]=['shopId','=',$shopId];
    	if($shopCatId1>0)$where[] =['shopCatId1','=',$shopCatId1];
    	if($shopCatId2>0)$where[] =['shopCatId2','=',$shopCatId2];
    	if($goodsName !='')$where[] = ['gl.goodsName', 'like', '%'.$goodsName.'%'];
    	$rs = Db::name('goods')
            ->alias('g')
            ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
            ->where($where)->field('gl.goodsName,g.goodsId,marketPrice,shopPrice,gl.goodsSeoKeywords,gl.goodsSeoDesc,goodsImg')->select();
        return WSTReturn('',1,$rs);
    }

	/**
	 *  获取拍卖商品
	 */
	public function getById($id,$sId=0){
        $isApp = (int)input("post.isApp",0);
		$where = [];
		$where['au.shopId']=($sId==0)?(int)session('WST_USER.shopId'):$sId;
		$where['au.auctionId'] = $id;
		$where['au.dataFlag'] = 1;
		$where['au.dataFlag'] = 1;
		$rs =  $this->alias('au')
            ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
            ->join('__GOODS__ g','g.goodsId=au.goodsId','left')->where($where)->field('aul.goodsName,aul.auctionDesc,au.goodsImg,g.marketPrice,g.shopPrice,au.*')->find()->toArray();
        $resourceDomain = ($isApp==1)?WSTConf('CONF.resourceDomain'):WSTConf('CONF.resourcePath');
        $rs['langParams'] = Db::name('auctions_langs')->where(['auctionId'=>$id])->column('*','langId');
        foreach ($rs['langParams'] as $key => $lv) {
            $lv['auctionDesc'] = htmlspecialchars_decode($lv['auctionDesc']);
            $rs['langParams'][$key]['auctionDesc'] = str_replace('${DOMAIN}',$resourceDomain,$lv['auctionDesc']);
        }
        return $rs;
	}

	/**
	 * 获取规格数组
	 */
	public function getSpecs($goodsId){
		$sales = Db::name('goods_specs')->where(['goodsId'=>$goodsId,'isDefault'=>1])->field('specIds')->find();
		$specIds = [];
		if(!empty($sales)){
			$specIds = explode(':',$sales['specIds']);
			sort($specIds);
		}
		$spec = [];
		//获取默认规格值
		$specs = Db::name('spec_cats')->alias('gc')
				   ->join('__SPEC_ITEMS__ sit','gc.catId=sit.catId','inner')
                   ->join('__SPEC_CATS_LANGS__ scl','gc.catId=scl.catId and scl.langId='.WSTCurrLang())
                   ->join('__SPEC_ITEMS_LANGS__ sil','sit.itemId=sil.itemId and sil.langId='.WSTCurrLang())
				   ->where(['sit.goodsId'=>$goodsId,'gc.isShow'=>1,'sit.dataFlag'=>1])
				   ->field('gc.isAllowImg,scl.catName,sit.catId,sit.itemId,sil.itemName,sit.itemImg')
				   ->order('gc.isAllowImg desc,gc.catSort asc,gc.catId asc')->select();
		foreach ($specs as $key =>$v){
			if(in_array($v['itemId'],$specIds)){
				$catId = $v['catId'];
				$spec[$catId]['name'] = $v['catName'];
				unset($v['catName'],$v['catId']);
				$spec[$catId]['list'][] = $v;
			}
		}
		return $spec;
	}

	/**
	 * 新增拍卖
	 */
	public function add($sid=0){
		$data = input('post.');
        $isApp = (int)input('post.isApp',0);
		$shopId =($sid==0)?(int)session('WST_USER.shopId'):$sid;
		$goods = model('common/Goods')->get(['goodsId'=>(int)$data['goodsId']]);
		if(empty($goods))return WSTReturn(lang('auction_goods_not_exist'));
        foreach (WSTSysLangs() as $key => $v) {
            $goodsName = input('langParams' . $v['id'] . 'goodsName');
            if ($goodsName == '') {
                return WSTReturn(lang('auction_require_goods_name'));
            }
        }
		if($goods->goodsStatus!=1 || $goods->goodsType!=0 || $goods->dataFlag!=1 || $goods->shopId != $shopId)return WSTReturn(lang('auction_invalid_goods'));
		if((int)$data['auctionPrice']<0)return WSTReturn(lang('auction_start_price_limit'));
		if((int)$data['fareInc']<=0)return WSTReturn(lang('auction_add_price_limit'));
		if($data['startTime']=='' || $data['endTime']=='')return WSTReturn(lang('auction_require_time'));
		if(strtotime($data['startTime']) >= strtotime($data['endTime']))return WSTReturn(lang('auction_time_limit_1'));
		//判断是否已经存在同时间的拍卖
		$where = [];
		$where['goodsId'] = (int)$data['goodsId'];
		$where['dataFlag'] = 1;
		$whereOr = ' ( ("'.date('Y-m-d H:i:s',strtotime($data['startTime'])).'" between startTime and endTime) or ( "'.date('Y-m-d H:i:s',strtotime($data['endTime'])).'" between startTime and endTime) ) ';
		$rn = $this->where($where)->where($whereOr)->Count();
		if($rn>0)return WSTReturn(lang('auction_goods_exist_other_tips'));
		WSTUnset($data,'auctionId,cat_0,illegalRemarks');
		$specs = [];
		if($goods->isSpec==1){
			$specs = $this->getSpecs($goods->goodsId);
		}
		$resourceDomain = ($isApp==1)?WSTConf('CONF.resourceDomain'):WSTConf('CONF.resourcePath');

		$data['shopId'] = $shopId;
		$data['goodsVideo'] = $goods->goodsVideo;
		$data['goodsJson'] = json_encode(['gallery'=>$goods->gallery,'specs'=>$specs]);
		$data['dataFlag'] = 1;
		$data['orderNum'] = 0;
		$data['currPrice'] = $data['auctionPrice'];
		$data['auctionStatus'] = 0;
		$data['updateTime'] = date('Y-m-d H:i:s');
		$data['createTime'] = date('Y-m-d H:i:s');
		Db::startTrans();
		try{
			$result = $this->allowField(true)->save($data);
			if(false !== $result){
	            $auctionId = $this->auctionId;
                $dataLangs = [];
                foreach (WSTSysLangs() as $key => $v) {
                    $dataLang = [];
                    $dataLang['auctionId'] = $auctionId;
                    $dataLang['langId'] = $v['id'];
                    $dataLang['goodsName'] = input('langParams'.$v['id'].'goodsName');
                    $dataLang['goodsSeoKeywords'] = input('langParams'.$v['id'].'goodsSeoKeywords');
                    $dataLang['goodsSeoDesc'] = input('langParams'.$v['id'].'goodsSeoDesc');
                    $dataLang['auctionDesc'] = input('langParams'.$v['id'].'auctionDesc');
                    //对图片域名进行处理
                    $dataLang['auctionDesc'] = htmlspecialchars_decode($dataLang['auctionDesc']);
                    $dataLang['auctionDesc'] = WSTRichEditorFilter($dataLang['auctionDesc']);
                    $dataLang['auctionDesc'] = str_replace($resourceDomain.'/upload/','${DOMAIN}/upload/',$dataLang['auctionDesc']);
                    $dataLangs[] = $dataLang;
                }
                Db::name('auctions_langs')->insertAll($dataLangs);
                //WSTUseResource(0, $auctionId, $data['goodsImg']);
	            //拍卖描述图片
	            //WSTEditorImageRocord(0, $auctionId, '',$auctionDesc);
	            Db::commit();
				return WSTReturn(lang('auction_operation_success'),1);
			}
		}catch (\Exception $e) {
			Db::rollback();
		}
		return WSTReturn(lang('auction_operation_fail'));
	}

	/**
	 * 编辑拍卖
	 */
	public function edit($sid=0){
		$data = input('post.');
        $isApp = (int)input('post.isApp');
		$shopId =($sid==0)?(int)session('WST_USER.shopId'):$sid;
		$auctionId = $data['auctionId'];
		$auction = $this->get($auctionId);
		if($auction->shopId!=$shopId)return WSTReturn(lang('auction_invalid_record'));
		if($auction->isClose==1)return WSTReturn(lang('auction_overtime_cannot_edit'));
		if($auction->auctionNum>0)return WSTReturn(lang('auction_has_user_cannot_edit'));
		//如果有改变商品则更新内容
		if($auction->goodsId!=(int)$data['goodsId']){
			$goods = model('common/Goods')->get(['goodsId'=>(int)$data['goodsId']]);
			if(empty($goods))return WSTReturn(lang('auction_goods_not_exist'));
		    if($goods->goodsStatus!=1 || $goods->goodsType!=0 || $goods->dataFlag!=1 || $goods->shopId != $shopId)return WSTReturn(lang('auction_invalid_goods'));
		    $specs = [];
			if($goods->isSpec==1){
				$specs = $this->getSpecs($goods->goodsId);
			}
			$auction->goodsId = $goods->goodsId;
		    $auction->goodsImg = $goods->goodsImg;
		    $auction->goodsVideo = $goods->goodsVideo;
		    $auction->goodsJson = json_encode(['gallery'=>$goods->gallery,'specs'=>$specs]);
		}
        foreach (WSTSysLangs() as $key => $v) {
            $goodsName = input('langParams' . $v['id'] . 'goodsName');
            if ($goodsName == '') {
                return WSTReturn(lang('auction_require_goods_name'));
            }
        }
		if((int)$data['auctionPrice']<0)return WSTReturn(lang('auction_start_quantity_limit'));
		if((int)$data['fareInc']<=0)return WSTReturn(lang('auction_add_price_limit'));
		if($data['startTime']=='' || $data['endTime']=='')return WSTReturn(lang('auction_require_time'));
		if(strtotime($data['startTime']) >= strtotime($data['endTime']))return WSTReturn(lang('auction_time_limit_1'));
		//判断是否已经存在同时间的拍卖
		$where = [];
		$where[] = ['goodsId',"=",$data['goodsId']];
		$where[] = ['auctionId','<>',$data['auctionId']];
		$where[] = ['dataFlag',"=",1];
		$whereOr = ' ( ("'.date('Y-m-d H:i:s',strtotime($data['startTime'])).'" between startTime and endTime) or ( "'.date('Y-m-d H:i:s',strtotime($data['endTime'])).'" between startTime and endTime) ) ';
		$rn = $this->where($where)->where($whereOr)->Count();
		if($rn>0)return WSTReturn(lang('auction_goods_exist_other_tips'));
		$auction->startTime = $data['startTime'];
		$auction->endTime = $data['endTime'];
		$auction->auctionPrice = $data['auctionPrice'];
		$auction->fareInc = $data['fareInc'];
		$auction->cautionMoney = $data['cautionMoney'];
		$auction->goodsImg = $data['goodsImg'];
		$auction->auctionStatus = 0;
		$auction->updateTime = date('Y-m-d H:i:s');
		$resourceDomain = ($isApp==1)?WSTConf('CONF.resourceDomain'):WSTConf('CONF.resourcePath');
        Db::startTrans();
		try{
			$auction->save();
            Db::name('auctions_langs')->where(['auctionId'=>$auctionId])->delete();
            $dataLangs = [];
            foreach (WSTSysLangs() as $key => $v) {
                $dataLang = [];
                $dataLang['auctionId'] = $auctionId;
                $dataLang['langId'] = $v['id'];
                $dataLang['goodsName'] = input('langParams'.$v['id'].'goodsName');
                $dataLang['goodsSeoKeywords'] = input('langParams'.$v['id'].'goodsSeoKeywords');
                $dataLang['goodsSeoDesc'] = input('langParams'.$v['id'].'goodsSeoDesc');
                $dataLang['auctionDesc'] = input('langParams'.$v['id'].'auctionDesc');
                //对图片域名进行处理
                $dataLang['auctionDesc'] = htmlspecialchars_decode($dataLang['auctionDesc']);
                $dataLang['auctionDesc'] = WSTRichEditorFilter($dataLang['auctionDesc']);
                $dataLang['auctionDesc'] = str_replace($resourceDomain.'/upload/','${DOMAIN}/upload/',$dataLang['auctionDesc']);
                $dataLangs[] = $dataLang;
            }
            Db::name('auctions_langs')->insertAll($dataLangs);
            //WSTUseResource(0, $data['auctionId'], $data['goodsImg']);
	        //拍卖描述图片
	        //$desc = $this->where('auctionId',$auctionId)->value('auctionDesc');
	        //WSTEditorImageRocord(0, $auctionId, $desc, $auctionDesc);
	        Db::commit();
			return WSTReturn(lang('auction_operation_success'),1);
		}catch (\Exception $e) {
			Db::rollback();
		}
		return WSTReturn(lang('auction_operation_fail'));
	}

	/**
	 * 删除拍卖
	 */
	public function del($sid=0){
		$id = (int)input('id');
		$shopId =($sid==0)?(int)session('WST_USER.shopId'):$sid;
		$auction = $this->get(['auctionId'=>$id]);
		if($auction->shopId != $shopId)return WSTReturn(lang('auction_illegal_operation'));
		if($auction->auctionNum>0 && $auction->orderId==0)return WSTReturn(lang('auction_has_user_cannot_del'));
        Db::startTrans();
        try{
        	$auction->dataFlag = -1;
            $auction->save();
            //没有结束的订单则全部退回保证金
            if($auction->isClose==0){
                $rs = Db::name('auction_moneys')
                    ->where(['auctionId'=>$id,'moneyType'=>1,'cautionStatus'=>1])
                    ->where('payType', ['=','wallets'], ['=','ccgwpays'], 'or')
                    ->field('userId,cautionMoney,createTime')
                    ->select();
	            if(!empty($rs)){
	            	$logUsers = [];
	            	foreach($rs as $key =>$v){
	            		$logUsers[] = $v['userId'];
	            		$lm = [];
						$lm['targetType'] = 0;
						$lm['targetId'] = $v['userId'];
						$lm['dataId'] = $id;
						$lm['dataSrc'] = 'auction';
						$lm['remark'] = json_encode(['type'=>'lang_params','key'=>'auction_unsale_tips','params'=>[$auction->goodsName,$v['cautionMoney']]]);
						$lm['moneyType'] = 1;
						$lm['money'] = $v['cautionMoney'];
						$lm['payType'] = '0';
						$lm['tradeNo'] = '';
						$lm['createTime'] = date('Y-m-d H:i:s');
						model('common/LogMoneys')->add($lm);

						$tpl = WSTMsgTemplates('AUCTION_USER_RESULT');
				        if($tpl['tplContent']!='' && $tpl['status']=='1'){
				            $find = ['${GOODS}','${JOIN_TIME}','${ASTART_TIME}','${RESULT}'];
				            $replace = [$auction->goodsName,$v['createTime'],$auction->startTime,lang('auction_unsale_back_caution_money')];
				            WSTSendMsg($v['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>'auction','dataId'=>$auction->auctionId]);
				        }
				        if((int)WSTConf('CONF.wxenabled')==1){
				        	$params = [];
			                $params['GOODS'] = $auction->goodsName;
			                $params['JOIN_TIME'] = $v['createTime'];
		                    $params['ASTART_TIME'] = $auction->startTime;
		                    $params['RESULT'] = lang('auction_unsale_back_caution_money');
				            WSTWxMessage(['CODE'=>'WX_AUCTION_USER_RESULT','userId'=>$v['userId'],'params'=>$params]);
				        }
			        }
			        if(count($logUsers)>0){
			        	$refundTime = date('Y-m-d H:i:s');
				        Db::name('auction_moneys')
				          ->where('cautionStatus=1 and auctionId='.$id.' and moneyType=1 and userId in('.implode(',',$logUsers).')')
				          ->update(['cautionStatus'=>2,'refundStatus'=>2,'refundTime'=>$refundTime]);
			        }
	            }
	            Db::name('auction_moneys')->where(['auctionId'=>$id,'moneyType'=>1,'cautionStatus'=>1])
	                                      ->where('payType', ['=','weixinpays'], ['=','app_weixinpays'], ['=','alipays'], 'or')
	                                      ->update(["refundStatus"=>1]);
            }
            //WSTUnuseResource('auctions','goodsImg',$id);
            Db::commit();
            return WSTReturn(lang('auction_operation_success'),1);
        }catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('auction_operation_fail'));
	}

    /***
	 * 获取前台团购列表
	 */
	public function pageQuery($num=0){
        $pagesize = ($num>0)?$num:input('limit/d',16);
		$goodsCatId = (int)input('catId');
		$goodsName = input('goodsName');
        hook('afterUserSearchWords',['keyword'=>$goodsName]);
		$areaId = (int)input('areaId');
		$where = [];
        // 如果是移动端首页拍卖活动，取正在拍卖的商品
        if($num>0)$where[] = ['au.isClose','=',0];
		if($goodsCatId>0){
			$gc = new GoodsCats();
			$goodsCatIds = $gc->getParentIs($goodsCatId);
			$where[] = ['goodsCatIdPath','like',implode('_',$goodsCatIds).'_%'];
		}
		if($goodsName!='')$where[] = ['aul.goodsName','like','%'.$goodsName.'%'];
		$page = Db::name('auctions')->alias('au')
                ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
                ->join('__GOODS__ g','au.goodsId=g.goodsId','inner')
                ->where('au.dataFlag=1 and au.isSale=1 and au.auctionStatus=1 and g.dataFlag=1')
                ->where($where)
                ->field('au.goodsId,au.goodsImg,aul.goodsName,au.currPrice,au.startTime,au.endTime,au.auctionId,au.auctionNum,g.gallery')
                ->order('au.isClose asc,au.startTime asc,au.updateTime desc')
                ->paginate($pagesize)->toArray();
		if(count($page)>0){
			$time = time();
			foreach($page['data'] as $key =>$v){
				$page['data'][$key]['goodsImg'] = WSTImg($v['goodsImg']);
				$page['data'][$key]['gallery'] = ($v['gallery']!='')?explode(',',$v['gallery']):'';
				if(strtotime($v['startTime'])<=$time && strtotime($v['endTime'])>=$time){
        			$page['data'][$key]['status'] = 1;
        		}else if(strtotime($v['startTime'])>$time){
                    $page['data'][$key]['status'] = 0;
        		}else{
        			$page['data'][$key]['status'] = -1;
        		}
			}
		}
		return $page;
	}

	/**
	 * 获取团购详情
	 */
	public function getBySale($auctionId, $uId=0){
		$key = input('key');
		$isApp = (int)input('isApp');
		$where = ['dataFlag'=>1,'isSale'=>1,'au.auctionId'=>$auctionId];
		$gu = $this
            ->alias('au')
            ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
            ->field('au.*,aul.goodsName,aul.auctionDesc,aul.goodsSeoKeywords,aul.goodsSeoDesc')
            ->where($where)->find();
		$viKey = WSTShopEncrypt($gu['shopId']);
        if($key!=''){
            if($viKey!=$key && $gu['auctionStatus']!=1)return [];
        }else{
        	if($gu['auctionStatus']!=1)return [];
        }
		$goodsId = $gu['goodsId'];
		if(empty($gu))return [];
		$gu = $gu->toArray();
        //编辑器图片处理
		$gu['auctionDesc'] = htmlspecialchars_decode($gu['auctionDesc']);
        $resourceDomain = ($isApp==1)?WSTConf('CONF.resourceDomain'):WSTConf('CONF.resourcePath');
        $gu['auctionDesc'] = str_replace('${DOMAIN}',$resourceDomain,$gu['auctionDesc']);

		$goods = Db::name('goods')->where('goodsId',$gu['goodsId'])->field('goodsCatId')->find();
		$gu['goodsCatId'] = $goods['goodsCatId'];
		WSTUnset($gu,'illegalRemarks,dataFlag,updateTime,createTime,orderId,bidLogId,isPay');
		Db::name('auctions')->where('auctionId',$auctionId)->setInc('visitNum',1);
		$time = time();
		if(strtotime($gu['startTime'])<=$time && strtotime($gu['endTime'])>=$time){
        	$gu['status'] = 1;
        }else if(strtotime($gu['startTime'])>$time){
            $gu['status'] = 0;
        }else{
        	$gu['status'] = -1;
        }
		$gu['read'] = false;
		//判断是否可以公开查看
		if($key!='')$gu['read'] = true;
		//获取店铺信息
		$gu['shop'] = model('common/shops')->getShopInfo((int)$gu['shopId']);
        if(empty($gu['shop']))return [];
        $goodsCats = Db::name('cat_shops')->alias('cs')
            ->join('__GOODS_CATS__ gc','cs.catId=gc.catId and gc.dataFlag=1','left')->join('__SHOPS__ s','s.shopId = cs.shopId','left')
            ->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=gc.catId and gcl.langId='.WSTCurrLang())
            ->where('cs.shopId',$gu['shopId'])->field('cs.shopId,s.shopTel,gc.catId,gcl.catName')->select();
        $gu['shop']['catId'] = $goodsCats[0]['catId'];
        $gu['shop']['shopTel'] = $goodsCats[0]['shopTel'];
        $cat = [];
        foreach ($goodsCats as $v){
            $cat[] = $v['catName'];
        }
        $gu['shop']['cat'] = implode('，',$cat);

		if(empty($gu['shop']))return [];
		$gu['goodsJson'] = json_decode($gu['goodsJson'],true);
		$gallery = [];
		$gallery[] = $gu['goodsImg'];
		if($gu['goodsJson']['gallery']!=''){
			$tmp = explode(',',$gu['goodsJson']['gallery']);
			$gallery = array_merge($gallery,$tmp);
		}
		$gu['gallery'] = $gallery;
		if(!empty($gu['goodsJson']['specs']))$gu['spec'] = $gu['goodsJson']['specs'];
        unset($gu['goodsJson']);
		//关注
		$gu['favShop'] = model('common/ShopMembers')->checkFavorite($gu['shopId'],$uId);
		//判断是否支付保证金
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		$gu['payMoney'] = 0;
		if($userId>0){
			$gu['payMoney']  = Db::name('auction_moneys')->where(['auctionId'=>$gu['auctionId'],'userId'=>$userId])->count();
		}
		$conf=$this->getConf('Auction');
		//获取拍卖须知
		$article = Db::name('articles')
            ->alias('a')
            ->join('__ARTICLES_LANGS__ asl','asl.articleId=a.articleId and asl.langId='.WSTCurrLang())
            ->field('asl.articleContent')->where(['isShow'=>1,'dataFlag'=>1,'a.articleId'=>(int)$conf['auctionArticleId']])->find();
		$gu['article'] = $article['articleContent'];
		$gu['endPayDate'] = $conf['endPayDate'];
		return $gu;
	}

	/**
	 * 管理员查看拍卖列表
	 */
	public function pageQueryByAdmin($grouponStatus){
		$goodsName = input('goodsName');
		$shopName = input('shopName');
		$areaIdPath = input('areaIdPath');
		$goodsCatIdPath = input('goodsCatIdPath');
		$where[] = ['au.dataFlag','=',1];
		$where[] = ['auctionStatus','=',$grouponStatus];
		if($goodsName !='')$where[] = ['aul.goodsName','like','%'.$goodsName.'%'];
		if($shopName !='')$where[] = ['s.shopName|s.shopSn','like','%'.$shopName.'%'];
		if($areaIdPath !='')$where[] = ['s.areaIdPath','like',$areaIdPath."%"];
		if($goodsCatIdPath !='')$where[] = ['g.goodsCatIdPath','like',$goodsCatIdPath."%"];
        $page =  $this->alias('au')
                    ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
                    ->join('__GOODS__ g','g.goodsId=au.goodsId and g.dataFlag=1','inner')
                    ->join('__SHOPS__ s','s.shopId=au.shopId','left')
                    ->where($where)
                    ->field('au.*,aul.goodsName,s.shopId,s.shopName')
                    ->order('au.updateTime desc')
                    ->paginate(input('limit/d'))->toArray();
        if(count($page['data'])>0){
        	$time = time();
        	foreach($page['data'] as $key =>$v){
        		$page['data'][$key]['goodsImg'] = WSTImg($v['goodsImg']);
        		$page['data'][$key]['verfiycode'] = WSTShopEncrypt($v['shopId']);
        		if(strtotime($v['startTime'])<=$time && strtotime($v['endTime'])>=$time){
        			$page['data'][$key]['status'] = 1;
        		}else if(strtotime($v['startTime'])>$time){
                    $page['data'][$key]['status'] = 0;
        		}else{
        			$page['data'][$key]['status'] = -1;
        		}
        	}
        }
        return $page;
	}

	/**
	* 设置商品违规状态
	*/
	public function illegal(){
		$illegalRemarks = input('post.illegalRemarks');
		$id = (int)input('post.id');
		if($illegalRemarks=='')return WSTReturn(lang('auction_require_illegal_reason'));
		//判断商品状态
		$rs = $this->alias('au')
                   ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
		           ->join('__SHOPS__ s','au.shopId=s.shopId','left')
		           ->where('au.auctionId',$id)
		           ->field('au.auctionId,au.shopId,s.userId,aul.goodsName,au.auctionStatus,au.goodsId,au.auctionNum')->find();
		if((int)$rs['auctionId']==0)return WSTReturn(lang('auction_invalid_goods'));
		if((int)$rs['auctionNum']>0)return WSTReturn(lang('auction_has_user_cannot_unsale'));
		if((int)$rs['auctionStatus']==-1)return WSTReturn(lang('auction_goods_status_changed_tips'));
		Db::startTrans();
		try{
			$res = $this->where('auctionId',$id)->setField(['auctionStatus'=>-1,'illegalRemarks'=>$illegalRemarks]);
			if($res!==false){
				//发送一条商家信息
				$tpl = WSTMsgTemplates('AUCTION_GOODS_REJECT');
		        if($tpl['tplContent']!='' && $tpl['status']=='1'){
		            $find = ['${GOODS}','${TIME}','${REASON}'];
		            $replace = [$rs['goodsName'],date('Y-m-d H:i:s'),$illegalRemarks];

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
					$params['TIME'] = date('Y-m-d H:i:s');
					$params['REASON'] = $illegalRemarks;

					$msg = array();
					$tplCode = "WX_AUCTION_GOODS_REJECT";
					$msg["shopId"] = $rs['shopId'];
		            $msg["tplCode"] = $tplCode;
		            $msg["msgType"] = 4;
		            $msg["paramJson"] = ['CODE'=>$tplCode,'params'=>$params];
		            $msg["msgJson"] = "";
		            model("common/MessageQueues")->add($msg);
				}
				Db::commit();
				return WSTReturn(lang('auction_operation_success'),1);
			}
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('auction_operation_fail'),-1);
	}
   /**
	* 通过商品审核
	*/
	public function allow(){
		$id = (int)input('post.id');
		//判断商品状态
		$rs = $this->alias('au')
                   ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
		           ->join('__SHOPS__ s','au.shopId=s.shopId','left')
		           ->where('au.auctionId',$id)
		           ->field('au.auctionId,au.shopId,s.userId,aul.goodsName,au.auctionStatus,au.goodsId')->find();
		if((int)$rs['auctionId']==0)return WSTReturn(lang('auction_invalid_goods'));
		if((int)$rs['auctionStatus']!=0)return WSTReturn(lang('auction_goods_status_changed_tips'));
		Db::startTrans();
		try{
			$res = $this->where('auctionId',$id)->setField(['auctionStatus'=>1]);
			if($res!==false){
				//发送一条商家信息
				$tpl = WSTMsgTemplates('AUCTION_GOODS_ALLOW');
		        if($tpl['tplContent']!='' && $tpl['status']=='1'){
		            $find = ['${GOODS}','${TIME}'];
		            $replace = [$rs['goodsName'],date('Y-m-d H:i:s')];

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
					$params['TIME'] = date('Y-m-d H:i:s');

					$msg = array();
					$tplCode = "WX_AUCTION_GOODS_ALLOW";
					$msg["shopId"] = $rs['shopId'];
		            $msg["tplCode"] = $tplCode;
		            $msg["msgType"] = 4;
		            $msg["paramJson"] = ['CODE'=>$tplCode,'params'=>$params];
		            $msg["msgJson"] = "";
		            model("common/MessageQueues")->add($msg);
				}
				Db::commit();
				return WSTReturn(lang('auction_operation_success'),1);
			}
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('auction_operation_fail'),-1);
	}

    /**
	 * 删除拍卖
	 */
	public function delByAdmin(){
		$id = (int)input('id');
        $auction = $this
                ->alias('au')
                ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
                ->field('au.*,aul.goodsName')->where(['auctionId'=>$id])
                ->find();
        if($auction->auctionNum>0 && $auction->orderId==0)return WSTReturn(lang('auction_order_not_complete_cannot_del'));
        Db::startTrans();
        try{
        	$auction->dataFlag = -1;
            $auction->save();
            //没有结束的订单则全部退回保证金
            if($auction->isClose==0){
                $rs = Db::name('auction_moneys')
                    ->where(['auctionId'=>$id,'moneyType'=>1,'cautionStatus'=>1])
                    ->where('payType', ['=','wallets'],['=','ccgwpays'], 'or')
                    ->field('userId,cautionMoney,createTime')
                    ->select();
	            if(!empty($rs)){
	            	$logUsers = [];
	            	foreach($rs as $key =>$v){
	            		$logUsers[] = $v['userId'];
	            		$lm = [];
						$lm['targetType'] = 0;
						$lm['targetId'] = $v['userId'];
						$lm['dataId'] = $id;
						$lm['dataSrc'] = 'auction';
						$lm['remark'] = json_encode(['type'=>'lang_params','key'=>'auction_unsale_tips','params'=>[$auction->goodsName,$v['cautionMoney']]]);
						$lm['moneyType'] = 1;
						$lm['money'] = $v['cautionMoney'];
						$lm['payType'] = '0';
						$lm['tradeNo'] = '';
						$lm['createTime'] = date('Y-m-d H:i:s');
						model('common/LogMoneys')->add($lm);

						$tpl = WSTMsgTemplates('AUCTION_USER_RESULT');
				        if($tpl['tplContent']!='' && $tpl['status']=='1'){
				            $find = ['${GOODS}','${JOIN_TIME}','${ASTART_TIME}','${RESULT}'];
				            $replace = [$auction->goodsName,$v['createTime'],$auction->startTime,lang('auction_unsale_back_caution_money')];
				            WSTSendMsg($v['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>'auction','dataId'=>$auction->auctionId]);
				        }
				        if((int)WSTConf('CONF.wxenabled')==1){
				        	$params = [];
			                $params['GOODS'] = $auction->goodsName;
			                $params['JOIN_TIME'] = $v['createTime'];
		                    $params['ASTART_TIME'] = $auction->startTime;
		                    $params['RESULT'] = lang('auction_unsale_back_caution_money');
				            WSTWxMessage(['CODE'=>'WX_AUCTION_USER_RESULT','userId'=>$v['userId'],'params'=>$params]);
				        }
			        }
			        if(count($logUsers)>0){
				        Db::name('auction_moneys')
				          ->where('cautionStatus=1 and auctionId='.$id.' and moneyType=1 and userId in('.implode(',',$logUsers).')')
				          ->update(['cautionStatus'=>2]);
			        }
	            }
	            Db::name('auction_moneys')->where(['auctionId'=>$id,'moneyType'=>1,'cautionStatus'=>1])
	                                      ->where('payType', ['=','weixinpays'],['=','app_weixinpays'], ['=','alipays'], 'or')
	                                      ->update(["refundStatus"=>1]);
            }
            //WSTUnuseResource('auctions','goodsImg',$id);
            Db::commit();
            return WSTReturn(lang('auction_operation_success'),1);
        }catch (\Exception $e) {
            Db::rollback();
        }

        return WSTReturn(lang('auction_operation_fail'));
	}

	/**
	 * 查询查询竞拍记录
	 */
	public function pageAuctionLogQuery($auctionId,$isAdmin = false,$sid=0){
		$where = ['auctionId'=>$auctionId];
		$where2 = [];
		$key = input('key');
		if($key != '')$where2[] = ['u.loginName','like','%'.$key.'%'];
		if(!$isAdmin){
			$where['shopId']=($sid==0)?(int)session('WST_USER.shopId'):$sid;
		}
		$auction = Db::name('auctions')->where($where)
		             ->field('orderId,currPrice')->find();
        if(empty($auction))return WSTReturn('',-1);
        $page =  Db::name('auction_logs')->alias('a')
                  ->join('__USERS__ u','a.userId=u.userId')
                  ->where('a.dataFlag>=0 and a.auctionId='.$auctionId)
                  ->where($where2)
                  ->field('u.loginName,u.userPhoto,a.payPrice,a.createTime,a.isTop')
                  ->order('payPrice desc,createTime desc')
                  ->paginate(input('limit/d'))->toArray();
        if(count($page['data'])>0){
        	 $order = ['orderNo'=>''];
        	 if($auction['orderId'] > 0){
        	      $order = Db::name('orders')->where('orderId',$auction['orderId'])->field('orderNo')->find();
             }
        	foreach ($page['data'] as $k => $v) {
        		$page['data'][$k]['orderId'] = '';
        		$page['data'][$k]['orderNo'] = '';
        		if($auction['currPrice']==$v['payPrice']){
        			$page['data'][$k]['orderNo'] = $order['orderNo'];
        			$page['data'][$k]['orderId'] = $auction['orderId'];
        		}
        	}
        }
        return WSTReturn('',1,$page);
	}


	/**
	 * 拍卖报价
	 */
	public function addAcution($uId=0){
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		if($userId==0)return WSTReturn(lang('no_login'));
		$auctionId = (int)input('id');
		$payPrice = (float)input('payPrice');
		//判断出价是否大于当前报价
		$auction = $this->get(['auctionId'=>$auctionId]);
		if($auction->isClose!=0)return WSTReturn(lang('auction_end_tips'));
		//判断是否支付保证金
		$isPay = Db::name('auction_moneys')
		           ->where(['userId'=>$userId,'auctionId'=>$auctionId,'cautionStatus'=>1])
		           ->count();
		if($isPay==0)return WSTReturn(lang('auction_not_pay_caution_money'));
		if($auction->currPrice >= $payPrice)return WSTReturn(lang('auction_offer_price_less',[$auction->currPrice]),-2);
		if((($payPrice-$auction->currPrice)%$auction->fareInc)>0)return WSTReturn(lang('auction_illegal_bid_range'));
		Db::startTrans();
		try{
			//修改当前价格
			$auction->currPrice = $payPrice;
			$auction->auctionNum = $auction->auctionNum + 1;
			$auction->save();
			//获取上一条要通知的用户
			$log = Db::name('auction_logs')->where('auctionId='.$auctionId.' and userId!='.$userId.' and isTop=1')->find();
			//标记之前的出价信息为隐藏状态
			Db::name('auction_logs')->where(['auctionId'=>$auctionId,'userId'=>$userId])->update(['dataFlag'=>0]);
			Db::name('auction_logs')->where(['auctionId'=>$auctionId])->update(['isTop'=>0]);
		    $data = [];
		    $data['userId'] = $userId;
		    $data['auctionId'] = $auctionId;
		    $data['payPrice'] = $payPrice;
		    $data['createTime'] = date('Y-m-d H:i:s');
		    $data['isTop'] = 1;
		    $data['dataFlag'] = 1;
		    $result = Db::name('auction_logs')->insert($data);
		    if(false !== $result){
		    	//发送系统消息-用户
		        if(!empty($log)){
		        	$tpl = WSTMsgTemplates('AUCTION_USER_RESULT');
			        if($tpl['tplContent']!='' && $tpl['status']=='1'){
			            $find = ['${GOODS}','${JOIN_TIME}','${ASTART_TIME}','${RESULT}'];
			            $replace = [$auction->goodsName,$log['createTime'],$auction->startTime,lang('auction_out')];
			            WSTSendMsg($log['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>'auction','dataId'=>$auction->auctionId]);
			        }
			        if((int)WSTConf('CONF.wxenabled')==1){
			        	$params = [];
		                $params['GOODS'] = $auction->goodsName;
		                $params['JOIN_TIME'] = $log['createTime'];
	                    $params['ASTART_TIME'] = $auction->startTime;
	                    $params['RESULT'] = lang('auction_out');
			            WSTWxMessage(['CODE'=>'WX_AUCTION_USER_RESULT','userId'=>$log['userId'],'params'=>$params]);
			        }
		        }
		    	Db::commit();
		    	return WSTReturn(lang('auction_offer_price_success'),1);
		    }
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('auction_offer_price_fail'),-1);
	}

	/**
	 *  获取出价记录
	 */
	public function pageQueryByAuctionLog($auctionId,$isReplace = true){
		$auction = Db::name('auctions')->where('auctionId',$auctionId)->field('currPrice')->find();
        $page =  Db::name('auction_logs')->alias('a')
                  ->join('__USERS__ u','a.userId=u.userId')
                  ->where('a.dataFlag >=0 and a.auctionId='.$auctionId)
                  ->field('u.loginName,u.userPhoto,a.payPrice,a.createTime,a.isTop')
                  ->order('payPrice desc,createTime desc')
                  ->paginate(input('limit/d'))->toArray();
        if(count($page['data'])>0){
        	foreach ($page['data'] as $k => $v) {
				$page['data'][$k]['userPhoto'] = WSTUserPhoto($v['userPhoto'], 1);
        		if($isReplace)$page['data'][$k]['loginName'] = WSTStrReplace($v['loginName'],'*',2);
        	}
        }
        return WSTReturn('',1,$page);
	}

	/**
	 * 获取用户竞拍记录
	 */
	public function pageQueryByUser($uId=0){
		 $userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
         $page = Db::name('auction_logs')->alias('al')
           ->join('__AUCTIONS__ a','al.auctionId=a.auctionId','inner')
           ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=a.auctionId and aul.langId='.WSTCurrLang())
           ->join('__GOODS__ g','a.goodsId=g.goodsId')
           ->where(['al.dataFlag'=>1,'al.userId'=>$userId])
           ->field('a.bidLogId,a.auctionId,a.goodsId,aul.goodsName,a.goodsImg,a.auctionPrice,a.currPrice,a.startTime,a.endTime,al.id,al.payPrice,al.isTop,a.isPay,a.isClose')
           ->order('al.createTime desc')
           ->paginate(input('limit/d'))->toArray();
        if(count($page['data'])>0){
        	$time = time();
        	foreach ($page['data'] as $key => $v) {
        		if($v['bidLogId']==$v['id'])$page['data'][$key]['bid'] = 1;
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
        return WSTReturn('',1,$page);
	}


	/**
	 * 支付保证金信息
	 */
	public function getPayInfo($auctionId,$payfor){
		$data = [];
		$auction = $this
            ->alias('au')
            ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
            ->field('au.*,aul.goodsName')
            ->where(['au.auctionId'=>$auctionId])
            ->find();
		//判断时间是否合适
		$time = time();
		if($payfor !=2 && (strtotime($auction->startTime) > $time || strtotime($auction->endTime) < $time)){
			return WSTReturn(lang('auction_illegal_operation'));
		}
		$data["auction"] = $auction;
		if($payfor==2){
			$where = [];
			$bidLogId = (int)$auction["bidLogId"];
			$where['id'] = $bidLogId;
			$where['dataFlag'] = 1;
			$where['isTop'] = 1;
			$log = Db::name('auction_logs')->where($where)->field(["payPrice"])->find();
			$data["auction"]['cautionMoney'] = $log["payPrice"];
		}

		//获取支付信息
		$data['payments'] = $this->getPayments();
		return WSTReturn('',1,$data);
	}


	public function getPayments(){
		//获取支付信息
		$payments = Db::name('payments')->where(['isOnline'=>1,'enabled'=>1])->order('payOrder asc')->select();
		return $payments;
	}

	public function getUserAuction($auctionId){
		$where = [];
		$userId = (int)session('WST_USER.userId');
		$where['au.auctionId'] = $auctionId;
		$where['au.dataFlag'] = 1;
		$rs = $this->alias('au')
            ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
            ->join('__AUCTION_MONEYS__ a','a.auctionId=au.auctionId and a.moneyType=1 and a.userId='.$userId,'left')->where($where)->field('a.userId,au.*,aul.goodsName')->find();
		return $rs;
	}

	public function getAuctionPay($auctionId, $uId=0){
		$where = [];
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		$where['au.auctionId'] = $auctionId;
		$where['au.dataFlag'] = 1;
		$rs = $this->alias('au')
            ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
            ->join('__AUCTION_LOGS__ a','a.auctionId=au.auctionId and au.bidLogId=a.id and a.userId='.$userId)->where($where)->field('a.payPrice,au.*,aul.goodsName')->find();
		return $rs;
	}


	/**
	 * 完成保证金支付
	 */
	public function complateCautionMoney($obj){

		$trade_no = $obj["trade_no"];
		$userId = (int)$obj["userId"];
		$auctionId = (int)$obj["auctionId"];
		$payFrom = $obj["payFrom"];
		$payMoney = (float)$obj["total_fee"];
		$moneyType = ($obj["payObj"]=="bao")?1:2;

		$auction = Db::name('auction_moneys')->where(["userId"=>$userId,"moneyType"=>$moneyType,"tradeNo"=>$trade_no,"payType"=>$payFrom])->find();
		if(!empty($auction)){
			return WSTReturn(($moneyType==1)?lang('auction_caution_money_has_pay'):lang('auction_order_money_has_pay'),-1);
		}
		$auction = Db::name('auctions')
            ->alias('au')
            ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
            ->where(["au.auctionId"=>$auctionId,"dataFlag"=>1])->field(["cautionMoney","bidLogId","aul.goodsName","startTime","endPayTime"])->find();
		if($moneyType==1){
			$cautionMoney = $auction["cautionMoney"];
		}else{
			$where = [];
			$bidLogId = (int)$auction["bidLogId"];
			$where['id'] = $bidLogId;
			$where['dataFlag'] = 1;
			$where['isTop'] = 1;
			$log = Db::name('auction_logs')->where($where)->field(["payPrice"])->find();
			$cautionMoney = $log["payPrice"];
		}

		if($payMoney<$cautionMoney){
			return WSTReturn(($moneyType==1)?lang('auction_caution_money_incorrect'):lang('auction_order_money_incorrect'),-1);
		}

		Db::startTrans();
		try {
			//创建一条充值流水记录
			$lm = [];
			$lm['targetType'] = 0;
			$lm['targetId'] = $userId;
			$lm['dataId'] = $auctionId;
			$lm['dataSrc'] = 'auction';
			$lm['remark'] = ($moneyType==1)?(json_encode(['type'=>'lang_params','key'=>'auction_caution_money_recharge','params'=>[$auction['goodsName'],$payMoney]])):(json_encode(['type'=>'lang_params','key'=>'auction_order_money_recharge','params'=>[$auction['goodsName'],$payMoney]]));
			$lm['moneyType'] = 1;
			$lm['money'] = $payMoney;
			$lm['payType'] = $payFrom;
			$lm['tradeNo'] = $trade_no;
			model('common/LogMoneys')->add($lm);

			$mauction = Db::name('auction_moneys')->where(["userId"=>$userId,"moneyType"=>$moneyType,"auctionId"=>$auctionId])->find();
			if(empty($mauction)){
				if($moneyType==2){
					if($auction["endPayTime"]<date("Y-m-d H:i:s")){
						Db::commit();
						return WSTReturn(lang('auction_order_pay_overtime'),-1);
					}

					$data = array();
					$data["isPay"] = 1;
					$data["isClose"] = 1;
					Db::name('auctions')->where(["auctionId"=>$auctionId])->update($data);

					//退回保证金
					$cmoney = Db::name('auction_moneys')
                                ->where(["auctionId"=>$auctionId,"moneyType"=>1,"cautionStatus"=>1])
                                ->field(["id","cautionMoney","payType","tradeNo","createTime"])
                                ->find();
					$cautionMoney = $cmoney["cautionMoney"];
					if($cmoney['payType']=='wallets' || $cmoney['payType']=='ccgwpays'){

						$cId = $cmoney["id"];
						$refundTime = date("Y-m-d H:i:s");
						Db::name('auction_moneys')->where(["id"=>$cId])->update(["cautionStatus"=>2,"refundStatus"=>2,"refundTime"=>$refundTime]);
						//创建一条收出流水记录
						$lm = [];
						$lm['targetType'] = 0;
						$lm['targetId'] = $userId;
						$lm['dataId'] =  $auctionId;
						$lm['dataSrc'] = 'auction';
						$lm['remark'] =json_encode(['type'=>'lang_params','key'=>'auction_refund_caution_money','params'=>[$auction['goodsName'],$cautionMoney]]);
						$lm['moneyType'] = 1;
						$lm['money'] = $cautionMoney;
						$lm['payType'] = 'wallets';
						model('common/LogMoneys')->add($lm);

						if((int)WSTConf('CONF.wxenabled')==1){
				        	$params = [];
			                $params['GOODS'] = $auction['goodsName'];
			                $params['JOIN_TIME'] = $cmoney['createTime'];
		                    $params['ASTART_TIME'] = $auction['startTime'];
		                    $params['RESULT'] = lang('auction_success_refund_caution_money');
				            WSTWxMessage(['CODE'=>'WX_AUCTION_USER_RESULT','userId'=>$userId,'params'=>$params]);
				        }
					}else{
						//退款原路返回
						Db::name('auction_moneys')->where(['auctionId'=>$auctionId,'moneyType'=>1,'cautionStatus'=>1])
			                                      ->where('payType', ['=','weixinpays'],['=','app_weixinpays'], ['=','alipays'], 'or')
			                                      ->update(["cautionStatus"=>2,"refundStatus"=>1]);
					}
				}

                if($payFrom=='ccgwpays'){
                    Db::name('ccgw_address_trans')->where(['ccTxid'=>$trade_no])->update(['dataStatus'=>1]);
                }

				//创建一条支出流水记录
				$lm = [];
				$lm['targetType'] = 0;
				$lm['targetId'] = $userId;
				$lm['dataId'] = $auctionId;
				$lm['dataSrc'] = 'auction';
				$lm['remark'] = ($moneyType==1)?(json_encode(['type'=>'lang_params','key'=>'auction_pay_caution_money','params'=>[$auction['goodsName'],$payMoney]])):(json_encode(['type'=>'lang_params','key'=>'auction_pay_order_money','params'=>[$auction['goodsName'],$payMoney]]));
				$lm['moneyType'] = 0;
				$lm['money'] = $payMoney;
				$lm['payType'] = $payFrom;
				model('common/LogMoneys')->add($lm);

				//创建一条拍卖金记录
				$am = [];
				$am['auctionId'] = $auctionId;
				$am['userId'] = $userId;
				$am['cautionMoney'] =  $payMoney;
				$am['cautionStatus'] = 1;
				$am['payType'] = $payFrom;
				$am['tradeNo'] = $trade_no;
				$am['moneyType'] = $moneyType;
				$am['createTime'] = date('Y-m-d H:i:s');
				Db::name('auction_moneys')->insert($am);
			}
			Db::commit();
			return WSTReturn(lang('auction_pay_success'),1);
		} catch (Exception $e) {
			Db::rollback();
			return WSTReturn(lang('auction_pay_fail'),-1);
		}
	}

	/**
	 * 用户钱包支付保证金
	 */
	public function payByWallet($uId=0){
		$payPwd = input('payPwd');
		$isWeapp = (int)input('isWeapp');
		if($uId==0 || $isWeapp==1){// 大于0表示来自app端
			$decrypt_data = WSTRSA($payPwd);
			if($decrypt_data['status']==1){
				$payPwd = $decrypt_data['data'];
			}else{
				return WSTReturn(lang('auction_pay_fail'));
			}
		}
		$pkey = input('pkey');
		$pkey = WSTBase64urlDecode($pkey);
		$pkey = explode('@',$pkey);
		$moneyType = ($pkey[0]=="bao")?1:2;

		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		//判断是否开启余额支付
		$isEnbalePay = model('common/Payments')->isEnablePayment('wallets');
		if($isEnbalePay==0)return WSTReturn(lang('auction_illegal_pay_method'),-1);
		$payMoney = 0;
		$auctionId = (int)$pkey[1];
		$auction = Db::name('auctions')
            ->alias('au')
            ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
            ->where(["au.auctionId"=>$auctionId,"dataFlag"=>1])->field(["cautionMoney","bidLogId","aul.goodsName","startTime","endPayTime"])->find();
		$now = date("Y-m-d H:i:s");
		if($auction["bidLogId"]>0 && $auction["endPayTime"]<$now){
			return WSTReturn(lang('auction_order_pay_overtime'),-1);
		}
		$data = array();
		if($moneyType==1){
			$payMoney = $auction["cautionMoney"];
		}else{
			$where = [];
			$bidLogId = (int)$auction["bidLogId"];
			$where['id'] = $bidLogId;
			$where['dataFlag'] = 1;
			$where['isTop'] = 1;
			$log = Db::name('auction_logs')->where($where)->field(["payPrice"])->find();
			$payMoney = $log["payPrice"];
		}
		$data = Db::name('auction_moneys')->where(["userId"=>$userId,"moneyType"=>$moneyType,"auctionId"=>$auctionId])->find();
		//获取用户钱包
		$user = model('common/users')->get(['userId'=>$userId]);
		if($user->payPwd=='')return WSTReturn(lang('auction_please_set_pay_password'),-1);
		if($user->payPwd!=md5($payPwd.$user->loginSecret))return WSTReturn(lang('auction_pay_password_incorrect'),-1);
		if($payMoney > $user->userMoney)return WSTReturn(lang('auction_wallet_balance_less_cannot_pay'),-1);
		$rechargeMoney = $user->rechargeMoney;
		Db::startTrans();
		try {
			$lockCashMoney = 0;
			if(empty($data)){
				$lockCashMoney = ($rechargeMoney>$payMoney)?$payMoney:$rechargeMoney;
				//创建一条支出流水记录
				$lm = [];
				$lm['targetType'] = 0;
				$lm['targetId'] = $userId;
				$lm['dataId'] =  $auctionId;
				$lm['dataSrc'] = 'auction';
				$lm['remark'] = ($moneyType==1)?(json_encode(['type'=>'lang_params','key'=>'auction_pay_caution_money','params'=>[$auction['goodsName'],$payMoney]])):(json_encode(['type'=>'lang_params','key'=>'auction_pay_order_money','params'=>[$auction['goodsName'],$payMoney]]));

				$lm['moneyType'] = 0;
				$lm['money'] = $payMoney;
				$lm['payType'] = 'wallets';
				model('common/LogMoneys')->add($lm);
				//修改用户充值金额
				model('common/users')->where(["userId"=>$userId])->setDec("rechargeMoney",$lockCashMoney);
				if($moneyType==2){
					$data = array();
					$data["isPay"] = 1;
					$data["isClose"] = 1;
					Db::name('auctions')->where(["auctionId"=>$auctionId])->update($data);
					//退回保证金
					$cmoney = Db::name('auction_moneys')
                                ->where(["auctionId"=>$auctionId,"moneyType"=>1,"cautionStatus"=>1])
                                ->field(["id","createTime","payType","tradeNo","cautionMoney"])
                                ->find();
					$cautionMoney = $cmoney["cautionMoney"];
					if($cmoney['payType']=='wallets' || $cmoney['payType']=='ccgwpays'){

						$cId = $cmoney["id"];
						$refundTime = date("Y-m-d H:i:s");
						Db::name('auction_moneys')->where(["id"=>$cId])->update(["cautionStatus"=>2,"refundStatus"=>2,"refundTime"=>$refundTime]);

						//创建一条收出流水记录
						$lm = [];
						$lm['targetType'] = 0;
						$lm['targetId'] = $userId;
						$lm['dataId'] =  $auctionId;
						$lm['dataSrc'] = 'auction';
						$lm['remark'] = json_encode(['type'=>'lang_params','key'=>'auction_refund_caution_money','params'=>[$auction['goodsName'],$cautionMoney]]);
						$lm['moneyType'] = 1;
						$lm['money'] = $cautionMoney;
						$lm['payType'] = 'wallets';
						model('common/LogMoneys')->add($lm);

						model('common/users')->where(["userId"=>$userId])->setInc("rechargeMoney",$lockCashMoney);

						if((int)WSTConf('CONF.wxenabled')==1){
				        	$params = [];
			                $params['GOODS'] = $auction['goodsName'];
			                $params['JOIN_TIME'] = $cmoney['createTime'];
		                    $params['ASTART_TIME'] = $auction['startTime'];
		                    $params['RESULT'] = lang('auction_success_refund_caution_money');
				            WSTWxMessage(['CODE'=>'WX_AUCTION_USER_RESULT','userId'=>$userId,'params'=>$params]);
				        }
					}else{
						//退款原路返回
						Db::name('auction_moneys')->where(['auctionId'=>$auctionId,'moneyType'=>1,'cautionStatus'=>1])
			                                      ->where('payType', ['=','weixinpays'], ['=','app_weixinpays'], ['=','alipays'], 'or')
			                                      ->update(["cautionStatus"=>2,"refundStatus"=>1]);
					}

				}
				//创建一条保证金记录
				$am = [];
				$am['auctionId'] = $auctionId;
				$am['userId'] = $userId;
				$am['cautionMoney'] =  $payMoney;
				$am['cautionStatus'] = 1;
				$am['payType'] = "wallets";
				$am['tradeNo'] = '';
				$am['moneyType'] = $moneyType;
				$am['lockCashMoney'] = $lockCashMoney;
				$am['createTime'] = date('Y-m-d H:i:s');
				Db::name('auction_moneys')->insert($am);
				Db::commit();
				return WSTReturn(lang('auction_pay_success'),1);
			}else{
				return WSTReturn(lang('auction_has_pay_not_again'),-1);
			}
		} catch (Exception $e) {
			Db::rollback();
			return WSTReturn(lang('auction_pay_fail'),-1);
		}
	}

	/**
	 * 检测是否完成竞拍支付
	 */
	public function checkAuctionPayStatus($auctionId,$uId=0){
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
        return Db::name('auctions')->alias('a')
                     ->join('__AUCTION_LOGS__ al','a.auctionId=al.auctionId and a.bidLogId=al.id','inner')
                     ->where(['a.auctionId'=>$auctionId,'a.dataFlag'=>1,'al.dataFlag'=>1,'al.userId'=>$userId])
                     ->field('a.shopId,a.auctionId,a.isPay,al.payPrice,al.userId')
                     ->find();
	}

	/**
	 * 完成下单
	 */
	public function submit($orderSrc,$uId=0){
		$auctionId = (int)input('post.auctionId');
		$addressId = (int)input('post.s_addressId');
		$deliverType = ((int)input('post.deliverType')!=0)?1:0;
		$isInvoice = ((int)input('post.isInvoice')!=0)?1:0;
		$invoiceClient = ($isInvoice==1)?input('post.invoiceClient'):'';
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		if($userId==0)return WSTReturn(lang('no_login'));
		//检测是否提交了订单/是否具体提交订单的资格
		
        $auction = $this
            ->alias('au')
            ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
            ->field('au.*,aul.goodsName')
            ->where(['au.auctionId'=>$auctionId])
            ->find();

		if($auction->orderId>0)return WSTReturn(lang('auction_has_complete_order'));
        $log = Db::name('auction_logs')->where(['auctionId'=>$auctionId,'userId'=>$userId,'isTop'=>1,'dataFlag'=>1])->find();
		if(empty($log))return WSTReturn(lang('auction_invalid_record'));

		//检测地址是否有效
		$address = Db::name('user_address')->where(['userId'=>$userId,'addressId'=>$addressId,'dataFlag'=>1])->find();
		if(empty($address)){
			return WSTReturn(lang('auction_invalid_address'));
		}
	    $areaIds = [];
        $areaMaps = [];
        $tmp = explode('_',$address['areaIdPath']);
        $address['areaId2'] = $tmp[1];//记录配送城市
        foreach ($tmp as $vv){
         	if($vv=='')continue;
         	if(!in_array($vv,$areaIds))$areaIds[] = $vv;
        }
        if(!empty($areaIds)){
	         $areas = Db::name('areas')->where([['dataFlag','=',1],['areaId','in',$areaIds]])->field('areaId,areaName')->select();
	         foreach ($areas as $v){
	         	 $areaMaps[$v['areaId']] = $v['areaName'];
	         }
	         $tmp = explode('_',$address['areaIdPath']);
	         $areaNames = [];
		     foreach ($tmp as $vv){
	         	 if($vv=='')continue;
	         	 $areaNames[] = $areaMaps[$vv];
	         	 $address['areaName'] = implode('',$areaNames);
	         }
        }
		$address['userAddress'] = $address['areaName'].$address['userAddress'];
		WSTUnset($address, 'isDefault,dataFlag,createTime,userId');


		//获取支付记录
		$auctionMoney = Db::name('auction_moneys')->where(['auctionId'=>$auction->auctionId,'userId'=>$userId,'moneyType'=>2])->find();
		if(empty($auctionMoney))return WSTReturn(lang('auction_pay_price_first'));
		//生成订单
		Db::startTrans();
		try{
			$orderunique = WSTOrderQnique();
			$orderNo = WSTOrderNo();
			//创建订单
			$order = [];
			$order = array_merge($order,$address);
			$order['payFrom'] = $auctionMoney['payType'];
			$order['tradeNo'] = $auctionMoney['tradeNo'];
			$order['orderNo'] = $orderNo;
			$order['userId'] = $userId;
			$order['shopId'] = $auction->shopId;
			$order['payType'] = 1;
			$order['goodsMoney'] = $auction->currPrice;
			//计算运费和总金额
			$order['deliverType'] = $deliverType;
			$order['deliverMoney'] = 0;
			$order['totalMoney'] = $auction->currPrice;
            $order['scoreMoney'] = 0;
			$order['useScore'] = 0;
			if($deliverType==1){//自提
				$order['storeId'] = (int)input("storeId");
				$order['storeType'] = 1;
			}
			//实付金额要减去积分兑换的金额
			$order['realTotalMoney'] = $auction->currPrice;
			$order['needPay'] = 0;
			$order['orderCode'] = 'auction';
			$order['orderCodeTargetId'] = $auction->auctionId;
			$order['extraJson'] = json_encode(['auctionId'=>$auction->auctionId]);
            $order['orderStatus'] = 0;//待发货
			$order['isPay'] = 1;
			//积分
			$orderScore = 0;
			//如果开启下单获取积分则有积分
			if(WSTConf('CONF.isOrderScore')==1){
				$orderScore = WSTMoneyGiftScore($order['goodsMoney']);
			}
			$order['orderScore'] = $orderScore;
			$shop = model("common/shops")->getFieldsById($auction->shopId,"isInvoice");
			$shopIsInvoice = $shop['isInvoice'];
			if($shopIsInvoice==1 && $isInvoice==1){
				$order['isInvoice'] = $isInvoice;
				$order['invoiceJson'] = model('common/invoices')->getInviceInfo((int)input('param.invoiceId'),$userId);// 发票信息
				$order['invoiceClient'] = $invoiceClient;
			}else{
				$order['isInvoice'] = 0;
				$order['invoiceJson'] = "";// 发票信息
				$order['invoiceClient'] = "";
			}
			if($deliverType==1)$order['verificationCode'] = WSTOrderVerificationCode($auction->shopId);
			$order['orderRemarks'] = input('post.remark');
			$order['orderunique'] = $orderunique;
			$order['orderSrc'] = $orderSrc;
			$order['dataFlag'] = 1;
			$order['payRand'] = 1;
			$order['createTime'] = date('Y-m-d H:i:s');
			$order['payTime'] = $auction['endPayTime'];
			$m = model('common/orders');
			$result = $m->data($order,true)->isUpdate(false)->allowField(true)->save($order);
			if(false !== $result){
				$orderId = $m->orderId;
				$auction->orderId = $orderId;
				$auction->isClose = 2;
				$auction->save();

				$goods = Db::name('goods')->where('goodsId',$auction->goodsId)->field('goodsCatId')->find();
				//创建订单商品记录
				$orderGgoods = [];
				$orderGoods['orderId'] = $orderId;
				$orderGoods['goodsId'] = $auction->goodsId;
				$orderGoods['goodsNum'] = 1;
				$orderGoods['goodsPrice'] = $auction->currPrice;
				$orderGoods['goodsSpecId'] = 0;
				$specNams = [];
				$specs = $this->getSpecs($auction->goodsId);
				if(!empty($specs)){
					foreach($specs as $spkey =>$svv){
						$specNams[] = $svv['name']."：".$svv['list'][0]['itemName'];
					}
					$orderGoods['goodsSpecNames'] = implode('@@_@@',$specNams);
				}
				$orderGoods['goodsType'] = 0;
				$orderGoods['goodsName'] = $auction->goodsName;
				$orderGoods['goodsImg'] = $auction->goodsImg;
				$orderGoods['commissionRate'] = WSTGoodsCommissionRate($goods['goodsCatId']);
				//计算订单佣金
				$commissionFee = 0;
				if((float)$orderGoods['commissionRate']>0){
					$orderGoodscommission = round($orderGoods['goodsPrice']*1*$orderGoods['commissionRate']/100,2);
					$orderGoods["orderGoodscommission"] = $orderGoodscommission;
                	$commissionFee += $orderGoodscommission;
				}
				// 记录商品获得的积分及 获得积分可抵扣的金额
				$orderGoods['getScoreVal'] = WSTMoneyGiftScore($orderGoods['goodsPrice']*1);
				// 获得的积分可抵扣金额
				$orderGoods['getScoreMoney'] = WSTScoreToMoney($orderGoods['getScoreVal']);

				Db::name('order_goods')->insert($orderGoods);

				model('common/orders')->where('orderId',$orderId)->update(['commissionFee'=>$commissionFee]);

				//建立订单记录
				$logArr = [];
				$logOrder = [];
				$logOrder['orderId'] = $orderId;
				$logOrder['orderStatus'] = 0;
				$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>'order_success']);
				$logOrder['logUserId'] = $userId;
				$logOrder['logType'] = 0;
				$logOrder['logTime'] = date('Y-m-d H:i:s');
				$logArr[] = $logOrder;
				$logOrder = [];
				$logOrder['orderId'] = $orderId;
				$logOrder['orderStatus'] = -2;
				$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>'order_success_has_pay']);
				$logOrder['logUserId'] = $userId;
				$logOrder['logType'] = 0;
				$logOrder['logTime'] = date('Y-m-d H:i:s');
				$logArr[] = $logOrder;
				Db::name('log_orders')->insertAll($logArr);

				if($deliverType==1){//自提
					//自提订单（已支付）发送核验码
					$tpl = WSTMsgTemplates('PHONE_USER_ORDER_VERIFICATCODE');
			        if( $tpl['tplContent']!='' && $tpl['status']=='1'){

			        	$userPhone = $order['userPhone'];
			        	$areaCode = (int)$order['areaCode'];
			        	$storeId = $order['storeId'];
			        	$store = Db::name("stores")->where(["storeId"=>$storeId])->field("storeName,storeAddress")->find();
			        	$storeName = $store["storeName"];
			        	$storeAddress = $store["storeAddress"];
			        	$splieVerificationCode = join(" ",str_split($order['verificationCode'],4));
			            $params = ['tpl'=>$tpl,'params'=>['MALL_NAME'=>WSTConf('CONF.mallName'),'ORDER_CODE'=>$splieVerificationCode,'SHOP_NAME'=>$storeName,'SHOP_ADDRESS'=>$storeAddress]];
			            model("common/LogSms")->sendSMS(0,$areaCode.$userPhone,$params,'submit','',$userId,0);
			        }
			    }

				//给店铺增加提示消息
				$tpl = WSTMsgTemplates('ORDER_SUBMIT');
		        if($tpl['tplContent']!='' && $tpl['status']=='1'){
		            $find = ['${ORDER_NO}'];
		            $replace = [$orderNo];

		            $msg = array();
		            $msg["shopId"] = $auction->shopId;
		            $msg["tplCode"] = $tpl["tplCode"];
		            $msg["msgType"] = 1;
		            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
		            $msg["msgJson"] = ['from'=>1,'dataId'=>$orderId];
		            model("common/MessageQueues")->add($msg);
		        }
				//判断是否需要发送商家短信
	            $tpl = WSTMsgTemplates('PHONE_SHOP_SUBMIT_ORDER');
				if((int)WSTConf('CONF.smsOpen')==1 && $tpl['tplContent']!='' && $tpl['status']=='1'){

					$params = ['tpl'=>$tpl,'params'=>['ORDER_NO'=>$orderNo]];

					$msg = array();
					$tplCode = "PHONE_SHOP_SUBMIT_ORDER";
					$msg["shopId"] = $auction->shopId;
		            $msg["tplCode"] = $tplCode;
		            $msg["msgType"] = 2;
		            $msg["paramJson"] = ['CODE'=>$tplCode,'method'=>'cancel','params'=>$params];
		            $msg["msgJson"] = "";
		            model("common/MessageQueues")->add($msg);
				}

		        //微信消息
		        if((int)WSTConf('CONF.wxenabled')==1){
		            $params = [];
		            $params['ORDER_NO'] = $orderNo;
	                $params['ORDER_TIME'] = date('Y-m-d H:i:s');
		            $params['GOODS'] = $auction->goodsName."*1";
		            $params['MONEY'] = $order['realTotalMoney'];
		            $params['ADDRESS'] = $order['userAddress']." ".$order['userName'];
		            $params['PAY_TYPE'] = WSTLangPayType(1);

			       	$msg = array();
					$tplCode = "WX_ORDER_SUBMIT";
					$msg["shopId"] = $auction->shopId;
		            $msg["tplCode"] = $tplCode;
		            $msg["msgType"] = 4;
		            $msg["paramJson"] = ['CODE'=>$tplCode,'URL'=>Url('wechat/orders/sellerorder','',true,true),'params'=>$params];
		            $msg["msgJson"] = "";
		            model("common/MessageQueues")->add($msg);

			    }
			}
		    Db::commit();
			return WSTReturn(lang('auction_order_submit_success'), 1,$orderunique);
		}catch (\Exception $e) {
			print_r($e);
            Db::rollback();
            return WSTReturn(lang('auction_operation_fail'),-1);
        }
	}

	/**
	 * 定时任务
	 */
	public function scanTask(){
		$cron = Db::name('crons')->where('cronCode','autoAuctionEnd')->find();
		$runNum = 0;
		if($cron && $cron['isEnable']==1){
			 $cron['cronJson'] = unserialize($cron['cronJson']);
			 $runNum = (int)$cron['cronJson'][0]['fieldVal'];
		}
		//获取到期的拍卖
		$date = date('Y-m-d H:i:s');
		$dbo = $this
            ->alias('au')
            ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
            ->field('au.*,aul.goodsName')
            ->where('endTime<"'.$date.'" and auctionStatus=1 and dataFlag=1 and isClose=0');
		if($runNum>0)$dbo->limit($runNum);
		$rs = $dbo->select();
		if(!empty($rs)){
			 $acutionLog = [];
		     $auctionConf = $this->getConf('Auction');
		     $auctionConf['endPayDate'] = ((int)$auctionConf['endPayDate']==0)?3:(int)$auctionConf['endPayDate'];
		     Db::startTrans();
		     try{
		        //先改变拍卖状态
	            foreach ($rs as $key => $v) {
	             	$al = Db::name('auction_logs')->where(['auctionId'=>$v->auctionId,'isTop'=>1])->find();
	             	if(!empty($al)){
	             		$v->bidLogId = $al['id'];
	             		$acutionLog[$v->auctionId] = $al;
	             		$v->isClose = 1;
	             	}else{
                        $v->isClose = 2;
	             	}
	             	$v->endPayTime = date('Y-m-d H:i:s',strtotime("+".(int)$auctionConf['endPayDate']." day"));
	             	$v->save();
                    $bidUserId = empty($al)?0:$al['userId'];
	             	//退回除中标人以外的保证金
		            $logUsers = Db::name('auction_moneys')
                                ->where("cautionStatus=1 and auctionId=".$v->auctionId." and moneyType=1 and userId !=".$bidUserId)
                                ->where('payType', ['=','wallets'], ['=','ccgwpays'], 'or')
                                ->field("userId,createTime,lockCashMoney")
                                ->select();
		            $logUserIds = array();
					foreach ($logUsers as $lkey => $lv) {
		             	$lm = [];
						$lm['targetType'] = 0;
						$lm['targetId'] = $lv['userId'];
						$lm['dataId'] = $v->auctionId;
						$lm['dataSrc'] = 'auction';
						$lm['remark'] = json_encode(['type'=>'lang_params','key'=>'auction_refund_caution_money','params'=>[$v['goodsName'],$v->cautionMoney]]);
						$lm['moneyType'] = 1;
						$lm['money'] = $v->cautionMoney;
						$lm['payType'] = '0';
						$lm['tradeNo'] = '';
						$lm['createTime'] = date('Y-m-d H:i:s');
						model('common/LogMoneys')->add($lm);

						if((int)WSTConf('CONF.wxenabled')==1){
				        	$params = [];
			                $params['GOODS'] = $v['goodsName'];
			                $params['JOIN_TIME'] = $lv['createTime'];
		                    $params['ASTART_TIME'] = $v['startTime'];
		                    $params['RESULT'] = lang('auction_end_back_caution_money');
				            WSTWxMessage(['CODE'=>'WX_AUCTION_USER_RESULT','userId'=>$lv['userId'],'params'=>$params]);
				        }

						model('common/users')->where(["userId"=>$lv['userId']])->setInc("rechargeMoney",$lv['lockCashMoney']);
						$logUserIds[] = $lv['userId'];
		            }
		            if(count($logUserIds)>0){
		            	$refundTime = date('Y-m-d H:i:s');
			            Db::name('auction_moneys')
			              ->where('cautionStatus=1 and auctionId='.$v->auctionId.' and moneyType=1 and userId in('.implode(',',$logUserIds).')')
			              ->update(['cautionStatus'=>2,'refundStatus'=>2,'refundTime'=>$refundTime]);
                    }

                    Db::name('auction_moneys')->where("cautionStatus=1 and auctionId=".$v->auctionId." and moneyType=1 and userId !=".$bidUserId)
		                                      ->where('payType', ['=','weixinpays'], ['=','app_weixinpays'], ['=','alipays'], 'or')
		                                      ->update(["refundStatus"=>1]);
	             }
	             //发送拍卖消息
	             foreach ($rs as $key => $v) {
	             	$log = isset($acutionLog[$v->auctionId])?$acutionLog[$v->auctionId]:[];
	             	//获取商家资料
	             	$shop = Db::name('shops')->where('shopId',$v->shopId)->field('userId')->find();
	             	//发送系统消息-商家
	             	$tpl = WSTMsgTemplates('AUCTION_SHOP_RESULT');
			        if($tpl['tplContent']!='' && $tpl['status']=='1'){
			            $find = ['${GOODS}','${ASTART_TIME}','${RESULT}'];
			            $replace = [$v->goodsName,$v->startTime,!empty($log)?WSTLang('auction_success'):WSTLang('auction_streaming')];

			        	$msg = array();
			            $msg["shopId"] = $v->shopId;
			            $msg["tplCode"] = $tpl["tplCode"];
			            $msg["msgType"] = 1;
			            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
			            $msg["msgJson"] = ['from'=>'auction','dataId'=>$v->auctionId];
			            model("common/MessageQueues")->add($msg);
			        }
			        //发送系统消息-用户
			        if(!empty($log) && isset($log['userId'])){
			        	$tpl = WSTMsgTemplates('AUCTION_USER_RESULT');
				        if($tpl['tplContent']!='' && $tpl['status']=='1'){
				            $find = ['${GOODS}','${JOIN_TIME}','${ASTART_TIME}','${RESULT}'];
				            $replace = [$v->goodsName,$log['createTime'],$v->startTime,WSTLang('auction_success')];
				            WSTSendMsg($log['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>'auction','dataId'=>$v->auctionId]);
				        }
			        }
			        //发送微信消息-商家
			        if((int)WSTConf('CONF.wxenabled')==1){
			            $params = [];
			            $params['GOODS'] = $v->goodsName;
		                $params['ASTART_TIME'] = $v->startTime;
		                $params['RESULT'] = !empty($log)?WSTLang('auction_success'):WSTLang('auction_streaming');

				        $msg = array();
						$tplCode = "WX_AUCTION_SHOP_RESULT";
						$msg["shopId"] = $v['shopId'];
			            $msg["tplCode"] = $tplCode;
			            $msg["msgType"] = 4;
			            $msg["paramJson"] = ['CODE'=>$tplCode,'params'=>$params];
			            $msg["msgJson"] = "";
			            model("common/MessageQueues")->add($msg);
				        if(!empty($log) && isset($log['userId'])){
				        	$params = [];
			                $params['GOODS'] = $v->goodsName;
			                $params['JOIN_TIME'] = $log['createTime'];
		                    $params['ASTART_TIME'] = $v->startTime;
		                    $params['RESULT'] = WSTLang('auction_success');
				            WSTWxMessage(['CODE'=>'WX_AUCTION_USER_RESULT','userId'=>$log['userId'],'params'=>$params]);
				        }
				    }

			    }
			    Db::commit();
			}catch (\Exception $e) {
			    Db::rollback();
			}
		}
		//计算逾期未支付拍卖，没收保证金
		$dbo = Db::name('auctions')
                ->alias('au')
                ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
                ->where('endPayTime<"'.$date.'" and bidLogId!=0 and isPay=0 and isClose=1')
		        ->field('bidLogId,au.auctionId,shopId,aul.goodsName,cautionMoney,startTime');
		if($runNum>0)$dbo->limit($runNum);
		$rs = $dbo->select();
		if(!empty($rs)){
			Db::startTrans();
		    try{
			    foreach($rs as $key =>$v){
			    	Db::name('auctions')->where('auctionId',$v['auctionId'])->update(['isClose'=>2]);
	                $auctionlog = Db::name('auction_logs')->where('id',$v['bidLogId'])->field('userId,createTime')->find();
	                $auctionMoney = Db::name('auction_moneys')->where(['userId'=>$auctionlog['userId'],'auctionId'=>$v['auctionId']])->field('id')->find();
				    $shop = Db::name('shops')->where('shopId',$v['shopId'])->field('userId')->find();
				    //将保证金划拨给商家
				    $lm = [];
					$lm['targetType'] = 1;
					$lm['targetId'] = $v['shopId'];
					$lm['dataId'] = $v['auctionId'];
					$lm['dataSrc'] = 'auction';
					$lm['remark'] = json_encode(['type'=>'lang_params','key'=>'auction_overtime_not_pay_order','params'=>[$v['goodsName'],$v['cautionMoney']]]);
					$lm['moneyType'] = 1;
					$lm['money'] = $v['cautionMoney'];
					$lm['payType'] = '0';
					$lm['tradeNo'] = '';
					$lm['createTime'] = date('Y-m-d H:i:s');
					model('common/LogMoneys')->add($lm);
					//改变保证金状态
					Db::name('auction_moneys')->where('id',$auctionMoney['id'])->update(['cautionStatus'=>-1]);
				    //发送系统消息-商家
		            $tpl = WSTMsgTemplates('AUCTION_SHOP_RESULT');
				    if($tpl['tplContent']!='' && $tpl['status']=='1'){
				        $find = ['${GOODS}','${ASTART_TIME}','${RESULT}'];
				        $replace = [$v['goodsName'],$v['startTime'],WSTLang('auction_overtime_not_pay_order_tips')];

				    	$msg = array();
			            $msg["shopId"] = $v['shopId'];
			            $msg["tplCode"] = $tpl["tplCode"];
			            $msg["msgType"] = 1;
			            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
			            $msg["msgJson"] = ['from'=>'auction','dataId'=>$v['auctionId']];
			            model("common/MessageQueues")->add($msg);
				    }
				    //发送系统消息-用户
				    $tpl = WSTMsgTemplates('AUCTION_USER_RESULT');
					if($tpl['tplContent']!='' && $tpl['status']=='1'){
					    $find = ['${GOODS}','${JOIN_TIME}','${ASTART_TIME}','${RESULT}'];
					    $replace = [$v['goodsName'],$auctionlog['createTime'],$v['startTime'],WSTLang('auction_overtime_not_pay_order_tips')];
					    WSTSendMsg($auctionlog['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>'auction','dataId'=>$v['auctionId']]);
				    }
				    //发送微信消息-商家
				    if((int)WSTConf('CONF.wxenabled')==1){
				        $params = [];
				        $params['GOODS'] = $v['goodsName'];
			            $params['ASTART_TIME'] = $v['startTime'];
			            $params['RESULT'] = WSTLang('auction_overtime_not_pay_order_tips');

					    $msg = array();
						$tplCode = "WX_AUCTION_SHOP_RESULT";
						$msg["shopId"] = $v['shopId'];
			            $msg["tplCode"] = $tplCode;
			            $msg["msgType"] = 4;
			            $msg["paramJson"] = ['CODE'=>$tplCode,'params'=>$params];
			            $msg["msgJson"] = "";
			            model("common/MessageQueues")->add($msg);

					    $params = [];
				        $params['GOODS'] = $v['goodsName'];
				        $params['JOIN_TIME'] = $auctionlog['createTime'];
			            $params['ASTART_TIME'] = $v['startTime'];
			            $params['RESULT'] = WSTLang('auction_success');
					    WSTWxMessage(['CODE'=>'WX_AUCTION_USER_RESULT','userId'=>$auctionlog['userId'],'params'=>$params]);
					}
			    }
			    Db::commit();
			}catch (\Exception $e) {
			    Db::rollback();
			}
		}
		return WSTReturn(lang('auction_operation_success'),1);
	}

	public function batchRefund(){
		$amoneys = Db::name('auction_moneys')->where(['refundStatus'=>1])->select();
		$am = null;
		$wm = null;
		$pm = null;
		for($i=0,$j=count($amoneys);$i<$j;$i++){
			$amoney = $amoneys[$i];
			if($amoney['payType']=='weixinpays'){
				if($wm==null) $wm = new Weixinpays();
				$wm->auctionRefund($amoney);
			}else if($amoney['payType']=='app_weixinpays'){
				if($pm==null) $pm = new WeixinpaysApp();
				$pm->auctionRefund($amoney);
			}else if($amoney['payType']=='alipays'){
				if($am==null) $am = new Alipays();
				$am->auctionRefund($amoney);
			}
		}
	}

	/**
	 * 获取我的保证金
	 */
	public function pageQueryByMoney($uId=0){
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		$page = $this->alias('a')
                ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=a.auctionId and aul.langId='.WSTCurrLang())
                ->join('__AUCTION_MONEYS__ m','a.auctionId=m.auctionId','inner')
                ->where(['m.userId'=>$userId,'m.moneyType'=>1])
                ->order('m.createTime desc')
                ->field('a.auctionId,aul.goodsName,a.goodsImg,a.currPrice,a.startTime,a.endTime,m.cautionStatus,m.cautionMoney')
                ->paginate(input('limit/d'))->toArray();
        if(count($page['data'])>0){
        	$time = time();
        	foreach ($page['data'] as $key => $v) {
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
        return WSTReturn('',1,$page);
	}

	/**
	 * 获取热门拍卖
	 */
	public function getHotActions($num){
		$rs = Db::name('auctions')
                ->alias('au')
                ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
                ->where(['dataFlag'=>1,'isClose'=>0,'auctionStatus'=>1])
                ->limit($num)->order('auctionNum desc,visitNum desc')
                ->field('au.auctionId,aul.goodsName,goodsImg,currPrice')->cache(600)->select();
		return $rs;
	}

	public function complateAuctionRefund($obj){
		Db::startTrans();
		try{
			$id = $obj["id"];
			$refundTradeNo = $obj["refundTradeNo"];
			$amoney = Db::name('auction_moneys')->alias('am')
					->join('__AUCTIONS__ a','am.auctionId=a.auctionId','inner')
                    ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=a.auctionId and aul.langId='.WSTCurrLang())
					->field('am.userId,am.auctionId,am.createTime,am.payType,am.cautionMoney,aul.goodsName,a.startTime')
					->where(["id"=>$id,"refundStatus"=>1])->find();
			if(!empty($amoney)){

				if((int)WSTConf('CONF.wxenabled')==1){
		        	$params = [];
	                $params['GOODS'] = $amoney['goodsName'];
	                $params['JOIN_TIME'] = $amoney['createTime'];
	                $params['ASTART_TIME'] = $amoney['startTime'];
	                $params['RESULT'] = lang('auction_end_back_caution_money');
		            WSTWxMessage(['CODE'=>'WX_AUCTION_USER_RESULT','userId'=>$amoney['userId'],'params'=>$params]);
		        }

		        $refundTime = date('Y-m-d H:i:s');
	            Db::name('auction_moneys')
	              ->where(["id"=>$id,"refundStatus"=>1])
	              ->update(['cautionStatus'=>2,'refundStatus'=>2,'refundTradeNo'=>$refundTradeNo,'refundTime'=>$refundTime]);
		      	Db::commit();
			}
         	return WSTReturn(lang('auction_refund_success'),1);
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return WSTReturn(lang('auction_refund_fail'),-1);
	   	}
	}

	/**
	 * 获取待审核的拍卖
	 */
	public function getAuditCount(){
         return $this->where(['dataFlag'=>1,'auctionStatus'=>0])->count();
	}


	/*
    * 商品详情分享海报
    */
    public function createPoster($userId,$qr_code,$outImg){

        $id = input("id");
        $goods = Db::name("goods g")->join("auctions a","g.goodsId=a.goodsId","inner")
                ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=a.auctionId and aul.langId='.WSTCurrLang())
		        ->where(['a.auctionId'=>$id])
		        ->field("g.goodsId,aul.goodsName,a.goodsImg,a.currPrice shopPrice,g.goodsUnit")
		        ->find();

        //生成二维码图片
        $share_bg = WSTConf('CONF.resourceDomain').'/'.WSTConf("CONF.goodsPosterBg");;
        $share_bg = imagecreatefromstring(file_get_contents($share_bg));
        $new_qrcode = imagecreatefromstring(file_get_contents($qr_code));

        $share_width = imagesx($share_bg);//二维码图片宽度
        $share_height = imagesy($share_bg);//二维码图片高度
        $new_width = imagesx($new_qrcode);//logo图片宽度
        $new_height = imagesy($new_qrcode);//logo图片高度
        $new_qr_width = $share_width / 5;
        $new_qr_height = $new_qr_width;
        $from_width = ($share_width - $new_qr_width) / 2;

        //重新组合图片并调整大小
        imagecopyresampled($share_bg, $new_qrcode, 495, 925, 0, 0, $new_qr_width, $new_qr_height, $new_width, $new_height);
        imagedestroy($new_qrcode);
        unlink($qr_code);
        $new_qrcode = WSTConf('CONF.resourceDomain').'/'.$goods['goodsImg'];
        $new_qrcode = imagecreatefromstring(file_get_contents($new_qrcode));

        $new_width = imagesx($new_qrcode);//logo图片宽度
        $new_height = imagesy($new_qrcode);//logo图片高度
        $new_qr_width = $share_width-140;
        $new_qr_height = $new_qr_width;
        $from_width = ($share_width - $new_qr_width) / 2;
        //重新组合图片并调整大小
        imagecopyresampled($share_bg, $new_qrcode, $from_width, 70, 0, 0, $new_qr_width, $new_qr_height, $new_width, $new_height);

        // 字体文件
        $textcolor = imagecolorallocate($share_bg,50,50,50);
        $textcolor2 = imagecolorallocate($share_bg,0,0,0);
        $font = WSTRootPath().'/extend/verify/verify/ttfs/SourceHanSerifCN-Medium.otf';//思源字体
        $text = WSTImageAutoWrap(22, 0, $font, $goods['goodsName'],630);
        imagettftext($share_bg, 22, 0, 70, 760, $textcolor, $font, $text);
        $vh = 1100;
        if($userId>0){
        	$user = Db::name("users")->where(["userId"=>$userId])->field("userPhoto,userName,loginName")->find();
        	//$user["userPhoto"] = '';
	        $new_qrcode = WSTUserPhoto($user["userPhoto"]);
	        if(substr($new_qrcode,0,4)!='http' && $new_qrcode){
	        	$new_qrcode = WSTConf('CONF.resourceDomain').'/'.($user["userPhoto"]?$user["userPhoto"]:WSTConf('CONF.userLogo'));

	        	$tmpImg = WSTRootPath().'/upload/shares/auction/'.date("Y-m").'/'.$userId.'.jpg';
		        $new_qrcode = WSTCutFillet($new_qrcode, $tmpImg);
		        $new_qrcode = imagecreatefromstring(file_get_contents($new_qrcode));
		        unlink($tmpImg);
	        }else{
	        	$new_qrcode = imagecreatefromstring(file_get_contents($new_qrcode));
	        }

	        //重新组合图片并调整大小
        	WSTImagecopymergeAlpha($share_bg, $new_qrcode, 70, 954, 0, 0, 100,  100, 100);

	        $userName = $user["userName"]?$user["userName"]:$user["loginName"];
	        $text2 = mb_convert_encoding($userName, "html-entities", "utf-8"); //转成html编码
        	imagettftext($share_bg, 22, 0, 185, 1010, $textcolor2, $font, $text2);
        }else{
        	$vh = 1030;
        }
        $text2 = mb_convert_encoding(lang('auction_curr_price').'：', "html-entities", "utf-8"); //转成html编码
        imagettftext($share_bg, 18, 0, 80, $vh, $textcolor2, $font, $text2);

        $textcolor3 = imagecolorallocate($share_bg,255,0,54);
        $text = WSTImageAutoWrap(20, 0, $font, lang('currency_symbol').(float)$goods['shopPrice'],700);
        imagettftext($share_bg, 24, 0, 210, $vh, $textcolor3, $font, $text);

        $text = mb_convert_encoding(lang('auction_share_tip_words'), "html-entities", "utf-8"); //转成html编码
        imagettftext($share_bg, 24, 0, 120, 1255, $textcolor, $font, $text);
        //输出图片
        $shareImg = WSTRootPath().'/'.$outImg;
        imagepng($share_bg, $shareImg);
        imagedestroy($new_qrcode);
    	imagedestroy($share_bg);

        return WSTReturn("",1,["shareImg"=>$outImg]);
    }

    public function checkSupportStores(){
      $auctionId = (int)input("auctionId");
      $rs = Db::name("auctions")->where(['auctionId'=>$auctionId])->field("shopId")->find();
      $shopId = (int)$rs["shopId"];
      $where = [];
      $where[] = ["shopId","=",$shopId];
      $where[] = ["dataFlag","=",1];
      $where[] = ["storeStatus","=",1];
      $cnt = Db::name("stores")->where($where)->count();
      $storeMap = [];
      $rs = ($cnt>0)?1:0;
      return $rs;
    }
}
