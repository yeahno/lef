<?php
/**
 * ============================================================================
 * 自动注入容器类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Di{
	//注册的类或服务
	private static $registers=array();
	/**
	 * 获得实例化后的类或服务
	 * @param string $alias  注册的服务别名
	 * @throws \Exception
	 * @return object
	 * 当企图获取的别名没有注册时，将从\Kenel空间命名下寻找别名对应的服务
	 */
	public static function get($alias){
		if(!isset(self::$registers[$alias])){
			$service='\\Kenel\\'.self::snakeToCamel($alias);
			if(class_exists($service)){
				self::set($alias,$service);
			}else{
				throw new \DomainException($alias . ' was not found in di',500);
			}
		}
		if(!is_object(self::$registers[$alias]) || self::$registers[$alias] instanceof \Closure){
			if(is_callable(self::$registers[$alias])){
				$reflect = new \ReflectionFunction(self::$registers[$alias]);
				$params=$reflect->getParameters();
				if(empty($params)){
					self::set($alias,$reflect->invoke());
				}else{
					$args=self::getReflectArgs($alias,$params);
					self::set($alias,$reflect->invokeArgs($args));
				}
			}else if(is_string(self::$registers[$alias])){
				$reflect  = new \ReflectionClass(self::$registers[$alias]);
				$constructor=$reflect->getConstructor();
				if(is_null($constructor)){
					self::set($alias,$reflect->newInstance());
				}else{
					$params=$constructor->getParameters();
					if(empty($params)){
						self::set($alias,$reflect->newInstance());
					}else{
						$args=self::getReflectArgs($alias,$params);
						self::set($alias,$reflect->newInstanceArgs($args));
					}
				}
			}else{
				throw new \InvalidArgumentException('Illegal type of '.$alias,500);
			}
		}
		return self::$registers[$alias];
	}
	/**
	 * 注册服务
	 * @param string|array $alias
	 * @param string|func|object $value
	 */
	public static function set($alias,$value=null){
		if(is_array($alias)){
			self::$registers=array_merge(self::$registers,$alias);
		}else{
			self::$registers[$alias]=$value;
		}
	}
	/**
	 * 检查是否注册
	 * @param string $alias
	 */
	public static function has($alias){
		return isset(self::$registers[$alias]);
	}
	/**
	 * 移除注册的类
	 * @param string $alias
	 */
	public static function remove($alias){
		if(isset(self::$registers[$alias])){
			unset(self::$registers[$alias]);
		}
	}
	/**
	 * 根据反射出的参数，获取对象
	 * @param  [string] $alias  [键名]
	 * @param  [array] $params [ReflectionParameter对象组成的数组]
	 * @return [array] 
	 */
	private static function getReflectArgs($alias,$params){
		$args=array();
		foreach($params as $v){
			try{
				$param_class=$v->getClass();
			}catch(Exception $e){
				throw new Exception($alias.' exception :'.$e->getMessage(),500);
			}
			if(is_null($param_class)){
				if($v->isOptional()){
					$args[]=$v->getDefaultValue();
					continue;
				}
				throw new Exception($alias.' argument of "$'.$v->getName().'"  must specify a class or has a default value',500);
			}
			$find=false;
			$class_name=$param_class->getName();
			foreach(self::$registers as $key=>$val){
				if((is_object($val) && $param_class->isInstance($val)) || (is_string($val) && $val==$class_name)) {
					$args[]=self::get($key);
					$find=true;
					break;
				}
			}
			if(!$find){
				$arr=explode('\\',$class_name);
				$name=self::camelToSnake(array_pop($arr));
				if($alias==$name){
					self::remove($alias);
					$args[]=self::get($name);
					self::set($alias,self::$registers[$alias]);
				}else{
					$args[]=self::get($name);
				}
			}
		}
		return $args;
	}
	/**
	 * [snakeToCamel 蛇形命名转为大驼峰命名]
	 * @param  [string] $str [要转换的字符串]
	 * @return [string]      [转换后的字符串]
	 */
	public static function snakeToCamel($str){
		$arr = explode('_', $str);
		$rs = '';
		foreach($arr as $v){
			$rs.= ucfirst($v);
		}
		return $rs;
	}
	/**
	 * [camelToSnake 大驼峰命名转为蛇形命名]
	 * @param  [string] $str [要转换的字符串]
	 * @return [string]      [转换后的字符串]
	 */
	public static function camelToSnake($str){
		$arr = array();
		$rs='';
		for($i=0;$i<strlen($str);$i++){
			$asicc=ord($str[$i]);
			if($asicc>=65 && $asicc <=90){
				if($i>0){
					$rs.='_';
				}
				$rs.=chr($asicc+32);
			}else{
				$rs.=$str[$i];
			}
		}
		return $rs;
	}
	/**
	 * [snakeToCamel 蛇形命名转为小驼峰命名]
	 * @param  [string] $str [要转换的字符串]
	 * @return [string]      [转换后的字符串]
	 */
	public static function snakeToSCamel($str){
		$arr = explode('_', $str);
		$rs = '';
		foreach($arr as $k=>$v){
			if($k){
				$rs.= ucfirst($v);
			}else{
				$rs.= lcfirst($v);
			}
		}
		return $rs;
	}
}
?>