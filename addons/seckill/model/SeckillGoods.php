<?php
namespace addons\seckill\model;
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
 * 秒杀商品
 */
class SeckillGoods extends Base{

	/**
	 * 改变秒杀信息
	 */
	public function spQueryGoodsByPage($shopId){
		$timeId = (int)input("timeId");
		$seckillId = (int)input("seckillId");
		$goodsName = input("gname");
		$where = [];
		$where[] = ["sg.shopId",'=',$shopId];
		$where[] = ["sg.seckillId",'=',$seckillId];
		$where[] = ["sg.timeId",'=',$timeId];
		$where[] = ["sg.dataFlag",'=',1];
		$where[] = ['g.goodsStatus','=',1];
		$where[] = ['g.dataFlag','=',1];
		if($goodsName!='')$where[] = ['gl.goodsName','like','%'.$goodsName.'%'];
		$page = Db::name("seckill_goods sg")
				->join("goods g","sg.goodsId=g.goodsId","inner")
                ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
				->field("g.goodsId,gl.goodsName,g.shopPrice,g.goodsImg,sg.id,sg.seckillId,sg.timeId,sg.secPrice,sg.secNum,sg.secLimit,sg.createTime")
				->where($where)
				->paginate(input('limit/d'))->toArray();
		if(count($page['data'])>0){
        	$time = time();
        	foreach($page['data'] as $key =>$v){
        		$page['data'][$key]['goodsImg'] = WSTImg($v['goodsImg']);
        	}
        }
        $page['status'] = 1;
        return $page;
	}

	/**
	 * 商家删除商品
	 */
	public function spDelGoods($shopId){
		$id = (int)input("id");
		$data = [];
		$data["dataFlag"] = -1;
		$result = $this->allowField(true)->update($data,['id'=>$id,'shopId'=>$shopId]);
		if(false !== $result){
			return WSTReturn('删除成功',1);
		}
		return WSTReturn('删除失败');
	}

	/**
	 * 秒杀待添加商品分页查询
	 */
	public function spSearchGoodsByPage($shopId){
		$timeId = (int)input("timeId");
		$seckillId = (int)input("seckillId");
		$cat1 = (int)input("cat1");
		$cat2 = (int)input("cat2");
		$isCheck = (int)input("isCheck")?true:false;
		$goodsName = input("goodsName");
		$where = [];
		$where[] = ['g.shopId','=',$shopId];
		$where[] = ['g.goodsStatus','=',1];
		$where[] = ['g.dataFlag','=',1];

		if($cat1>0) $where[] = ['g.shopCatId1','=',$cat1];
		if($cat2>0) $where[] = ['g.shopCatId2','=',$cat2];
		if($goodsName!='')$where[] = ['gl.goodsName','like','%'.$goodsName.'%'];
		$page = [];
		if($isCheck){
			$page = Db::name("goods g")
                ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
				->join("seckill_goods sg","g.goodsId=sg.goodsId and sg.timeId=".$timeId." and sg.seckillId=".$seckillId." and sg.dataFlag=1","inner")
				->where($where)
				->field("g.goodsId,g.goodsImg,gl.goodsName,g.shopPrice,sg.id")
				->paginate(input('limit/d'))->toArray();
		}else{
			$page = Db::name("goods g")
                ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
				->join("seckill_goods sg","g.goodsId=sg.goodsId and sg.timeId=".$timeId." and sg.seckillId=".$seckillId." and sg.dataFlag=1","left")
				->where($where)
				->field("g.goodsId,g.goodsImg,gl.goodsName,g.shopPrice,sg.id")
				->paginate(input('limit/d'))->toArray();
		}

		if(count($page['data'])>0){
        	$time = time();
        	foreach($page['data'] as $key =>$v){
        		$page['data'][$key]['goodsImg'] = WSTImg($v['goodsImg']);
        		$page['data'][$key]['isCheck'] = ($v['id']>0)?1:0;
        	}
        }
        $page['status'] = 1;
        return $page;
	}

	public function checkGoods($shopId){
		$timeId = (int)input("timeId");
		$seckillId = (int)input("seckillId");
		$goodsId = (int)input("goodsId");
		$where = [];
		$where[] = ['shopId','=',$shopId];
		$where[] = ['dataFlag','=',1];
		$where[] = ['timeId','=',$timeId];
		$where[] = ['seckillId','=',$seckillId];
		$where[] = ['goodsId','=',$goodsId];
		$seckill = Db::name("seckills")->where(["id"=>$seckillId,"shopId"=>$shopId])->field("id,seckillStatus")->find();
		if($seckill["seckillStatus"]>=0)return WSTReturn(lang('seckill_status_changed_tips'),-1);
		$goods = Db::name("seckill_goods")->where($where)->find();
		if(empty($goods)){
			$data = [];
			$data["shopId"] = $shopId;
			$data["seckillId"] = $seckillId;
			$data["timeId"] = $timeId;
			$data["goodsId"] = $goodsId;
			$data["dataFlag"] = 1;
			$data["createTime"] = date("Y-m-d H:i:s");
			Db::name("seckill_goods")->insert($data);
			return WSTReturn('添加成功',1);
		}else{
			$data = [];
			$data["dataFlag"] = -1;
			$this->allowField(true)->update($data,['goodsId'=>$goodsId,'timeId'=>$timeId,'seckillId'=>$seckillId,'shopId'=>$shopId]);
			return WSTReturn('取消成功',1);
		}

	}

	public function goodsSet($shopId){
		$id = (int)input("id");
		$iname = input("iname");
		$arr = ["secPrice","secNum","secLimit"];
		if(in_array($iname, $arr)){
			$seckill = Db::name("seckills s")->join("seckill_goods sg","s.id=sg.seckillId","inner")
						->where(["sg.id"=>$id])
						->field("s.id,seckillStatus")->find();
			if($seckill["seckillStatus"]>=0)return WSTReturn(lang('seckill_status_changed_tips'),-1);
			$ival = 0;
			if($iname == "secPrice"){
				$ival = (float)input("ival");
				$goods = Db::name("seckill_goods sg")
						->join("goods g","g.goodsId=sg.goodsId","inner")
						->where(['sg.id'=>$id,'sg.shopId'=>$shopId])
						->field("g.shopPrice")->find();
				if($ival>=$goods['shopPrice']){
					return WSTReturn(lang('seckill_price_tips'));
				}

			}else{
				$ival = (int)input("ival");
			}
			$data = [];
			$data[$iname] = $ival;
			$result = $this->allowField(true)->update($data,['id'=>$id,'shopId'=>$shopId]);
			if(false !== $result){
				return WSTReturn(lang('seckill_operation_success'),1);
			}
			return WSTReturn(lang('seckill_operation_fail'));
		}else{
			return WSTReturn(lang('seckill_operation_fail'));
		}
	}

	public function goodsBatchSet($shopId){
		Db::startTrans();
		try{
			$ids = input("ids/a");
			$iname = input("iname");
			$seckillId = (int)input("seckillId");
			$arr = ["secNum","secLimit"];
			if(in_array($iname, $arr)){

				$seckill = Db::name("seckills")->where(["id"=>$seckillId,"shopId"=>$shopId])->field("id,seckillStatus")->find();
				if(empty($seckill))return WSTReturn(lang('seckill_operation_fail'),-1);
				if($seckill["seckillStatus"]>=0)return WSTReturn(lang('seckill_status_changed_tips'),-1);
				$ival = (int)input("ival");
				$data = [];
				$data[$iname] = $ival;
				$where = [];
				$where[] = ['id','in',$ids];
				$where[] = ['seckillId','=',$seckillId];
				$where[] = ['shopId','=',$shopId];
				$result = $this->allowField(true)->where($where)->update($data);
				if(false !== $result){
					Db::commit();
					return WSTReturn(lang('seckill_operation_success'),1);
				}
				return WSTReturn(lang('seckill_operation_fail'));
			}else{
				return WSTReturn(lang('seckill_operation_fail'));
			}
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('seckill_operation_fail'),-1);
        }
	}


	/**
	 * 秒杀列表信息
	 */
	public function queryGoodsByPage(){
		$timeId = (int)input("timeId");
		$seckillId = (int)input("seckillId");
		$goodsName = input("goodsName");
		$where = [];
		$where[] = ["sg.seckillId",'=',$seckillId];
		$where[] = ["sg.timeId",'=',$timeId];
		$where[] = ["sg.dataFlag",'=',1];
		$where[] = ['g.goodsStatus','=',1];
		$where[] = ['g.dataFlag','=',1];
		if($goodsName!='')$where[] = ['g.goodsName','like','%'.$goodsName.'%'];
		$page = Db::name("seckill_goods sg")
				->join("goods g","sg.goodsId=g.goodsId","inner")
                ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
				->field("g.goodsId,gl.goodsName,g.shopPrice,g.goodsImg,sg.id,sg.seckillId,sg.timeId,sg.secPrice,sg.secNum,sg.secLimit,sg.createTime")
				->where($where)
				->paginate(input('limit/d'))->toArray();
		if(count($page['data'])>0){
        	$time = time();
        	foreach($page['data'] as $key =>$v){
        		$page['data'][$key]['goodsImg'] = WSTImg($v['goodsImg']);
        	}
        }
        $page['status'] = 1;
        return $page;
	}

	/**
	* 删除秒杀商品
	*/
	public function delGoods(){
		$id = (int)input('post.id');
		//判断商品状态
		$rs = $this->where(['id'=>$id,"dataFlag"=>1])->find();
		if(empty($rs))return WSTReturn(lang('seckill_invalid_goods'));
		Db::startTrans();
		try{
			$res = $this->where(['id'=>$id])->update(['dataFlag'=>-1]);
			if($res!==false){
				Db::commit();
				return WSTReturn(lang('seckill_operation_success'),1);
			}
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('seckill_operation_fail'),-1);
	}


	/**
	 * 秒杀列表信息
	 */
	public function pageQuery($timeId=0,$num=0){
        $num = ($num>0)?$num:input('limit/d');
        $shopId = (int)input("shopId",0);
		$catId = (int)input("catId");
		$timeId = ($timeId>0)?$timeId:(int)input("timeId");
		$goodsName = input("keyword");
		$today = date("Y-m-d");
		$where = [];
        if($shopId>0)$where[] = ["sg.shopId",'=',$shopId];
		$where[] = ["sg.timeId",'=',$timeId];
		$where[] = ["sg.dataFlag",'=',1];
		$where[] = ['sg.secPrice','>',0];
		$where[] = ['g.goodsStatus','=',1];
		$where[] = ['g.dataFlag','=',1];
		$where[] = ['k.seckillStatus','=',1];
		$where[] = ['k.dataFlag','=',1];
		$where[] = ['k.isSale','=',1];
		$where[] = ['k.startDate','<=',$today];
		$where[] = ['k.endDate','>=',$today];
		if($goodsName!='')$where[] = ['gl.goodsName','like','%'.$goodsName.'%'];
		if($catId>0)$where[] = ['g.goodsCatIdPath','like',$catId.'_%'];
		$page = Db::name("seckill_goods sg")
				->join("goods g","sg.goodsId=g.goodsId","inner")
                ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
				->join("seckills k","k.id=sg.seckillId","inner")
				->join("shops s","g.shopId = s.shopId")
				->field("g.goodsId,gl.goodsName,g.shopPrice,g.goodsImg,sg.id,sg.seckillId,sg.timeId,sg.secPrice,sg.secNum,sg.secLimit,sg.createTime,sg.hasBuyNum")
				->where($where)
				->order("sg.saleNum desc,sg.id")
				->paginate($num)
				->toArray();
		if(count($page['data'])>0){
        	$time = time();
        	foreach($page['data'] as $key =>$v){
        		$page['data'][$key]['goodsImg'] = WSTImg($v['goodsImg']);
        		$page['data'][$key]['gstock'] = $v['secNum']-$v['hasBuyNum'];
        		$page['data'][$key]['percent'] = ($v['secNum']>0)?(number_format($v['hasBuyNum']/$v['secNum']*100,1)):100;

        	}
        }
        $page['status'] = 1;
        return $page;
	}


	/**
	 * 获取秒杀详情
	 */
	public function getBySale($id,$userId,$imgType=1,$from=0){

		$key = input('key');
		$where = [];
		$where[] = ['sg.dataFlag','=',1];
		$where[] = ['sg.id','=',$id];
		$where[] = ['s.dataFlag','=',1];
		$where[] = ['s.seckillStatus','=',1];
		$where[] = ['s.isSale','=',1];
		$sg = $this->alias("sg")->join("seckills s","s.id=sg.seckillId","inner")
                    ->join('__SECKILLS_LANGS__ sl','sl.seckillId=s.id and sl.langId='.WSTCurrLang())
					->where($where)
					->field("sg.*,s.startDate,s.endDate,s.seckillStatus,sl.seckillDes")
					->find();

		$goodsId = $sg['goodsId'];
		if(empty($sg))return [];
		$sg = $sg->toArray();
		$rs = Db::name('goods')
            ->alias('g')
            ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
            ->field('g.*,gl.goodsName,gl.goodsTips,gl.goodsDesc,gl.goodsSeoKeywords,gl.goodsSeoDesc')
            ->where(['g.goodsId'=>$goodsId,'dataFlag'=>1])->find();
		if(!empty($rs)){
			$gunit = WSTDatas('GOODS_UNIT',$rs['goodsUnit']);
            $rs['goodsUnit'] = ($gunit && isset($gunit['dataName']))?$gunit['dataName']:'';
			$rs['goodsDesc'] = htmlspecialchars_decode($rs['goodsDesc']);
			$rs['goodsDesc'] = str_replace('${DOMAIN}',WSTConf('CONF.resourcePath'),$rs['goodsDesc']);
			Db::name('goods')->where('goodsId',$goodsId)->update(['visitNum'=>Db::raw('visitNum+1')]);
			$rs = array_merge($rs,$sg);
			$today = date("Y-m-d");
			$tomorrow = date("Y-m-d",strtotime("+1 day"));
			$nowTime = date("H:i:s");
			$timeId = $sg["timeId"];
			$timeItv = Db::name("seckill_time_intervals")->where(['id'=>$timeId])->find();
			$status = 0;
			if($timeItv['startTime']<$nowTime && $timeItv['endTime']>=$nowTime){
        		$status = 1;
        	}else if($timeItv['startTime']>=$nowTime){
                $status = 0;
        	}else{
        	    $status = 2;
        	}
        	if($status<2){
        		$rs['status'] = $status;
        		$rs["startTime"] = $today." ".$timeItv["startTime"];
				$rs["endTime"] = $today." ".$timeItv["endTime"];
        	}else{
        		$rs['status'] = 0;
        		$rs["startTime"] = $tomorrow." ".$timeItv["startTime"];
				$rs["endTime"] = $tomorrow." ".$timeItv["endTime"];
        	}

			$rs['read'] = false;
			$rs['canBuyNum']=$rs['secLimit'];
            if($userId>0){
                $myOrder=Db::name('orders')->where(['orderCode'=>'seckill','orderCodeTargetId'=>$id,'dataFlag'=>1,'userId'=>$userId])->whereNotIn('orderStatus',-1)->column("orderId");//获取个人参与此秒杀的所有订单
                $myOrderNum=Db::name('order_goods')->where("orderId","in",$myOrder)->sum("goodsNum");//获取个人参与此秒杀的总数
                if($myOrderNum>=$rs['secLimit']){
                    //$rs['status'] = -1;//已达购买限制
                    $rs['canBuyNum'] = 0;
                }else{
                	$rs['canBuyNum'] = $rs['secLimit']-$myOrderNum;
                }

            }
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
				           ->field('gc.isAllowImg,scl.catName,sit.catId,sit.itemId,sil.itemName,sit.itemImg')
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
			//关注
			$f = model('common/ShopMembers');
			$rs['favShop'] = $f->checkFavorite($rs['shopId'],$userId);

		}
		return $rs;
	}

	// 获取商品详情
	public function getGoodsDetail($goodsId=0){
		// 未传递goodsId返回空数组
		if($goodsId <= 0)return [];
		$rs = Db::name('goods')
                ->alias('g')
                ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
                ->field('gl.goodsDesc')->where(['g.goodsId'=>$goodsId,'dataFlag'=>1])->find();
        return $rs;
	}

	/**
	 * 获取配置
	 */
	public function getAddonConfig(){
		$addon = Db::name('addons')->where("name","Seckill")->field("config")->find();
		$config = json_decode($addon["config"],true);
		return $config;
	}

	/*
    * 商品详情分享海报
    */
    public function createPoster($userId,$qr_code,$outImg){

        $cfg = self::getAddonConfig();
        $id = (int)input("id");
        $goods = Db::name("goods g")
                ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
		        ->join("seckill_goods sg","g.goodsId=sg.goodsId","inner")
		        ->where(['sg.id'=>$id])
		        ->field("g.goodsId,gl.goodsName,g.goodsImg,sg.secPrice shopPrice,g.goodsUnit")
		        ->find();
        $gunit = WSTDatas('GOODS_UNIT',$goods['goodsUnit']);
        $goods['goodsUnit'] = ($gunit && isset($gunit['dataName']))?$gunit['dataName']:'';
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
	        	$tmpImg = WSTRootPath().'/upload/shares/seckill/'.date("Y-m").'/'.$userId.'.jpg';
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
        $text2 = mb_convert_encoding(lang('seckill_price').'：', "html-entities", "utf-8"); //转成html编码
        imagettftext($share_bg, 18, 0, 80, $vh, $textcolor2, $font, $text2);

        $textcolor3 = imagecolorallocate($share_bg,255,0,54);
        $text = WSTImageAutoWrap(20, 0, $font, lang('currency_symbol').(float)$goods['shopPrice'],700);
        imagettftext($share_bg, 24, 0, 180, $vh, $textcolor3, $font, $text);

        $text = mb_convert_encoding(lang('seckill_share_tip_words'), "html-entities", "utf-8"); //转成html编码
        imagettftext($share_bg, 24, 0, 126, 1255, $textcolor, $font, $text);
        //输出图片
        $shareImg = WSTRootPath().'/'.$outImg;
        imagepng($share_bg, $shareImg);
        imagedestroy($new_qrcode);
    	imagedestroy($share_bg);

        return WSTReturn("",1,["shareImg"=>$outImg]);
    }

}

