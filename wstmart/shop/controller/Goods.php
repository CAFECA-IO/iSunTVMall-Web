<?php

namespace wstmart\shop\controller;

use wstmart\shop\model\Goods as M;
use wstmart\shop\model\ShopMemberGroups;

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
 * 商品控制器
 */
class Goods extends Base
{
    protected $beforeActionList = ['checkAuth'];
    /**
     * 批量删除商品
     */
    public function batchDel()
    {
        $m = new M();
        return $m->batchDel();
    }
    /**
     * 修改商品库存/价格
     */
    public function editGoodsBase()
    {
        $m = new M();
        return $m->editGoodsBase();
    }

    /**
     * 修改商品状态
     */
    public function changSaleStatus()
    {
        $m = new M();
        return $m->changSaleStatus();
    }
    /**
     * 批量修改商品状态 新品/精品/热销/推荐
     */
    public function changeGoodsStatus()
    {
        $m = new M();
        return $m->changeGoodsStatus();
    }
    /**
     *   批量上(下)架
     */
    public function changeSale()
    {
        $m = new M();
        return $m->changeSale();
    }
    /**
     *  上架商品列表
     */
    public function sale()
    {
        $this->assign("p", (int)input("p"));
        return $this->fetch('goods/list_sale');
    }
    /**
     * 获取上架商品列表
     */
    public function saleByPage()
    {
        $m = new M();
        $rs = $m->saleByPage();
        $rs['status'] = 1;
        return WSTGrid($rs);
    }
    /**
     * 仓库中商品
     */
    public function store()
    {
        $this->assign("p", (int)input("p"));
        return $this->fetch('goods/list_store');
    }
    /**
     * 审核中的商品
     */
    public function audit()
    {
        $this->assign("p", (int)input("p"));
        return $this->fetch('goods/list_audit');
    }
    /**
     * 获取审核中的商品
     */
    public function auditByPage()
    {
        $m = new M();
        $rs = $m->auditByPage();
        $rs['status'] = 1;
        return WSTGrid($rs);
    }
    /**
     * 获取仓库中的商品
     */
    public function storeByPage()
    {
        $m = new M();
        $rs = $m->storeByPage();
        $rs['status'] = 1;
        return WSTGrid($rs);
    }
    /**
     * 违规商品
     */
    public function illegal()
    {
        $this->assign("p", (int)input("p"));
        return $this->fetch('goods/list_illegal');
    }
    /**
     * 获取违规的商品
     */
    public function illegalByPage()
    {
        $m = new M();
        $rs = $m->illegalByPage();
        $rs['status'] = 1;
        return WSTGrid($rs);
    }

    /**
     * 跳去新增页面
     */
    public function add()
    {
        $m = new M();
        $smg = new ShopMemberGroups();
        $shopMemberGroups = $smg->listQuery();
        $this->assign("p", 1);
        $object = $m->getEModel('goods');
        foreach (WSTSysLangs() as $key => $lv) {
            $object['langParams'][$lv['id']]['goodsName'] = '';
            $object['langParams'][$lv['id']]['goodsTips'] = '';
            $object['langParams'][$lv['id']]['goodsDesc'] = '';
            $object['langParams'][$lv['id']]['goodsSeoKeywords'] = '';
            $object['langParams'][$lv['id']]['goodsSeoDesc'] = '';
        }
        $object['goodsSn'] = WSTGoodsNo();
        $object['productNo'] = WSTGoodsNo();
        $src = input("src") ? input("src") : 'add';
        $data = ['object' => $object, 'src' => $src];
        $shopExpressList = model("common/express")->shopExpressList();
        $this->assign("shopExpressList", $shopExpressList);
        $this->assign("shopMemberGroups", $shopMemberGroups);
        return $this->fetch('goods/edit', $data);
    }

    /**
     * 新增商品
     */
    public function toAdd()
    {
        $m = new M();
        return $m->add();
    }

    /**
     * 跳去编辑页面
     */
    public function edit()
    {
        $m = new M();
        $smg = new ShopMemberGroups();
        $shopMemberGroups = $smg->listQuery();
        $object = $m->getById(input('get.id'));
        $this->assign("p", (int)input("p"));
        $data = ['object' => $object, 'src' => input('src')];
        $shopExpressList = model("common/express")->shopExpressList();
        $this->assign("shopExpressList", $shopExpressList);
        $this->assign("shopMemberGroups", $shopMemberGroups);
        return $this->fetch('goods/edit', $data);
    }

    /**
     * 编辑商品
     */
    public function toEdit()
    {
        $m = new M();
        return $m->edit();
    }
    /**
     * 删除商品
     */
    public function del()
    {
        $m = new M();
        return $m->del();
    }
    /**
     * 获取商品规格属性
     */
    public function getSpecAttrs()
    {
        $m = new M();
        return $m->getSpecAttrs();
    }

    /**
     * 预警库存
     */
    public function stockwarnbypage()
    {
        $this->assign("p", (int)input("p"));
        return $this->fetch("stockwarn/list");
    }
    /**
     * 获取预警库存列表
     */
    public function stockByPage()
    {
        $m = new M();
        $rs = $m->stockByPage();
        $rs['status'] = 1;
        return WSTGrid($rs);
    }
    /**
     * 修改预警库存
     */
    public function editwarnStock()
    {
        $m = new M();
        return $m->editwarnStock();
    }

    /**
     * 检测要导出二维码的商品
     */
    public function checkExportGoods()
    {
        $shopId = (int)session('WST_USER.shopId');
        $m = new M();
        $data = [];
        $data['glist'] = $m->checkExportGoods();
        $data['gdir'] = 'upload/shares/goods/' . date("Y-m") . '/' . date("Ymdhis") . $shopId . mt_rand(1000, 9999);
        return WSTReturn("", 1, $data);
    }

    /**
     * 生成商品二维码
     */
    public function createGoodsQrcode()
    {
        $shopId = (int)session('WST_USER.shopId');
        $m = new M();
        $vtype = (int)input("vtype", 0);
        $goodsId = (int)input("goodsId", 0);
        if ($goodsId > 0) {
            $flag = $m->checkGoodsIsExit($shopId, $goodsId);
            if (!$flag) return WSTReturn(lang('op_err'));
            $subDir =  input("dir");
            WSTCreateDir(WSTRootPath() . '/' . $subDir);
            $today = date("Ymd");
            $fname = 'goods_qr_' . ($vtype == 1 ? 'mo' : 'we') . '_' . $goodsId . '.png';
            $outImg = $subDir . '/' . $fname;
            $shareImg = WSTRootPath() . '/' . $outImg;

            if ($vtype == 2) {
                $weapp = new \weapp\WSTWeapp(WSTConf('CONF.weAppId'), WSTConf('CONF.weAppKey'));
                $tokenId = $weapp->getToken();

                $parm['scene'] = $goodsId;
                $parm['page'] = "pages/goods-detail/goods-detail";
                $parm['width'] = 200;
                $parm['is_hyaline'] = true;
                $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $tokenId;
                $qrdata = $weapp->http($url, json_encode($parm));
                $qr_code = WSTRootPath() . '/' . $subDir . '/' . $fname; // 小程序码
                file_put_contents($qr_code, $qrdata);
            } else {
                $qr_url = url('mobile/goods/detail', array('goodsId' => $goodsId), true, true); //二维码内容
                //生成二维码图片
                $qr_code = WSTCreateQrcode($qr_url, '', 'goods', 3600, 2);
                $qr_code = WSTRootPath() . '/' . $qr_code;
            }

            $rs = model("common/Goods")->createPoster(0, $qr_code, $outImg);
            return $rs;
        } else {
            return WSTReturn(lang('op_err'));
        }
    }

    /**
     * 打包下载商品二维码
     */
    public function packageDownQrcode()
    {
        $m = new M();
        // 需要压缩的目录名
        $dirpath =  input("dir");
        $dirs = explode("/", $dirpath);
        // 创建压缩目录的名称
        $zipFile = input("dir") . ".zip";
        $subDir = $dirpath . "/";
        // 创建新的zip类
        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE) === TRUE) {
            // 将路径存储到变量中
            $dir = opendir($subDir);
            while ($file = readdir($dir)) {
                if (is_file($subDir . $file)) {
                    $zip->addFile($subDir . $file, $file);
                }
            }
            $zip->close();
        }
        //清除文件
        WSTDelDir($dirpath);
        if (count(scandir($dirpath)) == 2) {
            rmdir($dirpath);
        }
        return WSTReturn("", 1, $zipFile);
    }
}
