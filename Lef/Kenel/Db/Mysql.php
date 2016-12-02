<?php
namespace Kenel\Db;
use Kenel\Db\DatabaseAbstract;
/**
 * ============================================================================
 * 基于PDO的数据库基类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
final class Mysql extends DatabaseAbstract{
	private $PdoStatement;
	private $affected_rows;
	private $prepare_sql;
	private $sth;
	/**
	 * $configs=array(
	 * 	'read'=>array(
	 * 		'host'=>'127.0.0.1'
	 * 	),
	 * 	'write'=>array(
	 * 		'host'=>'127.0.0.2'
	 * 	),
	 * 	'username'=>'root',
	 * 	'password'=>'',
	 * 	'dbname'=>'test',
	 *  'charset'=>'utf8'
	 * );
	 * @see \kenel\db\databaseAbstract::connect()
	 */
	protected function connect($configs){
		if(!isset($configs['dsn'])){
			$configs['port'] = isset($configs['port']) ?: 3306;
			$configs['dsn']='mysql:host='.$configs['host'].';port='.$configs['port'].';dbname='.$configs['dbname'];
		}
		$configs['params']=isset($configs['params']) ? : array();
		$configs['charset']=isset($config['charset']) ? : 'utf8';
		$pdo=new \PDO( $configs['dsn'], $configs['username'], $configs['password'],$configs['params']);
		$pdo->exec('SET NAMES '.$configs['charset']);
		return $pdo;
	}
	/**
	 * 执行查询语句
	 * @see \kenel\db\databaseAbstract::query()
	 */
	public function query($sql,$binds=[],$return_more=true){
		$this->beforeSql($sql,$binds);
		$rs=false;
		try{
			$this->PdoStatement=$this->read_link->prepare($sql);
			$rs=$this->PdoStatement->execute($binds);
			$this->afterSql($sql,$binds);
			if(!$rs){
				throw new \PDOException("sql query failed: ".$sql);
			}
		}catch(\PDOException $e){
			throw new \RuntimeException($e,500);
		}
		return $return_more ? $this->PdoStatement->fetchAll(\PDO::FETCH_ASSOC) : $this->PdoStatement->fetch(\PDO::FETCH_ASSOC);
	}
	/**
	 * 执行修改语句
	 * @see \kenel\db\databaseAbstract::exec()
	 */
	public function exec($sql,$binds=[]){
		$this->beforeSql($sql,$binds);
		$rs=false;
		try{
			$this->PdoStatement=$this->read_link->prepare($sql);
			$rs=$this->PdoStatement->execute($binds);
			$this->afterSql($sql,$binds);
			if(!$rs){
				throw new \PDOException("sql exec failed: ".$sql);
			}
		}catch(\PDOException $e){
			throw new \RuntimeException($e,500);
		}
		return $rs;
	}
    /**
     * 获取最后插入的ID
     * @see \kenel\db\databaseAbstract::getLastId()
     */
	public function getLastId(){
		return $this->write_link->lastInsertId();
	}
	/**
	 * @see \kenel\db\databaseAbstract::getAffectedRows()
	 */
	public function getAffectedRows(){
		return $this->PdoStatement->rowCount();
	}
	/**
	 * 获取查询或修改影响的行数
	 * @see \kenel\db\databaseAbstract::getNumRows()
	 */
	public function getNumRows(){
		return $this->PdoStatement->rowCount();
	}
	/**
	 * @see \kenel\db\databaseAbstract::quote()
	 */
	public function quote($str,$type=null){
		$link=$this->write_link ? $this->write_link : $this->read_link;
		return $link->quote($str,$type);
	}
	/**
	 * @see \kenel\db\databaseAbstract::getFields()
	 */
	public function getFields($table_name){
		return $this->query('DESCRIBE '.$table_name);
	}
	/**
	 * @see \kenel\db\databaseAbstract::getTables()
	 */
	public function getTables(){
		return $this->query('show tables');
	}
	/**
	 * @see \kenel\db\databaseAbstract::beginTransaction()
	 */
	public function beginTransaction(){
		$this->write_link->beginTransaction();
	}
	/**
	 * @see \kenel\db\databaseAbstract::commit()
	 */
	public function commit(){
		$this->write_link->commit();
	}
	/**
	 * @see \kenel\db\databaseAbstract::rollBack()
	 */
	public function rollBack(){
		$this->write_link->rollBack();
	}
}
?>