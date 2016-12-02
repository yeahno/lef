<?php
namespace Kenel;
use Kenel\Hook;
use Di;
/**
 * ============================================================================
 * 分发类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Dispatch{
	protected $success=false;
	protected $result='';
	/**
	 * 执行某个控制器里的方法
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 * @param array $params
	 * @throws \RuntimeException
	 * @return string
	 */
	public function exec($module,$controller,$action,$params=array()){
		$this->success=false;
		$this->result='';
		$this->initModule($module);
		Hook::trigger('before_action',array(&$module,&$controller,&$action,&$params));
		$controller_class=$module.'\\Controller\\'.$controller;
		$action_func=$action.'Action';
		if(class_exists($controller_class)){
			$reflect  = new \ReflectionClass ($controller_class);
			if($reflect->hasMethod($action_func)){
				$controller_obj=$reflect->newInstance();//new $controller_class();
				$method=$reflect->getMethod($action_func);
				$parameters=$method->getParameters();
				if(empty($parameters)){
					$this->success=true;
					$this->result=$method->invoke($controller_obj);
				}else{
					$args=array();
					$lacked_params=false;
					foreach($parameters as $v){
						$params_key=$v->getName();
						if(!isset($params[$params_key])){
							if($v->isOptional()){
								$params[$params_key]=$v->getDefaultValue();
							}else{
								$lacked_params=true;
								break;
							}
						}
						$args[]=$params[$params_key];
					}
					if(!$lacked_params){
						$this->success=true;
						$this->result=$method->invokeArgs($controller_obj, $args);
					}
				}
			}
		}
		Hook::trigger('after_action',array(&$module,&$controller,&$action,&$params,&$this->success,&$this->result));
		return $this;
	}
	/**
	 * 初始化当前请求的模块
	 */
	public function initModule($module){
		static $modules=[];
		$class_name='\\'.$module.'\\Module';
		Di::get('log')->setSubDir($module);
		if($module && !isset($modules[$module]) && class_exists($class_name)){
			Hook::trigger('before_module_init',array(&$module));
			$modules[$module]=new $class_name();
			Hook::trigger('after_module_init',array(&$module));
		}
	}
	/**
	 * 是否执行成功
	 * @return boolean [description]
	 */
	public function isSuccess(){
		return $this->success;
	}
	/**
	 * 获取执行结果
	 * @return [type] [description]
	 */
	public function getResult(){
		return $this->result;
	}
}
