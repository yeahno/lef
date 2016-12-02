<?php 
namespace Kenel\Db;
/**
 * ============================================================================
 * 数据库语法抽象类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
abstract class GrammerAbstract{
	protected $options;
	protected $db;
	protected $binds=array();
	function __construct($db){
		$this->db=$db;
	}
	/**
	 * 设置选项
	 * @param [type] $options [description]
	 */
	function setOption($options){
		$this->options=$options;
	}
	/**
	 * 获取查询sql
	 * @return [type] [description]
	 */
	abstract function getSelectSql();
	/**
	 * 获取插入sql
	 * @return [type] [description]
	 */
	abstract function getInsertSql();
	/**
	 * 获取更新sql
	 * @return [type] [description]
	 */
	abstract function getUpdateSql();
	/**
	 * 获取删除sql
	 * @return [type] [description]
	 */
	abstract function getDeleteSql();
	/**
	 * 获取绑定数据
	 * @return [type] [description]
	 */
	public function getBinds(){
		return $this->binds;
	}
	/**
	 * 清空绑定数据
	 */
	public function clearBinds(){
		$this->binds=array();
	}
	/**
	 * 过滤录入数据库的数据
	 * @param mixed $value
	 */
	protected function filter($value){
		if(is_array($value)){
			$value=\Di::get('filter')->sanitize($value[0],$value[1]);
		}else{
			$value=\Di::get('filter')->sanitizeString($value);
		}
		return $this->db->quote($value);
	}
}
?>