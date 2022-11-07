<?php
namespace wstmart\supplier\model;
use wstmart\common\validate\SupplierGoodsConsult as Validate;
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
 * 商品咨询类
 */
class SupplierGoodsConsult extends Base{
    /**
     * 根据供货商id获取商品咨询
     */
    public function pageQuery($sId=0){
        // 查询条件
        $type = (int)input('consultType');
        $consultKey = (int)input('consultKey');
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $where = [];
        $where[] = ['g.supplierId',"=",$supplierId];
        if($type>0){$where[] = ['consultType',"=",$type];}
        if($consultKey!=0){$where[] = ['consultContent','like',"%$consultKey%"];}
        $rs = $this->alias('gc')
                   ->join('__USERS__ u','u.userId=gc.userId','left')
                   ->join('__SUPPLIER_GOODS__ g','g.goodsId=gc.goodsId','inner')
                   ->join('__SUPPLIER_GOODS_LANGS__ sgl','sgl.goodsId=g.goodsId and sgl.langId='.WSTCurrLang())
                   ->field('gc.*,u.loginName,u.userPhoto,sgl.goodsName,g.goodsImg')
                   ->where($where)
                   ->order('gc.replyTime asc,gc.createTime desc')
                   ->paginate(input('pagesize/d',5))->toArray();
        if(!empty($rs['data'])){
            foreach($rs['data'] as $k=>&$v){
                // 解义
                $v['consultContent'] = htmlspecialchars_decode($v['consultContent']);
                $v['reply'] = htmlspecialchars_decode($v['reply']);
                // 处理匿名
                if($v['userId']>0){
                    // 替换中间两个字符
                    $start = floor((strlen($v['loginName'])/2))-1;
                    $v['loginName'] = mb_convert_encoding(substr_replace($v['loginName'],'**',$start,2),'UTF-8');
                    $v['userPhoto'] = WSTUserPhoto($v['userPhoto'], true);
                }
            }
        }
        return $rs;
    }

    /**
    * 根据商品id获取商品咨询
    */
    public function listQuery(){
        $goodsId = (int)input('goodsId');
        $type = (int)input('type');
        $consultKey = input('consultKey');
        $where = [];
        $where[] = ['gc.dataFlag','=',1];
        $where[] = ['gc.isShow','=',1];
        $where[] = ['gc.goodsId','=',$goodsId];
        // 筛选类别
        if($type>0)$where[] = ['gc.consultType','=',$type];
        // 关键字搜索
        if($consultKey!=''){$where[] = ['gc.consultContent','like',"%$consultKey%"];}
        $rs = $this->alias('gc')
                   ->join('__USERS__ u','u.userId=gc.userId','left')
                   ->field('gc.*,u.loginName')
                   ->where($where)
                   ->order('gc.createTime desc')
                   ->paginate(input('pagesize/d',5))->toArray();
        if(!empty($rs['data'])){
            foreach($rs['data'] as $k=>&$v){
                // 解义
                $v['consultContent'] = htmlspecialchars_decode($v['consultContent']);
                // 处理匿名
                if($v['userId']>0){
                    // 替换中间字符
                    $v['loginName'] = WSTAnonymous($v['loginName']);
                }
            }
        }
        return WSTReturn('', 1,$rs);
    }
    /**
     * 根据商品id获取一条最新商品咨询
     */
    public function firstQuery($id){
        $where = [];
        $where['gc.dataFlag'] = 1;
        $where['gc.isShow'] = 1;
        $where['gc.goodsId'] = $id;
        $rs = $this->alias('gc')->join('__USERS__ u','u.userId=gc.userId','left')
        ->where($where)->field('gc.*,u.loginName')->order('gc.createTime desc')->find();
        if(!empty($rs)){
            // 解义
            $rs['consultContent'] = htmlspecialchars_decode($rs['consultContent']);
            // 处理匿名
            if($rs['userId']>0){
                $rs['loginName'] = WSTAnonymous($rs['loginName']);
            }
        }
        return $rs;
    }

    /**
     * 编辑
     */
    public function edit(){
        $Id = input('post.id/a',0);
        $data = input('post.');
        unset($data['id']);
        $result = $this->where([['id','in',$Id]])->update($data);
        if(false !== $result){
            return WSTReturn(lang("op_ok"), 1);
        }else{
            return WSTReturn($this->getError(),-1);
        }
    }

    /**
    * 商家回复
    */
    public function reply(){
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $data = input('param.');
        // 检测是否含有系统禁用关键字
        if(!WSTCheckFilterWords($data['reply'],WSTConf("CONF.limitWords"))){
                return WSTReturn(lang("reply_illegal_char"));
        }
        // 转义,防止xss攻击
        $data['reply'] = htmlspecialchars($data['reply']);
        $data['replyTime'] = date('Y-m-d H:i:s');
        // 检测是否已经回复过了
        $hasReply = $this->where(['id'=>(int)$data['id']])->value('reply');
        if($hasReply!='')return WSTReturn(lang("goods_consult_need_refresh"));
        // 检测是否属于该商家
        $owner = $this->alias('gc')
                      ->join('__SUPPLIER_GOODS__ g','g.goodsId=gc.goodsId','inner')
                      ->where([['gc.id','=',$data['id']],['g.supplierId','=',$supplierId]])
                      ->find();
        if(empty($owner))return WSTReturn(lang("no_reply_privilege"));
        $validate = new Validate;
        if (!$validate->scene('edit')->check($data)) {
            return WSTReturn($validate->getError());
        }else{
            $rs = $this->allowField(true)->update($data);
        }
        if($rs===false){
            return WSTReturn($this->getError(),-1);
        }
        return WSTReturn(lang("op_ok"), 1);
    }
    /**
     * 设置显示隐藏
     */
    public function isShow(){
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $id = (int)input('id');
        $isShow = (int)input('isShow');
        $rs = $this->where(['id'=>$id])->setField(['isShow'=>$isShow]);
        if($rs!==false)return WSTReturn(lang("op_ok"), 1);
        return WSTReturn(lang("op_err"));
    }


    /**
    * 根据用户id获取商品咨询
    */
    public function myConsultByPage(){
        $userId = (int)session('WST_SUPPLIER.userId');
        $where = [];
        $where['gc.userId'] = $userId;
        $where['gc.dataFlag'] = 1;
        $rs = $this->alias('gc')
                   ->join('__SUPPLIER_GOODS__ g','g.goodsId=gc.goodsId')
                   ->join('__SUPPLIER_GOODS_LANGS__ sgl','sgl.goodsId=g.goodsId and sgl.langId='.WSTCurrLang())
                   ->field('gc.*,sgl.goodsName,g.goodsImg')
                   ->where($where)
                   ->order('gc.createTime desc')
                   ->paginate(input('pagesize/d'))->toArray();
        if(!empty($rs['data'])){
            foreach($rs['data'] as $k=>&$v){
                // 解义
                $v['consultContent'] = htmlspecialchars_decode($v['consultContent']);
            }
        }
        return WSTReturn('', 1,$rs);
    }

    /**
     * 添加
     */
    public function add($uId=0){
        $userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
        $data = input('param.');
        $data['userId'] = $userId;
        // 检测是否含有系统禁用关键字
        if(!WSTCheckFilterWords($data['consultContent'],WSTConf("CONF.limitWords"))){
            return WSTReturn(lang("goods_consult_illegal_char"));
        }
        if(empty(WSTConf("CONF.isConsult"))){
            $data['isShow'] = 0;
        };
        // 转义,防止xss攻击
        $data['consultContent'] = htmlspecialchars($data['consultContent']);
        $validate = new Validate;
        if (!$validate->scene('add')->check($data)) {
            return WSTReturn($validate->getError());
        }else{
            $rs = $this->allowField(true)->save($data);
        }
        if($rs===false){
            return WSTReturn($this->getError(),-1);
        }
        return WSTReturn(lang("op_ok"), 1);
    }
}
