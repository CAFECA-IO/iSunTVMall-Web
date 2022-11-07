<?php
namespace addons\wstim\model;
use think\addons\BaseModel as Base;
use think\Db;
class Chats extends Base{
	/**
	 * 安装
	 */
	public function install(){
		Db::startTrans();
		try{
			$hooks = ["homeDocumentContact","afterUserLogin","homeDocumentListener","shopDocumentListener",
				      "mobileDocumentContact","mobileDocumentBottomNav",
				      "wechatDocumentContact","wechatDocumentBottomNav",
				      "adminAfterConfigAddon","pushNoticeToApp"
					 ];
			$this->bindHoods("Wstim", $hooks);
			//管理员后台
			$mIdArrs = [];
			// 顶部菜单
            $datas = [];
            $menuLangParams = [
                1=>['menuName'=>'客服'],
                2=>['menuName'=>'客服'],
                3=>['menuName'=>'Customer Service'],
            ];
            $menuId = Db::name('menus')->insertGetId(["parentId"=>0, "menuSort"=>99, "dataFlag"=>1, "isShow"=>1, "menuIcon"=>"comment", "menuMark"=>"wstim"]);
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['menuId'] = $menuId;
                $data['langId'] = $v['id'];
                $data['menuName'] = $menuLangParams[$v['id']]['menuName'];
                $datas[] = $data;
            }
            Db::name('menus_langs')->insertAll($datas);

			$parentId = $menuId;
            $privilegeLangParams = [
                1=>['privilegeName_01'=>'客服','privilegeName_02'=>'查看聊天管理','privilegeName_03'=>'查看聊天記錄','privilegeName_04'=>'聊天禁用關鍵字'],
                2=>['privilegeName_01'=>'客服','privilegeName_02'=>'查看聊天管理','privilegeName_03'=>'查看聊天记录','privilegeName_04'=>'聊天禁用关键字'],
                3=>['privilegeName_01'=>'Customer Service','privilegeName_02'=>'View chat management','privilegeName_03'=>'View chat history','privilegeName_04'=>'Disable keywords'],
            ];
			array_push($mIdArrs, ['code'=>'WSTIM_KF_00','val'=>$parentId,'url'=>'','oUrl'=>'','pcode'=>'01']);
			// 左侧一级菜单
            $datas = [];
            $menuLangParams = [
                1=>['menuName'=>'聊天管理'],
                2=>['menuName'=>'聊天管理'],
                3=>['menuName'=>'Chat management'],
            ];
            $menuId = Db::name('menus')->insertGetId(["parentId"=>$parentId, "menuSort"=>1, "dataFlag"=>1, "isShow"=>1, "menuMark"=>"wstim"]);
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['menuId'] = $menuId;
                $data['langId'] = $v['id'];
                $data['menuName'] = $menuLangParams[$v['id']]['menuName'];
                $datas[] = $data;
            }
            Db::name('menus_langs')->insertAll($datas);

			$_pid = $menuId;
            $parentId = $menuId;
			array_push($mIdArrs, ['code'=>'WSTIM_LTGL_00','val'=>$parentId,'url'=>'','oUrl'=>'','pcode'=>'02']);
			// 左侧二级菜单
            $datas = [];
            $menuLangParams = [
                1=>['menuName'=>'聊天記錄'],
                2=>['menuName'=>'聊天记录'],
                3=>['menuName'=>'chat management'],
            ];
            $menuId = Db::name('menus')->insertGetId(["parentId"=>$parentId,"menuSort"=>1, "dataFlag"=>1, "isShow"=>1, "menuMark"=>"wstim"]);
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['menuId'] = $menuId;
                $data['langId'] = $v['id'];
                $data['menuName'] = $menuLangParams[$v['id']]['menuName'];
                $datas[] = $data;
            }
            Db::name('menus_langs')->insertAll($datas);

			$parentId = $menuId;
			array_push($mIdArrs, ['code'=>'WSTIM_LTJL_00','val'=>$parentId,'url'=>'/addon/wstim-dialogs-adminDialogList','oUrl'=>'/addon/wstim-dialogs-pagequery,/addon/wstim-dialogcontents-adminIndex','pcode'=>'03']);
			if($menuId!==false){
				foreach($mIdArrs as $v){
				    $datas = [];
					$data = ["menuId"=>$v['val'],
								"privilegeCode"=>$v['code'],
								"isMenuPrivilege"=>1,
								"privilegeUrl"=>$v['url'],
								"otherPrivilegeUrl"=>$v['oUrl'],
								"dataFlag"=>1,
								"isEnable"=>1];
					$privilegeId = Db::name('privileges')->insertGetId($data);
                    foreach (WSTSysLangs() as $key => $vo) {
                        $data = [];
                        $data['privilegeId'] = $privilegeId;
                        $data['langId'] = $vo['id'];
                        $data['privilegeName'] = $privilegeLangParams[$vo['id']]['privilegeName_'.$v['pcode']];
                        $datas[] = $data;
                    }
                    Db::name('privileges_langs')->insertAll($datas);
				}
                $datas = [];
                $menuLangParams = [
                    1=>['menuName'=>'聊天禁用關鍵字'],
                    2=>['menuName'=>'聊天禁用关键字'],
                    3=>['menuName'=>'Disable keywords'],
                ];
                $menuId = Db::name('menus')->insertGetId(["parentId"=>$_pid,"menuSort"=>1, "dataFlag"=>1, "isShow"=>1, "menuMark"=>"wstim"]);
                foreach (WSTSysLangs() as $key => $v) {
                    $data = [];
                    $data['menuId'] = $menuId;
                    $data['langId'] = $v['id'];
                    $data['menuName'] = $menuLangParams[$v['id']]['menuName'];
                    $datas[] = $data;
                }
                Db::name('menus_langs')->insertAll($datas);
				//WSTIM_LTDS_00 => DS => disable_keywords
                $data = [
                    "menuId"=>$menuId,
                    "privilegeCode"=>"WSTIM_LTDS_00",
                    "isMenuPrivilege"=>1,
                    "privilegeUrl"=>'/addon/wstim-disableKeywords-adminIndex',
                    "otherPrivilegeUrl"=>'/addon/wstim-disableKeywords-commit,/addon/wstim-disableKeywords-getKeywords',
                    "dataFlag"=>1,
                    "isEnable"=>1
                ];
                $privilegeId = Db::name('privileges')->insertGetId($data);
                $datas = [];
                foreach (WSTSysLangs() as $key => $vo) {
                    $data = [];
                    $data['privilegeId'] = $privilegeId;
                    $data['langId'] = $vo['id'];
                    $data['privilegeName'] = $privilegeLangParams[$vo['id']]['privilegeName_04'];
                    $datas[] = $data;
                }
                Db::name('privileges_langs')->insertAll($datas);
			}
            $now = date("Y-m-d H:i:s");
            //商家中心
            $homeMenuLangParams = [
                1=>[
                    'menuName_01'=>'客服管理',
                    'menuName_02'=>'客服管理',
                    'menuName_03'=>'聊天記錄',
                    'menuName_04'=>'店鋪客服管理',
                    'menuName_05'=>'客服後台',
                    'menuName_06'=>'自動回復',
                    'menuName_07'=>'客服評分',
                    'menuName_08'=>'訪問統計',
                ],
                2=>[
                    'menuName_01'=>'客服管理',
                    'menuName_02'=>'客服管理',
                    'menuName_03'=>'聊天记录',
                    'menuName_04'=>'店铺客服管理',
                    'menuName_05'=>'客服后台',
                    'menuName_06'=>'自动回复',
                    'menuName_07'=>'客服评分',
                    'menuName_08'=>'访问统计',
                ],
                3=>[
                    'menuName_01'=>'Customer Service',
                    'menuName_02'=>'Customer Servic',
                    'menuName_03'=>'Chat Log',
                    'menuName_04'=>'Service Mgr',
                    'menuName_05'=>'Service Background',
                    'menuName_06'=>'Auto Reply',
                    'menuName_07'=>'Service Score',
                    'menuName_08'=>'Visits Report',
                ],
            ];
            $homeMenuIds = [];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>0,"menuUrl"=>"#","menuOtherUrl"=>"#","menuType"=>1,"isShow"=>1,"menuSort"=>10,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"wstim"]);
            $homeMenuIds[] = ['homeMenuId'=>$homeMenuId,'code'=>'01'];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>$homeMenuId,"menuUrl"=>"#","menuOtherUrl"=>"","menuType"=>1,"isShow"=>1,"menuSort"=>0,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"wstim"]);
            $parentId = $homeMenuId;
            $homeMenuIds[] = ['homeMenuId'=>$homeMenuId,'code'=>'02'];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>$parentId,"menuUrl"=>"/addon/wstim-shops-index","menuOtherUrl"=>"","menuType"=>1,"isShow"=>1,"menuSort"=>0,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"wstim"]);
            $homeMenuIds[] = ['homeMenuId'=>$homeMenuId,'code'=>'03'];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>$parentId,"menuUrl"=>"/addon/wstim-shopservices-index","menuOtherUrl"=>"","menuType"=>1,"isShow"=>1,"menuSort"=>0,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"wstim"]);
            $homeMenuIds[] = ['homeMenuId'=>$homeMenuId,'code'=>'04'];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>$parentId,"menuUrl"=>"/addon/wstim-shopchats-index","menuOtherUrl"=>"","menuType"=>1,"isShow"=>1,"menuSort"=>0,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"wstim"]);
            $homeMenuIds[] = ['homeMenuId'=>$homeMenuId,'code'=>'05'];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>$parentId,"menuUrl"=>"/addon/wstim-autoReplys-index","menuOtherUrl"=>"","menuType"=>1,"isShow"=>1,"menuSort"=>0,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"wstim"]);
            $homeMenuIds[] = ['homeMenuId'=>$homeMenuId,'code'=>'06'];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>$parentId,"menuUrl"=>"/addon/wstim-serviceevaluates-index","menuOtherUrl"=>"","menuType"=>1,"isShow"=>1,"menuSort"=>0,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"wstim"]);
            $homeMenuIds[] = ['homeMenuId'=>$homeMenuId,'code'=>'07'];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>$parentId,"menuUrl"=>"/addon/wstim-Imchatstatistics-index","menuOtherUrl"=>"/addon/wstim-Imchatstatistics-pagequery","menuType"=>1,"isShow"=>1,"menuSort"=>0,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"wstim"]);
            $homeMenuIds[] = ['homeMenuId'=>$homeMenuId,'code'=>'08'];
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
			installSql("wstim");//传入插件名
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return false;
	   	}
	}
	/**
	 * 卸载
	 */
	public function uninstall(){
		Db::startTrans();
		try{
			$hooks = ["homeDocumentContact","afterUserLogin","homeDocumentListener","shopDocumentListener",
				      "mobileDocumentContact","mobileDocumentBottomNav",
				      "wechatDocumentContact","wechatDocumentBottomNav",
				      "adminAfterConfigAddon","pushNoticeToApp"
					 ];
			$this->unbindHoods("Wstim", $hooks);
            $menuId = Db::name('menus')->where(["menuMark"=>"wstim"])->column('menuId');
            Db::name('menus')->where(["menuMark"=>"wstim"])->delete();
            Db::name('menus_langs')->where([['menuId','in',$menuId]])->delete();

            $privilegeIds = Db::name('privileges')->where("privilegeCode","like","WSTIM_%")->column('privilegeId');
            Db::name('privileges')->where("privilegeCode","like","WSTIM_%")->delete();
            Db::name('privileges_langs')->where([['privilegeId','in',$privilegeIds]])->delete();

            $homeMenuIds = Db::name('home_menus')->where(["menuMark"=>"wstim"])->column('menuId');
            Db::name('home_menus')->where(["menuMark"=>"wstim"])->delete();
            Db::name('home_menus_langs')->where([['menuId','in',$homeMenuIds]])->delete();

			uninstallSql("wstim");//传入插件名
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
			Db::name('menus')->where(["menuMark"=>"wstim"])->update(["isShow"=>$isShow]);
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return false;
	   	}
	}
	/**
	 * 【普通商品】获取商品图片
	 */
	public function getChatGoods($goodsId){
		$data = Db::name('goods')->where(['goodsId'=>$goodsId])->field('goodsImg, shopPrice')->find();
		$data['goodsImg'] = WSTImg($data['goodsImg']);
		return $data;
	}

	/**
	* 查询与店铺相关的订单列表
	*/
	public function getOrderInfo($userId, $shopId){
		$orderId = (int)input('orderId');
		$where = ['o.dataFlag'=>1,'o.orderId'=>$orderId];
		$whereOr = "o.userId={$userId} or o.shopId={$shopId}";
		$data = Db::name('orders')->alias('o')
								  ->where($where)
								  ->where($whereOr)
								  ->field('o.orderId,o.orderNo,o.orderStatus,o.goodsMoney,o.totalMoney,o.realTotalMoney,o.createTime')
								  ->find();
		if(!empty($data)){
			$data['orderStatus'] = WSTLangOrderStatus($data['orderStatus']);
			// 日期显示年月日
			$data['createTime'] = date('Y-m-d',strtotime($data['createTime']));
			$data['list'] = [];
			$data['list'] = Db::name('order_goods')->field("goodsId,goodsImg,goodsName")->where('orderId', $data['orderId'])->select();
		}
	    return $data;
	}


	/**
	* 获取商品信息
	*/
	public function getGoodsInfo(){
		$goodsId = (int)input('goodsId');
		$shopId = (int)input('shopId');
		return Db::name('goods')->alias('a')->join('__GOODS_LANGS__ gl','gl.goodsId=a.goodsId and gl.langId='.WSTcurrLang())->field('a.goodsId,goodsName,shopPrice,goodsImg')
								->where(['a.goodsId'=>$goodsId,'shopId'=>$shopId])
								->find();
	}
	/**
    * 获取用户信息
    */
    public function getUserInfo(){
    	$userId = (int)input('userId');
        $m = model('common/users');
        $rs = $m->getFieldsById($userId,['loginName','userName',
        								 'userPhone','areaCode','userPhoto',
        								 'userTotalScore','userQQ']);
        if($rs["userPhone"]!=''){
        	$rs["userPhone"] = "+".$rs['areaCode']." ****".substr($rs["userPhone"], -4);
        }

    	$rrs = Db::name('user_ranks')->alias("a")->join('__USER_RANKS_LANGS__ ur','ur.rankId=a.rankId and ur.langId='.WSTcurrLang())
									->where(['dataFlag'=>1])
    								 ->where('startScore','<=',$rs['userTotalScore'])
    								 ->where('endScore','>=',$rs['userTotalScore'])
    								 ->field('rankName')
    								 ->find();
		$rs['rankName'] = $rrs['rankName'];
	    return $rs;
    }
	/**
	* 查询与店铺相关的浏览记录
	*/
	public function getHistory($wap=0){
		$isApp = input('isApp');
		$isWeapp = input('isWeapp');
        if($isApp==1 || $isWeapp==1){
			$ids = input('goodsIds');
			$ids = trim($ids,',');
			$ids = explode(',',$ids);
		}else{
			$ids = ($wap==1)?cookie("wx_history_goods"):cookie("history_goods");
		}
		if(empty($ids))return [];
		$ids = array_unique($ids);
	    $where = [];
	    $where['isSale'] = 1;
	    $where['goodsStatus'] = 1;
	    $where['dataFlag'] = 1;
	    $where['shopId'] = (int)input('shopId');
	    $orderBy = "field('goodsId',".implode(',',$ids).")";
        $goods = Db::name('goods')->alias('a')
                   ->join('__GOODS_LANGS__ gl','gl.goodsId=a.goodsId and gl.langId='.WSTcurrLang())
                   ->where($where)
                   ->whereIn('a.goodsId',$ids)
                   ->field('a.goodsId,goodsName,goodsImg,shopPrice,isSpec')
                   ->orderRaw($orderBy)
                   ->select();
        $ids = [];
        foreach($goods as $key =>$v){
        	if($v['isSpec']==1)$ids[] = $v['goodsId'];
        }
        if(!empty($ids)){
        	$specs = [];
        	$rs = Db::name('goods_specs gs ')->where([['goodsId','in',$ids],['dataFlag','=','1']])->order('id asc')->select();
        	foreach ($rs as $key => $v){
        		$specs[$v['goodsId']] = $v;
        	}
        	foreach($goods as $key =>$v){
        		if(isset($specs[$v['goodsId']]))
        		$goods[$key]['specs'] = $specs[$v['goodsId']];
        	}
        }
        return $goods;
	}
	/**
	* 查询与店铺相关的订单列表
	*/
	public function getOrderList($userId){
		$shopId = (int)input('shopId');
		$where = ['o.userId'=>$userId,'o.dataFlag'=>1,'o.shopId'=>$shopId];
		$page = Db::name('orders')->alias('o')
								  ->join('__SHOPS__ s','o.shopId=s.shopId','left')
					              ->where($where)
					              ->field('o.orderId,o.orderNo,o.orderStatus,o.goodsMoney,o.totalMoney,o.realTotalMoney,o.createTime')
						          ->order('o.createTime', 'desc')
						          ->paginate(input('pagesize/d'))->toArray();
	    if(count($page['data'])>0){
	    	 $orderIds = [];
	    	 foreach ($page['data'] as $k=>$v){
	    	 	 $orderIds[] = $v['orderId'];
	    	 	 $page['data'][$k]['orderStatus'] = WSTLangOrderStatus($v['orderStatus']);
	    	 }
	    	 $goods = Db::name('order_goods')->where('orderId','in',$orderIds)->select();
	    	 $goodsMap = [];
	    	 foreach ($goods as $v){
	    	 	 $v['goodsSpecNames'] = str_replace('@@_@@','、',$v['goodsSpecNames']);
	    	 	 $goodsMap[$v['orderId']][] = $v;
	    	 }
	    	 foreach ($page['data'] as $key => $v){
	    	 	 $page['data'][$key]['list'] = $goodsMap[$v['orderId']];
	    	 	 $page['data'][$key]['createTime'] = date('Y-m-d H:i',strtotime($v['createTime']));
	    	 }
	    }
	    return $page;
	}
	/**
	* 查询聊天记录
	*/
	public function pageQuery($receiveId, $userId){
		// 根据receiveId及店铺id查找dialogId
		$dialogId = Db::name('dialogs')->where(['userId'=>$userId,'shopId'=>$receiveId])
									   ->column('id');
		$rs = Db::name('dialog_contents')->where([['dialogId','in',$dialogId]])
										 ->order('createTime desc')
										 ->paginate(input('pagesize/d'))
										 ->toArray();
		// 反序列化
		foreach($rs['data'] as $k=>$v){
			$content = $this->mb_unserialize($v['content']);
			$rs['data'][$k]['content'] = $content['content'];
			$rs['data'][$k]['from'] = $content['from'];
			$rs['data'][$k]['userName'] = Db::name('users')->where(['userId'=>$content['from']])->value('userName');
		}
		if(!empty($rs['data']))usort($rs['data'], "createTimeAscSort");
		return $rs;
	}
	function mb_unserialize($serial_str) {
	    $out = preg_replace_callback( '!s:(\d+):"(.*?)";!s', function($r){ return 's:'.strlen($r[2]).':"'.$r[2].'";'; }, $serial_str );
	    return unserialize($out);
	}

	/**
    * 设置为已读【用户】
    */
    public function setRead($userId){
    	$shopId = (int)input('shopId');
        $dialogIds = Db::name('dialogs')->where(['userId'=>$userId,'shopId'=>$shopId])->column('id');
        $msgIds = [];
        if(!empty($dialogIds)){
        	$unReadArr = Db::name('dialog_contents')
        				 ->field('content,id')
        				 ->where([['dialogId','in',$dialogIds],['isRead','=','0']])
        				 ->select();
	        foreach($unReadArr as $v){
	          $content = $this->mb_unserialize($v['content']);
	          if($content['from']!=$userId)array_push($msgIds, $v['id']);
	        }
      	}
		if(!empty($msgIds)){
			Db::name('dialog_contents')->where([['id','in',$msgIds]])->setField('isRead',1);
		}
		return WSTReturn('ok',1);
    }
	/******************************************************* 店铺 **********************************************************************/
    public function setReadForService($serviceId){
    	// 发送者
        $userId = input('from');
        // 接收者
        // $serviceId = session('WST_USER.serviceId');
        $shopId = Db::name('shop_services')->where(["serviceId"=>$serviceId])->value('shopId');
        $dialogIds = Db::name('dialogs')->where(['userId'=>$userId,'shopId'=>$shopId])->column('id');
        $msgIds = [];
        if(!empty($dialogIds)){
        	$unReadArr = Db::name('dialog_contents')
        				 ->field('content,id')
        				 ->where([['dialogId','in',$dialogIds],['isRead','=','0']])
        				 ->select();
	        foreach($unReadArr as $v){
	          $content = $this->mb_unserialize($v['content']);
	          if($content['from']==$userId)array_push($msgIds, $v['id']);
	        }
      	}
		if(!empty($msgIds)){
			Db::name('dialog_contents')->where([['id','in',$msgIds]])->setField('isRead',1);
		}
		return WSTReturn('ok',1);
    }
	/**
	* 查询聊天记录【店铺】
	*/
	public function shopPageQuery($receiveId, $serviceId){
		$shopId = Db::name('shop_services')->where("serviceId={$serviceId}")->value('shopId');
		// 根据receiveId及店铺id查找dialogId
		$dialogId = Db::name('dialogs')->where(['userId'=>$receiveId,'shopId'=>$shopId])
									   ->column('id');
		$rs = Db::name('dialog_contents')->where([['dialogId','in',$dialogId]])
										 ->order('createTime desc')
										 ->paginate(input('pagesize/d'))
										 ->toArray();
		// 反序列化
		foreach($rs['data'] as $k=>$v){
			$content = $this->mb_unserialize($v['content']);
			$rs['data'][$k]['content'] = $content['content'];
			$rs['data'][$k]['from'] = $content['from'];
			$rs['data'][$k]['userName'] = Db::name('users')->where(['userId'=>$content['from']])->value('userName');
		}
		if(!empty($rs['data']))usort($rs['data'], "createTimeAscSort");
		return $rs;
	}
	/**
	* 查询最近会话
	*/
	public function getRecent($receiveId){
		// 1.获取与用户联系过的所有店铺id
		//	(1).先获取会话id
		$dialogIdArrs = Db::name('dialogs')->where(['userId'=>$receiveId,'userDel'=>0])
										->group('shopId')
										->field('GROUP_CONCAT(id) id,shopId')
										->select();
		//  (2).根据会话id
		// 根据dialogId查询所有未读会话
		$result = [];
		$todayStart = strtotime(date('Y-m-d'));
        $todayEnd = strtotime(date('Y-m-d 23:59:59'));
		foreach($dialogIdArrs as $k=>$v){
			$dialogIds = $v['id'];
			$shopId = $v['shopId'];
			$unReadArr = Db::name('dialog_contents')->field('content,dialogId')
													->where([['dialogId','in',$dialogIds],['isRead','=','0']])
													->select();
			// 统计未读消息数
	        $unReadNum = 0;
	        foreach($unReadArr as $v){
	          $content = $this->mb_unserialize($v['content']);
	          if($content['from']!=$receiveId){// 将用户发送给客服的未读消息排除
		          ++$unReadNum;
	          }
	        }
	        // 取店铺信息及最后一条聊天内容
	        $shopInfo = Db::name('shops')->field('shopName,shopImg,shopId')
	        							 ->find(['shopId'=>$shopId]);
	        $shopInfo['unReadNum'] = $unReadNum;
	        $dialog_content = Db::name('dialog_contents')->field('content,createTime')
	        											 ->where([['dialogId','in',$dialogIds]])
	        											 ->order('createTime','desc')
	        											 ->find();
	        // 处理时间 时间格式：若为今天则显示时:分:秒，否则显示 月:日
			$cTime = strtotime($dialog_content['createTime']);
			$dialog_content['createTime'] = ($todayEnd>=$cTime && $todayStart<$cTime )?date('H:i',$cTime):date('Y-m-d',$cTime);
	        $dialog_content['content'] = $this->mb_unserialize($dialog_content['content']);
	        $_content = htmlspecialchars_decode($dialog_content['content']['content']);
	        $flag = self::is_not_json($_content);
	        if($flag){
	        	$_json = json_decode($_content,true);
	        	switch ($_json['type']) {
	        		case 'image':
	        			$dialog_content['content']['content'] = '['.lang('wstim_picture').']';
	        			break;
	        		case 'goods':
	        			$dialog_content['content']['content'] = '['.lang('wstim_product_information').']';
	        			break;
	        		case 'orders':
	        			$dialog_content['content']['content'] = '['.lang('wstim_order_information').']';
	        			break;
	        	}
	        }
	        $result[$k] = array_merge($shopInfo,$dialog_content);
		}
		usort($result, "createTimeDescSort");
		return $result;
	}
	private function is_not_json($str){
	   return is_array(json_decode($str,true));
	}
	/**
	* 获取客服所属店铺id
	*/
	public function getShopId(){
		$serviceId = session('WST_USER.serviceId');
		return Db::name('shop_services')->where(['serviceId'=>$serviceId])->value('shopId');
	}
	/**
    * 检测是否为客服
    */
    public function isService($serviceId){
		$rs = Db::name('shop_services')->where(['serviceId'=>$serviceId,'dataFlag'=>1])->find();
		return !empty($rs);
    }
	/******************************************************* 店铺 **********************************************************************/
	/**
	* 查询店铺最近会话
	*/
	public function getShopRecent($shopId){
		// 店铺下的所有账号
		$receiveIds = Db::name('shop_users')->where(['shopId'=>$shopId])->column('userId');

		// 1.获取与用户联系过的所有店铺id
		//	(1).先获取会话id
		$dialogIdArrs = Db::name('dialogs')->where(['shopId'=>$shopId,'shopDel'=>0])
										->group('userId')
										->field('GROUP_CONCAT(id) id,userId')
										->select();
		//  (2).根据会话id
		// 根据dialogId查询所有未读会话
		$result = [];
		$todayStart = strtotime(date('Y-m-d'));
        $todayEnd = strtotime(date('Y-m-d 23:59:59'));
		foreach($dialogIdArrs as $k=>$v){
			$dialogIds = $v['id'];
			$userId = $v['userId'];
			$unReadArr = Db::name('dialog_contents')->field('content,dialogId,isRead')
													->where([['dialogId','in',$dialogIds]])
													->select();
			// 统计未读消息数
			$unReadNum = 0;
	        foreach($unReadArr as $v){
			  $content = $this->mb_unserialize($v['content']);
	          if((!in_array($content['from'],$receiveIds)) && $v['isRead']==0){// 将客服发送给用户的未读消息排除
		          ++$unReadNum;
	          }
	        }
	        // 取用户信息及最后一条聊天内容
	        $userInfo = Db::name('users')->field('userName,userPhoto,userId,loginName')
	        							 ->find(['userId'=>$userId]);
	        $userInfo['unReadNum'] = $unReadNum;
	        $dialog_content = Db::name('dialog_contents')->field('content,createTime')
	        											 ->where([['dialogId','in',$dialogIds]])
	        											 ->order('createTime','desc')
	        											 ->find();
	        // 处理时间 时间格式：若为今天则显示时:分:秒，否则显示 月:日
			$cTime = strtotime($dialog_content['createTime']);
			$dialog_content['createTime'] = ($todayEnd>=$cTime && $todayStart<$cTime )?date('H:i',$cTime):date('Y-m-d',$cTime);
	        $dialog_content['content'] = $this->mb_unserialize($dialog_content['content']);
	        $_content = htmlspecialchars_decode($dialog_content['content']['content']);
	        $flag = self::is_not_json($_content);
	        if($flag){
	        	$_json = json_decode($_content,true);
	        	switch ($_json['type']) {
	        		case 'image':
	        			$dialog_content['content']['content'] = '['.lang('wstim_picture').']';
	        			break;
	        		case 'goods':
	        			$dialog_content['content']['content'] = '['.lang('wstim_product_information').']';
	        			break;
	        		case 'orders':
	        			$dialog_content['content']['content'] = '['.lang('wstim_order_information').']';
	        			break;
	        	}
	        }
	        $result[$k] = array_merge($userInfo,$dialog_content);
		}
		usort($result, "createTimeDescSort");
		return $result;
	}
	/**
	 * 删除对话
	 */
	public function deldialog($userId){
		$ids = input('ids');
		if($ids=='')return WSTReturn(lang('wstim_please_select_a_conversation_to_delete'));
		$idArr = explode(',',$ids);
		// 待删除的shopId
		$sids = [];
		// 待删除的userId
		$uids = [];
		foreach($idArr as $v){
			if(strpos($v,'s_')!==false){
				$sids[] = str_replace('s_','',$v);
			}else if(strpos($v,'u_')!==false){
				$uids[] = str_replace('u_','',$v);
			}
		}
		// 用户删店铺
		if(!empty($sids)){
			$rs = Db::name('dialogs')->where(['userId'=>$userId])
									 ->whereIn('shopId',$sids)
									 ->setField('userDel',1);
			if($rs===false)return WSTReturn(lang('wstim_op_err'));
		}
		// 店铺删用户
		$shopId = getShopId($userId);
		if(!empty($uids) && $shopId!=0){
			$rs = Db::name('dialogs')->where(['shopId'=>$shopId])
									 ->whereIn('userId',$uids)
									 ->setField('shopDel',1);
			if($rs===false)return WSTReturn(lang('wstim_op_err'));
		}
		return WSTReturn(lang('wstim_op_ok'),1);
	}
	/**
	 * 新增留言
	 */
	public function add($userId){
		$data =	input('param.');
		// 数据入库
		$dm = Db::name('dialogs');
		$dialogId = $dm->where(['userId'=>$userId,'shopId'=>$data['to'],'serviceId'=>0])->value('id');
		// 将会话记录写入会话表
		if(empty($dialogId)){
			$_data = [
		    	'userId'=>$userId,
		    	'shopId'=>$data['to'],
		    	'serviceId'=>0,
		    	'createTime'=>date('Y-m-d H:i:s')
		    ];
			$dialogId = Db::name('dialogs')->insert($_data,false,true);
		}
		// 是否有触发关键词
		$autoReply = ImAutoReply($data['content'], $data['to']);
		if($dialogId){
			// 设置userDel、shopDel的值为0
			Db::name('dialogs')->where(['userId'=>$userId,'shopId'=>$data['to']])->update(['userDel'=>0,'shopDel'=>0]);

			$dcm = Db::name('dialog_contents');
			// 190118防止双引号被转义成 &quot;
			$data['content'] = strip_tags(htmlspecialchars_decode($data['content']),"");
			// 禁用词过滤
			$data['content'] = filterContent($data['content']);
			$chat_content = serialize(["content"=>$data['content'],"from"=>"{$userId}"]);
			$_data = ['dialogId'=>"$dialogId",
				      'content'=>$chat_content,
				      'createTime'=>date('Y-m-d H:i:s')];
			$rs = $dcm->insert($_data);
			if($rs!==false){
				if($autoReply!==false){
					// 发送者为店铺所有者的userId
					$from = Db::name('shops')->where(['shopId'=>$data['to']])->value('userId');
					$createTime = date('Y-m-d H:i:s', time()+1);
					foreach($autoReply as $content){
						$dcm = Db::name('dialog_contents');
						// 写入自动回复内容
						$content = strip_tags(htmlspecialchars_decode($content),"");
						$chat_content = serialize(["content"=>$content,"from"=>"{$from}"]);
						$_data = ['dialogId'=>"$dialogId",
								'content'=>$chat_content,
								'createTime'=>$createTime];
						$dcm->insert($_data);
					}
				}
				return WSTReturn(lang('wstim_op_ok'),1, $autoReply);
			}
		}
		return WSTReturn(lang('wstim_op_err'));
	}
	/**
	* 检测当前客服是否属于该店铺
	*/
	public function checkIsShopService($userId,$shopId){
		$serviceId = Db::name('shop_users')->where(['userId'=>$userId, 'shopId'=>$shopId, 'dataFlag'=>1])->value('serviceId');
		return Db::name('shop_services')->where(['serviceId'=>$serviceId, 'shopId'=>$shopId])->find();
	}
	/**
	 * 新增留言【服务器异常情况】
	 */
	public function sendOffLineMsg($serviceId){
		$data =	input('param.');
		// 数据入库
		$dm = Db::name('dialogs');
		$dialogId = $dm->where(['userId'=>$data['userId'],'shopId'=>$data['shopId'],'serviceId'=>$serviceId])->value('id');
		// 将会话记录写入会话表
		if(empty($dialogId)){
			$_data = [
		    	'userId'=>$data['userId'],
		    	'shopId'=>$data['shopId'],
		    	'serviceId'=>$serviceId,
		    	'createTime'=>date('Y-m-d H:i:s')
		    ];
			$dialogId = $dm->insert($_data,false,true);
		}
		// 禁用词过滤
		$data['content'] = filterContent($data['content']);

		// 设置userDel的值为0
		Db::name('dialogs')->where(['userId'=>$data['userId'],'shopId'=>$data['shopId']])->update(['userDel'=>0,'shopDel'=>0]);
		if($dialogId){
			$dcm = Db::name('dialog_contents');
			$chat_content = serialize(["content"=>$data['content'],"from"=>$serviceId,"to"=>"{$data['userId']}"]);
			$_data = ['dialogId'=>"$dialogId",
				      'content'=>"$chat_content",
				      'createTime'=>date('Y-m-d H:i:s')];
			$rs = $dcm->insert($_data);
			if($rs!==false)return WSTReturn(lang('wstim_op_ok'),1);
		}
		return WSTReturn(lang('wstim_op_err'));
	}
}
