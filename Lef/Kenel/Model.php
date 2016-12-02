<?php
namespace Kenel;
use Di;
/**
 * ============================================================================
 * 数据库模型类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Model{
	//数据库实例
	protected $db;
	//语法分析器名称
	private $grammer;
	//SQL条件选项
	private $options=array();
	//生成的SQL语句
	private $sql;
	//表名
	protected $table;
	//自动填充<insert,update>
	protected $auto=array();
	/**
	 * 初始化
	 */
	function __construct($db='db'){
		$this->setDb($db);
	}
	/**
	 * 更换数据链接
	 * @param \kenel\db\databaseAbstract|string $db
	 */
	public function setDb($db){
		$this->db=is_string($db) ? Di::get($db) : $db;
		$grammer_name=$this->db->getGrammerName();
    	$this->grammer=new $grammer_name($this->db);
		return $this;
	}
	/**
	 * 自动调用
	 * @param string $method
	 * @param array $args
	 * @throws \BadMethodCallException
	 * @return \kenel\model
	 */
	function __call($method,$args){
		$method=strtolower($method);
		switch($method){
			case 'table':
			case 'where':
			case 'data':
			case 'having':
				$this->options[$method] = $args[0];
				break;
			case 'limit':
			case 'order':
			case 'group':	
			case 'field':
				$this->options[$method]=implode(',',$args);
				break;
			case 'join':
				if(is_array($args[0])){
					$this->options['join']=$args[0];
				}else{
					$this->options['join'][]=$args[0];
				}
				break;
			default:
				throw new \BadMethodCallException('Called undefined functions:'.$method.' !',500);

		}
        return $this;
	}
	/**
	 * 单行查找,返回一维数组，如果单个字段，返回字段的值
	 * @param array|string $where
	 * 字符串表示要查找的字段，数组表示要查询的条件
	 */
	public function find($where=null){
		$this->sql=$this->getSelectSql($where);
		$binds=$this->grammer->getBinds();
		$this->clear();
		return $this->db->query($this->sql,$binds,false);
	}

	/**
	 * 数据库查询，返回二维数组
	 * @param string|array $where
	 * 字符串表示要查找的字段，数组表示要查询的条件
	 * @must field
	 */
	public function select($where=null){
		$this->sql=$this->getSelectSql($where);
		$binds=$this->grammer->getBinds();
		$this->clear();
		return $this->db->query($this->sql,$binds);
	}
	/**
	 * 插入数据
	 * @param array $data
	 * 增加的数据，以字段=》内容排列
	 * @return bool
	 */
	public function insert($data=array()){
		$this->sql=$this->getInsertSql($data);
		$binds=$this->grammer->getBinds();
		$this->clear();
		return $this->db->exec($this->sql,$binds);
	}
	/**
	 * 插入数据后，获取最后插入的ID
	 * @param array $data
	 * @return int|boolean
	 */
	public function insertGetId($data=array()){
		if($this->insert($data)){
			return $this->db->getLastId();
		}
		return false;
	}
	/**
	 * 更新
	 * @param array $data
	 * 以键值=>更新的值 为数组
	 * @must table data
	 */
	public function update($data=array()){
		$this->sql=$this->getUpdateSql($data);
		$binds=$this->grammer->getBinds();
		$this->clear();
		return $this->db->exec($this->sql,$binds);
	}
	/**
	 * 删除
	 * @must table
	 */
	public function delete($where=null){
		$this->sql=$this->getDeleteSql($where);
		$binds=$this->grammer->getBinds();
		$this->clear();
		return $this->db->exec($this->sql,$binds);
    }
    /**
     * 获取条数
     * @param unknown_type $sql
     * 如果为字符串，则表示SQL语句，否则补充查询条件
     */
    public function count($where=null){
    	$this->field('count(*) as _count_row');
		$this->sql=$this->getSelectSql($where);
		$binds=$this->grammer->getBinds();
		$this->clear();
		if($rs=$this->db->query($this->sql,$binds,false)){
			return $rs['_count_row'];
		}else{
			return false;
		}
    }
    /**
     * 获取影响的行数
     */
    public function getAffectedRows(){
    	return $this->db->getAffectedRows();
    }
    /**
     * 开始事物
     */
    public function beginTransaction(){
    	$this->db->beginTransaction();
    }
    /**
     * 提交事物
     */
    public function commit(){
    	$this->db->commit();
    }
    /**
     * 回滚事物
     */
    public function rollBack(){
    	$this->db->rollBack();
    }
    /**
     * 获取查询的SQL
     */
    public function getSelectSql($where=null){
    	$where && $this->where($where);
    	$this->initGrammer();
    	return $this->grammer->getSelectSql();
    }
    /**
     * 获取插入的SQL
     */
    public function getInsertSql($data=array()){
      	!empty($data) && $this->data($data);
		isset($this->auto['insert']) && $this->options['data']=array_merge($this->options['data'],$this->auto['insert']);
		if(empty($this->options['data'])){
			trigger_error('Insert data is empty');
			return false;
		}
		$this->initGrammer();
    	return $this->grammer->getInsertSql();
    }
    /**
     * 获取更新的SQL
     */
    public function getUpdateSql($data=array()){
      	!empty($data) && $this->data($data);
		isset($this->auto['update']) && $this->options['data']=array_merge($this->options['data'],$this->auto['insert']);
		if(empty($this->options['data'])){
			trigger_error('Update data is empty');
			return false;
		}
		$this->initGrammer();
    	return $this->grammer->getUpdateSql();
    }
    /**
     * 获取删除的SQL
     */
    public function getDeleteSql($where=null){
    	$where && $this->where($where);
		$this->initGrammer();
    	return $this->grammer->getDeleteSql();
    }
    /**
     * 返回最后执行的SQL
     * @return string
     */
	public function getLastSql(){
		return $this->sql;
	}
	/**
     * 初始化语法解析
     */
	private function initGrammer(){
		if(!isset($this->options['table'])){
			$this->options['table']=$this->getTableName();
		}
		$this->grammer->clearBinds();
		$this->grammer->setOption($this->options);
	}
    /**
     * 获取表名
     * @return string
     */
    private function getTableName(){
		if(isset($this->options['table'])){
			return $this->options['table'];
		}
		if($this->table){
			return $this->table;
		}
		$class=get_called_class();
		$class=substr($class,strrpos($class,'\\')+1,-5);
		return strtolower(ltrim(preg_replace("/[A-Z]/", "_\\0", $class),'_'));
    }
    /*
     * 重置条件语句
     */
	private function clear(){
		$this->options=array();
	}
}
?>