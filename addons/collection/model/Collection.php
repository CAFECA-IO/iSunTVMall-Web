<?php
namespace addons\collection\model;
use think\addons\BaseModel as Base;
use think\Db;
use Env;
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
 * 商品采集业务处理
 */
class Collection extends Base{

	/**
	 * 添加菜单、权限
	 */
	public function installMenu(){
		Db::startTrans();
		try{
			$now = date("Y-m-d H:i:s");
			//商家中心
            $homeMenuLangParams = [
                1=>['menuName'=>'商品采集'],
                2=>['menuName'=>'商品采集'],
                3=>['menuName'=>'Commodity collection'],
            ];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>29,"menuUrl"=>"/addon/collection-collection-index","menuOtherUrl"=>"/addon/collection-collection-save","menuType"=>1,"isShow"=>1,"menuSort"=>1,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"collection"]);
            $datas = [];
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['menuId'] = $homeMenuId;
                $data['langId'] = $v['id'];
                $data['menuName'] = $homeMenuLangParams[$v['id']]['menuName'];
                $datas[] = $data;
            }
            Db::name('home_menus_langs')->insertAll($datas);
			installSql("collection");//传入插件名
			Db::commit();
			return true;
		}catch (\Exception $e) {
			//print_r($e);
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
            $homeMenuId = Db::name('home_menus')->where(["menuMark"=>"collection"])->value('menuId');
            Db::name('home_menus')->where(["menuMark"=>"collection"])->delete();
            Db::name('home_menus_langs')->where(['menuId'=>$homeMenuId])->delete();
			uninstallSql("collection");//传入插件名
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
			Db::name('home_menus')->where("menuMark",'=',"collection")->update(["isShow"=>$isShow]);
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
	public function collectionSave(){
		set_time_limit (0);
		// 请求示例 url 默认请求参数已经做URL编码
		$goodsUrl = input("goodsUrl");
		$preg = "/^http(s)?:\\/\\/.+/";
		if(!preg_match($preg,$goodsUrl)){
			return WSTReturn(lang("collection_url_error"),-1);
		}
		$conf = $this->getConf("Collection");
		$apiKey = $conf['apiKey'];

		if(strpos($goodsUrl,"tmall.com")){//天猫
			return $this->tmallSave($goodsUrl,$apiKey,1);
		}else if(strpos($goodsUrl,"taobao.com")){//淘宝
			return $this->tmallSave($goodsUrl,$apiKey,0);
		}else if(strpos($goodsUrl,"jd.com")){//京东
			return $this->jdSave($goodsUrl,$apiKey);
		}else{
			return WSTReturn(lang("collection_not_support_url"),-1);
		}
	}
	//天猫/淘宝
	public function tmallSave($goodsUrl,$apiKey,$isTmall=1){
		$resourceDomain = WSTConf('CONF.resourcePath');
		$preg = "/^http(s)?:\\/\\/.+/";
		$shopId = (int)session("WST_USER.shopId");
		$goodsCatId = (int)input("goodsCatId");
		$goodsCatIds = model('common/GoodsCats')->getParentIs($goodsCatId);
		$goodsCatIdPath = implode('_',$goodsCatIds)."_";
		Db::startTrans();
		try{
			$gdata = explode("?",$goodsUrl);
			$str_param = $gdata[1];
			$params = explode("&amp;",$str_param);
			$itemid = 0;
			foreach ($params as $key => $v) {
				if($v!=""){
					$obj = explode("=",$v);

					if($obj[0]=="id"){
						$itemid = $obj[1];
					}
				}
			}
			if(!($itemid>0)){
				return WSTReturn(lang("collection_goods_link_error"),-1);
			}
			$url = "https://api03.6bqb.com/tmall/detail?apikey=$apiKey&itemid=$itemid";
			if($isTmall==0){
				$url = "https://api03.6bqb.com/taobao/detail?apikey=$apiKey&itemid=$itemid";
			}
			$goodsData = $this->httpLoad($url);

			$goodsObj = json_decode($goodsData,true);
			$goodsObj = $goodsObj["data"];
			$goodsName = $goodsObj['item']['title'];
			$goods = Db::name("goods g")->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang(),'inner')
					->where(['g.shopId'=>$shopId,'g.dataFlag'=>1,'gl.goodsName'=>$goodsName])
					->find();
			if(!empty($goods)){
				return WSTReturn(lang("collection_repeat_import"),-1);
			}

			$data = [];
			$data['goodsSn'] = WSTGoodsNo();
			$productNo = WSTGoodsNo();
			$data['productNo'] = $productNo;
			//$data['goodsName'] = $goodsObj['item']['title'];
			//复制商品主图
			$gimages = $goodsObj['item']['images'];
			$goodsImgUrl = $gimages[0];
			$timg = explode(".",$gimages[0]);
			$ext = $timg[count($timg)-1];
			$gpath = 'upload/goods/'.date('Y-m');
			$filePath = Env::get('root_path').$gpath;
			$filename = strtolower(WSTGuid()).".".$ext;
			if(!preg_match($preg,$goodsImgUrl))$goodsImgUrl = 'https:'.$goodsImgUrl;
			WSTDownCopyFile($goodsImgUrl, $filePath, $filename);
			$data['goodsImg'] = $gpath.'/'.$filename;

			if(isset($goodsObj['item']['videos']) && count($goodsObj['item']['videos'])>0){
	    		//复制商品视频
	    		$pvideoUrl = $goodsObj['item']['videos'][0]['url'];
	    		$videoUrl = explode("?",$pvideoUrl);
	    		$videoUrl = $videoUrl[0];
	    		$timg = explode(".",$videoUrl);
	    		$ext = $timg[count($timg)-1];
	    		$gpath = 'upload/goods/'.date('Y-m');
	    		$filePath = Env::get('root_path').$gpath;
	    		$filename = strtolower(WSTGuid()).".".$ext;
	    		if(!preg_match($preg,$pvideoUrl))$pvideoUrl = 'https:'.$pvideoUrl;
	    		WSTDownCopyFile($pvideoUrl, $filePath, $filename,1);
	    		$data['goodsVideo'] = $gpath.'/'.$filename;


	    		$videoThumbnailURL = $goodsObj['item']['videos'][0]['videoThumbnailURL'];

				$timg = explode(".",$videoThumbnailURL);
				$ext = $timg[count($timg)-1];
				$gpath = 'upload/goods/'.date('Y-m');
				$filePath = Env::get('root_path').$gpath;
				$filename = strtolower(WSTGuid()).".".$ext;
				if(!preg_match($preg,$videoThumbnailURL))$videoThumbnailURL = 'https:'.$videoThumbnailURL;
				WSTDownCopyFile($videoThumbnailURL, $filePath, $filename);
	    		$data['goodsVideoThumb'] = $gpath.'/'.$filename;
	    	}

	    	//复制商品相册
			$gallerys = [];
			foreach ($gimages as $key => $gimg) {
				if($key>0){
					$timg = explode(".",$gimg);
		    		$ext = $timg[count($timg)-1];
		    		$gpath = 'upload/goods/'.date('Y-m');
		    		$filePath = Env::get('root_path').$gpath;
		    		$filename = strtolower(WSTGuid()).".".$ext;
		    		if(!preg_match($preg,$gimg))$gimg = 'https:'.$gimg;
		    		WSTDownCopyFile($gimg, $filePath, $filename);
		    		$gallerys[$key] = $gpath.'/'.$filename;
				}
			}
			$data['gallery'] = implode(",",$gallerys);

			$descImgs = $goodsObj['item']['descImgs'];
			$goodsDesc = [];
			foreach ($descImgs as $key => $gimg) {

				$timg = explode(".",$gimg);
	    		$ext = $timg[count($timg)-1];
	    		$ext = ($ext!="")?$ext:"jpg";
	    		$gpath = 'upload/goods/'.date('Y-m');
	    		$filePath = Env::get('root_path').$gpath;
	    		$filename = strtolower(WSTGuid()).'.'.$ext;
	    		if(!preg_match($preg,$gimg))$gimg = 'https:'.$gimg;
	    		WSTDownCopyFile($gimg, $filePath, $filename,2);
	    		$imgPath = $gpath.'/'.$filename;
				$goodsDesc[] = '<img src="'.'${DOMAIN}'.'/'.$imgPath.'"/>';
			}
			$goodsDesc = WSTHtmlspecialchars(implode("",$goodsDesc));

			$data['shopId'] = $shopId;
			$data['goodsType'] = 0;
			$priceRange = $goodsObj["item"]['priceRange'];
			$priceRange = explode("-",$priceRange);
			$data['marketPrice'] = $priceRange[0];
			$data['shopPrice'] = $priceRange[0];
			$data['warnStock'] = 0;
			$data['goodsStock'] = 0;
			$data['goodsUnit'] = '';
			$data['isSale'] = 0;
			$data['goodsCatIdPath'] = $goodsCatIdPath;
			$data['goodsCatId'] = $goodsCatId;
			$data['shopCatId1'] = 0;
			$data['shopCatId2'] = 0;
			$data['brandId'] = 0;
			if(WSTConf("CONF.isGoodsVerify")==1){
	            $data['goodsStatus'] = 0;
	        }else{
	            $data['goodsStatus'] = 1;
	        }
			$data['saleNum'] = 0;
			$data['saleTime'] = date("Y-m-d H:i:s");
			$data['isSpec'] = (count($goodsObj["item"]['sku'])>0)?1:0;

			$data['createTime'] = date("Y-m-d H:i:s");
			$data['costPrice'] = 0;
			$goodsId = Db::name("goods")->insertGetId($data);
			if($goodsId>0){


				foreach (WSTSysLangs() as $key => $v) {
				   $dataLang = [];
				   $dataLang['langId'] = $v['id'];
				   $dataLang['goodsId'] = $goodsId;
			       $dataLang['goodsName'] = $goodsName;
			       $dataLang['goodsSeoKeywords'] = $goodsName;
			       $dataLang['goodsSeoDesc'] = $goodsName;
                   $dataLang['goodsTips'] = isset($goodsObj['item']['subTitle'])?$goodsObj['item']['subTitle']:'';
                   $dataLang['goodsDesc'] = $goodsDesc;
                   //对图片域名进行处理

				   $dataLang['goodsDesc'] = htmlspecialchars_decode($dataLang['goodsDesc']);
		           $dataLang['goodsDesc'] = WSTRichEditorFilter($dataLang['goodsDesc']);
		           $dataLang['goodsDesc'] = str_replace($resourceDomain.'/upload/','${DOMAIN}/upload/',$dataLang['goodsDesc']);
                   $goodsLangs[] = $dataLang;
                }
                Db::name('goods_langs')->insertAll($goodsLangs);

				//商品图片
				//WSTUseResource(0, $goodsId, $data['goodsImg']);
				//商品相册
				//WSTUseResource(0, $goodsId, $data['gallery']);
				//商品描述图片
				//WSTEditorImageRocord(0, $goodsId, '',$goodsDesc);
				// 视频
				//if(isset($data['goodsVideo']) && $data['goodsVideo']!=''){
					//WSTUseResource(0, $goodsId, $data['goodsVideo']);
				//}
				$now = date("Y-m-d H:i:s");
				//建立商品评分记录
				$gs = [];
				$gs['goodsId'] = $goodsId;
				$gs['shopId'] = $shopId;
				Db::name('goods_scores')->insert($gs);
				//规格
				if($data['goodsType']==0 && $data['isSpec']==1){
					$specProps = isset($goodsObj["item"]['props'])?$goodsObj["item"]['props']:[];
					$goodsSku = isset($goodsObj["item"]['sku'])?$goodsObj["item"]['sku']:[];
					$specCatIdMap = [];
					$specItemIdMap = [];

					$where = [];
					$where[] = ['shopId','in',[0,$shopId]];
					$where[] = ["dataFlag",'=',1];
					$where[] = ["isAllowImg",'=',1];
					$where[] = ["goodsCatId",'in',$goodsCatIds];
					$imgSpecCat = Db::name("spec_cats")->where($where)->find();

					foreach ($specProps as $key1 => $spec) {
						$specCatName = $spec['name'];
						$where = [];
						$where[] = ['sc.shopId','in',[0,$shopId]];
						$where[] = ["sc.dataFlag",'=',1];
						$where[] = ["scl.catName",'=',$specCatName];
						$where[] = ["sc.goodsCatId",'in',$goodsCatIds];
						$specCat = Db::name("spec_cats sc")->join('__SPEC_CATS_LANGS__ scl','scl.catId=sc.catId and scl.langId='.WSTcurrLang())
									->where($where)
									->find();

						$isImgSpec = 0;
						$catValues = $spec['values'];
						foreach ($catValues as $key3 => $vobj) {
							if($vobj['name']=="")continue;
							if(isset($vobj['image'])){
								$isImgSpec = 1;
								break;
							}
						}

						$catId = 0;
						//创建规格分类
						if(empty($specCat)){
							if(!($isImgSpec==1 && !empty($imgSpecCat))){
								if($spec['name']=="")continue;
								$pcat = [];
								$pcat["shopId"] = $shopId;
								$pcat["goodsCatId"] = $goodsCatId;
								$pcat["goodsCatPath"] = $goodsCatIdPath;
								//$pcat["catName"] = $spec['name'];
								$pcat["isAllowImg"] = 0;
								$pcat["catSort"] = 99;
								$pcat["isShow"] = 1;
								$pcat["dataFlag"] = 1;
								$pcat["createTime"] = $now;
								$catId = Db::name("spec_cats")->insertGetId($pcat);
								$specCatLangs = [];
								foreach (WSTSysLangs() as $key => $v) {
					                $pcatLang = [];
					                $pcatLang['catId'] = $catId;
					                $pcatLang['langId'] = $v['id'];
					                $pcatLang['catName'] = $spec['name'];
					                $specCatLangs[] = $pcatLang;
					            }
					            Db::name('spec_cats_langs')->insertAll($specCatLangs);
							}else{
								$catId = $imgSpecCat['catId'];
							}
						}else{
							$catId = $specCat['catId'];
						}
						$specCatIdMap[$spec['pid']] = $catId;

						$specItems = [];
						$sitemLangs = [];
						//创建规格项
						foreach ($catValues as $key3 => $vobj) {
							if($vobj['name']=="")continue;
							$item = [];
							$item['shopId'] = $shopId;
							$item['catId'] = $catId;
							$item['goodsId'] = $goodsId;
							if(isset($vobj['image'])){
								$gimg = $vobj['image'];
								$timg = explode(".",$gimg);
					    		$ext = $timg[count($timg)-1];
					    		$ext = ($ext!="")?$ext:"jpg";
					    		$gpath = 'upload/goods/'.date('Y-m');
					    		$filePath = Env::get('root_path').$gpath;
					    		$filename = strtolower(WSTGuid()).".".$ext;
					    		if(!preg_match($preg,$gimg))$gimg = 'https:'.$gimg;
					    		WSTDownCopyFile($gimg, $filePath, $filename,0,0);
								$item['itemImg'] = $gpath.'/'.$filename;

								//WSTUseResource(0, $goodsId, $item['itemImg']);
								$isImgSpec = 1;
							}else{
								$item['itemImg'] = '';
							}
							$item["dataFlag"] = 1;
							$item["createTime"] = $now;
							$itemId = Db::name("spec_items")->insertGetId($item);
							foreach (WSTSysLangs() as $lkey => $lv) {
			    				$sitemLang = [];
			    				$sitemLang['itemId'] = $itemId;
			    				$sitemLang['langId'] = $lv['id'];
			    				$sitemLang['goodsId'] = $goodsId;
				    			$sitemLang['itemName'] = $vobj['name'];
				    			$sitemLangs[] = $sitemLang;
				    		}

							$specItemIdMap[$spec['pid']][$vobj['vid']] = $itemId;
						}
						if($isImgSpec==1)Db::name("spec_cats")->where(["shopId"=>$shopId,"catId"=>$catId])->update(["isAllowImg"=>1]);
						//保存规格多语言文字
		    			if(count($sitemLangs)>0)Db::name('spec_items_langs')->insertAll($sitemLangs);
					}
					$goodsSpecs = [];
					foreach ($goodsSku as $key => $sku) {
						if($sku['skuId']>0){
							$propPath = $sku['propPath'];
							$propIds = explode(";",$propPath);
							$specIds = [];
							foreach ($propIds as $key1 => $propId) {
								$pid = explode(":",$propId);
								$specIds[] = $specItemIdMap[$pid[0]][$pid[1]];
							}
							$item = [];
							$item['shopId'] = $shopId;
							$item['goodsId'] = $goodsId;
							$item['productNo'] = $productNo.'-'.($key+1);
							$item['specIds'] = implode(":",$specIds);
							$item['marketPrice'] = $sku['price'];
							$item['specPrice'] = $sku['price'];
							$item['specStock'] = 0;
							$item['warnStock'] = 0;
							$item['isDefault'] = ($key==0)?1:0;
							$item['dataFlag'] = 1;
							$goodsSpecs[] = $item;
						}
					}
					if(count($goodsSpecs)>0)Db::name("goods_specs")->insertAll($goodsSpecs);
				}

				//属性
				$groupProps = isset($goodsObj["item"]['groupProps'])?$goodsObj["item"]['groupProps']:[];
				if(count($groupProps)>0){
					$goodsAttributes = [];

					foreach ($groupProps as $key1 => $props) {
						foreach ($props as $key2 => $vo) {
							foreach ($vo as $key3 => $obj) {
								foreach ($obj as $key4 => $val) {
									$attrName = $key4;
									if($attrName=="")continue;
									$where = [];
									$where[] = ['a.shopId','in',[0,$shopId]];
									$where[] = ["a.dataFlag",'=',1];
									$where[] = ["al.attrName",'=',$attrName];
									$where[] = ["a.goodsCatId",'in',$goodsCatIds];
									$attr = Db::name("attributes a")->join('__ATTRIBUTES_LANGS__ al','al.attrId=a.attrId and al.langId='.WSTcurrLang())
											->where($where)
											->find();

									$attrId = 0;
									if(empty($attr)){
										$attribute = [];
										$attribute["shopId"] = $shopId;
										$attribute["goodsCatId"] = $goodsCatId;
										$attribute["goodsCatPath"] = $goodsCatIdPath;
										$attribute["attrType"] = 0;
										$attribute["attrSort"] = 99;
										$attribute["isShow"] = 1;
										$attribute["dataFlag"] = 1;
										$attribute["createTime"] = $now;
										$attrId = Db::name("attributes")->insertGetId($attribute);

										$attrLangs = [];
							            foreach (WSTSysLangs() as $key => $v) {
							                $attrLang = [];
							                $attrLang['attrId'] = $attrId;
							                $attrLang['langId'] = $v['id'];
							                $attrLang['attrName'] = $attrName;
							                $attrLangs[] = $attrLang;
							            }
							            Db::name('attributes_langs')->insertAll($attrLangs);

									}else{
										$attrId = $attr["attrId"];
									}
									$gattr = [];
									$gattr["shopId"] = $shopId;
									$gattr["goodsId"] = $goodsId;
									$gattr["attrId"] = $attrId;
									$gattr["createTime"] = $now;
									$goodsAttrId = Db::name('goods_attributes')->insertGetId($gattr);

									//保存商品属性多语言
									$attrLangs = [];
									foreach (WSTSysLangs() as $lkey => $lv) {
						    			$attrLang = [];
						    			$attrLang['goodsAttrId'] = $goodsAttrId;
						    			$attrLang['langId'] = $lv['id'];
						    			$attrLang['goodsId'] = $goodsId;
						    			$attrLang['attrVal'] = $val;
						    			$attrLangs[] = $attrLang;
						    		}
						    		Db::name('goods_attributes_langs')->insertAll($attrLangs);


								}
							}
						}
					}

				}
			}
			Db::commit();
			return WSTReturn(lang("collection_success"), 1,$goodsId);
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang("collection_fail"),-1);
        }
	}



	//京东
	public function jdSave($goodsUrl,$apiKey){
		$resourceDomain = WSTConf('CONF.resourcePath');
		$preg = "/^http(s)?:\\/\\/.+/";
		$goodsCatId = (int)input("goodsCatId");
		$goodsCatIds = model('common/GoodsCats')->getParentIs($goodsCatId);
		$goodsCatIdPath = implode('_',$goodsCatIds)."_";
		Db::startTrans();
		try{
			$gdata = explode("jd.com/",$goodsUrl);
			$str_param = $gdata[1];
			$params = explode(".",$str_param);
			$itemid = $params[0];
			if(!($itemid>0)){
				return WSTReturn(lang("collection_goods_link_error"),-1);
			}
			//$itemid = 100007505813;
			$url = "https://api03.6bqb.com/jd/detail?apikey=$apiKey&itemid=$itemid";
			$goodsData = $this->httpLoad($url);
			$goodsObj = json_decode($goodsData,true);
			$goodsObj = $goodsObj["data"];
			//echo $goodsData;
			//$goodsObj = json_decode($goodsData);
			$shopId = (int)session("WST_USER.shopId");

			$goodsName = $goodsObj['item']['name'];
			$goods = Db::name("goods g")->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang(),'inner')
					->where(['g.shopId'=>$shopId,'g.dataFlag'=>1,'gl.goodsName'=>$goodsName])
					->find();
			if(!empty($goods)){
				return WSTReturn(lang("collection_repeat_import"),-1);
			}

			$data = [];
			$data['goodsSn'] = WSTGoodsNo();
			$productNo = WSTGoodsNo();
			$data['productNo'] = $productNo;
			//$data['goodsName'] = $goodsObj['item']['name'];
			//复制商品主图
			$gimages = $goodsObj['item']['images'];
			$goodsImgUrl = $gimages[0];
			if(!preg_match($preg,$goodsImgUrl))$goodsImgUrl = 'https:'.$goodsImgUrl;
			$timg = explode(".",$gimages[0]);
			$ext = $timg[count($timg)-1];
			$ext = ($ext!="")?$ext:"jpg";
			$gpath = 'upload/goods/'.date('Y-m');
			$filePath = Env::get('root_path').$gpath;
			$filename = strtolower(WSTGuid()).".".$ext;
			WSTDownCopyFile($goodsImgUrl, $filePath, $filename);
			$data['goodsImg'] = $gpath.'/'.$filename;

			//京东取不到视频信息

	    	//复制商品相册
			$gallerys = [];

			foreach ($gimages as $key => $gimg) {
				if($key>0){
					$timg = explode(".",$gimg);
		    		$ext = $timg[count($timg)-1];
		    		$gpath = 'upload/goods/'.date('Y-m');
		    		$filePath = Env::get('root_path').$gpath;
		    		$filename = strtolower(WSTGuid()).".".$ext;
		    		if(!preg_match($preg,$gimg))$gimg = 'https:'.$gimg;
		    		WSTDownCopyFile($gimg, $filePath, $filename);
		    		$gallerys[$key] = $gpath.'/'.$filename;
				}
			}
			$data['gallery'] = implode(",",$gallerys);

			$descImgs = isset($goodsObj['item']['descImgs'])?$goodsObj['item']['descImgs']:[];


			$goodsDesc = [];
			foreach ($descImgs as $key => $gimg) {

				$timg = explode(".",$gimg);
	    		$ext = $timg[count($timg)-1];
	    		$ext = ($ext!="")?$ext:"jpg";
	    		$gpath = 'upload/goods/'.date('Y-m');
	    		$filePath = Env::get('root_path').$gpath;
	    		$filename = strtolower(WSTGuid()).".".$ext;
	    		if(!preg_match($preg,$gimg))$gimg = 'https:'.$gimg;
	    		WSTDownCopyFile($gimg, $filePath, $filename,2);
	    		$imgPath = $gpath.'/'.$filename;
				$goodsDesc[] = '<img src="'.'${DOMAIN}'.'/'.$imgPath.'"/>';
			}
			$goodsDesc = WSTHtmlspecialchars(implode("",$goodsDesc));
		
			$data['shopId'] = $shopId;
			$data['goodsType'] = 0;
			$shopPrice = $goodsObj["item"]['price'];
			$data['marketPrice'] = $shopPrice;
			$data['shopPrice'] = $shopPrice;
			$data['warnStock'] = 0;
			$data['goodsStock'] = 0;
			$data['goodsUnit'] = '';
			$data['isSale'] = 0;
			$data['goodsCatIdPath'] = $goodsCatIdPath;
			$data['goodsCatId'] = $goodsCatId;
			$data['shopCatId1'] = 0;
			$data['shopCatId2'] = 0;
			$data['brandId'] = 0;
			if(WSTConf("CONF.isGoodsVerify")==1){
	            $data['goodsStatus'] = 0;
	        }else{
	            $data['goodsStatus'] = 1;
	        }
			$data['saleNum'] = 0;
			$data['saleTime'] = date("Y-m-d H:i:s");
			$data['isSpec'] = (count($goodsObj["item"]['sku'])>0)?1:0;

			$data['createTime'] = date("Y-m-d H:i:s");
			$data['costPrice'] = 0;
			$goodsId = Db::name("goods")->insertGetId($data);

			if($goodsId>0){

				foreach (WSTSysLangs() as $key => $v) {
				   $dataLang = [];
				   $dataLang['langId'] = $v['id'];
				   $dataLang['goodsId'] = $goodsId;
			       $dataLang['goodsName'] = $goodsName;
			       $dataLang['goodsSeoKeywords'] = $goodsName;
			       $dataLang['goodsSeoDesc'] = $goodsName;
                   $dataLang['goodsTips'] = isset($goodsObj['item']['subTitle'])?$goodsObj['item']['subTitle']:'';
                   $dataLang['goodsDesc'] = $goodsDesc;
                   //对图片域名进行处理

				   $dataLang['goodsDesc'] = htmlspecialchars_decode($dataLang['goodsDesc']);
		           $dataLang['goodsDesc'] = WSTRichEditorFilter($dataLang['goodsDesc']);
		           $dataLang['goodsDesc'] = str_replace($resourceDomain.'/upload/','${DOMAIN}/upload/',$dataLang['goodsDesc']);
                   $goodsLangs[] = $dataLang;
                }
                Db::name('goods_langs')->insertAll($goodsLangs);

				//商品图片
				//WSTUseResource(0, $goodsId, $data['goodsImg']);
				//商品相册
				//WSTUseResource(0, $goodsId, $data['gallery']);
				//商品描述图片
				//WSTEditorImageRocord(0, $goodsId, '',$data['goodsDesc']);
				// 视频
				//if(isset($data['goodsVideo']) && $data['goodsVideo']!=''){
					//WSTUseResource(0, $goodsId, $data['goodsVideo']);
				//}
				$now = date("Y-m-d H:i:s");
				//建立商品评分记录
				$gs = [];
				$gs['goodsId'] = $goodsId;
				$gs['shopId'] = $shopId;
				Db::name('goods_scores')->insert($gs);
				//规格
				if($data['goodsType']==0 && $data['isSpec']==1){
					$specProps = isset($goodsObj["item"]['saleProp'])?$goodsObj["item"]['saleProp']:[];
					$skuProps = isset($goodsObj["item"]['skuProps'])?$goodsObj["item"]['skuProps']:[];
					$goodsSku = isset($goodsObj["item"]['sku'])?$goodsObj["item"]['sku']:[];
					$specCatIdMap = [];
					$specItemIdMap = [];
					foreach ($specProps as $key1 => $spec) {
						if($spec=="")continue;
						$where = [];
						$where[] = ['sc.shopId','in',[0,$shopId]];
						$where[] = ["sc.dataFlag",'=',1];
						$where[] = ["scl.catName",'=',$spec];
						$where[] = ["sc.goodsCatId",'in',$goodsCatIds];
						$specCat = Db::name("spec_cats sc")->join('__SPEC_CATS_LANGS__ scl','scl.catId=sc.catId and scl.langId='.WSTcurrLang())
									->where($where)
									->find();

						$catId = 0;
						//创建规格分类
						if(empty($specCat)){
							$pcat = [];
							$pcat["shopId"] = $shopId;
							$pcat["goodsCatId"] = $goodsCatId;
							$pcat["goodsCatPath"] = $goodsCatIdPath;
							//$pcat["catName"] = $spec;
							$pcat["isAllowImg"] = 0;
							$pcat["catSort"] = 99;
							$pcat["isShow"] = 1;
							$pcat["dataFlag"] = 1;
							$pcat["createTime"] = $now;
							$catId = Db::name("spec_cats")->insertGetId($pcat);

							$specCatLangs = [];
							foreach (WSTSysLangs() as $key => $v) {
				                $pcatLang = [];
				                $pcatLang['catId'] = $catId;
				                $pcatLang['langId'] = $v['id'];
				                $pcatLang['catName'] = $spec;
				                $specCatLangs[] = $pcatLang;
				            }
				            Db::name('spec_cats_langs')->insertAll($specCatLangs);

						}else{
							$catId = $specCat['catId'];
						}
						//$specCatIdMap[$spec['pid']] = $catId;
						$catValues = $skuProps[$key1];
						$specItems = [];
						$sitemLangs = [];
						//$isImgSpec = 0;
						//创建规格项
						foreach ($catValues as $key3 => $itemName) {
							if($itemName=="")continue;
							$item = [];
							$item['shopId'] = $shopId;
							$item['catId'] = $catId;
							$item['goodsId'] = $goodsId;
							//$item['itemName'] = $itemName;
							$item['itemImg'] = '';

							$item["dataFlag"] = 1;
							$item["createTime"] = $now;
							$itemId = Db::name("spec_items")->insertGetId($item);

							foreach (WSTSysLangs() as $lkey => $lv) {
			    				$sitemLang = [];
			    				$sitemLang['itemId'] = $itemId;
			    				$sitemLang['langId'] = $lv['id'];
			    				$sitemLang['goodsId'] = $goodsId;
				    			$sitemLang['itemName'] = $itemName;
				    			$sitemLangs[] = $sitemLang;
				    		}

							$specItemIdMap[$key1][$itemName] = $itemId;
						}

						//保存规格多语言文字
		    			if(count($sitemLangs)>0)Db::name('spec_items_langs')->insertAll($sitemLangs);
					}
					$goodsSpecs = [];
					foreach ($goodsSku as $key => $sku) {
						$specIds = [];
						foreach ($sku as $key2 => $skuval) {
							if(is_int($key2)){
								if(isset($specItemIdMap[$key2][$skuval]))$specIds[] = $specItemIdMap[$key2][$skuval];
							}
						}

						$item = [];
						$item['shopId'] = $shopId;
						$item['goodsId'] = $goodsId;
						$item['productNo'] = $productNo.'-'.($key+1);
						$item['specIds'] = implode(":",$specIds);
						$item['marketPrice'] = $sku['price'];
						$item['specPrice'] = $sku['price'];
						$item['specStock'] = 0;
						$item['warnStock'] = 0;
						$item['isDefault'] = ($key==0)?1:0;
						$item['dataFlag'] = 1;
						$goodsSpecs[] = $item;
					}
					if(count($goodsSpecs)>0)Db::name("goods_specs")->insertAll($goodsSpecs);

				}

				//属性
				$groupProps = isset($goodsObj["item"]['attributes']['propGroups'])?$goodsObj["item"]['attributes']['propGroups']:[];
				if(count($groupProps)>0){
					$goodsAttributes = [];

					foreach ($groupProps as $key1 => $props) {
						foreach ($props['atts'] as $key3 => $obj) {
							$attrName = $obj['attName'];
							if($attrName=="")continue;
							$where = [];
							$where[] = ['a.shopId','in',[0,$shopId]];
							$where[] = ["a.dataFlag",'=',1];
							$where[] = ["al.attrName",'=',$attrName];
							$where[] = ["a.goodsCatId",'in',$goodsCatIds];
							$attr = Db::name("attributes a")->join('__ATTRIBUTES_LANGS__ al','al.attrId=a.attrId and al.langId='.WSTcurrLang())
									->where($where)
									->find();
							$attrId = 0;
							if(empty($attr)){
								$attribute = [];
								$attribute["shopId"] = $shopId;
								$attribute["goodsCatId"] = $goodsCatId;
								$attribute["goodsCatPath"] = $goodsCatIdPath;
								$attribute["attrType"] = 0;
								$attribute["attrSort"] = 99;
								$attribute["isShow"] = 1;
								$attribute["dataFlag"] = 1;
								$attribute["createTime"] = $now;
								$attrId = Db::name("attributes")->insertGetId($attribute);

								$attrLangs = [];
					            foreach (WSTSysLangs() as $key => $v) {
					                $attrLang = [];
					                $attrLang['attrId'] = $attrId;
					                $attrLang['langId'] = $v['id'];
					                $attrLang['attrName'] = $attrName;
					                $attrLangs[] = $attrLang;
					            }
					            Db::name('attributes_langs')->insertAll($attrLangs);

							}else{
								$attrId = $attr["attrId"];
							}
							$attrVal = implode(",",$obj['vals']);
							$gattr = [];
							$gattr["shopId"] = $shopId;
							$gattr["goodsId"] = $goodsId;
							$gattr["attrId"] = $attrId;
							$gattr["createTime"] = $now;
							$goodsAttrId = Db::name('goods_attributes')->insertGetId($gattr);

							//保存商品属性多语言
							$attrVal = implode(",",$obj['vals']);
							$attrLangs = [];
							foreach (WSTSysLangs() as $lkey => $lv) {
				    			$attrLang = [];
				    			$attrLang['goodsAttrId'] = $goodsAttrId;
				    			$attrLang['langId'] = $lv['id'];
				    			$attrLang['goodsId'] = $goodsId;
				    			$attrLang['attrVal'] = $attrVal;
				    			$attrLangs[] = $attrLang;
				    		}
				    		Db::name('goods_attributes_langs')->insertAll($attrLangs);

						}
					}

				}

			}

			Db::commit();
			return WSTReturn(lang("collection_success"), 1,$goodsId);
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang("collection_fail"),-1);
        }
	}

	public function httpLoad($url){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_FAILONERROR, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_ENCODING, "gzip");
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
		$response = curl_exec($curl);
		$err = curl_error($curl);

		//curl_close($curl);

		if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == '200') {
		    $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		    $header = substr($response, 0, $headerSize);
		    $response = substr($response, $headerSize);
		    file_put_contents("runtime/log.txt",$response, FILE_APPEND);
		}

		$data = [];
		if ($err) {
			$data["wstStatus"] = -1;
			$data["err"] = $err;
			return json_encode($data);
		} else {
			return $response;
		}
	}

	/**
     * 上传商品数据
     */
    public function importGoods($data){
        require Env::get('root_path') . 'extend/phpexcel/PHPExcel/IOFactory.php';
        $objReader = \PHPExcel_IOFactory::load(WSTRootPath().json_decode($data)->route.json_decode($data)->name);
        $objReader->setActiveSheetIndex(0);
        $sheet = $objReader->getActiveSheet();
        $rows = $sheet->getHighestRow();
        $cells = $sheet->getHighestColumn();
        //数据集合
        $readData = [];
        $shopId = (int)session('WST_USER.shopId');
        $importNum = 0;
        $goodsCatMap = []; //记录最后一级商品分类
        $shopCatMap = [];//记录店铺分类

        //获取商家商城分类
        $applyCatIds = model('common/GoodsCats')->getShopApplyGoodsCats($shopId);
        //循环读取每个单元格的数据
        $goodsList = [];
        for ($row = 3; $row <= $rows; $row++){//行数是以第3行开始

            $goods = [];
            $goodsUrl = trim($sheet->getCell("A".$row)->getValue());;
            $goods['goodsUrl'] = $goodsUrl;
            if($goods['goodsUrl']==''){
            	return json_encode(['status'=>1,'goodsList'=>$goodsList]);
            	//break;//如果某一行第一列为空则停止导入
            }
            $preg = "/^http(s)?:\\/\\/.+/";
			if(!preg_match($preg,$goodsUrl)){
				$goods['statusMsg'] = lang("collection_url_error");
				$goods['status'] = -1;
			}
            if(!(strpos($goodsUrl,"tmall.com") || strpos($goodsUrl,"taobao.com") || strpos($goodsUrl,"jd.com"))){
            	$goods['statusMsg'] = lang("collection_not_support_url");
            	$goods['status'] = -1;
            }
            //查询商城分类
            $goodsCat = trim($sheet->getCell("B".$row)->getValue());
            if(!empty($goodsCat)){
                //先判断集合是否存在，不存在的时候才查数据库
                if(isset($goodsCatMap[$goodsCat])){
                    $goods['goodsCatId'] = $goodsCatMap[$goodsCat];
                }else{
                	$where = [];
                	$where[] = ['dataFlag','=',1];
                	$where[] = ['catName','like','%'.$goodsCat.'%'];
                    $goodsCats = Db::name('goods_cats')->where($where)->field('catId,catName')->select();
                    //判断是否最后一级，如果不是的话，则取最后一级（如果有多个的话，只一个)

                    if(count($goodsCats)>0){
						$flag = true;
                        foreach($goodsCats as $key => $cat){
							// 判断商城分类是否为最后一级分类
							$catName = $cat['catName'];
							$langCatNams = explode("{L}",$catName);
							if(in_array($goodsCat,$langCatNams)){
								$lastCatCount = Db::name('goods_cats')->where(['parentId'=>$cat['catId']])->count();
								if($lastCatCount>0)continue;
								$goodsCats = $cat;
								$flag = false;
								break;
							}

                        }
                        if($flag){
                            //$specGoodsErrMsgArr[] = ['msg'=>"商品【".$goods['goodsName']."】导入失败，商城分类非最后一级分类"];
                            $goods['statusMsg'] = lang("collection_goods_cat_not_last");
            				$goods['status'] = -1;
                        }
                    }else{
                        if(count($goodsCats)==1)$goodsCats = $goodsCats[0];
                    }

                    if(!empty($goodsCats['catId']) && in_array($goodsCats['catId'],$applyCatIds)){
                        //$goodsCats = model('common/GoodsCats')->getParentIs($goodsCats['catId']);
                        $goods['goodsCatId'] = $goodsCats['catId'];
                        //放入集合
                        $goodsCatMap[$goodsCat] = $goodsCats['catId'];
                    }else{
                    	$goods['statusMsg'] = lang("collection_not_shop_apply_cat");
            			$goods['status'] = -1;
                    }
                }
            }
            if(!isset($goods['status'])){
            	$goods['statusMsg'] = lang("collection_can_collect");
         		$goods['status'] = 1;
            }

            $goodsList[] = $goods;
            $importNum++;
        }
        return json_encode(['status'=>1,'goodsList'=>$goodsList]);

    }


}
