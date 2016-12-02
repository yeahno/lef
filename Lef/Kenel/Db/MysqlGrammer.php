<?php
namespace Kenel\Db;
use Kenel\Db\GrammerAbstract;
/**
 * ============================================================================
 * 数据库语法分析类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class MysqlGrammer extends GrammerAbstract{
	private $tables=array();
	/**
	 * 组装查询语句
	 * @return [type] [description]
	 */
	public function getSelectSql(){
		return 'select '.$this->parseField().' from '.$this->parseTable().$this->parseJoin().$this->parseWhere().$this->parseGroup().$this->parseHaving().$this->parseOrder().$this->parseLimit();
	}
	/**
	 * 组装插入语句
	 * (non-PHPdoc)
	 * @see Service\Db.GrammerAbstract::buildInsert()
	 * $this->table('demo')->insert(['a','b']);
	 * $this->table('demo')->insert(['a'=>'a','#b'=>'b']);
	 */
	public function getInsertSql(){
		$field=$value=$sep='';
		if(isset($this->options['data'][0]) && is_array($this->options['data'][0])){
			foreach($this->options['data'] as $k=>$v){
				list($fields,$values,$binds)=$this->getSingleInsert($v);
				if(!$sep){
					$field=implode(',',$fields);
				}elseif($field!=implode(',',$fields)){
					throw new \InvalidArgumentException("multi insert field different",500);
				}
				$value.=$sep.'('.implode(',',$values).')';
				$this->binds=array_merge($this->binds,$binds);
				$sep=',';
			}
		}else{
			list($fields,$values,$binds)=$this->getSingleInsert($this->options['data']);
			$field=implode(',',$fields);
			$value='('.implode(',',$values).')';
			$this->binds=$binds;
		}
		$field=$field ? '('.$field.')' : '';
		return 'insert into '.$this->parseTable().' '.$field.'values '.$value;
	}
	/**
	 * 单个的插入数据处理
	 * @param  [type] $datas [description]
	 * @return [type]        [description]
	 */
	private function getSingleInsert($datas){
		$fields=$values=$binds=array();
		if(isset($datas[0])){
			$values=array_fill(0,count($datas),'?');
			$binds=$datas;
		}else{
			foreach($datas as $k=>$v){
				if($k[0]=='#'){
					$k=ltrim($k,'#');
					$fields[]='`'.$k.'`';
					$values[]=$v;
				}else{
					$fields[]='`'.$k.'`';
					$values[]='?';
					$binds[]=$v;
				}
			}
		}
		return array($fields,$values,$binds);
	}
	/**
	 * 组装更新语句
	 * (non-PHPdoc)
	 * @see Service\Db.GrammerAbstract::buildUpdate()
	 */
	public function getUpdateSql(){
		$set=$type='';
		foreach($this->options['data'] as $k=>$v){
			if($k[0]=='#'){
				$set.=$type.'`'.ltrim($k,'#').'`='.$v;
			}else{
				$set.=$type.'`'.$k.'`=?';
				$this->binds[]=$v;
			}
			$type=',';
		}
		return 'update '.$this->parseTable()." set $set".$this->parseWhere().$this->parseLimit();
	}
	/**
	 * 组装删除语句
	 * (non-PHPdoc)
	 * @see Service\Db.GrammerAbstract::buildDelete()
	 */
	public function getDeleteSql(){
		return 'delete from '.$this->parseTable().$this->parseWhere().$this->parseOrder().$this->parseLimit();
	}
	/**
	 * 分析字段
	 * @return string
	 */
	private function parseField($default_field='*'){
		return isset($this->options['field']) ? $this->options['field'] : $default_field;
	}
	/**
	 * 分析表名
	 * @throws \LogicException
	 * @throws \InvalidArgumentException
	 * @return string
	 */
	private function parseTable(){
		if(!isset($this->options['table']) || !$this->options['table']){
			throw new \LogicException('Table is not exist!',500);
		}
		$table='';
		if(is_string($this->options['table'])){
			$table=$this->options['table'];
		}elseif(is_array($this->options['table'])){
			$rs='';
			foreach($this->options['table'] as $k=>$v){
				if(is_object($v) && $v instanceof self){
					$v='('.$v->getSelectSql().')';
				}
				$this->tables[$k]=$v;
				if(is_numeric($k)){
					$rs.=',`'.$v.'`';
				}else{
					$rs.=','.$v.' as `'.$k.'`';
				}
			}
			$table=ltrim($rs,',');
		}else if(is_object($this->options['table']) && $this->options['table'] instanceof self){
			$table='('.$this->options['table']->getSelectSql().') as lef_tmp';
		}else{
			throw new \InvalidArgumentException('Illegal table types',500);
		}
		return $table;
	}
	/**
	 * 分析条件
	 * @return string
	 */
	private function parseWhere(){
		$where='';
		if(isset($this->options['where'])){
			if (is_array($this->options['where'])){
				$where=$this->parseWhereArray($this->options['where'],'and');
			}else{
				$where= $this->options['where'];
			}
		}
		return $where ? ' where '.$where : '';
	}
	/**
	 * 分析排序
	 * @return string
	 */
	private function parseOrder(){
		$order='';
		if(isset($this->options['order'])){
			$order=' order by '.$this->options['order'];
		}
		return $order;
	}
	/**
	 * 分析限制条目
	 * @return string
	 */
	private function parseLimit(){
		$limit='';
		if(isset($this->options['limit'])){
			if(is_string($this->options['limit'])){
				$this->options['limit']=explode(',',$this->options['limit']);
			}
			if(!is_numeric($this->options['limit'][0]) || (isset($this->options['limit'][1]) && !is_numeric($this->options['limit'][1]))){
				throw new \InvalidArgumentException('Illegal limit types',500);
			}
			if(isset($this->options['limit'][1])){
				$limit= ' limit '.$this->options['limit'][0].','.$this->options['limit'][1];
			}else{
				$limit= ' limit '.$this->options['limit'][0];
			}
		}
		return $limit;
	}
	/**
	 * 分析分组
	 * @return string
	 */
	private function parseGroup(){
		return isset($this->options['group']) ? ' group by '.$this->options['group'] : '';
	}
	/**
	 * 分析联表
	 * @return string
	 */
	private function parseJoin(){
		$join='';
		if(!empty($this->options['join'])){
			$join=' '.implode(' ',$this->options['join']);
		}
		return $join;
	}
	/**
	 * 分析having
	 * @return string
	 */
	private function parseHaving(){
		$having='';
		if(isset($this->options['having'])){
			$having=' having '.$this->options['having'];
		}
		return $having;
	}
	/**
	 * 分析where数组条件语句
	 * @param array $wheres
	 * @param string $conjunctor
	 */
	private function parseWhereArray($wheres,$conjunctor){
		$conditions = array();
		foreach ($wheres as $key => $value){
			$type = gettype($value);
			if(is_int($key) && $type=='array'){
				$conditions[]='('.$this->parseWhereArray($value,'and').')';
			}elseif(preg_match('/^or(\[\d+\])?$/i',$key)){
				$conditions[]='('.$this->parseWhereArray($value,'or').')';
			}else{
				if($key=='#'){
					$conditions[]=$value;
					continue;
				}
				preg_match('/^([\w\.]+)(\[(>|>=|<|<=|!|<>|><|like)?\])?$/', $key, $matchs);
				$column = $matchs[1];
				if (isset($matchs[3])){
					if ($matchs[3] == '!'){
						switch ($type){
							case 'NULL':
								$conditions[] = $column.' is not null';
								break;
							case 'array':
								$conditions[] = $column.' not in('.implode(',',array_fill(0,count($value),'?')).')';
								$this->binds=array_merge($this->binds,$value);
								break;
							default:
								$conditions[] = $column.'!=?';
								$this->binds[]=$value;
								break;
						}
					}else{
						if ($matchs[3] == '<>' || $matchs[3] == '><'){
							if ($type == 'array'){
								if ($matchs[3] == '><'){
									$column .= ' not';
								}
								$conditions[] = $column.' between ? and ?';
								$this->binds[]=array_shift($value);
								$this->binds[]=array_shift($value);
							}
						}else{
							$conditions[] = $column.' '.$matchs[3].' ?';
							$this->binds[]=$value;
						}
					}
				}else{
					if (is_int($key)){
						$conditions[] = '?';
						$this->binds[]=$value;
					}else{
						switch ($type){
							case 'NULL':
								$conditions[] = $column.' is null';
								break;
							case 'array':
								$conditions[] = $column.' in('.implode(',',array_fill(0,count($value),'?')).')';
								$this->binds=array_merge($this->binds,$value);
								break;
							default:
								$conditions[] = $column.'=?';
								$this->binds[]=$value;
								break;
						}
					}
				}
			}
		}
		return implode(' '.$conjunctor.' ', $conditions);
	}
}
?>