<?php
namespace addons\integral\model;
use think\addons\BaseModel as Base;
use wstmart\common\model\GoodsCats;
use think\Db;
use wstmart\common\model\LogSms;
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
 * 积分商城插件
 */
class Integrals extends Base{

	public function getConfigs(){
		$data = cache('integral_sms');
		if(!$data){
			$rs = Db::name('addons')->where('name','Integral')->field('config')->find();
		    $data =  json_decode($rs['config'],true);
		    cache('integral_sms',$data,31622400);
		}
		return $data;
	}
    /***
     * 安装插件
     */
    public function installMenu(){
    	Db::startTrans();
		try{
			$hooks = ['afterCancelOrder','mobileDocumentIndex','wechatDocumentIndex'];
			$this->bindHoods("Integral", $hooks);
			//管理员后台
            $datas = [];
            $menuLangParams = [
                1=>['menuName'=>'積分商城'],
                2=>['menuName'=>'积分商城'],
                3=>['menuName'=>'Integral Mall'],
            ];
            $menuId = Db::name('menus')->insertGetId(["parentId"=>93,"menuSort"=>1,"dataFlag"=>1,"isShow"=>1,"menuMark"=>"integral"]);
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
                    1=>['privilegeName_00'=>'查看積分商城活動','privilegeName_04'=>'積分商城活動操作','privilegeName_03'=>'刪除積分商城商品'],
                    2=>['privilegeName_00'=>'查看积分商城活动','privilegeName_04'=>'积分商城商品操作','privilegeName_03'=>'删除积分商城商品'],
                    3=>['privilegeName_00'=>'View integral mall activities','privilegeName_04'=>'integral mall product operation','privilegeName_03'=>'Delete integral mall product'],
                ];
                $privilegeIds = [];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"INTEGRAL_TGHD_00","isMenuPrivilege"=>1,"privilegeUrl"=>"/addon/integral-goods-pageByAdmin","otherPrivilegeUrl"=>"/addon/integral-goods-pageQueryByAdmin,/addon/integral-goods-pageAuditQueryByAdmin","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'00'];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"INTEGRAL_TGHD_04","isMenuPrivilege"=>0,"privilegeUrl"=>"","otherPrivilegeUrl"=>"/addon/integral-goods-allow,/addon/integral-goods-illegal","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'04'];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"INTEGRAL_TGHD_03","isMenuPrivilege"=>0,"privilegeUrl"=>"/addon/integral-goods-delByAdmin","otherPrivilegeUrl"=>"","dataFlag"=>1,"isEnable"=>1]);
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
			$this->addNavMenu();
			$this->addMobileBtn();
            //新增上传目录
            $dataLangParams = [
                1=>['dataName'=>'積分商城'],
                2=>['dataName'=>'积分商城'],
                3=>['dataName'=>'Integral Mall'],
            ];
            $datas = [];
            $dataId = Db::name('datas')->insertGetId(['catId'=>3,'dataVal'=>'integral']);
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['dataId'] = $dataId;
                $data['langId'] = $v['id'];
                $data['dataName'] = $dataLangParams[$v['id']]['dataName'];
                $datas[] = $data;
            }
            Db::name('datas_langs')->insertAll($datas);
			installSql("integral");
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
			$hooks = ['afterCancelOrder','mobileDocumentIndex','wechatDocumentIndex'];
			$this->unbindHoods("Integral", $hooks);
            $menuId = Db::name('menus')->where(["menuMark"=>"integral"])->value('menuId');
            Db::name('menus')->where(["menuMark"=>"integral"])->delete();
            Db::name('menus_langs')->where(['menuId'=>$menuId])->delete();
            $privilegeIds = Db::name('privileges')->where("privilegeCode","like","INTEGRAL_%")->column('privilegeId');
            Db::name('privileges')->where("privilegeCode","like","INTEGRAL_%")->delete();
            Db::name('privileges_langs')->where([['privilegeId','in',$privilegeIds]])->delete();

           	$position = Db::name('ad_positions')->where(["positionCode"=>'ads-integral'])->find();
           	$positionId = (int)$position["positionId"];
           	Db::name('ads')->where(["adPositionId"=>$positionId])->delete();
           	Db::name('ad_positions')->where(["positionCode"=>'ads-integral'])->delete();
            $dataId = Db::name('datas')->where(["dataVal"=>"integral"])->value('id');
            Db::name('datas')->where(["dataVal"=>"integral"])->delete();
            Db::name('datas_langs')->where(["dataId"=>$dataId])->delete();
			uninstallSql("integral");//传入插件名
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
			Db::name('menus')->where(["menuMark"=>"integral"])->update(["isShow"=>$isShow]);
			Db::name('home_menus')->where(["menuMark"=>"integral"])->update(["isShow"=>$isShow]);
			Db::name('navs')->where(["navUrl"=>"addon/integral-goods-lists.html"])->update(["isShow"=>$isShow]);
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
            'navUrl'=>'addon/integral-goods-lists.html',
            'isShow'=>1,
            'isOpen'=>0,
            'navSort'=>0,
            'createTime'=>date('Y-m-d H:i:s')
        ];
        $navId = Db::name('navs')->insertGetId($navData);
        $datas = [];
        $langParams = [
            1=>['navTitle'=>'積分商城'],
            2=>['navTitle'=>'积分商城'],
            3=>['navTitle'=>'Integral mall'],
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
            1=>['btnName'=>'積分商城'],
            2=>['btnName'=>'积分商城'],
            3=>['btnName'=>'Integral mall'],
        ];
        $datas = [];
		$data = array();
		$data["btnSrc"] = 0;
		$data["btnUrl"] = "addon/integral-goods-molists.html";
		$data["btnImg"] = "addons/integral/view/images/integral.png";
		$data["addonsName"] = "Integral";
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
		$data["btnUrl"] = "addon/integral-goods-wxlists.html";
		$data["btnImg"] = "addons/integral/view/images/integral.png";
		$data["addonsName"] = "Integral";
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
			$data["btnUrl"] = "wst://Integral";
			$data["btnImg"] = "addons/integral/view/images/integral.png";
			$data["addonsName"] = "Integral";
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
			$data["btnUrl"] = "/addons/package/pages/integral/goods/list";
			$data["btnImg"] = "addons/integral/view/images/integral.png";
			$data["addonsName"] = "Integral";
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
        $navId = Db::name('navs')->where(['navUrl'=>'addon/integral-goods-lists.html'])->value('id');
        Db::name('navs')->where(['navUrl'=>'addon/integral-goods-lists.html'])->delete();
        Db::name('navs_langs')->where(['navId'=>$navId])->delete();
    }

	public function delMobileBtn(){
        $btnIds =  Db::name('mobile_btns')->where(["addonsName"=>"Integral"])->column('id');
        Db::name('mobile_btns')->where(["addonsName"=>"Integral"])->delete();
        Db::name('mobile_btns_langs')->where([['btnId','in',$btnIds]])->delete();
	}

	/**
	 * 改变积分商城信息
	 */
	public function changeIntegral($params){
		$goodsId = (int)$params['goodsId'];
		$date = date('Y-m-d H:i:s');
		Db::name('integral_goods')
		  ->where(" endTime >='".$date."' and dataFlag=1 and goodsId=".$goodsId)
		  ->update(['integralStatus'=>0]);
	}

	/**
	 * 取消积分商城订单
	 */
	public function cancelOrder($params){
		$orderId = (int)$params['orderId'];
		$order = Db::name('orders')->where('orderId',$orderId)->field('orderCode,extraJson,orderCodeTargetId')->find();
        if($order['orderCode']=='integral'){
            $goods = Db::name('order_goods')->alias('og')
                       ->join('__GOODS__ g','og.goodsId=g.goodsId','inner')
					   ->where('orderId',$orderId)->field('og.*')
					   ->find();
            //处理虚拟产品
			if($goods['goodsType']==1){
	            $extraJson = json_decode($goods['extraJson'],true);
	            foreach ($extraJson as  $ecard) {
	                Db::name('goods_virtuals')->where('id',$ecard['cardId'])
	                      ->update(['orderId'=>0,'orderNo'=>'','isUse'=>0]);
	            }
	            $counts = Db::name('goods_virtuals')->where(['dataFlag'=>1,'goodsId'=>$goods['goodsId'],'isUse'=>0])->count();
	            Db::name('goods')->where('goodsId',$goods['goodsId'])->setField('goodsStock',$counts);
			}
			//修改积分商品库存
			Db::name('integral_goods')->where('id',$order['orderCodeTargetId'])->setDec('orderNum',$goods['goodsNum']);
        }
	}

	public function getSelfShop(){
		$rs = Db::name('shops')->where(["isSelf"=>1,"dataFlag"=>1])->field('shopId,shopSn,userId,shopName')->find();
		return $rs;
	}


    /**
     * 搜索商品
     */
    public function searchGoods($sId = 0){
    	$shop = $this->getSelfShop();
    	$shopId =($sId==0)?(int)$shop["shopId"]:$sId;
    	$goodsCatIdPath = input('goodsCatIdPath');
    	$goodsName = input('post.goodsName');
    	$where = [];
    	$where[] = ['goodsStatus','=',1];
    	$where[] = ['dataFlag','=',1];
    	$where[] = ['shopId','=',$shopId];
    	if($goodsCatIdPath !='')$where[] = ['goodsCatIdPath','like',$goodsCatIdPath."%"];
    	if($goodsName!='')$where[] = ['gl.goodsName','like','%'.$goodsName.'%'];
    	$rs = Db::name('goods')
            ->alias('g')
            ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
            ->where($where)->field('gl.goodsName,gl.goodsSeoKeywords,gl.goodsSeoDesc,g.goodsId,marketPrice,shopPrice,goodsType,goodsImg')->select();
        return WSTReturn('',1,$rs);
    }
	/**
     * 获取商品类别
	 */
	public function getGoodsCats(){
		$rs = Db::name('goods_cats')
                ->alias('gc')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=gc.catId and gcl.langId='.WSTCurrLang())
		        ->where(['dataFlag'=>1,'isShow'=>1])
		        ->order('catSort asc')
		        ->field('parentId pid,gc.catId id,gcl.catName text')
		        ->select();
		return WSTReturn('',1,$rs);
	}
	/**
	 *  获取积分商城商品
	 */
	public function getById($id){
		$where = [];
		$where['ig.id'] = $id;
		$where['ig.dataFlag'] = 1;
		$where['g.dataFlag'] = 1;
		$rs = Db::name('integral_goods')
            ->alias('ig')
            ->join('__INTEGRAL_GOODS_LANGS__ igl','igl.integralId=ig.id and igl.langId='.WSTCurrLang())
            ->join('__GOODS__ g','g.goodsId=ig.goodsId','left')
            ->where($where)->field('g.marketPrice,g.shopPrice,ig.*,igl.goodsName,igl.integralDesc,igl.goodsSeoKeywords,igl.goodsSeoDesc')->find();
        $rs['langParams'] = Db::name('integral_goods_langs')->where(['integralId'=>$id])->column('*','langId');
		return $rs;
	}

	/**
	 * 新增积分商品
	 */
	public function add(){
		$data = input('post.');
		$goodsId = (int)$data['goodsId'];
		$goods = model('common/Goods')->get($goodsId);
		if(empty($goods))return WSTReturn(lang('integral_goods_not_exist'));
		if((float)$data['goodsPrice']<0)return WSTReturn(lang('integral_price_limit_tips'));
		if((int)$data['integralNum']<=0)return WSTReturn(lang('integral_score_limit_tips'));
		if((int)$data['totalNum']<=0)return WSTReturn(lang('integral_number_limit_tips'));
		if($data['startTime']=='' || $data['endTime']=='')return WSTReturn(lang('integral_reqiure_time_tips'));
		if(WSTStrToTime($data['startTime']) > WSTStrToTime($data['endTime']))return WSTReturn(lang('integral_time_limit_tips'));
		//判断是否已经存在同时间的积分商城商品
		$where = [];
		$where['goodsId'] = $data['goodsId'];
		$where['dataFlag'] = 1;
		$whereOr = ' ( ("'.date('Y-m-d H:i:s',WSTStrToTime($data['startTime'])).'" between startTime and endTime) or ( "'.date('Y-m-d H:i:s',WSTStrToTime($data['endTime'])).'" between startTime and endTime) ) ';
		$rn = Db::name('integral_goods')->where($where)->where($whereOr)->Count();

		if($rn>0)return WSTReturn(lang('integral_goods_exist_other_tips'));
		WSTUnset($data,'id,cat_0,illegalRemarks');
		$shopId = (int)$goods['shopId'];
		$data['shopId'] = $shopId;
		$data['dataFlag'] = 1;
		$data['orderNum'] = 0;
		$data['integralStatus'] = 1;
		$data['updateTime'] = date('Y-m-d H:i:s');
		$data['createTime'] = date('Y-m-d H:i:s');
		$id = Db::name('integral_goods')->field('goodsId,goodsImg,goodsPrice,integralNum,totalNum,startTime,endTime,shopId,dataFlag,orderNum,integralStatus,updateTime,createTime')->insertGetId($data);
		if(false !== $id){
            $dataLangs = [];
            foreach (WSTSysLangs() as $key => $v) {
                $dataLang = [];
                $dataLang['integralId'] = $id;
                $dataLang['langId'] = $v['id'];
                $dataLang['goodsName'] = input('langParams'.$v['id'].'goodsName');
                $dataLang['goodsSeoKeywords'] = input('langParams'.$v['id'].'goodsSeoKeywords');
                $dataLang['goodsSeoDesc'] = input('langParams'.$v['id'].'goodsSeoDesc');
                $dataLang['integralDesc'] = input('langParams'.$v['id'].'integralDesc');
                $dataLangs[] = $dataLang;
            }
            Db::name('integral_goods_langs')->insertAll($dataLangs);
            //WSTUseResource(0, $id, $data['goodsImg']);
			return WSTReturn(lang('integral_operation_success'),1);
		}
		return WSTReturn(lang('integral_operation_fail'));
	}

	/**
	 * 编辑商品
	 */
	public function edit(){
		$data = input('post.');
		$goods = model('common/Goods')->get((int)$data['goodsId']);
		if(empty($goods))return WSTReturn(lang('integral_goods_not_exist'));
		if((float)$data['goodsPrice']<0)return WSTReturn(lang('integral_price_limit_tips'));
		if((int)$data['integralNum']<=0)return WSTReturn(lang('integral_score_limit_tips'));
		if((int)$data['totalNum']<=0)return WSTReturn(lang('integral_tips17'));
		if($data['startTime']=='' || $data['endTime']=='')return WSTReturn(lang('integral_reqiure_time_tips'));
		if(WSTStrToTime($data['startTime']) > WSTStrToTime($data['endTime']))return WSTReturn(lang('integral_time_limit_tips'));
		//判断是否已经存在同时间的积分商城
		$id = $data['id'];
		$shopId = (int)$goods['shopId'];
		$where = [];
		$where[] = ['goodsId','=',$data['goodsId']];
		$where[] = ['id','<>',$data['id']];
		$where[] = ['dataFlag','=',1];
		$whereOr = ' ( ("'.date('Y-m-d H:i:s',WSTStrToTime($data['startTime'])).'" between startTime and endTime) or ( "'.date('Y-m-d H:i:s',WSTStrToTime($data['endTime'])).'" between startTime and endTime) ) ';
		$rn = Db::name('integral_goods')->where($where)->where($whereOr)->Count();
		if($rn>0)return WSTReturn(lang('integral_goods_exist_other_tips'));
		WSTUnset($data,'id,shopId,dataFlag,createTime,cat_0,orderNum');
		$data['integralStatus'] = 1;
		$data['updateTime'] = date('Y-m-d H:i:s');
		$result = Db::name('integral_goods')->field('goodsId,goodsImg,goodsPrice,integralNum,totalNum,startTime,endTime,integralStatus,updateTime')->where(['id'=>$id,'shopId'=>$shopId])->update($data);
		if(false !== $result){
            Db::name('integral_goods_langs')->where(['integralId'=>$id])->delete();
            $dataLangs = [];
            foreach (WSTSysLangs() as $key => $v) {
                $dataLang = [];
                $dataLang['integralId'] = $id;
                $dataLang['langId'] = $v['id'];
                $dataLang['goodsName'] = input('langParams'.$v['id'].'goodsName');
                $dataLang['goodsSeoKeywords'] = input('langParams'.$v['id'].'goodsSeoKeywords');
                $dataLang['goodsSeoDesc'] = input('langParams'.$v['id'].'goodsSeoDesc');
                $dataLang['integralDesc'] = input('langParams'.$v['id'].'integralDesc');
                $dataLangs[] = $dataLang;
            }
            Db::name('integral_goods_langs')->insertAll($dataLangs);
            //WSTUseResource(0, $id, $data['goodsImg']);
			return WSTReturn(lang('integral_operation_success'),1);
		}
		return WSTReturn(lang('integral_operation_fail'));
	}

	/**
	 * 删除积分商城商品
	 */
	public function del(){
		$id = (int)input('id');
		$data = [];
		$data['id'] = $id;
        $rs = Db::name('integral_goods')->update(['dataFlag'=>-1],$data);
        //WSTUnuseResource('integral_goods','goodsImg',$id);
        return WSTReturn(lang('integral_operation_success'),1);
	}


	/***
	 * 获取前台积分商品列表
	 */
	public function pageQuery($num=0){
        $num = ($num>0)?$num:input('pagesize/d');
		$goodsCatId = (int)input('catId');
		$goodsName = input('goodsName');
        hook('afterUserSearchWords',['keyword'=>$goodsName]);
		$where = [];
		$now = date("Y-m-d H:i:s");
		if($goodsCatId>0){
			$gc = new GoodsCats();
			$goodsCatIds = $gc->getParentIs($goodsCatId);
			$where[] = ['goodsCatIdPath','like',implode('_',$goodsCatIds).'_%'];
		}
		if($goodsName!='')$where[] = ['ig.goodsName','like','%'.$goodsName.'%'];
		$page = Db::name('integral_goods')->alias('ig')
                  ->join('__INTEGRAL_GOODS_LANGS__ igl','igl.integralId=ig.id and igl.langId='.WSTCurrLang())
                  ->join('__GOODS__ g','ig.goodsId=g.goodsId','inner')
		          ->where('g.dataFlag=1 and g.goodsStatus=1 and ig.dataFlag=1 and ig.integralStatus=1')
		          ->where($where)
		          ->where('ig.startTime', '<=', $now)
		          ->where('ig.endTime', '>=', $now)
		          ->field('igl.goodsName,g.goodsImg,g.marketPrice,g.shopPrice,ig.*')
		          ->order('ig.updateTime desc,ig.startTime asc,id desc')
		          ->paginate($num)->toArray();
		if(count($page)>0){
			$time = time();
			foreach($page['data'] as $key =>$v){
				$page['data'][$key]['goodsImg'] = WSTImg($v['goodsImg']);
				if(WSTStrToTime($v['startTime'])<=$time && WSTStrToTime($v['endTime'])>=$time){
        			$page['data'][$key]['status'] = 1;
        		}else if(WSTStrToTime($v['startTime'])>$time){
                    $page['data'][$key]['status'] = 0;
        		}else{
        			$page['data'][$key]['status'] = -1;
        		}
        		if($v['orderNum']>=$v['totalNum']){
        			$page['data'][$key]['status'] = -1;
        		}
			}
		}
		return $page;
	}

	/**
	 * 获取积分商品详情
	 */
	public function getBySale($id,$uId=0){
		$key = input('key');
		$where = ['dataFlag'=>1,'id'=>$id];
		$gu = Db::name('integral_goods')
            ->alias('ig')
            ->join('__INTEGRAL_GOODS_LANGS__ igl','igl.integralId=ig.id and igl.langId='.WSTCurrLang())
            ->field('ig.*,igl.goodsName,igl.integralDesc,igl.goodsSeoKeywords,igl.goodsSeoDesc')
            ->where($where)->find();
		$viKey = WSTShopEncrypt($gu['shopId']);
        if($key!=''){
            if($viKey!=$key && $gu['integralStatus']!=1)return [];
        }else{
        	if($gu['integralStatus']!=1)return [];
        }
		$goodsId = $gu['goodsId'];
		if(empty($gu))return [];
		//$gu = $gu->toArray();
		$rs = Db::name('goods')
            ->alias('g')
            ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
            ->field('g.*,gl.goodsDesc')
            ->where(['g.goodsId'=>$goodsId,'dataFlag'=>1])->find();
		if(!empty($rs)){
			$rs['goodsDesc'] = htmlspecialchars_decode($rs['goodsDesc']);
			$rs['goodsDesc'] = str_replace('${DOMAIN}',WSTConf('CONF.resourcePath'),$rs['goodsDesc']);
			Db::name('goods')->where('goodsId',$goodsId)->setInc('visitNum',1);
			$rs = array_merge($rs,$gu);
			$time = time();
			if(WSTStrToTime($rs['startTime'])<=$time && WSTStrToTime($rs['endTime'])>=$time){
        		$rs['status'] = 1;
        	}else if(WSTStrToTime($rs['startTime'])>$time){
                $rs['status'] = 0;
        	}else{
        	    $rs['status'] = -1;
        	}
        	if($rs['orderNum']>=$rs['totalNum'])$rs['status'] = -1;
			$rs['read'] = false;
			//判断是否可以公开查看
			if($rs['goodsStatus']==0 )return [];
			if($key!='')$rs['read'] = true;
			//获取店铺信息
			$rs['shop'] = model('common/shops')->getShopInfo((int)$rs['shopId']);

			if(empty($rs['shop']))return [];
			$goodsCats = Db::name('cat_shops')->alias('cs')
                ->join('__GOODS_CATS__ gc','cs.catId=gc.catId and gc.dataFlag=1','left')
                ->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=gc.catId and gcl.langId='.WSTCurrLang())
                ->join('__SHOPS__ s','s.shopId = cs.shopId','left')
			    ->where('cs.shopId',$rs['shopId'])->field('cs.shopId,s.shopTel,gc.catId,gcl.catName')->select();
			$rs['shop']['catId'] = $goodsCats[0]['catId'];
			$rs['shop']['shopTel'] = $goodsCats[0]['shopTel'];
			$cat = [];
			foreach ($goodsCats as $v){
				$cat[] = $v['catName'];
			}
			$rs['shop']['cat'] = implode('，',$cat);

			$gallery = [];
			$gallery[] = $rs['goodsImg'];
			if($rs['gallery']!=''){
				$tmp = explode(',',$rs['gallery']);
				$gallery = array_merge($gallery,$tmp);
			}
			$rs['gallery'] = $gallery;
			if($rs['isSpec']==1){
				//获取销售规格
				$sales = Db::name('goods_specs')->where(['goodsId'=>$goodsId,'isDefault'=>1])->field('id,isDefault,productNo,specIds,marketPrice,specPrice,specStock')->find();
				$specIds = [];
				if(!empty($sales)){
					$str = explode(':',$sales['specIds']);
					foreach ($str as $skey => $sv) {
						if(!in_array($sv,$specIds))$specIds[] = $sv;
					}
					sort($str);
					unset($sales['specIds']);
					$rs['saleSpec'][implode(':',$str)] = $sales;
				}
				//获取默认规格值
				$specs = Db::name('spec_cats')->alias('gc')
                        ->join('__SPEC_ITEMS__ sit','gc.catId=sit.catId','inner')
                        ->join('__SPEC_CATS_LANGS__ scl','gc.catId=scl.catId and scl.langId='.WSTCurrLang())
                        ->join('__SPEC_ITEMS_LANGS__ sil','sit.itemId=sil.itemId and sil.langId='.WSTCurrLang())
                        ->where(['sit.goodsId'=>$goodsId,'gc.isShow'=>1,'sit.dataFlag'=>1])
                        ->field('gc.isAllowImg,scl.catName,sit.catId,sit.itemId,siL.itemName,sit.itemImg')
                        ->order('gc.isAllowImg desc,gc.catSort asc,gc.catId asc')->select();
				foreach ($specs as $key =>$v){
					if(in_array($v['itemId'],$specIds)){
						$rs['spec'][$v['catId']]['name'] = $v['catName'];
						$rs['spec'][$v['catId']]['list'][] = $v;
					}
				}

			}
			//获取商品属性
			$rs['attrs'] = Db::name('attributes')->alias('a')
                            ->join('__ATTRIBUTES_LANGS__ al','a.attrId=al.attrId and al.langId='.WSTCurrLang())
                            ->join('goods_attributes ga','a.attrId=ga.attrId','inner')
                            ->join('__GOODS_ATTRIBUTES_LANGS__ gal','ga.id=gal.goodsAttrId and gal.langId='.WSTCurrLang())
                            ->where(['a.isShow'=>1,'dataFlag'=>1,'ga.goodsId'=>$goodsId])->field('al.attrName,gal.attrVal')
                            ->order('attrSort asc')->select();
			//获取商品评分
			$rs['scores'] = Db::name('goods_scores')->where('goodsId',$goodsId)->field('totalScore,totalUsers')->find();
			$rs['scores']['totalScores'] = ($rs['scores']['totalScore']==0)?5:WSTScore($rs['scores']['totalScore'],$rs['scores']['totalUsers'],5,0,3);
			WSTUnset($rs, 'totalUsers');
			//关注
			$f = model('common/Favorites');
            $sm = model('common/ShopMembers');
			$rs['favShop'] = $sm->checkFavorite($rs['shopId'],$uId);
			$rs['favGood'] = $f->checkFavorite($goodsId,$uId);


			// 获取一条商品评价
            $rs['goodsAppr'] = model('common/GoodsAppraises')->getGoodsFirstAppraise($goodsId);
            // 评价数
            $rs['appraises'] = model('common/GoodsAppraises')->getGoodsEachApprNum($goodsId);
		}
		return $rs;
	}


	/**
     * 下单
     */
	public function addCart($uId=0){
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		if($userId==0)return WSTReturn(lang('integral_no_login'));
		$id = (int)input('post.id');
		$cartNum = (int)input('post.buyNum',1);
		$cartNum = ($cartNum>0)?$cartNum:1;
		$goodsSpecId = 0;
		//验证传过来的商品是否合法
		$chk = $this->checkGoodsSaleSpec($id);
		if($chk['status']==-1)return $chk;
		//检测库存是否足够
		if($chk['data']['stock']<$cartNum)return WSTReturn(lang('integral_stock_less_tips'), -1);
		$user = model('common/users')->getFieldsById($userId,["userScore","userId"]);
		$goods = Db::name('integral_goods')->where(["id"=>$id])->field(["integralNum","totalNum","goodsPrice"])->find();
		if($user['userScore']<($goods["integralNum"]*$cartNum)){
			return WSTReturn(lang('integral_score_less'), -1);
		}
        $carts = [];
        $carts['id'] = $id;
        $carts['cartNum'] = $cartNum;
        session('INTEGRAL_CARTS',$carts);
        return WSTReturn(lang('integral_operation_success'), 1);
	}
	/**
	 * 验证商品是否合法
	 */
	public function checkGoodsSaleSpec($id){
		$goods = Db::name('integral_goods')->alias('gu')->join('__GOODS__ g','gu.goodsId=g.goodsId','inner')
		              ->where(['g.goodsStatus'=>1,'g.dataFlag'=>1,'gu.dataFlag'=>1,'gu.id'=>$id,'gu.integralStatus'=>1])
		              ->field('g.goodsId,isSpec,goodsType,gu.integralNum,gu.totalNum,gu.orderNum,gu.startTime,gu.endTime')
		              ->find();
		if(empty($goods))return WSTReturn(lang('integral_goods_invalid_fail_tips'), -1);
		//判断积分商城是否过期
		$time = time();
		if(WSTStrToTime($goods['startTime']) > $time)return WSTReturn(lang('integral_not_start_tips'));
		if(WSTStrToTime($goods['endTime']) < $time)return WSTReturn(lang('integral_over_time_tips'));
		$goodsId = $goods['goodsId'];
		$goodsStock = (int)$goods['totalNum']-(int)$goods['orderNum'];
		//有规格的话查询规格是否正确
		if($goods['isSpec']==1){
			$specs = Db::name('goods_specs')->where(['goodsId'=>$goodsId,'dataFlag'=>1])->field('id,isDefault')->select();
			if(count($specs)==0){
				return WSTReturn(lang('integral_goods_invalid_fail_tips'), -1);
			}
			$goodsSpecId = 0;
			foreach ($specs as $key => $v){
				if($v['isDefault']==1){
					$goodsSpecId = $v['id'];
					$isFindSpecId = true;
				}
			}

			if($goodsSpecId==0)return WSTReturn(lang('integral_goods_invalid_fail_tips'), -1);//有规格却找不到规格的话就报错
			return WSTReturn("", 1,['goodsId'=>$goods['goodsId'],'goodsSpecId'=>$goodsSpecId,'stock'=>$goodsStock,'goodsType'=>$goods['goodsType']]);
		}else{
			return WSTReturn("", 1,['goodsId'=>$goods['goodsId'],'goodsSpecId'=>0,'stock'=>$goodsStock,'goodsType'=>$goods['goodsType']]);
		}
	}

	/**
	 * 计算订单金额
	 */
	public function getCartMoney($uId=0){
		$data = ['shops'=>[],'totalMoney'=>0,'totalGoodsMoney'=>0];
        $areaId = input('post.areaId2/d',-1);
		//计算各店铺运费及金额
		$deliverType = (int)input('deliverType');
		$carts = $this->getCarts();
		$deliverType = ($carts['goodsType']==1)?1:$deliverType;
		$shopFreight = 0;
		//判断是否包邮
		if($carts['carts']['isFreeShipping']){
			$shopFreight = 0;
		}else{
			if($areaId>0){
				$shopFreight = ($deliverType==1)?0:WSTOrderFreight($carts['carts']['shopId'],$areaId,$carts['carts']);
			}else{
				$shopFreight = 0;
			}

		}

		$data['shops']['freight'] = $shopFreight;
		$data['shops']['shopId'] = $carts['carts']['shopId'];
		$data['shops']['goodsMoney'] = $carts['carts']['goodsMoney'];
		$data['totalGoodsMoney'] = $carts['carts']['goodsMoney'];
		$data['totalMoney'] += $carts['carts']['goodsMoney'] + $shopFreight;

		$data['realTotalMoney'] = $data['totalMoney'];
		return WSTReturn('',1,$data);
	}

	/**
	 * 获取session中购物车列表
	 */
	public function getCarts(){
		$userId = (int)session('WST_USER.userId');
		$tmp_carts = session('INTEGRAL_CARTS');
		$where = [];
		$where['ig.id'] = $tmp_carts['id'];
		$where['ig.dataFlag'] = 1;
		$where['ig.integralStatus'] = 1;
		$where['g.goodsStatus'] = 1;
		$where['g.dataFlag'] = 1;
		$rs = Db::name('integral_goods')
                ->alias('ig')
                ->join('__INTEGRAL_GOODS_LANGS__ igl','igl.integralId=ig.id and igl.langId='.WSTCurrLang())
                ->join('__GOODS__ g','ig.goodsId=g.goodsId','inner')
                ->join('__SHOPS__ s','s.shopId=ig.shopId','left')
                ->join('__GOODS_SPECS__ gs','g.goodsId=gs.goodsId and gs.isDefault','left')
                ->where($where)
                ->field('s.userId,s.shopId,s.shopName,g.goodsId,s.shopQQ,shopWangWang,igl.goodsName,ig.goodsPrice shopPrice,ig.integralNum,ig.totalNum goodsStock,ig.orderNum,ig.goodsImg,g.goodsCatId,g.goodsType,gs.specIds,gs.id goodsSpecId,ig.startTime,ig.endTime,g.isFreeShipping,s.isInvoice,
                g.isSpec,g.shippingFeeType,g.shopExpressId,g.goodsWeight,g.goodsVolume,gs.specWeight,gs.specVolume')
                ->find();
		if(empty($rs))return ['carts'=>[],'goodsTotalMoney'=>0,'goodsTotalNum'=>0];
		// 确保goodsSpecId不为null.
		$rs['goodsSpecId'] = (int)$rs['goodsSpecId'];
		$rs['cartNum'] = $tmp_carts['cartNum'];
		$carts = [];

		$list = [];
		$item = [];
		$item["shippingFeeType"] = $rs["shippingFeeType"];
		$item["shopExpressId"] = $rs["shopExpressId"];
		$item["goodsWeight"] = $rs["goodsWeight"];
		$item["goodsVolume"] = $rs["goodsVolume"];
		if($rs["isSpec"]==1){
			$item["goodsWeight"] = $rs["specWeight"];
			$item["goodsVolume"] = $rs["specVolume"];
		}
		$item["cartNum"] = $rs["cartNum"];
		$list[] = $item;

		$goodsTotalNum = 0;
		$goodsTotalMoney = 0;
		$totalIntegralNum = 0;
		if(!isset($carts['goodsMoney']))$carts['goodsMoney'] = 0;
		$carts['isFreeShipping'] = ($rs['isFreeShipping']==1)?true:false;
		$carts['id'] = $tmp_carts['id'];
		$carts['shopId'] = $rs['shopId'];
		$carts['shopName'] = $rs['shopName'];
		$carts['shopQQ'] = $rs['shopQQ'];
		$carts['userId'] = $rs['userId'];
		$carts['shopWangWang'] = $rs['shopWangWang'];
		$carts['isInvoice'] = $rs['isInvoice'];
		//判断能否购买，预设allowBuy值为10，为将来的各种情况预留10个情况值，从0到9
		$rs['allowBuy'] = 10;
		if($rs['goodsStock']<0){
			$rs['allowBuy'] = 0;//库存不足
		}else if($rs['goodsStock']<$tmp_carts['cartNum']){
			$rs['allowBuy'] = 1;//库存比购买数小
		}
		$carts['goodsMoney'] = $carts['goodsMoney'] + $rs['shopPrice'] * $rs['cartNum'];
		$goodsTotalMoney = $goodsTotalMoney + $rs['shopPrice'] * $rs['cartNum'];
		$totalIntegralNum = $totalIntegralNum + $rs['integralNum'] * $rs['cartNum'];
		$goodsTotalNum = $rs['cartNum'];
		if($rs['specIds']!=''){
			//加载规格值
			$specs = DB::name('spec_items')->alias('s')->join('__SPEC_CATS__ sc','s.catId=sc.catId','left')
                        ->join('__SPEC_CATS_LANGS__ scl','gc.catId=scl.catId and scl.langId='.WSTCurrLang())
                        ->join('__SPEC_ITEMS_LANGS__ sil','sit.itemId=sil.itemId and sil.langId='.WSTCurrLang())
                        ->where(['s.goodsId'=>$rs['goodsId'],'s.dataFlag'=>1])
                        ->field('scl.catName,s.itemId,sil.itemName')
                        ->select();
		    if(count($specs)>0){
			    $specMap = [];
			    foreach ($specs as $key =>$v){
			    	$specMap[$v['itemId']] = $v;
			    }
				$strName = [];
				if($rs['specIds']!=''){
				    $str = explode(':',$rs['specIds']);
				    foreach ($str as $vv){
				    	if(isset($specMap[$vv]))$strName[] = $specMap[$vv];
				    }
				}
				$rs['specNames'] = $strName;
			}
		}

		unset($rs['shopName']);
		$carts['goods'] = $rs;
		$carts['list'] = $list;
		return ['carts'=>$carts,'goodsType'=>$rs['goodsType'],'goodsTotalMoney'=>$goodsTotalMoney,'totalIntegralNum'=>$totalIntegralNum,'goodsTotalNum'=>$goodsTotalNum];
	}



	/**
	 * 虚拟商品下单
	 */
	public function submitByVirtual($carts,$orderSrc = 0,$uId){
        $addressId = 0;
		$isInvoice = ((int)input('post.isInvoice')!=0)?1:0;
		$invoiceClient = ($isInvoice==1)?input('post.invoiceClient'):'';
		$payType = 1;
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		if($userId==0)return WSTReturn(lang('integral_no_login'));
		//计算积分
		$totalIntegralNum = $carts["totalIntegralNum"];
		$carts = $carts['carts'];
		$user = Db::name('users')->where(["userId"=>$userId])->field("userId,userScore")->find();
		if($user["userScore"]<$totalIntegralNum){
			return WSTReturn(lang('integral_score_less_tips'));
		}
		//生成订单
		Db::startTrans();
		try{
			$goods = $carts['goods'];

			//给用户分配卡券
			$cards = model('common/GoodsVirtuals')->where(['goodsId'=>$goods['goodsId'],'dataFlag'=>1,'shopId'=>$goods['shopId'],'isUse'=>0])->lock(true)->limit($goods['cartNum'])->select();
			if(count($cards)<$goods['cartNum'])return WSTReturn(lang('integral_order_fail_stock_less_tips'));
			//修改库存
			Db::name('goods')->where('goodsId',$goods['goodsId'])->setDec('goodsStock',$goods['cartNum']);
			Db::name('goods')->where('goodsId',$goods['goodsId'])->setInc('saleNum',1);
			$orderunique = WSTOrderQnique();

			$orderNo = WSTOrderNo();
			$orderScore = 0;
			//创建订单
			$order = [];
			$order['orderNo'] = $orderNo;
			$order['userId'] = $userId;
			$order['orderType'] = 1;
			$order['areaId'] = 0;
			$order['userName'] = '';
			$order['userAddress'] = '';
			$order['shopId'] = $carts['shopId'];
			$order['payType'] = $payType;
			$order['goodsMoney'] = $carts['goodsMoney'];
			//计算运费和总金额
			$order['deliverType'] = 1;
			$order['deliverMoney'] = 0;
			$order['totalMoney'] = $order['goodsMoney'];
            //积分支付-计算分配积分和金额
            $order['scoreMoney'] = 0;
			$order['useScore'] = $totalIntegralNum;

			//实付金额要减去积分兑换的金额
			$order['realTotalMoney'] = $order['totalMoney'] - $order['scoreMoney'];
			$order['needPay'] = $order['realTotalMoney'];
			$order['orderCode'] = 'integral';
			$order['orderCodeTargetId'] = $carts['id'];
			$order['extraJson'] = json_encode(['id'=>$carts['id']]);
            if($order['needPay']>0){
                $order['orderStatus'] = -2;//待付款
				$order['isPay'] = 0;
            }else{
                $order['orderStatus'] = 0;//待发货
				$order['isPay'] = 1;
				$order['payTime'] = date('Y-m-d H:i:s');
            }
			//积分
			$orderScore = 0;
			$order['orderScore'] = $orderScore;

			$shop = model("common/shops")->getFieldsById($carts['shopId'],"isInvoice");
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

			$order['orderRemarks'] = input('post.remark_'.$carts['shopId']);
			$order['orderunique'] = $orderunique;
			$order['orderSrc'] = $orderSrc;
			$order['dataFlag'] = 1;
			$order['payRand'] = 1;
			$order['createTime'] = date('Y-m-d H:i:s');
			$m = model('common/orders');
			$result = $m->data($order,true)->isUpdate(false)->allowField(true)->save($order);
			if(false !== $result){
				$orderId = $m->orderId;
				//标记虚拟卡券为占用状态
				$goodsCards = [];
			    foreach ($cards as $key => $card) {
				    $card->isUse = 1;
				    $card->orderId = $orderId;
				    $card->orderNo = $orderNo;
				    $card->save();
				    $goodsCards[] = ['cardId'=>$card->id];
			    }
				$goods = $carts['goods'];
				//创建订单商品记录
				$orderGgoods = [];
				$orderGoods['orderId'] = $orderId;
				$orderGoods['goodsType'] = 1;
				$orderGoods['goodsId'] = $goods['goodsId'];
				$orderGoods['goodsNum'] = $goods['cartNum'];
				$orderGoods['goodsPrice'] = $goods['shopPrice'];
				$orderGoods['goodsSpecId'] = $goods['goodsSpecId'];
				if(!empty($goods['specNames'])){
					$specNams = [];
					foreach ($goods['specNames'] as $pkey =>$spec){
						$specNams[] = $spec['catName'].'：'.$spec['itemName'];
					}
					$orderGoods['goodsSpecNames'] = implode('@@_@@',$specNams);
				}else{
					$orderGoods['goodsSpecNames'] = '';
				}
				$orderGoods['goodsName'] = $goods['goodsName'];
				$orderGoods['goodsImg'] = $goods['goodsImg'];
				$orderGoods['commissionRate'] = WSTGoodsCommissionRate($goods['goodsCatId']);
				$orderGoods['extraJson'] = json_encode($goodsCards);
				//计算订单佣金
				$commissionFee = 0;
				if((float)$orderGoods['commissionRate']>0){
					$orderGoodscommission = round($orderGoods['goodsPrice']*1*$orderGoods['commissionRate']/100,2);
					$orderGoods["orderGoodscommission"] = $orderGoodscommission;
               		$commissionFee += $orderGoodscommission;
				}
				// 积分可抵扣金额
				$orderGoods['useScoreVal'] = $order['useScore'];
				Db::name('order_goods')->insert($orderGoods);

				model('common/orders')->where('orderId',$orderId)->update(['commissionFee'=>$commissionFee]);
				//修改积分商城数量
				Db::name('integral_goods')->where('id',$carts['id'])->setInc('orderNum',$goods['cartNum']);
				//创建积分流水--如果有抵扣积分就肯定是开启了支付支付
				if($order['useScore']>0){
					$score = [];
				    $score['userId'] = $userId;
					$score['score'] = $order['useScore'];
					$score['dataSrc'] = 1;
					$score['dataId'] = $orderId;
					$score['dataRemarks'] = json_encode(['type'=>'lang_params','key'=>'integral_order_trans_use_score_tips','params'=>[$orderNo,$order['useScore']]]);
					$score['scoreType'] = 0;
					model('common/UserScores')->add($score);
				}

				//建立订单记录
				$logOrder = [];
				$logOrder['orderId'] = $orderId;
				$logOrder['orderStatus'] = -2;
				$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>'order_success_wait_pay']);
				$logOrder['logUserId'] = $userId;
				$logOrder['logType'] = 0;
				$logOrder['logTime'] = date('Y-m-d H:i:s');
				Db::name('log_orders')->insert($logOrder);
				if($payType==1 && $order['needPay']==0){
					$logOrder = [];
					$logOrder['orderId'] = $orderId;
					$logOrder['orderStatus'] = 0;
					$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>'order_success_has_pay']);
					$logOrder['logUserId'] = $userId;
					$logOrder['logType'] = 0;
					$logOrder['logTime'] = date('Y-m-d H:i:s');
					Db::name('log_orders')->insert($logOrder);
				}
				//发送商家消息
				$tpl = WSTMsgTemplates('ORDER_SUBMIT');
		        if($tpl['tplContent']!='' && $tpl['status']=='1'){
		            $find = ['${ORDER_NO}'];
		            $replace = [$orderNo];

		        	$msg = array();
		            $msg["shopId"] = $carts['shopId'];
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
					$msg["shopId"] = $carts['shopId'];
		            $msg["tplCode"] = $tplCode;
		            $msg["msgType"] = 2;
		            $msg["paramJson"] = ['CODE'=>$tplCode,'method'=>'submitByVirtual','params'=>$params];
		            $msg["msgJson"] = "";
		            model("common/MessageQueues")->add($msg);
				}
		        //微信消息
		        if((int)WSTConf('CONF.wxenabled')==1){
		            $params = [];
		            $params['ORDER_NO'] = $orderNo;
	                $params['ORDER_TIME'] = date('Y-m-d H:i:s');
	                $goodsNames = $goods['goodsName']."*".$goods['cartNum'];
		            $params['GOODS'] = $goodsNames;
		            $params['MONEY'] = $order['realTotalMoney'] + $order['scoreMoney'];
		            $params['ADDRESS'] = $order['userAddress']." ".$order['userName'];
		            $params['PAY_TYPE'] = WSTLangPayType($order['payType']);

			        $msg = array();
					$tplCode = "WX_ORDER_SUBMIT";
					$msg["shopId"] = $carts['shopId'];
		            $msg["tplCode"] = $tplCode;
		            $msg["msgType"] = 4;
		            $msg["paramJson"] = ['CODE'=>$tplCode,'URL'=>Url('wechat/orders/sellerorder','',true,true),'params'=>$params];
		            $msg["msgJson"] = "";
		            model("common/MessageQueues")->add($msg);

			    }
				//已付款的虚拟商品
				if($order['needPay']==0){
					model('common/orders')->handleVirtualGoods($orderId);
				}
			}
			Db::commit();
			//删除session的购物车商品
			session('INTEGRAL_CARTS',null);
			return WSTReturn(lang('integral_submit_success'), 1,$orderunique);
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('integral_submit_fail'),-1);
        }
	}
	/**
	 * 实物商品下单
	 */
	public function submitByEntity($carts,$orderSrc = 0,$uId=0){
		$addressId = (int)input('post.s_addressId');
		$deliverType = ((int)input('post.deliverType')!=0)?1:0;
		$isInvoice = ((int)input('post.isInvoice')!=0)?1:0;
		$invoiceClient = ($isInvoice==1)?input('post.invoiceClient'):'';
		$payType = ((int)input('post.payType')!=0)?1:0;
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		if($userId==0)return WSTReturn(lang('integral_no_login'));
		$isUseScore = (int)input('isUseScore');
		$useScore = (int)input('useScore');

		//检测地址是否有效
		$address = Db::name('user_address')->where(['userId'=>$userId,'addressId'=>$addressId,'dataFlag'=>1])->find();
		if(empty($address)){
			return WSTReturn(lang('integral_invalid_user_address'));
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

		$totalIntegralNum = $carts["totalIntegralNum"];
		$carts = $carts['carts'];
		//计算积分
		$user = Db::name('users')->where(["userId"=>$userId])->field("userId,userScore")->find();
		if($user["userScore"]<$totalIntegralNum){
			return WSTReturn(lang('integral_score_less_tips'));
		}
		//生成订单
		Db::startTrans();
		try{
			$orderunique = WSTOrderQnique();
			$orderNo = WSTOrderNo();
			$orderScore = 0;
			//创建订单
			$order = [];
			$order = array_merge($order,$address);
			$order['orderNo'] = $orderNo;
			$order['userId'] = $userId;
			$order['shopId'] = $carts['shopId'];
			$order['payType'] = $payType;
			$order['goodsMoney'] = $carts['goodsMoney'];
			//计算运费和总金额
			$order['deliverType'] = $deliverType;
			if($carts['isFreeShipping']){
                $order['deliverMoney'] = 0;
			}else{
			    $order['deliverMoney'] = ($deliverType==1)?0:WSTOrderFreight($carts['shopId'],$order['areaId2'],$carts);
			}
			if($deliverType==1){//自提
				$order['storeId'] = (int)input("storeId");
				$order['storeType'] = 1;
			}
			$order['totalMoney'] = $order['goodsMoney']+$order['deliverMoney'];
            //积分支付-计算分配积分和金额
            $order['scoreMoney'] = 0;
			$order['useScore'] = $totalIntegralNum;

			//实付金额要减去积分兑换的金额
			$order['realTotalMoney'] = $order['totalMoney'] - $order['scoreMoney'];
			$order['needPay'] = $order['realTotalMoney'];
			$order['orderCode'] = 'integral';
			$order['orderCodeTargetId'] = $carts['id'];
			$order['extraJson'] = json_encode(['id'=>$carts['id']]);
            if($payType==1){
                if($order['needPay']>0){
                    $order['orderStatus'] = -2;//待付款
				    $order['isPay'] = 0;
                }else{
                    $order['orderStatus'] = 0;//待发货
				    $order['isPay'] = 1;
				    $order['payTime'] = date('Y-m-d H:i:s');
				    if($deliverType==1)$order['verificationCode'] = WSTOrderVerificationCode($carts['shopId']);
                }
			}else{
				$order['orderStatus'] = 0;//待发货
				if($order['needPay']==0){
					$order['isPay'] = 1;
					$order['payTime'] = date('Y-m-d H:i:s');
				}
				if($deliverType==1)$order['verificationCode'] = WSTOrderVerificationCode($carts['shopId']);
			}
			//积分
			$orderScore = 0;

			$order['orderScore'] = $orderScore;

			$shop = model("common/shops")->getFieldsById($carts['shopId'],"isInvoice");
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

			$order['orderRemarks'] = input('post.remark_'.$carts['shopId']);
			$order['orderunique'] = $orderunique;
			$order['orderSrc'] = $orderSrc;
			$order['dataFlag'] = 1;
			$order['payRand'] = 1;
			$order['createTime'] = date('Y-m-d H:i:s');
			$m = model('common/orders');
			$result = $m->data($order,true)->isUpdate(false)->allowField(true)->save($order);
			if(false !== $result){
				$orderId = $m->orderId;
				$goods = $carts['goods'];
				//创建订单商品记录
				$orderGgoods = [];
				$orderGoods['orderId'] = $orderId;
				$orderGoods['goodsId'] = $goods['goodsId'];
				$orderGoods['goodsNum'] = $goods['cartNum'];
				$orderGoods['goodsPrice'] = $goods['shopPrice'];
				$orderGoods['goodsSpecId'] = $goods['goodsSpecId'];
				if(!empty($goods['specNames'])){
					$specNams = [];
					foreach ($goods['specNames'] as $pkey =>$spec){
						$specNams[] = $spec['catName'].'：'.$spec['itemName'];
					}
					$orderGoods['goodsSpecNames'] = implode('@@_@@',$specNams);
				}else{
					$orderGoods['goodsSpecNames'] = '';
				}
				$orderGoods['goodsName'] = $goods['goodsName'];
				$orderGoods['goodsImg'] = $goods['goodsImg'];
				$orderGoods['commissionRate'] = WSTGoodsCommissionRate($goods['goodsCatId']);
				//计算订单佣金
				$commissionFee = 0;
				if((float)$orderGoods['commissionRate']>0){
					$orderGoodscommission = round($orderGoods['goodsPrice']*$orderGoods['goodsNum']*$orderGoods['commissionRate']/100,2);
					$orderGoods["orderGoodscommission"] = $orderGoodscommission;
                 	$commissionFee += $orderGoodscommission;
				}
				// 积分可抵扣金额
				$orderGoods['useScoreVal'] = $order['useScore'];


				Db::name('order_goods')->insert($orderGoods);

				model('common/orders')->where('orderId',$orderId)->update(['commissionFee'=>$commissionFee]);

				//修改积分商城数量
				Db::name('integral_goods')->where('id',$carts['id'])->setInc('orderNum',$goods['cartNum']);
				//创建积分流水--如果有抵扣积分就肯定是开启了支付支付
				if($order['useScore']>0){
					$score = [];
				    $score['userId'] = $userId;
					$score['score'] = $order['useScore'];
					$score['dataSrc'] = 1;
					$score['dataId'] = $orderId;
					$score['dataRemarks'] = json_encode(['type'=>'lang_params','key'=>'integral_order_trans_use_score_tips','params'=>[$orderNo,$order['useScore']]]);
					$score['scoreType'] = 0;
					model('common/UserScores')->add($score);
				}

				//建立订单记录
				$logOrder = [];
				$logOrder['orderId'] = $orderId;
				$logOrder['orderStatus'] = ($payType==1 && $order['needPay']==0)?-2:$order['orderStatus'];
				$logOrder['logJson'] = ($payType==1)?json_encode(['type'=>'lang','key'=>'order_success_wait_pay']):json_encode(['type'=>'lang','key'=>'order_success']);
				$logOrder['logUserId'] = $userId;
				$logOrder['logType'] = 0;
				$logOrder['logTime'] = date('Y-m-d H:i:s');
				Db::name('log_orders')->insert($logOrder);
				if($payType==1 && $order['needPay']==0){
					$logOrder = [];
					$logOrder['orderId'] = $orderId;
					$logOrder['orderStatus'] = 0;
					$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>'order_success_has_pay']);
					$logOrder['logUserId'] = $userId;
					$logOrder['logType'] = 0;
					$logOrder['logTime'] = date('Y-m-d H:i:s');
					Db::name('log_orders')->insert($logOrder);
				}
				if($deliverType==1){//自提
					//自提订单（已支付）发送核验码
					if(($payType==1 && $order['needPay']==0) || $payType==0){
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
				            model("common/LogSms")->sendSMS(0,$areaCode.$userPhone,$params,'submitByEntity','',$userId,0);
				        }
					}
			    }
				//给店铺增加提示消息
				$tpl = WSTMsgTemplates('ORDER_SUBMIT');
		        if($tpl['tplContent']!='' && $tpl['status']=='1'){
		            $find = ['${ORDER_NO}'];
		            $replace = [$orderNo];

		        	$msg = array();
		            $msg["shopId"] = $carts['shopId'];
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
					$msg["shopId"] = $carts['shopId'];
		            $msg["tplCode"] = $tplCode;
		            $msg["msgType"] = 2;
		            $msg["paramJson"] = ['CODE'=>$tplCode,'method'=>'submit','params'=>$params];
		            $msg["msgJson"] = "";
		            model("common/MessageQueues")->add($msg);
				}
		        //微信消息
		        if((int)WSTConf('CONF.wxenabled')==1){
		            $params = [];
		            $params['ORDER_NO'] = $orderNo;
	                $params['ORDER_TIME'] = date('Y-m-d H:i:s');
	                $goodsNames = $goods['goodsName']."*".$goods['cartNum'];
		            $params['GOODS'] = $goodsNames;
		            $params['MONEY'] = $order['realTotalMoney'] + $order['scoreMoney'];
		            $params['ADDRESS'] = $order['userAddress']." ".$order['userName'];
		            $params['PAY_TYPE'] = WSTLangPayType($order['payType']);

			        $msg = array();
					$tplCode = "WX_ORDER_SUBMIT";
					$msg["shopId"] = $carts['shopId'];
		            $msg["tplCode"] = $tplCode;
		            $msg["msgType"] = 4;
		            $msg["paramJson"] = ['CODE'=>$tplCode,'URL'=>Url('wechat/orders/sellerorder','',true,true),'params'=>$params];
		            $msg["msgJson"] = "";
		            model("common/MessageQueues")->add($msg);

			    }
			}
			Db::commit();
			//删除session的购物车商品
			session('INTEGRAL_CARTS',null);
			return WSTReturn(lang('integral_submit_success'), 1,$orderunique);
		}catch (\Exception $e) {
			//print_r($e);
            Db::rollback();
            return WSTReturn(lang('integral_submit_fail'),-1);
        }
	}

    /**
	 * 下单
	 */
	public function submit($orderSrc = 0,$uId=0){
		//检测购物车
		$carts = $this->getCarts();
		if(empty($carts['carts']))return WSTReturn(lang('integral_select_want_buy_goods'));
		//检测时间过了没有
		$time = time();
		if(WSTStrToTime($carts['carts']['goods']['startTime']) > $time)return WSTReturn(lang('integral_not_start_tips'));
		if(WSTStrToTime($carts['carts']['goods']['endTime']) < $time)return WSTReturn(lang('integral_over_time_tips'));
		$checkNum = $carts['carts']['goods']['goodsStock']-$carts['carts']['goods']['orderNum'];
		if($checkNum<$carts['goodsTotalNum'])return WSTReturn(lang('integral_stock_less_number_tips',[$checkNum]));
		if($carts['goodsType']==1){
            return $this->submitByVirtual($carts,$orderSrc,$uId);
		}else{
            return $this->submitByEntity($carts,$orderSrc,$uId);
		}
	}


	/**
	 * 管理员查看积分商城列表
	 */
	public function pageQueryByAdmin(){
		$goodsName = input('goodsName');
		$shopName = input('shopName');
		$areaIdPath = input('areaIdPath');
		$goodsCatIdPath = input('goodsCatIdPath');
		$where = [];
		$where[] = ['ig.dataFlag','=',1];
		if($goodsName !='')$where[] = ['igl.goodsName|g.goodsSn','like','%'.$goodsName.'%'];
		if($shopName !='')$where[] = ['s.shopName|s.shopSn','like','%'.$shopName.'%'];
		if($areaIdPath !='')$where[] = ['s.areaIdPath','like',$areaIdPath."%"];
		if($goodsCatIdPath !='')$where[] = ['g.goodsCatIdPath','like',$goodsCatIdPath."%"];
        $page =  Db::name('integral_goods')
                ->alias('ig')
                ->join('__INTEGRAL_GOODS_LANGS__ igl','igl.integralId=ig.id and igl.langId='.WSTCurrLang())
                ->join('__GOODS__ g','g.goodsId=ig.goodsId and g.dataFlag=1','inner')
                ->join('__SHOPS__ s','s.shopId=ig.shopId','left')
                ->where($where)->order('ig.createTime desc')->field('g.goodsSn,ig.*,s.shopId,s.shopName,igl.goodsName')
                ->order('ig.updateTime desc')
                ->paginate(input('limit/d'))->toArray();
        if(count($page['data'])>0){
        	$time = time();
        	foreach($page['data'] as $key =>$v){
        		$page['data'][$key]['goodsImg'] = WSTImg($v['goodsImg']);
        		$page['data'][$key]['verfiycode'] = WSTShopEncrypt($v['shopId']);
        		if(WSTStrToTime($v['startTime'])<=$time && WSTStrToTime($v['endTime'])>=$time){
        			$page['data'][$key]['status'] = 1;
        		}else if(WSTStrToTime($v['startTime'])>$time){
                    $page['data'][$key]['status'] = 0;
        		}else{
        			$page['data'][$key]['status'] = -1;
        		}
        	}
        }
        return $page;
	}

	/**
	 * 商品上下架
	 */
	public function changeSale(){
		$id = (int)input('id');
		$type = (int)input('type');
		$integralStatus = ($type==1)?1:0;
		$where = [];
		$where['id'] = $id;
        $rs = Db::name('integral_goods')->where($where)->update(['integralStatus'=>$integralStatus]);
        if($integralStatus==1){
        	return WSTReturn(lang('integral_onsale_success'),1);
        }else{
        	return WSTReturn(lang('integral_unsale_success'),1);
        }

	}

    /**
	 * 删除商品
	 */
	public function delByAdmin(){
		$id = (int)input('id');
		$where = [];
		$where['id'] = $id;
        $rs = Db::name('integral_goods')->where($where)->update(['dataFlag'=>-1]);
        //WSTUnuseResource('integral_goods','goodsImg',$id);
        return WSTReturn(lang('integral_operation_success'),1);
	}


	/**
	 * 获取指定父级的商家店铺分类
	 */
	public function getShopCats($parentId){
		$shop = $this->getSelfShop();
		$shopId = (int)$shop["shopId"];
		$rs = Db::name('shop_cats')
            ->alias('a')
            ->join('__SHOP_CATS_LANGS__ scl','scl.catId=a.catId and langId='.WSTCurrLang())
            ->where(['dataFlag'=>1, 'isShow' => 1,'parentId'=>$parentId,'shopId'=>$shopId])
		    ->field("scl.catName,a.catId")->order('catSort asc')->select();
		return $rs;
	}


	/*
    * 商品详情分享海报
    */
    public function createPoster($userId,$qr_code,$outImg){

        $id = input("id");
        $goods = Db::name("goods g")
            ->join("integral_goods ig","g.goodsId=ig.goodsId","inner")
            ->join('__INTEGRAL_GOODS_LANGS__ igl','igl.integralId=ig.id and igl.langId='.WSTCurrLang())
            ->where(['ig.id'=>$id])->field("g.goodsId,igl.goodsName,ig.goodsImg,ig.goodsPrice,ig.integralNum,g.goodsUnit")->find();

        //生成二维码图片
        $share_bg = WSTConf('CONF.resourceDomain').'/'.WSTConf("CONF.goodsPosterBg");
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
	        	$tmpImg = WSTRootPath().'/upload/shares/integral/'.date("Y-m").'/'.$userId.'.jpg';
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
        $text2 = mb_convert_encoding(lang('integral_conversion_price').'：', "html-entities", "utf-8"); //转成html编码
        imagettftext($share_bg, 18, 0, 80, $vh, $textcolor2, $font, $text2);

        $textcolor3 = imagecolorallocate($share_bg,255,0,54);
        $text = WSTImageAutoWrap(20, 0, $font, lang('currency_symbol').(float)$goods['goodsPrice']." + ".$goods['integralNum'].'积分',700);
        imagettftext($share_bg, 24, 0, 180, $vh, $textcolor3, $font, $text);

        $text = mb_convert_encoding(lang('integral_share_tip_words'), "html-entities", "utf-8"); //转成html编码
        imagettftext($share_bg, 24, 0, 126, 1255, $textcolor, $font, $text);
        //输出图片
        $shareImg = WSTRootPath().'/'.$outImg;
        imagepng($share_bg, $shareImg);
        imagedestroy($new_qrcode);
    	imagedestroy($share_bg);

        return WSTReturn("",1,["shareImg"=>$outImg]);
    }

    public function checkSupportStores(){
      $id = (int)session('INTEGRAL_CARTS.id');
      $rs = Db::name("integral_goods")->where(['id'=>$id])->field("shopId")->find();
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
