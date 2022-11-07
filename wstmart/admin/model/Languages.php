<?php
namespace wstmart\admin\model;
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
 * 语言业务处理
 */
class Languages extends Base{
	protected $pk = 'id';

	/**
	 * 列表
	 */
	public function listQuery(){
		return $this->where(['status'=>1])->order('sort asc')->select();
	}
    public function listAllQuery(){
        return $this->order('sort asc')->select();
    }
	/**
	 * 新增
	 */
	public function changeStatus(){
		$type = ((int)input('type')==1)?1:0;
        $stype = ($type==1)?0:1;
		$id = (int)input('id');
		Db::startTrans();
		try{
            $adminLangs = Db::name('sys_configs')->where(['fieldCode'=>'defaultSysLang'])->value('fieldValue');
			$has = Db::name('languages')->where(['id'=>$id])->count();
            if($has==0)return WSTReturn(lang('lang_error_invalid_language'));
            if($type==0 && ($adminLangs==$id))return WSTReturn(lang('lang_error_not_delete'));
            $rs = $this->where(['id'=>$id,'status'=>$stype])->update(['status'=>$type]);
            if($rs !==false){
                if($type==1){
                    $this->enableLang($id,$adminLangs);
                }else{
                    $this->disableLang($id);
                }
                WSTClearAllCache();
	            Db::commit();
	            return WSTReturn(lang('op_ok'),1);
           }
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('op_err'),-1);
        }

	}
    public function disableLang($id){
        $tables = $this->getLangTables();
        for($i=0;$i<count($tables);$i++){
            $table = $tables[$i];
            if($this->isTableExist($table))Db::table($table)->where(['langId'=>$id])->delete();
        }
    }
    public function enableLang($id,$adminLangs){
        $tables = $this->getLangTables();
        for($i=0;$i<count($tables);$i++){
            $table = $tables[$i];
            if($this->isTableExist($table)){
                $acl = Db::table($table)->where(['langId'=>$adminLangs])->select();
                $datas = [];
                foreach ($acl as $key => $v) {
                    $v['langId'] = $id;
                    $datas[] = $v;
                }
                Db::table($table)->insertAll($datas);
            }
        }
    }

    public function isTableExist($tableName){
        $isTable = Db::query('SHOW TABLES LIKE '."'".$tableName."'");
        if($isTable){
            return true;// 表存在
        }else{
            return false;// 表不存在
        }
    }

    public function getLangTables(){
        $database = config('database.database');
        $sql = "SELECT TABLE_NAME FROM information_schema.`TABLES` WHERE TABLE_SCHEMA = '".$database."' AND TABLE_NAME LIKE '%langs'";
        $tables = Db::query($sql);
        $tables = array_column($tables,"TABLE_NAME");
        return $tables?$tables:[];
    }
}
