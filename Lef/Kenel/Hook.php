<?php
namespace Kenel;
/**
 * ============================================================================
 * 事件类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		2.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Hook{
	//事件
	private static $listens=array();
	/**
	 * 监听事件
	 * @param string $event_key
	 * @param func   $exec
	 */
	public static function addListen($event_key,$exec){
		self::$listens[$event_key][]=$exec;
	}
	/**
	 * 移除监听事件
	 * @param string $event_key
	 */
	public static  function removeListen($event_key){
		if(isset(self::$listens[$event_key])){
			unset(self::$listens[$event_key]);
		}
	}
	/**
	 * 是否有监听事件
	 * @param string $event_key
	 */
	public static function hasListen($event_key){
		return isset(self::$listens[$event_key]) && count(self::$listens[$event_key])>0;
	}
	/**
	 * 触发事件
	 * @param string $event_key
	 */
	public static function trigger($event_key,$data=null){
		if(self::hasListen($event_key)){
			foreach(self::$listens[$event_key] as $scripts){
				if(is_callable($scripts)){
					is_array($data) ? call_user_func_array($scripts,$data) : call_user_func($scripts,$data);
				}else{
					throw new \Exception('Illegal type of hook event can not execute in '.$event_key,500);
				}
			}
		}
	}
}
?>