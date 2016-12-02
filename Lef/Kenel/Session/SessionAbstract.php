<?php 
/**
 * ============================================================================
 * session抽象类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
namespace Kenel\Session;
abstract class SessionAbstract{
	public function __construct(array $configs=array()){
		if(!empty($configs)){
			foreach($configs as $k=>$v){
				ini_set('session.'.$k, $v);
			}
		}
		$this->onStart();
	}
	abstract function onStart();

	public function get($key){
		return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	}
	public function set($key,$value){
		$_SESSION[$key]=$value;
	}
	public function getSessionId(){
		return session_id();
	}
	public function clean(){
		$_SESSION=array();
		session_destroy();
	}
}
?>