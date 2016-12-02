<?php
namespace Kenel\Db;
use Di;
/**
 * ============================================================================
 * 数据库抽象类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
abstract class DatabaseAbstract{
	protected $read_link;
	protected $write_link;
	public function __construct(array $configs){
		if(isset($configs['read']) || isset($configs['write'])){
			$common_config=$configs;
			unset($common_config['read'],$common_config['write']);
			if(isset($configs['read'])){
				if(isset($configs['read'][0]) && count($configs['read'])>1){
					$configs['read']=$configs['read'][mt_rand(1,count($configs['read']))-1];
				}
				$read_config=array_merge($common_config,$configs['read']);
				$this->read_link=$this->connect($read_config);
			}
			if(isset($configs['write'])){
				$write_config=array_merge($common_config,$configs['write']);
				$this->write_link=$this->connect($write_config);
			}
		}else{
			$this->read_link=$this->write_link=$this->connect($configs);
		}
	}
	/**
	 * 连接数据库
	 * @param array $configs 连接配置，各个驱动参数不一样
	 */
	abstract protected function connect($configs);
	/**
	 * 执行查询语句
	 * @param string $sql SQL语句
	 * @param boolean $get_one 是否只返回一条记录
	 */
	abstract function query($sql,$binds=[],$return_one=false);
	/**
	 * 执行写操作语句
	 * @param string $sql
	 */
	abstract function exec($sql,$binds=[]);
	/**
	 * 获取最后执行的ID
	 */
	abstract function getLastId();
	/**
	 * 获取修改影响的行数
	 */
	abstract function getAffectedRows();
	/**
	 * 获取查询行数
	 */
	abstract function getNumRows();
	/**
	 * 数据验证
	 * @param string $str
	 * @param int $type
	 */
	abstract function quote($str,$type=null);
	/**
	 * 获取表的字段
	 * @param string $table
	 */
	abstract function getFields($table);
	/**
	 * 获取库的表名
	 */
	abstract function getTables();
	/**
	 * 开始事物
	 */
	abstract function beginTransaction();
	/**
	 * 提交事物
	 */
	abstract function commit();
	/**
	 * 回滚事物
	 */
	abstract function rollBack();
	/**
	 * 获取语法分析器类名
	 * @return string
	 */
	public function getGrammerName(){
		$db_class_name=get_called_class();
		return $db_class_name.'Grammer';
	}
	/**
	 * 执行SQL前
	 * @param  [type] $sql   [description]
	 * @param  [type] $binds [description]
	 * @return [type]        [description]
	 */
	protected function beforeSql($sql,$binds){
		\Kenel\Hook::trigger('before_sql',array('sql'=>$sql,'binds'=>$binds));
	}
	/**
	 * 执行SQL后
	 * @param  [type] $sql   [description]
	 * @param  [type] $binds [description]
	 * @return [type]        [description]
	 */
	protected function afterSql($sql,$binds){
		\Kenel\Hook::trigger('after_sql',array('sql'=>$sql,'binds'=>$binds));
	}
}
?>